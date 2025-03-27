<?php

namespace makeSitemap;

require_once __DIR__ . "/../Core/AutoLoader.php";
require_once __DIR__ . "/../config.php";

use Core\File;
use Entity\Episode;
use Entity\Movie;
use Entity\Serie;

/**
 * @param string $view
 * @param string $template
 * @param array $params
 * @param string $outputFile
 * @return void
 */
function renderToFile(string $view, string $template, array $params, string $outputFile): void
{
    $template = __DIR__ . "/../Templates/" . $template . ".php";
    if (file_exists($template) === false) {
        throw new \Exception("Template '" . $template . "' not exist.");
    }

    $view = __DIR__ . "/../Views/" . $view . ".php";
    if (file_exists($view) === false) {
        throw new \Exception("View '" . $view . "' not exist.");
    }

    extract($params);

    ob_start();
    include $template;
    $output = ob_get_clean();

    file_put_contents($outputFile, $output);
}

/**
 * main
 * @return void
 */
function main() {
    $pages = new File(__DIR__ . "/../public/cuteVue/pages.json");

    $vars = [
        "config" => CONFIG,
        "pages" => json_decode($pages->read(), true),
        "movies" => Movie::findIterator([]),
        "episodes" => Episode::findIterator([]),
    ];

    try {
        renderToFile("sitemap", "none", $vars, __DIR__ . "/../public/sitemap.xml");
        echo "Sitemap généré avec succès dans public/sitemap.html\n";
    } catch (\Exception $e) {
        echo "Erreur : " . $e->getMessage() . "\n";
    }
}
main();