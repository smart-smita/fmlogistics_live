<?php
function display_error_message($validation, $field) {
    if (isset($validation) && $validation->hasError($field)) {
        return $validation->getError($field);
    }
    return false;
}
?>
