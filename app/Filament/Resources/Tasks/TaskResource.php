<?php

namespace App\Filament\Resources\Tasks;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\Tables\TasksTable;
use App\Models\Task;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TaskResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Task::class;

    protected static string $viewPermission = 'tasks.view';

    protected static string $managePermission = 'tasks.manage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'المهام اليومية والأسبوعية';

    protected static ?string $modelLabel = 'مهمة';

    protected static ?string $pluralModelLabel = 'المهام';

    protected static string|UnitEnum|null $navigationGroup = 'العقارات';

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
