<?php

use App\Models\WeeklyService;
use Illuminate\Database\Seeder;

use App\Models\WeeklyServiceCategory;

class WeeklyServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * for test
     * @return void
     */
    public function run()
    {
        WeeklyService::truncate();
        WeeklyServiceCategory::truncate();

        $categories = [
            '食事' => [
                '朝食介助',
                '昼食介助',
                '夕食介助',
                'おやつ介助',
                '飲水介助'
            ],
            '服薬・バイタル' => [
                '服薬介助',
                'バイタル測定'
            ],
            '身体介護' => [
                '起床介助',
                '口腔ケア',
                '更衣介助',
                '排泄介助',
                '入浴介助',
                '清拭',
                '足浴',
                '身体整容',
                '体位変換',
                '就寝介助'
            ],
            '機能訓練' => [
                '歩行介助',
                '外出介助',
                'レクリエーション',
                'リハビリテーション'
            ],
            '声かけ・見守り' => [
                '食事声掛け・見守り',
                '更衣声掛け・見守り',
                '歩行見守り',
                '巡視',
                '巡回'
            ],
        ];

        // 通常のサービス(共通マスタ)
        foreach ($categories as $name => $services) {
            $category = factory(WeeklyServiceCategory::class)->states('weekly', 'common')->create(['description' => $name]);
            foreach ($services as $service) {
                factory(WeeklyService::class)->create([
                    'weekly_service_category_id' => $category->id,
                    'type' => $category->type,
                    'facility_id' => 0,
                    'description' => $service
                ]);
            }
        }

        factory(WeeklyServiceCategory::class)->states('weekly', 'common')->create(['description' => 'その他']);

        // 日常生活上の活動
        $categories = [
            '食事' => ['朝食', '昼食', '夕食', 'おやつ', '水分補給', '食事準備', '食事片付け', '、'],
            '生活動作' => ['起床', '歯磨き', '着替え', '整容', 'トイレ', '入浴', '昼寝', '洗濯', '掃除', '就寝', '、'],
            '機能訓練' => ['歩行訓練', '散歩', 'レクリエーション参加', '体操', '買い物', '運動', '、'],
            '服薬・バイタル' => ['服薬', '体温・血圧を測る', '体重を測る', '、'],
            '趣味・娯楽' => ['テレビ鑑賞', '趣味の活動', 'サークル活動', '、'],
        ];

        $serviceList = collect();
        foreach ($categories as $name => $services) {
            $category = WeeklyServiceCategory::create([
                'facility_id' => 0,
                'type' => WeeklyServiceCategory::TYPE_EVERYDAY,
                'description' => $name
            ]);

            collect($services)->each(function ($service) use ($category, $serviceList) {
                $serviceList->push([
                    'facility_id' => 0,
                    'type' => WeeklyService::TYPE_EVERYDAY,
                    'description' => $service,
                    'weekly_service_category_id' => $category->id
                ]);
            });
        }
        WeeklyService::insert($serviceList->all());

        // 週単位以外のサービス
        $categories = [
            '訪問診療', '訪問看護師', '訪問歯科', '通院', '薬局受診', '理美容', '家族との面会', '家族との電話', '外出レクリエーション', '買い物', 'ボランティア活動', 'イベント', '、',
        ];

        $serviceList = collect();
        foreach ($categories as $description) {
            $serviceList->push([
                'facility_id' => 0,
                'type' => WeeklyService::TYPE_NOT_WEEKLY,
                'description' => $description,
                'weekly_service_category_id' => null
            ]);
        }
        WeeklyService::insert($serviceList->all());
    }
}
