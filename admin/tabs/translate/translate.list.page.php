<?php

function areTranslationLanguages() {
    return count(getSupportedLanguages()) > 1 && count(getSupportedPostTypes()) > 0;
}

function welcomeMessage() {
    return '
        <span class="md-msg__body"><b>Welcome! 🎉</b><br>It&#x2019;s our first time working together! First, please <b>add</b> more languages in the <b>General</b> tab!</span>
        <a class="md-msg__action button-primary" href="?page=my-dictionary">Add languages</a>
    ';
}

/**
 * Start showGlobalLanguagesProgress area
 */

function string_addPostTypes() {
    $postTable = $GLOBALS['cfg']['postTable'];
    $supportedPostTypes = getSupportedPostTypes();
    $addPostTypes = "";
    if ( count($supportedPostTypes) > 0 ) {
        $addPostTypes .= " ( ";
        foreach ($supportedPostTypes as $key => $supportedPostType) {
            $addPostTypes .= " $postTable.post_type = '$supportedPostType' ";
            if ( $key !== count($supportedPostTypes) - 1 ) {
                $addPostTypes .= " OR ";
            }
        }
        $addPostTypes .= " ) ";
    }
    return $addPostTypes;
}

function getLogsByLanguage() {
    if ( isset($GLOBALS['cfg']['logsByLanguage']) ) {
        return $GLOBALS['cfg']['logsByLanguage'];
    } else {
        showFunctionFired('--> getLogsByLanguage()');
        $table = $GLOBALS['cfg']['table'];
        $postTable = $GLOBALS['cfg']['postTable'];
        $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
        $supportedLanguages = getSupportedLanguages();
        $languageList = "";
        foreach ($supportedLanguages as $key => $supportedLanguage) {
            $lang = convertLanguageCodesForDB($supportedLanguage);
            $languageList .= " COUNT($lang) AS $lang ";
            $languageList .= $key !== count($supportedLanguages) - 1 ? ', ' : '' ;
        }

        $addPostTypes = string_addPostTypes();
        $query_logsByLanguage = "SELECT $languageList FROM $table, $postTable WHERE track_language = '$defaultLanguage' AND wp_my_dictionary.post_id = wp_posts.ID AND $addPostTypes";
        $logsByLanguage = $GLOBALS['wpdb']->get_results($query_logsByLanguage);
        $logsByLanguage = (array) $logsByLanguage[0];

        $GLOBALS['cfg']['logsByLanguage'] = $logsByLanguage;
        return $logsByLanguage;
    }
}

function showGlobalLanguagesProgress() {
    $maxSavedLogs = getLogsByLanguage()[convertLanguageCodesForDB(getDefaultLanguage())];

    $translationLanguages = getTranslationLanguages();
    $output = "";
        foreach ($translationLanguages as $languageCode) {
            $languageName = getLanguageName($languageCode);
            $output .= "<span class='md-translate__language-global'><p>$languageName</p>";
            if ($maxSavedLogs > 0) {
                $output .= createProgressBar(getLogsByLanguage()[convertLanguageCodesForDB($languageCode)],$maxSavedLogs);
            } else {
                $output .= createProgressBar(0,100);
            }
            $output .= "</span>";
        }
    echo $output;
}

/**
 * Start showPostList area
 */

function headingCells() {
    echo '<tr>
        <th scope="col">Title</th>
        <th scope="col">Progress</th>
        <th scope="col">Actions</th>
    </tr>';
}

function translateBtn($post,$isTitle) {
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());

    $titleClass = $isTitle ? 'class="row-title"' : '' ;
    $title = $isTitle ? $post->post_title : "Translate" ;

    $route = !isNullValue($post->$defaultLanguage) ? "&translate_id=".$post->post_id : "" ;
    $href = !isNullValue($post->$defaultLanguage) ? ' href="'.$GLOBALS['cfg']['current_link'].$route.'"' : '' ;
    $tag = !isNullValue($post->$defaultLanguage) ? 'a' : 'span' ;
    $ariaLabel = !isNullValue($post->$defaultLanguage) ? " aria-label='Translate $post->post_title'" : '' ;

    echo "<$tag $titleClass $href $ariaLabel>$title</$tag>";
}

function createPostListByType($postType, $allPostListData) {
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $GLOBALS['cfg']['current_link'] = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $isEmpty = true;
    foreach ($allPostListData as $post) {
        if ($post->post_type === $postType) {
            $isEmpty = false;
            ?>
                <tr id="post-<?php echo $post->post_id; ?>" class="md-translate__item" data-colname="Title">
                    <td class="md-translate__item-title">
                        <strong>
                            <?php translateBtn($post,true); ?>
                        </strong>
                        <div class="row-actions">
                            <?php
                                if ( !isNullValue($post->$defaultLanguage) ) {
                                    echo '<span class="translate">';
                                    translateBtn($post,false);
                                    echo ' | </span>';
                                }
                            ?>
                            <span class="view">
                                <a href="<?php echo $post->post_guid; ?>" target="_blank" rel="bookmark" aria-label="View “<?php echo $post->post_title; ?>”">View</a></span>
                        </div>
                    </td>
                    <td class="md-translate__item-progress">
                        <?php showSingleProgressBar($post); ?>
                    </td>
                    <td class="md-translate__item-actions">
                        <?php
                            $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
                            $submitValue = !isNullValue($post->$defaultLanguage) ? 'Re-scan' : 'Scan' ;
                        ?>
                        <form class="md-translate__item-single-scan-form" method="post">
                            <input type="hidden" name="scan_id" value="<?php echo $post->post_id; ?>">
                            <input type="submit" value="<?php echo $submitValue; ?>" class="button-secondary">
                        </form>
                    </td>
                </tr>
            <?php
        }
    }

    if ( $isEmpty ) {
        ?> <tr><td colspan="2">No texts found under this post type</td></tr> <?
    }
}

function getAllPostListData() {
    showFunctionFired('--> getAllPostListData()');
    $table = $GLOBALS['cfg']['table'];
    $postTable = $GLOBALS['cfg']['postTable'];
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $translationLanguages = getTranslationLanguages();
    $addSelectLanguage = "";
    $addWhereLanguage = "";
    $languageColumnNullFields = "";
    foreach ($translationLanguages as $key => $translationLanguage) {
        $lang = convertLanguageCodesForDB($translationLanguage);
        $addSelectLanguage .= ", count($lang) AS $lang ";
        $addWhereLanguage .= " OR $lang IS NOT NULL ";
        $languageColumnNullFields .= ", 'null' ";
    }
    $languageColumnNullFields .= ", 'null' ";

    $addPostTypes = string_addPostTypes();

    $query_getAllPostListData = "SELECT $table.post_id, $postTable.post_title AS post_title, $postTable.guid AS post_guid, post_type AS post_type, count($defaultLanguage) AS $defaultLanguage $addSelectLanguage FROM $table, $postTable WHERE ($defaultLanguage IS NOT NULL $addWhereLanguage) AND $table.post_id = $postTable.ID AND $table.track_language = '$defaultLanguage'AND $table.post_text_id IS NOT NULL  GROUP BY $table.post_id UNION SELECT $postTable.ID as post_id, $postTable.post_title AS post_title, $postTable.guid AS post_guid, post_type AS post_type $languageColumnNullFields FROM $postTable WHERE $addPostTypes AND post_title != 'Auto Draft' ORDER BY post_type ASC, post_id DESC";
    $allPostListData = $GLOBALS['wpdb']->get_results($query_getAllPostListData);

    foreach ($allPostListData as $index => $rawData) {
        foreach ($allPostListData as $key => $data) {
            if ($index !== $key && $rawData->post_title === $data->post_title && $data->$defaultLanguage === 'null') {
                unset($allPostListData[$key]);
            }
        }
    }

    return $allPostListData;
}

function createPostListTable() {
    $allPostListData = getAllPostListData();
    $supportedPostTypes = getSupportedPostTypes();
    foreach ($supportedPostTypes as $postType) {
        ?>
            <div class="col-container md-container-<?php echo $postType; ?>">
                <div class="col-left"><?php echo ucfirst($postType); ?></div>
                <div class="col-right">
                    <div class="col-wrap">
                        <table class="striped widefat">
                            <thead>
                                <?php headingCells(); ?>
                            </thead>
                            <tbody>
                                <?php createPostListByType($postType, $allPostListData); ?>
                            </tbody>
                            <tfoot>
                                <?php headingCells(); ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php
    }
}

?>

<table class="form-table md-translate__global-progress">
    <tbody>
        <tr>
            <th colspan="2">Global progress</th>
        </tr>
        <tr>
            <td class="md-translate__global">
                <?php
                    if ( areTranslationLanguages() ) {
                        showGlobalLanguagesProgress();
                    } else {
                        infoMessage(welcomeMessage());
                    }
                ?>
            </td>
        </tr>
    </tbody>
</table>
<?php if ( areTranslationLanguages() ) {
    createPostListTable();
    } ?>