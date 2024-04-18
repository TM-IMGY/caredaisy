<?php

namespace App\Lib\InMemoryRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRegisterRepositoryInterface;
use App\Lib\Entity\FacilityUserRegister;

/**
 * 事業所のユーザーの名簿のインメモリのリポジトリ。
 */
class FacilityUserRegisterInMemoryRepository implements FacilityUserRegisterRepositoryInterface
{
    private array $db;

    public function __construct()
    {
        $this->db = [];
    }

    /**
     * 事業所のユーザーの名簿を返す。
     * @param int $accountId アカウントのID
     */
    public function find(int $accountId): FacilityUserRegister
    {
        return new FacilityUserRegister($this->db[$accountId]);
    }

    /**
     * アクセス可能な施設利用者IDを挿入する。
     * @param int $accountId アカウントID
     * @param int $facilityUserId 施設利用者ID
     */
    public function insert(
        int $accountId,
        int $facilityUserId
    ): void {
        if (!array_key_exists($accountId, $this->db)) {
            $this->db[$accountId] = [];
        }

        $this->db[$accountId][] = $facilityUserId;
    }
}
