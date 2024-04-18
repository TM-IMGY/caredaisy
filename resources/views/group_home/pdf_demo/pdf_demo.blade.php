{{-- author yhamada --}}
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ $data['year_month'] }}_介護給付費明細書_{{ $data['facility_user']['last_name'] }}{{ $data['facility_user']['first_name'] }}</title>
  <link media="all" rel='stylesheet' href="{{ asset('/css/group_home/pdf_demo/pdf_all_yoshiki_6-x.css') }}">
</head>
<body>
  @for ($s = 0; $s < count($data['benefit_status']); $s++)
    <div class="meisai">
      @if ($data['service_type_code'] == '32')
        <img src="{{ asset('/chouhyou/meisai.jpg')}}">
      @elseif ($data['service_type_code'] == '37')
        <img src="{{ asset('/chouhyou/meisai_37.jpg')}}">
      @else
      @endif

      {{-- 利用者毎の公費情報 --}}
      <div>
        {{-- 公費負担者番号 --}}
        <div class="bearer_number">{{ $data['user_public_expense_information']['bearer_number'] }}</div>
        {{-- 公費受給者番号 --}}
        <div class="recipient_number">{{ $data['user_public_expense_information']['recipient_number'] }}</div>
      </div>

      {{-- 提出年月 --}}
      <div>
        <span class="yaers">{{ $data['japanese_imperial_year']['year'] }}</span>
        <span class="months">{{ $data['japanese_imperial_year']['month'] }}</span>
      </div>

      {{-- 保険者番号 --}}
      <div class="insurer_no">{{ $data['facility_user']['insurer_no'] }}</div>

      {{-- 施設利用者情報 --}}
      <div>
        {{-- 被保険者番号 --}}
        <span class="insured_no">{{ $data['facility_user']['insured_no'] }}</span>
        {{-- フリガナ --}}
        <div class="name_kana">
          {{-- フリガナ姓 --}}
          <span>{{ $data['facility_user']['last_name_kana'] }}</span>
          {{-- フリガナ名 --}}
          <span>{{ $data['facility_user']['first_name_kana'] }}</span>
        </div>
        {{-- 氏名 --}}
        <div class="name">
          {{-- 姓 --}}
          <span>{{ $data['facility_user']['last_name'] }}</span>
          {{-- 名 --}}
          <span>{{ $data['facility_user']['first_name'] }}</span>
        </div>
        {{-- 生年月日 --}}
        {{-- 和暦 --}}
        <div class="japanese_calender">
          @if($data['facility_user']['birthday']['name'] == '明治')
            {{-- 明治 --}}
            <span class="meiji">〇</span>
          @elseif($data['facility_user']['birthday']['name'] == '大正')
            {{-- 大正 --}}
            <span class="taisho">〇</span>
          @else
            {{-- 昭和 --}}
            <span class="showa">〇</span>
          @endif
        </div>
        {{-- 年月日 --}}
        <span class="date_of_birth">
          <span class="year">{{ $data['facility_user']['birthday']['year'] }}</span>
          <span class="month">{{ $data['facility_user']['birthday']['month'] }}</span>
          <span class="day">{{ $data['facility_user']['birthday']['day'] }}</span>
        </span>
        {{-- 性別 --}}
        @if($data['facility_user']['gender'] == '1')
          {{-- 男 --}}
          <span class="man">〇</span>
        @else
          {{-- 女 --}}
          <span class="woman">〇</span>
        @endif
      </div>

      {{-- 利用者毎の介護認定情報 --}}
      <div>
        {{-- 要介護状態区分 --}}
        <div class="care_level">
          @if($data['user_care_information']['care_level_id'] == '6')
            {{-- 要介護1 --}}
            <span class="one">〇</span>
          @elseif($data['user_care_information']['care_level_id'] == '7')
            {{-- 要介護2 --}}
            <span class="two">〇</span>
          @elseif($data['user_care_information']['care_level_id'] == '8')
            {{-- 要介護3 --}}
            <span class="three">〇</span>
          @elseif($data['user_care_information']['care_level_id'] == '9')
            {{-- 要介護4 --}}
            <span class="four">〇</span>
          @elseif($data['user_care_information']['care_level_id'] == '10')
            {{-- 要介護5 --}}
            <span class="five">〇</span>
          @elseif($data['service_type_code'] == '37')
            {{-- サービスコード37 要支援2 --}}
            <span class="support_two">〇</span>
          @else
          @endif
        </div>
        {{--  認定有効期間--}}
        {{-- 認定有効期間開始日 --}}
        {{-- 和暦 --}}
        <span class="period_start_calender">
          @if($data['user_care_information']['care_period_start']['name'] == '平成')
            {{-- 平成 --}}
            <div class="heisei">〇</div>
          @elseif($data['user_care_information']['care_period_start']['name'] == '令和')
            {{-- 令和 --}}
            <div class="reiwa">〇</div>
          @endif
        </span>
        {{-- 年月日 --}}
        <span class="period_start_date">
          <span class="year">{{ $data['user_care_information']['care_period_start']['year'] }}</span>
          <span class="month">{{ $data['user_care_information']['care_period_start']['month'] }}</span>
          <span class="day">{{ $data['user_care_information']['care_period_start']['day'] }}</span>
        </span>
        {{-- 認定有効期間終了日 --}}
        {{-- 年月日 --}}
        <div class="period_end_date">
          <span class="year">{{ $data['user_care_information']['care_period_end']['year'] }}</span>
          <span class="month">{{ $data['user_care_information']['care_period_end']['month'] }}</span>
          <span class="day">{{ $data['user_care_information']['care_period_end']['day'] }}</span>
        </div>
      </div>

      {{-- 請求事業者 --}}
      <div>
        {{-- 事業者番号 --}}
        <div class="facility_number">{{ $data['facility']['facility_number'] }}</div>
        {{-- 事業者名称 --}}
        <div class="facility_name_kanji">{{ $data['facility']['facility_name_kanji'] }}</div>
        {{-- 所在地 --}}
        {{-- 郵便番号--}}
        <div class="postal_code">{{ $data['facility']['postal_code'] }}</div>
        {{-- 住所 --}}
        <div class="location">{{ $data['facility']['location'] }}</div>
        {{-- 電話番号 --}}
        <div class="phone_number">{{ $data['facility']['phone_number'] }}</div>
      </div>

      {{-- 施設利用者情報 --}}
      <div>
        {{-- 入退去情報 --}}
        {{-- 入居年月日 --}}
        <span class="start_calender">
          @if($data['facility_user']['start_date']['name'] == '平成')
            {{-- 平成 --}}
            <div class="heisei">〇</div>
          @else
            {{-- 令和 --}}
            <div class="reiwa">〇</div>
          @endif
        </span>
        <span class="start_date">
          <span class="year">{{ $data['facility_user']['start_date']['year'] }}</span>
          <span class="month">{{ $data['facility_user']['start_date']['month'] }}</span>
          <span class="day">{{ $data['facility_user']['start_date']['day'] }}</span>
        </span>
        {{-- 退去年月日 --}}
        <span class="end_date">
          <span class="year">{{ $data['facility_user']['end_date']['year'] }}</span>
          <span class="month">{{ $data['facility_user']['end_date']['month'] }}</span>
          <span class="day">{{ $data['facility_user']['end_date']['day'] }}</span>
        </span>
        {{-- 入居実日数 --}}
        <span class="actual_number">{{ $data['stayInOutDays']['actualDays'] }}</span>
        {{-- 外泊日数 --}}
        <span class="stay_out_overnight">{{ $data['stayInOutDays']['stayOut'] }}</span>
        {{-- 入居前の状況 --}}
        <div class="before_in_status_id">
          @if($data['facility_user']['before_in_status_id'] == '1')
            {{-- 居宅 --}}
            <span class="home">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '2')
            {{-- 医療機関 --}}
            <span class="medical_institution">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '3')
            {{-- 介護老人福祉施設 --}}
            <span class="welfare_facility">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '4')
            {{-- 介護老人保健施設 --}}
            <span class="health_facility">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '5')
            {{-- 介護療養型医療施設 --}}
            <span class="medical_facility">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '6')
            {{-- 認知症対応型共同生活介護 --}}
            <span class="dementia">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '7')
            {{-- 特定施設入居者生活介護 --}}
            <span class="specific_facility">〇</span>
          @elseif($data['facility_user']['before_in_status_id'] == '8')
            {{-- その他 --}}
            <span class="others">〇</span>
          @else
            {{-- 介護医療院 --}}
            <span class="medical_clinic">〇</span>
          @endif
        </div>
        {{-- 退去後の状況 --}}
        <div class="after_out_status_id">
          @if($data['facility_user']['after_out_status_id'] == '1')
            {{-- 居宅 --}}
            <span class="home">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '2')
            {{-- 医療機関入院 --}}
            <span class="medical_institution">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '3')
            {{-- 死亡 --}}
            <span class="death">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '4')
            {{-- その他 --}}
            <span class="others">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '5')
            {{-- 介護老人福祉施設入所 --}}
            <span class="welfare_facility">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '6')
            {{-- 介護老人保健施設入所 --}}
            <span class="health_facility">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '7')
            {{-- 介護療養型医療施設入院 --}}
            <span class="medical_facility">〇</span>
          @elseif($data['facility_user']['after_out_status_id'] == '8')
            {{-- 介護医療院入所 --}}
            <span class="medical_clinic">〇</span>
          @endif
        </div>
      </div>


      {{-- 給付費明細欄 --}}
      <div class="benefit_status_area">
        @for ($i = 0; $i < count($data['benefit_status'][$s]); $i++)
        <div class="benefit_status">
          {{-- サービス内容 --}}
          <span class="service_item_name">{{ $data['benefit_status'][$s][$i]['service_item_name'] }}</span>
          {{-- サービスコード --}}
          <span class="service_code">{{ $data['benefit_status'][$s][$i]['service_code'] }}</span>
          {{-- 単位数 --}}
          <span class="unit_number">{{ $data['benefit_status'][$s][$i]['unit_number'] }}</span>
          {{-- 回数日数 --}}
          <span class="service_count_date">{{ $data['benefit_status'][$s][$i]['service_count_date'] }}</span>
          {{-- サービス単位数 --}}
          <span class="service_unit_amount">{{ $data['benefit_status'][$s][$i]['service_unit_amount'] }}</span>
          {{-- 公費分回数等 --}}
          <span class="public_spending_count">{{ $data['benefit_status_public'][$s][$i]['public_spending_count'] }}</span>
          {{-- 公費対象単位数 --}}
          <span class="public_expenditure_unit">{{ $data['benefit_status_public'][$s][$i]['public_expenditure_unit'] }}</span>
          {{-- 摘要 --}}
          @if( isset($data['benefit_status'][$s][$i]['service_code_data']['service_type_code']) )
          <span id="death_date">{{ $data['benefit_status'][$s][$i]['facility_user']['death_date'] }}</span>
          @endif
        </div>
        @endfor
        @if(reset($data['benefit_status']) == $data['benefit_status'][$s])
          {{-- 合計 --}}
          <div class="total">
            {{-- サービス単位数の合計 --}}
            <span class="service_unit_amount">{{ $data['benefit_billing']['service_unit_amount'] }}</span>
            {{-- 公費対象単位数の合計 --}}
            <span class="public_spending_unit">{{ $data['public_spending']['public_expenditure_unit'] }}</span>
          </div>
        @endif
      </div>

      @if(reset($data['benefit_status']) == $data['benefit_status'][$s])
        {{-- 請求額集計欄 --}}
        <div>
          <div class="total_number">
            {{-- 保険分 --}}
            <span class="service_unit_amount">{{ $data['benefit_billing']['service_unit_amount'] }}</span>
            {{-- 公費分 --}}
            <span class="public_spending_unit_number">{{ $data['public_spending']['public_expenditure_unit'] }}</span>
          </div>
          {{-- 単位数単位 --}}
          <div class="unit_price">{{ $data['benefit_billing']['unit_price'] }}</div>
          {{-- 給付率 --}}
          <div class="rate">
            {{-- 保険分 --}}
            <span class="benefit_rate">{{ $data['benefit_billing']['benefit_rate'] }}</span>
            {{-- 公費分 --}}
            <span class="public_benefit_rate">{{ $data['public_spending']['public_benefit_rate'] }}</span>
          </div>
          {{-- 請求額（円） --}}
          <div class="billing_amount">
            {{-- 保険分 --}}
            <span class="insurance_benefit">{{ $data['benefit_billing']['insurance_benefit'] }}</span>
            {{-- 公費分 --}}
            <span class="public_spending_amount">{{ $data['public_spending']['public_spending_amount'] }}</span>
          </div>
          {{-- 利用者負担額 --}}
          <div class="user_burden">
            {{-- 保険分 --}}
            <span class="part_payment">{{ $data['benefit_billing']['part_payment'] }}</span>
            {{-- 公費分 --}}
            <span class="public_payment">{{ $data['public_spending']['public_payment'] }}</span>
          </div>
        </div>{{-- 請求額集計欄end --}}
      @endif
      <div class="sheet_count">
        <span>{{ count($data['benefit_status']) }}</span>
        <span>{{ $s + 1 }}</span>
      </div>
    </div>
  @endfor
  {{-- yhamada --}}

</body>
</html>
