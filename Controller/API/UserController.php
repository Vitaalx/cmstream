<?php

namespace Controller\API\UserController;

use Core\Token;
use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\User;
use Entity\Waiting_validate;

use Services\Back\MailService;
use Services\MustBeAdmin;
use Services\MustBeConnected;

/**
 * @POST{/register}
 * @Body Json Request
 * @param $firstname
 * @param $lastname
 * @param $username
 * @param $email
 * @param $password
 * 
 * @return $token
 */
/*
Entry:
{
    "firstname": "John",
    "lastname": "Doe",
    "username": "jdoe",
    "email": "jdoe@example.com",
    "password": "jdo123!"
}
*/
class register extends Controller
{
    public function checkers(Request $request): array
    {
        $user = $request->getBody();
        return [
            ["user/firstname", $user["firstname"], "firstname"],
            ["user/lastname", $user["lastname"], "lastname"],
            ["user/email", $user["email"], "email"],
            ["user/mailMustBeFree", fn () => $this->floor->pickup("email")],
            ["user/username", $user["username"], "username"],
            ["user/password", $user["password"], "password"]
        ];
    }

    /**
     * @throws \Exception
     */
    public function handler(Request $request, Response $response): void
    {
        $waitingUser = Waiting_validate::insertOne([
            "firstname" => $this->floor->pickup("firstname"),
            "lastname" => $this->floor->pickup("lastname"),
            "username" => $this->floor->pickup("username"),
            "email" => $this->floor->pickup("email"),
            "password" => password_hash($this->floor->pickup("password"), PASSWORD_DEFAULT),
        ]);

        $token = Token::generateToken(["id" => $waitingUser->getId()], CONFIG["SECRET_KEY"]);

        MailService::send(
            $this->floor->pickup("email"),
            "Validation de votre compte",

            "Bonjour " . $this->floor->pickup("firstname") . " " . $this->floor->pickup("lastname") . ",\n\n" .
                "Merci de vous être inscrit sur notre site.\n" .
                "Pour valider votre compte, veuillez cliquer sur le lien suivant :\n" .
                CONFIG['HOST'] . "api/user/validate?token={$token}\n\n" .
                "Cordialement,\n" .
                "L'équipe de notre site."
        );

        $response->code(200)->info("user.registered")->send();
    }
}

/**
 * @POST{/api/login}
 * @Body Json Request
 * @param $email
 * @param $password
 * 
 * @return $token
 * @example =>
 * {
 *  "email": "jdoe@mail.com",
 *  "password": "jdo123!"
 * }
 */
class login extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["user/email", $request->getBody()["email"] ?? null, "email"],
            ["user/existByMail", fn () => $this->floor->pickup("email"), "user"],
            ["type/string", $request->getBody()["password"] ?? null, "password"],
        ];
    }
    public function handler(Request $request, Response $response): void
    {

        if (password_verify($this->floor->pickup("password"), $this->floor->pickup("user")->getPassword()) === false) {
            $response->code(401)->info("wrong.password")->send();
        }
        $token = Token::generateToken(["id" => $this->floor->pickup("user")->getId()], CONFIG["SECRET_KEY"]);
        $response->setCookie("token", $token);
        $response->code(200)->info("user.logged")->send(["token" => $token]);
    }
}

/**
 * @GET{/api/user}
 */
class selfInfo extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\User $user */
        $user = $this->floor->pickup("user");

        $response
            ->code(200)
            ->info("user.info")
            ->send(
                [
                    "username" => $user->getUsername(), 
                    "role" => $user->getRole()->getName()
                ]
            );
    }
}

/**
 * @GET{/api/logout}
 */
class logout extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        $response
            ->code(204)
            ->info("user.logout")
            ->setCookie("token", null)
            ->send();
    }
}

/**
 * @DELETE{/user/{id}}
 * @Path Path Request
 * @param $userId
 *
 * @return void
 */
class deleteUserAdmin extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "user_delete"],
            ["user/exist", fn () => $this->floor->pickup("user_delete"), "user_delete"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $this->floor->pickup("user_delete")->delete();
        $response->code(200)->info("user.deleted")->send();
    }
}

/**
 * @DELETE{/user}
 * @return void
 */
class deleteUser extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        $this->floor->pickup("user")->delete();
        $response->code(200)->info("user.deleted")->send();
    }
}

/**
 * @PUT{/user/{id}}
 */
/*
Entry: 
{
    "firstname": "John",
    "lastname": "Doe",
    "username": "jdoe",
    "password": "jdo123!"
}
*/
class modifyUserAdmin extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "user_id"],
            ["user/exist", fn () => $this->floor->pickup('user_id'), "user"],
            ["user/firstname", $request->getBody()["firstname"], "firstname"],
            ["user/lastname", $request->getBody()["lastname"], "lastname"],
            ["user/username", $request->getBody()["username"], "username"],
            ["user/password", $request->getBody()["password"], "password"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {

        $this->floor->pickup("user")->setFirstname($this->floor->pickup("firstname"));
        $this->floor->pickup("user")->setLastname($this->floor->pickup("lastname"));
        $this->floor->pickup("user")->setUsername($this->floor->pickup("username"));
        $this->floor->pickup("user")->setPassword($this->floor->pickup("password"));
        $this->floor->pickup("user")->save();

        $response->code(200)->info("user.modified")->send(["user" => $this->floor->pickup("user")]);
    }
}

/**
 * @PUT{/user}
 */
/*
Entry: 
{
    "firstname": "John",
    "lastname": "Doe",
    "username": "jdoe",
    "password": "jdo123!"
}
*/
class modifyUser extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["user/firstname", $request->getBody()["firstname"], "firstname"],
            ["user/lastname", $request->getBody()["lastname"], "lastname"],
            ["user/username", $request->getBody()["username"], "username"],
            ["user/password", $request->getBody()["password"], "password"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {

        $this->floor->pickup("user")->setFirstname($this->floor->pickup("firstname"));
        $this->floor->pickup("user")->setLastname($this->floor->pickup("lastname"));
        $this->floor->pickup("user")->setUsername($this->floor->pickup("username"));
        $this->floor->pickup("user")->setPassword($this->floor->pickup("password"));
        $this->floor->pickup("user")->save();

        $response->code(200)->info("user.modified")->send(["user" => $this->floor->pickup("user")]);
    }
}

/**
 * @GET{/api/user/validate}
 * @apiName CMStream
 * @apiGroup User
 * @Feature mail
 * @Description validate email address
 * @param string token
 * @return Response
 */
/*
Entry:
{
    token: "ezoifjepiozfjpozejfpozejfj",
}
*/
class MailValidate extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["token/check", $request->getQuery("token"), "payload"],
            ["waiting_validate/existById", fn () => $this->floor->pickup("payload")["id"] ?? null, "waiting_user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\Waiting_validate $waiting_user */
        $waiting_user = $this->floor->pickup("waiting_user");

        User::insertOne(
            fn (User $user) => $user
                ->setFirstname($waiting_user->getFirstname())
                ->setLastname($waiting_user->getLastname())
                ->setUsername($waiting_user->getUsername())
                ->setEmail($waiting_user->getEmail())
                ->setPassword($waiting_user->getPassword())
        );

        $response->code(200)->info("user.validated")->send();
    }
}

/**
 * @GET{/api/users}
 */
class getUsers extends MustBeAdmin
{
    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->info("users")->send(["users" => User::findMany()]);
    }
}

/**
 * @GET{/api/user/{id}}
 */
class getUserAdmin extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "user_id"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->info("user")->send(["user" => $this->floor->pickup("user")]);
    }
}

/**
 * @GET{/api/user}
 */
class getUser extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->info("user")->send(["user" => $this->floor->pickup("user")]);
    }
}
