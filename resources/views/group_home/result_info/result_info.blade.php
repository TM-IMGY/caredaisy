@extends('layouts.application')

@section('title','実績情報')

@section('style')
  <link rel='stylesheet' href="{{ mix('/css/group_home/result_info/result_info.css') }}">
  <link rel='stylesheet' href="{{ mix('/css/calendar.css') }}">
@endsection

@section('contents')
  <div>
    {{-- 請求メニュー --}}
    <div id="facility_user_menu">
      <div id="fum_pulldown_list">
        {{-- 事業所プルダウン --}}
        <select class="fum_pulldown" id="facility_pulldown"></select>

        <div class="ym_pulldown_and_re_billing_btn">
          {{-- 対象年月プルダウン --}}
          <select class="fum_pulldown" id="year_month_pulldown" dusk="year-month-pulldown">
            {{-- 2021/04まで遡る --}}
            @php
                $todayUtc = \Carbon\Carbon::today('UTC')->startOfDay();
                $todayJp = \Carbon\Carbon::today()->startOfDay();
                if ($todayJp->diffInDays($todayUtc) >= 1) {
                  $todayUtc->addDay();
                }
                $const = new App\Lib\Common\Consts();
                $monthDiff = $todayUtc->diffInMonths($const->MIN_YEAR_MONTH);
            @endphp
            @for($i=0; $i<=$monthDiff; $i++)
              <option value="{{ date('Y/m',strtotime(date('Y/m/1')." -${i} month")) }}">
                {{ date('Y/m',strtotime(date('Y/m/1')." -${i} month")) }}
              </option>
            @endfor
          </select>

          {{-- 再請求ボタン --}}
          <button class="submit_btn" id="rb_btn" dusk="rb-btn"></button>
        </div>

        {{-- データを出力プルダウン --}}
        <div id="side_area">
          <div class="side_wrap">
            <div class="dropdown_list export">
              <ul>
                <li class="has-child">
                  <a href="#" dusk="show-output-dropdown-list">データを出力</a>
                  <ul>
                    <li id="dep_billing" style="display: none;">
                      <a class="csv_icon" href="#">国保連請求データを出力する</a>
                    </li>
                    <li id="dep_make_invoice" style="display: none;">
                      <a class="export_icon" href="#">伝送用国保連請求データを作成する</a>
                    </li>
                    <li id="dep_pdf_demo_all">
                      <a class="pdf_icon" href="#">介護給付費請求書と介護給付費明細書を出力する</a>
                    </li>
                    <li id="dep_pdf_demo_facility">
                      <a class="pdf_icon" href="#">介護給付費請求書を出力する</a>
                    </li>
                    <li class="has-child pdf_icon">
                      <a href="#">介護給付費明細書を出力する</a>
                      <ul>
                        <!--<li>
                          <a class="all_user_icon" href="#">利用者全員分を出力する</a>
                        </li>-->
                        <li id="dep_care_benefit_statement">
                          <a class="select_user_icon" href="#">選択中の利用者を出力する</a>
                        </li>
                      </ul>
                    </li>
                    <li class="has-child pdf_icon">
                      <a href="#" dusk="show-usage-fee-modal-buttton">利用料請求書を出力する</a>
                      <ul>
                        <li dusk="li_issue_date">
                          <a id="dep_usage_fee_invoice" class="all_user_icon" href="#" dusk="dep_usage_fee_invoice">利用者全員分を出力する</a>
                        </li>
                        <li>
                          <a id="dep_usage_fee_invoice_individual" class="select_user_icon" href="#" dusk="dep_usage_fee_invoice_individual">選択中の利用者を出力する</a>
                        </li>
                      </ul>
                    </li>
                    <li class="has-child pdf_icon">
                      <a href="#" dusk="show-usage-receipt-modal-buttton">利用料領収書を出力する</a>
                      <ul>
                        <li>
                          <a id="dep_usage_fee_receipt" class="all_user_icon" href="#" dusk="dep_usage_fee_receipt">利用者全員分を出力する</a>
                        </li>
                        <li>
                          <a id="dep_usage_fee_receipt_individual" class="select_user_icon" href="#" dusk="dep_usage_fee_receipt_individual">選択中の利用者を出力する</a>
                        </li>
                      </ul>
                    </li>
                    <li id="dep_usage_fee_invoice_list" class="has-child list_icon">
                      <a href="#" dusk="show-usage-fee-list-modal-buttton">利用料請求書一覧を出力する</a>
                      <ul>
                        <li>
                          <a id="dep_usage_fee_invoice_list_pdf" class="pdf_icon" href="#" dusk="dep_usage_fee_invoice_list_pdf">PDFで出力する</a>
                        </li>
                        <li>
                          <a id="dep_usage_fee_invoice_list_csv" class="csv_icon" href="#" dusk="dep_usage_fee_invoice_list_csv">CSVで出力する</a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
              {{-- 介護給付費請求書と利用者全員分の介護給付費明細書を出力する --}}
              <a href="{{route('group_home.pdf_demo_all')}}" id="pdf_demo_all_form" method="get" rel="noopener noreferrer" target="_blank">
                @csrf
              </a>
              {{-- 介護給付費請求書を出力する --}}
              <a href="{{route('group_home.pdf_demo_facility')}}" id="pdf_demo_facility_form" method="get" rel="noopener noreferrer" target="_blank">
                @csrf
              </a>
              {{-- 選択中の利用者の介護給付費明細書を出力する --}}
              <a href="{{route('group_home.pdf_demo')}}" id="dep_pdf_demo_form" method="get" rel="noopener noreferrer" target="_blank">
                @csrf
              </a>
              {{-- 利用者全員分の利用料請求書と利用料領収書を出力する --}}
              <form action="{{route('own_non_insurance_bill_ledger_sheets')}}" id="usage_fee_all_form" method="get" rel="noopener noreferrer" target="_blank">
                @csrf
              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- 施設利用者テーブル --}}
      <table class="caredaisy_table" id="facility_user_table">
        <tbody id="facility_user_tbody"></tbody>
      </table>

      {{-- 施設利用者テーブルプラスボタン --}}
      <div id="fu_table_plus_btn">+</div>
    </div>

    <div id="service_result_contents">
      {{-- カテゴリータブ(サブ) --}}
      <div id="tm_sub_tab">
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_5" dusk="stay-out-button" id="tm_tabs_contents_5">外泊日登録</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_1" dusk="service-result-button" id="tm_tabs_contents_1">実績登録</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_2" dusk="national-health-button" id="tm_tabs_contents_2">国保連請求</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_3" dusk="facility-user-billing-button" id="tm_tabs_contents_3">保険外請求</a>
        {{-- <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_4">入金</a> --}}
      </div>

      <div id="tm_contents">
        {{-- サービス実績テーブル --}}
        @component('components.group_home.result_info.service_result_table')@endcomponent
        {{-- 国保連請求 --}}
        @component('components.group_home.result_info.national_health')@endcomponent
        {{-- 利用者請求 --}}
        @component('components.group_home.result_info.facility_user_billing')@endcomponent
        {{-- 入金 --}}
        {{-- @component('components.group_home.result_info.payment')@endcomponent --}}
        {{-- 外泊日登録 --}}
        @component('components.group_home.result_info.stay_out')@endcomponent
      </div>
    </div>
  </div>

  {{-- 実績情報画面カバー --}}
  <div id="result_registration_cover" class="result_registration_hidden"></div>

  {{-- サービスコードフォーム(サービス) --}}
  <div class="result_registration_hidden" id="service_code_form_basic">
    <div class="service_code_form_label">サービスコード選択</div>
    <div class="service_code_form_pulldowns">
      <div>
        <div class="service_code_form_row">事業所</div>
        <select class="service_code_form_row service_code_form_pulldown" id="service_code_form_basic_facility"></select>
      </div>
      <div>
        <div class="service_code_form_row">種別</div>
        <select class="service_code_form_row service_code_form_pulldown" id="service_code_form_basic_service_type"></select>
      </div>
    </div>
    {{-- サービスコードテーブル --}}
    <table class="service_code_form_row caredaisy_table">
      <thead class="caredaisy_table_thead">
        <tr>
          <th class="service_code_form_table_header service_code_form_basic_code">サービスコード</th>
          <th class="service_code_form_table_header service_code_form_basic_name">サービス内容</th>
        </tr>
      </thead>
      <tbody id="service_code_form_basic_tbody" class="caredaisy_table_tbody"></tbody>
    </table>
    {{-- サブミットボタン --}}
    <div class="service_code_form_submit_btns">
      <button class="submit_btn service_code_form_register" id="service_code_form_register_basic">登録</button>
      <button class="caredaisy_cancel_btn service_code_form_cancel" id="service_code_form_cancel_basic">キャンセル</button>
    </div>
  </div>

  {{-- 特別診療コードフォーム --}}
  <div class="result_registration_hidden" id="service_code_form_special">
    <div class="service_code_form_label">特別診療コード選択</div>
    <div class="service_code_form_pulldowns">
      <div>
        <div class="service_code_form_row">事業所</div>
        <select class="service_code_form_row service_code_form_pulldown" id="service_code_form_special_facility"></select>
      </div>
    </div>
    {{-- 特別診療コードテーブル --}}
    <table class="service_code_form_row caredaisy_table">
      <thead class="caredaisy_table_thead">
        <tr>
          <th class="service_code_form_table_header service_code_form_basic_code">特別診療識別番号</th>
          <th class="service_code_form_table_header service_code_form_basic_name">特別診療費の内容</th>
        </tr>
      </thead>
      <tbody id="service_code_form_special_tbody" class="caredaisy_table_tbody"></tbody>
    </table>
    {{-- サブミットボタン --}}
    <div class="service_code_form_submit_btns">
      <button class="submit_btn service_code_form_register" id="service_code_form_register_special">登録</button>
      <button class="caredaisy_cancel_btn service_code_form_cancel" id="service_code_form_cancel_special">キャンセル</button>
    </div>
  </div>

  {{-- 特定入所者サービスコードフォーム --}}
  <div class="result_registration_hidden" id="service_code_form_incompetent_resident">
    <div class="service_code_form_label">特別診療コード選択</div>
    <div class="service_code_form_pulldowns">
      <div>
        <div class="service_code_form_row">事業所</div>
        <select class="service_code_form_row service_code_form_pulldown" id="scf_ir_facility"></select>
      </div>
    </div>
    {{-- 特定入所者サービスコードテーブル --}}
    <table class="service_code_form_row caredaisy_table">
      <thead class="caredaisy_table_thead">
        <tr>
          <th class="service_code_form_table_header scf_ir_code">サービスコード</th>
          <th class="service_code_form_table_header scf_ir_name">サービス内容</th>
        </tr>
      </thead>
      <tbody id="scf_ir_tbody" class="caredaisy_table_tbody"></tbody>
    </table>
    {{-- 負担者限度額 --}}
    <div class="service_code_form_row">
      <div>限度額（単位：円）</div>
      <input id="scf_ir_payer_limit" max="9999" min="0" type="number" value="0">
    </div>
    {{-- サブミットボタン --}}
    <div class="service_code_form_submit_btns">
      <button class="submit_btn service_code_form_register" id="scf_register_incompetent_resident">登録</button>
      <button class="caredaisy_cancel_btn service_code_form_cancel" id="scf_cancel_incompetent_resident">キャンセル</button>
    </div>
  </div>

  {{-- 施設利用者テーブルの施設利用者選択ポップアップ --}}
  <div id="fu_table_select_user_popup_grayout" class="fu_table_hidden"></div>
  <div id="fu_table_select_user_popup" class="fu_table_hidden">
    <div class="fu_table_sup_row">
      <div id="fu_table_sup_label">利用者選択</div>
    </div>
    {{-- 検索 --}}
    <div class="fu_table_sup_row">
      <input id="fu_table_sup_search_box" placeholder="カナ検索">
      <div class="submit_btn" id="fu_table_sup_search_btn">検索</div>
    </div>
    {{-- 一括選択と一括クリア --}}
    <div class="fu_table_sup_row">
      <button class="fu_table_sup_select_btn" id="fu_table_sup_all_select_btn">一括選択</button>
      <button class="fu_table_sup_select_btn" id="fu_table_sup_all_cancel_btn">一括キャンセル</button>
    </div>
    {{-- 利用者一覧 --}}
    <div class="fu_table_sup_row">
      <div id="fu_table_sup_tbody"></div>
    </div>
    {{-- 確定ボタン、キャンセルボタン --}}
    <div class="fu_table_sup_row">
      <button class="submit_btn" id="fu_table_sup_ok_btn">確定</button>
      <button class="caredaisy_cancel_btn" id="fu_table_sup_cancel_btn">キャンセル</button>
    </div>
  </div>

  {{-- 保険外請求:保険外品目追加モーダル --}}
  {{-- 品目追加テーブルカバー --}}
  <div id="un_table_cover" class="un_table_hidden"></div>
  {{-- 品目追加フォーム --}}
  <div id="un_table_s_item_form" class="un_table_hidden" dusk="un-table-form">
    <div id="item_form_lbl">品目追加</div>
    {{-- ワーニングメッセージエリア --}}
    <div>
      <ul class="warning" id="validateErrorsUninsuredItem">
      </ul>
    </div>
    <div class="item_pulldown_container">
      {{-- タブ入力 --}}
      <div class="item_titles">
        <input class="modal_radio_input" type="radio" id="select_list" name="modal_radio" value="list" dusk="select-list-radio" checked="checked">
        <label>品目リストから選ぶ<span class="required unclear_required">*必須</span></label>
      </div>
        <select class="item_form_row item_form_pulldown" id="un_table_s_item_pulldown" dusk="select-item">
      </select>
    </div>

    <div class="item_sub_container">
      {{-- 保険外品目入力 --}}
      <label>
        <div class="item_titles">
          <input class="modal_radio_input" type="radio" id="select_add" name="modal_radio" value="add_item" dusk="select-add-radio">
          <label>品目を追加する</label>
          <div><small>※選択すると入力項目が表示されます</small></div>
        </div>
        <div id="add_item" dusk="add-item">
          <div class="item_form_row item_titles">品目名<span class="required">*必須</span></div>
            <input type="text" class="item_input" id="item_sub_name" placeholder="品目名">
          <div class="item_form_row item_titles">単価</div>
            <input type="number" min="0" pattern=="^[1-9][0-9]*$" class="item_input" id="item_sub_unit" placeholder="単価" >
          <div class="item_form_row item_titles">日付</div>
            <div class="modal_calendar_area">
              <p class="modalJaCalbox"><span id="jaCalPubExpenditureDate"></span><span>年</span></p>
              <input type="tel" id="item_sub_date" class="modalJaCalinput datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
          </div>
        </div>
      </label>
    </div>
    <div class="item_form_btns">
      <button class="item_form_btn" id="item_form_register">登録</button>
      <button class="item_form_btn" id="item_form_cancel">キャンセル</button>
    </div>
  </div>

  {{-- 利用料請求書/領収書出力モーダル --}}
  <div id="dep_table_cover" class="dep_table_hidden"></div>
  <div id="dep_table_s_item_form" class="dep_table_hidden">
    {{-- モーダル --}}
    <div id="dep_form_lbl"></div>
    <div class="dep_container">
      <div class="dep_form_title">発行日</div>
      <div class="modal_calendar_area">
          <p class="modalJaCalbox"><span id="jaCalIssueDate"></span><span>年</span></p>
          <input type="text" id="issue_date" class="modalJaCalinput" maxlength="12" placeholder="yyyy/mm/dd" autocomplete="off">
      </div>
    </div>
    {{-- ワーニングメッセージエリア --}}
    <div>
      <ul class="warning" id="validateErrorsDep">
      </ul>
    </div>
    <div class="dep_form_btns">
      <button class="item_form_btn" id="dep_form_submit">確定</button>
      <button class="item_form_btn" id="dep_form_cancel">キャンセル</button>
    </div>
  </div>
  {{-- エラーメッセージモーダル --}}
  <div id="dep_table_error" class="dep_table_hidden">
    {{-- モーダル --}}
    <div id="dep_form_lbl"></div>
    <div class="dep_container">
      <div class="dep_form_label"><span id="error_message"><span></div>
    </div>
    <div class="dep_form_btn">
      <button class="item_form_btn" id="dep_error_close">OK</button>
    </div>
  </div>
@endsection

@section('script')
  <script type='module' src="{{ mix('/js/group_home/result_info/result_info.js') }}" defer></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/datepicker-ja.js"></script>
@endsection
