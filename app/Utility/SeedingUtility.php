<?php

namespace App\Utility;

class SeedingUtility
{
  /**
   * 与えられたパスのCSVファイルを読み込んで返す
   * @param string $path csvファイルのパス
   * @return array
   */
    public static function getData($path){
      // csvを読み込んでファイルオブジェクトを作成
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV);

      // ファイルオブジェクトを配列に変換
        $record = [];
        foreach ($file as $line) {
            if (!is_null($line[0])) {
                $record[] = $line;
            }
        }

      // シーディングするデータのキーを取得
        $keyList = $record[0];

      // シーディングするデータを取得
        $dataList = [];
        for ($i = 1,$cnt = count($record); $i < $cnt; $i++) {
            $list = [];
            for ($keyID = 0,$keyCnt = count($keyList); $keyID < $keyCnt; $keyID++) {
                if ($record[$i][$keyID] !== '') {
                    $list[$keyList[$keyID]] = $record[$i][$keyID];
                }
            }
            // TODO: created_atとupdated_atが同じ時刻を参照するようにする。
            $list['created_at'] = new \DateTime();
            $list['updated_at'] = new \DateTime();
            $dataList[] = $list;
        }

        return $dataList;
    }
}
