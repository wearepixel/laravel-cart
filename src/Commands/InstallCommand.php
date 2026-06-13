<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'cart:install';

    protected $description = 'Publish the cart config and scaffold the app/Cart directory structure.';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'cart-config', '--ansi' => true]);

        static::scaffoldDirectories(app_path('Cart'));

        $this->info('Cart installed successfully.');
        $this->newLine();
        $this->line('  Next steps:');
        $this->line('    1. Review <comment>config/cart.php</comment> and set your preferred storage driver.');
        $this->line('    2. Run <comment>php artisan cart:make:coupon MyCoupon</comment> to create your first coupon.');
        $this->line('    3. Run <comment>php artisan cart:make:tax MyTax</comment> to add a tax rule.');
        $this->line('    4. Run <comment>php artisan cart:make:shipping MyShipping</comment> to add a shipping rate.');

        return self::SUCCESS;
    }

    public static function scaffoldDirectories(string $base): void
    {
        foreach (['Coupons', 'Tax', 'Shipping', 'Drivers'] as $subdir) {
            $path = "{$base}/{$subdir}";
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
}
