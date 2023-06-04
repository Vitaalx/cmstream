<?php

namespace Controller\API\InitAppController;

use Core\Controller;
use Core\Entity;
use Core\Request;
use Core\Response;

define("EXAMPLE_FILENAME", "config.example.txt");
define("EXAMPLE_FILE_PATH", "./../Core/");
define("CONFIG_FILENAME", "config.php");
define("CONFIG_PATH", "./../");

class initApp extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["initApp/tokenDuration", $request->getBody()["TOKEN_DURATION"]],
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
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();
        $file = fopen(EXAMPLE_FILE_PATH . EXAMPLE_FILENAME, "a+");
        try {
            Entity::dataBaseConnection($body);
        } catch (\PDOException $e) {
            $response->code(500)->info("Config.uncreated")->send(["error" => "Connexion à la base de données impossible"]);
        }
        if ($file) {
            $configFile = fread($file, filesize(EXAMPLE_FILE_PATH . EXAMPLE_FILENAME));
            preg_match_all("/{(.*)}/", $configFile, $groups);
            foreach ($groups[1] as $key => $value) {
                $configFile = str_replace($groups[0][$key], $body[$value], $configFile);
            }
            file_put_contents(CONFIG_PATH . CONFIG_FILENAME, $configFile);
        }
        fclose($file);

        $file = fopen("./../html/index.php", "r");
        $fileContent = fread($file, filesize("./../html/index.php"));
        rename("./../html/index.tmp.php", "index.php");
        file_put_contents("./../html/index.tmp.php", $fileContent);

        $response->code(204)->info("Config.create")->send();
    }
}
