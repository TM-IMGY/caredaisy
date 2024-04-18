<?php

namespace App\Service\GroupHome;

use App\Models\Corporation;

class CorporationService
{
  /**
   * アカウントに紐づく事業所のデータを取得
   * @return array
   */
    public function getRelatedData()
    {
      // アカウントIDに紐づく法人IDのリストを取得
        $corporation = Corporation::get();
        return $corporation;
    }
}
