<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// use Illuminate\Contracts\Console\Kernel;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
// use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * 雛型
 */
// class ExampleTest extends DuskTestCase
// {
    // 処理ごとにDBを初期化したいなら有効化する
    //use DatabaseMigrations;

    /**
     * migration override
     * DBまたいでいるためfreshではエラーが出る
     * DatabaseMigrationsと共に有効化必要
     */
    // public function runDatabaseMigrations(): void
    // {
    //     $this->artisan('migrate:refresh --seed --env=dusk');
    //     $this->artisan('db:seed --class=TestDataSeeder --env=dusk');

    //     $this->app[Kernel::class]->setArtisan(null);

    //     $this->beforeApplicationDestroyed(function () {
    //         $this->artisan('migrate:rollback');

    //         RefreshDatabaseState::$migrated = false;
    //     });
    //     $this->migrated = true;
    // }

    // public function testBasicExample()
    // {
    // }
// }
