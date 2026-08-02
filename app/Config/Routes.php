<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', static fn () => redirect()->to('/login'));

$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/logout', 'AuthController::logout');

// All routes below require a logged-in session (see app/Filters/AuthFilter.php).
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/dashboard', 'DashboardController::index');

    $routes->get('/students', 'StudentController::index');
    $routes->get('/students/create', 'StudentController::create');
    $routes->post('/students', 'StudentController::store');

    $routes->get('/students/import', 'StudentImportController::showForm', ['filter' => 'role:admin,guidance']);
    $routes->get('/students/import/template', 'StudentImportController::template', ['filter' => 'role:admin,guidance']);
    $routes->post('/students/import', 'StudentImportController::import', ['filter' => 'role:admin,guidance']);

    $routes->get('/students/(:num)', 'StudentController::show/$1');
    $routes->get('/students/(:num)/edit', 'StudentController::edit/$1');
    $routes->post('/students/(:num)', 'StudentController::update/$1');
    $routes->post('/students/(:num)/delete', 'StudentController::delete/$1', ['filter' => 'role:admin,guidance']);

    $routes->get('/students/(:num)/cases/create', 'CaseController::create/$1');
    $routes->post('/students/(:num)/cases', 'CaseController::store/$1');

    $routes->get('/cases', 'CaseListController::index', ['filter' => 'role:guidance,discipline_officer,principal,admin']);
    $routes->get('/cases/(:num)', 'CaseController::show/$1');
    $routes->post('/cases/(:num)/resolve', 'CaseController::resolve/$1');
    $routes->get('/cases/(:num)/pdf', 'CasePdfController::generate/$1');

    $routes->get('/offense-matrix', 'OffenseMatrixController::index', ['filter' => 'role:admin,guidance']);

    $routes->get('/offense-types', 'OffenseTypeController::index', ['filter' => 'role:admin,guidance']);
    $routes->get('/offense-types/create', 'OffenseTypeController::create', ['filter' => 'role:admin,guidance']);
    $routes->post('/offense-types', 'OffenseTypeController::store', ['filter' => 'role:admin,guidance']);
    $routes->post('/offense-types/quick-add', 'OffenseTypeController::quickAdd');
    $routes->get('/offense-types/(:num)/edit', 'OffenseTypeController::edit/$1', ['filter' => 'role:admin,guidance']);
    $routes->post('/offense-types/(:num)', 'OffenseTypeController::update/$1', ['filter' => 'role:admin,guidance']);
    $routes->post('/offense-types/(:num)/toggle', 'OffenseTypeController::toggleActive/$1', ['filter' => 'role:admin,guidance']);

    $routes->get('/reports', 'ReportController::index', ['filter' => 'role:guidance,discipline_officer,principal,admin']);
    $routes->get('/reports/export', 'ReportController::exportCsv', ['filter' => 'role:guidance,discipline_officer,principal,admin']);

    $routes->get('/sections', 'SectionController::index', ['filter' => 'role:admin,guidance']);
    $routes->get('/sections/create', 'SectionController::create', ['filter' => 'role:admin,guidance']);
    $routes->post('/sections', 'SectionController::store', ['filter' => 'role:admin,guidance']);
    $routes->get('/sections/(:num)/edit', 'SectionController::edit/$1', ['filter' => 'role:admin,guidance']);
    $routes->post('/sections/(:num)', 'SectionController::update/$1', ['filter' => 'role:admin,guidance']);
    $routes->post('/sections/(:num)/delete', 'SectionController::delete/$1', ['filter' => 'role:admin,guidance']);

    $routes->get('/promotion', 'PromotionController::showForm', ['filter' => 'role:admin']);
    $routes->post('/promotion/preview', 'PromotionController::preview', ['filter' => 'role:admin']);
    $routes->post('/promotion/execute', 'PromotionController::execute', ['filter' => 'role:admin']);

    $routes->get('/users', 'UserController::index', ['filter' => 'role:admin']);
    $routes->get('/users/create', 'UserController::create', ['filter' => 'role:admin']);
    $routes->post('/users', 'UserController::store', ['filter' => 'role:admin']);
    $routes->get('/users/(:num)/edit', 'UserController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('/users/(:num)', 'UserController::update/$1', ['filter' => 'role:admin']);
    $routes->post('/users/(:num)/reset-password', 'UserController::resetPassword/$1', ['filter' => 'role:admin']);
    $routes->post('/users/(:num)/toggle', 'UserController::toggleActive/$1', ['filter' => 'role:admin']);
});
