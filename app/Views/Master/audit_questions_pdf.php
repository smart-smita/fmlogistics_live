<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e1c77; padding-bottom: 10px; }
        .site-category { background-color: #1e1c77; color: white; padding: 8px 12px; margin-top: 20px; font-size: 13px; font-weight: bold; border-radius: 4px; }
        .category-header { background-color: #f2f2f7; padding: 6px 10px; margin-top: 12px; font-weight: bold; border-left: 4px solid #1e1c77; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f9f9f9; font-weight: bold; font-size: 11px; }
        .cat-id { width: 60px; text-align: center; font-weight: bold; }
        .default-val { width: 60px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0; color:#1e1c77;"><?= htmlspecialchars($template_name ?? 'HSE Audit Question Master') ?></h2>
        <p style="margin:5px 0 0; color:#666; font-size:10px;">Generated on: <?= date('d-m-Y H:i:s') ?></p>
    </div>

    <?php if(!empty($grouped_questions)): ?>
        <?php foreach($grouped_questions as $section => $categories): ?>
            <div class="site-category"><?= htmlspecialchars($section) ?></div>
            
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
                                <td class="cat-id"><?= htmlspecialchars($q['category_id'] ?? $q['audit_category_id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($q['question_text'] ?? $q['audit_question'] ?? '') ?></td>
                                <td class="default-val"><?= htmlspecialchars($q['default_value'] ?? $q['audit_question_default_value'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; padding:30px; color:#888;">No questions found for this template.</p>
    <?php endif; ?>
</body>
</html>
