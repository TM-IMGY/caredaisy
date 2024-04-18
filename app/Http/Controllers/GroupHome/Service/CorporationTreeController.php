<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;

use App\Models\Facility;
use App\Models\CorporationAccount;
use App\Models\Institution;
use App\Models\Corporation;
use App\Models\ServiceType;
use App\Models\Service;

use Illuminate\Support\Facades\Auth;

class CorporationTreeController extends Controller
{
    public function reference()
    {
        $corporationIdList = CorporationAccount::where('account_id', Auth::id())
            ->select('corporation_id')
            ->get()
            ->map(function($item){return $item->corporation_id;
            });

      // 法人IDのリストから法人情報を取得
        $corporationList = Corporation::whereIn('id', $corporationIdList)
            ->select('id', 'abbreviation', 'name')
            ->orderBy('abbreviation', 'asc')
            ->get()
            ->toArray();

        if (empty($corporationList)) {
            return null;
        }

        $corpIdList = array_map(function($corp){return $corp['id'];
        }, $corporationList);

        $institutionList = Institution::whereIn('corporation_id', $corpIdList)
            ->select('id', 'corporation_id', 'abbreviation', 'name')
            ->get()
            ->toArray();
        $insIdList = array_map(function($ins){return $ins['id'];
        }, $institutionList);

        $facilityList = Facility::whereIn('institution_id', $insIdList)
            ->select('facility_id', 'abbreviation', 'institution_id', 'facility_name_kanji')
            ->get()
            ->toArray();
        $facilityIdList = array_map(function($facility){return $facility['facility_id'];
        }, $facilityList);

        $serviceList = Service::whereIn('facility_id', $facilityIdList)
            ->select('id', 'service_type_code_id', 'facility_id')
            ->get()
            ->toArray();
        $serviceIdList = array_map(function($service){return $service['service_type_code_id'];
        }, $serviceList);

        $serviceCode = ServiceType::whereIn('service_type_code_id', $serviceIdList)
            ->select('service_type_code_id', 'service_type_code', 'service_type_name')
            ->get()
            ->toArray();

      // キー名変更
        foreach ($institutionList as $key => $institution) {
            $institutionList[$key]['institution_id'] = $institution['id'];
            $institutionList[$key]['institution_abbreviation'] = $institution['abbreviation'];
            $institutionList[$key]['institution_name'] = $institution['name'];
            unset($institutionList[$key]['id']);
            unset($institutionList[$key]['abbreviation']);
            unset($institutionList[$key]['name']);
        }

        foreach ($facilityList as $key => $facility) {
            $facilityList[$key]['facility_abbreviation'] = $facility['abbreviation'];
            unset($facilityList[$key]['abbreviation']);
        }

        foreach ($serviceList as $key => $service) {
            $serviceList[$key]['service_id'] = $service['id'];
            unset($serviceList[$key]['id']);
        }

        $services = [];
        foreach ($serviceList as $key => $service) {
            $serviceTypeCodeId = $service['service_type_code_id'];

            foreach ($serviceCode as $codeKey => $code) {
                if ($code['service_type_code_id'] != $serviceTypeCodeId) {
                    continue;
                }
                $services[] = array_merge($code, $service);
            }
        }

        foreach ($facilityList as $key => $facility) {
            $facilityId = $facility['facility_id'];

            foreach ($services as $index => $service) {
                if ($service['facility_id'] != $facilityId) {
                    continue;
                }
                $facilityList[$key]['service'][] = $service;
            }
        }

        foreach ($institutionList as $key => $institution) {
            $institutionId = $institution['institution_id'];

            foreach ($facilityList as $index => $facility) {
                if ($facility['institution_id'] != $institutionId) {
                    continue;
                }
                $institutionList[$key]['facility'][] = $facility;
            }
        }

        foreach ($corporationList as $key => $corporation) {
            $corporationId = $corporation['id'];

            foreach ($institutionList as $index => $institution) {
                if ($institution['corporation_id'] != $corporationId) {
                    continue;
                }
                $corporationList[$key]['institution'][] = $institution;
            }
        }

        return $corporationList;
    }
}
