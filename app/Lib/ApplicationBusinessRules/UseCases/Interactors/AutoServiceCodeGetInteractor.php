<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\Interactors;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\CareRewardHistoryRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUser\StayOutRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserCareRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserIndependenceRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserServiceRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceItemCodesRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceCodeConditionalBranchRepositoryInterface;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\CareRewardNotFoundException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserCareRecordException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserServiceRecordException;
use App\Lib\ApplicationBusinessRules\OutputData\AutoServiceCodeOutputData;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\AutoServiceCodeGetInputBoundary;
use App\Lib\DomainService\ServiceCodeGetSpecification;
use App\Lib\DomainService\ResultFlagSpecification;
use App\Lib\DomainService\ScheduleFlagSpecification;
use Exception;
use Log;

/**
 * 自動サービスコードのユースケースの実装。
 */
class AutoServiceCodeGetInteractor implements AutoServiceCodeGetInputBoundary
{
    private CareRewardHistoryRepositoryInterface $careRewardHistoryRepository;

    private FacilityRepositoryInterface $facilityRepository;

    private FacilityUserCareRecordRepositoryInterface $facilityUserCareRecordRepository;

    private FacilityUserIndependenceRepositoryInterface $facilityUserIndependenceRepository;

    private FacilityUserRepositoryInterface $facilityUserRepository;

    private FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository;

    private ServiceCodeConditionalBranchRepositoryInterface $serviceCodeConditionalBranchRepository;

    private ServiceItemCodesRepositoryInterface $serviceItemCodesRepository;

    private StayOutRecordRepositoryInterface $stayOutRecordRepository;

    public function __construct(
        CareRewardHistoryRepositoryInterface $careRewardHistoryRepository,
        FacilityRepositoryInterface $facilityRepository,
        FacilityUserCareRecordRepositoryInterface $facilityUserCareRecordRepository,
        FacilityUserIndependenceRepositoryInterface $facilityUserIndependenceRepository,
        FacilityUserRepositoryInterface $facilityUserRepository,
        FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository,
        ServiceCodeConditionalBranchRepositoryInterface $serviceCodeConditionalBranchRepository,
        ServiceItemCodesRepositoryInterface $serviceItemCodesRepository,
        StayOutRecordRepositoryInterface $stayOutRecordRepository
    ) {
        $this->careRewardHistoryRepository = $careRewardHistoryRepository;
        $this->facilityRepository = $facilityRepository;
        $this->facilityUserCareRecordRepository = $facilityUserCareRecordRepository;
        $this->facilityUserIndependenceRepository = $facilityUserIndependenceRepository;
        $this->facilityUserRepository = $facilityUserRepository;
        $this->facilityUserServiceRecordRepository = $facilityUserServiceRecordRepository;
        $this->serviceCodeConditionalBranchRepository = $serviceCodeConditionalBranchRepository;
        $this->serviceItemCodesRepository = $serviceItemCodesRepository;
        $this->stayOutRecordRepository = $stayOutRecordRepository;
    }

    /**
     * 施設利用者に対象年月に提供したと考えられるサービスコードを返す。
     */
    public function handle(int $facilityId, int $facilityUserId, int $year, int $month): AutoServiceCodeOutputData
    {
        $careRewardHistory = null;
        $conditionalBranch = null;
        $facilityUser = null;
        $facilityUserCareRecord = null;
        $facilityUserIndependence = null;
        $facilityUserServiceRecord = null;
        $stayOutRecord = null;
        $logParameter = "facilityId is ${facilityId}, facilityUserId is ${facilityUserId}, and ${year}-${month}";
        try {
            // TODO: 施設利用者が見つからないのは処理上ありえないが可能なら実装する。
            $facilityUser = $this->facilityUserRepository->find($facilityUserId, $year, $month);

            $facilityUserCareRecord = $this->facilityUserCareRecordRepository->find($facilityUserId, $year, $month);
            if (!$facilityUserCareRecord->hasRecord()) {
                // TODO: 一時対応
                throw new InvalidFacilityUserCareRecordException('', -10);
            }

            // 自立度はない者もいる。
            $facilityUserIndependence = $this->facilityUserIndependenceRepository->find($facilityUserId, $year, $month);

            $facilityUserServiceRecord = $this->facilityUserServiceRecordRepository->find(
                $facilityUserId,
                $year,
                $month
            );
            if (!$facilityUserServiceRecord->hasRecord()) {
                // TODO: 一時対応
                throw new InvalidFacilityUserServiceRecordException('', -4);
            }

            $latestService = $facilityUserServiceRecord->getLatest();

            $careRewardHistory = $this->careRewardHistoryRepository->find(
                $latestService->getServiceId(),
                $year,
                $month
            );

            $conditionalBranch = $this->serviceCodeConditionalBranchRepository->find(
                $latestService->getServiceTypeCode()->getServiceTypeCode(),
                $year,
                $month
            );

            // 外泊の履歴を取得する。
            $stayOutRecord = $this->stayOutRecordRepository->find($facilityUserId);

            // TODO: ここでのtry/catchをやめて、throwされたカスタム例外クラス内のreport/renderメソッドでのロギング/レスポンスに作り変え
            // TODO: ユースケースの例外処理として適切ではないので修正する。
        } catch (InvalidFacilityUserCareRecordException $e) {
            Log::channel('app')->info('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']'.$logParameter);
            abort(422, '認定情報の取得に失敗しました。利用者の認定情報が正しく登録されているか確認してください。');
        } catch (InvalidFacilityUserServiceRecordException $e) {
            Log::channel('app')->info('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']'.$logParameter);
            abort(422, '利用者のサービス種別の取得に失敗しました。');
        } catch (CareRewardNotFoundException $e) {
            Log::channel('app')->info('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']'.$logParameter);
            abort(422, '有効な加算情報が見つかりません。加算状況画面にて入力内容をご確認ください。');
        } catch (Exception $e) {
            Log::channel('app')->info('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']'.$logParameter);
            abort(500, 'データベースへの接続に失敗しました。');
        }

        // サービスコードの取得の仕様から項目コードを取得する。
        $autoServiceCodeResults = ServiceCodeGetSpecification::getServiceItemCodes(
            $careRewardHistory,
            $facilityUser,
            $facilityUserCareRecord,
            $facilityUserIndependence,
            $facilityUserServiceRecord,
            $conditionalBranch,
            $stayOutRecord,
            $year,
            $month
        );

        // 事業所を確保する変数。
        $facility = null;
        // サービス項目コードを確保する変数。
        $serviceItemCodes = null;

        try {
            // 事業所を取得する。
            // TODO: 事業所が見つからないのは処理上ありえないが可能なら実装する。
            $facility = $this->facilityRepository->find($facilityId);

            // サービス項目コードを取得する。
            $serviceItemCodes = $this->serviceItemCodesRepository->getByServiceItemCodes(
                $latestService->getServiceTypeCode()->getServiceTypeCode(),
                $autoServiceCodeResults,
                $year,
                $month
            );
        // TODO: ユースケースの例外処理として適切ではないので修正する。
        } catch (Exception $e) {
            Log::channel('app')->info('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']'.$logParameter);
            abort(500, 'データベースへの接続に失敗しました。');
        }

        // 出力データを作成する。
        $outputData = new AutoServiceCodeOutputData();
        $all = $serviceItemCodes->getAll();
        foreach ($all as $serviceItemCode) {
            $scheduleFlag = ScheduleFlagSpecification::getByTargetYm($facilityUser, $serviceItemCode, $year, $month);

            $resultFlag = ResultFlagSpecification::get(
                $careRewardHistory,
                $facilityUser,
                $facilityUserCareRecord,
                $facilityUserServiceRecord,
                $conditionalBranch,
                $serviceItemCode,
                $stayOutRecord,
                $year,
                $month
            );

            $outputData->addData(
                $resultFlag->getDateDailyRate(),
                $resultFlag->getDateDailyRateOneMonthAgo(),
                $resultFlag->getDateDailyRateTwoMonthAgo(),
                $scheduleFlag,
                $facility->getFacilityNameKanji(),
                $facility->getFacilityNumber(),
                $resultFlag->getServiceCountDate(),
                $serviceItemCode->getServiceItemCode(),
                $serviceItemCode->getServiceItemCodeId(),
                $serviceItemCode->getServiceItemName(),
                $serviceItemCode->getServiceTypeCode(),
                "${year}-${month}-01",
                $serviceItemCode->getServiceSyntheticUnit()
            );
        }

        return $outputData;
    }
}
