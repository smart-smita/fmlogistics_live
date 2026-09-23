<?php
$file = 'app/Controllers/Customer/Audit_dashboard.php';
$content = file_get_contents($file);
$content = str_replace(
    ["return \$this->response->setJSON(['clusters' => []]);", "return \$this->response->setJSON(['clusters' => \$clusters]);"], 
    ["return \$this->response->setJSON([]);", "return \$this->response->setJSON(\$clusters);"], 
    $content
);
$content = str_replace(
    ["return \$this->response->setJSON(['locations' => []]);", "return \$this->response->setJSON(['locations' => \$locations]);"], 
    ["return \$this->response->setJSON([]);", "return \$this->response->setJSON(\$locations);"], 
    $content
);
file_put_contents($file, $content);
echo "Fixed JSON responses in $file\n";
