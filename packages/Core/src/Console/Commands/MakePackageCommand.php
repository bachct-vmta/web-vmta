<?php

namespace Packages\Core\Src\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakePackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:package 
                            {name : The name of the package (e.g., Product, BlogPost)}
                            {--model= : Also create a model with the given name}
                            {--all : Create all files (model, controller, service, repository, migration)}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new package structure with optional model, controller, service, and repository';

    protected Filesystem $files;

    protected string $packageName;

    protected string $packageLower;

    protected string $packagePath;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->packageName = Str::studly($this->argument('name'));
        $this->packageLower = Str::lower($this->packageName);
        $this->packagePath = base_path("packages/{$this->packageName}");

        // Check if package already exists
        if ($this->files->isDirectory($this->packagePath) && ! $this->option('force')) {
            $this->error("Package [{$this->packageName}] already exists!");
            $this->info('Use --force to overwrite existing package.');

            return self::FAILURE;
        }

        $this->info("Creating package: {$this->packageName}");
        $this->newLine();

        // Create package structure
        $this->createDirectoryStructure();
        $this->createBaseFiles();

        // Check if we need to create model-related files
        $modelName = $this->option('model') ?: ($this->option('all') ? $this->packageName : null);

        if ($modelName) {
            $this->createModelFiles($modelName);
        }

        // Update main composer.json
        $this->updateComposerJson();

        $this->newLine();
        $this->info("✅ Package [{$this->packageName}] created successfully!");
        $this->newLine();

        $this->displayNextSteps($modelName);

        return self::SUCCESS;
    }

    /**
     * Create the directory structure for the package
     */
    protected function createDirectoryStructure(): void
    {
        $this->info('📁 Creating directory structure...');

        $directories = [
            'configs',
            'database/migrations',
            'resources/css',
            'resources/js',
            'resources/lang',
            'resources/views',
            'resources/views/admin',
            'routes',
            'src/Enums',
            'src/Http/Controllers',
            'src/Http/Middleware',
            'src/Http/Requests',
            'src/Models',
            'src/Providers',
            'src/Repositories',
            'src/Services',
            'src/Services/Actions',
            'src/Services/Interfaces',
        ];

        foreach ($directories as $dir) {
            $path = "{$this->packagePath}/{$dir}";
            if (! $this->files->isDirectory($path)) {
                $this->files->makeDirectory($path, 0755, true);
            }
        }

        $this->line('  Created directory structure');
    }

    /**
     * Create base package files
     */
    protected function createBaseFiles(): void
    {
        $this->info('📄 Creating base files...');

        // composer.json
        $this->createFromStub(
            'composer.json.stub',
            "{$this->packagePath}/composer.json"
        );
        $this->line('  Created composer.json');

        // ServiceProvider
        $this->createFromStub(
            'ServiceProvider.stub',
            "{$this->packagePath}/src/Providers/{$this->packageName}ServiceProvider.php"
        );
        $this->line("  Created {$this->packageName}ServiceProvider.php");

        // Routes
        $this->createFromStub(
            'routes/web.stub',
            "{$this->packagePath}/routes/web.php"
        );
        $this->line('  Created routes/web.php');

        // Permissions
        $this->createFromStub(
            'permissions.stub',
            "{$this->packagePath}/configs/permissions.php"
        );
        $this->line('  Created configs/permissions.php');

        // Readme
        $this->createFromStub(
            'Readme.stub',
            "{$this->packagePath}/Readme.md"
        );
        $this->line('  Created Readme.md');
    }

    /**
     * Create model-related files
     */
    protected function createModelFiles(string $modelName): void
    {
        $modelName = Str::studly($modelName);
        $tableName = Str::snake(Str::plural($modelName));

        $this->newLine();
        $this->info("📦 Creating model files for: {$modelName}");

        $replacements = $this->getReplacements();
        $replacements['{{model_name}}'] = $modelName;
        $replacements['{{table_name}}'] = $tableName;

        // Model
        $this->createFromStub(
            'Model.stub',
            "{$this->packagePath}/src/Models/{$modelName}.php",
            $replacements
        );
        $this->line("  Created Model: {$modelName}.php");

        // Controller
        $this->createFromStub(
            'Controller.stub',
            "{$this->packagePath}/src/Http/Controllers/{$modelName}Controller.php",
            $replacements
        );
        $this->line("  Created Controller: {$modelName}Controller.php");

        // Repository Interface
        $this->createFromStub(
            'RepositoryInterface.stub',
            "{$this->packagePath}/src/Repositories/{$modelName}RepositoryInterface.php",
            $replacements
        );
        $this->line("  Created Repository Interface: {$modelName}RepositoryInterface.php");

        // Repository
        $this->createFromStub(
            'Repository.stub',
            "{$this->packagePath}/src/Repositories/{$modelName}Repository.php",
            $replacements
        );
        $this->line("  Created Repository: {$modelName}Repository.php");

        // Service
        $this->createFromStub(
            'Service.stub',
            "{$this->packagePath}/src/Services/{$modelName}Service.php",
            $replacements
        );
        $this->line("  Created Service: {$modelName}Service.php");

        // Admin routes with model
        $this->createFromStub(
            'routes/admin.stub',
            "{$this->packagePath}/routes/admin.php",
            $replacements
        );
        $this->line('  Created routes/admin.php');

        // Migration
        $timestamp = date('Y_m_d_His');
        $this->createFromStub(
            'migration.stub',
            "{$this->packagePath}/database/migrations/{$timestamp}_create_{$tableName}_table.php",
            $replacements
        );
        $this->line("  Created migration: create_{$tableName}_table.php");

        // Update ServiceProvider to register repository binding
        $this->updateServiceProviderWithBindings($modelName);
    }

    /**
     * Update ServiceProvider with repository bindings
     */
    protected function updateServiceProviderWithBindings(string $modelName): void
    {
        $providerPath = "{$this->packagePath}/src/Providers/{$this->packageName}ServiceProvider.php";
        $content = $this->files->get($providerPath);

        $useStatements = "use Packages\\{$this->packageName}\\Src\\Repositories\\{$modelName}Repository;\nuse Packages\\{$this->packageName}\\Src\\Repositories\\{$modelName}RepositoryInterface;";

        $bindingCode = "\n        // Bind repository interface\n        \$this->app->bind(\n            {$modelName}RepositoryInterface::class,\n            {$modelName}Repository::class\n        );";

        // Add use statements after namespace
        $content = preg_replace(
            '/^(namespace Packages.*?;)$/m',
            "$1\n\n{$useStatements}",
            $content,
            1
        );

        // Add binding in register method
        $content = str_replace(
            '// Register bindings',
            "// Register bindings{$bindingCode}",
            $content
        );

        $this->files->put($providerPath, $content);
        $this->line('  Updated ServiceProvider with repository bindings');
    }

    /**
     * Create a file from stub template
     */
    protected function createFromStub(string $stubName, string $targetPath, ?array $replacements = null): void
    {
        $stubPath = base_path("packages/Core/stubs/package/{$stubName}");

        if (! $this->files->exists($stubPath)) {
            $this->error("Stub file not found: {$stubName}");

            return;
        }

        $content = $this->files->get($stubPath);
        $content = str_replace(
            array_keys($replacements ?? $this->getReplacements()),
            array_values($replacements ?? $this->getReplacements()),
            $content
        );

        $this->files->put($targetPath, $content);
    }

    /**
     * Get replacement values for stubs
     */
    protected function getReplacements(): array
    {
        return [
            '{{package_name}}' => $this->packageName,
            '{{package_lower}}' => $this->packageLower,
            '{{author_name}}' => config('app.author_name', 'Author'),
            '{{author_email}}' => config('app.author_email', 'author@example.com'),
            '{{model_name}}' => $this->packageName,
            '{{table_name}}' => Str::snake(Str::plural($this->packageName)),
        ];
    }

    /**
     * Update main composer.json with package autoload
     */
    protected function updateComposerJson(): void
    {
        $this->newLine();
        $this->info('⚙️  Updating composer.json...');

        $composerPath = base_path('composer.json');
        $composer = json_decode($this->files->get($composerPath), true);

        $namespace = "Packages\\{$this->packageName}\\Src\\";
        $path = "packages/{$this->packageName}/src/";

        // Check if already exists
        if (isset($composer['autoload']['psr-4'][$namespace])) {
            $this->line('  Namespace already exists in composer.json');

            return;
        }

        // Add to autoload
        $composer['autoload']['psr-4'][$namespace] = $path;

        // Sort alphabetically
        ksort($composer['autoload']['psr-4']);

        $this->files->put(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->line('  Added namespace to composer.json');
        $this->warn('  ⚠️  Remember to run: composer dump-autoload');
    }

    /**
     * Display next steps
     */
    protected function displayNextSteps(?string $modelName): void
    {
        $this->info('📋 Next steps:');
        $this->line('');
        $this->line('  1. Run: <comment>composer dump-autoload</comment>');
        $this->line('  2. Run: <comment>php artisan migrate</comment>');
        $this->line('');

        if ($modelName) {
            $this->line('  3. Create admin views in:');
            $this->line("     <comment>packages/{$this->packageName}/resources/views/admin/</comment>");
            $this->line('');
            $this->line("  4. Access admin at: <comment>/admin/{$this->packageLower}</comment>");
        }

        $this->line('');
        $this->line("  Package location: <comment>packages/{$this->packageName}/</comment>");
    }
}
