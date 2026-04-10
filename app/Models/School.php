<?php

namespace App\Models;

use App\Core\AbstractModel;
use http\Exception\InvalidArgumentException;

class School extends AbstractModel
{
    protected string $table = "schools";

    protected string $primaryKey = 'id';

    protected array $fillable = [
        "name",
        "code",
        "address"
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "code" => "O campo CÓDIGO é obrigatório.",
        "address" => "O campo ENDEREÇO é obrigatório."
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public function getId(): ?int
    {
        return $this->attributes["id"];
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 15) {
            throw new \InvalidArgumentException("O nome da escola deve ter pelo menos 15 caracteres.");
        }

        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->attributes["name"];
    }

    public function setCode(string $code): void
    {
        $code = trim($code);

        if (strlen($code) !== 8) {
            throw new \InvalidArgumentException("O código da escola deve ter exatamente 8 caracteres.");
        }

        $this->attributes["code"] = $code;
    }

    public function getCode(): ?string
    {
        return $this->attributes["code"];
    }

    public function setAddress(string $address): void
    {
        $address = trim(strip_tags($address));

        if (strlen($address) < 20) {
            throw new \InvalidArgumentException("O endereço da escola deve ter pelo menos 20 caracteres.");
        }

        $this->attributes["address"] = $address;
    }

    public function getAddress(): ?string
    {
        return $this->attributes["address"];
    }

    //Atualizado
    public function findSchoolByCode(string $code): ?self
    {
        return (new static())->where("code", "=", $code)->first();
    }

    public function findSchoolByName(string $name): ?self
    {
        return (new static())->where("name", "=", $name)->first();
    }

    public function existsSchoolByName(string $name, ?int $ignoreId = null): bool
    {
        $query = (new static())->where("name", "=", $name);

        if ($ignoreId) {
            $query->where("id", "!=", $ignoreId);
        }

        return $query->first() !== null;
    }

    public function existsSchoolByCode(string $code, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE code = :code";
        $params = ['code' => $code];

        if ($ignoreId) {
            $sql .= " AND id != :ignore_id";
            $params['ignore_id'] = $ignoreId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return (int)$statement->fetchColumn() > 0;
    }

    public function existsUsers(): bool
    {
        return (new SchoolUser())->where("school_id", "=", $this->getId())->count() > 0;
    }

    public function existsTickets(): bool
    {
        return (new Ticket())->where("school_id", "=", $this->getId())->count() > 0;
    }

    public function validateBusinessRule(?int $ignoreId = null): array
    {
        $errors = [];

        if ($this->existsSchoolByName($this->getName(), $ignoreId)) {
            $errors[] = "Já existe uma escola com esse mesmo nome.";
        }

        if ($this->existsSchoolByCode($this->getCode(), $ignoreId)) {
            $errors[] = "Já existe uma escola com esse mesmo código.";
        }

        return $errors;
    }
}