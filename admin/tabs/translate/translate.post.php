<?php

/**
 * When form is submitted, values must be saved in DB
 */

if ( isset($_POST) && count($_POST) > 0 ) {
    include "translate.post.update.php";
}

if ( isset($_GET['translate_id']) ) {
    // Sequence of steps to automatically update the DB and show current valid texts into the post-translate page.
    $id = $_GET['translate_id'];
    fillDictionaryTableByPost($id, true);
    findDuplicateRows($id);
    querySavedPostTexts($id, true);
    updatePostTextIDs($id);
    removeNullRows();
    querySavedPostTexts($id, true);
    fillDictionaryTableByPost($id, true);
}

function getSinglePostData($post_id) {
    if ( isset($GLOBALS['cfg']['getSinglePostData']) ) {
        return $GLOBALS['cfg']['getSinglePostData'];
    } else {
        showFunctionFired('--> getSinglePostData()');
        $table = $GLOBALS['cfg']['table'];
        $postTable = $GLOBALS['cfg']['postTable'];
        $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
        $translationLanguages = getTranslationLanguages();
        $addSelectLanguage = "";
        $addWhereLanguage = "";
        foreach ($translationLanguages as $key => $translationLanguage) {
            $lang = convertLanguageCodesForDB($translationLanguage);
            $addSelectLanguage .= ", count($lang) AS $lang ";
            $addWhereLanguage .= " OR $lang IS NOT NULL ";
        }

        $query_getSinglePostData = "SELECT $table.post_id, $postTable.post_title AS post_title, $postTable.guid AS post_guid, post_type AS post_type, count($defaultLanguage) AS $defaultLanguage $addSelectLanguage FROM $table, $postTable WHERE ($defaultLanguage IS NOT NULL $addWhereLanguage) AND $table.post_id = $postTable.ID AND $table.track_language = '$defaultLanguage' AND $table.post_id = $post_id AND $table.post_text_id IS NOT NULL GROUP BY $table.post_id";
        $singlePostData = $GLOBALS['wpdb']->get_results($query_getSinglePostData)[0];
        $GLOBALS['cfg']['getSinglePostData'] = $singlePostData;
        return $singlePostData;
    }
}

function showTranslationLine($savedPostText) {
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $rows = round( strlen($savedPostText->$defaultLanguage) / 50 );
    $rows = $rows > 2 ? $rows : 2 ;
    ?>
        <div class="md-translate__page--item">
            <span class="md-translate__page--item-original"><?php echo $savedPostText->$defaultLanguage; ?></span>
            <div class="md-translate__page--item-translations">
                <?php
                    foreach ($savedPostText as $lang => $languageVariant) {
                        if ( $lang === 'id' ) {
                            echo "<input type='hidden' name='id[]' value='$languageVariant'>";
                        }
                        else if ( $lang !== $defaultLanguage && $lang !== 'id' && $lang !== 'post_text_id' ) {
                            $languageCode = str_replace("_","-",$lang);
                            ?>
                                <label for=""><span class="md-translate__page--item-translations-icon-mobile">⤷</span><?php echo getLanguageName($languageCode); ?> <textarea id="" rows="<?php echo $rows; ?>" name="<?php echo $lang; ?>[]"><?php echo $languageVariant; ?></textarea></label>
                            <?php
                        }
                    }
                ?>
            </div>
        </div>
    <?php
}

function showTranslationLines() {
    $savedPostTexts = getSavedPostTexts($_GET['translate_id'], true);
    
    foreach ( $savedPostTexts as $savedPostText ) {
        if ( $savedPostText->post_text_id !== null && $savedPostText->post_text_id !== '' ) {
            showTranslationLine($savedPostText);
        }
    }
}

function saveButtons() {
    ?>
        <p class="submit">
            <a href="<?php echo $GLOBALS['cfg']['current_link']; ?>" class="button-secondary">Scan it again</a>
            <input type="submit" class="button-primary" value="Save Translations">
        </p>
    <?php
}

?>

<a href="?page=my-dictionary&tab=translate" class="md-translate__page--back">← Back to Global process</a>

<table class="md-translate__page--header">
    <tr>
        <td class="md-translate__page--header-title">
            <h2><?php echo getSinglePostData($_GET['translate_id'])->post_title; ?></h2>
        </td>
        <td class="md-translate__page--header-progress">
            <div class="md-translate__page--header-progress-bar">
                Translated <?php showSingleProgressBar(getSinglePostData($_GET['translate_id'])); ?>
            </div>
        </div>
    </tr>
</table>

<form method="post" class="md-translate__page--form">
    <?php saveButtons(); ?>
    <div class="md-translate__page--type">TITLE</div>
    <div class="md-translate__page--type">CONTENT</div>
    <?php showTranslationLines(); ?>
    <?php saveButtons(); ?>
</form>