<?php

namespace Core;

class Response
{
    static private Response $currentResponse;
    private int $code = 200;
    private ?string $info = null;
    private array $headers = [];
    private array $expose = [];
    private array $cookies = [];

    public function __construct()
    {
        self::$currentResponse = $this;
    }

    public function code(int $code): Response
    {
        $this->code = $code;
        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
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

    /**
     * @param string $key
     * @param string|null $cookie
     * @param integer|null $duration
     * @param string|null $path
     * @param string|null $domain
     * @param boolean|null $secure
     * @param boolean|null $httponly
     * @return Response
     * 
     * Set a cookie in the response.
     */
    public function setCookie(
        string $key,
        ?string $cookie,
        ?int $duration = 0,
        ?string $path = "",
        ?string $domain = "",
        ?bool $secure = false,
        ?bool $httponly = false
    ): Response {
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

    public function getInfo(): ?string
    {
        return $this->info ?? null;
    }

    /**
     * @param mixed|string $content
     * @return void
     * 
     * Send a response.
     * Called by Controller.
     * Use for send a json or a html in function of the content type.
     */
    public function send(mixed $content = ""): void
    {
        if ($this->getHeader("Content-Type") === null) {
            if (gettype($content) === "array" || gettype($content) === "object") {
                $this->setHeader("Content-Type", "application/json");
                $content = json_encode($content);
            } else $this->setHeader("Content-Type", "text/html");
        }

        $this->autoSetHeaders();

        echo $content;

        fastcgi_finish_request();
        Logger::auto("send");
        exit;
    }

    /**
     * @param string $path
     * @return void
     * 
     * Send a file in function of the mime type.
     */
    public function sendFile(string $path): void
    {
        if ($this->getHeader("Content-Type") === null) {
            $this->setHeader(
                "Content-Type",
                self::getMimeType($path)
            );
        }

        $this->autoSetHeaders();

        readfile($path);

        fastcgi_finish_request();
        Logger::auto("sendFile");
        exit;
    }

    /**
     * @param string $view
     * @param string $template
     * @param array $params
     * @return void
     * 
     * Render a view with a template.
     * Unique use for render siteMap.
     */
    public function render(string $view, string $template, array $params = []): void
    {
        $this->setHeader("template", $template);
        $this->addExpose("template");
        $this->setHeader("view", $view);
        $this->addExpose("view");

        if ($this->getHeader("Content-Type") === null) {
            $this->setHeader(
                "Content-Type",
                "text/html"
            );
        }

        $template = __DIR__ . "/../Templates/" . $template . ".php";
        if (file_exists($template) === false) {
            throw new \Exception("Template '" . $template . "' not exist.");
        }

        $view = __DIR__ . "/../Views/" . $view . ".php";
        if (file_exists($view) === false) {
            throw new \Exception("View '" . $view . "' not exist.");
        }

        $this->autoSetHeaders();


        extract($params);

        include $template;

        fastcgi_finish_request();
        Logger::auto("sendFile");
        exit;
    }

    /**
     * @param string $url
     * @return void
     * 
     * Redirect to an url.
     */
    public function redirect(string $url)
    {
        $this->setHeader("Location", $url);
        if ($this->info === null) $this->info("redirected");
        $this->send();
    }

    /**
     * @return void
     * 
     * Set headers and cookies.
     * Called by send() and sendFile().
     */
    private function autoSetHeaders()
    {
        http_response_code($this->code);

        if ($this->info !== null) {
            $this->setHeader("Info", $this->info);
            $this->addExpose("Info");
        }

        if (isset(CONFIG["HOST"])) {
            $this->setHeader("Access-Control-Allow-Origin", CONFIG["HOST"]);
        }
        if (defined("METHODS") && isset(METHODS[Route::getInfo()["path"]])) {
            $this->setHeader("Access-Control-Allow-Methods", implode(", ", METHODS[Route::getInfo()["path"]]));
        }

        if (count($this->expose) !== 0) $this->setHeader("Access-Control-Expose-Headers", implode(", ", $this->expose));

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        foreach ($this->cookies as $key => $value) {
            setcookie($key, ...$value);
        }
    }

    /**
     * @param [type] $filename
     * @return void
     * Get the mime type of a file.
     * Called by sendFile().
     */
    static private function getMimeType($filename)
    {
        $split = explode(".", $filename);
        $ext = array_pop($split);
        $ext = strtolower($ext);

        $mimet = match ($ext) {
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

            "ttf" => "font/ttf",
            "otf" => "font/otf",
            "woff" => "font/woff",
            "woff2" => "font/woff2",
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
        return self::$currentResponse ?? new Response();
    }
}