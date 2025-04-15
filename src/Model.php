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
        print_r($this->getTable());
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
