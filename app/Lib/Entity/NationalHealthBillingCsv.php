<?php

namespace App\Lib\Entity;

use App\Lib\Entity\NationalHealthBilling;
use Carbon\CarbonImmutable;

/**
 * 国保連請求csvクラス。
 */
class NationalHealthBillingCsv
{
    /**
     * @var string ブランク
     * 国の指定により設定不要の場合は0またはブランクを利用する。(ブランクの場合は国保連側で0に置き換えている。)
     * サービス種類が対応していないものをこれで埋める必要がある。
     * 必ずブランクが出力されるものについてはケアデイジーが対応していないサービス種類のもの、ということになる。
     */
    private const BLANK_VALUE = '';

    /**
     * @var Facility 事業所
     */
    private Facility $facility;

    private int $invoiceFlg;

    private array $records;

    /**
     * @var int 連番
     */
    private int $serialNumber;

    /**
     * @var int 対象年
     */
    private int $year;

    /**
     * @var int 対象月
     */
    private int $month;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param BasicRemark[] $basicRemarks 基本摘要
     * @param Facility $facility 事業所
     * @param FacilityUserCareRecord[] $facilityUserCareRecords 施設利用者の介護情報
     * @param FacilityUserPublicExpenseRecord[] $facilityUserPublicExpenseRecords 施設利用者の公費の記録
     * @param FacilityUser[] $facilityUsers 施設利用者
     * @param FacilityUserServiceRecord[] $facilityUserServiceRecords 施設利用者のサービスの記録
     * @param InjuriesSickness[] $injuriesSicknesses 傷病情報
     * @param int $invoiceFlg
     * @param NationalHealthBilling[] $nationalHealthBillings 国保連請求
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function __construct(
        array $basicRemarks,
        Facility $facility,
        array $facilityUserCareRecords,
        array $facilityUserPublicExpenseRecords,
        array $facilityUsers,
        array $facilityUserServiceRecords,
        array $injuriesSicknesses,
        int $invoiceFlg,
        array $nationalHealthBillings,
        int $year,
        int $month
    ) {
        $this->facility = $facility;
        $this->invoiceFlg = $invoiceFlg;
        $this->records = [];
        $this->serialNumber = 1;
        $this->year = $year;
        $this->month = $month;

        // 国保連請求から計算種別合計かつサービスが承認されているものだけを対象にする。
        $nationalHealthBillingsApproval = null;
        foreach ($nationalHealthBillings as $index => $nationalHealthBilling) {
            if ($nationalHealthBilling->getServiceTotal()->isApproval()) {
                $nationalHealthBillingsApproval[] = $nationalHealthBilling;
            }
        }

        // 介護給付費請求情報レコード(保険請求)を作成する。
        $this->serialNumber++;
        $benefitBillingInsuranceRecord = new BenefitBillingInsuranceRecord(
            self::BLANK_VALUE,
            $this->facility,
            $nationalHealthBillingsApproval,
            $this->serialNumber,
            $this->getTargetYm()
        );
        $this->records[] = $benefitBillingInsuranceRecord->getRecord();

        // 施設利用者たちに適用される公費の法別番号を全て取得する。
        $legalNumbers = [];
        foreach ($facilityUserPublicExpenseRecords as $facilityUserPublicExpenseRecord) {
            $legalNumbers[] = $facilityUserPublicExpenseRecord->getApplicablePublicExpense()->getLegalNumber();
        }
        $legalNumbers =  array_values(array_unique($legalNumbers));
        sort($legalNumbers);

        // 介護給付費請求情報レコード(公費)を法別番号ごとに作成する。
        foreach ($legalNumbers as $legalNumber) {
            // 対象の施設利用者を全て取得する。
            $targetFacilityUserIds = [];
            foreach ($facilityUserPublicExpenseRecords as $record) {
                if ($record->getApplicablePublicExpense()->getLegalNumber() === $legalNumber) {
                    $targetFacilityUserIds[] = $record->getFacilityUserId();
                }
            }

            // 国保連請求から対象の施設利用者のサービス実績の合計を全て取得する。
            $targetServiceResultTotals = [];
            foreach ($nationalHealthBillingsApproval as $billing) {
                if (in_array($billing->getFacilityUserId(), $targetFacilityUserIds)) {
                    $targetServiceResultTotals = array_merge($targetServiceResultTotals, $billing->getTotals());
                }
            }

            // 連番を進める。
            $this->serialNumber++;
            $benefitBillingPublicRecord = new BenefitBillingPublicRecord(
                self::BLANK_VALUE,
                $this->facility,
                $legalNumber,
                $this->serialNumber,
                $targetServiceResultTotals,
                $this->getTargetYm()
            );
            $this->records[] = $benefitBillingPublicRecord->getRecord();
        }

        // 施設利用者ごとに基本情報レコード、明細情報レコード、集計情報レコードを作成する。
        // (種類55のみ)基本摘要情報レコード、特定診療費・特別療養費・特別診療費情報レコードを作成する。
        foreach ($facilityUsers as $facilityUser) {
            // 対象の施設利用者の国保連請求を取得する。
            $targetNationalHealthBilling = null;
            foreach ($nationalHealthBillingsApproval as $nationalHealthBilling) {
                if ($facilityUser->getFacilityUserId() === $nationalHealthBilling->getFacilityUserId()) {
                    $targetNationalHealthBilling = $nationalHealthBilling;
                    break;
                }
            }

            // 対象の施設利用者のサービスの記録を取得する。
            $targetServiceRecord = null;
            foreach ($facilityUserServiceRecords as $record) {
                if ($facilityUser->getFacilityUserId() === $record->getFacilityUserId()) {
                    $targetServiceRecord = $record;
                    break;
                }
            }

            // 対象の施設利用者の最新のサービス種類コードを取得する。
            $latestService = $targetServiceRecord->getLatest();
            $latestServiceTypeCode = $latestService->getServiceTypeCode()->getServiceTypeCode();

            // 対象の施設利用者の介護情報の記録を取得する。
            $targetCareRecord = null;
            foreach ($facilityUserCareRecords as $record) {
                if ($facilityUser->getFacilityUserId() === $record->getFacilityUserId()) {
                    $targetCareRecord = $record;
                    break;
                }
            }

            // 対象の施設利用者の最新の介護情報を取得する。
            $latestCare = $targetCareRecord->getCareLatest();

            // 対象の施設利用者の公費の記録を取得する。
            $targetPublicExpenseRecord = null;
            foreach ($facilityUserPublicExpenseRecords as $record) {
                if ($facilityUser->getFacilityUserId() === $record->getFacilityUserId()) {
                    $targetPublicExpenseRecord = $record;
                    break;
                }
            }

            // 対象の施設利用者の公費がなければnull、あれば適用可能な公費を取得する。
            $applicablePublicExpense = null;
            if ($targetPublicExpenseRecord !== null) {
                $applicablePublicExpense = $targetPublicExpenseRecord->getApplicablePublicExpense();
            }

            // 基本情報レコード
            $this->serialNumber++;
            $basicRecord = new BasicRecord(
                self::BLANK_VALUE,
                $this->getExchangeInformationNumber($latestServiceTypeCode),
                $this->facility,
                $facilityUser,
                $latestCare,
                $applicablePublicExpense,
                $latestService,
                $targetNationalHealthBilling,
                $this->serialNumber,
                $this->getTargetYm()
            );
            $this->records[] = $basicRecord->getRecord();

            // 基本摘要情報レコード
            // 種類55の場合のみ
            if ($latestService->isHospital()) {
                $targetBasicRemarks = array_filter($basicRemarks, function ($remark) use ($facilityUser) {
                    return $remark->getFacilityUserId() === $facilityUser->getFacilityUserId();
                });
                // 施設利用者は対象年月中に一つまで基本摘要を持つ。
                $targetBasicRemark = count($targetBasicRemarks) === 0 ? null : array_values($targetBasicRemarks)[0];

                $this->serialNumber++;
                $basicSummaryRecord = new BasicSummaryRecord(
                    $targetBasicRemark,
                    self::BLANK_VALUE,
                    $this->facility,
                    $facilityUser,
                    $this->serialNumber,
                    $this->getTargetYm()
                );
                $this->records[] = $basicSummaryRecord->getRecord();
            }

            // 施設利用者のサービス実績の明細を全て取得する。
            $targetServiceResultDetails = $targetNationalHealthBilling->getServiceDetails();

            // 明細情報レコード
            foreach ($targetServiceResultDetails as $individual) {
                $this->serialNumber++;
                $detailRecord = new DetailRecord(
                    self::BLANK_VALUE,
                    $this->getExchangeInformationNumber($latestServiceTypeCode),
                    $this->facility,
                    $facilityUser,
                    $latestService,
                    $individual,
                    $this->serialNumber,
                    $this->getTargetYm()
                );
                $this->records[] = $detailRecord->getRecord();
            }

            // 種類55の場合
            if ($latestService->isHospital()) {
                // 施設利用者の傷病名を取得する。
                $targetInjuriesSicknesses = array_filter($injuriesSicknesses, function ($injuriesSickness) use ($facilityUser) {
                    return $injuriesSickness->getFacilityUserId() === $facilityUser->getFacilityUserId();
                });
                // 施設利用者は対象年月中に一つまで傷病名を持つ。
                $targetInjuriesSickness = count($targetInjuriesSicknesses) === 0 ? null : array_values($targetInjuriesSicknesses)[0];

                // 特定診療費・特別療養費・特別診療費情報レコード
                $medicalCareRecords = new MedicalCareRecords(
                    self::BLANK_VALUE,
                    $this->facility,
                    $facilityUser,
                    $targetInjuriesSickness,
                    $targetNationalHealthBilling,
                    $this->serialNumber,
                    $this->getTargetYm()
                );
                $this->records = array_merge($this->records, $medicalCareRecords->getRecords());
                // 連番を進める。
                $this->serialNumber = $medicalCareRecords->getSerialNumber();
            }

            // 集計情報レコード
            $this->serialNumber++;
            $serviceResultTotalBasic = $targetNationalHealthBilling->getServiceTotal();
            $serviceResultTotalSpecial = $targetNationalHealthBilling->getSpecialMedicalTotal();
            $aggregationRecord = new AggregationRecord(
                self::BLANK_VALUE,
                $this->getExchangeInformationNumber($latestServiceTypeCode),
                $this->facility,
                $facilityUser,
                $latestService,
                $this->serialNumber,
                $serviceResultTotalBasic,
                $serviceResultTotalSpecial,
                $this->getTargetYm()
            );
            $this->records[] = $aggregationRecord->getRecord();

            if ($latestService->isHospital()) {
                $incompetentResidentRecords = new IncompetentResidentRecords(
                    self::BLANK_VALUE,
                    $facility,
                    $facilityUser,
                    $targetNationalHealthBilling,
                    $this->serialNumber,
                    $this->getTargetYm()
                );
                // 特定入所者介護サービス費用情報レコード
                $this->records = array_merge($this->records, $incompetentResidentRecords->getRecords());
                $this->serialNumber = $incompetentResidentRecords->getSerialNumber();
            }
        }

        // コントロールレコードを作成する。データレコードの件数に依存するため最後に作成する必要がある。
        array_unshift($this->records, $this->getCtrlRecord($facility, $invoiceFlg));

        // エンドレコードを作成する。
        $this->records[] = $this->getEndRecord();
    }

    /**
     * 請求対象年月を返す。
     *
     * @param CarbonImmutable $systemDatetime
     */
    public static function getBillingTargetYm($systemDatetime): string
    {
        // 請求処理実行が月の10日以下であるか。
        $isLessThan10Days = $systemDatetime->day <= 10;

        return $isLessThan10Days ? $systemDatetime->format('Ym') : $systemDatetime->addMonthNoOverflow()->format('Ym');
    }

    /**
     * コントロールレコードを返す。
     * @param Facility $facility
     * @return array
     */
    public function getCtrlRecord(Facility $facility): array
    {
        // 伝送用国保連請求データの作成時かつテスト請求モードが1の時はコントロールレコード（1行目）の12項を「*TEST*」にして作成
        $fileNo = '0';
        if ($this->invoiceFlg == 1 && config('invoice.test_invoice') == 1) {
            $fileNo = '*TEST*';
        }

        return
        [
            // 1 レコード種別 マジックナンバー
            '1',
            // 2 レコード番号(連番)
            1,
            // 3 ボリューム通番 マジックナンバー
            '0',
            // 4 レコード件数
            count($this->records),
            // 5 データ種別 マジックナンバー
            '711',
            // 6 福祉事務所特定番号 マジックナンバー
            0,
            // 7 保険者番号 マジックナンバー
            '000000',
            // 8 事業所番号
            $this->facility->getFacilityNumber(),
            // 9 都道府県番号 マジックナンバー
            '00',
            // 10 媒体区分 マジックナンバー
            7,
            // 11 処理対象年月
            self::getBillingTargetYm(CarbonImmutable::parse('now')),
            // 12 ファイル管理番号 マジックナンバー
            $fileNo
        ];
    }

    /**
     * エンドレコードを返す。
     * @param array
    */
    public function getEndRecord(): array
    {
        // 連番を進める。
        $this->serialNumber++;

        return [
            // レコード種別 マジックナンバー
            '3',
            // レコード番号(連番)
            $this->serialNumber
        ];
    }

    /**
     * サービス種類コードから交換情報識別番号を取得して返す。
     * @param string $serviceTypeCode サービス種類コード。
     * @return string
     */
    public function getExchangeInformationNumber(string $serviceTypeCode): string
    {
        $id = null;
        switch ($serviceTypeCode) {
            case '32':
                $id = '7171';
                break;

            case '33':
                $id = '7173';
                break;

            case '35':
                $id = '7174';
                break;

            case '36':
                $id = '7173';
                break;

            case '37':
                $id = '7172';
                break;

            case '55':
                $id = '7196';
                break;

            case '59':
                $id = '7196';
                break;
        }

        return $id;
    }

    /**
     * レコードを返す。
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * サービス提供年月を返す。
     * @return string yyyymm
     */
    public function getTargetYm(): string
    {
        $year = $this->year;
        $month = $this->month;
        $targetDate = new CarbonImmutable("${year}-${month}-1");
        $targetYm = $targetDate->format('Ym');
        return $targetYm;
    }
}
