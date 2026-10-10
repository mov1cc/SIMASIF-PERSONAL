<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'SIMASIF', ENT_QUOTES, 'UTF-8') ?> - SIMASIF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"  rel="stylesheet">
</head>
<body class="bg-light">

        <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">SIMASIF</a>

            <ul class="navbar-nav ms-auto flex-row gap-3">
                <li class="nav-item">
                    <a class="nav-link" href="/layanan">Layanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="/login">Login Staf</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container py-4">
        <?php require dirname(__DIR__) . '/components/alert.php'; ?>
        <?= $content ?? '' ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php foreach (($scripts ?? []) as $js): ?>
        <script src="<?= htmlspecialchars($js, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
</body>
</html>