<?php

declare(strict_types=1);

namespace ZenOrm;

class Column
{
    protected string $table;
    protected string $currentColumn;
    protected array $columnArgs = [];

    public function __construct(string $model)
    {
        $token = explode('\\', $model);
        $this->table = strtolower($token[sizeof($token) - 1]) . "s";
    }

    public function getQuery()
    {
        $args = implode(', ', $this->columnArgs);

        return "drop table if exists {$this->table}; create table {$this->table} ($args);";
    }

    public function id(string $id = 'id')
    {
        $this->currentColumn = $id;

        $this->columnArgs[$id] = "$id int primary key auto_increment";

        return $this;
    }

    public function uuid(string $id = 'id')
    {
        $this->currentColumn = $id;

        $this->columnArgs[$id] = "$id char(36) primary key default (uuid())";

        return $this;
    }

    public function string(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = $size ? "$column varchar($size)" : "$column varchar(255)";

        return $this;
    }

    public function required(): Column
    {
        $this->columnArgs[$this->currentColumn] = $this->columnArgs[$this->currentColumn] . ' not null';

        return $this;
    }

    public function int(string $column, $size = null)
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = $size ? "$column int($size)" : "$column int";

        return $this;
    }

    public function float(string $column, $size = null)
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = $size ? "$column float($size)" : "$column float";

        return $this;
    }

    public function char(string $column, $size = null)
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = $size ? "$column char($size)" : "$column char";

        return $this;
    }


    public function text(string $column, $size = null)
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = $size ? "$column text($size)" : "$column text";

        return $this;
    }

    public function binary(string $column, $size = null)
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = $size ? "$column binary($size)" : "$column binary";

        return $this;
    }

    public function blob(string $column)
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = "$column blob";

        return $this;
    }

    public function timestamps()
    {
        $this->columnArgs['created_at'] = 'created_at timestamp default current_timestamp';

        $this->columnArgs['updated_at'] = 'updated_at timestamp default current_timestamp on update current_timestamp';

        return $this;
    }

    public function created_at()
    {
        $this->columnArgs['created_at'] = 'created_at timestamp default current_timestamp';

        return $this;
    }
}
