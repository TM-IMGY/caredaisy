<?php

namespace App\Service\GroupHome;

use App\Models\UninsuredItem;
use App\Models\UninsuredItemHistory;
use App\Models\UninsuredRequest;

class UninsuredCostService
{

    public function getUninsuredServiceHistory($id)
    {
        $uninsuredList = UninsuredItem::where('service_id', $id)
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        if (empty($uninsuredList)) {
            return [];
        };

        $res = self::getUninsuredItemHistories($uninsuredList[0]['id']);

        $result = [
            'uninsured_list' => $uninsuredList,
            'uninsured_item_list' => $res['uninsured_item_list'],
            'uninsured_request' => $res['uninsured_request']
        ];

        return response()->json($result);
        ;
    }

    public function getUninsuredItemHistories($id)
    {
        $getUninsuredItemLists = UninsuredItemHistory::GetHistories($id);
        $uninsuredItemList = $getUninsuredItemLists->toArray();

        $uninsuredItemIdList = [];

        $uninsuredItemIdList = $getUninsuredItemLists
            ->map(function($item, $key){return $item['id'];
            })
            ->toArray();

        $uninsuredRequests = UninsuredRequest::whereIn('uninsured_item_history_id', $uninsuredItemIdList)
            ->select('uninsured_item_history_id', 'unit_cost')
            ->get()
            ->toArray();

        $result = [
            'uninsured_item_list' => $uninsuredItemList,
            'uninsured_request' => $uninsuredRequests
        ];

        return $result;
    }

    public function firstServiceRegister($param)
    {
        $register = UninsuredItem::create([
            'service_id' => $param['service_id'],
            'start_month' => $param['start_month'],
        ]);

        $firstDatas = [
            ['uninsured_item_id' => $register->id,'item' => '朝食', 'unit' => 1, 'sort' => 1],
            ['uninsured_item_id' => $register->id,'item' => '昼食', 'unit' => 1, 'sort' => 2],
            ['uninsured_item_id' => $register->id,'item' => '夕食', 'unit' => 1, 'sort' => 3],
            ['uninsured_item_id' => $register->id,'item' => 'おやつ', 'unit' => 1, 'sort' => 4],
        ];

        $result = UninsuredItemHistory::insert($firstDatas);

        return $result;
    }

    public function newMonthService($updateParam, $newParam, $newItemList)
    {
        $res = [];
        $res = \DB::transaction(function() use ($updateParam, $newParam, $newItemList){
            // 最新履歴をクローズする
            $update = UninsuredItem::where('id', '=', $updateParam['id'])
            ->update([
                'end_month' => $updateParam['end_month'],
            ]);

            // 新しい保険外品目
            $newService = UninsuredItem::create([
                'service_id' => $newParam['service_id'],
                'start_month' => $newParam['start_month'],
            ]);

            // 保険外品目の最新履歴が持っていた保険外品目履歴を引き継ぐ
            $result = [];
            foreach ($newItemList as $key => $val) {
                $newItemListResult = UninsuredItemHistory::create([
                    'uninsured_item_id' => $newService->id,
                    'item' => $val['item'],
                    'unit_cost' => $val['unit_cost'],
                    'unit' => $val['unit'],
                    'set_one' => $val['set_one'],
                    'fixed_cost' => $val['fixed_cost'],
                    'variable_cost' => $val['variable_cost'],
                    'welfare_equipment' => $val['welfare_equipment'],
                    'meal' => $val['meal'],
                    'daily_necessary' => $val['daily_necessary'],
                    'hobby' => $val['hobby'],
                    'escort' => $val['escort'],
                    'billing_reflect_flg' => $val['billing_reflect_flg'],
                    'sort' => $val['sort'],
                ]);
                $result[$key] = $newItemListResult;
            }
            return $result;
        });
        return $res;
    }

    public function saveUninsuredItem($param)
    {
        if (empty($param['id'])) {
            $result = UninsuredItemHistory::create([
                'uninsured_item_id' => $param['uninsured_item_id'],
                'item' => $param['item'],
                'unit_cost' => $param['unit_cost'],
                'unit' => $param['unit'],
                'set_one' => $param['set_one'],
                'fixed_cost' => $param['fixed_cost'],
                'variable_cost' => $param['variable_cost'],
                'welfare_equipment' => $param['welfare_equipment'],
                'meal' => $param['meal'],
                'daily_necessary' => $param['daily_necessary'],
                'hobby' => $param['hobby'],
                'escort' => $param['escort'],
                'billing_reflect_flg' => $param['billing_reflect_flg'],
                'sort' => $param['sort'],
            ]);
        } else {
            $result = UninsuredItemHistory::where('id', '=', $param['id'])
            ->update($param);
        }

        return $result;
    }

    public function deleteServiceItem($id)
    {
        $uninsuredItemId = UninsuredItemHistory::where('id', $id)
            ->value('uninsured_item_id');
        try {
            $result = UninsuredItemHistory::destroy($id);
            $uninsuredItems = UninsuredItemHistory::where('uninsured_item_id', $uninsuredItemId)
                ->orderByRaw('sort asc, id asc')
                ->get();
            for ($i=0; $i<count($uninsuredItems); $i++) {
                $uninsuredItems[$i]->update(['sort' => $i + 1]);
            }
        } catch (Exception $e) {
            report($e);
            return 0;
        }
        return $result;
    }

    public function saveSort($params)
    {
        $result = UninsuredItemHistory::where('id', $params['id'])
            ->update(['sort' => $params['sort']]);
        
        return $result;
    }
}
