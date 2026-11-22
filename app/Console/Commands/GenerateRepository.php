<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository with service and interface in app repositories directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Define paths
        $repositoriesPath = app_path('Repositories');
        $interfacesPath = app_path('Repositories/Interfaces');
        $servicesPath = app_path('Repositories/Services');
        $interfacePath = "{$interfacesPath}/{$name}RepositoryInterface.php";
        $repositoryPath = "{$servicesPath}/{$name}Repository.php";

        // Ensure necessary directories exist
        File::ensureDirectoryExists($repositoriesPath, 0755, true);
        File::ensureDirectoryExists($interfacesPath, 0755, true);
        File::ensureDirectoryExists($servicesPath, 0755, true);

        // Check if files already exist
        if (File::exists($interfacePath)) {
            $this->error("Interface for $name already exists: {$interfacePath}");
            return;
        }

        if (File::exists($repositoryPath)) {
            $this->error("Repository for $name already exists: {$repositoryPath}");
            return;
        }

        // Interface content
        $interfaceContent = <<<PHP
        <?php

        namespace App\Repositories\Interfaces;

        interface {$name}RepositoryInterface
        {
            // Define your methods here
        }
        PHP;

        // Repository content
        $repositoryContent = <<<PHP
        <?php

        namespace App\Repositories\Services;

        use App\Repositories\Interfaces\\{$name}RepositoryInterface;

        class {$name}Repository implements {$name}RepositoryInterface
        {
            // Define your methods here
        }
        PHP;

        // Create files
        File::put($interfacePath, $interfaceContent);
        File::put($repositoryPath, $repositoryContent);

        $this->info("{$name}RepositoryInterface created successfully: {$interfacePath}");
        $this->info("{$name}Repository created successfully: {$repositoryPath}");

        $this->warn("Register your Repository in AppServiceProvider class inside the register() method:");
        $this->warn("\$this->app->bind({$name}RepositoryInterface::class, {$name}Repository::class);");
    }
}
