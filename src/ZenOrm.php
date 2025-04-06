<?php

declare(strict_types=1);

namespace ZenOrm;

use PDO;

class ZenOrm
{
    protected PDO $pdo;
    public static PDO $pdoGetter;
    protected string $dsn;
    protected array $models;

    public function __construct($dsn)
    {
        try {
            $this->pdo = new PDO($dsn, null, null, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            self::$pdoGetter = $this->pdo;

            $this->dsn = $dsn;
            $this->models = [];
        } catch (\PDOException $e) {
            die('ZenOrm Execption: ' . $e->getMessage());
        }
    }

    public function register(string $model)
    {
        try {
            if (!(new $model instanceof Model)) {
                throw new \Exception("Class $model must be a ZenOrm\Model class.", 4408);
            }

            array_push($this->models, $model);
        } catch (\Throwable $e) {
            die('ZenOrm Exception: ' . $e->getMessage());
        }
    }

    public function getModels()
    {
        return $this->models;
    }
}
