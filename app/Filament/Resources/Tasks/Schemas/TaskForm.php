<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\Task;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('عنوان المهمة')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('التفاصيل')
                    ->rows(4),

                Select::make('assigned_to_user_id')
                    ->label('المسؤول')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),

                DateTimePicker::make('due_at')
                    ->label('موعد الاستحقاق')
                    ->seconds(false),

                Select::make('recurrence')
                    ->label('التكرار')
                    ->options([
                        Task::RECURRENCE_ONCE => 'مرة واحدة',
                        Task::RECURRENCE_DAILY => 'يوميًا',
                        Task::RECURRENCE_WEEKLY => 'أسبوعيًا',
                    ])
                    ->required()
                    ->default(Task::RECURRENCE_ONCE),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        Task::STATUS_PENDING => 'جديدة',
                        Task::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        Task::STATUS_COMPLETED => 'مكتملة',
                        Task::STATUS_CANCELLED => 'ملغاة',
                    ])
                    ->required()
                    ->default(Task::STATUS_PENDING),

                TextInput::make('notify_before_minutes')
                    ->label('التنبيه قبل الاستحقاق بالدقائق')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(1440),
            ]);
    }
}
