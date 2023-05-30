<?php

namespace controller\API\UserController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Services\Back\AuthService;

/*
entry: {
	"firstname": "John",
	"lastname": "Doe",
	"email": "johndoe@test.com",
	"password": "test"
}
response: {
	"token": "MTIxMGZmMzQ5MTZiZDEwODhlZmMyNmE1ODdkY2M4ZWIwNGI3MmVkMWQyYjAwZDBkZjE0ODM5MGQ0YzRlMjVmZC8yIHdpbGxpYW1mbG9yZW50aW5AbWFpbC5jb20gV0lMTElBTS8xNjg0NTE2MDY2"
}
*/
/**
 * @method POST
 * @path /register
 * @Body Json Request
 * @param $firstname
 * @param $lastname
 * @param $email
 * @param $password
 * 
 * @return $token
 */
class register extends Controller
{
    public function checkers(Request $request): array
    {
        $user = $request->getBody();
        return [
            ["user/firstname", $user['firstname']],
            ["user/lastname", $user["lastname"]],
            ["user/email", $user['email']]
        ];
    }

    /**
     * @throws \Exception
     */
    public function handler(Request $request, Response $response): void
    {
        $auth = new AuthService();
        try {
            $token = $auth->register(
                $this->floor->pickup("user/firstname"),
                $this->floor->pickup("user/lastname"),
                $this->floor->pickup("user/email"),
                $request->getBody()['password']
            );
        } catch (\Exception $e) {
            $response->code($e->getCode())->send([
                "error" => $e->getMessage(),
                "code" => $e->getCode()
            ]);
        }

        $response->code(200)->send([
            'token' => $token
        ]);
    }
}

/*
entry: {
    "email": "johndoe@test.com",
    "password": "test"
}
response: {
	"token": "MTIxMGZmMzQ5MTZiZDEwODhlZmMyNmE1ODdkY2M4ZWIwNGI3MmVkMWQyYjAwZDBkZjE0ODM5MGQ0YzRlMjVmZC8yIHdpbGxpYW1mbG9yZW50aW5AbWFpbC5jb20gV0lMTElBTS8xNjg0NTE2MDY2"
}
*/
/**
 * @method POST
 * @path /login
 * @Body Json Request
 * @param $email
 * @param $password
 * 
 * @return $token
 */
class login extends Controller
{
    public function checkers(Request $request): array
    {
        $user = $request->getBody();
        return [
            ["user/email", $user['email']]
        ];
    }

    /**
     * @throws \Exception
     */
    public function handler(Request $request, Response $response): void
    {
        $auth = new AuthService();
        try {
            $token = $auth->login(
                $this->floor->pickup("user/email"),
                $request->getBody()['password']
            );
        } catch (\Exception $e) {
            $response->code($e->getCode())->send([
                "error" => $e->getMessage(),
                "code" => $e->getCode()
            ]);
        }
        $response->code(200)->send([
            'token' => $token
        ]);
    }
}
