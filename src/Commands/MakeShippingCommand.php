<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Commands;

use Illuminate\Console\Command;

class MakeShippingCommand extends Command
{
    protected $signature = 'cart:make:shipping {name : The class name of the shipping rate}';

    protected $description = 'Create a new ShippingRate class in app/Cart/Shipping.';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            $this->error("Invalid class name: [{$name}]. Must be a valid PHP class name.");

            return self::FAILURE;
        }

        $directory = app_path('Cart/Shipping');
        $path = "{$directory}/{$name}.php";

        if (file_exists($path)) {
            $this->error("Shipping rate already exists: app/Cart/Shipping/{$name}.php");

            return self::FAILURE;
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_put_contents($path, static::stub($name)) === false) {
            $this->error("Failed to write file: {$path}");

            return self::FAILURE;
        }

        $this->info("Shipping rate created: app/Cart/Shipping/{$name}.php");

        return self::SUCCESS;
    }

    public static function stub(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Cart\Shipping;

use Wearepixel\Cart\Shipping\ShippingRate;

class {$className} extends ShippingRate
{
    protected string \$name = 'Standard Shipping';

    protected string \$value = '+10';

    protected string \$target = 'total';

    public function isApplicable(): bool
    {
        return true;
    }
}
PHP;
    }
}
