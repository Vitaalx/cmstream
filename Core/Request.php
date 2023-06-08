<?php
namespace Core;

class Request{
    static private Request $currentRequest;
    private string $uri;
    private string $requestPath;
    private string $requestMethod;
    private array $requestQuery = [];
    private array $requestParams;
    private array $requestJsonBody;
    private $requestBody;
    private array $info;
    private array $cookies;

    public function __construct(string $path, array $info, string $regexPath)
    {
        self::$currentRequest = $this;
        
        $this->uri = $_SERVER["REQUEST_URI"];
        $this->requestMethod = $_SERVER["REQUEST_METHOD"];
        $this->requestQuery = $_GET;
        $this->cookies = $_COOKIE;

        $this->requestPath = $path;
        $this->info = $info;
        $this->setRequestBody();
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

    public function getQuery(string $key): ?string
    {
        return $this->requestQuery[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getQuerys(): array
    {
        return $this->requestQuery;
    }

    public function getParam(string $key): ?string
    {
        return $this->requestParams[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->requestParams;
    }

    public function getCookie(string $key): ?string
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
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
        if(isset($_SERVER["CONTENT_TYPE"]) && str_contains($_SERVER["CONTENT_TYPE"], "application/json"))
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

    /**
     * Get the value of currentRequest
     *
     * @return Request
     */
    public static function getCurrentRequest(): Request
    {
        return self::$currentRequest;
    }
}