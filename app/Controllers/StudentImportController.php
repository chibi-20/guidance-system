<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\SchoolYearModel;
use App\Models\SectionModel;
use App\Models\StudentModel;
use Config\Database;
use DateTime;
use Throwable;

class StudentImportController extends BaseController
{
    protected $helpers = ['form', 'url'];

    private const EXPECTED_HEADERS = ['lrn', 'lastname', 'firstname', 'middlename', 'sex', 'birthdate', 'gradelevel', 'section'];
    private const CHUNK_SIZE       = 200;

    public function showForm()
    {
        return view('students/import_form');
    }

    public function template()
    {
        $headers = ['LRN', 'LastName', 'FirstName', 'MiddleName', 'Sex', 'Birthdate', 'GradeLevel', 'Section'];
        $example = ['136420100001', 'Dela Cruz', 'Juan', 'Santos', 'Male', '2010-06-15', '10', 'Locsin'];

        $buffer = fopen('php://temp', 'w+');
        fputcsv($buffer, $headers);
        fputcsv($buffer, $example);
        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="student_import_template.csv"')
            ->setBody($csv);
    }

    public function import()
    {
        // Bulk imports of a few thousand rows can run past the default 30s
        // execution limit; this only affects this request, not the whole app.
        ini_set('max_execution_time', '300');

        $rules = [
            'csv_file' => [
                'label'  => 'CSV file',
                'rules'  => 'uploaded[csv_file]|ext_in[csv_file,csv]|max_size[csv_file,10240]',
                'errors' => [
                    'uploaded' => 'Please choose a CSV file to upload.',
                    'ext_in'   => 'Only .csv files are allowed.',
                    'max_size' => 'The file is too large (max 10MB).',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/students/import')->withInput();
        }

        $file = $this->request->getFile('csv_file');

        [$header, $rows] = $this->readCsv($file->getTempName());

        if ($header === null) {
            return redirect()->to('/students/import')->with('error', 'The CSV file is empty.');
        }

        $normalizedHeader = array_map(static fn ($h): string => strtolower(trim((string) $h)), $header);
        // Strip a UTF-8 BOM that Excel commonly prepends to the first cell.
        if (isset($normalizedHeader[0])) {
            $normalizedHeader[0] = ltrim($normalizedHeader[0], "\xEF\xBB\xBF");
        }

        if ($normalizedHeader !== self::EXPECTED_HEADERS) {
            return redirect()->to('/students/import')->with(
                'error',
                'CSV headers do not match the expected template. Expected columns in this exact order: '
                    . 'LRN, LastName, FirstName, MiddleName, Sex, Birthdate, GradeLevel, Section.'
            );
        }

        if ($rows === []) {
            return redirect()->to('/students/import')->with('error', 'The CSV file contains no data rows.');
        }

        // Phase 1: validate every row up front. No database writes happen
        // here, so nothing needs to be undone if a row is bad.
        $errors    = [];
        $validRows = [];
        $seenLrns  = [];

        foreach ($rows as $row) {
            $result = $this->validateRow($row['data'], $row['line'], $seenLrns);

            if (isset($result['error'])) {
                $errors[] = $result['error'];

                continue;
            }

            $validRows[] = $result;
        }

        $summary = [
            'processed' => count($rows),
            'inserted'  => 0,
            'updated'   => 0,
            'errors'    => $errors,
        ];

        // All-or-nothing: if any row failed validation, import nothing.
        if ($errors !== []) {
            return view('students/import_result', ['summary' => $summary]);
        }

        $db = Database::connect();
        $db->transStart();

        try {
            $counts = $this->writeStudents($validRows);
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Student CSV import failed: ' . $e->getMessage());
            $summary['errors'][] = 'Import failed due to an unexpected error and no changes were saved. '
                . 'Please check the file and try again.';

            return view('students/import_result', ['summary' => $summary]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            $summary['errors'][] = 'Import failed due to a database error and no changes were saved.';

            return view('students/import_result', ['summary' => $summary]);
        }

        $summary['inserted'] = $counts['inserted'];
        $summary['updated']  = $counts['updated'];

        return view('students/import_result', ['summary' => $summary]);
    }

    /**
     * Reads the whole CSV into memory: [header row, list of data rows].
     * Blank lines are skipped. Each data row is tagged with its 1-based
     * line number (matching what the user would see if they opened the
     * file in a spreadsheet) so error messages can point back to it.
     *
     * @return array{0: array<int, string>|null, 1: list<array{line: int, data: array<int, string>}>}
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return [null, []];
        }

        $rows    = [];
        $lineNum = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $lineNum++;

            $isBlank = count(array_filter($data, static fn ($v): bool => trim((string) $v) !== '')) === 0;
            if ($isBlank) {
                continue;
            }

            $rows[] = ['line' => $lineNum, 'data' => $data];
        }

        fclose($handle);

        return [$header, $rows];
    }

    /**
     * Validates and normalizes one CSV data row.
     *
     * @param array<int, string> $data
     * @param array<string, int> $seenLrns LRN => line number seen so far in this file, passed by reference
     *
     * @return array{error: string}|array{line: int, lrn: string, last_name: string, first_name: string, middle_name: ?string, gender: string, birthdate: ?string, grade_level: int, section: string}
     */
    private function validateRow(array $data, int $line, array &$seenLrns): array
    {
        $data = array_pad($data, 8, '');
        [$lrn, $lastName, $firstName, $middleName, $sex, $birthdate, $gradeLevel, $section] = array_map(
            static fn ($v): string => trim((string) $v),
            array_slice($data, 0, 8)
        );

        if ($lrn === '') {
            return ['error' => "Row {$line}: missing LRN"];
        }
        if (strlen($lrn) > 20) {
            return ['error' => "Row {$line}: LRN exceeds 20 characters"];
        }
        if (isset($seenLrns[$lrn])) {
            return ['error' => "Row {$line}: duplicate LRN '{$lrn}' (already used on row {$seenLrns[$lrn]})"];
        }

        if ($lastName === '') {
            return ['error' => "Row {$line}: missing LastName"];
        }
        if ($firstName === '') {
            return ['error' => "Row {$line}: missing FirstName"];
        }
        if (strlen($lastName) > 100 || strlen($firstName) > 100 || strlen($middleName) > 100) {
            return ['error' => "Row {$line}: a name field exceeds 100 characters"];
        }

        $normalizedSex = $this->normalizeSex($sex);
        if ($normalizedSex === null) {
            return ['error' => "Row {$line}: invalid Sex '{$sex}' (expected Male, Female, M, or F)"];
        }

        $normalizedBirthdate = null;
        if ($birthdate !== '') {
            $normalizedBirthdate = $this->normalizeBirthdate($birthdate);
            if ($normalizedBirthdate === null) {
                return ['error' => "Row {$line}: invalid Birthdate '{$birthdate}' (expected YYYY-MM-DD or MM/DD/YYYY)"];
            }
        }

        if (! ctype_digit($gradeLevel) || (int) $gradeLevel < 7 || (int) $gradeLevel > 10) {
            return ['error' => "Row {$line}: invalid GradeLevel '{$gradeLevel}' (expected a whole number 7-10)"];
        }

        if ($section === '') {
            return ['error' => "Row {$line}: missing Section"];
        }
        if (strlen($section) > 100) {
            return ['error' => "Row {$line}: Section exceeds 100 characters"];
        }

        $seenLrns[$lrn] = $line;

        return [
            'line'        => $line,
            'lrn'         => $lrn,
            'last_name'   => $lastName,
            'first_name'  => $firstName,
            'middle_name' => $middleName !== '' ? $middleName : null,
            'gender'      => $normalizedSex,
            'birthdate'   => $normalizedBirthdate,
            'grade_level' => (int) $gradeLevel,
            'section'     => $section,
        ];
    }

    private function normalizeSex(string $value): ?string
    {
        return match (strtoupper($value)) {
            'MALE', 'M'   => 'Male',
            'FEMALE', 'F' => 'Female',
            default       => null,
        };
    }

    private function normalizeBirthdate(string $value): ?string
    {
        foreach (['Y-m-d', 'm/d/Y'] as $format) {
            $date = DateTime::createFromFormat('!' . $format, $value);
            if ($date instanceof DateTime && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Writes all validated rows in chunks: resolves/creates sections once
     * up front (there are only ever a handful of distinct sections, unlike
     * students), then per chunk of self::CHUNK_SIZE rows, bulk-fetches
     * existing students/enrollments and issues one insertBatch/updateBatch
     * per group instead of a query per row.
     *
     * @param list<array<string, mixed>> $validRows
     *
     * @return array{inserted: int, updated: int}
     */
    private function writeStudents(array $validRows): array
    {
        $studentModel    = new StudentModel();
        $sectionModel    = new SectionModel();
        $enrollmentModel = new EnrollmentModel();
        $schoolYearModel = new SchoolYearModel();

        $currentYear = $schoolYearModel->where('is_current', 1)->first();
        if ($currentYear === null) {
            throw new \RuntimeException('No current school year is configured.');
        }
        $schoolYearId = $currentYear['id'];

        // Resolve (grade_level, section name) -> section_id, creating
        // missing sections as needed. Distinct sections in a real import
        // number in the dozens at most, so one-by-one find-or-create here
        // is negligible next to the per-row cost of thousands of students.
        $sectionCache = [];
        foreach ($validRows as $row) {
            $key = $row['grade_level'] . '|' . strtolower($row['section']);
            if (isset($sectionCache[$key])) {
                continue;
            }

            $existing = $sectionModel
                ->where('grade_level', $row['grade_level'])
                ->where('name', $row['section'])
                ->first();

            $sectionCache[$key] = $existing['id'] ?? $sectionModel->insert([
                'grade_level' => $row['grade_level'],
                'name'        => $row['section'],
            ]);
        }

        $insertedCount = 0;
        $updatedCount  = 0;

        foreach (array_chunk($validRows, self::CHUNK_SIZE) as $chunk) {
            $lrns = array_column($chunk, 'lrn');

            $existingStudents = $studentModel->select('id, lrn')->whereIn('lrn', $lrns)->findAll();
            $studentIdByLrn   = array_column($existingStudents, 'id', 'lrn');

            $newStudentRows    = [];
            $updateStudentRows = [];

            foreach ($chunk as $row) {
                $studentData = [
                    'lrn'         => $row['lrn'],
                    'last_name'   => $row['last_name'],
                    'first_name'  => $row['first_name'],
                    'middle_name' => $row['middle_name'],
                    'gender'      => $row['gender'],
                    'birthdate'   => $row['birthdate'],
                ];

                if (isset($studentIdByLrn[$row['lrn']])) {
                    $studentData['id']    = $studentIdByLrn[$row['lrn']];
                    $updateStudentRows[] = $studentData;
                } else {
                    $newStudentRows[] = $studentData;
                }
            }

            if ($newStudentRows !== []) {
                $studentModel->insertBatch($newStudentRows, null, self::CHUNK_SIZE);
                $insertedCount += count($newStudentRows);

                $newLrns  = array_column($newStudentRows, 'lrn');
                $freshIds = $studentModel->select('id, lrn')->whereIn('lrn', $newLrns)->findAll();
                $studentIdByLrn += array_column($freshIds, 'id', 'lrn');
            }

            if ($updateStudentRows !== []) {
                $studentModel->updateBatch($updateStudentRows, 'id', self::CHUNK_SIZE);
                $updatedCount += count($updateStudentRows);
            }

            $studentIdsInChunk    = array_values($studentIdByLrn);
            $existingEnrollments  = $enrollmentModel
                ->select('id, student_id')
                ->whereIn('student_id', $studentIdsInChunk)
                ->where('school_year_id', $schoolYearId)
                ->findAll();
            $enrollmentByStudentId = array_column($existingEnrollments, 'id', 'student_id');

            $newEnrollmentRows    = [];
            $updateEnrollmentRows = [];

            foreach ($chunk as $row) {
                $studentId = $studentIdByLrn[$row['lrn']] ?? null;
                if ($studentId === null) {
                    continue;
                }

                $sectionId = $sectionCache[$row['grade_level'] . '|' . strtolower($row['section'])];

                $enrollmentData = [
                    'student_id'     => $studentId,
                    'section_id'     => $sectionId,
                    'school_year_id' => $schoolYearId,
                ];

                if (isset($enrollmentByStudentId[$studentId])) {
                    $enrollmentData['id']  = $enrollmentByStudentId[$studentId];
                    $updateEnrollmentRows[] = $enrollmentData;
                } else {
                    $newEnrollmentRows[] = $enrollmentData;
                }
            }

            if ($newEnrollmentRows !== []) {
                $enrollmentModel->insertBatch($newEnrollmentRows, null, self::CHUNK_SIZE);
            }
            if ($updateEnrollmentRows !== []) {
                $enrollmentModel->updateBatch($updateEnrollmentRows, 'id', self::CHUNK_SIZE);
            }
        }

        return ['inserted' => $insertedCount, 'updated' => $updatedCount];
    }
}
