<?php
$content = file_get_contents('http://localhost/fmlogistic.unitglo.com/Masters/User');
if (preg_match('/function edit_id.*?\{.*?\}/s', $content, $matches)) {
    echo $matches[0];
} else {
    echo "edit_id not found in page source";
}
?>
