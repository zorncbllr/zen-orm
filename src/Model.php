<?php

declare(strict_types=1);

namespace ZenOrm;

use AllowDynamicProperties;

#[AllowDynamicProperties]
abstract class Model
{
    protected Column $column;

    public function __construct(...$args)
    {
        $this->column = new Column($this->getTable());

        $this->schema();

        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }

    abstract public function schema();

    public static function findById(string | int $id): Model
    {
        $instance = new (get_called_class());
        $table = $instance->getTable();

        $primaryKey = $instance->column->getPrimaryKey();

        $stmt = ZenOrm::$pdo->prepare("select * from $table where $primaryKey = :$primaryKey");
        $stmt->execute([$primaryKey => $id]);

        return $stmt->fetchObject(get_called_class());
    }

    public function save()
    {
        $table = $this->getTable();
        $model = $this->getFieldAndPlaceholder();

        $fields = implode(', ', $model['fields']);
        $placeholders = implode(', ', $model['placeholders']);

        $parameters = array_filter(
            get_object_vars($this),
            fn($vars) => !($vars instanceof Column)
        );

        $query = "insert into $table ($fields) values ($placeholders)";

        $stmt = ZenOrm::$pdo->prepare($query);
        $stmt->execute($parameters);
    }

    public function update()
    {
        $table = $this->getTable();

        $fields = $this->getFieldAndPlaceholder()['fields'];

        $parameters = [];

        foreach ($this as $key => $value) {
            if (in_array($key, $fields)) {
                $parameters[$key] = $value;
            }
        }

        $values = implode(", ", array_map(
            fn($field) =>  "$field = :$field",
            $fields
        ));

        $primaryKey = $this->column->getPrimaryKey();

        $query = "update $table set $values where $primaryKey = :$primaryKey";

        $stmt = ZenOrm::$pdo->prepare($query);
        $stmt->execute([
            ...$parameters,
            $primaryKey => $this->$primaryKey
        ]);
    }

    public function getMigrationQuery()
    {
        return $this->column->getQuery();
    }

    protected function getFieldAndPlaceholder(): array
    {
        $vars = array_keys(
            array_filter(
                get_object_vars($this),
                fn($var) => !($var instanceof Column)
            )
        );

        $fields = array_filter($vars, fn($var) => $var != $this->column->getPrimaryKey());

        $placeholders = array_map(fn($field) => ":$field", $fields);

        return [
            'fields' => $fields,
            'placeholders' => $placeholders
        ];
    }

    protected function getTable(): string
    {
        $tokens = explode('\\', get_called_class());
        $class = $tokens[sizeof($tokens) - 1];
        $words = preg_split("/(?=[A-Z])/", $class);

        $table = "";

        for ($i = 1; $i < sizeof($words); $i++) {
            $table .= $words[$i] . (array_key_last($words) !== $i ? "_" : "");
        }

        $table = strtolower($table);

        if (str_ends_with($table, 'ss') || str_ends_with($table, 'h')) {
            $table .= 'es';
        } elseif (str_ends_with($table, 'y')) {
            $table[strlen($table) - 1] = 'i';
            $table .= 'es';
        } else {
            $table .= 's';
        }

        return $table;
    }
}
