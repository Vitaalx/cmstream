<?php
namespace Core;

use Core\Route;

class Response{
    static private Response $currentResponse;
    private int $code = 200;
    private string $info;
    private array $headers = [];
    private array $expose = [];
    private array $cookies = [];

    public function __construct(){
        self::$currentResponse = $this;
    }

    public function code(int $code): Response
    {
        $this->code = $code;
        return $this;
    }
    
    public function setHeader(string $key, $content): Response
    {
        $this->headers[$key] = $content;
        return $this;
    }
    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }

    public function setHeaders(array $content): Response
    {
        $this->headers = $content;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getCookie(string $key): ?string
    {
        return $this->cookies[$key] ?? null;
    }

    public function setCookies(array $cookies): Response
    {
        $this->cookies = $cookies;

        return $this;
    }

    public function setCookie(
        string $key, 
        ?string $cookie, 
        ?int $duration = 0, 
        ?string $path = "", 
        ?string $domain = "", 
        ?bool $secure = false, 
        ?bool $httponly = false
    ): Response
    {
        $this->cookies[$key] = [$cookie, $duration, $path, $domain, $secure, $httponly];
        return $this;
    }

    public function addExpose(mixed $value): Response
    {
        array_push($this->expose, $value);
        return $this;
    }
    
    public function info(string $info): Response
    {
        $this->info = $info;
        return $this;
    }

    public function send(mixed $content = ""): void
    {
        $this->autoSetHeaders($content);

        echo $content;
        
        throw new SendResponse("send");
    }
    public function sendFile(string $path): void
    {
        if($this->getHeader("Content-Type") === null){
            $this->setHeader(
                "Content-Type",
                self::getMimeType($path)
            );
        }

        $this->autoSetHeaders();

        readfile($path);

        throw new SendResponse("sendFile");
    }

    public function render(string $view, string $template, array $params): void
    {
        $this->setHeader("template", $template);
        $this->addExpose("template");
        $this->setHeader("view", $view);
        $this->addExpose("view");

        $template = __DIR__ . "/../Templates/" . $template . ".php";
        if(file_exists($template) === false)
        {
            die("Template '" . $template . "' not exist.");
        }

        $view = __DIR__ . "/../Views/" . $view . ".php";
        if(file_exists($view) === false)
        {
            die("View '" . $view . "' not exist.");
        }

        $this->autoSetHeaders();


        extract($params);

        include $template;
        
        throw new SendResponse("render");
    }

    public function redirect(string $url){
        $this->setHeader("Location", $url);
        $this->code(303)->info("redirected")->send();
    }

    private function autoSetHeaders(mixed &$content = ""){
        http_response_code($this->code);
        
        if(isset($this->headers["Content-Type"]) === false){
            if(gettype($content) === "array" || gettype($content) === "object"){
                $this->headers["Content-Type"] = "application/json";
                $content = json_encode($content);
            }
            else{
                $this->headers["Content-Type"] = "text/html";
            }
        }

        if(isset($this->info) === true){
            $this->setHeader("aob-info", $this->info);
            $this->addExpose("aob-info");
        }

        $this->setHeader("access-control-expose-headers", implode(", ", $this->expose));

        foreach($this->headers as $key => $value){
            header("{$key}: {$value}");
        }

        foreach($this->cookies as $key => $value){
            setcookie($key, ...$value);
        }
    }

    static private function getMimeType($filename) {
        $split = explode(".", $filename);
        $ext = array_pop($split);
        $ext = strtolower($ext);
    
        $mimet = match($ext) { 
            "txt" => "text/plain",
            "htm" => "text/html",
            "html" => "text/html",
            "php" => "text/html",
            "css" => "text/css",
            "js" => "application/javascript",
            "json" => "application/json",
            "xml" => "application/xml",
            "swf" => "application/x-shockwave-flash",
        
            "flv" => "video/x-flv",
            "png" => "image/png",
            "jpe" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "jpg" => "image/jpeg",
            "gif" => "image/gif",
            "bmp" => "image/bmp",
            "ico" => "image/vnd.microsoft.icon",
            "tiff" => "image/tiff",
            "tif" => "image/tiff",
            "svg" => "image/svg+xml",
            "svgz" => "image/svg+xml",
        
            "zip" => "application/zip",
            "rar" => "application/x-rar-compressed",
            "exe" => "application/x-msdownload",
            "msi" => "application/x-msdownload",
            "cab" => "application/vnd.ms-cab-compressed",
        
            "mp3" => "audio/mpeg",
            "qt" => "video/quicktime",
            "mov" => "video/quicktime",
        
            "pdf" => "application/pdf",
            "psd" => "image/vnd.adobe.photoshop",
            "ai" => "application/postscript",
            "eps" => "application/postscript",
            "ps" => "application/postscript",
        
            "doc" => "application/msword",
            "rtf" => "application/rtf",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "docx" => "application/msword",
            "xlsx" => "application/vnd.ms-excel",
            "pptx" => "application/vnd.ms-powerpoint",
        
            "odt" => "application/vnd.oasis.opendocument.text",
            "ods" => "application/vnd.oasis.opendocument.spreadsheet",
        };
        
        return $mimet ?? "application/octet-stream";
    }

    /**
     * Get the value of currentResponse
     *
     * @return Response
     */
    static public function getCurrentResponse(): Response
    {
        return self::$currentResponse;
    }
}

Class SendResponse extends \Exception{
    private string $type;
    public function __construct(string $type){
        fastcgi_finish_request();
        $this->type = $type;
    }

    /**
     * Get the value of type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}