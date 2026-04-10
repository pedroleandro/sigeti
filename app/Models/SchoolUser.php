<?php

namespace App\Models;

use App\Core\AbstractModel;

class SchoolUser extends AbstractModel
{
    protected string $table = "school_users";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "school_id",
        "user_id",
        "shift"
    ];

    protected array $required = [
        "school_id" => "O campo ESCOLA é obrigatório.",
        "user_id" => "O campo USER é obrigatório.",
        "shift" => "O campo TURNO é obrigatório.",
    ];

    protected bool $timestamps = false;

    protected bool $softDelete = false;

    public const MORNING = "manha";
    public const AFTERNOON = "tarde";

    public const WHOLE = "integral";

    private const SHIFTS = [
        self::MORNING,
        self::AFTERNOON,
        self::WHOLE
    ];

    public function getId(): ?int
    {
        return $this->attributes["id"];
    }

    public function setSchoolId(int $schoolId): void
    {
        $this->attributes["school_id"] = $schoolId;
    }

    public function getSchoolId(): int
    {
        return $this->attributes["school_id"];
    }

    public function setUserId(int $userId): void
    {
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): int
    {
        return $this->attributes["user_id"];
    }

    public function setShift(?string $shift): void
    {
        $shift = $shift ?? self::WHOLE;
        if (!in_array($shift, self::SHIFTS)) {
            throw new \InvalidArgumentException("O turno é inválido,");
        }

        $this->attributes["shift"] = $shift;

    }

    public function getShift(): ?string
    {
        return $this->attributes["shift"];
    }

    public function school(): ?School
    {
        return School::find($this->getSchoolId());
    }

    public function user(): ?User
    {
        return User::find($this->getUserId());
    }

    public function findBySchoolAndUser(int $schoolId, int $userId): ?self
    {
        return (new static())
            ->where("school_id", "=", $schoolId)
            ->where("user_id", "=", $userId)
            ->first();
    }

    public static function linksByUser(int $userId): ?array
    {
        return (new static())->where("user_id", "=", $userId)->get();
    }

    public static function validateSchoolUserLinks(array $links): ?array
    {
        if (empty($links)) {
            return ["Vincule o professor a pelo menos uma escola."];
        }

        $links = self::validateSchools($links);

        if (empty($links)) {
            return ["Nenhuma escola válida foi informada."];
        }

        $errors = [];

        $shifts = [];

        foreach ($links as $link) {
            if (!empty($link['shift'])) {
                $shifts[] = $link['shift'];
            }
        }

        if (in_array(self::WHOLE, $shifts, true) && count($shifts) > 1) {
            $errors[] = "Se o professor é do turno INTEGRAL, não pode selecionar outros turnos.";
        }

        $shiftsCount = array_count_values($shifts);

        foreach ($shiftsCount as $shift => $count) {
            if ($count > 1) {
                $value = match ($shift) {
                    self::MORNING => "MANHÃ",
                    self::AFTERNOON => "TARDE",
                    self::WHOLE => "INTEGRAL"
                };

                $errors[] = "O turno {$value} não pode ser usado em mais de uma escola.";
            }
        }

        return $errors;
    }

    public static function validateSchools(array $links): array
    {
        $validSchools = [];

        foreach ($links as $link) {
            $schoolId = $link["school_id"] ?? 0;

            if (School::find((int)$schoolId)) {
                $validSchools[] = $link;
            }
        }

        return $validSchools;
    }
}