<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Laravelのsizeはutf8の文字数を正しくカウントしないのでカスタムバリデーションを行う
 */
class MbSize implements Rule
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $message;

    /**
     * Create a new rule instance.
     *
     * @param  int      $size
     * @param  string   ?$message
     * @return void
     */
    public function __construct(int $size, $message = null)
    {
        $this->size = $size;
        // $messageが未指定の場合はデフォルトのメッセージを使用する
        $this->message = empty($message) ? ":attribute は{$this->size}文字で入力してください。" : $message;
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
        return $this->size == mb_strlen($value);
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
