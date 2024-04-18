<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Laravelのmaxは意図した動作にならないため最大文字数チェックをカスタム実装
 */
class MaxLength implements Rule
{
    /**
     * @var int
     */
    private $max;

    /**
     * @var string
     */
    private $message;

    /**
     * Create a new rule instance.
     *
     * @param  int  $max
     * @return void
     */
    public function __construct(int $max, $message = null)
    {
        $this->max = $max;
        $this->message = empty($message) ? ":attribute は{$this->size}文字以内で入力してください。" : $message;
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
        return $this->max >= mb_strlen($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
