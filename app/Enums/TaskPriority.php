<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'Low';
    case MEDIUM = 'Medium';
    case HIGH = 'High';

    /**
     * Get the label for the priority.
     *
     * @return string The label for the priority.
     */
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High'
        };
    }

    /**
     * Get the status values.
     *
     * @return array<string> The status values.
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
