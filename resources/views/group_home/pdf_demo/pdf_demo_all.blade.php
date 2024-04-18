{{-- author eikeda --}}
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ $data['year_month'] }}_介護給付費</title>
  <link media="all" rel='stylesheet' href="{{ asset('/css/group_home/pdf_demo/pdf_all_yoshiki_6-x.css') }}">
</head>
<body>
  <div class="seikyuu">
    <img src="{{ asset('/chouhyou/seikyuu.jpg')}}">

    {{-- 対象年月 --}}
    <div class="yaers_months">
      <span class="yaers">{{ $data['japanese_imperial_year']['year'] }}</span>
      <span class="months">{{ $data['japanese_imperial_year']['month'] }}</span>
    </div>

    {{-- 印刷を実施した日 --}}
    <div class="print_date">
      <span class="print_yaer">{{ $data['system_timestamp']['year'] }}</span>
      <span class="print_month">{{ $data['system_timestamp']['month'] }}</span>
      <span class="print_day">{{ $data['system_timestamp']['day'] }}</span>
    </div>

    {{-- 事業所情報 --}}
    <div class="facility">
      <div class="facility_number">{{ $data['facility']['facility_number'] }}</div>
      <div class="facility_name_kanji">{{ $data['facility']['facility_name_kanji'] }}</div>
      <div class="postal_code">{{ $data['facility']['postal_code'] }}</div>
      <div class="location">{{ $data['facility']['location'] }}</div>
      <div class="phone_number">{{ $data['facility']['phone_number'] }}</div>
    </div>

    {{-- 保険請求 --}}
    <div class="insurance_billing">
      {{-- サービス費用 --}}
      {{-- 件数 --}}
      <span>{{ $data['insurance_billing']['cnt'] }}</span>
      {{-- 単位数・点数 --}}
      <span>{{ $data['insurance_billing']['service_unit_amount'] }}</span>
      {{-- 費用合計 --}}
      <span>{{ $data['insurance_billing']['total_cost'] }}</span>
      {{-- 保険請求額 --}}
      <span>{{ $data['insurance_billing']['insurance_benefit'] }}</span>
      {{-- 公費請求額 --}}
      <span>{{ $data['insurance_billing']['public_spending_amount'] }}</span>
      {{-- 利用者負担 --}}
      <span>{{ $data['insurance_billing']['part_payment'] }}</span>

      {{-- 特定入居者介護サービス費等 --}}
      {{-- 件数 --}}
      <span>{{ $data['insurance_billing']['particular_cnt'] }}</span>
      {{-- 費用合計 --}}
      <span>{{ $data['insurance_billing']['particular_total_cost'] }}</span>
      {{-- 利用者負担 --}}
      <span>{{ $data['insurance_billing']['particular_part_payment'] }}</span>
      {{-- 公費請求額 --}}
      <span>{{ $data['insurance_billing']['particular_public_spending_amount'] }}</span>
      {{-- 保険請求額 --}}
      <span>{{ $data['insurance_billing']['particular_insurance_benefit'] }}</span>
    </div>

    {{-- 保険請求合計 --}}
    <div class="insurance_billing_total">
      {{-- サービス費用 --}}
      {{-- 件数 --}}
      <span>{{ $data['insurance_billing']['cnt'] }}</span>
      {{-- 単位数・点数 --}}
      <span>{{ $data['insurance_billing']['service_unit_amount'] }}</span>
      {{-- 費用合計 --}}
      <span>{{ $data['insurance_billing']['total_cost'] }}</span>
      {{-- 保険請求額 --}}
      <span>{{ $data['insurance_billing']['insurance_benefit'] }}</span>
      {{-- 公費請求額 --}}
      <span>{{ $data['insurance_billing']['public_spending_amount'] }}</span>
      {{-- 利用者負担 --}}
      <span>{{ $data['insurance_billing']['part_payment'] }}</span>

      {{-- 特定入居者介護サービス費等 --}}
      {{-- 件数 --}}
      <span>{{ $data['insurance_billing']['particular_cnt'] }}</span>
      {{-- 費用合計 --}}
      <span>{{ $data['insurance_billing']['particular_total_cost'] }}</span>
      {{-- 利用者負担 --}}
      <span>{{ $data['insurance_billing']['particular_part_payment'] }}</span>
      {{-- 公費請求額 --}}
      <span>{{ $data['insurance_billing']['particular_public_spending_amount'] }}</span>
      {{-- 保険請求額 --}}
      <span>{{ $data['insurance_billing']['particular_insurance_benefit'] }}</span>
    </div>

    {{-- 公費請求 --}}
    {{-- 区分１２ --}}
    <div class="public_spending">
      {{-- サービス費用 --}}
      {{-- 件数 --}}
      <span class="public_cnt">{{ $data['public_spending']['public_spending_cnt_12'] }}</span>
      {{-- 単位数・点数 --}}
      <span class="public_unit_amount">{{ $data['public_spending']['public_expenditure_unit_12'] }}</span>
      {{-- 費用合計 --}}
      <span class="public_total_cost">{{ $data['public_spending']['public_payment_12'] }}</span>
      {{-- 公費請求額 --}}
      <span class="public_spending_amount">{{ $data['public_spending']['public_spending_amount_12'] }}</span>

      {{-- 特定入居者介護サービス費等 --}}
      {{-- 件数 --}}
      <span class="particular_cnt">{{ $data['public_spending']['public_particular_cnt_12'] }}</span>
      {{-- 費用合計 --}}
      <span class="particular_total_cost">{{ $data['public_spending']['public_particular_total_cost_12'] }}</span>
      {{-- 公費請求額 --}}
      <span class="particular_spending_amount">{{ $data['public_spending']['public_particular_spending_amount_12'] }}</span>
    </div>

    {{-- 区分８１ --}}
    <div class="public_spending class_81">
      {{-- サービス費用 --}}
      {{-- 件数 --}}
      <span class="public_cnt">{{ $data['public_spending']['public_spending_cnt_81'] }}</span>
      {{-- 単位数・点数 --}}
      <span class="public_unit_amount">{{ $data['public_spending']['public_expenditure_unit_81'] }}</span>
      {{-- 費用合計 --}}
      <span class="public_total_cost">{{ $data['public_spending']['public_payment_81'] }}</span>
      {{-- 公費請求額 --}}
      <span class="public_spending_amount">{{ $data['public_spending']['public_spending_amount_81'] }}</span>

      {{-- 特定入居者介護サービス費等 --}}
      {{-- 件数 --}}
      <span class="particular_cnt">{{ $data['public_spending']['public_particular_cnt_81'] }}</span>
      {{-- 費用合計 --}}
      <span class="particular_total_cost">{{ $data['public_spending']['public_particular_total_cost_81'] }}</span>
      {{-- 公費請求額 --}}
      <span class="particular_spending_amount">{{ $data['public_spending']['public_particular_spending_amount_81'] }}</span>
    </div>

    {{-- 区分２５ --}}
    <div class="public_spending class_25">
      {{-- サービス費用 --}}
      {{-- 件数 --}}
      <span class="public_cnt">{{ $data['public_spending']['public_spending_cnt_25'] }}</span>
      {{-- 単位数・点数 --}}
      <span class="public_unit_amount">{{ $data['public_spending']['public_expenditure_unit_25'] }}</span>
      {{-- 費用合計 --}}
      <span class="public_total_cost">{{ $data['public_spending']['public_payment_25'] }}</span>
      {{-- 公費請求額 --}}
      <span class="public_spending_amount">{{ $data['public_spending']['public_spending_amount_25'] }}</span>

      {{-- 特定入居者介護サービス費等 --}}
      {{-- 件数 --}}
      <span class="particular_cnt">{{ $data['public_spending']['public_particular_cnt_25'] }}</span>
      {{-- 費用合計 --}}
      <span class="particular_total_cost">{{ $data['public_spending']['public_particular_total_cost_25'] }}</span>
      {{-- 公費請求額 --}}
      <span class="particular_spending_amount">{{ $data['public_spending']['public_particular_spending_amount_25'] }}</span>
    </div>

    {{-- 公費請求合計 --}}
    <div class="public_spending_total">
      {{-- サービス費用 --}}
      {{-- 公費請求額 --}}
      <span class="public_spending_amount_total">{{ $data['public_spending']['public_spending_amount_total'] }}</span>

      {{-- 特定入居者介護サービス費等 --}}
      {{-- 公費請求額 --}}
      <span class="particular_public_spending_amount_total">{{ $data['public_spending']['public_particular_spending_amount_total'] }}</span>
    </div>
  </div>

  @for ($s = 0; $s < count($data['facility_user']); $s++)
    @for ($j = 0; $j < count($data['benefit_status'][$s]); $j++)
      {{-- 国保連請求未承認の利用者はスキップ --}}
      @if ($data['benefit_status'][$s][$j][0]['service_item_name'] == "")
          @continue;
      @endif
        @if (
                $data['benefit_status'][$s][$j][0]['service_type_code'] == '33'
                ||
                $data['benefit_status'][$s][$j][0]['service_type_code'] == '35'
                ||
                $data['benefit_status'][$s][$j][0]['service_type_code'] == '36'
              )
          @include('group_home.pdf_demo.pdf_all_yoshiki_6-x',
              [
                  'user_public_expense_information'=>$data['user_public_expense_information'][$s],
                  "japanese_imperial_year" => $data['japanese_imperial_year'],
                  "facility_user" => $data['facility_user'][$s],
                  "user_care_information"=> $data['user_care_information'][$s],
                  "facility" => $data['facility'],
                  "check_benefit_status" => $data['benefit_status'][$s],
                  "benefit_status" => $data['benefit_status'][$s][$j],
                  "billing_total"=> $data['billing_total'][$s],
                  "stayInOutDays"=> $data['stayInOutDays'][$s],
                  'all_sheet' => count($data['benefit_status'][$s]),
                  'sheet_num' => $j + 1
              ]
            )
            @continue
        @else
        @endif
        <div class="meisai">
          @if ($data['benefit_status'][$s][$j][0]['service_type_code'] == '32')
            <img src="{{ asset('/chouhyou/meisai.jpg')}}">
          @elseif ($data['benefit_status'][$s][$j][0]['service_type_code'] == '37')
            <img src="{{ asset('/chouhyou/meisai_37.jpg')}}">
          @endif
        {{-- 利用者毎の公費情報 --}}
        <div>
          {{-- 公費負担者番号 --}}
          <div class="bearer_number">{{ $data['user_public_expense_information'][$s]['bearer_number'] }}</div>
          {{-- 公費受給者番号 --}}
          <div class="recipient_number">{{ $data['user_public_expense_information'][$s]['recipient_number'] }}</div>
        </div>

        {{-- 提出年月 --}}
        <div class="yaers_and_months">
          <span class="yaers">{{ $data['japanese_imperial_year']['year'] }}</span>
          <span class="months">{{ $data['japanese_imperial_year']['month'] }}</span>
        </div>

        {{-- 保険者番号 --}}
        <div class="insurer_no">{{ $data['facility_user'][$s]['insurer_no'] }}</div>

        {{-- 被保険者 --}}
        <div class="facility_info">
          {{-- 被保険者番号 --}}
          <span class="insured_no">{{ $data['facility_user'][$s]['insured_no'] }}</span>
          {{-- フリガナ --}}
          <div class="name_kana">
            <span>{{ $data['facility_user'][$s]['last_name_kana'] }}</span>
            <span>{{ $data['facility_user'][$s]['first_name_kana'] }}</span>
          </div>
          {{-- 氏名 --}}
          <div class="name">
            <span>{{ $data['facility_user'][$s]['last_name'] }}</span>
            <span>{{ $data['facility_user'][$s]['first_name'] }}</span>
          </div>
          {{-- 生年月日 --}}
          {{-- 元号 --}}
          <div class="japanese_calender">
            @if($data['facility_user'][$s]['birthday']['name'] == '明治')
              <span class="meiji">〇</span>
            @elseif($data['facility_user'][$s]['birthday']['name'] == '大正')
              <span class="taisho">〇</span>
            @else
              <span class="showa">〇</span>
            @endif
          </div>
          {{-- 年月日 --}}
          <div class="date_of_birth">
            <span class="year">{{ $data['facility_user'][$s]['birthday']['year'] }}</span>
            <span class="month">{{ $data['facility_user'][$s]['birthday']['month'] }}</span>
            <span class="day">{{ $data['facility_user'][$s]['birthday']['day'] }}</span>
          </div>
          {{-- 性別 --}}
          <div class="gender">
            @if($data['facility_user'][$s]['gender'] == '1')
              <span class="man">〇</span>
            @else
              <span class="woman">〇</span>
            @endif
          </div>
          {{-- 要介護状態区分 --}}
          <div class="care_level">
            @if($data['user_care_information'][$s]['care_level_id'] == '6')
              {{-- 要介護1 --}}
              <span class="one">〇</span>
            @elseif($data['user_care_information'][$s]['care_level_id'] == '7')
              {{-- 要介護2 --}}
              <span class="two">〇</span>
            @elseif($data['user_care_information'][$s]['care_level_id'] == '8')
              {{-- 要介護3 --}}
              <span class="three">〇</span>
            @elseif($data['user_care_information'][$s]['care_level_id'] == '9')
              {{-- 要介護4 --}}
              <span class="four">〇</span>
            @elseif($data['user_care_information'][$s]['care_level_id'] == '10')
              {{-- 要介護5 --}}
              <span class="five">〇</span>
            @elseif($data['user_care_information'][$s]['care_level_id'] == '5')
              {{-- サービスコード37 要支援2 --}}
              <span class="support_two">〇</span>
            @else
            @endif
          </div>
          {{--  認定有効開始--}}
          <div class="period_start_calender">
            @if($data['user_care_information'][$s]['care_period_start']['name'] == '平成')
              <div class="heisei">〇</div>
            @elseif($data['user_care_information'][$s]['care_period_start']['name'] == '令和')
              <div class="reiwa">〇</div>
            @endif
          </div>
          <div class="period_start_date">
            <span class="year">{{ $data['user_care_information'][$s]['care_period_start']['year'] }}</span>
            <span class="month">{{ $data['user_care_information'][$s]['care_period_start']['month'] }}</span>
            <span class="day">{{ $data['user_care_information'][$s]['care_period_start']['day'] }}</span>
          </div>
          {{-- 認定有効期間終了日 --}}
          <div class="period_end_date">
            <span class="year">{{ $data['user_care_information'][$s]['care_period_end']['year'] }}</span>
            <span class="month">{{ $data['user_care_information'][$s]['care_period_end']['month'] }}</span>
            <span class="day">{{ $data['user_care_information'][$s]['care_period_end']['day'] }}</span>
          </div>
        </div>{{-- 被保険者end --}}

        {{-- 請求事業者 --}}
        <div class="facilities">
          {{-- 事業者番号 --}}
          <div class="facility_number">{{ $data['facility']['facility_number'] }}</div>
          {{-- 事業者名称 --}}
          <div class="facility_name_kanji">{{ $data['facility']['facility_name_kanji'] }}</div>
          {{-- 郵便番号--}}
          <div class="postal_code">{{ $data['facility']['postal_code'] }}</div>
          {{-- 住所 --}}
          <div class="location">{{ $data['facility']['location'] }}</div>
          {{-- 電話番号 --}}
          <div class="phone_number">{{ $data['facility']['phone_number'] }}</div>
        </div>{{-- 請求事業者end --}}

        {{-- 入居年月日等 --}}
        <div>
          {{-- 入居年月日元号 --}}
          <div class="start_calender">
            @if($data['facility_user'][$s]['start_date']['name'] == '平成')
              <div class="heisei">〇</div>
            @else
              <div class="reiwa">〇</div>
            @endif
          </div>
          <div class="in_out_info">
            {{-- 入居年月日 --}}
            <span class="start_date_year">{{ $data['facility_user'][$s]['start_date']['year'] }}</span>
            <span class="start_date_month">{{ $data['facility_user'][$s]['start_date']['month'] }}</span>
            <span class="start_date_day">{{ $data['facility_user'][$s]['start_date']['day'] }}</span>
            {{-- 退去年月日 --}}
            <span class="end_date_year">{{ $data['facility_user'][$s]['end_date']['year'] }}</span>
            <span class="end_date_month">{{ $data['facility_user'][$s]['end_date']['month'] }}</span>
            <span class="end_date_day">{{ $data['facility_user'][$s]['end_date']['day'] }}</span>
            {{-- 入居実日数 --}}
            <span class="actual_number">{{ $data['stayInOutDays'][$s]['actualDays'] }}</span>
            {{-- 外泊日数 --}}
            <span class="stay_out_overnight">{{ $data['stayInOutDays'][$s]['stayOut'] }}</span>
          </div>
        </div>{{-- 入居年月日等end --}}

        {{-- 入居前の状況 --}}
        <div class="before_in_status_id">
          @if($data['facility_user'][$s]['before_in_status_id'] == '1')
            {{-- 居宅 --}}
            <span class="home">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '2')
            {{-- 医療機関 --}}
            <span class="medical_institution">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '3')
            {{-- 介護老人福祉施設 --}}
            <span class="welfare_facility">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '4')
            {{-- 介護老人保健施設 --}}
            <span class="health_facility">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '5')
            {{-- 介護療養型医療施設 --}}
            <span class="medical_facility">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '6')
            {{-- 認知症対応型共同生活介護 --}}
            <span class="dementia">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '7')
            {{-- 特定施設入居者生活介護 --}}
            <span class="specific_facility">〇</span>
          @elseif($data['facility_user'][$s]['before_in_status_id'] == '8')
            {{-- その他 --}}
            <span class="others">〇</span>
          @else
            {{-- 介護医療院 --}}
            <span class="medical_clinic">〇</span>
          @endif
        </div>{{-- 入居前の状況end --}}

        {{-- 退居後の状況 --}}
        <div class="after_out_status_id">
          @if($data['facility_user'][$s]['after_out_status_id'] == '1')
            {{-- 居宅 --}}
            <span class="home">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '2')
            {{-- 医療機関入院 --}}
            <span class="medical_institution">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '3')
            {{-- 死亡 --}}
            <span class="death">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '4')
            {{-- その他 --}}
            <span class="others">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '5')
            {{-- 介護老人福祉施設入所 --}}
            <span class="welfare_facility">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '6')
            {{-- 介護老人保健施設入所 --}}
            <span class="health_facility">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '7')
            {{-- 介護療養型医療施設入院 --}}
            <span class="medical_facility">〇</span>
          @elseif($data['facility_user'][$s]['after_out_status_id'] == '8')
            {{-- 介護医療院入所 --}}
            <span class="medical_clinic">〇</span>
          @endif
        </div>{{-- 退去後の状況end --}}

        {{-- 給付費明細欄 --}}
        <div class="benefit_status_area">
          @for ($i = 0; $i < count($data['benefit_status'][$s][$j]); $i++)
            <div class="benefit_status">
              {{-- サービス内容 --}}
              <span class="service_item_name">{{ $data['benefit_status'][$s][$j][$i]['service_item_name'] }}</span>
              {{-- サービスコード --}}
              <span class="service_code">{{ $data['benefit_status'][$s][$j][$i]['service_code'] }}</span>
              {{-- 単位数 --}}
              <span class="unit_number">{{ $data['benefit_status'][$s][$j][$i]['unit_number'] }}</span>
              {{-- 回数日数 --}}
              <span class="service_count_date">{{ $data['benefit_status'][$s][$j][$i]['service_count_date'] }}</span>
              {{-- サービス単位数 --}}
              <span class="service_unit_amount">{{ $data['benefit_status'][$s][$j][$i]['service_unit_amount'] }}</span>
              {{-- 公費分回数等 --}}
              <span class="public_spending_count">{{ $data['benefit_status'][$s][$j][$i]['public_spending_count'] }}</span>
              {{-- 公費対象単位数 --}}
              <span class="public_expenditure_unit">{{ $data['benefit_status'][$s][$j][$i]['public_expenditure_unit'] }}</span>
              {{-- 摘要 --}}
              @if( isset($data['benefit_status'][$s][$j][$i]['service_code_data']['service_type_code']) )
              <span id="death_date">{{ $data['benefit_status'][$s][$j][$i]['facility_user']['death_date'] }}</span>
              @endif
            </div>
          @endfor
          @if(reset($data['benefit_status'][$s]) == $data['benefit_status'][$s][$j])
            {{-- 合計 --}}
            <div class="total">
              {{-- サービス単位数の合計 --}}
              <span class="service_unit_amount">{{ $data['billing_total'][$s]['service_unit_amount'] }}</span>
              {{-- 公費対象単位数の合計 --}}
              <span class="public_spending_unit">{{ $data['billing_total'][$s]['public_expenditure_unit'] }}</span>
            </div>
          @endif
        </div>{{-- 給付費明細欄end --}}

        @if(reset($data['benefit_status'][$s]) == $data['benefit_status'][$s][$j])
          {{-- 請求額集計欄 --}}
          <div>
            {{-- 単位数合計 --}}
            <div class="total_number">
              {{-- 保険分 --}}
              <span class="service_unit_amount">{{ $data['billing_total'][$s]['service_unit_amount'] }}</span>
              {{-- 公費分 --}}
              <span class="public_spending_unit_number">{{ $data['billing_total'][$s]['public_expenditure_unit'] }}</span>
            </div>
            {{-- 単位数単価 --}}
            <div class="unit_price">{{ $data['billing_total'][$s]['unit_price'] }}</div>
            {{-- 給付率 --}}
            <div class="rate">
              {{-- 保険分 --}}
              <span class="benefit_rate">{{ $data['billing_total'][$s]['benefit_rate'] }}</span>
              {{-- 公費分 --}}
              <span class="public_benefit_rate">{{ $data['billing_total'][$s]['public_benefit_rate'] }}</span>
            </div>
            {{-- 請求額（円） --}}
            <div class="billing_amount">
              {{-- 保険分 --}}
              <span class="insurance_benefit">{{ $data['billing_total'][$s]['insurance_benefit'] }}</span>
              {{-- 公費分 --}}
              <span class="public_spending_amount">{{ $data['billing_total'][$s]['public_spending_amount'] }}</span>
            </div>
            {{-- 利用者負担額 --}}
            <div class="user_burden">
              {{-- 保険分 --}}
              <span class="part_payment">{{ $data['billing_total'][$s]['part_payment'] }}</span>
              {{-- 公費分 --}}
              <span class="public_payment">{{ $data['billing_total'][$s]['public_payment'] }}</span>
            </div>
          </div>{{-- 請求額集計欄end --}}
        @endif
        <div class="sheet_count">
          <span>{{ count($data['benefit_status'][$s]) }}</span>
          <span>{{ $j + 1 }}</span>
        </div>
      </div>
    @endfor
  @endfor
</body>
</html>
