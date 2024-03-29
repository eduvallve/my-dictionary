<?php

function reloadPage() {
    ?>
        <script type="application/javascript">
            window.location.href="<?php echo $GLOBALS['cfg']['current_link']; ?>";
        </script>
    <?php
}

function convertLanguageCodesForDB($code) {
    return str_replace("-", "_", $code);
}

function convertAsciiValues($content) {
    $content = str_replace('"', '\"', $content);
    $content = str_replace("'", "\'", $content);
    $content = htmlentities($content);
    return $content;
}

function getAllLanguages() {
    if (function_exists('plugin_dir_path')) {
        $path = plugin_dir_path( __DIR__ );
    } else {
        $path = '../../..';
    }
    $file = $path.'/languages.json';
    $json = file_get_contents($file);
    return json_decode($json,true);
}

function getLanguageName($languageCode) {
    return getAllLanguages()[$languageCode];
}

function getTranslationLanguages() {
    return array_values(array_diff(getSupportedLanguages(),[getDefaultLanguage()]));
}

function successMessage($msg) {
    echo "<div class='md-msg md-msg__success'>$msg</div>";
}

function warningMessage($msg) {
    echo "<div class='md-msg md-msg__warning'>$msg</div>";
}

function infoMessage($msg) {
    echo "<div class='md-msg md-msg__info'>$msg</div>";
}

?>