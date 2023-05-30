<?php

namespace checker\comment;

use Core\Response;

function videoId(int $id): int {
    return $id;
}

function userId(int $id): int {
    return $id;
}

function id(int $id): int {
    return $id;
}

function status(int $id): int {
    return $id;
}

function content(string $content): string {
    return htmlspecialchars($content);
}
