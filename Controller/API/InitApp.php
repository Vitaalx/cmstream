<?php

namespace Controller\API\InitApp;

use Core\Controller;
use Core\Entity;
use Core\Request;
use Core\Response;
use Entity\Role;
use Entity\User;

class getInit extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->render("init", "none", []);
    }
}

define("EXAMPLE_FILENAME", "config.example.txt");
define("EXAMPLE_FILE_PATH", "./../Core/");
define("CONFIG_FILENAME", "config.php");
define("CONFIG_PATH", "./../");

/*
{
    "DB_HOST" : "database",
    "DB_PORT" : "5432",
    "APP_NAME" : "cmStream",
    "DB_CONNECTION" : "pgsql",
    "DB_DATABASE" : "esgi",
    "DB_USERNAME" : "esgi",
    "DB_PASSWORD" : "Test1234",
    "SECRET_KEY" : "secretKey",
    "TOKEN_DURATION" : 3600,
    "MAIL_HOST" : "maildev",
    "MAIL_PORT" : 1025,
    "MAIL_FROM" : "no-reply-cmstream@mail.com",
    "MAIL_FROM_NAME" : "cmStream",
    "HOST" : "http://localhost:1506/",
    
    "title": "trop bg le site",

    "firstname": "Mathieu",
    "lastname": "Campani",
    "username": "mathcovax",
    "email": "campani.mathieu@gmail.com",
    "password": "!mlkit1234"
}
*/
class postInit extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["TOKEN_DURATION"] ?? 3600],
            ["type/int", $request->getBody()["MAIL_PORT"]],
            ["type/string", $request->getBody()["MAIL_HOST"]],
            ["type/string", $request->getBody()["MAIL_FROM"]],
            ["type/string", $request->getBody()["MAIL_FROM_NAME"]],
            ["type/string", $request->getBody()["HOST"]],
            ["type/string", $request->getBody()["DB_HOST"]],
            ["type/string", $request->getBody()["APP_NAME"]],
            ["type/string", $request->getBody()["DB_CONNECTION"]],
            ["type/string", $request->getBody()["DB_DATABASE"]],
            ["type/string", $request->getBody()["DB_USERNAME"]],
            ["type/string", $request->getBody()["DB_PASSWORD"]],
            ["type/string", $request->getBody()["SECRET_KEY"]],

            ["type/string", $request->getBody()["title"], "title"],

            ["user/firstname", $request->getBody()["firstname"], "firstname"],
            ["user/lastname", $request->getBody()["lastname"], "lastname"],
            ["user/email", $request->getBody()["email"], "email"],
            ["user/username", $request->getBody()["username"], "username"],
            ["user/password", $request->getBody()["password"], "password"]
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();

        try {
            Entity::dataBaseConnection($body);
        } catch (\PDOException $e) {
            $response->code(500)->info("Config.uncreated")->send(["error" => "Connexion à la base de données impossible"]);
        }

        try {
            $file = fopen(EXAMPLE_FILE_PATH . EXAMPLE_FILENAME, "a+");
            if ($file) {
                $configFile = fread($file, filesize(EXAMPLE_FILE_PATH . EXAMPLE_FILENAME));
                preg_match_all("/{(.*)}/", $configFile, $groups);
                foreach ($groups[1] as $key => $value) {
                    $configFile = str_replace($groups[0][$key], $body[$value], $configFile);
                }
                file_put_contents(CONFIG_PATH . CONFIG_FILENAME, $configFile);
            }
            fclose($file);

            $file = fopen(__DIR__ . "/../../index.html", "a+");
            $fileContent = fread($file, filesize(__DIR__ . "/../../index.html"));
            $fileContent = preg_replace("/<title>.*<\/title>/", "<title>" . $this->floor->pickup("title") . "</title>", $fileContent);
            file_put_contents(__DIR__ . "/../../index.html", $fileContent);
            fclose($file);

            $file = fopen(__DIR__ . "/../../html/index.php", "r");
            $fileContent = fread($file, filesize(__DIR__ . "/../../html/index.php"));
            rename(__DIR__ . "/../../html/index.tmp.php", "index.php");
            file_put_contents(__DIR__ . "/../../html/index.tmp.php", $fileContent);

            exec("php " . __DIR__ . "/../../bin/makeMigration.php", $output, $retval);
            if($retval === 1 || count($output) !== 0)throw new \Exception(implode("\n", $output));
            exec("php " . __DIR__ . "/../../bin/doMigration.php", $output, $retval);
            if($retval === 1 || count($output) !== 0)throw new \Exception(implode("\n", $output));
            exec("php " . __DIR__ . "/../../bin/makeRoute.php", $output, $retval);
            if($retval === 1 || count($output) !== 0)throw new \Exception(implode("\n", $output));

            $role = Role::insertOne(
                fn (Role $role) => $role
                    ->setName("admin") 
            );
            
            User::insertOne(
                fn (User $user) => $user
                    ->setEmail($this->floor->pickup("email"))
                    ->setFirstname($this->floor->pickup("firstname"))
                    ->setLastname($this->floor->pickup("lastname"))
                    ->setUsername($this->floor->pickup("username"))
                    ->setRole($role)
                    ->setPassword(password_hash($this->floor->pickup("password"), PASSWORD_DEFAULT))
            );

            $response->code(204)->info("Config.create")->send();

        } catch (\Throwable $th) {
            exec("php " . __DIR__ . "/../../bin/reset.php", $output, $retval);

            $data = [
                "info" => "init error",
                "message" => $th->getMessage(),
                "file" => $th->getFile(),
                "line" => $th->getLine(),
            ];

            $response->code(500)->info("Config.uncreated")->send($data);
        }
    }
}
