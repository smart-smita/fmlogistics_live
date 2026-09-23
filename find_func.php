<?php
$dir = new RecursiveDirectoryIterator('app');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    $content = file_get_contents($file[0]);
    if (stripos($content, 'function logNcActionHistory') !== false) {
        echo "Found in: " . $file[0] . "<br>";
    }
}
echo "Done.";
?>
