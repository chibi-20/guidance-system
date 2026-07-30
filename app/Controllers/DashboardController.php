<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\StudentModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $studentModel = new StudentModel();
        $caseModel    = new CaseModel();

        $data = [
            'totalStudents'          => $studentModel->countActive(),
            'openCases'              => $caseModel->countOpen(),
            'flaggedRepeatOffenders' => $caseModel->countFlaggedRepeatOffenders(3),
            'casesThisMonth'         => $caseModel->countThisMonth(),
            'recentCases'            => $caseModel->getRecentCases(10),
            'casesByCategory'        => $caseModel->getCasesByCategoryThisMonth(),
            'topOffenseTypes'        => $caseModel->getTopOffenseTypes(5),
        ];

        return view('dashboard/index', $data);
    }
}
