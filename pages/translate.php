<?php

rex_response::cleanOutputBuffers();

// add page param to all adminer urls
ob_start(function ($output) {
    return preg_replace('#(?<==(?:"|\'))index\.php\?(?=username=&amp;db=|file=[^&]*&amp;version=)#', 'index.php?page=nv_deepl&amp;', $output);
});

$iArticleId = rex_get('article_id', 'int');
$iCtype = rex_get('ctype', 'int');
$iOriginLangId = rex_get('clang_id', 'int');
$iTargetLangId = rex_get('target_clang_id', 'int');


$iCtype = "-1";

?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>abc</title>
</head>

<body>




    <div class="container-fluid mt-5">
        <div class="row">

            <div class="col-6">
                <?php echo rex_clang::get($iOriginLangId)->getName(); ?>
                <hr>


                <?php
                rex::setProperty('redaxo', false);
                $oContent = new rex_article_content($iArticleId, $iOriginLangId);

                $sContent = $oContent->getArticle($iCtype);

                $sContent = strip_tags($sContent);

                echo $sContent;

                rex::setProperty('redaxo', true);
                /*
$oDbQ = rex_sql::factory();
$sQuery = "SELECT s.* FROM " . rex::getTablePrefix() . "article_slice AS s LEFT JOIN " . rex::getTablePrefix() . "article AS a ON s.article_id = a.id WHERE (a.id = '$iArticleId' OR a.path LIKE '|$iArticleId|%') && a.clang_id = '" . $iOriginLangId . "'  && s.clang_id = '" . $iOriginLangId . "' ORDER BY s.priority ASC";
$oDbQ->setQuery($sQuery);
foreach ($oDbQ as $oRow) {

    $oSlice = new rex_article_slice($oRow->getValue("id"));
    dump($oSlice);

}*/
                ?>

            </div>

            <div class="col-6">
                <?php echo rex_clang::get($iTargetLangId)->getName(); ?>
                <hr>

                <? $oTranslated = json_decode(nvDeepl::translate($sContent, rex_clang::get($iTargetLangId)->getCode()), true);
                echo $oTranslated["translations"][0]["text"]; ?>




            </div>

        </div>
    </div>












    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>


<?php // make sure the output buffer callback is called
while (ob_get_level()) {
    ob_end_flush();
}


exit;
