<?php

namespace App\Controllers;

use App\Models\OffenseTypeModel;
use App\Models\ReportModel;
use App\Models\SectionModel;

class ReportController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        [$startDate, $endDate, $filters] = $this->resolveFiltersFromRequest();

        $reportModel = new ReportModel();

        $offenseTypeModel = new OffenseTypeModel();
        $sectionModel     = new SectionModel();

        return view('reports/index', [
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'filters'           => $filters,
            'resolutionStats'   => $reportModel->resolutionStats($startDate, $endDate),
            'byOffenseType'     => $reportModel->summaryByOffenseType($startDate, $endDate, $filters),
            'byCategory'        => $reportModel->summaryByCategory($startDate, $endDate, $filters),
            'bySection'         => $reportModel->summaryBySection($startDate, $endDate, $filters),
            'repeatOffenders'   => $reportModel->repeatOffendersSummary(3),
            'cases'             => $reportModel->casesByDateRange($startDate, $endDate, $filters),
            'offenseTypes'      => $offenseTypeModel->getAll(),
            'sections'          => $sectionModel->orderBy('grade_level')->orderBy('name')->findAll(),
        ]);
    }

    public function exportCsv()
    {
        [$startDate, $endDate, $filters] = $this->resolveFiltersFromRequest();

        $reportModel = new ReportModel();
        $cases       = $reportModel->casesByDateRange($startDate, $endDate, $filters);

        $filename = 'case_report_' . $startDate . '_to_' . $endDate . '.csv';

        $buffer = fopen('php://temp', 'w+');
        fputcsv($buffer, [
            'Case No', 'Date of Incident', 'Student', 'Grade & Section', 'Offense Type',
            'Category', 'Status', 'Offense Count (Type)', 'Offense Count (Overall)',
        ]);

        foreach ($cases as $case) {
            fputcsv($buffer, [
                $case['case_no'],
                $case['date_of_incident'],
                trim($case['student_last_name'] . ', ' . $case['student_first_name']),
                ! empty($case['grade_level']) ? 'Grade ' . $case['grade_level'] . ' - ' . $case['section_name'] : 'Not enrolled',
                $case['offense_type_name'],
                $case['offense_type_category'],
                $case['status'],
                $case['offense_count_type'],
                $case['offense_count_overall'],
            ]);
        }

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Reads date range + filters from the query string, defaulting the
     * range to the current calendar month when not provided.
     *
     * @return array{0: string, 1: string, 2: array<string, ?string>}
     */
    private function resolveFiltersFromRequest(): array
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $filters = [
            'category'        => $this->request->getGet('category'),
            'offense_type_id' => $this->request->getGet('offense_type_id'),
            'grade_level'     => $this->request->getGet('grade_level'),
            'section_id'      => $this->request->getGet('section_id'),
            'status'          => $this->request->getGet('status'),
        ];

        return [$startDate, $endDate, $filters];
    }
}
