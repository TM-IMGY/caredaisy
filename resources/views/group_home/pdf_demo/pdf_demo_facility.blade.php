{{-- author eikeda --}}
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ $data['year_month'] }}_介護給付費請求書</title>
  {{-- <link rel='stylesheet' href="{{ asset('/css/group_home/pdf_demo/pdf_demo_facility.css') }}"> --}}
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
</body>
</html>
