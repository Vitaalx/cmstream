<?php

require __DIR__ . "/../Core/AutoLoader.php";
require __DIR__ . "/scan.php";
require __DIR__ . "/../config.php";

$textRoute = '

Route::match([
    "method" => "{METHOD}",
    "path" => "{PATH}",
    "controller" => "{CONTROLLER}",
]);
';

$routeFileContent = "<?php\nuse Core\Route;";

scan(
    __DIR__ . "/../Controller",
    function($path){
        $file = fopen($path, "r");
        $fileContent = fread($file, filesize($path));
        fclose($file);

        preg_match("/namespace[ ]*([a-zA-Z0-9\\\]*)/", $fileContent, $namespaceMatch);
        $namespace = $namespaceMatch[1];
        preg_match_all("/class[ ]*([a-zA-Z0-9]*)[ ]*extends[ ]*Controller/", $fileContent, $classMatch);
        $classList = $classMatch[1];

        include $path;

        foreach ($classList as $className) {
            $class = "{$namespace}\\{$className}";
            $rp = new \ReflectionClass($class);
            $class = str_replace("controller\\", "", $class);
            $class = str_replace("\\", "/", $class);
            $comment = $rp->getDocComment();
            if($comment === false) continue;
            
            preg_match("/@([a-zA-Z]*){(.*)}/", $comment, $match);
            if(isset($match[0]) === false) continue;
            $method = strtoupper($match[1]);
            $path = $match[2];

            global $textRoute;
            $newRout = str_replace("{METHOD}", $method, $textRoute);
            $newRout = str_replace("{PATH}", $path, $newRout);
            $newRout = str_replace("{CONTROLLER}", $class, $newRout);

            global $routeFileContent;
            $routeFileContent .= $newRout;
        }
    }
);

$file = fopen(__DIR__ . "/../html/generate.index.php", "w");
$fileContent = fwrite($file, $routeFileContent);
fclose($file);