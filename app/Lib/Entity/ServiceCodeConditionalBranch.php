<?php

namespace App\Lib\Entity;

/**
 * サービスコードの条件分岐表クラス。
 * TODO: サービス種類を暗黙的に持っているので明示する。
 */
class ServiceCodeConditionalBranch
{
    /**
     * @var array 条件分岐表はデータを連想配列形式で持つ。
     * 適切にプロパティに切り分けたいがサービス種類などの条件によって在り方が変わるため、やる際は相応の時間が必要になる。
     * コミットされている構造は下記の通りになる。
     * {block0: [レコード1,レコード2,...], block1: [レコード1,レコード2,...]}
     */
    private array $blocks;

    /**
     * コンストラクタ
     * @param array $blocks
     */
    public function __construct(
        array $blocks
    ) {
        $this->blocks = $blocks;
    }

    /**
     * サービス項目コードの要求介護レベルを返す。
     * TODO: このクラスではなくサービス項目コードクラスの責任にしたい(現状はテーブル構造的に面倒)。
     * @param ServiceItemCode $serviceItemCode
     */
    public function findServiceItemCodeCareLevel(ServiceItemCode $serviceItemCode): ?int
    {
        foreach ($this->blocks as $block) {
            foreach ($block as $record) {
                if ($record['service_item_code'] === $serviceItemCode->getServiceItemCode()) {
                    return array_key_exists('care_level', $record) ? $record['care_level'] : null;
                }
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
