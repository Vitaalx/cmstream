<?php

namespace Controller\API\UserController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Reset_Password;
use Entity\User;
use Entity\Waiting_validate;
use Services\Access\AccessDashboard;
use Services\Access\AccessUserEditor;
use Services\Back\MailService;
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
 * 
 * This controller is used to register a new user.
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
            ["user/mailMustBeFree", fn() => $this->floor->pickup("email")],
            ["user/username", $user["username"], "username"],
            ["user/usernameMustBeFree", fn() => $this->floor->pickup("username")],
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
 * 
 * This controller is used to login a user.
 */
class login extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["user/email", $request->getBody()["email"] ?? null, "email"],
            ["user/existByMail", fn() => $this->floor->pickup("email"), "user"],
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
 * @return Response
 * 
 * This controller is used to get the user informatio if he is connected.
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
 * @DELETE{/api/user}
 * @return Response
 * 
 * This controller is used to delete the user account if he is connected.
 */
class selfDelete extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $user->delete();
        
        AccessToken::delete();

        $response->code(204)->info("user.delete")->send();
    }
}

/**
 * @GET{/api/logout}
 * @return Response
 * 
 * This controller is used to logout the user if he is connected.
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
 * @DELETE{/api/user/{id}}
 * @Path Path Request
 * @param $userId
 *
 * @return void
 * 
 * This controller is used to delete a user on the admin dashboard.
 */
class deleteUserAdmin extends AccessUserEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "user_delete"],
            ["user/exist", fn() => $this->floor->pickup("user_delete"), "user_delete"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user_delete");
        $role = $user->getRole();
        if (($role !== null && $role->getId() === 1) || $user->getId() === 1) {
            $response->code(403)->info("user.cant.delete.admin")->send();
        }
        $user->delete();
        $response->code(200)->info("user.deleted")->send();
    }
}

/**
 * @DELETE{/user}
 * @return void
 * 
 * This controller is used to delete the user account if he is connected.
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
 * @PUT{/api/user/{id}}
 * @apiName CMStream
 * @apiGroup User
 * @param Request $request
 * @return Response
 * 
 * This controller is used to modify a user on the admin dashboard.
 */
/*
Entry:
{
    "firstname": "John",
    "lastname": "Doe",
    "username": "jdoe",
}
*/

class modifyUserAdmin extends AccessUserEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "user_id"],
            ["user/exist", fn() => $this->floor->pickup('user_id'), "user"],
            ["user/firstname", $request->getBody()["firstname"], "firstname"],
            ["user/lastname", $request->getBody()["lastname"], "lastname"],
            ["user/username", $request->getBody()["username"], "username"],
            ["user/usernameMustBeFree", fn() => $this->floor->pickup('username'), "userByUsername"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        /** @var User $userByUsername */
        $userByUsername = $this->floor->pickup("userByUsername");
        $user->setFirstname($this->floor->pickup("firstname"));
        $user->setLastname($this->floor->pickup("lastname"));
        if ($userByUsername === null || $user->getId() === $userByUsername->getId()) $user->setUsername($this->floor->pickup("username"));
        else $response->code(409)->info("username.already.used")->send();
        $user->save();

        $response->code(200)->info("user.modified")->send(["user" => $user]);
    }
}

/**
 * @PUT{/api/user}
 * @apiName CMStream
 * @apiGroup User
 * @Feature UserEditor
 * @param Request $request
 * @return Response
 * 
 * This controller is used to modify the user account if he is connected.
 */
class modifyUser extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["user/firstname", $request->getBody()["firstname"], "firstname"],
            ["user/lastname", $request->getBody()["lastname"], "lastname"],
            ["user/username", $request->getBody()["username"], "username"],
            ["user/usernameMustBeFree", fn() => $this->floor->pickup('username'), "userByUsername"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $userByUsername */
        $userByUsername = $this->floor->pickup("userByUsername");

        /** @var User $user */
        $user = $this->floor->pickup("user");

        if($userByUsername !== null && $userByUsername->getId() !== $user->getId()) $response->code(409)->info("username.already.used")->send();

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
            ["user/email", fn() => $this->floor->pickup("email"), "email"],
            ["user/existByMail", fn() => $this->floor->pickup("email"), "user"],
            ["reset_password/mustNotExistOrExpire", fn() => $this->floor->pickup("user")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");

        $resetPassword = Reset_Password::insertOne(
            fn(Reset_Password $resetPassword) => $resetPassword
                ->setUser($user)
        );

        $token = resetToken::generate(["id" => $resetPassword->getId()], true);

        MailService::send(
            $user->getEmail(),
            "Changement de mot de passe",
            "Bonjour " . $user->getFirstname() . " " . $user->getLastname() . ",<br><br>" .
            "Pour Changer votre mot de passe, veuillez cliquer sur le lien suivant :<br><br>" .
            "<a href='" . CONFIG["HOST"] . "/reset-password?token=" . $token . "'>Changer mon mot de passe</a><br><br>" .
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
            ["reset_password/exist", fn() => $this->floor->pickup("payload")["id"], "reset_password"],
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
            ["waiting_validate/existById", fn() => $this->floor->pickup("payload")["id"] ?? null, "waiting_user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Waiting_validate $waiting_user */
        $waiting_user = $this->floor->pickup("waiting_user");

        $user = User::insertOne(
            fn(User $user) => $user
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
 * @apiName CMStream
 * @apiGroup User
 * @Feature UserEditor
 * @param Request $request
 * @return Response
 * 
 * This controller is used to get all users on the admin dashboard.
 */
class getUsers extends AccessUserEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getQuery("page") ?? 0, "page"],
            ["type/string", $request->getQuery("name") ?? "", "name"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $page = $this->floor->pickup("page");
        $name = $this->floor->pickup("name");
        $number = 5;

        /** @var User $users */
        $users = User::findMany(
            [
                "username" => [
                    "\$CTN" => $name
                ]
            ],
            ["ORDER_BY" => ["id"], "OFFSET" => $number * $page, "LIMIT" => $number]
        );

        User::groups("userRole");

        $response
            ->code(200)
            ->info("users")
            ->send(["users" => $users]);
    }
}

/**
 * @GET{/api/users/count}
 * @apiName CMStream
 * @apiGroup User
 * @Feature UserEditor
 * @param Request $request
 * @return Response
 * 
 * This controller is used to get the number of users on the admin dashboard.
 */
class getUsersCount extends AccessDashboard
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $response
            ->code(200)
            ->info("count")
            ->send(["count" => User::count()]);
    }
}

/**
 * @GET{/api/user/{id}}
 * @apiName CMStream
 * @apiGroup User
 * @Feature UserEditor
 * @param Request $request
 * @return Response
 * 
 * This controller is used to get a user on the admin dashboard.
 */
class getUserAdmin extends AccessUserEditor
{
    public function checkers(Request $request): array
    {
        return [
            [],
            ["type/int", $request->getParam("id"), "user_id"],
            ["user/exist", fn() => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->info("user")->send(["user" => $this->floor->pickup("user")]);
    }
}
