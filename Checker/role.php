<?php
namespace checker\role;

use Core\Floor;
use Core\Response;
use Entity\Role;
use Entity\User;

function alreadyExistByName(string $name, Floor $floor, Response $response): void
{
    /** @var Role $role */
    $role = Role::findFirst(["name" => $name]);
    if($role !== null) $response->info("role.exist")->code(409)->send();
}

function exist(int $id, Floor $floor, Response $response): Role
{
    /** @var Role $role */
    $role = Role::findFirst(["id" => $id]);
    if($role === null) $response->info("role.notfound")->code(404)->send();
    return $role;
}

function existByName(string $name, Floor $floor, Response $response): Role
{
    /** @var Role $role */
    $role = Role::findFirst(["name" => $name]);
    if($role === null) $response->info("role.notfound")->code(404)->send();
    return $role;
}

function admin(int $id, Floor $floor, Response $response): void
{
    /** @var Role $role */
    $role = Role::findFirst(["id" => $id]);
    if($role === null) $response->info("role.notfound")->code(404)->send();
    if($role->getId() === 1) $response->info("role.protected.admin")->code(403)->send();
}