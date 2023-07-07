<?php

namespace Controller\API\ContentManager\Pages;

use Core\File;
use Core\Request;
use Core\Response;
use Services\Access\AccessContentsManager;

/**
 * @PUT{/api/pages}
 */
class SetPages extends AccessContentsManager
{
    public function handler(Request $request, Response $response): void
    {
        $file = new File(__DIR__ . "/../../../public/cuteVue/pages.json");
        $file->write($request->getBody());
        $response->code(204)->info("pages.set")->send();
    }
}