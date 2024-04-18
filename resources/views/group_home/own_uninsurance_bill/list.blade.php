<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること --}}
    {{-- <link rel='stylesheet' href="{{ mix('/css/own_uninsurance_bill/own_uninsurance_bill_lsit.css') }}"> --}}
    <title>利用料請求書一覧_{{ $targetYM }}</title>
</head>
<body>
    @foreach($page as $index1 => $details)
        <div class="page">
            <div class="container">
                @if($index1 == 0)
                    <img class="image_file" src="data:image/jpeg;base64, {{ $imgBillList }}">
                    <span class="facility_name">{{$header['facility_name']}}</span>
                    <span class="system_timestamp">{{ $header['system_timestamp']['name'] }} {{ $header['system_timestamp']['year'] }}年{{ $header['system_timestamp']['month'] }}月{{ $header['system_timestamp']['day'] }}日</span>
                    <span class="service_period">{{ $header['service_period']['name'] }} {{ $header['service_period']['year'] }} 年 {{ $header['service_period']['month'] }} 月分</span>
                    <span class="current_page">{{ $index1+1 }}</span>
                    <span class="max_page">{{ count($page) }}</span>
                    <div class="header">
                        <span class="total_user_count header_data">{{$header['total_user_count']}}</span>
                        <span class="total_unit header_data">{{$header['total_unit']}}</span>
                        <span class="total_amount header_data">{{$header['total_amount']}}</span>
                        <span class="billing_insurance_benefit header_data">{{$header['billing_insurance_benefit']}}</span>
                        <span class="billing_part_payment header_data">{{$header['billing_part_payment']}}</span>
                        <span class="billing_public_insurance_benefit header_data">{{$header['billing_public_insurance_benefit']}}</span>
                        <span class="billing_public_part_payment header_data">{{$header['billing_public_part_payment']}}</span>
                        <span class="uninsured_self_total header_data">{{$header['uninsured_self_total']}}</span>
                        <span class="total_amount_self header_data">{{$header['total_amount_self']}}</span>
                    </div>
                    <div class="detail">
                        @foreach($details as $index => $detail)
                            <div>
                                <span class="fullname detail-{{ $index }}">{{$detail['fullname']}}</span>
                                <span class="honorifics detail-{{ $index }}">様</span>
                                <span class="benefit_rate detail-{{ $index }}">{{$detail['benefit_rate']}}</span>
                                <span class="total_unit detail-{{ $index }}">{{$detail['total_unit']}}</span>
                                <span class="total_amount detail-{{ $index }}">{{$detail['total_amount']}}</span>
                                <span class="billing_insurance_benefit detail-{{ $index }}">{{$detail['billing_insurance_benefit']}}</span>
                                <span class="billing_part_payment detail-{{ $index }}">{{$detail['billing_part_payment']}}</span>
                                <span class="billing_public_insurance_benefit detail-{{ $index }}">{{$detail['billing_public_insurance_benefit']}}</span>
                                <span class="billing_public_part_payment detail-{{ $index }}">{{$detail['billing_public_part_payment']}}</span>
                                <span class="uninsured_self_total detail-{{ $index }}">{{$detail['uninsured_self_total']}}</span>
                                <span class="total_amount_self detail-{{ $index }}">{{$detail['total_amount_self']}}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($index1 > 0)
                    <img class="image_file" src="data:image/jpeg;base64, {{ $imgBillList2 }}">
                    <span class="current_page2">{{ $index1+1 }}</span>
                    <span class="max_page2">{{ count($page) }}</span>
                    <div class="detail">
                        @foreach($details as $index => $detail)
                            <div>
                                <span class="fullname detail2-{{ $index }}">{{$detail['fullname']}}</span>
                                <span class="honorifics detail2-{{ $index }}">様</span>
                                <span class="benefit_rate detail2-{{ $index }}">{{$detail['benefit_rate']}}</span>
                                <span class="total_unit detail2-{{ $index }}">{{$detail['total_unit']}}</span>
                                <span class="total_amount detail2-{{ $index }}">{{$detail['total_amount']}}</span>
                                <span class="billing_insurance_benefit detail2-{{ $index }}">{{$detail['billing_insurance_benefit']}}</span>
                                <span class="billing_part_payment detail2-{{ $index }}">{{$detail['billing_part_payment']}}</span>
                                <span class="billing_public_insurance_benefit detail2-{{ $index }}">{{$detail['billing_public_insurance_benefit']}}</span>
                                <span class="billing_public_part_payment detail2-{{ $index }}">{{$detail['billing_public_part_payment']}}</span>
                                <span class="uninsured_self_total detail2-{{ $index }}">{{$detail['uninsured_self_total']}}</span>
                                <span class="total_amount_self detail2-{{ $index }}">{{$detail['total_amount_self']}}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</body>
</html>
