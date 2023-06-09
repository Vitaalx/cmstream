<?php

namespace checker\init;

use Core\Floor;
use Core\Response;

function valideHost(string $host, Floor $floor, Response $response): void
{
    if(!preg_match("/^(https:\/\/|http:\/\/)[a-z0-9.:]+$/", $host)) $response->code(400)->info("host.error")->send();
}