<?php
namespace Core;

use Core\Route;

class Response{
    static private Response $currentResponse;
    private int $code = 200;
    private string $info;
    private array $headers = [];
    private array $expose = [];

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
    public function getHeader(string $key): array
    {
        return $this->headers[$key];
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

    public function addExpose(mixed $value): void
    {
        array_push($this->expose, $value);
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
        
        exit;
    }
    public function sendFile(string $path): void
    {
        $this
        ->setHeader(
            "Content-Type",
            mime_content_type($path)
        )
        ->autoSetHeaders($content);

        readfile($path);

        exit;
    }

    public function render(string $view, string $template, array $params): void
    {
        $this->setHeader("template", $template);
        $this->addExpose("template");
        $this->setHeader("view", $view);
        $this->addExpose("view");

        $template = __DIR__ . "/../Template/" . $template . ".php";
        if(file_exists($template) === false)
        {
            die("Template '" . $template . "' not exist.");
        }

        $view = __DIR__ . "/../View/" . $view . ".php";
        if(file_exists($view) === false)
        {
            die("View '" . $view . "' not exist.");
        }

        $this->autoSetHeaders();


        extract($params);

        include $template;
        exit;
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
            $this->setHeader("info", $this->info);
            $this->addExpose("info");
        }

        $this->setHeader("access-control-expose-headers", implode(", ", $this->expose));

        foreach($this->headers as $key => $value){
            header("{$key}: {$value}");
        }
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