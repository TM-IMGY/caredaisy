<?php

namespace App\Lib\Entity;

/**
 * 被保険者番号。個人情報なので注意する。
 * 一意に識別されるため値オブジェクトに配置しない。
 */
class InsuredNo
{
    private string $value;

    /**
     * コンストラクタ
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
