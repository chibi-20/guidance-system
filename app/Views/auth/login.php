<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In &mdash; Guidance Record System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/theme.css') ?>" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="text-center mb-4">
                    <img src="<?= base_url('assets/img/jacobo.png') ?>" alt="School logo" class="login-logo mb-3">
                    <h3 class="fw-bold mb-1">Guidance Record System</h3>
                    <p class="text-muted mb-0">Jacobo Z. Gonzales Memorial National High School</p>
                </div>

                <div class="card login-card">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="card-title text-center mb-4">Sign In</h5>

                        <?php if (session('error')) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?= esc(session('error')) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session('message')) : ?>
                            <div class="alert alert-success" role="alert">
                                <?= esc(session('message')) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (validation_errors()) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?= validation_list_errors() ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('login') ?>" method="post">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="username"
                                    name="username"
                                    value="<?= esc(old('username')) ?>"
                                    autofocus
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    required
                                >
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Log In</button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center text-muted small mt-3">&copy; <?= date('Y') ?> Guidance Office</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
