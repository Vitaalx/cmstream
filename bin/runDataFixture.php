<?php

namespace runDataFixture;

require_once __DIR__ . '/../Core/Autoloader.php';
require __DIR__ . '/../config.php';

use Entity\Role;

define("KEY", [
    "u" => "user",
    "c" => "category",
    "s" => "serie",
    "e" => "episode",
    "m" => "movie",
    "C" => "comment",
    "S" => "s",
    "h" => "history",
    "w" => "watchlist",
    "r" => "role",
]);

//base function 
function generateRandomStringWhereSize(int $size): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $size; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateRandomIntWhereSize(int $size): int
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomInt = '';
    for ($i = 0; $i < $size; $i++) {
        $randomInt .= $characters[rand(0, $charactersLength - 1)];
    }
    return intval($randomInt);
}

function generateRandomDate(): string
{
    $start = strtotime("01 January 2010");
    $end = strtotime("31 December 2020");
    $timestamp = mt_rand($start, $end);
    return date("Y-m-d", $timestamp);
}

function generateRandomBool(): bool
{
    return (bool)rand(0, 1);
}

function getRandomUrlYoutube(): string
{
    $url = 'https://www.youtube.com/watch?v=';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 11; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $url . $randomString;
}

function getRandomUrlImgur(): string
{
    $url = 'https://i.imgur.com/';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 7; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $url . $randomString . '.jpg';
}

function generateIntBetween(int $min, int $max): int
{
    return rand($min, $max);
}

function createArrayYoutubeUrl(): array
{

    $arrayYoutubeUrl = [];
    for ($i = 0; $i < generateIntBetween(1, 4); $i++) {
        $arrayYoutubeUrl[] = getRandomUrlYoutube();
    }
    return $arrayYoutubeUrl;
}

// function for create random data
function createRandomUser(): array
{
    $firstname = generateRandomStringWhereSize(10);
    $lastname = generateRandomStringWhereSize(10);
    $username = generateRandomStringWhereSize(10);
    $email = generateRandomStringWhereSize(10) . '@gmail.com';
    $password = generateRandomStringWhereSize(10) . "@";

    $user = \Entity\User::insertOne([
        "firstname" => $firstname,
        "lastname" => $lastname,
        "email" => $email,
        "username" => $username,
        "password" => password_hash($password, PASSWORD_DEFAULT),
        "role_id" => 0,
    ]);

    return [
        "user_id" => $user->getId(), // "user_id" => "1
        "firstname" => $firstname,
        "lastname" => $lastname,
        "username" => $username,
        "email" => $email,
        "password" => $password,
    ];
}

function createRandomCategory(): void
{
    \Entity\Category::insertOne([
        "title" => generateRandomStringWhereSize(10),
    ]);
}

function getRandomCategory(): int
{
    $categories = \Entity\Category::findMany();
    $randomCategory = $categories[rand(0, count($categories) - 1)];
    return $randomCategory->getId();
}

function createRandomSerie(): int
{
    $serie = \Entity\Serie::insertOne([
        "description" => generateRandomStringWhereSize(100),
        "title" => generateRandomStringWhereSize(10),
        "image" => getRandomUrlImgur(),
        "category_id" => getRandomCategory(),
    ]);

    return $serie->getId();
}

function addEpisode(int $serieId, int $nbEpisode, int $nbSeason): void
{
    /** @var Video $video */
    $video = \Services\Back\VideoManagerService::createVideo(
        generateRandomStringWhereSize(10),
        generateRandomStringWhereSize(100)
    );

    $url = createArrayYoutubeUrl();

    foreach ($url as $item) {
        \Entity\Url::insertOne([
            "url" => $item,
            "video_url_id" => $video->getId(),
        ]);
    }

    \Entity\Episode::insertOne([
        "episode" => $nbEpisode,
        "season" => $nbSeason,
        "video_id" => $video->getId(),
        "serie_id" => $serieId,
    ]);
}

function createRandomMovie(): void
{
    /** @var Video $video */
    $video = \Services\Back\VideoManagerService::createVideo(
        generateRandomStringWhereSize(10),
        generateRandomStringWhereSize(100)
    );

    $url = createArrayYoutubeUrl();

    foreach ($url as $item) {
        \Entity\Url::insertOne([
            "url" => $item,
            "video_url_id" => $video->getId(),
        ]);
    }

    \Entity\Movie::insertOne([
        "video_id" => $video->getId(),
        "image" => getRandomUrlImgur(),
        "category_id" => getRandomCategory(),
    ]);
}

function createRandomComment(int $userId, int $videoId): void
{
    \Entity\Comment::insertOne([
        "content" => generateRandomStringWhereSize(100),
        "user_id" => $userId,
        "video_id" => $videoId,
        "status" => generateIntBetween(0, 1),
    ]);
}

function createRandomStar(int $userId, int $videoId): void
{
    \Entity\Star::insertOne([
        "user_id" => $userId,
        "video_id" => $videoId,
        "note" => generateIntBetween(0, 5),
    ]);
}

function createHistory(int $userId, int $videoId): void
{
    \Entity\History::insertOne([
        "user_id" => $userId,
        "video_id" => $videoId,
    ]);
}

function addRandomMovieInWatchlist(int $userId, int $videoId): void
{
    \Entity\Watchlist::insertOne([
        "user_id" => $userId,
        "movie_id" => $videoId,
        "serie_id" => 0,
    ]);
}

function addRandomSerieInWatchlist(int $userId, int $videoId): void
{
    \Entity\Watchlist::insertOne([
        "user_id" => $userId,
        "movie_id" => 0,
        "serie_id" => $videoId,
    ]);
}

function createRandomRole(): Role
{
    $name = generateRandomStringWhereSize(10);
    $role = \Entity\Role::insertOne([
        "name" => $name,
    ]);
    return $role;
}

function setRandomRoleToUser(int $userId): void
{
    $roles = \Entity\Role::findMany();
    $randomRole = $roles[rand(0, count($roles) - 1)];
    $user = \Entity\User::findFirst(["id" => $userId]);
    $user->setRole($randomRole);
}

function attributeRandomPermissionToRole(Role $role): void
{
    $perm = [
        \Services\Permissions::AccessDashboard,
        \Services\Permissions::RoleEditor,
        \Services\Permissions::UserEditor,
        \Services\Permissions::ConfigEditor,
        \Services\Permissions::StatsViewer,
        \Services\Permissions::ContentsManager,
        \Services\Permissions::CommentsManager,
    ];

    for ($i = 0; $i < generateIntBetween(1, 7); $i++) {
        $randomPerm = $perm[rand(0, count($perm) - 1)];
        $role->addPermission($randomPerm);
    }
}

function getRandomVideo(): int
{
    $videos = \Entity\Video::findMany();
    $randomVideo = $videos[rand(0, count($videos) - 1)];
    return $randomVideo->getId();
}

function getArgv(): array
{
    $argv = $_SERVER['argv'];
    foreach ($argv as $key => $value) {
        if (strpos($value, "runDataFixture") !== false) {
            unset($argv[$key]);
        }
    }
    $result = [];
    foreach ($argv as $arg) {
        $arg = explode("=", $arg);
        if (count($arg) === 2) {
            $key = $arg[0];
            $value = $arg[1];
            if (array_key_exists($key, KEY)) {
                $result[KEY[$key]] = intval($value);
            }
        }
    }
    return $result;
}

function main(): void
{
    $argument = [
        "user" => 10,
        "category" => 10,
        "serie" => 10,
        "episode" => 30,
        "movie" => 100,
        "comment" => 200,
        "start" => 1000,
        "history" => 300,
        "watchlist" => 10,
        "role" => 10,
    ];
    if (isset($_SERVER['argv'][1])) {
        if ($_SERVER['argv'][1] === "help") {
            print_r("argument list :\n");
            print_r(KEY);
            return;
        } else if ($_SERVER['argv'][1] === "default") {
            print_r("default paramter :\n");
            print_r($argument);
            return;
        }
        $argument = array_merge($argument, getArgv());
    }

    for ($i = 0; $i < $argument["category"]; $i++) {
        createRandomCategory();
    }

    for ($i = 0; $i < $argument["role"]; $i++) {
        $role = createRandomRole();
        attributeRandomPermissionToRole($role);
    }

    for ($i = 0; $i < $argument["serie"]; $i++) {
        $serieId = createRandomSerie();
        for ($j = 0; $j < $argument["episode"]; $j++) {
            addEpisode($serieId, $j, $j > $argument["episode"] / 2 ? 2 : 1);
        }
    }

    for ($i = 0; $i < $argument["movie"]; $i++) {
        createRandomMovie();
    }

    for ($i = 0; $i < $argument["user"]; $i++) {
        $user = createRandomUser();
        print_r($user);

        for ($j = 0; $j < $argument["comment"]; $j++) {
            createRandomComment($user["user_id"], getRandomVideo());
        }

        for ($j = 0; $j < $argument["start"]; $j++) {
            createRandomStar($user["user_id"], getRandomVideo());
        }

        for ($j = 0; $j < $argument["history"]; $j++) {
            createHistory($user["user_id"], getRandomVideo());
        }

        for ($j = 0; $j < $argument["watchlist"]; $j++) {
            if (rand(0, 1) === 0) {
                addRandomMovieInWatchlist($user["user_id"], getRandomVideo());
            } else {
                addRandomSerieInWatchlist($user["user_id"], getRandomVideo());
            }
        }
    }
    print_r("Process finished !");
}
main();
