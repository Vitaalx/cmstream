<?php

namespace Controller\API\UserController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Reset_Password;
use Entity\User;
use Entity\Waiting_validate;

use Services\Back\MailService;
use Services\MustBeAdmin;
use Services\MustBeConnected;
use Services\token\AccessToken;
use Services\token\EmailToken;
use Services\token\ResetToken;

/**
 * @POST{/api/register}
 * @Body Json Request
 * @param $firstname
 * @param $lastname
 * @param $username
 * @param $email
 * @param $password
 * 
 * @return $token
 * @example =>
 * {
 *  "firstname": "Jon",
 *  "lastname": "DOE",
 *  "username": "doe",
 *  "email": "jdoe@mail.com",
 *  "password": "jdo123!"
 * }
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
            ["user/usernameMustBeFree", fn () => $this->floor->pickup("username")],
            ["user/password", $user["password"], "password"]
        ];
    }

    //TODO fonction check permettant de vérifier si l'email est joignable
    public function check($email)
    {
        $result = FALSE;
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

        $token = EmailToken::generate(["id" => $waitingUser->getId()], true);

        MailService::send(
            $this->floor->pickup("email"),
            "Validation de votre compte",

            "Bonjour " . $this->floor->pickup("firstname") . " " . $this->floor->pickup("lastname") . ",<br><br>" .
                "Merci de vous &ecirctre inscrit sur notre site.<br>" .
                "Pour valider votre compte, veuillez cliquer sur le lien suivant :<br><br>" .
                "<a href='" . CONFIG["HOST"] . "/validate?token=" . $token . "'>Valider mon compte</a><br><br>" .
                "Cordialement,<br>" .
                "L'&eacutequipe de notre site."
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
        /** @var User $user */
        $user = $this->floor->pickup("user");

        if (password_verify($this->floor->pickup("password"), $user->getPassword()) === false) {
            $response->code(401)->info("wrong.password")->send();
        }

        AccessToken::generate(["id" => $user->getId()]);
        $response->code(204)->info("user.logged")->send();
    }
}

/**
 * @GET{/api/user}
 */
class selfInfo extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $role = $user->getRole();
        $permissions = $role ? $role->getPermissions() : [];
        $role = $role ? $role->getName() : null;

        $response
            ->code(200)
            ->info("user.info")
            ->send(
                [
                    "username" => $user->getUsername(),
                    "role" => $role,
                    "userId" => $user->getId(),
                    "permissions" => $permissions,
                    "lastname" => $user->getLastname(),
                    "firstname" => $user->getFirstname(),
                    "email" => $user->getEmail()
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
        AccessToken::delete();

        $response
            ->code(204)
            ->info("user.logout")
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
            ["user/usernameMustBeFree", fn () => $this->floor->pickup('username'), "username"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $user->setFirstname($this->floor->pickup("firstname"));
        $user->setLastname($this->floor->pickup("lastname"));
        $user->setUsername($this->floor->pickup("username"));
        $user->save();

        $response->code(200)->info("user.modified")->send(["user" => $user]);
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
            ["user/usernameMustBeFree", fn () => $this->floor->pickup('username'), "username"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user->setFirstname($this->floor->pickup("firstname"));
        $user->setLastname($this->floor->pickup("lastname"));
        $user->setUsername($this->floor->pickup("username"));
        $user->save();

        $response->code(200)->info("user.modified")->send(["user" => $user]);
    }
}

/**
 * @POST{/api/user/password}
 * @apiName CMStream
 * @param string email
 *
 * @return Response
 */
/*
Entry:
*/
class mailResetPassword extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["email"], "email"],
            ["user/email", fn () => $this->floor->pickup("email"), "email"],
            ["user/existByMail", fn () => $this->floor->pickup("email"), "user"],
            ["reset_password/mustNotExist", fn () => $this->floor->pickup("user")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");

        $resetPassword = Reset_Password::insertOne(
            fn (Reset_Password $resetPassword) => $resetPassword
                ->setUser($user)
        );

        $token = resetToken::generate(["id" => $resetPassword->getId()], true);

        MailService::send(
            $user->getEmail(),
            "Récupération de mot de passe",
            "Bonjour " . $user->getFirstname() . " " . $user->getLastname() . ",<br><br>" .
                "Pour valider votre nouveay mot de passe, veuillez cliquer sur le lien suivant :<br><br>" .
                "<a href='" . CONFIG["HOST"] . "/reset-password?token=" . $token . "'>Valider mon compte</a><br><br>" .
                "Cordialement,<br>" .
                "L'&eacutequipe de notre site."
        );

        $response->code(200)->info("send.email")->send();
    }
}

/**
 * @POST{/api/user/password/validate}
 * @apiName CMStream
 * @apiGroup User
 * @Description validate new password
 * @param string token
 * @return Response
 */
class resetPasswordValidate extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["token/checkResetToken", $request->getBody()["token"], "payload"],
            ["reset_password/exist", fn () => $this->floor->pickup("payload")["id"], "reset_password"],
            ["user/password", $request->getBody()["password"], "password"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Reset_Password $resetPassword */
        $resetPassword = $this->floor->pickup("reset_password");
        $user = $resetPassword->getUser();
        $user->setPassword(password_hash($this->floor->pickup("password"), PASSWORD_DEFAULT));
        $user->save();
        $resetPassword->delete();
        AccessToken::generate(["id" => $user->getId()]);
        $response->code(200)->info("user.logged")->send();
    }
}

/**
 * @POST{/api/user/validate}
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
            ["token/checkEmailToken", $request->getBody()["token"] ?? null, "payload"],
            ["waiting_validate/existById", fn () => $this->floor->pickup("payload")["id"] ?? null, "waiting_user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\Waiting_validate $waiting_user */
        $waiting_user = $this->floor->pickup("waiting_user");

        $user = User::insertOne(
            fn (User $user) => $user
                ->setFirstname($waiting_user->getFirstname())
                ->setLastname($waiting_user->getLastname())
                ->setUsername($waiting_user->getUsername())
                ->setEmail($waiting_user->getEmail())
                ->setPassword($waiting_user->getPassword())
        );

        $waiting_user->delete();

        AccessToken::generate(["id" => $user->getId()]);

        $response->code(204)->info("user.logged")->send();
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
