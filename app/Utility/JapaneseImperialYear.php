<?php

namespace App\Utility;

class JapaneseImperialYear
{
  /**
   * 和暦を返す
   * @param string $date 変換対象となる西暦の文字列
   * @return array $result 和暦 [name:元号,year:年]
   */
    public static function get($date) : array
    {
      // 返すデータのプレースホルダ
        $data = [];

        $eraList = [
        // 令和(2019年5月1日〜)
        ['name' => '令和', 'ryaku' => 'R', 'start_date' => '20190501'],
        // 平成(1989年1月8日〜)
        ['name' => '平成', 'ryaku' => 'H', 'start_date' => '19890108'],
        // 昭和(1926年12月25日〜)
        ['name' => '昭和', 'ryaku' => 'S', 'start_date' => '19261225'],
        // 大正(1912年7月30日〜)
        ['name' => '大正', 'ryaku' => 'T', 'start_date' => '19120730'],
        // 明治(1873年1月1日〜)
        ['name' => '明治', 'ryaku' => 'M', 'start_date' => '18730101'],
        ];

        $dt = new \DateTime($date);

        foreach ($eraList as $era) {
            $eraStartDate = new \DateTime($era['start_date']);
            if ($dt->format('Ymd') >= $eraStartDate->format('Ymd')) {
                $data['name'] = $era['name'];
                $data['ryaku'] = $era['ryaku'];
                $year = $dt->format('Y') - $eraStartDate->format('Y') + 1;
                $data['year'] = sprintf('%02d', $year);
                $data['month'] = $dt->format('m');
                $data['day'] = $dt->format('d');
                break;
            }
        }

        return $data;
    }
}
