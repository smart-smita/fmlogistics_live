<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            margin: 0;
            padding: 50px;
        }
        .qr-container {
            background-color: #ffffff;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e1c77;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .qr-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .url-text {
            font-size: 14px;
            color: #333;
            word-break: break-all;
            margin-bottom: 30px;
        }
        .btn-print {
            background-color: #1e1c77;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print:hover {
            background-color: #0a1b5c;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .qr-container {
                border: none;
                box-shadow: none;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="qr-container">
    <h1>Scan to Report Near Miss</h1>
    <p>Use your smartphone camera to scan this QR code and quickly report a Near Miss.</p>
    
    <!-- Generate QR code using Google Chart API or QRServer -->
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?=urlencode($public_url)?>" alt="QR Code" class="qr-image">
    
    <div class="url-text">
        <strong>Or visit:</strong> <br>
        <a href="<?=$public_url?>" target="_blank"><?=$public_url?></a>
    </div>

    <button class="btn-print" onclick="window.print()">
        🖨️ Print QR Code
    </button>
</div>

</body>
</html>
