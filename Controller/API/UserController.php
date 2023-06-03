<?php

namespace Controller\API\UserController;

use Core\Auth;
use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\User;

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
            ["user/mailUsed", fn() => $this->floor->pickup("email"), "mailUsed"],
            ["user/username", $user["username"], "username"],
            ["user/password", $user["password"], "password"]
        ];
    }

    /**
     * @throws \Exception
     */
    public function handler(Request $request, Response $response): void
    {
        $auth = new Auth();
        if($this->floor->pickup("mailUsed")) $response->code(409)->info("email.already.used")->send();
        User::insertOne([
           "firstname" => $this->floor->pickup("firstname"),
           "lastname" => $this->floor->pickup("lastname"),
           "username" => $this->floor->pickup("username"),
           "email" => $this->floor->pickup("email"),
           "password" => $auth::passwordHash($this->floor->pickup("password")),
            "role" => null
        ]);
        $token = $auth::generateToken($this->floor->pickup("email"), $this->floor->pickup("username"));
        $response->code(200)->info("user.registered")->send(["token" => $token]);
    }
}

/**
 * @POST{/login}
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
        $user = $request->getBody();
        return [
            ["user/email", $user['email'], "email"],
            ["user/existByMail", fn() => $this->floor->pickup("email"), "user"],
            ["type/string", $user['password'], "password"],
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        $auth = new Auth();
        /** @var User $user */
        $user = $this->floor->pickup("user");

        if ($user->getPassword() !== $auth::passwordHash($this->floor->pickup("password"))) {
            $response->code(401)->info("wrong.password")->send();
        }
        $token = $auth::generateToken($user->getEmail(), $user->getUsername());
        $response->code(200)->info("user.logged")->send(["token" => $token]);
    }
}
/**
 * @DELETE{/user/{id}}
 * @Path Path Request
 * @param $userId
 *
 * @return void
 */
class deleteUser extends Controller {

    public function checkers(Request $request): array
    {
        $userId = $request->getParam("id");
        return [
            ["type/int", $userId],
            ["user/exist", fn() => $userId, "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $user->delete();
        $response->code(204)->info("user.deleted")->send();
    }
}

/**
 * @PUT{/user/{id}}
 */
class modifyUser extends Controller {

    public function checkers(Request $request): array
    {
        $userId = $request->getQuery("id");
        $user = $request->getBody();
        return [
            ["type/int", $userId, "userId"],
            ["user/exist", fn() => $userId, "user"],
            ["user/firstname", $user["firstname"], "firstname"],
            ["user/lastname", $user["lastname"], "lastname"],
            ["user/username", $user["username"], "username"],
            ["user/password", $user["password"], "password"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");

        $user->setFirstname($this->floor->pickup("firstname"));
        $user->setLastname($this->floor->pickup("lastname"));
        $user->setUsername($this->floor->pickup("username"));
        $user->setPassword($this->floor->pickup("password"));

        $user->save();

        $response->code(200)->info("user.modified")->send(["user" => $user]);
    }
}
