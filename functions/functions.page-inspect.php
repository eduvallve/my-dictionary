<?php

function findShortcodes($content) {
    // Extract only shordcodesin an Array
    $post_innerShort = array_map(function($text) {
        $innerShort = explode("]", $text);
        $returnable = count($innerShort) === 2 && $innerShort[0] !== '' && !strpos($innerShort[0],'=') ? $innerShort[0] : '' ;
        return $returnable;
    }, explode("[", $content));

    $post_innerShort = array_values(
        array_unique(
            array_filter(
                $post_innerShort, function($value) {
                    return $value !== 'noun' && $value !== 'edu-years-experience' && $value !== '';
                }
            )
        )
    );
    // Return found shortcodes
    return $post_innerShort;
}

function explodeShortcode($content) {
    $shortcodes = findShortcodes($content);
    $newContent = '';
    foreach ($shortcodes as $shortcode) {
        // Replace each shortcode with its rendered content
        $newContent .= do_shortcode("[$shortcode]");
    }
    // Return expanded shortcodes within the page content
    return $content.$newContent;
}

function getDataFromPost($post_id) {
    if ( isset($GLOBALS['cfg']['datafromPost']) ) {
        return $GLOBALS['cfg']['datafromPost'];
    } else {
        showFunctionFired('--> getDataFromPost($post_id)');
        $postTable = $GLOBALS['cfg']['postTable'];
        $getDataFromPost = "SELECT post_content FROM $postTable WHERE ID = $post_id";
        $dataFromPost = $GLOBALS['wpdb']->get_results($getDataFromPost);
        $post_content = implode(array_column($dataFromPost, 'post_content'));

        $post_content = explodeShortcode($post_content);

        $GLOBALS['cfg']['datafromPost'] = $post_content;
        return $post_content;
    }
}

function cleanHtmlTags($post_content) {
    // Extract only plain text areas from page content & store them in an Array
    $post_innerText = array_map(function($text) {
        $innerText = explode(">", $text);
        return count($innerText) === 2 && $innerText[1] !== '' ? $innerText[1] : '' ;
    }, explode("<", $post_content));

    // Remove all blank/empty cells from the Array + exceptions
    foreach ($post_innerText as $key => $cell) {
        if (
            trim($cell) === '' ||
            trim($cell) === '-' ||
            trim($cell) === '_' ||
            strpos(trim($cell), '/*! elementor') !== false ||
            (strpos(trim($cell), '[') === 0 && strpos(trim($cell), ']') === intval(strlen(trim($cell))) - 1)
            ) {
            unset($post_innerText[$key]);
        }
    }

    // Reset the indexs of the Array. Ex: [2,10,12,16] ==> [0,1,2,3] & return
    $innerText = array_unique(array_values($post_innerText));
    $GLOBALS['cfg']['cleanHtmlTags'] = $innerText;
    return $innerText;
}

function removeDuplicatedRows($duplicateRows) {
    showFunctionFired('<-- removeDuplicatedRows($duplicateRows)');
    $table = $GLOBALS['cfg']['table'];

    $condition = '';
    foreach ( $duplicateRows as $i => $row ) {
        $condition .= " id = $row ";
        if ($i < count($duplicateRows) - 1) {
            $condition .= " OR ";
        }
    }

    $query_removeDuplicatedRows = "DELETE FROM $table WHERE $condition";
    echo $query_removeDuplicatedRows;
    $removeDuplicatedRows = $GLOBALS['wpdb']->query($GLOBALS['wpdb']-> prepare($query_removeDuplicatedRows));
}

function findDuplicateRows($post_id) {
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $savedPostTexts = getSavedPostTexts($post_id);

    // Remove all duplicated cells from the Array
    $previousText = "";
    $previousTextId = "";
    $duplicateRows = [];
    foreach ($savedPostTexts as $key => $cell ) {
        if ( $previousText === $cell->$defaultLanguage ) {
            unset($savedPostTexts[$key]);
            array_push($duplicateRows, $cell->id);
        } else {
            $previousText = $cell->$defaultLanguage;
            $previousTextId = $cell->post_text_id;
        }
    }

    // Remove all duplicated rows in the DB table
    if (count($duplicateRows) > 0) {
        removeDuplicatedRows($duplicateRows);
    }
}

function querySavedPostTexts($post_id, $isPostTranslatePage = false) {
    showFunctionFired('--> getSavedPostTexts($post_id)');
    $table = $GLOBALS['cfg']['table'];

    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $translationLanguages = array_map(function($lang) {
        return convertLanguageCodesForDB($lang);
    }, getTranslationLanguages());

    $requestedLanguages = $isPostTranslatePage ? ", ".implode(", ",$translationLanguages) : '' ;

    $query_getSavedPostTexts = "SELECT id, post_text_id, $defaultLanguage $requestedLanguages FROM $table WHERE post_id = $post_id AND track_language = '$defaultLanguage' ORDER BY $defaultLanguage ASC, id ASC";
    $savedPostTexts = $GLOBALS['wpdb']->get_results($query_getSavedPostTexts);


    usort($savedPostTexts, function($a, $b) {
        return $a->post_text_id - $b->post_text_id;
    });

    /** Return right text fragments */
    $GLOBALS['cfg']['savedPostTexts'] = $savedPostTexts;
    return $savedPostTexts;
}

function getSavedPostTexts($post_id, $isPostTranslatePage = false) {
    if ( isset($GLOBALS['cfg']['savedPostTexts']) ) {
        return $GLOBALS['cfg']['savedPostTexts'];
    } else {
        return querySavedPostTexts($post_id, $isPostTranslatePage);
    }
}

function savePostTexts($post_id, $post_diffTexts) {
    showFunctionFired('--> savePostTexts($post_id, $post_diffTexts)');
    $table = $GLOBALS['cfg']['table'];
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $query_savePostTexts = "INSERT INTO $table (post_id, track_language, post_text_id, $defaultLanguage) VALUES ";
    $acum = 0;
    foreach ($post_diffTexts as $index => $post_text) {
        $post_text = trim(str_replace('"', '\"', $post_text));
        $query_savePostTexts .= "($post_id,\"$defaultLanguage\",$index,\"$post_text\")";
        $acum !== count($post_diffTexts) - 1 ? $query_savePostTexts .= ', ' : '' ;
        $acum = $acum + 1;
    }
    $savePostTexts = $GLOBALS['wpdb']->query($GLOBALS['wpdb']-> prepare($query_savePostTexts));
}

function removeNullRows() {
    $table = $GLOBALS['cfg']['table'];
    $query_removeNullRows = "DELETE FROM $table WHERE post_text_id IS NULL";
    $removeNullRows = $GLOBALS['wpdb']->query($GLOBALS['wpdb']-> prepare($query_removeNullRows));
}

function updatePostTextIDs($post_id) {
    showFunctionFired('<-> updatePostTextIDs($post_id)');
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $table = $GLOBALS['cfg']['table'];

    /**
     * Clean all indexes for the current post
     */
    $query_cleanIndexValues = "UPDATE $table SET post_text_id = NULL WHERE post_id = $post_id AND track_language = '$defaultLanguage'; ";
    $cleanIndexValues = $GLOBALS['wpdb']->query($GLOBALS['wpdb']-> prepare($query_cleanIndexValues));

    /**
     * Get data from post
     */
    $post_innerText = cleanHtmlTags(getDataFromPost($post_id));
    $innerTexts = array_map(function($text) {
        return convertAsciiValues($text);
    }, $post_innerText);

    /**
     * Get already saved data from DB
     */
    $post_savedTexts = getSavedPostTexts($post_id);
    $savedTexts = array_map(function($text) {
        return convertAsciiValues($text);
    }, array_column($post_savedTexts, $defaultLanguage));
    $savedTextsIds = array_column($post_savedTexts, 'id');

    /**
     * Compare them and update post_text_ids accordingly
     */
    foreach ( $innerTexts as $i => $innerText ) {
        foreach ($savedTexts as $j => $savedText ) {
            if ( trim($innerText) === trim($savedText) ) {
                $query_updateIndexValues = " UPDATE $table SET post_text_id = $i WHERE id = $savedTextsIds[$j]; ";
                $updateIndexValues = $GLOBALS['wpdb']->query($GLOBALS['wpdb']-> prepare($query_updateIndexValues));
            }
        }
    }
}

function fillDictionaryTableByPost($post_id, $isPostTranslatePage = false) {
    /**
     * Get:
     * - All texts from post_content
     * - All saved texts in dictionary regarding that same post
     * Compare them and extract new still-not-saved texts in dictionary DB
     */
    $defaultLanguage = convertLanguageCodesForDB(getDefaultLanguage());
    $post_innerText = cleanHtmlTags(getDataFromPost($post_id));

    $post_savedTexts = getSavedPostTexts($post_id, $isPostTranslatePage);
    $post_savedTextLang = array_column($post_savedTexts, $defaultLanguage);
    $post_diffTexts = array_diff($post_innerText, $post_savedTextLang);

    if (count($post_diffTexts) > 0) {
        foreach ($post_savedTextLang as $savedSentence) {
            foreach ($post_diffTexts as $d => $diffSentence) {
                if (trim($savedSentence) === trim($diffSentence)) {
                    unset($post_diffTexts[$d]);
                }
            }
        }
        savePostTexts($post_id, $post_diffTexts);
    }

    return count($post_diffTexts) > 0;
}

function fillDictionaryTable() {
    $post_id = get_the_ID();
    fillDictionaryTableByPost($post_id);
}

?>