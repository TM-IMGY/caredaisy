<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use App\Models\Watchdog;
use Carbon\Carbon;
 
class WatchdogCommand extends Command
{
    const TRANSMISSION = 'TRANSMISSION';
    const CANCEL_TRANSMISSION = 'CANCEL_TRANSMISSION';
    const CHECK_STATUS = 'CHECK_STATUS';
    const GET_DOCUMENT = 'GET_DOCUMENT';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watchdog:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '伝送機能の処理が実行されているか確認する';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->logFormat = '['.__CLASS__.':';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now()->format('Y/m/d H:i:s');
        $facility = Watchdog::get();
        foreach ($facility as $value) {
            self::condition($value);
        }
    }
    /**
     * 死活確認
     */
    private function check($updateDate, $error_limit, $target_function)
    {
        if ($error_limit == 0) {
            return 0;
        }

        $now = Carbon::now();
        $calculatedUpdateDate = new Carbon($updateDate);
        $calculatedUpdateDate = $calculatedUpdateDate->addMinute($error_limit);
        if ($now->gt($calculatedUpdateDate)) {
            Log::error($this->logFormat . __FUNCTION__.':'.__LINE__.']'
            . "死活エラー 種類:${target_function} 更新日:${updateDate} 経過時間閾値:${error_limit}");
        }
    }
    /**
     * 死活確認実行条件
     */
    private function condition($value)
    {
        switch ($value->target_function) {
            case self::TRANSMISSION:
            case self::CANCEL_TRANSMISSION:
                $now = Carbon::now();
                if (1 <= $now->day && $now->day <= 10) {
                    $firstOfMonth = $now->firstOfMonth();
                    $updated_at = Carbon::create($value->updated_at);
                    if ($firstOfMonth->lt($updated_at)) {
                        self::check($value->updated_at, $value->error_limit, $value->target_function);
                    } else {
                        self::check($firstOfMonth, $value->error_limit, $value->target_function);
                    }
                }
                break;

            case self::CHECK_STATUS:
            case self::GET_DOCUMENT:
                self::check($value->updated_at, $value->error_limit, $value->target_function);
                break;

            default:
                Log::error($this->logFormat . __FUNCTION__.':'.__LINE__.']'. '死活エラー 想定していない種類です。 target_function:' . $value->target_function);
        }
    }
}
