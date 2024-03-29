<?php

namespace Core;

use Core\Logger;

class UploadFile
{
    private array $file;
    private ?string $save = null;

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    public function getName(): string
    {
        return $this->file["name"];
    }

    public function getType(): string
    {
        return $this->file["type"];
    }

    public function getSize(): int
    {
        return $this->file["size"];
    }

    public function getPath(): ?string
    {
        return $this->save;
    }

    /**
     * @param string $path
     * @return File|null
     * 
     * Save the file in function of the path and return a File object.
     */
    public function saveTo(string $path): ?File
    {
        if ($this->save !== null) return null;
        move_uploaded_file($this->file["tmp_name"], $path);
        $this->save = $path;

        return new File($path);
    }

    /**
     * @return string
     * 
     * Return the content of the uploaded file.
     */
    public function read()
    {
        $file = fopen($this->file["tmp_name"], "r");
        $content = fread($file, filesize($this->file["tmp_name"]));
        fclose($file);
        return $content;
    }
}
