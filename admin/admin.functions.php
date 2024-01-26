<?php

function getAllLanguages() {
    if (function_exists('plugin_dir_path')) {
        $path = plugin_dir_path( __DIR__ );
    } else {
        $path = '..';
    }
    $file = $path.'/languages.json';
    $json = file_get_contents($file);
    return json_decode($json,true);
}

function successMessage($msg) {
    echo "<div class='md-msg__success'><span class='dashicons-before dashicons-saved'> $msg</span></div>";
}

?>