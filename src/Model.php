<?php

declare(strict_types=1);

namespace ZenOrm;

abstract class Model
{
    public function __construct(...$args)
    {
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }

    abstract public function schema(Column $column);

    public function save()
    {
        $table = $this->getTable();
        $vars = array_keys(get_object_vars($this));
        $fields = implode(', ', $vars);
        $placeHolders = implode(
            ', ',
            array_map(fn($field) => ":$field", $vars)
        );

        $query = "insert into $table ($fields) values ($placeHolders)";

        $stmt = ZenOrm::$pdo->prepare($query);
        $stmt->execute(get_object_vars($this));
    }

    private function getTable(): string
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
