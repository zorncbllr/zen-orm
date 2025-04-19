<?php

declare(strict_types=1);

namespace ZenOrm;

class Column
{
    protected string $table;
    protected string $currentColumn;
    protected array $columnArgs = [];
    protected array $foreignKeys = [];

    protected string $primaryKey = 'id';

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getQuery(): string
    {
        $args = implode(', ', [...$this->columnArgs, ...$this->foreignKeys]);

        return "drop table if exists {$this->table}; create table {$this->table} ($args);";
    }

    public function foreignIdFor(string $class): Column
    {
        $instance = new $class;
        $table = $instance->getTable();
        $primaryKey = $instance->getColumnDefinition()->getPrimaryKey();

        $this->currentColumn = $primaryKey;

        $keyType = $instance->getColumnDefinition()->columnArgs[$primaryKey];

        if (preg_match("/int/", $keyType)) {
            $this->columnArgs[$primaryKey] = str_replace("primary key auto_increment", "not null", $keyType);
        } elseif (preg_match("/uuid/", $keyType)) {
            $this->columnArgs[$primaryKey] = str_replace("primary key default (uuid())", "not null", $keyType);
        }

        array_push(
            $this->foreignKeys,
            "foreign key ($primaryKey) references {$table} ($primaryKey)"
        );

        return $this;
    }

    public function id(string $id = 'id'): Column
    {
        $this->currentColumn = $id;
        $this->primaryKey = $id;

        $this->columnArgs[$id] = "$id int primary key auto_increment";

        return $this;
    }

    public function uuid(string $id = 'id'): Column
    {
        $this->currentColumn = $id;
        $this->primaryKey = $id;

        $this->columnArgs[$id] = "$id char(36) primary key default (uuid())";

        return $this;
    }

    public function string(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = ($size ? "$column varchar($size)" : "$column varchar(255)") . ' not null';

        return $this;
    }

    public function nullable(): Column
    {
        $this->columnArgs[$this->currentColumn] = str_replace(' not null', '', $this->columnArgs[$this->currentColumn]);

        return $this;
    }

    public function unique(): Column
    {
        $this->columnArgs[$this->currentColumn] = $this->columnArgs[$this->currentColumn] . ' unique';

        return $this;
    }


    public function int(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = ($size ? "$column int($size)" : "$column int") . ' not null';

        return $this;
    }

    public function float(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = ($size ? "$column float($size)" : "$column float") . ' not null';

        return $this;
    }

    public function char(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = ($size ? "$column char($size)" : "$column char") . ' not null';

        return $this;
    }


    public function text(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = ($size ? "$column text($size)" : "$column text") . ' not null';

        return $this;
    }

    public function binary(string $column, $size = null): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = ($size ? "$column binary($size)" : "$column binary") . ' not null';

        return $this;
    }

    public function blob(string $column): Column
    {
        $this->currentColumn = $column;

        $this->columnArgs[$column] = "$column blob not null";

        return $this;
    }

    public function timestamps(): Column
    {
        $this->columnArgs['created_at'] = 'created_at timestamp default current_timestamp';

        $this->columnArgs['updated_at'] = 'updated_at timestamp default current_timestamp on update current_timestamp';

        return $this;
    }

    public function created_at(): Column
    {
        $this->columnArgs['created_at'] = 'created_at timestamp default current_timestamp';

        return $this;
    }
}
