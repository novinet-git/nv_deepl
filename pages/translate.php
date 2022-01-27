<?php

rex_response::cleanOutputBuffers();
$sql = rex_sql::factory();

// add page param to all adminer urls
ob_start(function ($output) {
    return preg_replace('#(?<==(?:"|\'))index\.php\?(?=username=&amp;db=|file=[^&]*&amp;version=)#', 'index.php?page=nv_deepl&amp;', $output);
});

$iArticleId = rex_get('article_id', 'int');
$iOriginLangId = rex_get('clang_id', 'int');
$iTargetLangId = rex_get('target_clang_id', 'int');
$target_lang_code = rex_clang::get($iTargetLangId)->getCode();
$article = rex_article::get($iArticleId);

$query = "SELECT attributes FROM rex_template WHERE id=:id LIMIT 1";
$sql->setQuery($query, ["id" => $article->getTemplateId()]);
$attributes = $sql->getValue("attributes");
$attributes = json_decode($attributes, true);

function removeElementsByTagName($tagName, $document) {
  $nodeList = $document->getElementsByTagName($tagName);
  for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0; ) {
    $node = $nodeList->item($nodeIdx);
    $node->parentNode->removeChild($node);
  }
}

$query = "SELECT name from rex_module WHERE id=:id LIMIT 1";
$sql->prepareQuery($query);



rex::setProperty('redaxo', false);
$slices = rex_article_slice::getSlicesForArticle($iArticleId);
$clean_slices_texts = [];
$translated_slices = [];
$i = 0;
foreach($slices as $slice) {
    if(!$slice->isOnline()) continue;
    $id = $slice->getModuleId();
    $sql->execute(["id" => $id]);
    $doc = new DOMDocument();
    $doc->loadHTML($slice->getSlice());
    removeElementsByTagName('script', $doc);
    removeElementsByTagName('style', $doc);
    removeElementsByTagName('link', $doc);
    $text = utf8_decode($doc->saveHTML($doc->documentElement));
    $text = sprogdown($text, $iOriginLangId);
    $text = strip_tags($text);
    $text = str_replace(array("\n", "\t", "\r"), '', $text);
    $text = trim($text);
   
    if($text) {
        $ctype = $attributes["ctype"][$slice->getCtype()] ? $attributes["ctype"][$slice->getCtype()] : false;
        $clean_slices_texts[$i] = ["ctype" => $ctype, "text" => $text, "name" => $sql->getValue("name")];
        $oTranslated = json_decode(nvDeepl::translate($text, $target_lang_code), true);
        $translated_slices[$i] = $oTranslated["translations"][0]["text"];
        //$translated_slices[$i] = $text;
        $i++;
    }
}

rex::setProperty('redaxo', true);

$usage = nvDeepl::get_usage();
$usage = json_decode($usage, true);
$limit = intval($usage["character_limit"]);
$current_count = intval($usage["character_count"]);
$remains = $limit - $current_count;
?>


<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="/theme/public/assets/frontend/addons/bootstrap/css/bootstrap.css" rel="stylesheet">
        <title>Übersetzung - API</title>
        <link rel="apple-touch-icon" sizes="180x180" href="/assets/addons/be_style/plugins/redaxo/icons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/assets/addons/be_style/plugins/redaxo/icons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/assets/addons/be_style/plugins/redaxo/icons/favicon-16x16.png">
        <link rel="manifest" href="/assets/addons/be_style/plugins/redaxo/icons/site.webmanifest">
        <link rel="mask-icon" href="/assets/addons/be_style/plugins/redaxo/icons/safari-pinned-tab.svg" color="#4d99d3">
        <meta name="msapplication-TileColor" content="#2d89ef">
        </head>
    <body>

        <div class="container-fluid mt-5">
            <div class="row justify-content-between align-items-start mb-5">
                <div class="col-md-6 mb-4">
                    <h1>Übersetzungs - API</h1>
                </div>
                <div class="col-md-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th scope="row">Zeichen Limit</th>
                                <td><?=$limit?></td>
                            </tr>
                            <tr>
                                <th scope="row">Zeichen verbraucht</th>
                                <td><?=$current_count?></td>
                            </tr>
                            <tr>
                                <th scope="row">Zeichen verbleibend</th>
                                <td><?=$remains?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Modul</th>
                                <th scope="col"><?=rex_clang::get($iOriginLangId)->getName()?></th>
                                <th scope="col"><?=rex_clang::get($iTargetLangId)->getName()?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            for($i = 0; $i < count($clean_slices_texts); $i++) {
                                $slice = $clean_slices_texts[$i];
                                $text = $slice["text"];
                                $other_text = $translated_slices[$i];
                                $ctype = $slice["ctype"];
                                $name = $ctype ? $slice["name"] . "<br>(" . $ctype . ")" : $slice["name"];
                                echo <<<HTML
                                    <tr>
                                        <th class="col-2" scope="row">$name</th>
                                        <td class="col-5">$text</td>
                                        <td class="col-5"><textarea rows="5" style="background-color: transparent;border: none;min-width: 100%;resize:none;min-height:100%">$other_text</textarea></td>
                                    </tr>
                                HTML;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>

<?php // make sure the output buffer callback is called
while (ob_get_level()) {
    ob_end_flush();
}
exit;
