<?php

namespace App\Lib\Entity;

/**
 * 事業所のユーザーの名簿。
 */
class FacilityUserRegister
{
    /**
     * @var int[] 事業所のユーザーのID全て。
     */
    private array $ids;

    /**
     * @param int[] 事業所のユーザーのID全て。
     */
    public function __construct(
        array $ids
    ) {
        $this->ids = $ids;
    }

    /**
     * 施設利用者のID全てを返す。
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
