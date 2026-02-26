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

    public function setName(string $name): self
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('O campo nome é obrigatório.');
        }

        $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);

        $this->attributes['name'] = $name;

        return $this;
    }

    public function setDocument(string $document): self
    {
        $document = preg_replace('/\D/', '', $document);

        if ($document === '') {
            throw new \InvalidArgumentException('O campo documento é obrigatório.');
        }

        $this->attributes['document'] = $document;

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