<?php

namespace controller\API\InitAppController;

use Core\Controller;
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
            ["initApp/tokenDuration", $request->getBody()["token_duration"]]
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();
        $file = fopen(EXAMPLE_FILE_PATH.EXAMPLE_FILENAME, "a+");
        if($file) {
            $configFile = fread($file, filesize(EXAMPLE_FILE_PATH.EXAMPLE_FILENAME));
            preg_match_all("/{(.*)}/", $configFile, $groups);
            foreach($groups[1] as $key => $value) {
                $configFile = str_replace($groups[0][$key], $body[$value], $configFile);
            }
            file_put_contents(CONFIG_PATH.CONFIG_FILENAME, $configFile);
        }
        fclose($file);

        $file = fopen("./../html/index.php", "r");
        $fileContent = fread($file, filesize("./../html/index.php"));
        rename("./../html/index_tmp.php", "index.php");
        file_put_contents("./../html/index_tmp.php", $fileContent);

        $response->code(204)->info("Config.create")->send();
    }
}