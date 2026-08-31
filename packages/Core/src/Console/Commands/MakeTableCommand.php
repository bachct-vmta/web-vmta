<?php

namespace Packages\Core\Src\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:table 
                            {name : The name of the table class (e.g., UserTable)}
                            {--model= : The model class to use}
                            {--package= : Package name (default: Core)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new table class extending BaseTable';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $package = $this->option('package') ?? 'Core';
        $model = $this->option('model');

        // Ensure name ends with Table
        if (! Str::endsWith($name, 'Table')) {
            $name .= 'Table';
        }

        // Determine model name if not provided
        if (! $model) {
            $model = Str::replaceLast('Table', '', $name);
        }

        $packagePath = base_path("packages/{$package}/src/Tables");
        $filePath = "{$packagePath}/{$name}.php";

        // Create Tables directory if not exists
        if (! is_dir($packagePath)) {
            mkdir($packagePath, 0755, true);
        }

        // Check if file exists
        if (file_exists($filePath)) {
            $this->error("Table {$name} already exists at {$filePath}");

            return self::FAILURE;
        }

        // Generate the class content
        $namespace = "Packages\\{$package}\\Src\\Tables";
        $modelNamespace = "Packages\\{$package}\\Src\\Models\\{$model}";

        $stub = $this->getStub($namespace, $name, $model, $modelNamespace);

        file_put_contents($filePath, $stub);

        $this->info("Table class created successfully: {$filePath}");
        $this->line('');
        $this->line('Next steps:');
        $this->line("1. Edit {$name} to customize columns, filters, and actions");
        $this->line("2. Inject {$name} into your controller");
        $this->line('3. Render with: {"{$table}"} in your Blade view');

        return self::SUCCESS;
    }

    /**
     * Get the stub content
     */
    protected function getStub(string $namespace, string $className, string $model, string $modelNamespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Packages\\Core\\Src\\Tables\\BaseTable;
use {$modelNamespace};
use Packages\\Core\\Src\\Tables\\Columns\\TextColumn;
use Packages\\Core\\Src\\Tables\\Columns\\BadgeColumn;
use Packages\\Core\\Src\\Tables\\Columns\\BooleanColumn;
use Packages\\Core\\Src\\Tables\\Columns\\DateColumn;
use Packages\\Core\\Src\\Tables\\Filters\\SelectFilter;
use Packages\\Core\\Src\\Tables\\Filters\\BooleanFilter;
use Packages\\Core\\Src\\Tables\\Actions\\Action;
use Illuminate\\Database\\Eloquent\\Builder;

/**
 * {$model} Table definition
 * 
 * Usage:
 * public function index({$className} \$table)
 * {
 *     return view('your-view', ['table' => \$table]);
 * }
 */
class {$className} extends BaseTable
{
    protected ?string \$heading = '{$model} Management';
    protected int \$perPage = 15;
    protected ?string \$defaultSort = 'created_at';
    protected string \$defaultSortDirection = 'desc';

    /**
     * Define the model
     */
    protected function model(): string
    {
        return {$model}::class;
    }

    /**
     * Customize base query (optional)
     */
    protected function query(Builder \$query): Builder
    {
        return \$query;
    }

    /**
     * Define columns
     */
    protected function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->sortable(),
            
            DateColumn::make('created_at')
                ->label('Created')
                ->since()
                ->sortable(),
        ];
    }

    /**
     * Define filters (optional)
     */
    protected function filters(): array
    {
        return [
            // SelectFilter::make('status')->options([...]),
        ];
    }

    /**
     * Define row actions (optional)
     */
    protected function actions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->iconEdit()
                ->route('admin.{$this->getRoutePrefix()}.edit'),
            
            Action::make('delete')
                ->label('Delete')
                ->iconDelete()
                ->route('admin.{$this->getRoutePrefix()}.destroy')
                ->method('DELETE')
                ->confirm('Are you sure?')
                ->danger(),
        ];
    }

    /**
     * Get route prefix for actions
     */
    protected function getRoutePrefix(): string
    {
        return strtolower(class_basename(\$this->model())) . 's';
    }
}

PHP;
    }
}
