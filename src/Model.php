<?php

declare(strict_types=1);

namespace ZenOrm;

use AllowDynamicProperties;
use PDO;

#[AllowDynamicProperties]
abstract class Model
{
    abstract public function schema(Column $column);

    protected string $queryString = "";
    protected array $queryDependencies = [];

    public static function all()
    {
        $token  = explode('\\', get_called_class());
        $table = strtolower($token[sizeof($token) - 1]) . 's';

        return ZenOrm::$pdoGetter->query("select * from $table")->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    public static function create(array $data)
    {
        $token  = explode('\\', get_called_class());
        $table = strtolower($token[sizeof($token) - 1]) . 's';

        $columns = implode(',', array_keys($data));
        $values = implode(',', array_map(
            fn($col) => ":$col",
            array_keys($data)
        ));

        $stmt = ZenOrm::$pdoGetter->prepare("insert into $table ($columns) values ($values)");

        $stmt->execute($data);
    }

    public static function update(array $data): Model
    {
        $token  = explode('\\', get_called_class());
        $table = strtolower($token[sizeof($token) - 1]) . 's';

        $fields = [];
        foreach ($data as $key => $value) {
            array_push($fields, "$key = :$key");
        }

        $fields = implode(', ', $fields);

        $model = get_called_class();
        $modelInstance = new $model;

        $modelInstance->queryString = "update $table set $fields";

        $modelInstance->queryDependencies = $data;

        return $modelInstance;
    }

    public static function find(array $args = ["*"]): Model
    {
        $token  = explode('\\', get_called_class());
        $table = strtolower($token[sizeof($token) - 1]) . 's';

        $fields = implode(', ', $args);

        $model = get_called_class();
        $modelInstance = new $model;

        $modelInstance->queryString = "select $fields from $table";

        return $modelInstance;
    }

    public static function delete(): Model
    {
        $token  = explode('\\', get_called_class());
        $table = strtolower($token[sizeof($token) - 1]) . 's';

        $model = get_called_class();
        $modelInstance = new $model;

        $modelInstance->queryString = "delete from $table";

        return $modelInstance;
    }

    public function where(array $args)
    {
        $someArgs = [];
        foreach ($args as $key => $value) {
            array_push($someArgs, "$key = :$key");
        }

        $someArgs = implode(' and ', $someArgs);

        $this->queryString .= " where $someArgs";
        $this->queryDependencies = [...$this->queryDependencies, ...$args];

        return $this;
    }

    public function exec()
    {
        $stmt = ZenOrm::$pdoGetter->prepare($this->queryString);
        $stmt->execute($this->queryDependencies);

        unset($this->queryString);
        unset($this->queryDependencies);
    }

    public function many(): array
    {
        $stmt = ZenOrm::$pdoGetter->prepare($this->queryString);
        $stmt->execute($this->queryDependencies);

        unset($this->queryString);
        unset($this->queryDependencies);

        return $stmt->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    public function one(): Model
    {
        $stmt = ZenOrm::$pdoGetter->prepare($this->queryString);
        $stmt->execute($this->queryDependencies);

        unset($this->queryString);
        unset($this->queryDependencies);

        return $stmt->fetchObject(
            get_called_class(),
        );
    }
}
