<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Editor => 'Editor',
        };
    }
}
