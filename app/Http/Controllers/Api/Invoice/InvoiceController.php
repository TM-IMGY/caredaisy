<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Service\Api\Invoice\InvoiceService;
use Exception;
use Log;

class InvoiceController
{
    public function __construct(InvoiceService $invoice)
    {
        $this->invoice = $invoice;
    }

    // 事業所一覧取得API
    public function getFacilities()
    {
        try {
            $result = $this->invoice->generateAllFacilitiesWithDocumentCode();

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result'     => 'OK',
                'facilities' => $result,
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }

    // 事業所一覧取得API v1
    public function getFacilitiesV1(\App\Http\Requests\Api\Invoice\FacilityListRequestV1 $request)
    {
        try {
            $result = $this->invoice->generateAllFacilitiesWithDocumentCodeV1($request->get('terminal_number'));

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result'     => 'OK',
                'facilities' => $result,
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }

    // 請求情報取得API
    public function getInvoices(\App\Http\Requests\Api\Invoice\InvoiceListRequest $request)
    {
        try {
            $result = $this->invoice->generateInvoices($request->get('target'), $request->get('ym'));

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result'   => 'OK',
                'invoices' => $result,
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }

    // 請求情報取得API v1
    public function getInvoicesV1(\App\Http\Requests\Api\Invoice\InvoiceListRequestV1 $request)
    {
        try {
            $result = $this->invoice->generateInvoicesV1(
                $request->get('terminal_number'),
                $request->get('target'),
                $request->get('ym')
            );

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result'   => 'OK',
                'invoices' => $result,
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function updateInvoices(\App\Http\Requests\Api\Invoice\InvoiceUpdateRequest $request)
    {
        try {
            $this->invoice->updateInvoices($request->post('invoices'));

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result' => 'OK',
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function updateDocuments(\App\Http\Requests\Api\Invoice\DocumentUpdateRequest $request)
    {
        try {
            $this->invoice->updateDocuments($request->post('documents'));

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result' => 'OK',
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function updateAttachments(\App\Http\Requests\Api\Invoice\DocumentUpdateRequest $request){
        try {
            $this->invoice->updateAttachments($request->post('documents'));

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return [
                'result' => 'OK',
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'result' => 'NG',
                'error'  => $e->getMessage(),
            ];
        }
    }
}
