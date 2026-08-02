<?php
$role = session('role');

$currentSegment = explode('/', trim(uri_string(), '/'))[0] ?? '';

$navTitles = [
    'dashboard'     => 'Dashboard',
    'students'      => 'Students',
    'cases'         => 'Cases',
    'reports'       => 'Reports',
    'offense-types' => 'Manage Offense Types',
    'offense-matrix' => 'Offense Consequence Matrix',
    'sections'      => 'Sections',
    'users'         => 'Users',
    'promotion'     => 'Promote Students',
];
$pageTitle = $title ?? ($navTitles[$currentSegment] ?? 'Guidance Record System');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> &mdash; Guidance Record System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/theme.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
            <div class="offcanvas-header d-lg-none">
                <span class="text-white fw-semibold" id="appSidebarLabel">Menu</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body d-flex flex-column p-0">
              <div class="sidebar-inner d-flex flex-column flex-grow-1">
                <div class="brand">
                    <img src="<?= base_url('assets/img/jacobo.png') ?>" alt="School logo">
                    <div class="brand-text">
                        <div class="school-name">Jacobo Z. Gonzales Memorial NHS</div>
                        <div class="brand-subtitle">Guidance Record System</div>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column px-2">
                    <li class="nav-item">
                        <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $currentSegment === 'dashboard' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    <?php if (in_array($role, ['admin', 'guidance', 'discipline_officer', 'adviser', 'principal'], true)) : ?>
                        <li class="nav-item">
                            <a href="<?= site_url('students') ?>" class="nav-link <?= $currentSegment === 'students' ? 'active' : '' ?>">
                                <i class="bi bi-people-fill"></i> Students
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['guidance', 'discipline_officer', 'principal', 'admin'], true)) : ?>
                        <li class="nav-item">
                            <a href="<?= site_url('cases') ?>" class="nav-link <?= $currentSegment === 'cases' ? 'active' : '' ?>">
                                <i class="bi bi-folder2-open"></i> Cases
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('reports') ?>" class="nav-link <?= $currentSegment === 'reports' ? 'active' : '' ?>">
                                <i class="bi bi-bar-chart-line"></i> Reports
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'guidance'], true)) : ?>
                        <li><hr class="nav-divider"></li>
                        <li class="nav-section-label">Administration</li>

                        <?php if (in_array($role, ['admin', 'guidance'], true)) : ?>
                            <li class="nav-item">
                                <a href="<?= site_url('offense-types') ?>" class="nav-link <?= $currentSegment === 'offense-types' ? 'active' : '' ?>">
                                    <i class="bi bi-clipboard-check"></i> Offense Types
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= site_url('offense-matrix') ?>" class="nav-link <?= $currentSegment === 'offense-matrix' ? 'active' : '' ?>">
                                    <i class="bi bi-diagram-3"></i> Offense Matrix
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= site_url('sections') ?>" class="nav-link <?= $currentSegment === 'sections' ? 'active' : '' ?>">
                                    <i class="bi bi-building"></i> Sections
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($role === 'admin') : ?>
                            <li class="nav-item">
                                <a href="<?= site_url('users') ?>" class="nav-link <?= $currentSegment === 'users' ? 'active' : '' ?>">
                                    <i class="bi bi-person-gear"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= site_url('promotion') ?>" class="nav-link <?= $currentSegment === 'promotion' ? 'active' : '' ?>">
                                    <i class="bi bi-mortarboard"></i> Promote Students
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
              </div>
            </div>
        </div>

        <div class="app-main flex-grow-1 d-flex flex-column">
            <nav class="app-topbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="sidebar-toggle btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="page-title"><?= esc($pageTitle) ?></h1>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="d-none d-sm-flex align-items-center gap-2">
                        <span class="fw-semibold"><?= esc(session('full_name')) ?></span>
                        <span class="user-role-badge text-capitalize"><?= esc(str_replace('_', ' ', session('role') ?? '')) ?></span>
                    </span>
                    <a href="<?= site_url('logout') ?>" class="btn-logout-icon" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </nav>

            <div class="flex-grow-1 p-4">
                <?php if (session('message')) : ?>
                    <div class="alert alert-success"><?= esc(session('message')) ?></div>
                <?php endif; ?>
                <?php if (session('error')) : ?>
                    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
                <?php endif; ?>
                <?php if (session('warning')) : ?>
                    <div class="alert alert-warning"><?= esc(session('warning')) ?></div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
