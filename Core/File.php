<?php

namespace Core;

class File
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return void
     * 
     * return the content in string format of the file.
     */
    public function read()
    {
        $file = fopen($this->path, "r");
        fseek($file, 0, SEEK_SET);
        $content = fread($file, filesize($this->path));
        return $content;
    }

    /**
     * @param string $data
     * @return File
     * 
     * Write data in the file.
     */
    public function write($data): File
    {
        $file = fopen($this->path, "w");
        fseek($file, 0, SEEK_SET);
        fwrite($file, $data);
        return $this;
    }

    /**
     * @param string $data
     * @return File
     * 
     * Append data in the file.
     */
    public function append($data): File
    {
        $file = fopen($this->path, "a+");
        fseek($file, 0, SEEK_END);
        fwrite($file, $data);
        return $this;
    }

    /**
     * @param string $path
     * @return File
     * 
     * Change the path of the file.
     */
    public function rename($path): File
    {
        rename($this->path, $path);
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $path
     * @return File
     * 
     * Copy the file in the path.
     */
    public function copyTo($path): File
    {
        copy($this->path, $path);
        $this->path = $path;
        return $this;
    }

    /**
     * @return void
     * 
     * Delete the file in tmp folder of the server.
     */
    public function delete(): void
    {
        unlink($this->path);
    }

    /**
     * @return int
     * 
     * Return the size of the file.
     */
    public function getSize(): int
    {
        return filesize($this->path);
    }
}
