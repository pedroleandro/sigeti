<?php

namespace App\Models;

use App\Core\AbstractModel;
use DateTimeImmutable;
use InvalidArgumentException;

class User extends AbstractModel
{
    protected string $table = 'users';

    protected string $primaryKey = 'people_id';

    protected array $fillable = [
        'people_id',
        'email',
        'password',
        'status'
    ];

    public function fill(array $data, bool $isNewPassword = true): self
    {
        foreach ($data as $field => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

            if (method_exists($this, $setter)) {
                if ($field === 'password') {
                    $this->$setter($value, $isNewPassword);
                } else {
                    $this->$setter($value);
                }
            }
        }

        return $this;
    }

    public function getPeopleId(): ?int
    {
        return $this->attributes['people_id'] ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    public function getPassword(): ?string
    {
        return $this->attributes['password'] ?? null;
    }

    public function getStatus(): ?string
    {
        return $this->attributes['status'] ?? null;
    }

    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return isset($this->attributes['last_login_at'])
            ? new DateTimeImmutable($this->attributes['last_login_at'])
            : null;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return isset($this->attributes['created_at'])
            ? new DateTimeImmutable($this->attributes['created_at'])
            : null;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return isset($this->attributes['updated_at'])
            ? new DateTimeImmutable($this->attributes['updated_at'])
            : null;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return isset($this->attributes['deleted_at'])
            ? new DateTimeImmutable($this->attributes['deleted_at'])
            : null;
    }

    public function setPeopleId(int $peopleId): self
    {
        if ($peopleId <= 0) {
            throw new InvalidArgumentException('O ID da pessoa deve ser um número inteiro positivo.');
        }

        $person = People::find($peopleId);
        if (!$person) {
            throw new InvalidArgumentException("Pessoa com ID {$peopleId} não encontrada.");
        }

        $this->attributes['people_id'] = $peopleId;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido');
        }

        $this->attributes['email'] = strtolower($email);

        return $this;
    }

    public function setPassword(string $password, bool $isNew = true): self
    {
        $password = trim($password);

        if ($isNew) {
            if (strlen($password) < 8) {
                throw new InvalidArgumentException('A senha deve ter no mínimo 8 caracteres.');
            }

            $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            if (!password_get_info($password)['algo']) {
                if (strlen($password) < 8) {
                    throw new InvalidArgumentException('A senha deve ter no mínimo 8 caracteres.');
                }
                $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $this->attributes['password'] = $password;
            }
        }

        return $this;
    }

    public function setStatus(string $status): self
    {
        $allowed = ['ativo', 'inativo'];

        if (!in_array($status, $allowed)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        $this->attributes['status'] = $status;

        return $this;
    }

    public function markAsLoggedIn(): self
    {
        $this->attributes['last_login_at'] = $this->now();
        return $this;
    }

    public function delete(): bool
    {
        $this->attributes['deleted_at'] = $this->now();
        return $this->save();
    }

    public function restore(): bool
    {
        $this->attributes['deleted_at'] = null;
        return $this->save();
    }

    public function isDeleted(): bool
    {
        return !empty($this->attributes['deleted_at']);
    }

    public function people(): ?People
    {
        return (new People())
            ->where('id', '=', $this->attributes['people_id'])
            ->first();
    }
}