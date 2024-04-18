<?php

use Illuminate\Database\Seeder;
use App\Models\Invoice;

class InvoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = collect(['target_date']);
        $values = collect([
            ['2022-02-01'],
            ['2022-02-28'],
            ['2022-03-01'],
            ['2022-03-02']
        ]);
        $datas = $values->map(fn($x) => $columns->combine($x))->toArray();
        foreach ($datas as $key => $value) {
            factory(Invoice::class)->create([
                'target_date' => $value['target_date']
            ]);
        }

    }
}
