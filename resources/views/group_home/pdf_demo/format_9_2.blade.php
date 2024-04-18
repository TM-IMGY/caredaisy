<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ $data['year_month'] }}_介護給付費明細書_{{ $data['facility_user']['last_name'] }}{{ $data['facility_user']['first_name'] }}</title>
  <link media="all" rel='stylesheet' href="{{ mix('/css/group_home/pdf_demo/pdf_demo.css') }}">
  <link media="all" rel='stylesheet' href="{{ mix('/css/group_home/pdf_demo/format_9_2.css') }}">
</head>
<body>
  {{-- TODO: 給付費明細書は数が大量に出力することが想定されているので、windowsの印刷ドライバを使わないようにする。 --}}
  <script>
    // window.print();
    // window.onafterprint = (event) => {
    //   window.close();
    // };
  </script>

  @for ($sheetIndex = 0, $cnt = count($data['benefit_status']); $sheetIndex < $cnt; $sheetIndex++)
    <div class="meisai">
      {{-- 様式9の2の画像 --}}
      <img alt="様式9の2" src="{{ asset('/chouhyou/format_9_2.jpg')}}">

      {{-- 施設利用者の公費情報 --}}
      <div>
        {{-- 公費負担者番号 --}}
        <div class="bearer_number">{{ $data['user_public_expense_information']['bearer_number'] }}</div>
        {{-- 公費受給者番号 --}}
        <div class="recipient_number">{{ $data['user_public_expense_information']['recipient_number'] }}</div>
      </div>

      {{-- 年月 --}}
      <div>
        <span class="yaers">{{ $data['japanese_imperial_year']['year'] }}</span>
        <span class="months">{{ $data['japanese_imperial_year']['month'] }}</span>
      </div>

      {{-- 保険者番号 --}}
      <div class="insurer_no">{{ $data['facility_user']['insurer_no'] }}</div>

      {{-- 被保険者 --}}
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
        <div>
          {{-- 明治 --}}
          @if($data['facility_user']['birthday']['name'] == '明治')
            <span class="date_of_birth date_of_birth_meiji">〇</span>
          {{-- 大正 --}}
          @elseif($data['facility_user']['birthday']['name'] == '大正')
            <span class="date_of_birth date_of_birth_taisho">〇</span>
          {{-- 昭和 --}}
          @else
            <span class="date_of_birth date_of_birth_showa">〇</span>
          @endif
          {{-- 年月日 --}}
          <span class="date_of_birth_ymd">
            <span class="date_of_birth_year">{{ $data['facility_user']['birthday']['year'] }}</span>
            <span class="date_of_birth_month">{{ $data['facility_user']['birthday']['month'] }}</span>
            <span class="date_of_birth_day">{{ $data['facility_user']['birthday']['day'] }}</span>
          </span>
        </div>
        {{-- 性別 --}}
        {{-- 男 --}}
        @if($data['facility_user']['gender'] == '1')
          <span class="gender man">〇</span>
        {{-- 女 --}}
        @else
          <span class="gender woman">〇</span>
        @endif
      </div>

      <div>
        {{-- 要介護状態区分 --}}
        <div>
          {{-- 要介護1 --}}
          @if($data['user_care_information']['care_level_id'] == '6')
            <span class="care_level care_level_21">〇</span>
          {{-- 要介護2 --}}
          @elseif($data['user_care_information']['care_level_id'] == '7')
            <span class="care_level care_level_22">〇</span>
          {{-- 要介護3 --}}
          @elseif($data['user_care_information']['care_level_id'] == '8')
            <span class="care_level care_level_23">〇</span>
          {{-- 要介護4 --}}
          @elseif($data['user_care_information']['care_level_id'] == '9')
            <span class="care_level care_level_24">〇</span>
          {{-- 要介護5 --}}
          @elseif($data['user_care_information']['care_level_id'] == '10')
            <span class="care_level care_level_25">〇</span>
          @endif
        </div>
        {{-- 認定有効期間(開始) --}}
        {{-- 和暦 --}}
        <span>
          {{-- 平成 --}}
          @if($data['user_care_information']['care_period_start']['name'] == '平成')
            <div class="care_perido care_period_heisei">〇</div>
          {{-- 令和 --}}
          @elseif($data['user_care_information']['care_period_start']['name'] == '令和')
            <div class="care_perido care_period_reiwa">〇</div>
          @endif
        </span>
        {{-- 年月日 --}}
        <span>
          <span class="care_period_start care_period_start_year">{{ $data['user_care_information']['care_period_start']['year'] }}</span>
          <span class="care_period_start care_period_start_month">{{ $data['user_care_information']['care_period_start']['month'] }}</span>
          <span class="care_period_start care_period_start_day">{{ $data['user_care_information']['care_period_start']['day'] }}</span>
        </span>
        {{-- 認定有効期間(終了) --}}
        {{-- 年月日 --}}
        <div>
          <span class="care_period_end care_period_end_year">{{ $data['user_care_information']['care_period_end']['year'] }}</span>
          <span class="care_period_end care_period_end_month">{{ $data['user_care_information']['care_period_end']['month'] }}</span>
          <span class="care_period_end care_period_end_day">{{ $data['user_care_information']['care_period_end']['day'] }}</span>
        </div>
      </div>

      {{-- 請求事業者 --}}
      <div>
        {{-- 事業所番号 --}}
        <div class="facility_number">{{ $data['facility']['facility_number'] }}</div>
        {{-- 事業所名称 --}}
        <div class="facility_name_kanji">{{ $data['facility']['facility_name_kanji'] }}</div>
        {{-- 所在地 --}}
        {{-- 郵便番号--}}
        <div class="postal_code">{{ $data['facility']['postal_code'] }}</div>
        {{-- 住所 --}}
        <div class="location">{{ $data['facility']['location'] }}</div>
        {{-- 連絡先 --}}
        {{-- 電話番号 --}}
        <div class="phone_number">{{ $data['facility']['phone_number'] }}</div>
      </div>

      {{-- 施設利用者情報 --}}
      <div>
        {{-- 入所年月日 --}}
        {{-- 元号 --}}
        <span>
          {{-- 平成 --}}
          @if($data['facility_user']['start_date']['name'] == '平成')
            <div class="facility_user_start_date facility_user_start_date_heisei">〇</div>
          {{-- 令和 --}}
          @else
            <div class="facility_user_start_date facility_user_start_date_reiwa">〇</div>
          @endif
        </span>
        {{-- 年月日 --}}
        <span>
          <span class="facility_user_start facility_user_start_year">{{ $data['facility_user']['start_date']['year'] }}</span>
          <span class="facility_user_start facility_user_start_month">{{ $data['facility_user']['start_date']['month'] }}</span>
          <span class="facility_user_start facility_user_start_day">{{ $data['facility_user']['start_date']['day'] }}</span>
        </span>
        {{-- 退所年月日 --}}
        <span>
          <span class="facility_user_end facility_user_end_year">{{ $data['facility_user']['end_date']['year'] }}</span>
          <span class="facility_user_end facility_user_end_month">{{ $data['facility_user']['end_date']['month'] }}</span>
          <span class="facility_user_end facility_user_end_day">{{ $data['facility_user']['end_date']['day'] }}</span>
        </span>
        {{-- 入所実日数 --}}
        <span class="actual_number">{{ $data['stayInOutDays']['actualDays'] }}</span>
        {{-- 外泊日数 --}}
        <span class="stay_out_overnight">{{ $data['stayInOutDays']['stayOut'] }}</span>
        {{-- 入所前の状況 --}}
        <div>
          <span class="before_in_status before_in_status_{{$data['facility_user']['before_in_status_id']}}">〇</span>
        </div>
        {{-- 退所後の状況 --}}
        @if($data['facility_user']['after_out_status_id'])
        <div>
          <span class="after_out_status_{{ $data['facility_user']['after_out_status_id'] }}">〇</span>
        </div>
        @endif
      </div>

      {{-- 給付費明細欄 --}}
      <div class="benefit_details">
        @for ($i = 0; $i < count($data['benefit_status'][$sheetIndex]); $i++)
          <div class="benefit_detail">
            {{-- サービス内容 --}}
            <span class="service_item_name">{{ $data['benefit_status'][$sheetIndex][$i]['service_item_name'] }}</span>
            {{-- サービスコード --}}
            <span class="service_code">{{ $data['benefit_status'][$sheetIndex][$i]['service_code'] }}</span>
            {{-- 単位数 --}}
            <span class="unit_number">{{ $data['benefit_status'][$sheetIndex][$i]['unit_number'] }}</span>
            {{-- 回数日数 --}}
            <span class="service_count_date">{{ $data['benefit_status'][$sheetIndex][$i]['service_count_date'] }}</span>
            {{-- サービス単位数 --}}
            <span class="service_unit_amount">{{ $data['benefit_status'][$sheetIndex][$i]['service_unit_amount'] }}</span>
            {{-- 公費分回数等 --}}
            <span class="public_spending_count">{{ $data['benefit_status_public'][$sheetIndex][$i]['public_spending_count'] }}</span>
            {{-- 公費対象単位数 --}}
            <span class="public_spending_unit_number">{{ $data['benefit_status_public'][$sheetIndex][$i]['public_spending_unit_number'] }}</span>
            {{-- 摘要 --}}
            @if( isset( $data['benefit_status'][$sheetIndex][$i]['service_code_data']['service_type_code']))
              <span id="death_date">{{ $data['benefit_status'][$sheetIndex][$i]['facility_user']['death_date'] }}</span>
            @endif
          </div>
        @endfor
        {{-- 合計 --}}
        {{-- 複数枚に渡す場合、合計値は1枚目にのみ出力する。 --}}
        @if($sheetIndex == 0)
          <div>
            {{-- サービス単位数 --}}
            <span class="service_unit_amount_sum">{{ $data['benefit_billing']['service_unit_amount'] }}</span>
            {{-- 公費対象単位数 --}}
            <span class="public_spending_unit">{{ $data['public_spending']['public_expenditure_unit'] }}</span>
          </div>
        @endif
      </div>

      {{-- 請求額集計欄 --}}
      {{-- 複数枚に渡す場合、合計値は1枚目にのみ出力する。 --}}
      @if($sheetIndex == 0)
        <div>
          {{-- 単位数合計 --}}
          <div>
            {{-- 保険分 --}}
            <span class="benefit_unit">{{ $data['benefit_billing']['benefit_unit'] }}</span>
            {{-- 公費分 --}}
            <span class="public_benefit_unit">{{ $data['public_spending']['public_benefit_unit'] }}</span>
          </div>
          {{-- 単位数単価 --}}
          <div class="unit_price">{{ $data['benefit_billing']['unit_price'] }}</div>
          {{-- 給付率 --}}
          <div>
            {{-- 保険分 --}}
            <span class="benefit_rate">{{ $data['benefit_billing']['benefit_rate'] }}</span>
            {{-- 公費分 --}}
            <span class="public_benefit_rate">{{ $data['public_spending']['public_benefit_rate'] }}</span>
          </div>
          {{-- 請求額（円） --}}
          <div>
            {{-- 保険分 --}}
            <span class="insurance_benefit">{{ $data['benefit_billing']['insurance_benefit'] }}</span>
            {{-- 公費分 --}}
            <span class="public_spending_amount">{{ $data['public_spending']['public_spending_amount'] }}</span>
          </div>
          {{-- 利用者負担額 --}}
          <div>
            {{-- 保険分 --}}
            <span class="part_payment">{{ $data['benefit_billing']['part_payment'] }}</span>
            {{-- 公費分 --}}
            <span class="public_payment">{{ $data['public_spending']['public_payment'] }}</span>
          </div>
        </div>
      @endif

      {{-- n 枚中 n枚目 --}}
      <div class="sheet_count">
        <span>{{ count($data['benefit_status']) }}</span>
        <span>{{ $sheetIndex + 1 }}</span>
      </div>
    </div>
  @endfor
</body>
</html>
