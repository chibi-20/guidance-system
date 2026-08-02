<?php

namespace App\Controllers;

use App\Models\OffenseConsequenceModel;

class OffenseMatrixController extends BaseController
{
    public function index()
    {
        $offenseConsequenceModel = new OffenseConsequenceModel();

        return view('offense_matrix/index', [
            'groupedMatrix' => $offenseConsequenceModel->getAllGroupedByCategory(),
            'generalNote'   => $offenseConsequenceModel->getGeneralNote(),
        ]);
    }
}
