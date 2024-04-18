<?php

namespace App\Lib\DomainService;

/**
 * 法別番号の仕様クラス。
 */
class LegalNumberSpecification
{
    // 生活保護
    public const PUBLIC_ASSISTANCE = 12;

    // 自立更生
    public const REHABILITATION = 15;

    // 中国残留邦人等
    public const CHINA = 25;

    // 難病公費
    public const INCURABLE_DISEASE = 54;
}
