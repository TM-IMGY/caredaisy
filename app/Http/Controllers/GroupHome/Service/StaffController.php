<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\StaffHistory;

class StaffController extends Controller
{
    /**
     * スタッフ情報の取得
     */
    public function getHeader(Request $request)
    {
        $staff_row = Staff::find($request->staff_id);
        $staff_history_row = StaffHistory::find($request->staff_history_id);
        return ['staff_history' => $staff_history_row, 'staff' => $staff_row];
    }
}
