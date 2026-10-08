<?php
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;

$uri = Request::uri();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Pegawai', ENT_QUOTES, 'UTF-8') ?> - SIMASIF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <?php foreach (($styles ?? []) as $css): ?>
        <link href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <?php endforeach; ?>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/pegawai/dashboard">SIMASIF Pegawai</a>

            <ul class="navbar-nav me-auto flex-row gap-3">
                <li class="nav-item">
                    <a class="nav-link <?= $uri === '/pegawai/dashboard' ? 'active' : '' ?>"
                       href="/pegawai/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($uri, '/pegawai/layanan') ? 'active' : '' ?>"
                       href="/pegawai/layanan">Layanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($uri, '/pegawai/pelanggan') ? 'active' : '' ?>"
                        href="/pegawai/pelanggan">Pelanggan</a>
                </li>
            </ul>

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
    <?php foreach (($scripts ?? []) as $js): ?>
        <script src="<?= htmlspecialchars($js, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
</body>
</html>