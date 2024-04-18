<?php

namespace App\Http\Controllers\Api\Hospitac;

use App\Service\Hospitac\HospitacService;
use Exception;
use Log;

class HospitacController
{
    public function __construct(HospitacService $hospitac)
    {
        $this->hospitac = $hospitac;
    }

    /**
     * HOSPITAC連携ファイル情報登録
     */
    public function fileUpload(\App\Http\Requests\Api\Hospitac\FileUploadRequest $request)
    {
        try {
            // HOSPITAC連携ファイル情報登録処理
            $id = $this->hospitac->fileUpload($request->post());
            // ファイル取込
            $this->hospitac->fileImport($request->post(), $id);
            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result_info'  => [
                    'result' => 'OK',
                ],
            ];
        } catch (Exception $e) {
            report($e);
            return response()->json([
                'result_info'  => [
                    'result' => 'NG',
                    'result_code' => 'E00008',
                    'error'  => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
