<?php

namespace Core;

class Database
{
    private string $driver;
    private string $host;
    private string $port;
    private string $database;
    private string $username;
    private string $password;

    public function __construct(string $driver, string $host, string $port, string $database, string $username, string $password)
    {
        $this->driver = $driver;
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * this method is used to test the connection to the database
     * @throws \PDOException
     * @return void
     */
    public function testConnection(): void
    {
        try {
            $this->connection();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function connection(): \PDO
    {
        try {
            $pdo = new \PDO(
                $this->driver . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->database,
                $this->username,
                $this->password
            );
            return $pdo;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
}
