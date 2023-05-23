<?php

namespace controller\API\UserController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\User;

use Services\Back\AuthService;


/**
 * @method GET
 * @path /createUser
 * @Body Json Request
 * @param firstname
 * @param lastname
 * @param email
 * @param password hash method sha256
 * 
 * @return token
 * exemple: 
 * entry:
 *  {
	"firstname": "william",
	"lastname": "florentin",
	"email": "williamflorentin@mail.com",
	"password": "azertyuiop"
    }
 * response:
 *  {
	"token": "MTIxMGZmMzQ5MTZiZDEwODhlZmMyNmE1ODdkY2M4ZWIwNGI3MmVkMWQyYjAwZDBkZjE0ODM5MGQ0YzRlMjVmZC8yIHdpbGxpYW1mbG9yZW50aW5AbWFpbC5jb20gV0lMTElBTS8xNjg0NTE2MDY2"
    }
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

    public function handler(Request $request, Response $response): void
    {
        $auth = new AuthService();
        $token = $auth->register(
            $this->floor->pickup("user/firstname"),
            $this->floor->pickup("user/lastname"),
            $this->floor->pickup("user/email"),
            $request->getBody()['password']
        );

        $response->send([
            'token' => $token
        ]);
    }
}

/**
 * @method POST
 * @path /loginUser
 * @Body Json Request
 * @param email
 * @param password hash method sha256
 * 
 * @return token
 * exemple: 
 * entry:
 *  {
    "email": "williamflorentin@mail.com",
    "password": "azertyuiop"
    }
 * response:
 *  {
	"token": "MTIxMGZmMzQ5MTZiZDEwODhlZmMyNmE1ODdkY2M4ZWIwNGI3MmVkMWQyYjAwZDBkZjE0ODM5MGQ0YzRlMjVmZC8yIHdpbGxpYW1mbG9yZW50aW5AbWFpbC5jb20gV0lMTElBTS8xNjg0NTE2MDY2"
    }
 */
class loginUser extends Controller
{
    public function checkers(Request $request): array
    {
        $user = $request->getBody();
        return [
            ["user/email", $user['email']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $auth = new AuthService();
        $token = $auth->login(
            $this->floor->pickup("user/email"),
            $request->getBody()['password']
        );

        $response->send([
            'token' => $token
        ]);
    }
}
