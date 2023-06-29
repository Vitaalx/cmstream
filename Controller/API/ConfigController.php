<?php

namespace Controller\API\ConfigController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\QueryBuilder;
use Core\File;

use Services\Access\AccessConfigEditor;

/**
 * @POST{/api/config/logo}
 */
class uploadLogo extends AccessConfigEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/file", "logo", "logo"],
            ["file/sizeFile", fn () => $this->floor->pickup("logo"), "logo"],
            ["file/extensionFile", fn () => $this->floor->pickup("logo"), "logo"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $logo = $this->floor->pickup("logo");
        $return = move_uploaded_file($logo["tmp_name"], "/var/www/public/img/icons/logo.png");
        if (!$return) $response->code(500)->info("icon.notUploaded")->send();
        $response->code(201)->info("icon.uploaded")->send();
    }
}

/**
 * @PUT{/api/config/db}
 */
class updateConfigDB extends AccessConfigEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["DB_HOST"]],
            ["type/int", $request->getBody()["DB_PORT"]],
            ["type/string", $request->getBody()["DB_TYPE"]],
            ["type/string", $request->getBody()["DB_DATABASE"]],
            ["type/string", $request->getBody()["DB_USERNAME"]],
            ["type/string", $request->getBody()["DB_PASSWORD"]]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();
        try {
            $file = new File(__DIR__ . '/../../config.php');

            $config = $file->read();

            foreach (['DB_HOST', 'DB_PORT', 'DB_TYPE', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
                if (gettype($body[$key]) === 'string') {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)'([^']*)'(?:^$|[ ]*),/", "'{$key}' => '{$body[$key]}'", $config);
                } else {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)([0-9]*)(?:^$|[ ]*),/", "'{$key}' => {$body[$key]}", $config);
                }
            }
            $file->write($config);
            $response->code(204)->send();
        } catch (\Exception $e) {
            $response->code(500)->info("config.notUpdated")->send();
        }
    }
}

/**
 * @PUT{/api/config/app}
 */
class updateConfigApp extends AccessConfigEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["APP_NAME"]],
            ["type/string", $request->getBody()["SECRET_KEY"]],
            ["type/int", $request->getBody()["TOKEN_DURATION"] ?? 3600],
            ["type/string", $request->getBody()["HOST"], null, "host.error"],
            ["init/valideHost", $request->getBody()["HOST"], null, "host.error"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();
        try {
            $file = new File(__DIR__ . '/../../config.php');

            $config = $file->read();

            foreach (['APP_NAME', 'SECRET_KEY', 'TOKEN_DURATION', 'HOST'] as $key) {
                if (gettype($body[$key]) === 'string') {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)'([^']*)'(?:^$|[ ]*),/", "'{$key}' => '{$body[$key]}'", $config);
                } else {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)([0-9]*)(?:^$|[ ]*),/", "'{$key}' => {$body[$key]}", $config);
                }
            }
            $file->write($config);
            $response->code(204)->send();
        } catch (\Exception $e) {
            $response->code(500)->info("config.notUpdated")->send();
        }
    }
}

/**
 * @PUT{/api/config/mail}
 */
class updateConfigMail extends AccessConfigEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["MAIL_PORT"]],
            ["type/string", $request->getBody()["MAIL_HOST"]],
            ["type/string", $request->getBody()["MAIL_FROM"]],
            ["user/email", $request->getBody()["MAIL_FROM"]],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();
        try {
            $file = new File(__DIR__ . '/../../config.php');

            $config = $file->read();

            foreach (['MAIL_PORT', 'MAIL_HOST', 'MAIL_FROM'] as $key) {
                if (gettype($body[$key]) === 'string') {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)'([^']*)'(?:^$|[ ]*),/", "'{$key}' => '{$body[$key]}'", $config);
                } else {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)([0-9]*)(?:^$|[ ]*),/", "'{$key}' => {$body[$key]}", $config);
                }
            }
            $file->write($config);
            $response->code(204)->send();
        } catch (\Exception $e) {
            $response->code(500)->info("config.notUpdated")->send();
        }
    }
}

/**
 * GET{/api/config}
 */
class getConfig extends AccessConfigEditor
{
    public function handler(Request $request, Response $response): void
    {
        $file = new File(__DIR__ . '/../../config.php');
        $config = $file->read();
        $response->code(200)->send($config);
    }
}
