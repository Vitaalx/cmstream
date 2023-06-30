<?php

namespace Core;

class File
{
    private string $path;
    private mixed $file;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->file = fopen($path, "a+");
    }

    public function read()
    {
        fseek($this->file, 0, SEEK_SET);
        $content = fread($this->file, filesize($this->path));
        return $content;
    }

    public function write($data): File
    {
        fseek($this->file, 0, SEEK_SET);
        fwrite($this->file, $data);
        return $this;
    }

    public function append($data): File
    {
        fseek($this->file, 0, SEEK_END);
        fwrite($this->file, $data);
        return $this;
    }

    public function rename($path): File
    {
        fclose($this->file);
        rename($this->path, $path);
        $this->path = $path;
        $this->file = fopen($path, "a+");
        return $this;
    }

    public function copyTo($path): File
    {
        fclose($this->file);
        copy($this->path, $path);
        $this->path = $path;
        $this->file = fopen($path, "a+");
        return $this;
    }

    public function delete(): void
    {
        fclose($this->file);
        $this->file = null;
        unlink($this->path);
    }

    public function __destruct()
    {
        if ($this->file !== null && $this->file !== false) fclose($this->file);
    }
}
