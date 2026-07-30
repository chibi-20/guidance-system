<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\OffenseTypeModel;
use App\Models\SectionModel;

class CaseListController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $filters = [
            'date_from'         => $this->request->getGet('date_from'),
            'date_to'           => $this->request->getGet('date_to'),
            'offense_type_id'   => $this->request->getGet('offense_type_id'),
            'category'          => $this->request->getGet('category'),
            'status'            => $this->request->getGet('status'),
            'grade_level'       => $this->request->getGet('grade_level'),
            'section_id'        => $this->request->getGet('section_id'),
            'min_count_overall' => $this->request->getGet('min_count_overall'),
        ];

        $caseModel = new CaseModel();
        $cases     = $caseModel->getFilteredPaginated($filters, 25);

        $offenseTypeModel = new OffenseTypeModel();
        $sectionModel     = new SectionModel();

        return view('cases/index', [
            'cases'        => $cases,
            'pager'        => $caseModel->pager,
            'filters'      => $filters,
            'offenseTypes' => $offenseTypeModel->getAll(),
            'sections'     => $sectionModel->orderBy('grade_level')->orderBy('name')->findAll(),
        ]);
    }
}
