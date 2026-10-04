<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>

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
            padding: 0 20px;
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
            /* Menggunakan vw agar angka 403 membesar penuh mengikuti lebar layar */
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
        }

        a:hover {
            background-color: #000000;
            color: #ffffff;
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
        <h1>403</h1>

        <p>
            Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>

        <a href="/">
            Kembali
        </a>
    </div>

</body>
</html>