<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>500 - Server Error</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }

        h1 {
            font-size: 80px;
            margin: 0;
        }

        pre {
            text-align: left;
            width: 80%;
            margin: 30px auto;
            background: #f5f5f5;
            padding: 20px;
            overflow-x: auto;
        }
    </style>
</head>

<body>

    <h1>500</h1>

    <p>
        Terjadi kesalahan pada sistem.
    </p>


    <?php if (isset($exception)): ?>

        <pre>
<?= htmlspecialchars(
    $exception->getMessage()
) ?>


File:
<?= htmlspecialchars(
    $exception->getFile()
) ?>


Line:
<?= htmlspecialchars(
    $exception->getLine()
) ?>

        </pre>

    <?php endif; ?>


</body>
</html>