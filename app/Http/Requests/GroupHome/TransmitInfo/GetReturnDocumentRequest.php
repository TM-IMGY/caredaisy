<?php

namespace App\Http\Requests\GroupHome\TransmitInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetReturnDocumentRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // お知らせか通知文書の権限チェックかを判定する。
        if ($this::has('id') && $this->id != null) {
            if ($this::has('index') && $this->index != null) {
                return $this->authorizeReturnDocumentIdForNews($this->id) &&
                    $this->authorizeReturnAttachmentIndex($this->index, $this->id);
            } else {
                return $this->authorizeReturnDocumentIdForNotificationDocument($this->id);
            }
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('この操作は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
