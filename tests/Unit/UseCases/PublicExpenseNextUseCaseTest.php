<?php

namespace Tests\Unit\UseCases;

use App\Lib\ApplicationBusinessRules\UseCases\Exceptions\UnauthorizedAccessException;
use App\Lib\ApplicationBusinessRules\UseCases\Interactors\PublicExpenseNextInteractor;
use App\Lib\Entity\PublicExpense;
use App\Lib\InMemoryRepository\FacilityUserPublicExpenseRecordInMemoryRepository;
use App\Lib\InMemoryRepository\FacilityUserRegisterInMemoryRepository;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestPublicExpenseFactory;

/**
 * 公費の次回分の取得のユースケースのテスト。
 */
class PublicExpenseNextUseCaseTest extends TestCase
{
    /**
     * データプロバイダ。
     */
    public function dataProvider(): array
    {
        // 公費情報のファクトリを作成する。
        $testPublicExpenseFactory = new TestPublicExpenseFactory();

        return [
            // テストの目的: 法別番号が12(生活保護)の動きを確認する。
            'case_1' => [
                // amount_borne_person
                1000,
                // bearer_number
                '12000000',
                // correct_amount_borne_person
                1000,
                // correct_effective_start_date
                '2023-01-01',
                // correct_expiry_date
                '2023-01-31',
                // effective_start_date
                '2022-12-01',
                // expiry_date
                '2022-12-31',
                // public_expense
                $testPublicExpenseFactory->generatePublicAssistance32(),
                // recipient_number
                '1234567'
            ],
            // テスト目的: 法別番号が15(自立更生)の動きを確認する。
            'case_2' => [
                // amount_borne_person
                1000,
                // bearer_number
                '15000000',
                // correct_amount_borne_person
                1000,
                // correct_effective_start_date
                '2023-01-01',
                // correct_expiry_date
                null,
                // effective_start_date
                '2022-12-01',
                // expiry_date
                '2022-12-31',
                // public_expense
                $testPublicExpenseFactory->generateRehabilitation55(),
                // recipient_number
                '1234567'
            ],
            // テスト目的: 法別番号が25(中国残留法人等)の動きを確認する。
            'case_3' => [
                // amount_borne_person
                1000,
                // bearer_number
                '25000000',
                // correct_amount_borne_person
                1000,
                // correct_effective_start_date
                '2023-01-01',
                // correct_expiry_date
                null,
                // effective_start_date
                '2022-12-01',
                // expiry_date
                '2022-12-31',
                // public_expense
                $testPublicExpenseFactory->generateChina32(),
                // recipient_number
                '1234567'
            ],
            // テスト目的: 法別番号が54(難病公費)の動きを確認する。
            'case_4' => [
                // amount_borne_person
                1000,
                // bearer_number
                '54000000',
                // correct_amount_borne_person
                1000,
                // correct_effective_start_date
                '2023-01-01',
                // correct_expiry_date
                null,
                // effective_start_date
                '2022-12-01',
                // expiry_date
                '2022-12-31',
                // public_expense
                $testPublicExpenseFactory->generateIncurableDisease32(),
                // recipient_number
                '1234567'
            ],
            // テスト目的: 法別番号が81(原爆助成)の動きを確認する。
            'case_5' => [
                // amount_borne_person
                0,
                // bearer_number
                '81000000',
                // correct_amount_borne_person
                0,
                // correct_effective_start_date
                '2023-01-01',
                // correct_expiry_date
                null,
                // effective_start_date
                '2022-12-01',
                // expiry_date
                '2022-12-31',
                // public_expense
                $testPublicExpenseFactory->generateAtomicBomb32(),
                // recipient_number
                '1234567'
            ],
            // テスト目的: 月途中の動きを確認する。
            'case_6' => [
                // amount_borne_person
                1000,
                // bearer_number
                '12000000',
                // correct_amount_borne_person
                1000,
                // correct_effective_start_date
                '2022-12-31',
                // correct_expiry_date
                '2022-12-31',
                // effective_start_date
                '2022-12-01',
                // expiry_date
                '2022-12-30',
                // public_expense
                $testPublicExpenseFactory->generatePublicAssistance32(),
                // recipient_number
                '1234567'
            ]
        ];
    }

    /**
     * ユースケースのテスト。
     * @dataProvider dataProvider
     * @param int $amountBornePerson 本人支払い額
     * @param string $bearerNumber 負担者番号
     * @param int $correctAmountBornePerson 本人支払い額の正解
     * @param string $correctEffectiveStartDate 有効開始日の正解
     * @param ?string $correctExpiryDate 有効終了日の正解
     * @param string $effectiveStartDate 有効開始日
     * @param string $expiryDate 有効終了日
     * @param PublicExpense $publicExpense
     * @param string $recipientNumber 受給者番号
     */
    public function testUseCase(
        int $amountBornePerson,
        string $bearerNumber,
        int $correctAmountBornePerson,
        string $correctEffectiveStartDate,
        ?string $correctExpiryDate,
        string $effectiveStartDate,
        string $expiryDate,
        PublicExpense $publicExpense,
        string $recipientNumber
    ): void {
        // アカウントID。
        $accountId = 1;

        // 施設利用者ID。
        $facilityUserId = 1;

        // 事業所のユーザーのリポジトリを作成する。
        $facilityUserRegisterRepository = new FacilityUserRegisterInMemoryRepository();

        // 施設利用者の公費のリポジトリを作成する。
        $publicExpenseRecordRepository = new FacilityUserPublicExpenseRecordInMemoryRepository();

        // 施設利用者の公費を挿入する。
        $publicExpenseId = $publicExpenseRecordRepository->insert(
            $amountBornePerson,
            $bearerNumber,
            $effectiveStartDate,
            $expiryDate,
            $facilityUserId,
            $publicExpense,
            $recipientNumber
        );

        // 施設利用者IDを名簿に挿入する。
        $facilityUserRegisterRepository->insert($accountId, $facilityUserId);

        // 公費の次回分の取得のユースケースを作成する。
        $publicExpenseNextInteractor = new PublicExpenseNextInteractor(
            $publicExpenseRecordRepository,
            $facilityUserRegisterRepository
        );

        $outputData = $publicExpenseNextInteractor->handle($accountId, $publicExpenseId);

        $testTarget = $outputData->getData();

        // 登録した公費を取得する。
        $facilityUserPublicExpense = $publicExpenseRecordRepository->findById($publicExpenseId);

        // 負担者番号が正しいかテストする。
        $this->assertEquals($facilityUserPublicExpense->getBearerNumber(), $testTarget['bearer_number']);

        // 受給者番号が正しいかテストする。
        $this->assertEquals($facilityUserPublicExpense->getRecipientNumber(), $testTarget['recipient_number']);

        // 公費略称が正しいかテストする。
        $this->assertEquals($publicExpense->getLegalName(), $testTarget['legal_name']);

        // 有効開始日が正しいかテストする。
        $this->assertEquals($correctEffectiveStartDate, $testTarget['effective_start_date']);

        // 有効終了日が正しいかテストする。
        $this->assertEquals($correctExpiryDate, $testTarget['expiry_date']);

        // 公費情報確認日が正しいかテストする。
        $this->assertEquals(null, $testTarget['confirmation_medical_insurance_date']);

        // 本人支払い額が正しいかテストする。
        $this->assertEquals($correctAmountBornePerson, $testTarget['amount_borne_person']);
    }

    /**
     * ユースケースの例外のテスト。
     * @dataProvider dataProvider
     * @param int $amountBornePerson 本人支払い額
     * @param string $bearerNumber 負担者番号
     * @param int $correctAmountBornePerson 本人支払い額の正解
     * @param string $correctEffectiveStartDate 有効開始日の正解
     * @param ?string $correctExpiryDate 有効終了日の正解
     * @param string $effectiveStartDate 有効開始日
     * @param string $expiryDate 有効終了日
     * @param PublicExpense $publicExpense
     * @param string $recipientNumber 受給者番号
     */
    public function testException(
        int $amountBornePerson,
        string $bearerNumber,
        int $correctAmountBornePerson,
        string $correctEffectiveStartDate,
        ?string $correctExpiryDate,
        string $effectiveStartDate,
        string $expiryDate,
        PublicExpense $publicExpense,
        string $recipientNumber
    ): void {
        $this->expectException(UnauthorizedAccessException::class);

        // アカウントID(自分)を作成する。
        $accountIdMyself = 1;

        // アカウントID(他人)を作成する。
        $accountIdAnother = 2;

        // アカウントID(自分)の施設利用者のIDを作成する。
        $facilityUserIdAccessible = 1;

        // アカウントID(他人)の施設利用者のIDを作成する。
        $facilityUserIdNotAccessible = 2;

        // 事業所のユーザーのリポジトリを作成する。
        $facilityUserRegisterRepository = new FacilityUserRegisterInMemoryRepository();

        // 施設利用者の公費のリポジトリを作成する。
        $publicExpenseRecordRepository = new FacilityUserPublicExpenseRecordInMemoryRepository();

        // アカウントID(自分)の施設利用者の公費を挿入する。
        $publicExpenseIdAccessible = $publicExpenseRecordRepository->insert(
            $amountBornePerson,
            $bearerNumber,
            $effectiveStartDate,
            $expiryDate,
            $facilityUserIdAccessible,
            $publicExpense,
            $recipientNumber
        );

        // アカウントID(他人)の施設利用者の公費を挿入する。
        $publicExpenseIdNotAccessible = $publicExpenseRecordRepository->insert(
            $amountBornePerson,
            $bearerNumber,
            $effectiveStartDate,
            $expiryDate,
            $facilityUserIdNotAccessible,
            $publicExpense,
            $recipientNumber
        );

        // アカウントID(自分)の施設利用者IDを名簿に挿入する。
        $facilityUserRegisterRepository->insert($accountIdMyself, $publicExpenseIdAccessible);

        // アカウントID(他人)の施設利用者IDを名簿に挿入する。
        $facilityUserRegisterRepository->insert($accountIdAnother, $publicExpenseIdNotAccessible);

        // 公費の取得のユースケースを作成する。
        $interactor = new PublicExpenseNextInteractor(
            $publicExpenseRecordRepository,
            $facilityUserRegisterRepository
        );

        $outputData = $interactor->handle($accountIdMyself, $publicExpenseIdNotAccessible);
    }
}
