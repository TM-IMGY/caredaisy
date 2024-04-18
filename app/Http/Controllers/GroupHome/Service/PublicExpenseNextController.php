<?php

namespace App\Http\Controllers\GroupHome\Service;

use Auth;
use App\Lib\ApplicationBusinessRules\UseCases\Interactors\PublicExpenseNextInteractor;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\PublicExpenseNextGetRequest;
use App\Lib\ApplicationBusinessRules\UseCases\Exceptions\UnauthorizedAccessException;

/**
 * 公費の次回分機能のコントローラー。
 */
class PublicExpenseNextController extends Controller
{
    /**
     * @param PublicExpenseNextGetRequest $request
     * @param PublicExpenseNextInteractor $interactor
     */
    public function get(PublicExpenseNextGetRequest $request, PublicExpenseNextInteractor $interactor)
    {
        // 出力データを確保する変数。
        $outputData = null;

        try {
            $outputData = $interactor->handle(
                Auth::id(),
                $request->public_expense_information_id
            );
        } catch (UnauthorizedAccessException $e) {
            abort(400, 'この操作は許可されていません。');
        }

        return $outputData->getData();
    }
}
