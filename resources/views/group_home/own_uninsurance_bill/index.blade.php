<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること --}}
    {{-- <link rel='stylesheet' href="{{ mix('/css/own_uninsurance_bill/own_uninsurance_bill.css') }}"> --}}
    @if(count($ledgerSheets) > 1)
        @if ($eventType == 'dep_usage_fee_invoice' || $eventType == 'dep_usage_fee_invoice_individual')
        <title>{{ $targetMonth }}_利用料請求</title>
        @else
        <title>{{ $targetMonth }}_利用料領収</title>
        @endif
    @else
        @if ($eventType == 'dep_usage_fee_invoice' || $eventType == 'dep_usage_fee_invoice_individual')
        <title>{{ $targetMonth }}_利用料請求_{{ decrypt($ledgerSheets[0][0]['last_name']) }} {{ decrypt($ledgerSheets[0][0]['first_name']) }}</title>
        @else
        <title>{{ $targetMonth }}_利用料領収_{{ decrypt($ledgerSheets[0][0]['last_name']) }} {{ decrypt($ledgerSheets[0][0]['first_name']) }}</title>
        @endif
    @endif
</head>
<body>
@foreach($ledgerSheets as $sheetIndex => $sheet)
    @if ($eventType == 'dep_usage_fee_invoice' || $eventType == 'dep_usage_fee_invoice_individual')
    @foreach($sheet[1] as $serviceIndex => $service)
        <div class="page">
            <div class="container">
                <img class="receipt_and_bill" src="data:image/jpeg;base64, {{ $imgBill }}">
                <span class="fullname">{{ decrypt($sheet[0]['last_name']) }} {{ decrypt($sheet[0]['first_name']) }} 様</span>
                <span class="account_payable_bank_info">{{ $sheet[0]['account_payable_bank_info'] }} </span>
                <span class="account_payable_info">{{ $sheet[0]['account_payable_info'] }} </span>
                <span class="account_payable_remarks">{{ $sheet[0]['account_payable_remarks'] }} </span>
                @if($sheet[0]['remarks_for_bill'] !== null )
                <pre class="remarks_for_receipt_and_bill">{{ decrypt($sheet[0]['remarks_for_bill']) }} </pre>
                @else
                <pre class="remarks_for_receipt_and_bill"></pre>
                @endif

                <div class="user-info">
                    @if($sheet[0]['user_postal_code'] !== null)
                        <span class="user_postal_code">{{ decrypt($sheet[0]['user_postal_code']) }}</span>
                    @else
                        <span class="user_postal_code"></span>
                    @endif

                    @if($sheet[0]['user_location1'] !== null)
                        <span class="user_location1">{{ decrypt($sheet[0]['user_location1']) }}</span>
                    @else
                        <span class="user_location1"></span>
                    @endif

                    @if($sheet[0]['user_location2'] !== null)
                        <span class="user_location2">{{ decrypt($sheet[0]['user_location2']) }}</span>
                    @else
                        <span class="user_location2"></span>
                    @endif

                    @if($sheet[0]['name'] !== null )
                        <span class="username">{{ decrypt($sheet[0]['name']) }} 様</span>
                    @else
                        <span class="username"></span>
                    @endif
                    <!-- サービス対象期間 -->
                    <span class="service_period">{{ $servicePeriod['name'] }} {{ $servicePeriod['year'] }} 年 {{ $servicePeriod['month'] }} 月分</span>
                    <span class="service_period_start">{{ $sheet[0]['start_date']['name'] }} {{ $sheet[0]['start_date']['year'] }}年{{ $sheet[0]['start_date']['month'] }}月{{ $sheet[0]['start_date']['day'] }}日</span>
                    <span class="service_period_end">{{ $sheet[0]['end_date']['name'] }} {{ $sheet[0]['end_date']['year'] }}年{{ $sheet[0]['end_date']['month'] }}月{{ $sheet[0]['end_date']['day'] }}日</span>
                </div>

                <span class="contractor_number">{{ $sheet[0]['contractor_number'] }}</span>
                <span class="print_time">{{ $systemTimestamp['name'] }} {{ $systemTimestamp['year'] }}年{{ $systemTimestamp['month'] }}月{{ $systemTimestamp['day'] }}日</span>
                <span class="current_page">{{ $serviceIndex + 1 }}</span>
                <span class="total_page">{{ $sheet[1]->count() }}</span>

                <div class="facility">
                    <span class="facility_name">{{ $sheet[0]['facility_name_kanji'] }}</span>
                    <span class="facility_postal_code">{{ $sheet[0]['facility_postal_code'] }}</span>
                    <span class="facility_location">{{ $sheet[0]['facility_location'] }}</span>
                    <span class="fax_number">{{ $sheet[0]['fax_number'] }}</span>
                    <span class="phone_number">{{ $sheet[0]['phone_number'] }}</span>
                </div>

                @php
                    $addIndex = 0;
                @endphp

                <div class="fee_items_table">
                    @if($sheet[1]->first() == $service)
                        @php
                            $addIndex = 1;
                        @endphp
                        <div class="fee_items">
                            <span class="font-size-20">＜利用内訳＞</span>
                        </div>
                        {{-- 国保連請求が可能な施設利用者の場合 --}}
                        @if($sheet[0]['can_be_billed'])
                            <div class="insured_amount_self_row">
                                <span class="insured_amount_self_label font-size-16">保険対象自己負担額</span>
                                @foreach($sheet[2] as $detail)
                                    <span class="insured_amount_self">￥{{ number_format($detail->part_payment) }}</span>
                                @endforeach
                            </div>
                            @php
                                $addIndex = 2;
                            @endphp
                        @endif
                    @endif

                    @foreach($service->values() as $index => $detail)
                        <div class="uninsured_row">
                            @if (isset($detail->public_payment) && $detail->public_payment != null)
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>公費自己負担額</span>
                                </div>
                                <div class="detail-total-cost detail-{{ $index + $addIndex }}">
                                    <span>￥{{ number_format($detail->public_payment) }}</span>
                                </div>
                            @endif
                            @if (isset($detail->limit_over) && $detail->limit_over != null)
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>{{ $detail->name }}</span>
                                </div>
                                <div class="detail-unit-cost detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->limit_over) }}</span>
                                </div>
                                <div class="detail-quantity detail-{{ $index + $addIndex }}">
                                    <span>1</span>
                                </div>
                                <div class="detail-total-cost detail-{{ $index + $addIndex }}">
                                    <span>￥{{ number_format($detail->limit_over) }}</span>
                                </div>
                            @endif
                            @if(isset($detail->unit_cost))
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>{{ $detail->item ? $detail->item : $detail->name }}</span>
                                </div>
                                <div class="detail-unit-cost detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->unit_cost) }}</span>
                                </div>
                                <div class="detail-quantity detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->quantity) }}</span>
                                </div>
                                <div class="detail-total-cost detail-{{ $index + $addIndex }}">
                                    <span>￥{{ number_format($detail->total_cost) }}</span>
                                </div>
                            @endif

                            {{-- 国保連請求が可能な施設利用者の場合 --}}
                            @if($sheet[0]['can_be_billed'] && $detail == '＜保険対象自己負担額　内訳＞')
                                @php
                                    if($index  + $addIndex != 0){
                                        $addIndex +=1;
                                    }
                                @endphp

                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span class="font-size-20">{{ $detail }}</span>
                                </div>
                            @endif

                            @if(isset($detail->service_item_name))
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>{{ $detail->service_item_name }}</span>
                                </div>
                                <div class="detail-unit-cost detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->unit_number) }}</span>
                                </div>
                                <div class="detail-quantity detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->service_count_date) }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="total">
                    @if ($sheet[1]->first() == $service)
                        <!-- ご請求金額 -->
                        <span class="billing_amount">￥{{ number_format($sheet[4]) }}</span>

                        <!-- 保険対象請求分 -->
                        @foreach ($sheet[2] as $detail)
                            <!-- 単位数合計 -->
                            <span class="total_unit">{{ number_format($detail->service_unit_amount) }}</span>
                            <!-- 費用総額 -->
                            <span class="total_amount">￥{{ number_format($detail->total_cost) }}</span>
                            <!-- うち自己負担 -->
                            <span class="amount_self">￥{{ number_format($detail->insurance_self_pay) }}</span>
                            <!-- 負担割合 -->
                            @if (!is_null($detail->own_payment_rate))
                                <span class="benefit_rate">{{ $detail->own_payment_rate }} 割</span>
                            @endif
                        @endforeach

                        <!-- 自費請求分・自己負担 -->
                        @if ($sheet[3])
                            <span class="total_amount_self">￥{{ number_format($sheet[3]->total_amount) }}</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    @endif

    @if ($eventType == 'dep_usage_fee_receipt' || $eventType == 'dep_usage_fee_receipt_individual')
    @foreach($sheet[1] as $serviceIndex => $service)
        <div class="page">
            <div class="container">
                <img class="receipt_and_bill" src="data:image/jpeg;base64, {{ $imgReceipt }}">
                <span class="fullname">{{ decrypt($sheet[0]['last_name']) }} {{ decrypt($sheet[0]['first_name']) }} 様</span>
                <span class="account_payable_bank_info">{{ $sheet[0]['account_payable_bank_info'] }} </span>
                <span class="account_payable_info">{{ $sheet[0]['account_payable_info'] }} </span>
                <span class="account_payable_remarks">{{ $sheet[0]['account_payable_remarks'] }} </span>
                @if($sheet[0]['remarks_for_receipt'] !== null )
                <pre class="remarks_for_receipt_and_bill">{{ decrypt($sheet[0]['remarks_for_receipt']) }} </pre>
                @else
                <pre class="remarks_for_receipt_and_bill"></pre>
                @endif


                <div class="user-info">
                    @if($sheet[0]['user_postal_code'] !== null)
                        <span class="user_postal_code">{{ decrypt($sheet[0]['user_postal_code']) }}</span>
                    @else
                        <span class="user_postal_code"></span>
                    @endif

                    @if($sheet[0]['user_location1'] !== null)
                        <span class="user_location1">{{ decrypt($sheet[0]['user_location1']) }}</span>
                    @else
                        <span class="user_location1"></span>
                    @endif

                    @if($sheet[0]['user_location2'] !== null)
                        <span class="user_location2">{{ decrypt($sheet[0]['user_location2']) }}</span>
                    @else
                        <span class="user_location2"></span>
                    @endif

                    @if($sheet[0]['name'] !== null )
                        <span class="username">{{ decrypt($sheet[0]['name']) }} 様</span>
                    @else
                        <span class="username"></span>
                    @endif
                    <!-- サービス対象期間 -->
                    <span class="service_period">{{ $servicePeriod['name'] }} {{ $servicePeriod['year'] }} 年 {{ $servicePeriod['month'] }} 月分</span>
                    <span class="service_period_start">{{ $sheet[0]['start_date']['name'] }} {{ $sheet[0]['start_date']['year'] }}年{{ $sheet[0]['start_date']['month'] }}月{{ $sheet[0]['start_date']['day'] }}日</span>
                    <span class="service_period_end">{{ $sheet[0]['end_date']['name'] }} {{ $sheet[0]['end_date']['year'] }}年{{ $sheet[0]['end_date']['month'] }}月{{ $sheet[0]['end_date']['day'] }}日</span>
                </div>

                <span class="contractor_number">{{ $sheet[0]['contractor_number'] }}</span>
                <span class="print_time">{{ $systemTimestamp['name'] }} {{ $systemTimestamp['year'] }}年{{ $systemTimestamp['month'] }}月{{ $systemTimestamp['day'] }}日</span>
                <span class="current_page">{{ $serviceIndex + 1 }}</span>
                <span class="total_page">{{ $sheet[1]->count() }}</span>

                <div class="facility">
                    <span class="facility_name">{{ $sheet[0]['facility_name_kanji'] }}</span>
                    <span class="facility_postal_code">{{ $sheet[0]['facility_postal_code'] }}</span>
                    <span class="facility_location">{{ $sheet[0]['facility_location'] }}</span>
                    <span class="fax_number">{{ $sheet[0]['fax_number'] }}</span>
                    <span class="phone_number">{{ $sheet[0]['phone_number'] }}</span>
                </div>

                @php
                    $addIndex = 0;
                @endphp

                <div class="fee_items_table">
                    @if($sheet[1]->first() == $service)
                        @php
                            $addIndex = 1;
                        @endphp
                        <div class="fee_items">
                            <span class="font-size-20">＜利用内訳＞</span>
                        </div>
                        {{-- 国保連請求が可能な施設利用者の場合 --}}
                        @if($sheet[0]['can_be_billed'])
                            <div class="insured_amount_self_row">
                                <span class="insured_amount_self_label font-size-16">保険対象自己負担額</span>
                                @foreach($sheet[2] as $detail)
                                    <span class="insured_amount_self">￥{{ number_format($detail->part_payment) }}</span>
                                @endforeach
                            </div>
                            @php
                                $addIndex = 2;
                            @endphp
                        @endif
                    @endif

                    @foreach($service->values() as $index => $detail)
                        <div class="uninsured_row">
                            @if (isset($detail->public_payment) && $detail->public_payment != null)
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>公費自己負担額</span>
                                </div>
                                <div class="detail-total-cost detail-{{ $index + $addIndex }}">
                                    <span>￥{{ number_format($detail->public_payment) }}</span>
                                </div>
                            @endif
                            @if (isset($detail->limit_over) && $detail->limit_over != null)
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>{{ $detail->name }}</span>
                                </div>
                                <div class="detail-unit-cost detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->limit_over) }}</span>
                                </div>
                                <div class="detail-quantity detail-{{ $index + $addIndex }}">
                                    <span>1</span>
                                </div>
                                <div class="detail-total-cost detail-{{ $index + $addIndex }}">
                                    <span>￥{{ number_format($detail->limit_over) }}</span>
                                </div>
                            @endif
                            @if(isset($detail->unit_cost))
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>{{ $detail->item ? $detail->item : $detail->name }}</span>
                                </div>
                                <div class="detail-unit-cost detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->unit_cost) }}</span>
                                </div>
                                <div class="detail-quantity detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->quantity) }}</span>
                                </div>
                                <div class="detail-total-cost detail-{{ $index + $addIndex }}">
                                    <span>￥{{ number_format($detail->total_cost) }}</span>
                                </div>
                            @endif

                            {{-- 国保連請求が可能な施設利用者の場合 --}}
                            @if($sheet[0]['can_be_billed'] && $detail == '＜保険対象自己負担額　内訳＞')
                                @php
                                    if($index  + $addIndex != 0){
                                        $addIndex +=1;
                                    }
                                @endphp

                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span class="font-size-20">{{ $detail }}</span>
                                </div>
                            @endif

                            @if(isset($detail->service_item_name))
                                <div class="detail-item font-size-16 detail-{{ $index + $addIndex }}">
                                    <span>{{ $detail->service_item_name }}</span>
                                </div>
                                <div class="detail-unit-cost detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->unit_number) }}</span>
                                </div>
                                <div class="detail-quantity detail-{{ $index + $addIndex }}">
                                    <span>{{ number_format($detail->service_count_date) }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="total">
                    @if ($sheet[1]->first() == $service)
                        <!-- ご請求金額 -->
                        <span class="billing_amount">￥{{ number_format($sheet[4]) }}</span>

                        <!-- 保険対象請求分 -->
                        @foreach ($sheet[2] as $detail)
                            <!-- 単位数合計 -->
                            <span class="total_unit">{{ number_format($detail->service_unit_amount) }}</span>
                            <!-- 費用総額 -->
                            <span class="total_amount">￥{{ number_format($detail->total_cost) }}</span>
                            <!-- うち自己負担 -->
                            <span class="amount_self">￥{{ number_format($detail->insurance_self_pay) }}</span>
                            <!-- 負担割合 -->
                            @if (!is_null($detail->own_payment_rate))
                                <span class="benefit_rate">{{ $detail->own_payment_rate }} 割</span>
                            @endif
                        @endforeach

                        <!-- 自費請求分・自己負担 -->
                        @if ($sheet[3])
                            <span class="total_amount_self">￥{{ number_format($sheet[3]->total_amount) }}</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    @endif

@endforeach
</body>
</html>
