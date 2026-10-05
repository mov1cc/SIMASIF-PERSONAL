<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Owner', ENT_QUOTES, 'UTF-8') ?> - SIMASIF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/owner/dashboard">SIMASIF Owner</a>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small">
                    <?= htmlspecialchars(Session::get('nama', ''), ENT_QUOTES, 'UTF-8') ?>
                </span>
                <form action="/logout" method="POST" class="m-0">
                    <?= CsrfHelper::field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container-fluid py-4">
        <?php require dirname(__DIR__) . '/components/alert.php'; ?>
        <?= $content ?? '' ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>