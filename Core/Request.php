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
    private array $cookies;
    private array $headers;
    private array $files = [];

    public function __construct(string $path, string $regexPath)
    {
        self::$currentRequest = $this;
        
        $this->uri = $_SERVER["REQUEST_URI"];
        $this->requestMethod = $_SERVER["REQUEST_METHOD"];
        $this->requestQuery = $_GET;
        $this->cookies = $_COOKIE;
        $this->headers = getallheaders();
        foreach ($_FILES as $key => $value) {
            $this->files[$key] = new UploadFile($value);
        }

        $this->requestPath = $path;
        $this->setRequestBody();
        $this->setRequestParams($regexPath);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->requestPath;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * @param string $key
     * @return string|null
     */
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

    /**
     * @param string $key
     * @return string|null
     */
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

    /**
     * @param string $key
     * @return string|null
     */
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

    /**
     * @param string $key
     * @return string|null
     */
    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return UploadFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getFile(string $key): ?UploadFile
    {
        return $this->files[$key] ?? null;
    }
    /**
     * @return string
     * If the request is json, return json body, else return raw body
     */
    public function getBody()
    {
        if(isset($this->requestJsonBody))return $this->requestJsonBody;
        else return $this->requestBody;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return void
     * If the request is json, set json body, else set raw body
     * get request on input php global
     */
    private function setRequestBody()
    {
        if(isset($_SERVER["CONTENT_TYPE"]) && str_contains($_SERVER["CONTENT_TYPE"], "application/json"))
        {
            $this->requestJsonBody = json_decode(file_get_contents("php://input"), true);
        }
        else $this->requestBody = file_get_contents("php://input");
    }

    /**
     * @param string $regexPath
     * @return void
     * Set request params
     */
    private function setRequestParams(string $regexPath){
        preg_match_all('/{([^\/]*)}/', Route::getInfo()["path"], $groups1);
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
        return self::$currentRequest ?? new Request(explode("?", $_SERVER["REQUEST_URI"])[0], "//");
    }
}