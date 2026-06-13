<?php

use Wearepixel\Cart\Commands\InstallCommand;

describe('InstallCommand', function () {
    test('scaffoldDirectories() creates the expected subdirectories', function () {
        $base = sys_get_temp_dir() . '/cart-install-' . uniqid();

        InstallCommand::scaffoldDirectories($base);

        expect(is_dir("{$base}/Coupons"))->toBeTrue();
        expect(is_dir("{$base}/Tax"))->toBeTrue();
        expect(is_dir("{$base}/Shipping"))->toBeTrue();
        expect(is_dir("{$base}/Drivers"))->toBeTrue();

        foreach (["{$base}/Coupons", "{$base}/Tax", "{$base}/Shipping", "{$base}/Drivers", $base] as $dir) {
            @rmdir($dir);
        }
    });

    test('scaffoldDirectories() is idempotent - does not throw if dirs already exist', function () {
        $base = sys_get_temp_dir() . '/cart-install-idem-' . uniqid();

        InstallCommand::scaffoldDirectories($base);
        InstallCommand::scaffoldDirectories($base);

        expect(is_dir("{$base}/Coupons"))->toBeTrue();

        foreach (["{$base}/Coupons", "{$base}/Tax", "{$base}/Shipping", "{$base}/Drivers", $base] as $dir) {
            @rmdir($dir);
        }
    });
});
