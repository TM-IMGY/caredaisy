<!doctype html>
{{-- <html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ $year_month'] }}_介護給付費</title>
  <link media="all" rel='stylesheet' href="{{ mix('/css/group_home/pdf_demo/pdf_demo_all.css') }}">
  <link media="all" rel='stylesheet' href="{{ asset('/css/group_home/pdf_demo/pdf_all_yoshiki_6-3.css') }}">
</head> --}}
<body>
    <div class="meisai">
      {{-- サービス種別が33または36の場合 --}}
      @if(
        $data['benefit_status'][$s][$j][0]['service_type_code'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE
        || $data['benefit_status'][$s][$j][0]['service_type_code'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE
      )
        <img src="{{ asset('/chouhyou/yoshiki_6-3.jpg')}}">
      @elseif($data['benefit_status'][$s][$j][0]['service_type_code'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTION_SPECIFIED_FACILITY_RESIDENT_CARE)
        <img src="{{ asset('/chouhyou/yoshiki_6-4.jpg')}}">
      @endif

      {{-- 利用者毎の公費情報 --}}
      <div>
        {{-- 公費負担者番号 --}}
        <div class="bearer_number">{{ $user_public_expense_information['bearer_number'] }}</div>
        {{-- 公費受給者番号 --}}
        <div class="recipient_number">{{ $user_public_expense_information['recipient_number'] }}</div>
      </div>

      {{-- 提出年月 --}}
      <div class="yaers_and_months">
        <span class="yaers">{{ $japanese_imperial_year['year'] }}</span>
        <span class="months">{{ $japanese_imperial_year['month'] }}</span>
      </div>

      {{-- 保険者番号 --}}
      <div class="insurer_no">{{ $facility_user['insurer_no'] }}</div>

      {{-- 被保険者 --}}
      <div class="facility_info">
        {{-- 被保険者番号 --}}
        <span class="insured_no">{{ $facility_user['insured_no'] }}</span>
        {{-- フリガナ --}}
        <div class="name_kana">
          <span>{{ $facility_user['last_name_kana'] }}</span>
          <span>{{ $facility_user['first_name_kana'] }}</span>
        </div>
        {{-- 氏名 --}}
        <div class="name">
          <span>{{ $facility_user['last_name'] }}</span>
          <span>{{ $facility_user['first_name'] }}</span>
        </div>
        {{-- 生年月日 --}}
        {{-- 元号 --}}
        <div class="japanese_calender">
          @if($facility_user['birthday']['name'] == '明治')
            <span class="meiji">〇</span>
          @elseif($facility_user['birthday']['name'] == '大正')
            <span class="taisho">〇</span>
          @else
            <span class="showa">〇</span>
          @endif
        </div>
        {{-- 年月日 --}}
        <div class="date_of_birth">
          <span class="year">{{ $facility_user['birthday']['year'] }}</span>
          <span class="month">{{ $facility_user['birthday']['month'] }}</span>
          <span class="day">{{ $facility_user['birthday']['day'] }}</span>
        </div>
        {{-- 性別 --}}
        <div class="gender">
          @if($facility_user['gender'] == '1')
            <span class="man">〇</span>
          @else
            <span class="woman">〇</span>
          @endif
        </div>
        {{-- 要介護状態区分 --}}
        <div class="care_level">
          @if($user_care_information['care_level_id'] == '6')
            {{-- 要介護1 --}}
            <span class="one">〇</span>
          @elseif($user_care_information['care_level_id'] == '7')
            {{-- 要介護2 --}}
            <span class="two">〇</span>
          @elseif($user_care_information['care_level_id'] == '8')
            {{-- 要介護3 --}}
            <span class="three">〇</span>
          @elseif($user_care_information['care_level_id'] == '9')
            {{-- 要介護4 --}}
            <span class="four">〇</span>
          @elseif($user_care_information['care_level_id'] == '10')
            {{-- 要介護5 --}}
            <span class="five">〇</span>
          {{-- 介護レベルと見かけの数値は異なるので注意する --}}
          {{-- 要支援1 --}}
          @elseif($user_care_information['care_level_id'] == '4')
            <span class="care_level_12 service_type_35"></span>
          {{-- 要支援2 --}}
          @elseif($user_care_information['care_level_id'] == '5')
            <span class="care_level_13 service_type_35"></span>
          @endif
        </div>
        {{--  認定有効開始--}}
        <div class="period_start_calender">
          @if($user_care_information['care_period_start']['name'] == '平成')
            <div class="heisei">〇</div>
          @elseif($user_care_information['care_period_start']['name'] == '令和')
            <div class="reiwa">〇</div>
          @endif
        </div>
        <div class="period_start_date">
          <span class="year">{{ $user_care_information['care_period_start']['year'] }}</span>
          <span class="month">{{ $user_care_information['care_period_start']['month'] }}</span>
          <span class="day">{{ $user_care_information['care_period_start']['day'] }}</span>
        </div>
        {{-- 認定有効期間終了日 --}}
        <div class="period_end_date">
          <span class="year">{{ $user_care_information['care_period_end']['year'] }}</span>
          <span class="month">{{ $user_care_information['care_period_end']['month'] }}</span>
          <span class="day">{{ $user_care_information['care_period_end']['day'] }}</span>
        </div>
      </div>{{-- 被保険者end --}}

      {{-- 請求事業者 --}}
      <div class="facilities">
        {{-- 事業者番号 --}}
        <div class="facility_number">{{ $facility['facility_number'] }}</div>
        {{-- 事業者名称 --}}
        <div class="facility_name_kanji">{{ $facility['facility_name_kanji'] }}</div>
        {{-- 郵便番号--}}
        <div class="postal_code">{{ $facility['postal_code'] }}</div>
        {{-- 住所 --}}
        <div class="location">{{ $facility['location'] }}</div>
        {{-- 電話番号 --}}
        <div class="phone_number">{{ $facility['phone_number'] }}</div>
      </div>{{-- 請求事業者end --}}

      {{-- 入居年月日等 --}}
      <div>
        {{-- 入居年月日元号 --}}
        <div class="start_calender">
          @if($facility_user['start_date']['name'] == '平成')
            <div class="heisei">〇</div>
          @else
            <div class="reiwa">〇</div>
          @endif
        </div>
        <div class="in_out_info">
          {{-- 入居年月日 --}}
          <span class="start_date_year">{{ $facility_user['start_date']['year'] }}</span>
          <span class="start_date_month">{{ $facility_user['start_date']['month'] }}</span>
          <span class="start_date_day">{{ $facility_user['start_date']['day'] }}</span>
          {{-- 退去年月日 --}}
          <span class="end_date_year">{{ $facility_user['end_date']['year'] }}</span>
          <span class="end_date_month">{{ $facility_user['end_date']['month'] }}</span>
          <span class="end_date_day">{{ $facility_user['end_date']['day'] }}</span>
          {{-- 入居実日数 --}}
          <span class="actual_number">{{ $stayInOutDays['actualDays'] }}</span>
          {{-- 外泊日数 --}}
          <span class="stay_out_overnight">{{ $stayInOutDays['stayOut'] }}</span>
        </div>
      </div>{{-- 入居年月日等end --}}

      {{-- 入居前の状況 --}}
      <div class="before_in_status_id">
        @if($facility_user['before_in_status_id'] == '1')
          {{-- 居宅 --}}
          <span class="home">〇</span>
        @elseif($facility_user['before_in_status_id'] == '2')
          {{-- 医療機関 --}}
          <span class="medical_institution">〇</span>
        @elseif($facility_user['before_in_status_id'] == '3')
          {{-- 介護老人福祉施設 --}}
          <span class="welfare_facility">〇</span>
        @elseif($facility_user['before_in_status_id'] == '4')
          {{-- 介護老人保健施設 --}}
          <span class="health_facility">〇</span>
        @elseif($facility_user['before_in_status_id'] == '5')
          {{-- 介護療養型医療施設 --}}
          <span class="medical_facility">〇</span>
        @elseif($facility_user['before_in_status_id'] == '6')
          {{-- 認知症対応型共同生活介護 --}}
          <span class="dementia">〇</span>
        @elseif($facility_user['before_in_status_id'] == '7')
          {{-- 特定施設入居者生活介護 --}}
          <span class="specific_facility">〇</span>
        @elseif($facility_user['before_in_status_id'] == '8')
          {{-- その他 --}}
          <span class="others">〇</span>
        @else
          {{-- 介護医療院 --}}
          <span class="medical_clinic">〇</span>
        @endif
      </div>{{-- 入居前の状況end --}}

      {{-- 退居後の状況 --}}
      <div class="after_out_status_id">
        @if($facility_user['after_out_status_id'] == '1')
          {{-- 居宅 --}}
          <span class="home">〇</span>
        @elseif($facility_user['after_out_status_id'] == '2')
          {{-- 医療機関入院 --}}
          <span class="medical_institution">〇</span>
        @elseif($facility_user['after_out_status_id'] == '3')
          {{-- 死亡 --}}
          <span class="death">〇</span>
        @elseif($facility_user['after_out_status_id'] == '4')
          {{-- その他 --}}
          <span class="others">〇</span>
        @elseif($facility_user['after_out_status_id'] == '5')
          {{-- 介護老人福祉施設入所 --}}
          <span class="welfare_facility">〇</span>
        @elseif($facility_user['after_out_status_id'] == '6')
          {{-- 介護老人保健施設入所 --}}
          <span class="health_facility">〇</span>
        @elseif($facility_user['after_out_status_id'] == '7')
          {{-- 介護療養型医療施設入院 --}}
          <span class="medical_facility">〇</span>
        @elseif($facility_user['after_out_status_id'] == '8')
          {{-- 介護医療院入所 --}}
          <span class="medical_clinic">〇</span>
        @endif
      </div>{{-- 退去後の状況end --}}

      {{-- 給付費明細欄 --}}
      <div class="benefit_status_area">
        @for ($i = 0; $i < count($benefit_status); $i++)
          <div class="benefit_status">
            {{-- サービス内容 --}}
            <span class="service_item_name">{{ $benefit_status[$i]['service_item_name'] }}</span>
            {{-- サービスコード --}}
            <span class="service_code">{{ $benefit_status[$i]['service_code'] }}</span>
            {{-- 単位数 --}}
            <span class="unit_number">{{ $benefit_status[$i]['unit_number'] }}</span>
            {{-- 回数日数 --}}
            <span class="service_count_date">{{ $benefit_status[$i]['service_count_date'] }}</span>
            {{-- サービス単位数 --}}
            <span class="service_unit_amount">{{ $benefit_status[$i]['service_unit_amount'] }}</span>
            {{-- 公費分回数等 --}}
            <span class="public_spending_count">{{ $benefit_status[$i]['public_spending_count'] }}</span>
            {{-- 公費対象単位数 --}}
            <span class="public_expenditure_unit">{{ $benefit_status[$i]['public_expenditure_unit'] }}</span>
            {{-- 摘要 --}}
            @if( isset($benefit_status[$i]['service_code_data']['service_type_code']) )
            <span id="death_date">{{ $benefit_status[$i]['facility_user']['death_date'] }}</span>
            @endif
          </div>
        @endfor
        @if(reset($check_benefit_status) == $benefit_status)
          {{-- 合計 --}}
          <div class="total">
            {{-- サービス単位数の合計 --}}
            <span class="service_unit_amount">{{ $billing_total['service_unit_amount'] }}</span>
            {{-- 公費対象単位数の合計 --}}
            <span class="public_spending_unit">{{ $billing_total['public_expenditure_unit'] }}</span>
          </div>
        @endif
      </div>{{-- 給付費明細欄end --}}

      @if(reset($check_benefit_status) == $benefit_status)
        {{-- 請求額集計欄 --}}
        <div>
          {{-- 外部利用型給付上限単位数 --}}
          <div class="classification_support_limit_units_33">{{ $billing_total['classification_support_limit_units'] }}</div>
          {{-- 外部利用型上限管理対象単位数 --}}
          <div class="classification_support_benefit_limit_unit_33">{{ $billing_total['classification_support_benefit_limit_unit'] }}</div>
          {{-- 外部利用型外給付単位数 --}}
          <div class="classification_support_benefit_unit_33">{{ $billing_total['classification_support_benefit_unit'] }}</div>
          {{-- 給付単位数 --}}
          <div class="unit_rate_33">
            {{-- 保険分 --}}
            <span class="benefit_unit_33">{{ $billing_total['benefit_unit'] }}</span>
            {{-- 公費分 --}}
            <span class="public_benefit_unit_33">{{ $billing_total['public_benefit_unit'] }}</span>
          </div>
          {{-- 単位数単価 --}}
          <div class="unit_price_33">{{ $billing_total['unit_price'] }}</div>
          {{-- 給付率 --}}
          <div class="rate_33">
            {{-- 保険分 --}}
            <span class="benefit_rate_33">{{ $billing_total['benefit_rate'] }}</span>
            {{-- 公費分 --}}
            <span class="public_benefit_rate_33">{{ $billing_total['public_benefit_rate'] }}</span>
          </div>
          {{-- 請求額（円） --}}
          <div class="billing_amount_33">
            {{-- 保険分 --}}
            <span class="insurance_benefit_33">{{ $billing_total['insurance_benefit'] }}</span>
            {{-- 公費分 --}}
            <span class="public_spending_amount_33">{{ $billing_total['public_spending_amount'] }}</span>
          </div>
          {{-- 利用者負担額 --}}
          <div class="user_burden_33">
            {{-- 保険分 --}}
            <span class="part_payment_33">{{ $billing_total['part_payment'] }}</span>
            {{-- 公費分 --}}
            <span class="public_payment_33">{{ $billing_total['public_payment'] }}</span>
          </div>
        </div>{{-- 請求額集計欄end --}}
      @endif
      <div class="sheet_count_33">
        <span>{{ $all_sheet }}</span>
        <span>{{ $sheet_num }}</span>
      </div>
    </div>
</body>
</html>
