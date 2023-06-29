<?php

namespace makeRoute;

require_once __DIR__ . "/../Core/AutoLoader.php";
require_once __DIR__ . "/scan.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../Core/Controller.php";

$textRoute = '

Route::match([
    "method" => "{METHOD}",
    "path" => "{PATH}",
    "controller" => "{CONTROLLER}",
]);
';

$routeFileContent = "";

$methods = [];

scan(
    __DIR__ . "/../Controller",
    function ($path) {
        $file = fopen($path, "r");
        $fileContent = fread($file, filesize($path));
        fclose($file);

        preg_match("/namespace[ ]*([a-zA-Z0-9\\\]*)/", $fileContent, $namespaceMatch);
        $namespace = $namespaceMatch[1];
        preg_match_all("/class[ ]*([a-zA-Z0-9_]*)/", $fileContent, $classMatch);
        $classList = $classMatch[1];

        include $path;

        foreach ($classList as $className) {
            $class = "{$namespace}\\{$className}";
            $rp = new \ReflectionClass($class);
            $class = explode("\\", $class);
            array_shift($class);
            $class = implode("/", $class);
            $comment = $rp->getDocComment();
            if ($comment === false) continue;

            preg_match_all("/@([a-zA-Z]*){((?:[^{}]+|{(?2)})*)}/", $comment, $match);
            if ($match === null) continue;

            foreach ($match[0] as $key => $value) {
                $method = strtoupper($match[1][$key]);
                $path = $match[2][$key] === "/" ? "/" : rtrim($match[2][$key], "/");
                $path = str_starts_with($match[2][$key], "/") ? $path : "/" . $path;

                global $methods;
                if(isset($methods[$path]) === false)$methods[$path] = [];
                array_push($methods[$path], $method);

                global $textRoute;
                $newRout = str_replace("{METHOD}", $method, $textRoute);
                $newRout = str_replace("{PATH}", $path, $newRout);
                $newRout = str_replace("{CONTROLLER}", $class, $newRout);

                global $routeFileContent;
                $routeFileContent .= $newRout;
            }
        }
    }
);


$methodsString = "\n\ndefine(\n\t\"METHODS\",\n\t[\n";
foreach($methods as $key => $value){
    $methodsString .= "\t\t\"$key\" => [\"" . implode("\", \"", $value) . "\"],\n";
}
$methodsString .= "\t]\n);";
$file = fopen(__DIR__ . "/../html/generate.methods.php", "w");
$fileContent = fwrite($file, "<?php$methodsString");
fclose($file);

$file = fopen(__DIR__ . "/../html/generate.index.php", "w");
$fileContent = fwrite($file, "<?php\nuse Core\Route;$routeFileContent");
fclose($file);
