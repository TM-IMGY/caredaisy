<div class="tm_contents_hidden" id="tm_contents_2">
  <div class="nh_header">
    <div id="nh_label">
      <div dusk="national-health-form-label">
        国保連請求
      </div>
      <div id="nh_label_ym">
        <div>【</div>
        <div>対象月: </div>
        <div id="nh_label_year"></div>
        <div>年</div>
        <div id="nh_label_month"></div>
        <div>月</div>
        <div>】</div>
      </div>
    </div>

    {{-- 承認ボタンエリア --}}
    <div class="nh_agreement_area">
      @can('approveRequest')
      {{-- 承認ボタン --}}
      <button class="nh_agreement_btn" id="nh_agreement_ok_btn" dusk="national-helath-agreement-ok">承認</button>
      {{-- 承認解除ボタン --}}
      <button class="nh_agreement_btn" id="nh_agreement_cancel_btn">承認解除</button>
      @endcan
    </div>
  </div>

  <div class="userinfo_table_area">
    <div class="userinfo_table">
      <table>
        <tr>
          <td><span>被保険者氏名</span><span>：</span></td>
          <td id="nh_table_info_name"></td>
        </tr>
        <tr>
          {{-- 被保険者氏名（カナ） --}}
          <td></td>
          <td id="nh_table_info_name_kana"></td>
        </tr>
        <tr>
          <td><span>生年月日</span><span>：</span></td>
          <td id="nh_table_info_birthday"></td>
        </tr>
        <tr>
          <td><span>性別</span><span>：</span></td>
          <td id="nh_table_info_gender"></td>
        </tr>
        <tr>
          <td><span>保険者番号</span><span>：</span></td>
          <td id="nh_table_insurer_no"></td>
        </tr>
        <tr>
          <td><span>被保険者番号</span><span>：</span></td>
          <td id="nh_table_insured_no"></td>
        </tr>
      </table>
    </div>

    <div class="userinfo_table">
      <table>
        <tr>
          <td><span>入居日</span><span>：</span></td>
          <td id="nh_table_start_date"></td>
        </tr>
        <tr>
          <td><span>入居実日数</span><span>：</span></td>
          <td id="nh_table_actual_days"></td>
        </tr>
        <tr>
          <td><span>外泊日数</span><span>：</span></td>
          <td id="nh_table_stay_out"></td>
        </tr>
      </table>
    </div>

    <div class="userinfo_table">
      <table>

        <tr>
          <td>
            <span>認定情報</span>
            <span>：</span>
          </td>
          <td id="nh_table_care_level"></td>
        </tr>

        <tr>
          <td>
            <span>認定有効期間</span>
            <span>：</span>
          </td>
          <td id="nh_table_care_period_start">
          </td>
        </tr>

        <tr>
          <td>
            <span></span>
            <span></span>
          </td>
          <td id="nh_table_care_period_end">
          </td>
        </tr>

      </table>
    </div>
  </div>

  <div class="national_health_table_list">

    {{-- 給付費明細欄 --}}
    <div class="nh_benefit_status_lbl">給付費明細欄</div>
    <table class="caredaisy_table nh_benefit_status">
      <thead>
        <tr>
          <th class="nh_table_cell service_item_name">サービス内容</th>
          <th class="nh_table_cell service_code">サービスコード</th>
          <th class="nh_table_cell unit_number">単位数</th>
          <th class="nh_table_cell service_count_date">回数日数</th>
          <th class="nh_table_cell service_unit_amount">サービス単位数</th>
          <th class="nh_table_cell public_expenditure_cnt">公費分回数等</th>
          <th class="nh_table_cell public_expenditure_amount">公費対象<br/>単位数</th>
        </tr>
      </thead>
      <tbody class="caredaisy_table_tbody" id="national_health_tbody"></tbody>
    </table>

    {{-- 特別診療費 --}}
    <div class="special_medical_element_hidden">
      <div class="nh_benefit_status_lbl" dusk="special_medical_label">特別診療費</div>
      <table class="caredaisy_table nh_benefit_status">
        <thead>
          <tr>
            <th class="nh_table_cell sp_injury_and_illness_name">傷病名</th>
            <th class="nh_table_cell sp_identification_num">識別番号</th>
            <th class="nh_table_cell sp_special_medical_name">内容</th>
            <th class="nh_table_cell sp_unit_number">単位数</th>
            <th class="nh_table_cell sp_number_of_days">回数日数</th>
            <th class="nh_table_cell sp_service_unit_amount">サービス単位数</th>
            <th class="nh_table_cell sp_public_expenditure_cnt">公費分回数等</th>
            <th class="nh_table_cell sp_public_expenditure_amount">公費対象<br/>単位数</th>
          </tr>
        </thead>
        <tbody class="caredaisy_table_tbody" id="special_medical_tbody"></tbody>
      </table>
    </div>

    {{-- 給付額請求欄 --}}
    {{-- 特別診療費合計も含まれているので今後分割等を検討する --}}
    <div class="nh_benefit_billing_lbl" id="nh_benefit_billing_lbl">給付額請求欄</div>
    <table class="caredaisy_table nh_benefit_billing">
      <thead>
        <tr>
          <th class="nh_table_cell">区分</th>
          <th class="nh_table_cell" colspan="2">保険分</th>
          <th class="nh_table_cell" colspan="2">公費分</th>
          <th class="nh_table_cell special_medical_table_element_hidden" colspan="2" dusk="nh_benefit_billing_sp_medical_expenses_label">保険分特定治療・特別診療費</th>
          <th class="nh_table_cell special_medical_table_element_hidden" colspan="2" dusk="nh_benefit_billing_sp_public_medical_expenses_label">公費分特定治療・特別診療費</th>
        </tr>
      </thead>
      <tbody id="nh_claim_for_benefits_tbody">
        <tr>
          <td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_total_credits_label">①単位数合計</td>
          <td class="nh_table_cell nh_billing_td number_cell" colspan="2" id="nh_billing_unit_amount" dusk="national-health-billing-unit-amount"></td>
          <td class="nh_table_cell nh_billing_td number_cell" colspan="2" id="nh_billing_public_unit_amount" dusk="national-health-billing-unit-amount-public"></td>
          <td class="nh_table_cell nh_billing_td number_cell special_medical_table_element_hidden" colspan="2" id="nh_billing_sp_service_unit_amount" dusk="national-health-billing-sp-service-unit-amount"></td>
          <td class="nh_table_cell nh_billing_td number_cell special_medical_table_element_hidden" colspan="2" id="nh_billing_public_sp_spending_unit_number" dusk="national-health-billing-unit-sp-spending-unit-number"></td>
        </tr>
        <tr>
          <td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_unit_credits_label">②単位数単価</td>
          <td class="nh_table_cell nh_billing_td number_cell" id="nh_billing_unit_price" dusk="national-health-billing-unit-price"></td>
          <td class="nh_table_cell nh_billing_td">円/単位</td>
          <td class="nh_table_cell nh_billing_td invalid_cell" colspan="2"></td>
          <td class="nh_table_cell nh_billing_td special_medical_table_element_hidden" colspan="2">10円/点・単位</td>
          <td class="nh_table_cell nh_billing_td special_medical_table_element_hidden" colspan="2">10円/点・単位</td>
        </tr>
        <tr>
          <td class="nh_table_cell nh_billing_td">③給付率</td>
          <td class="nh_table_cell nh_billing_td number_cell" id="nh_billing_benefit_rate" dusk="national-helath-billing-benefit-rate"></td>
          <td class="nh_table_cell nh_billing_td">/100</td>
          <td class="nh_table_cell nh_billing_td number_cell" id="nh_billing_public_benefit_rate" dusk="national-helath-billing-benefit-rate-public"></td>
          <td class="nh_table_cell nh_billing_td">/100</td>
          <td class="nh_table_cell nh_billing_td number_cell special_medical_table_element_hidden" id="nh_billing_sp_benefit_rate" dusk="national-helath-billing-sp-benefit-rate"></td>
          <td class="nh_table_cell nh_billing_td special_medical_table_element_hidden">/100</td>
          <td class="nh_table_cell nh_billing_td number_cell special_medical_table_element_hidden" id="nh_billing_sp_public_benefit_rate" dusk="national-helath-billing-sp-benefit-rate-public"></td>
          <td class="nh_table_cell nh_billing_td special_medical_table_element_hidden">/100</td>
        </tr>
        <tr>
          <td class="nh_table_cell nh_billing_td">④請求額(円)</td>
          <td class="nh_table_cell nh_billing_td number_cell" colspan="2" id="nh_billing_insurance_benefit" dusk="national-health-billing-insurance-benefit"></td>
          <td class="nh_table_cell nh_billing_td number_cell" colspan="2" id="nh_billing_public_insurance_benefit" dusk="national-health-billing-insurance-benefit-public"></td>
          <td class="nh_table_cell nh_billing_td number_cell special_medical_table_element_hidden" colspan="2" id="nh_billing_sp_insurance_benefit" dusk="national-health-billing-sp-insurance-benefit"></td>
          <td class="nh_table_cell nh_billing_td number_cell special_medical_table_element_hidden" colspan="2" id="nh_billing_sp_public_spending_amount" dusk="national-health-billing-sp-public-spending-amount"></td>
        </tr>
        <tr>
          <td class="nh_table_cell">⑤利用者負担額(円)</td>
          <td class="nh_table_cell number_cell" colspan="2" id="nh_billing_part_payment" dusk="national-health-billing-part-payment"></td>
          <td class="nh_table_cell number_cell" colspan="2" id="nh_billing_public_part_payment" dusk="national-health-billing-part-payment-public"></td>
          <td class="nh_table_cell number_cell special_medical_table_element_hidden" colspan="2" id="nh_billing_sp_part_payment" dusk="national-health-billing-sp-part-payment"></td>
          <td class="nh_table_cell number_cell special_medical_table_element_hidden" colspan="2" id="nh_billing_sp_public_payment" dusk="national-health-billing-sp-public-payment"></td>
        </tr>
      </tbody>
    </table>

    {{-- 特定入所者介護サービス費 --}}
    <div class="special_medical_element_hidden">
      <div class="nh_benefit_status_lbl" dusk="incompetent_resident_label">特定入所者介護サービス費</div>
      <table class="caredaisy_table nh_benefit_status">
        <thead>
          <tr>
            <th class="nh_table_cell srs_service_item_name">サービス内容</th>
            <th class="nh_table_cell srs_service_item_code">サービスコード</th>
            <th class="nh_table_cell srs_unit_number">費用単価(円)</th>
            <th class="nh_table_cell srs_burden_limit">負担限度額</th>
            <th class="nh_table_cell srs_service_count_date">日数</th>
            <th class="nh_table_cell srs_total_cost">費用額(円)</th>
            <th class="nh_table_cell srs_insurance_benefit">保険分</th>
            <th class="nh_table_cell srs_public_spending_count">公費日数</th>
            <th class="nh_table_cell srs_public_spending_amount">公費分</th>
            <th class="nh_table_cell srs_part_payment">利用者負担額</th>
          </tr>
        </thead>
        <tbody class="caredaisy_table_tbody" id="incompetent_resident_tbody"></tbody>
      </table>
    </div>

  </div>
</div>
