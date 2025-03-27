<?php

namespace Controller\API\ConfigController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\QueryBuilder;
use Core\File;
use Core\UploadFile;

use Services\Access\AccessConfigEditor;

/**
 * @POST{/api/config/logo}
 * @apiName UploadLogo
 * @apiGroup ConfigController
 * @apiVersion 1.0.0
 * @Feature ConfigEditor
 * @return Response
 * 
 * This controller is used to upload the logo of the application.
 */
class uploadLogo extends AccessConfigEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/fileUpload", $request->getFile('logo'), "logo"],
            ["file/sizeFile", fn () => $this->floor->pickup("logo"), "logo"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var UploadFile $logo */
        $logo = $this->floor->pickup("logo");
        $logo->saveTo(__DIR__ . '/../../public/img/icons/logo.png');
        $response->code(201)->info("icon.uploaded")->send();
    }
}
/**
 * @PUT{/api/config/app}
 * @apiName UpdateAppConfig
 * @apiGroup ConfigController
 * @apiVersion 1.0.0
 * @Feature ConfigEditor
 * @param CONFIG
 * return Response
 * 
 * This controller is used to update the application configuration.
 * This configuration is stored in the config.php file.
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
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)'([^']*)'(?:^$|[ ]*),/", "'{$key}' => '{$body[$key]}',", $config);
                } else {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)([0-9]*)(?:^$|[ ]*),/", "'{$key}' => {$body[$key]},", $config);
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
 * @apiName UpdateMailConfig
 * @apiGroup ConfigController
 * @apiVersion 1.0.0
 * @Feature ConfigEditor
 * @param CONFIG
 * 
 * This controller is used to update the mail configuration.
 * This configuration is stored in the config.php file.
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
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)'([^']*)'(?:^$|[ ]*),/", "'{$key}' => '{$body[$key]}',", $config);
                } else {
                    $config = preg_replace("/'{$key}'(?:^$|[ ]*)=>(?:^$|[ ]*)([0-9]*)(?:^$|[ ]*),/", "'{$key}' => {$body[$key]},", $config);
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
 * @GET{/api/config/app}
 * @apiName GetAppConfig
 * @apiGroup ConfigController
 * @apiVersion 1.0.0
 * @Feature ConfigEditor
 * @param CONFIG
 * @return Response
 * 
 * This controller is used to get the application configuration.
 */
class getConfigApp extends AccessConfigEditor
{
    public function handler(Request $request, Response $response): void
    {
        foreach (['APP_NAME', 'SECRET_KEY', 'TOKEN_DURATION', 'HOST'] as $key) {
            $data[$key] = CONFIG[$key];
        }
        $response->code(200)->send($data);
    }
}

/**
 * @GET{/api/config/mail}
 * @apiName GetMailConfig
 * @apiGroup ConfigController
 * @apiVersion 1.0.0
 * @Feature ConfigEditor
 * @param CONFIG
 * @return Response
 * 
 * This controller is used to get the mail configuration.
 */
class getConfigMail extends AccessConfigEditor
{
    public function handler(Request $request, Response $response): void
    {
        foreach (['MAIL_PORT', 'MAIL_HOST', 'MAIL_FROM'] as $key) {
            $data[$key] = CONFIG[$key];
        }
        $response->code(200)->send($data);
    }
}

/**
 * @POST{/api/config/sitemap}
 * @apiName GenerateSitemap
 * @apiGroup ConfigController
 * @apiVersion 1.0.0
 * @Feature ConfigEditor
 * @param CONFIG
 * @return Response
 * 
 * This controller is used to generate the sitemap of the application.
 */
class generateSitemap extends AccessConfigEditor
{
    public function handler(Request $request, Response $response): void
    {
        $output = [];
        $returnCode = 0;

        exec("php " . __DIR__ . "/../../bin/makeSitemap.php", $output, $returnCode);

        if ($returnCode !== 0) {
            $response->code(500)->info("sitemap.notGenerated")->send();
        } else {
            $response->code(200)->info("sitemap.isGenerated")->send();
        }
    }
}