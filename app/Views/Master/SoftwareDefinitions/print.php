<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        .qa-item {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .question {
            font-weight: bold;
            font-size: 16px;
            background-color: #f5f5f5;
            padding: 10px;
            border-left: 4px solid #009ef7;
        }
        .answer {
            padding: 15px 10px 10px 14px;
        }
        .status {
            font-size: 12px;
            color: #888;
            float: right;
            font-weight: normal;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>Definitions Knowledge Base</h1>
        <p>Exported on: <?= date('d/m/Y H:i:s') ?></p>
        <button class="no-print" onclick="window.print()" style="margin-top: 10px; padding: 5px 15px; cursor: pointer;">Print / Save as PDF</button>
    </div>

    <?php if (!empty($definitions)): ?>
        <?php $count = 1; foreach ($definitions as $def): ?>
            <div class="qa-item">
                <div class="question">
                    Q<?= $count++ ?>: <?= esc($def['question']) ?>
                    <span class="status">[<?= esc($def['status']) ?>]</span>
                </div>
                <div class="answer">
                    <?= $def['answer'] ?> <!-- Allow HTML from rich text editor -->
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No definitions found.</p>
    <?php endif; ?>

</body>
</html>
