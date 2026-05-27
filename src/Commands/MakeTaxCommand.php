<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Commands;

use Illuminate\Console\Command;

class MakeTaxCommand extends Command
{
    protected $signature = 'cart:make:tax {name : The class name of the tax rule}';

    protected $description = 'Create a new TaxRule class in app/Cart/Tax.';

    public function handle(): int
    {
        $name = $this->argument('name');
        $directory = app_path('Cart/Tax');
        $path = "{$directory}/{$name}.php";

        if (file_exists($path)) {
            $this->error("Tax rule already exists: app/Cart/Tax/{$name}.php");
            return self::FAILURE;
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, static::stub($name));
        $this->info("Tax rule created: app/Cart/Tax/{$name}.php");

        return self::SUCCESS;
    }

    public static function stub(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Cart\Tax;

use Wearepixel\Cart\Tax\TaxRule;

class {$className} extends TaxRule
{
    protected string \$name = 'Tax Name';

    protected string \$value = '10%';

    protected string \$target = 'subtotal';

    public function isApplicable(): bool
    {
        return true;
    }
}
PHP;
    }
}
