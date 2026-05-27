<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Commands;

use Illuminate\Console\Command;

class MakeCouponCommand extends Command
{
    protected $signature = 'cart:make:coupon {name : The class name of the coupon}';

    protected $description = 'Create a new Coupon class in app/Cart/Coupons.';

    public function handle(): int
    {
        $name = $this->argument('name');
        $directory = app_path('Cart/Coupons');
        $path = "{$directory}/{$name}.php";

        if (file_exists($path)) {
            $this->error("Coupon already exists: app/Cart/Coupons/{$name}.php");
            return self::FAILURE;
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, static::stub($name));
        $this->info("Coupon created: app/Cart/Coupons/{$name}.php");

        return self::SUCCESS;
    }

    public static function stub(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Cart\Coupons;

use Wearepixel\Cart\Coupons\Coupon;

class {$className} extends Coupon
{
    protected string \$code = 'COUPON_CODE';

    protected string \$value = '-10%';

    protected string \$target = 'subtotal';

    public function isValid(): bool
    {
        return true;
    }
}
PHP;
    }
}
