<?php

namespace App\Models;

use App\Core\AbstractModel;
use DateTimeImmutable;

class User extends AbstractModel
{
    protected string $table = 'users';

    protected string $primaryKey = 'people_id';

    protected array $fillable = [
        'email',
        'password',
        'status',
        'last_login_at'
    ];

    public function getPeopleId(): ?int
    {
        return $this->attributes['people_id'] ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    public function getStatus(): ?string
    {
        return $this->attributes['status'] ?? null;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return isset($this->attributes['last_login_at'])
            ? new \DateTimeImmutable($this->attributes['last_login_at'])
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

    public function setEmail(string $email): self
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('E-mail inválido');
        }

        $this->attributes['email'] = strtolower($email);

        return $this;
    }

    public function setPassword(string $password): self
    {
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Password must have at least 6 characters.');
        }

        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);

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
        $this->attributes['last_login_at'] = date('Y-m-d H:i:s');
        return $this;
    }

    public function delete(): bool
    {
        $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
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