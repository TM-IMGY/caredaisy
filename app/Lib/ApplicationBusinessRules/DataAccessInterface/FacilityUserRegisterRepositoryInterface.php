<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\FacilityUserRegister;

/**
 * 事業所のユーザーの名簿のリポジトリのインターフェース。
 */
interface FacilityUserRegisterRepositoryInterface
{
    /**
     * 事業所のユーザーの名簿を返す。
     * @param int $accountId アカウントのID
     */
    public function find(int $accountId): FacilityUserRegister;
}
