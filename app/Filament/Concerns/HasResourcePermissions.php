<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasResourcePermissions
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->can(static::$viewPermission) ?? false;
    }


    public static function canCreate(): bool
    {
        return auth()->user()?->can(static::$managePermission) ?? false;
    }


    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(static::$managePermission) ?? false;
    }


    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(static::$managePermission) ?? false;
    }


    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can(static::$managePermission) ?? false;
    }
}