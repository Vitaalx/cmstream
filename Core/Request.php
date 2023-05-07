<?php
namespace Core;

class Request{
    private string $uri;
    private string $requestPath;
    private string $requestMethod;
    private array $requestQuery = [];
    private array $requestParams;
    private array $requestJsonBody;
    private $requestBody;
    private array $info;

    public function __construct(string $path, array $info, string $regexPath)
    {
        $this->uri = $_SERVER["REQUEST_URI"];
        $this->requestMethod = $_SERVER["REQUEST_METHOD"];

        $this->requestPath = $path;
        $this->info = $info;
        $this->setRequestBody();
        $this->setQuery();
        $this->setRequestParams($regexPath);
    }

    public function getPath(): string
    {
        return $this->requestPath;
    }

    public function getMethod(): string
    {
        return $this->requestMethod;
    }

    public function getQuery(string $key)
    {
        return $this->requestQuery[$key] ?? null;
    }

    public function getQuerys(): array
    {
        return $this->requestQuery;
    }

    public function getParam(string $key)
    {
        return $this->requestParams[$key] ?? null;
    }
    public function getParams(): array
    {
        return $this->requestParams;
    }

    public function getBody()
    {
        if(isset($this->requestJsonBody))return $this->requestJsonBody;
        else return $this->requestBody;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    private function setRequestBody()
    {
        if(isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] === 'application/json')
        {
            $this->requestJsonBody = json_decode(file_get_contents("php://input"), true);
        }
        else $this->requestBody = file_get_contents("php://input");
    }

    private function setRequestParams(string $regexPath){
        preg_match_all('/{([^\/]*)}/', $this->info["path"], $groups1);
        preg_match_all($regexPath, $this->requestPath, $groups2);

        if(isset($groups1[0]))
        {
            $groups1 = $groups1[1];
            array_shift($groups2);

            foreach ($groups1 as $k => $key)
            {
                $this->requestParams[$key] = $groups2[$k][0];
            }
        }
    }

    private function setQuery(){
        $uri = explode("?", $this->uri);

        if(isset($uri[1]))
        {
            $query = urldecode($uri[1]);
            foreach(explode("&", $query) as $key => $args){
                $args = explode("=", $args);
                if($args[0] !== "" && ($args[1] ?? null))$this->requestQuery[$args[0]] = $args[1];
            }
        }
    }
}