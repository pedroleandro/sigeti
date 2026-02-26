<?php

namespace App\Models;

use App\Core\AbstractModel;
use DateTimeImmutable;
use InvalidArgumentException;

class People extends AbstractModel
{
    protected string $table = 'peoples';

    protected array $fillable = [
        'name',
        'document'
    ];

    protected array $required = [
        'name'
    ];

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function getDocument(): ?string
    {
        return $this->attributes['document'] ?? null;
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

    public function validate(): void
    {
        foreach ($this->required as $field) {
            if (!isset($this->attributes[$field]) || trim($this->attributes[$field]) === '') {
                throw new InvalidArgumentException("O campo {$field} é obrigatório.");
            }
        }
    }

    public function setName(string $name): self
    {
        $this->attributes['name'] = trim($name);
        return $this;
    }

    public function setDocument(string $document): self
    {
        $this->attributes['document'] = preg_replace('/\D/', '', $document);
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

    public function user(): ?User
    {
        return (new User())
            ->where('people_id', '=', $this->attributes['id'])
            ->first();
    }
}