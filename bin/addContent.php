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

/**
 * Parse un fichier .env et charge les variables dans $_ENV
 *
 * @param string $filePath Chemin vers le fichier .env
 * @return void
 */
function parseEnvFile(string $filePath): void
{
    if (!file_exists($filePath)) {
        throw new Exception("Le fichier .env n'existe pas : $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);

        $value = trim($value, " \t\n\r\0\x0B\"'");

        $_ENV[$key] = $value;
    }
}

function getDetails(string $name, bool $type)
{
    if ($type) {
        $content = json_decode(getHtmlContent("https://api.themoviedb.org/3/search/tv?api_key=". $_ENV["MOVIE_BD_API_KEY"] ."&query=" . $name . "&language=fr-FR"));
    } else {
        $content = json_decode(getHtmlContent("https://api.themoviedb.org/3/search/movie?api_key=". $_ENV["MOVIE_BD_API_KEY"] ."&query=" . $name . "&language=fr-FR"));
    }

    if (!isset($content->results[0])) {
        return [
            "description" => "Aucune description disponible",
            "genre" => Category::findFirst(),
            "date" => date("Y-m-d H:i:s"),
            "title" => str_replace("+", " ", $name)
        ];
    }

    return [
        "description" => $content->results[0]->overview ?? "La description sera ajouté prochainement",
        "genre" => isset($content->results[0]->genre_ids) && count($content->results[0]->genre_ids) > 0
            ? setGender($content->results[0]->genre_ids[0]) 
            : Category::findFirst(),
        "date" => $content->results[0]->first_air_date ?? date("Y-m-d H:i:s"),
        "title" => $content->results[0]->name ?? str_replace("+", " ", $name)
    ];
}

function setGender(string $id): Category
{
    $genres=[["id"=>"10759","name"=>"Action & Adventure"],["id"=>"16","name"=>"Animation"],["id"=>"35","name"=>"Comedy"],["id"=>"80","name"=>"Crime"],["id"=>"99","name"=>"Documentary"],["id"=>"18","name"=>"Drama"],["id"=>"10751","name"=>"Family"],["id"=>"10762","name"=>"Kids"],["id"=>"9648","name"=>"Mystery"],["id"=>"10763","name"=>"News"],["id"=>"10764","name"=>"Reality"],["id"=>"10765","name"=>"Sci-Fi & Fantasy"],["id"=>"10766","name"=>"Soap"],["id"=>"10767","name"=>"Talk"],["id"=>"10768","name"=>"War & Politics"],["id"=>"37","name"=>"Western"],["id"=>"28","name"=>"Action"],["id"=>"12","name"=>"Adventure"],["id"=>"14","name"=>"Fantasy"],["id"=>"36","name"=>"History"],["id"=>"27","name"=>"Horror"],["id"=>"10402","name"=>"Music"],["id"=>"10749","name"=>"Romance"],["id"=>"878","name"=>"Science Fiction"],["id"=>"10770","name"=>"TV Movie"],["id"=>"53","name"=>"Thriller"],["id"=>"10752","name"=>"War"]];
    
    foreach ($genres as $genre) {
        if ($genre["id"] == $id) {
            $g = Category::findFirst(["title" => $genre["name"]]);
            if ($g != null) return $g;
            return Category::insertOne(
                fn (Category $category) => $category
                    ->setTitle($genre["name"])
            );
        }
    }
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
            $details = getDetails(str_replace("-", "+", $season[0]), true);
            $season = intval(str_replace("saison", "", $season[1]));
            array_push($content, [
                "name" => $details["title"],
                "type" => "serie",
                "season" => $season,
                "source" => [
                    $value["source1"],
                    $value["source2"],
                    $value["source3"],
                    $value["source4"]
                ],
                "description" => $details["description"],
                "date" => $details["date"],
                "genre" => $details["genre"]
            ]);
        }
        preg_match('/film/', $key, $result);
        if (count($result) > 0) {
            preg_match($pattern, $key, $matches);
            $details = getDetails(str_replace("-", "+", explode("/", $matches[1])[0]), false);
            array_push($content, [
                "name" => $details["title"],
                "type" => "film",
                "source" => [
                    $value["source1"],
                    $value["source2"],
                    $value["source3"],
                    $value["source4"]
                ],
                "description" => $details["description"],
                "date" => $details["date"],
                "genre" => $details["genre"]
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
    $url = "https://anime-sama.fr/catalogue/" . str_replace(" ", "-", strtolower($searchQuery));

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

function addMovie(string $title, string $image, array $source, string $description, string $date, Category $category): void
{
    try {
        $video = Video::insertOne([]);
        $movie = Movie::insertOne(
            fn (Movie $movie) => $movie
                ->setTitle($title)
                ->setDescription($description)
                ->setVideo($video)
                ->setImage($image)
                ->setReleaseDate($date)
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

function addSerie(string $title, string $image, string $description, string $date, Category $category): Serie
{
    try {
        if (Serie::findFirst(["title" => $title, "image" => $image]) !== null) {
            return Serie::findFirst(["title" => $title, "image" => $image]);
        }
        $serie = Serie::insertOne(
            fn (Serie $serie) => $serie
                ->setTitle($title)
                ->setDescription($description)
                ->setImage($image)
                ->setReleaseDate($date)
        );
        Content::insertOne(
            fn (Content $content) => $content
                ->setValue($serie)
                ->setCategory($category)
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
            "description" => $serie->getDescription()
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
    parseEnvFile(__DIR__ . "/../.env");
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
            addMovie($value["name"], $value["image"], $value["source"], $value["description"], $value["date"], $value["genre"]);
        } else if ($value["type"] == "serie") {
            print_r("start add serie " . $value["name"] . "\n");
            $serieEntity = addSerie($value["name"], $value["image"], $value["description"], $value["date"], $value["genre"]);
            addAllEpisodeBySerie($value["season"], $serieEntity, $value["source"]);
        }
    }
}
main();