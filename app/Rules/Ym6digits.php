<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 数字6桁(yyyymm)に年月妥当性を持たせるバリデーションを行う
 */
class Ym6digits implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $month = mb_substr($value, 4, 2);
        $date = '01';
        $year = mb_substr($value, 0, 4);
        return checkdate($month, $date, $year);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '[E00004]:attribute を正しい年月で指定してください。';
    }
}
