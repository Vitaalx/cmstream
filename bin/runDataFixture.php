<?php

namespace runDataFixture;

require_once __DIR__ . '/../Core/Autoloader.php';
require __DIR__ . '/../config.php';

use Entity\Role;


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
        createArrayYoutubeUrl(),
        generateRandomStringWhereSize(10),
        generateRandomStringWhereSize(100)
    );

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
        createArrayYoutubeUrl(),
        generateRandomStringWhereSize(10),
        generateRandomStringWhereSize(100)
    );

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

define("NB_USER", 10);
define("NB_CATEGORY", 10);
define("NB_SERIE", 10);
define("NB_EPISODE", 30);
define("NB_MOVIE", 100);
define("NB_COMMENT", 200);
define("NB_STAR", 1000);
define("NB_HISTORY", 300);
define("NB_WATCHLIST", 10);
define("NB_ROLE", 10);

function main(): void
{
    //genrerate Random Catergory
    for ($i = 0; $i < NB_CATEGORY; $i++) {
        createRandomCategory();
    }

    //generate Random Movie
    for ($i = 0; $i < NB_MOVIE; $i++) {
        createRandomMovie();
    }

    //generate Random Role
    for ($i = 0; $i < NB_ROLE; $i++) {
        $role = createRandomRole();
        attributeRandomPermissionToRole($role);
    }

    //generate Random Serie
    for ($i = 0; $i < NB_SERIE; $i++) {
        $serieId = createRandomSerie();
        //generate Random Episode
        for ($j = 0; $j < NB_EPISODE; $j++) {
            addEpisode($serieId, $j, $j > NB_EPISODE / 2 ? 2 : 1);
        }
    }

    //generate Random User
    for ($i = 0; $i < NB_USER; $i++) {
        $user = createRandomUser();
        print_r($user);
        //generate Random Comment
        for ($j = 0; $j < NB_COMMENT; $j++) {
            createRandomComment($user["user_id"], getRandomVideo());
        }
        //generate Random Star
        for ($j = 0; $j < NB_STAR; $j++) {
            createRandomStar($user["user_id"], getRandomVideo());
        }
        //generate Random History
        for ($j = 0; $j < NB_HISTORY; $j++) {
            createHistory($user["user_id"], getRandomVideo());
        }
        //generate Random Watchlist
        for ($j = 0; $j < NB_WATCHLIST; $j++) {
            if (generateIntBetween(0, 1) === 0) {
                addRandomMovieInWatchlist($user["user_id"], getRandomVideo());
            } else {
                addRandomSerieInWatchlist($user["user_id"], getRandomVideo());
            }
        }
    }
}
main();
