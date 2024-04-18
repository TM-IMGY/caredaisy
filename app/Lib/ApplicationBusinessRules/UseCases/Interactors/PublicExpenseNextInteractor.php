<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\Interactors;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRegisterRepositoryInterface;
use App\Lib\ApplicationBusinessRules\OutputData\PublicExpenseNextOutputData;
use App\Lib\DomainService\SecuritySpecification;
use App\Lib\ApplicationBusinessRules\UseCases\Exceptions\UnauthorizedAccessException;
use Carbon\Carbon;

/**
 * 公費の次回分の取得のユースケースの実装。
 * 公費は月ごとに追加登録することが多く、ユーザーの利便性のニーズから発生した。
 */
class PublicExpenseNextInteractor
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
     * TODO: 現状必要ないため公費マスタ情報の有効期限を参照していない。
     * @param int $accountId アカウントのID
     * @param int $facilityUserPublicExpenseId 施設利用者の公費のID
     * @throws UnauthorizedAccessException 不正なアクセスの例外。
     */
    public function handle(
        int $accountId,
        int $facilityUserPublicExpenseId
    ): PublicExpenseNextOutputData {
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

        // 公費マスター情報を取得する。
        $publicExpense = $facilityUserPublicExpense->getPublicExpense();

        // 本人支払い額がありえるかを判定する。
        $isAmountBornePersonPossible = $publicExpense->isAmountBornePersonPossible();

        // 本人支払い額を確保する。
        $amountBornePerson = $isAmountBornePersonPossible ? $facilityUserPublicExpense->getAmountBornePerson() : 0;

        // 公費情報確認日は必ずnullになる。
        $confirmationMedicalInsuranceDate = null;

        // 有効開始日を作成する(指定の施設利用者の公費の有効終了日の翌日)。
        $effectiveStartDate = (new Carbon($facilityUserPublicExpense->getExpiryDate()))->addDay();

        // 生活保護かを判定する。
        $isPublicAssistance = $publicExpense->isPublicAssistance();

        // 有効終了日を作成する。
        $expiryDate = $isPublicAssistance ? $effectiveStartDate->copy()->lastOfMonth() : null;

        // 出力データを作成する。
        $outputData = new PublicExpenseNextOutputData(
            $amountBornePerson,
            $facilityUserPublicExpense->getBearerNumber(),
            $confirmationMedicalInsuranceDate,
            $effectiveStartDate->format('Y-m-d'),
            $expiryDate === null ? null : $expiryDate->format('Y-m-d'),
            $publicExpense->getLegalName(),
            $facilityUserPublicExpense->getRecipientNumber()
        );

        return $outputData;
    }
}
