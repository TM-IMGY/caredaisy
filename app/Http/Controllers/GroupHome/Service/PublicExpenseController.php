<?php

namespace App\Http\Controllers\GroupHome\Service;

use Auth;
use App\Lib\ApplicationBusinessRules\UseCases\Exceptions\UnauthorizedAccessException;
use App\Lib\ApplicationBusinessRules\UseCases\Interactors\PublicExpenseAcquisitionInteractor;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\PublicExpenseGetRequest;

/**
 * 公費の取得のコントローラー。
 */
class PublicExpenseController extends Controller
{
    /**
     * @param PublicExpenseGetRequest $request
     * @param PublicExpenseAcquisitionInteractor $interactor
     */
    public function get(PublicExpenseGetRequest $request, PublicExpenseAcquisitionInteractor $interactor)
    {
        // 出力データを確保する変数。
        $outputData = null;

        try {
            $outputData = $interactor->handle(Auth::id(), $request->public_expense_information_id);
        } catch (UnauthorizedAccessException $e) {
            abort(400, 'この操作は許可されていません。');
        }

        return $outputData->getData();
    }
}
