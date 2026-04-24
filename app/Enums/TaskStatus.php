<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog    = 'backlog';
    case Todo       = 'todo';
    case InProgress = 'in_progress';
    case Review     = 'review';
    case Done       = 'done';

    public function label(): string
    {
        return match($this) {
            self::Backlog    => 'Backlog',
            self::Todo       => 'To Do',
            self::InProgress => 'In Progress',
            self::Review     => 'Review',
            self::Done       => 'Done',
        };
    }

    public function publicLabel(): string
    {
        return $this->label();
    }

    public function color(): string
    {
        return match($this) {
            self::Backlog    => 'stone',
            self::Todo       => 'blue',
            self::InProgress => 'yellow',
            self::Review     => 'orange',
            self::Done       => 'green',
        };
    }

    public static function ordered(): array
    {
        return [
            self::Backlog,
            self::Todo,
            self::InProgress,
            self::Review,
            self::Done,
        ];
    }
}
