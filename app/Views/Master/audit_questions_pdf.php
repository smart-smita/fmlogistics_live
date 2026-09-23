<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .site-category { background-color: #1e1c77; color: white; padding: 10px; margin-top: 20px; font-size: 16px; border-radius: 4px; }
        .category-header { background-color: #f2f2f2; padding: 8px; margin-top: 15px; font-weight: bold; border-left: 4px solid #1e1c77; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; font-weight: bold; }
        .cat-id { width: 60px; text-align: center; }
        .default-val { width: 60px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HSE Audit Question Master</h1>
        <p>Generated on: <?= date('d-m-Y H:i:s') ?></p>
    </div>

    <?php foreach($grouped_questions as $site => $categories): ?>
        <div class="site-category"><?= htmlspecialchars($site) ?></div>
        
        <?php foreach($categories as $catName => $questions): ?>
            <div class="category-header"><?= htmlspecialchars($catName) ?></div>
            <table>
                <thead>
                    <tr>
                        <th class="cat-id">ID</th>
                        <th>Audit Question</th>
                        <th class="default-val">Default</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($questions as $q): ?>
                        <tr>
                            <td class="cat-id"><?= htmlspecialchars($q['audit_category_id']) ?></td>
                            <td><?= htmlspecialchars($q['audit_question']) ?></td>
                            <td class="default-val"><?= htmlspecialchars($q['audit_question_default_value']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endforeach; ?>
</body>
</html>
