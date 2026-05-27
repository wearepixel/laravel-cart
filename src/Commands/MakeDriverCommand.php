<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Commands;

use Illuminate\Console\Command;

class MakeDriverCommand extends Command
{
    protected $signature = 'cart:make:driver {name : The class name of the custom cart driver}';

    protected $description = 'Create a new CartDriver implementation in app/Cart/Drivers.';

    public function handle(): int
    {
        $name = $this->argument('name');
        $directory = app_path('Cart/Drivers');
        $path = "{$directory}/{$name}.php";

        if (file_exists($path)) {
            $this->error("Driver already exists: app/Cart/Drivers/{$name}.php");
            return self::FAILURE;
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, static::stub($name));
        $this->info("Driver created: app/Cart/Drivers/{$name}.php");
        $this->line("  Register it in config/cart.php under <comment>drivers</comment>.");

        return self::SUCCESS;
    }

    public static function stub(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Cart\Drivers;

use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class {$className} implements CartDriver
{
    private string \$sessionKey;

    public function __construct(string \$sessionKey)
    {
        \$this->sessionKey = \$sessionKey;
    }

    public function getSessionKey(): string
    {
        return \$this->sessionKey;
    }

    public function setSessionKey(string \$sessionKey): void
    {
        \$this->sessionKey = \$sessionKey;
    }

    public function getDriverName(): string
    {
        return 'custom';
    }

    public function getItems(): array
    {
        // TODO: retrieve items from your storage backend
        return [];
    }

    public function putItems(array \$items): void
    {
        // TODO: persist items to your storage backend
    }

    public function getConditions(): array
    {
        // TODO: retrieve conditions from your storage backend
        return [];
    }

    public function putConditions(array \$conditions): void
    {
        // TODO: persist conditions to your storage backend
    }

    public function clearItems(): void
    {
        // TODO: remove only items from your storage backend
    }

    public function flush(): void
    {
        // TODO: remove both items and conditions from your storage backend
    }

    public function getSessionModel(): mixed
    {
        return null;
    }
}
PHP;
    }
}
