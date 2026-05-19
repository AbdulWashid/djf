<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmploymentType: string implements HasLabel
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case INTERNSHIP = 'internship';
    case TEMPORARY = 'temporary';
    case OTHER = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FULL_TIME => 'Full-time',
            self::PART_TIME => 'Part-time',
            self::CONTRACT => 'Contract',
            self::INTERNSHIP => 'Internship',
            self::TEMPORARY => 'Temporary',
            self::OTHER => 'Other',
        };
    }
    public static function toOptionsArray(): array
    {
        return array_reduce(self::cases(), function ($carry, EmploymentType $status) {
            $carry[$status->value] = $status->getLabel(); // Use ->value for the key and ->name for the label
            return $carry;
        }, []);
    }



}
