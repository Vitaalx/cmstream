<?php

namespace Core;

class File
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function read()
    {
        $file = fopen($this->path, "r");
        fseek($file, 0, SEEK_SET);
        $content = fread($file, filesize($this->path));
        return $content;
    }

    public function write($data): File
    {
        $file = fopen($this->path, "w");
        fseek($file, 0, SEEK_SET);
        fwrite($file, $data);
        return $this;
    }

    public function append($data): File
    {
        $file = fopen($this->path, "a+");
        fseek($file, 0, SEEK_END);
        fwrite($file, $data);
        return $this;
    }

    public function rename($path): File
    {
        rename($this->path, $path);
        $this->path = $path;
        return $this;
    }

    public function copyTo($path): File
    {
        copy($this->path, $path);
        $this->path = $path;
        return $this;
    }

    public function delete(): void
    {
        unlink($this->path);
    }

    public function getSize(): int
    {
        return filesize($this->path);
    }
}
