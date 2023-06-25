<?php

namespace Controller\API\InitApp;

use Core\Controller;
use Core\Entity;
use Core\Logger;
use Core\Request;
use Core\Response;
use Services\Permissions;
use Entity\Role;
use Entity\User;
use PHPMailer\PHPMailer;

class getInit extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->render("init", "none");
    }
}

class tryDB extends Controller
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
        Entity::dataBaseConnection($body);
        $response->code(204)->send();
    }
}

class tryAppConf extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["HOST"], null, "host.error"],
            ["init/valideHost", $request->getBody()["HOST"], null, "host.error"],
            ["type/string", $request->getBody()["APP_NAME"], null, "app.name.error"],
            ["type/string", $request->getBody()["SECRET_KEY"], null, "secret.key.error"],
            ["type/int", $request->getBody()["TOKEN_DURATION"] ?? 3600, null, "token.duration.error"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(204)->send();
    }
}

class tryEmail extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["MAIL_HOST"], "host"],
            ["type/int", $request->getBody()["MAIL_PORT"], "port"],
            ["type/string", $request->getBody()["MAIL_FROM"], "mail"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $this->floor->pickup("host");
        $mail->Port = $this->floor->pickup("port");

        $mail->setFrom($this->floor->pickup("mail"), "test");
        $mail->addAddress($this->floor->pickup("mail"));
        $mail->isHTML(true);
        $mail->Subject = "test";
        $mail->Body = "test";
        $mail->send();

        $response->code(204)->send();
    }
}

class tryFirstAccount extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["user/firstname", $request->getBody()["firstname"]],
            ["user/lastname", $request->getBody()["lastname"]],
            ["user/email", $request->getBody()["email"]],
            ["user/username", $request->getBody()["username"]],
            ["user/password", $request->getBody()["password"]]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(204)->send();
    }
}

/*
{
    "DB_HOST" : "database",
    "DB_PORT" : "5432",
    "DB_TYPE" : "pgsql",
    "DB_DATABASE" : "esgi",
    "DB_USERNAME" : "esgi",
    "DB_PASSWORD" : "Test1234",

    "HOST" : "http://localhost:1506",
    "APP_NAME" : "cmStream",
    "SECRET_KEY" : "secretKey",
    "TOKEN_DURATION" : 3600,

    "MAIL_HOST" : "maildev",
    "MAIL_PORT" : 1025,
    "MAIL_FROM" : "no-reply-cmstream@mail.com",

    "firstname": "Mathieu",
    "lastname": "Campani",
    "username": "mathcovax",
    "email": "campani.mathieu@gmail.com",
    "password": "!mlkit1234"
}
*/
class postInit extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["DB_HOST"]],
            ["type/int", $request->getBody()["DB_PORT"]],
            ["type/string", $request->getBody()["DB_TYPE"]],
            ["type/string", $request->getBody()["DB_DATABASE"]],
            ["type/string", $request->getBody()["DB_USERNAME"]],
            ["type/string", $request->getBody()["DB_PASSWORD"]],

            ["type/string", $request->getBody()["HOST"]],
            ["type/string", $request->getBody()["APP_NAME"]],
            ["type/string", $request->getBody()["SECRET_KEY"]],
            ["type/int", $request->getBody()["TOKEN_DURATION"] ?? 3600],

            ["type/string", $request->getBody()["MAIL_HOST"]],
            ["type/int", $request->getBody()["MAIL_PORT"]],
            ["type/string", $request->getBody()["MAIL_FROM"]],

            ["user/firstname", $request->getBody()["firstname"], "firstname"],
            ["user/lastname", $request->getBody()["lastname"], "lastname"],
            ["user/email", $request->getBody()["email"], "email"],
            ["user/username", $request->getBody()["username"], "username"],
            ["user/password", $request->getBody()["password"], "password"]
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        $body = $request->getBody();

        Entity::dataBaseConnection($body);

        try {
            $defaultConfigPath = __DIR__ . "/../../Core/config.example";

            $file = fopen($defaultConfigPath, "a+");
            if ($file) {
                $configFile = fread($file, filesize($defaultConfigPath));
                preg_match_all("/{(.*)}/", $configFile, $groups);
                foreach ($groups[1] as $key => $value) {
                    $configFile = str_replace($groups[0][$key], $body[$value], $configFile);
                }
                file_put_contents(__DIR__ . "/../../config.php", $configFile);
            }
            fclose($file);

            $file = fopen(__DIR__ . "/../../html/index.php", "r");
            $fileContent = fread($file, filesize(__DIR__ . "/../../html/index.php"));
            rename(__DIR__ . "/../../html/index.tmp.php", "index.php");
            file_put_contents(__DIR__ . "/../../html/index.tmp.php", $fileContent);

            exec("php " . __DIR__ . "/../../bin/makeMigration.php", $output, $retval);
            if($retval === 1 || count($output) !== 0)throw new \Exception(implode("\n", $output));
            exec("php " . __DIR__ . "/../../bin/doMigration.php", $output, $retval);
            if($retval === 1 || count($output) !== 0)throw new \Exception(implode("\n", $output));
            exec("php " . __DIR__ . "/../../bin/makeRoute.php", $output, $retval);
            if($retval === 1 || count($output) !== 0)throw new \Exception(implode("\n", $output));

            $role = Role::insertOne(
                fn (Role $role) => $role
                    ->setName("admin") 
            );

            $role->addPermission(Permissions::AccessDashboard);
            $role->addPermission(Permissions::RoleEditor);
            $role->addPermission(Permissions::CommentsManager);
            $role->addPermission(Permissions::ContentsManager);
            $role->addPermission(Permissions::StatsViewer);
            $role->addPermission(Permissions::UserEditor);
            $role->addPermission(Permissions::ConfigEditor);
            
            User::insertOne(
                fn (User $user) => $user
                    ->setEmail($this->floor->pickup("email"))
                    ->setFirstname($this->floor->pickup("firstname"))
                    ->setLastname($this->floor->pickup("lastname"))
                    ->setUsername($this->floor->pickup("username"))
                    ->setRole($role)
                    ->setPassword(password_hash($this->floor->pickup("password"), PASSWORD_DEFAULT))
            );

        } 
        catch (\Throwable $th) {
            exec("php " . __DIR__ . "/../../bin/reset.php", $output, $retval);

            $data = [
                "info" => "init error",
                "message" => $th->getMessage(),
                "file" => $th->getFile(),
                "line" => $th->getLine(),
            ];

            $response->code(500)->info("config.uncreated")->send($data);
        }

        $response->code(204)->info("config.create")->send();
    }
}
