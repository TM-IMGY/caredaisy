<?php

namespace App\Service\Api\Invoice;

use App\Models\Watchdog;
use Carbon\Carbon;
use Log;

class WatchdogService
{
    public function update($target_function, $date)
    {
        $logFormat = '['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']';
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d H:i:s');
        }
        $watchdog = Watchdog::where('target_function', $target_function)->first();
        if (!$watchdog) {
            Log::info("${logFormat} target_function: ${target_function} はDBに存在しません。");
            return ['result' => 'NG'];
        }
        \DB::beginTransaction();
        try {
            $watchdog->update(['updated_at' => $date]);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error("${logFormat} DBの更新に失敗しました。: ".$e->getMessage());
            throw $e;
            return ['result' => 'NG'];
        }
        return ['result' => 'OK'];
    }
}
