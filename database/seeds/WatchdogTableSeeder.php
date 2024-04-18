<?php

use Illuminate\Database\Seeder;
use App\Models\Watchdog;

class WatchdogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = collect(['target_function', 'error_limit']);
        $values = collect([
            ['TRANSMISSION', 15],
            ['CANCEL_TRANSMISSION', 15],
            ['CHECK_STATUS', 15],
            ['GET_DOCUMENT', 70]
        ]);
        $datas = $values->map(fn($x) => $columns->combine($x))->toArray();
        foreach ($datas as $key => $value) {
            factory(Watchdog::class)->create([
                'target_function' => $value['target_function']
            ]);
        }
    }
}
