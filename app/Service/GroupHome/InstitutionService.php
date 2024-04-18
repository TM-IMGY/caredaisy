<?php

namespace App\Service\GroupHome;

use App\Models\Institution;

class InstitutionService
{
  /**
   * データを取得
   * @return array
   */
    public function getRelatedData($request)
    {
        $institution = Institution::where('corporation_id', $request->id)->get();

        return $institution;
    }
}
