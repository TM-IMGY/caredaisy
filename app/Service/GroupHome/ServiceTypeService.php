<?php

namespace App\Service\GroupHome;

use App\Models\ServiceType;
use App\Models\Service;

class ServiceTypeService
{
    public function get($facilityId)
    {
        $serviceTypeCodeIdList = Service::where('facility_id', $facilityId)
            ->select('service_type_code_id')
            ->get()
            ->toArray();

        $serviceTypeCodeIds = array_column($serviceTypeCodeIdList, 'service_type_code_id');

        $serviceTypeAll = ServiceType::whereIn('service_type_code_id', $serviceTypeCodeIds)
            ->select('service_type_code', 'service_type_name')
            ->get()
            ->toArray();

        return $serviceTypeAll;
    }
}
