<?php

namespace Core;

use Core\Database;

class ConfigFile
{
    /**
     * this method is used to set the configuration of the database
     * @throws \Exception
     * @param array $data
     * @return void
     */
    public function setConfigDatabase(array $data): void
    {
        try {
            $database = new Database(
                $data['driver'],
                $data['host'],
                $data['port'],
                $data['database'],
                $data['username'],
                $data['password']
            );


            $database->testConnection();

            $file = fopen("./../.env", "a+");
            fwrite($file, "DB_CONNECTION=" . $data['driver'] . "\n");
            fwrite($file, "DB_HOST=" . $data['host'] . "\n");
            fwrite($file, "DB_PORT=" . $data['port'] . "\n");
            fwrite($file, "DB_DATABASE=" . $data['database'] . "\n");
            fwrite($file, "DB_USERNAME=" . $data['username'] . "\n");
            fwrite($file, "DB_PASSWORD=" . $data['password'] . "\n");
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * this method is used to set the name of the application
     * @throws \Exception
     * @param string $appName
     * @return void
     */
    public function setAppName(string $appName): void
    {
        try {
            $file = fopen("./../.env", "a+");
            fwrite($file, "APP_NAME=" . $appName . "\n");
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * this method is used to set the secretKey of the application
     * @throws \Exception
     * @param string $secretKey
     * @return void
     */
    public function setSecretKey(string $secretKey): void
    {
        try {
            $file = fopen("./../.env", "a+");
            fwrite($file, "SECRET_KEY=" . $secretKey . "\n");
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function setTokenDuration(int $duration): void
    {
        try {
            $file = fopen("./../.env", "a+");
            fwrite($file, "TOKEN_DURATION=" . $duration . "\n");
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * thus method is used to get the value of a key in the .env file
     *
     * @param string $key
     * @return string
     */
    public function getEnv(string $key): string
    {
        $file = fopen("./../.env", "r");
        if(!$file) die("Cannot opened file (Verify .env file at root folder).");
        while (!feof($file)) {
            $line = fgets($file);
            $line = explode("=", $line);
            if ($line[0] === $key) {
                return trim($line[1]);
            }
        }
        fclose($file);
        return "";
    }
}
