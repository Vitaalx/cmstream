<?php
namespace Core;

use Core\Route;

class Response{
    private int $code = 200;
    private string $info;
    private array $headers = [];

    public function __construct(){
        
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

    public function setHeaders(array $content): Response
    {
        $this->headers = $content;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
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

    public function render(string $options, array $params): void
    {
        $options = explode("@", $options);

        if(isset($options[1]) === false){
            die("Options '" . implode("@", $options) . "' is invalide.");
        }

        $template = $options[0];
        $template = "./Template/" . $template . ".php";
        if(file_exists($template) === false)
        {
            die("Template '" . $template . "' not exist.");
        }
        $view = $options[1];
        $view = "./View/" . $view . ".php";
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
            if(gettype($content) === "array"){
                $this->headers["Content-Type"] = "application/json";
                $content = json_encode($content);
            }
            else{
                $this->headers["Content-Type"] = "text/html";
            }
        }

        if(isset($this->info) === true){
            $this->setHeader("info", $this->info);
            $this->setHeader("access-control-expose-headers", "info");
        }

        foreach($this->headers as $key => $value){
            header("{$key}: {$value}");
        }
    }
}