<?php

namespace App\Lib\Entity;

/**
 * サービス項目コードの集まりのクラス。
 * サービス項目コードを単体ではなく、ある程度のまとまりとして扱う想定が増えたため作成した。
 */
class ServiceItemCodes
{
    /**
     * @var ServiceItemCode[] サービスコードの集まり。
     */
    private array $serviceItemCodes;

    /**
     * コンストラクタ
     * @param ServiceItemCode[] サービスコードの集まり。
     */
    public function __construct(array $serviceItemCodes)
    {
        $this->serviceItemCodes = $serviceItemCodes;
    }

    /**
     * 特定入所者サービスを持つかを返す。
     * @return bool
     */
    public function hasIncompetentResident(): bool
    {
        $find = false;
        foreach ($this->serviceItemCodes as $serviceItemCode) {
            if ($serviceItemCode->isIncompetentResident()) {
                $find = true;
                break;
            }
        }
        return $find;
    }

    /**
     * 特別診療費コードを持つかを返す。
     * @return bool
     */
    public function hasSpecialMedical(): bool
    {
        $find = false;
        foreach ($this->serviceItemCodes as $serviceItemCode) {
            if ($serviceItemCode->isSpecialMedical()) {
                $find = true;
                break;
            }
        }
        return $find;
    }

    /**
     * サービスコードを返す。
     * @param int $serviceItemCodeId
     * @return ServiceItemCode
     */
    public function find(int $serviceItemCodeId): ServiceItemCode
    {
        $find = null;
        foreach ($this->serviceItemCodes as $serviceItemCode) {
            if ($serviceItemCode->getServiceItemCodeId() === $serviceItemCodeId) {
                $find = $serviceItemCode;
                break;
            }
        }
        return $find;
    }

    /**
     * サービスコード(小計)を返す。
     * @return ?ServiceItemCode
     */
    public function findSubTotal(): ?ServiceItemCode
    {
        $find = null;
        foreach ($this->serviceItemCodes as $serviceItemCode) {
            if ($serviceItemCode->isSubTotal()) {
                $find = $serviceItemCode;
                break;
            }
        }
        return $find;
    }

    /**
     * サービスコード(合計)を返す。
     * @return ?ServiceItemCode
     */
    public function findTotal(): ?ServiceItemCode
    {
        $find = null;
        foreach ($this->serviceItemCodes as $serviceItemCode) {
            if ($serviceItemCode->isTotal()) {
                $find = $serviceItemCode;
                break;
            }
        }
        return $find;
    }

    /**
     * @return ServiceItemCode[]
     */
    public function getAll(): array
    {
        return $this->serviceItemCodes;
    }
}
