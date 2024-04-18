<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\Interactors;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRegisterRepositoryInterface;
use App\Lib\ApplicationBusinessRules\OutputData\PublicExpenseAcquisitionOutputData;
use App\Lib\DomainService\SecuritySpecification;
use App\Lib\ApplicationBusinessRules\UseCases\Exceptions\UnauthorizedAccessException;
use Carbon\Carbon;

/**
 * 公費の取得のユースケースの実装。
 */
class PublicExpenseAcquisitionInteractor
{
    private FacilityUserPublicExpenseRecordRepositoryInterface $facilityUserPublicExpenseRecordRepository;

    private FacilityUserRegisterRepositoryInterface $facilityUserRegisterRepository;

    /**
     * コンストラクタ。
     * @param FacilityUserPublicExpenseRecordRepositoryInterface 施設利用者の公費の記録のリポジトリ。
     * @param FacilityUserRegisterRepositoryInterface 事業所のユーザーの名簿のリポジトリ。
     */
    public function __construct(
        FacilityUserPublicExpenseRecordRepositoryInterface $facilityUserPublicExpenseRecordRepository,
        FacilityUserRegisterRepositoryInterface $facilityUserRegisterRepository
    ) {
        $this->facilityUserPublicExpenseRecordRepository = $facilityUserPublicExpenseRecordRepository;
        $this->facilityUserRegisterRepository = $facilityUserRegisterRepository;
    }

    /**
     * @param int $accountId アカウントのID
     * @param int $facilityUserPublicExpenseId 施設利用者の公費のID
     * @throws UnauthorizedAccessException 不正なアクセスの例外。
     */
    public function handle(
        int $accountId,
        int $facilityUserPublicExpenseId
    ): PublicExpenseAcquisitionOutputData {
        // アカウントのユーザーリストを取得する。
        $facilityUserRegister = $this->facilityUserRegisterRepository->find($accountId);

        // 施設利用者の公費を取得する。
        $facilityUserPublicExpense = $this->facilityUserPublicExpenseRecordRepository->findById(
            $facilityUserPublicExpenseId
        );

        // 施設利用者の公費にアクセス権があるか判定する。
        $isAccessible = SecuritySpecification::isAccessibleFacilityUer(
            $facilityUserPublicExpense->getFacilityUserId(),
            $facilityUserRegister
        );

        if (!$isAccessible) {
            throw new UnauthorizedAccessException('無効なアクセスです。');
        }

        // 出力データを作成する。
        $outputData = new PublicExpenseAcquisitionOutputData(
            $facilityUserPublicExpense->getAmountBornePerson(),
            $facilityUserPublicExpense->getBearerNumber(),
            $facilityUserPublicExpense->getConfirmationMedicalInsuranceDate(),
            $facilityUserPublicExpense->getEffectiveStartDate(),
            $facilityUserPublicExpense->getExpiryDate(),
            $facilityUserPublicExpense->getPublicExpense()->getLegalName(),
            $facilityUserPublicExpense->getPublicExpenseInformationId(),
            $facilityUserPublicExpense->getRecipientNumber()
        );

        return $outputData;
    }
}
