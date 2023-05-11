<?php
namespace Core;

use Core\Request;
use Core\Floor;
use Core\Response;

abstract class Controller {
    protected Floor $floor;

    public function __construct(Request $request, Response $response){
        $this->floor = new Floor();
        $checkers = $this->checkers($request);
        self::launchCheckers($checkers, $this->floor, $response);
        $this->handler($request, $response);
    }
    
    abstract public function checkers(Request $request): array;
    abstract public function handler(Request $request, Response $response): void;

    static private function launchCheckers(array $checkers, Floor $floor, Response $response)
    {
        try{
            foreach($checkers as $checker){
                $function = self::autoLoadChecker($checker[0]);
                if(is_callable($checker[1])){
                    $checker[1] = $checker[1]();
                }
                $value = $function($checker[1], $floor, $response);
                $floor->droped($checker[0], $value);
            }
        }
        catch(\TypeError $th){
            $data = [
                "info" => "Checker type error.",
                "message" => $th->getMessage(),
                "file" => $th->getFile(),
                "line" => $th->getLine(),
            ];
            
            $response->code(400)->info("ERROR.BAD_TYPE")->send($data);
        }
    }

    static function autoLoadChecker(string $checker): string
    {
        $function = "checker/{$checker}";
        $function = str_replace("/", "\\", $function);
        $function = str_replace("\\\\", "\\", $function);
        $function = rtrim($function, "\\");
        
        if(function_exists($function) === false){
            $path = explode("/", rtrim($checker, "/"));
            array_pop($path);
            $path = __DIR__ ."/../Checker/" . implode("/", $path) . ".php";
            
            if(file_exists($path) === false)
            {
                die("File '" . $path . "' not exist.");
            }

            include $path;

            if(function_exists($function) === false)
            {
                die("Function '" . $function . "' not exist.");
            }
        }

        return $function;
    }
}

function callChecker(string $checker, mixed $value){
    $checkerFunction = Controller::autoLoadChecker($checker);
    $floor = new Floor();
    try{
        return $checkerFunction($value, $floor, Response::getCurrentResponse());
    }
    catch(\TypeError $th){
        $data = [
            "info" => "Checker type error.",
            "message" => $th->getMessage(),
            "file" => $th->getFile(),
            "line" => $th->getLine(),
        ];
        
        Response::getCurrentResponse()->code(400)->info("ERROR.BAD_TYPE")->send($data);
    }
}