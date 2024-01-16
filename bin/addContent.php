<?php

namespace addContent;

//error_reporting(E_ALL ^ E_WARNING);

require __DIR__ . '/../Core/AutoLoader.php';
require __DIR__ . '/../config.php';

use Entity\Video;
use Entity\Movie;
use Entity\Content;
use Entity\Category;
use Entity\Serie;
use Services\Back\VideoManagerService;
use Entity\Episode;

function JsonToArray(string $path): array
{
    if (!file_exists($path)) throw new \Exception("File not found" + $path);
    return json_decode(file_get_contents($path), true);
}

function getNameContent(array $data)
{
    $content = array();
    $pattern = '/catalogue\/(.*)/';
    foreach ($data as $key => $value) {
        preg_match('/saison/', $key, $result);
        if (count($result) > 0) {
            preg_match($pattern, $key, $matches);
            $season = explode("/", $matches[1]);
            $title = str_replace("-", " ", $season[0]);
            $season = intval(str_replace("saison", "", $season[1]));
            array_push($content, [
                "name" => $title,
                "type" => "serie",
                "season" => $season,
                "source" => [
                    $value["source1"],
                    $value["source2"],
                    $value["source3"],
                    $value["source4"]
                ]
            ]);
        }
        preg_match('/film/', $key, $result);
        if (count($result) > 0) {
            preg_match($pattern, $key, $matches);
            $title = str_replace("-", " ", explode("/", $matches[1])[0]);
            array_push($content, [
                "name" => $title,
                "type" => "film",
                "source" => [
                    $value["source1"],
                    $value["source2"],
                    $value["source3"],
                    $value["source4"]
                ]
            ]);
        }
    }
    return $content;
}

function listOAV(array $data)
{
    $content = array();
    $pattern = '/catalogue\/(.*)/';
    foreach ($data as $key => $value) {
        preg_match('/oav/', $key, $result);
        if (count($result) > 0) {
            preg_match($pattern, $key, $matches);
            $title = str_replace("-", " ", explode("/", $matches[1])[0]);
            array_push($content, [
                "name" => $title,
                "type" => "oav",
                "source" => [
                    $value["source1"],
                    $value["source2"],
                    $value["source3"],
                    $value["source4"]
                ]
            ]);
        }
    }
    return $content;
}

function getImageFromContent($searchQuery)
{
    $url = "https://anime-sama.fr/catalogue/" . str_replace(" ", "-", $searchQuery);

    $maxRetries = 10;
    $retryCount = 0;

    do {
        $html = getHtmlContent($url);

        if ($html !== false) {
            preg_match('/<img.*?id="imgOeuvre".*?src="(.*?)"/', $html, $matches);

            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        $retryCount++;
    } while ($retryCount < $maxRetries);

    // Si toutes les tentatives ont échoué
    return null;
}

function getHtmlContent($url)
{
    $options = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
        ),
    );

    $context = stream_context_create($options);
    $htmlContent = file_get_contents($url, false, $context);

    return $htmlContent;
}

function addMovie(string $title, string $image, array $source): void
{
    try {
        $video = Video::insertOne([]);
        $movie = Movie::insertOne(
            fn (Movie $movie) => $movie
                ->setTitle($title)
                ->setDescription('La description sera ajouté prochainement')
                ->setVideo($video)
                ->setImage($image)
                ->setReleaseDate(date("Y-m-d H:i:s"))
        );
        Content::insertOne(
            fn (Content $content) => $content
                ->setValue($movie)
                ->setCategory(Category::findFirst())
        );
        for ($i = 0; $i < count($source); $i++) {
            foreach ($source[$i] as $src) {
                VideoManagerService::createUrlWhereVideo(
                    $video->getId(),
                    $src
                );
            }
        }
    } catch (\Exception $e) {
        die(print_r('error: ' . $e));
    }
}

function addSerie(string $title, string $image): Serie
{
    try {
        if (Serie::findFirst(["title" => $title, "image" => $image]) !== null) {
            return Serie::findFirst(["title" => $title, "image" => $image]);
        }
        $serie = Serie::insertOne(
            fn (Serie $serie) => $serie
                ->setTitle($title)
                ->setDescription('La description sera ajouté prochainement')
                ->setImage($image)
                ->setReleaseDate(date("Y-m-d H:i:s"))
        );
        Content::insertOne(
            fn (Content $content) => $content
                ->setValue($serie)
                ->setCategory(Category::findFirst())
        );

        return $serie;
    } catch (\Exception $e) {
        die(print_r('eroor: ' . $e));
    }
}

function addEpisodeBySerie(int $season, Serie $serie, array $source, int $ep): void
{
    try {
        $video = Video::insertOne([]);

        Episode::insertOne([
            "episode"     => $ep,
            "season"      => $season,
            "video_id"    => $video->getId(),
            "serie_id"    => $serie->getId(),
            "title"       => 'Episode' . $ep . " - " . $serie->getTitle(),
            "description" => "La description sera ajouté prochainement"
        ]);
        foreach ($source as $src) {
            if ($src == null) continue;
            VideoManagerService::createUrlWhereVideo(
                $video->getId(),
                $src
            );
        }
    } catch (\Exception $e) {
        die(print_r('eroor: ' . $e));
    }
}

function addAllEpisodeBySerie(int $season, Serie $serie, array $source): void
{
    $content = transformTable($source);
    for ($i = 0; $i < count($content); $i++) {
        addEpisodeBySerie($season, $serie, $content[$i], $i + 1);
    }
}

function transformTable(array $inputTable)
{
    $outputTable = [];
    for ($i = 0; $i < count($inputTable[0]); $i++) {
        $outputTable[$i] = [];
        for ($j = 0; $j < count($inputTable); $j++) {
            $outputTable[$i][$j] = "";
            $outputTable[$i][$j] = $inputTable[$j][$i];
        }
    }
    return $outputTable;
}

function main()
{
    $content = getNameContent(JsonToArray("/var/www/bin/src/content.json"));
    for ($i = 0; $i < count($content); $i++) {
        print_r("start get Image from content " . $content[$i]["name"] . "\n");
        $content[$i]["image"] = getImageFromContent($content[$i]["name"]);
        if ($content[$i]["image"] == null) {
            print_r("image not found\n");
            $content[$i]["image"] = "https://cdn.pixabay.com/photo/2017/04/09/12/45/error-2215702_1280.png";
        }
    }
    // TEST AVANT RUN
    var_dump($content);
    //stop if false
    //sleep(30);

    foreach ($content as $key => $value) {
        if ($value["type"] == "film") {
            print_r("start add movie " . $value["name"] . "\n");
            addMovie($value["name"], $value["image"], $value["source"]);
        } else if ($value["type"] == "serie") {
            print_r("start add serie " . $value["name"] . "\n");
            $serieEntity = addSerie($value["name"], $value["image"]);
            addAllEpisodeBySerie($value["season"], $serieEntity, $value["source"]);
        }
    }
}
main();
