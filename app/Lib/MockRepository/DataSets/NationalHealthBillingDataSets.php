<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 国保連請求のデータセット。
 * 初期はcsvで管理していたが可読性が低すぎたためクラスとして換装した。
 * 介護医療院では手間を考えて 9994000000000000000000000000000 の入力の仕方をするものがある(実際は毎日に1がつく)。
 */
class NationalHealthBillingDataSets
{
    public static function get()
    {
        return array_merge(
            NationalHealthBillingDataSet1::DATA,
            NationalHealthBillingDataSet2::DATA,
            NationalHealthBillingDataSet3::DATA,
            NationalHealthBillingDataSet4::DATA,
            NationalHealthBillingDataSet5::DATA,
            NationalHealthBillingDataSet6::DATA
        );
    }
}
