<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'Todo';
    case IN_PROGRESS = 'In Progress';
    case DONE = 'Done';

    /**
     * Get the label for the status.
     *
     * @return string The label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::TODO => 'Todo',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
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
