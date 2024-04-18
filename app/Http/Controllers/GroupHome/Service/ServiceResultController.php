<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\ServiceResultGetBenefitBillingRequest;
use App\Http\Requests\GroupHome\Service\ServiceResultGetFormUseCaseRequest;
use App\Http\Requests\GroupHome\Service\ServiceResultGetDataRequest;
use App\Http\Requests\GroupHome\Service\ServiceResultSaveRequest;
use App\Http\Requests\GroupHome\Service\ServiceResultUpdateApprovalRequest;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\FacilityNotFoundException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserBenefitRecordException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserCareRecordException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserServiceRecordException;
use App\Lib\ApplicationBusinessRules\UseCases\Interactors\NationalHealthBillingSaveInteractor;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\GetFormInputBoundary;
use App\Lib\ApplicationBusinessRules\UseCases\Interactors\NationalHealthBillingUpdateApprovalInteractor;
use App\Service\GroupHome\ServiceResultService;
use Exception;
use Log;

class ServiceResultController extends Controller
{
    /**
     * 給付額請求を返す
     * @param ServiceResultGetBenefitBillingRequest $request
     */
    public function getBenefitBilling(ServiceResultGetBenefitBillingRequest $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_id,
            'year' => $request->year,
            'month' => $request->month,
        ];

        // サービスが期待する形にパラメーターを整形
        $params['facility_user_id'] = [$params['facility_user_id']];

        $serviceResultService = new ServiceResultService();
        return $serviceResultService->getBenefitBilling($params);
    }

    /**
     * 国保連請求の様式データを返す。
     * @param GetFormInputBoundary $useCase
     * @param ServiceResultGetFormUseCaseRequest $request
     */
    public function getForm(GetFormInputBoundary $useCase, ServiceResultGetFormUseCaseRequest $request)
    {
        try {
            $outputData = $useCase->handle($request->facility_id, $request->facility_user_id, $request->year, $request->month);
            return $outputData->getData();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getFacilityUserTargetYm(ServiceResultGetDataRequest $request)
    {
        $serviceResultService = new ServiceResultService();
        $data = $serviceResultService->getFacilityUserTargetYm($request->facility_user_id, $request->year, $request->month);
        return $data;
    }

    public function save(ServiceResultSaveRequest $request, NationalHealthBillingSaveInteractor $useCase)
    {
        $facilityId = $request->facility_id;
        $facilityUserId = $request->facility_user_id;
        $serviceResults = $request->service_results;
        $year = $request->year;
        $month = $request->month;

        try {
            $useCase->handle($facilityId, $facilityUserId, $serviceResults, $year, $month);
        } catch (FacilityNotFoundException $e) {
            abort(422, '事業所マスタの取得に失敗しました。');
        } catch (InvalidFacilityUserBenefitRecordException $e) {
            abort(422, '給付率情報の取得に失敗しました。利用者の給付率情報が正しく登録されているか確認してください。');
        } catch (InvalidFacilityUserCareRecordException $e) {
            abort(422, '認定情報の取得に失敗しました。利用者の認定情報が正しく登録されているか確認してください。');
        } catch (InvalidFacilityUserServiceRecordException $e) {
            abort(422, '利用者のサービス種別の取得に失敗しました。');
        } catch (Exception $e) {
            abort(500, 'データベースへの接続に失敗しました。');
        }

        return [
            'facility_id' => $facilityId,
            'facility_user_id' => $facilityUserId,
            'service_results' => $serviceResults,
            'year' => $year,
            'month' => $month
        ];
    }

    /**
     * 国保連請求の承認状態を更新する。
     * @param NationalHealthBillingUpdateApprovalInteractor $interactor TODO: インターフェースを渡す。
     * @param ServiceResultUpdateApprovalRequest $request
     * @return array
     */
    public function updateApproval(
        NationalHealthBillingUpdateApprovalInteractor $interactor,
        ServiceResultUpdateApprovalRequest $request
    ): array {
        $interactor->handle($request->facility_user_id, $request->flag, $request->year, $request->month);
        return ['isSuccess' => true];
    }
}
