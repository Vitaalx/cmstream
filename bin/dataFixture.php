<?php

namespace runDataFixture;

require_once __DIR__ . '/../Core/AutoLoader.php';
require __DIR__ . '/../config.php';

define("KEY", [
    "u" => "user",
    "c" => "category",
    "s" => "serie",
    "e" => "episode",
    "m" => "movie",
    "C" => "comment",
    "S" => "star",
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

function generateRandomUrlYoutube(): string
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

function getRandomUrlImg(): string
{
    return 'https://picsum.photos/' . random_int(500, 1800) . '/' . random_int(500, 1800);
}

function generateIntBetween(int $min, int $max): int
{
    return rand($min, $max);
}

function getRandomCategory(): \Entity\Category
{
    $categories = \Entity\Category::findFirst([], ["ORDER_BY" => ["random()"]]);
    return $categories;
}

function getRandomVideo(): int
{
    $video = \Entity\Video::findFirst([], ["ORDER_BY" => ["random()"]]);
    return $video->getId();
}

function getRandomMovie(): int
{
    $video = \Entity\Movie::findFirst([], ["ORDER_BY" => ["random()"]]);
    return $video->getId();
}

function getRandomSerie(): int
{
    $video = \Entity\Serie::findFirst([], ["ORDER_BY" => ["random()"]]);
    return $video->getId();
}

function getRandomContent()
{
    $video = \Entity\Content::findFirst([], ["ORDER_BY" => ["random()"]]);
    return $video;
}

function createArrayYoutubeUrl(): array
{

    $arrayYoutubeUrl = [];
    for ($i = 0; $i < generateIntBetween(1, 4); $i++) {
        $arrayYoutubeUrl[] = generateRandomUrlYoutube();
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
        "password" => password_hash($password, PASSWORD_DEFAULT)
    ]);

    return [
        "user_id" => $user->getId(), // "user_id" => "1
        "firstname" => $firstname,
        "lastname" => $lastname,
        "username" => $username,
        "email" => $email,
        "password" => $password
    ];
}

function createRandomCategory(): void
{
    \Entity\Category::insertOne([
        "title" => generateRandomStringWhereSize(10),
    ]);
}

function createRandomSerie(): int
{
    $serie = \Entity\Serie::insertOne([
        "description" => generateRandomStringWhereSize(100),
        "title" => generateRandomStringWhereSize(10),
        "image" => getRandomUrlImg(),
        "release_date" => generateRandomDate(),
    ]);

    \Entity\Content::insertOne(
        fn (\Entity\Content $content) => $content->setValue($serie)->setCategory(getRandomCategory())
    );

    return $serie->getId();
}

function addEpisode(int $serieId, int $nbEpisode, int $nbSeason): void
{

    $video = \Entity\Video::insertOne([]);
    $url = createArrayYoutubeUrl();

    foreach ($url as $item) {
        \Entity\Url::insertOne([
            "value" => $item,
            "video" => $video,
        ]);
    }

    \Entity\Episode::insertOne([
        "episode" => $nbEpisode,
        "season" => $nbSeason,
        "video" => $video,
        "title" => generateRandomStringWhereSize(10),
        "serie_id" => $serieId,
    ]);
}

function createRandomMovie(): void
{
    $video = \Entity\Video::insertOne([]);

    $url = createArrayYoutubeUrl();

    foreach ($url as $item) {
        \Entity\Url::insertOne([
            "value" => $item,
            "video" => $video,
        ]);
    }

    $movie = \Entity\Movie::insertOne([
        "title" => generateRandomStringWhereSize(10),
        "video" => $video,
        "image" => getRandomUrlImg(),
        "release_date" => generateRandomDate(),
    ]);

    \Entity\Content::insertOne(
        fn (\Entity\Content $content) => $content->setValue($movie)->setCategory(getRandomCategory())
    );
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

function createRandomVote(int $userId): void
{
    if(generateRandomBool()){
        \Entity\Vote::insertOne([
            "user_id" => $userId,
            "content" => getRandomContent(),
            "value" => -1,
        ]);
    }
    else {
        \Entity\Vote::insertOne([
            "user_id" => $userId,
            "content" => getRandomContent(),
            "value" => 1,
        ]);
    }
    
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

function createRandomRole(): \Entity\Role
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

function attributeRandomPermissionToRole(\Entity\Role $role): void
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
        "comment" => 50,
        "vote" => 200,
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

        for ($j = 0; $j < $argument["vote"]; $j++) {
            createRandomVote($user["user_id"]);
        }

        // for ($j = 0; $j < $argument["history"]; $j++) {
        //     createHistory($user["user_id"], getRandomVideo());
        // }

        // for ($j = 0; $j < $argument["watchlist"]; $j++) {
        //     if (rand(0, 1) === 0) {
        //         addRandomMovieInWatchlist($user["user_id"], getRandomVideo());
        //     } else {
        //         addRandomSerieInWatchlist($user["user_id"], getRandomVideo());
        //     }
        // }
    }
    print_r("Process finished !");
}
main();
