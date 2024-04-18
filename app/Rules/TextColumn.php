<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * MySQLのTEXT型カラムの最大値65535バイト以下であるかのカスタムバリデーションルール
 */
class TextColumn implements Rule
{
    /**
     * 最大バイト数
     */
    const MAX_BYTE = 65535;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // そもそも文字列でない場合は本バリデーションをスルー
        if (!is_string($value)) return true;

        return strlen($value) <= self::MAX_BYTE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeに登録出来る文字数上限を超過しています。';
    }
}
