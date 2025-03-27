<?php

namespace reset;

//Script used to revert the index.php to index.tmp.php if config.php file exist.
const INDEX_PATH = __DIR__ . "/../html/index.php";
const INDEX_TMP_PATH = __DIR__ . "/../html/index.tmp.php";

if(file_exists(__DIR__ . "/../config.php")){
    $file = fopen(INDEX_TMP_PATH, "r");
    $fileContent = fread($file, filesize(INDEX_TMP_PATH));
    unlink(INDEX_TMP_PATH);
    rename(INDEX_PATH, INDEX_TMP_PATH);
    file_put_contents(INDEX_PATH, $fileContent);
    fclose($file);
    unlink(__DIR__ . "/../config.php");
    unlink(__DIR__ . "/../public/sitemap.xml");
    echo "Les fichiers ont bien été switché.\n";
}
else{
    die("config.php file not exist.\n");
}