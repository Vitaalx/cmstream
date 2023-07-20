<?php
namespace Core;

require __DIR__ . "/Request.php";
require __DIR__ . "/Controller.php";
require __DIR__ . "/Floor.php";
require __DIR__ . "/Response.php";
require __DIR__ . "/AutoLoader.php";
require __DIR__ . "/Logger.php";

error_reporting(0);

function callController(string $controller){
    $controllerClass = Route::autoLoadController($controller);
    return new $controllerClass(Request::getCurrentRequest(), Response::getCurrentResponse());
}

function error_handler(){
    $error = error_get_last();
    if($error === null) return;
    Logger::error($error["message"]);
    $response = new Response();
    $response
    ->code(500)
    ->info("ERROR.INTERNAL_SERVER")
    ->send([
        "info" => "Internal server error.",
        "message" => $error["message"],
        "file" => $error["file"],
        "line" => $error["line"],
    ]);
}

register_shutdown_function("Core\\error_handler");

set_error_handler("Core\\error_handler", E_COMPILE_ERROR);
set_error_handler("Core\\error_handler", E_CORE_ERROR);
set_error_handler("Core\\error_handler", E_ERROR);

if(file_exists(__DIR__ . "/../config.php")) include __DIR__ . "/../config.php";
else define("CONFIG", []);

class Route{
    static private string $requestPath;
    static private int $count = 0;
    static private array $info;

    /**
     * @param array $info
     * @param string $info["method"] : GET, POST, PUT, DELETE
     * @param string $info["path"] : /path/{id}/path
     * @param string $info["controller"] : controller/Controller
     * @return void
     * @example
     * Route::match([
     *     "method" => "GET",
     *    "path" => "/path/{id}/path",
     *   "controller" => "controller/Controller"
     * ]);
     * 
     * Check if the route matches the request.
     * If the route matches the request, the controller is called.
     * If the route does not match the request, the next route is checked.
     */
    static public function match(array $info): void
    {
        self::$count++;
        self::checkInfo($info);
        $regexPath = self::setRegexPath($info["path"]);
        if(
            preg_match($regexPath, self::$requestPath) === 1 && 
            (
                $info["method"] === $_SERVER["REQUEST_METHOD"] ||
                $info["method"] === "*"
            )
        )
        {  
            self::$info = $info;
            $request = new Request(self::$requestPath, $regexPath);
            $response = new Response();

            $class = self::autoLoadController($info["controller"]);

            new $class($request, $response);
            $response->code(503)->info("ERROR.NO_SEND_RESPONSE")->send();

            exit;
        }
    }

    /**
     * @param string $path
     * @return string
     * @example
     * Route::setRegexPath("/path/{id}/path");
     * 
     * Convert the path to a regular expression.
     */
    static private function setRegexPath(string $path)
    {
        preg_match_all('/({[^\/]*})/', $path, $groups);
        $groups = $groups[1];

        foreach($groups as $group){
            $path = str_replace($group, "([^/]*)", $path);
        }

        $path = str_replace("/", "\\/", $path);

        return "/^" . $path  . "$/";
    }

    /**
     * @param array $info
     * @param string $info["method"] : GET, POST, PUT, DELETE
     * @param string $info["path"] : /path/{id}/path
     * @param string $info["controller"] : controller/Controller
     * @return void
     * @example
     * Route::checkInfo([
     *     "method" => "GET",
     *    "path" => "/path/{id}/path",
     *   "controller" => "controller/Controller"
     * ]);
     * 
     * Check if the route is correct.
     * If the route is not correct, an exception is thrown.
     * @throws \Exception
     */
    static function checkInfo($info): void
    {
        if(isset($info["method"]) === false)
        {
            throw new \Exception("Route " . self::$count . " needs methods");
        }
        else if(isset($info["path"]) === false)
        {
            throw new \Exception("Route " . self::$count . " needs path");
        }
        else if(isset($info["controller"]) === false)
        {
            throw new \Exception("Route " . self::$count . " need controller.");
        }
    }

    /**
     * @param string $controller
     * @return string
     * @example
     * Route::autoLoadController("controller/Controller");
     * 
     * Autoload the controller.
     * If the controller is not found, an exception is thrown.
     * @throws \Exception
     */
    static function autoLoadController(string $controller)
    {
        $class = "controller/{$controller}";
        $class = str_replace("/", "\\", $class);
        $class = str_replace("\\\\", "\\", $class);
        $class = rtrim($class, "\\");

        if(class_exists($class) === false){
            $path = explode("/", rtrim($controller, "/"));
            array_pop($path);
            $path = __DIR__ . "/../Controller/" . implode("/", $path) . ".php";

            if(file_exists($path) === false)
            {
                throw new \Exception("File '" . $path . "' not exist.");
            }

            include $path;

            if(class_exists($class) === false)
            {
                throw new \Exception("Class '" . $class . "' not exist.");
            }
        }

        return $class;
    }

    /**
     * @return array
     * @example
     * Route::getInfo();
     * 
     * Get the information of the route that matches the request.
     */
    static function getInfo(){
        return self::$info ?? ["path" => "BEFORE_MATCH"];
    }

    /**
     * @return void
     * @example
     * Route::initRoute();
     * 
     * Initialize the route.
     * Get the request path.
     * If the request method is OPTIONS, check if the origin is correct.
     * If the origin is not correct, a bad request is return.
     * If the origin is correct, the route is initialized.
     */
    static function initRoute(): void
    {
        $origin = getallheaders()["Origin"] ?? null;
        if($_SERVER["REQUEST_METHOD"] === "OPTIONS" && isset(CONFIG["HOST"]) && $origin !== null){
            if(preg_match("/" . CONFIG["HOST"] . "/", $origin) === false){
                Response::getCurrentResponse()->code(400)->send();
            }
        }
        $uri = explode("?", $_SERVER["REQUEST_URI"]);
        self::$requestPath = $uri[0] === "/" ? "/" : rtrim($uri[0], "/");
    }
}

Route::initRoute();