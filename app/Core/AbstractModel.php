<?php

namespace App\Core;

use DateTimeImmutable;
use DateTimeZone;
use PDO;

abstract class AbstractModel
{
    protected PDO $connection;

    protected string $table = "";
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    protected array $required = [];

    protected bool $timestamps = true;

    protected array $attributes = [];

    protected array $wheres = [];

    protected array $params = [];

    protected ?int $limitValue = null;

    protected ?int $offsetValue = null;

    protected ?string $orderByColumn = null;

    protected string $orderDirection = 'ASC';

    protected bool $exists = false;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    public function fill(array $data): self
    {
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                if (method_exists($this, $setter)) {
                    $this->$setter($data[$field]);
                } else {
                    $this->attributes[$field] = $data[$field];
                }
            }
        }

        return $this;
    }

    public function save(): bool
    {
        return $this->exists ? $this->performUpdate() : $this->performInsert();
    }

    public function performInsert(): bool
    {
        if ($this->timestamps) {
            $now = $this->now();

            $this->attributes['created_at'] = $now;
            $this->attributes['updated_at'] = $now;
        }

        $columns = array_keys($this->attributes);

        $placeholders = [];
        foreach ($columns as $column) {
            $placeholders[] = ':' . $column;
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $statement = $this->connection->prepare($sql);

        $success = $statement->execute($this->attributes);

        if ($success) {
            $lastId = $this->connection->lastInsertId();

            if ($lastId && !isset($this->attributes[$this->primaryKey])) {
                $this->attributes[$this->primaryKey] = (int)$lastId;
            }

            $this->exists = true;
        }

        return $success;
    }

    public function performUpdate(): bool
    {
        if ($this->timestamps) {
            $this->attributes['updated_at'] = $this->now();
        }

        $fields = [];

        foreach ($this->attributes as $column => $value) {
            if ($column !== $this->primaryKey) {
                $fields[] = "{$column} = :{$column}";
            }
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :%s",
            $this->table,
            implode(', ', $fields),
            $this->primaryKey,
            $this->primaryKey
        );

        $statement = $this->connection->prepare($sql);

        return $statement->execute($this->attributes);
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s = :id",
            $this->table,
            $this->primaryKey
        );

        $statement = $this->connection->prepare($sql);

        $success = $statement->execute([
            'id' => $this->attributes[$this->primaryKey]
        ]);

        if ($success) {
            $this->exists = false;
        }

        return $success;
    }

    public static function find(int $id): ?static
    {
        $instance = new static();

        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = :id LIMIT 1",
            $instance->table,
            $instance->primaryKey
        );

        $statement = $instance->connection->prepare($sql);
        $statement->execute(['id' => $id]);

        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        $data = $statement->fetch();

        return $data ? static::hydrate($data) : null;
    }

    public static function all(): array
    {
        $instance = new static();

        $sql = "SELECT * FROM {$instance->table}";

        $statement = $instance->connection->query($sql);

        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        $models = [];

        while ($row = $statement->fetch()) {
            $models[] = static::hydrate($row);
        }

        return $models;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $param = $column . count($this->params);

        $this->wheres[] = "{$column} {$operator} :{$param}";
        $this->params[$param] = $value;

        return $this;
    }

    public function first(): ?static
    {
        $this->limit(1);

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if ($this->orderByColumn) {
            $sql .= " ORDER BY {$this->orderByColumn} {$this->orderDirection}";
        }

        $sql .= " LIMIT 1";

        $statement = $this->connection->prepare($sql);
        $statement->execute($this->params);

        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $data = $statement->fetch();

        return $data ? static::hydrate($data) : null;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderByColumn = $column;
        $this->orderDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if ($this->orderByColumn) {
            $sql .= " ORDER BY {$this->orderByColumn} {$this->orderDirection}";
        }

        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($this->params);

        return $statement->fetchAll();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    protected static function hydrate(array $data): static
    {
        $instance = new static();
        $instance->attributes = $data;
        $instance->exists = true;

        return $instance;
    }

    protected function now(): string
    {
        $timezone = new DateTimeZone($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');
        $now = new DateTimeImmutable('now', $timezone);

        return $now->format('Y-m-d H:i:s');
    }
}