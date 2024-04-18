<?php

namespace App\Service\GroupHome;

use App\Lib\ApplicationBusinessRules\UseCases\Interactors\NationalHealthBillingSaveInteractor;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\AutoServiceCodeGetInputBoundary;

/**
 * 夜間バッチで実行される自動請求処理
 */
class AutoBillingService
{
    /**
     * 国保連請求処理を実行する。
     * @param int $year
     * @param int $month
     * @return array
     */
    public function nationalHealthBilling(
        AutoServiceCodeGetInputBoundary $interactor,
        NationalHealthBillingSaveInteractor $useCase,
        int $year,
        int $month
    ): array {
        $autoBillingTable = new AutoBillingTable();

        // 請求対象の施設利用者の情報を全て取得する
        $facilityUsers = $autoBillingTable->getUnbilledFacilityUsers(['year' => $year, 'month' => $month]);

        // 施設利用者全員について国保連請求処理を行う
        $successFacilityUserCnt = 0;
        for ($i = 0, $cnt = count($facilityUsers); $i < $cnt; $i++) {
            $facilityUser = $facilityUsers[$i];

            \DB::beginTransaction();
            try {
                // サービスコードを取得する(実績情報の再集計ボタンと同じ処理を行う)。
                $serviceCodes = $interactor->handle($facilityUser['facility_id'], $facilityUser['facility_user_id'], $year, $month)->getData();

                // サービスコードの形式を調整する
                for ($serviceCodeIndex = 0, $serviceCodeCnt = count($serviceCodes); $serviceCodeIndex < $serviceCodeCnt; $serviceCodeIndex++) {
                    $serviceCode = $serviceCodes[$serviceCodeIndex];
                    $serviceCode['service_code'] = $serviceCode['service_type_code'].$serviceCode['service_item_code'];
                }

                $useCase->handle($facilityUser['facility_id'], $facilityUser['facility_user_id'], $serviceCodes, $year, $month);

                $successFacilityUserCnt++;

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
            }
        }

        return ['successful_facility_user_cnt' => $successFacilityUserCnt, 'sum' => count($facilityUsers)];
    }

    /**
     * 保険外請求処理を実行する
     * @param int $year
     * @param int $month
     * @return array
     */
    public function uninsuredBilling(int $year, int $month): array
    {
        $autoBillingTable = new AutoBillingTable();

        // 請求対象の施設利用者の情報を全て取得する
        $facilityUsers = $autoBillingTable->getUnbilledUninsuredFacilityUsers(['year' => $year, 'month' => $month]);

        // 対象の施設利用者全員について保険外請求処理を実行する
        $successFacilityUserCnt = 0;
        for ($i = 0, $cnt = count($facilityUsers); $i < $cnt; $i++) {
            \DB::beginTransaction();

            try {
                // 施設利用者が事業所より提供されているサービスの情報を取得する
                $service = $autoBillingTable->getFacilityUserService([
                    'facility_id' => $facilityUsers[$i]['facility_id'],
                    'facility_user_id' => $facilityUsers[$i]['facility_user_id'],
                    'year' => $year,
                    'month' => $month
                ]);

                // 取得したサービス情報から保険外品目の履歴情報を取得する
                $uninsuredItemHistories = $autoBillingTable->getUninsuredItemHistory([
                    'service_id' => $service['service_id'],
                    'year' => $year,
                    'month' => $month
                ]);

                // 保険外品目の履歴情報が1件でも取得できているのかチェックする。
                if (count($uninsuredItemHistories) !== 0) {
                    // 取得した履歴情報から施設利用者の保険外請求を実行する。
                    $autoBillingTable->executeUninsuredBilling([
                        'facility_user_id' => $facilityUsers[$i]['facility_user_id'],
                        'uninsured_item_histories' => $uninsuredItemHistories,
                        'year' => $year,
                        'month' => $month,
                    ]);
                }
                // 保険外品目の履歴情報が1件もない場合も、成功に含めてカウントしております。
                $successFacilityUserCnt++;

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
            }
        }

        return ['successful_facility_user_cnt' => $successFacilityUserCnt, 'sum' => count($facilityUsers)];
    }
}
