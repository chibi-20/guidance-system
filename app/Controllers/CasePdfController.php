<?php

namespace App\Controllers;

use App\Models\CaseActionModel;
use App\Models\CaseModel;
use App\Models\OthersInvolvedModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class CasePdfController extends BaseController
{
    public function generate($caseId)
    {
        $caseModel = new CaseModel();
        $case      = $caseModel->getFullDetailsForPdf((int) $caseId);

        if ($case === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseActionModel = new CaseActionModel();
        $caseAction      = $caseActionModel->findByCaseId((int) $caseId) ?? [];

        if (! empty($caseAction)) {
            $caseAction['action_prior']       = json_decode((string) ($caseAction['action_prior'] ?? 'null'), true) ?? [];
            $caseAction['disciplinary_action'] = json_decode((string) ($caseAction['disciplinary_action'] ?? 'null'), true) ?? [];
        }

        $othersInvolvedModel = new OthersInvolvedModel();
        $othersInvolved      = $othersInvolvedModel->getForCase((int) $caseId);

        $html = view('cases/pdf_template', [
            'case'            => $case,
            'caseAction'      => $caseAction,
            'othersInvolved'  => $othersInvolved,
        ]);

        $mpdf = new Mpdf([
            'format'       => 'Letter',
            'orientation'  => 'P',
            'margin_left'  => 15,
            'margin_right' => 15,
            'margin_top'   => 15,
            'margin_bottom' => 15,
        ]);
        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output('', Destination::STRING_RETURN);
        $filename   = 'DisciplineForm_' . $case['case_no'] . '.pdf';

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdfContent);
    }
}
