<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #ffffff;
            color: #000000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h1 {
            /* Angka 500 raksasa mengikuti lebar layar */
            font-size: 38vw; 
            font-weight: 900;
            line-height: 0.8;
            letter-spacing: -0.05em;
            width: 100%;
            margin-bottom: 40px;
            -webkit-text-stroke: 1px #000;
        }

        p {
            font-size: 1.15rem;
            color: #111111;
            margin-bottom: 24px;
            font-weight: 400;
            letter-spacing: 0.01em;
        }

        a {
            display: inline-block;
            text-decoration: none;
            color: #000000;
            border: 1px solid #000000;
            padding: 8px 22px;
            border-radius: 50px;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            margin-bottom: 30px;
        }

        a:hover {
            background-color: #000000;
            color: #ffffff;
        }

        /* Blok Tampilan Exception/Error Debugger */
        .error-debug {
            text-align: left;
            width: 100%;
            max-width: 900px;
            margin: 20px auto 0;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px 24px;
            border-radius: 12px;
            overflow-x: auto;
            font-family: "Courier New", Courier, monospace;
            font-size: 0.85rem;
            line-height: 1.6;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .error-debug strong {
            color: #f44747;
            display: block;
            margin-top: 10px;
        }

        .error-debug strong:first-child {
            margin-top: 0;
        }

        /* Batas ukuran maksimum untuk layar monitor yang sangat lebar */
        @media (min-width: 1600px) {
            h1 {
                font-size: 550px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>500</h1>

        <p>
            Terjadi kesalahan pada sistem.
        </p>

        <a href="/">
            Kembali ke halaman utama
        </a>

        <?php if (isset($exception)): ?>
            <pre class="error-debug">
<strong>Message:</strong>
<?= htmlspecialchars($exception->getMessage()) ?>


<strong>File:</strong>
<?= htmlspecialchars($exception->getFile()) ?>


<strong>Line:</strong>
<?= htmlspecialchars($exception->getLine()) ?>
            </pre>
        <?php endif; ?>
    </div>

</body>
</html>