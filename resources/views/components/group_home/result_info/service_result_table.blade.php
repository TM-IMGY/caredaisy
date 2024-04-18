{{-- 実績登録タブの中身 --}}
<div class="tm_contents_hidden" id="tm_contents_1">
  <div class="result_registration_header">
    <div id="result_registration_label">
      <div dusk="result-registration-label">実績登録</div>
      <div id="result_registration_ym">
        <div>【</div>
        <div>対象月: </div>
        <div id="result_registration_year"></div>
        <div>年</div>
        <div id="result_registration_month"></div>
        <div>月</div>
        <div>】</div>
      </div>
    </div>

    <div id="result_registration_submit_btns">
      {{-- 再集計ボタン(自動サービスコード機能を呼ぶ) --}}
      <button id="re_count_button" dusk="recount-button" title="サービスコードを取得します。">再集計</button>
      {{-- 保存ボタン --}}
      @can('writeRequest')
        <button class="submit_btn" id="result_registration_save_btn" dusk="result-registration-save-btn" title="サービス実績を保存します。">保存</button>
      @endcan
    </div>
  </div>

  @component('components.facility_user_info_header')@endcomponent

  {{-- タブ --}}
  <div id="result_registration_tabs">
    {{-- サービス --}}
    <div class="result_registration_hidden" data-rr-contents-id="result_registration_basic_contents" id="result_registration_basic">サービス</div>
    {{-- 特別診療 --}}
    <div class="result_registration_hidden" data-rr-contents-id="result_registration_special_contents" id="result_registration_special">特別診療</div>
    {{-- 特定入所者サービス --}}
    <div class="result_registration_hidden" data-rr-contents-id="rr_incompetent_resident_contents" id="rr_incompetent_resident">特定入所者サービス</div>
  </div>

  {{-- サービスタブのコンテンツ --}}
  <div id="result_registration_basic_contents">
    {{-- 実績登録テーブル(サービスタブ) --}}
    <table class="caredaisy_table" id="result_registration_table">
      <thead class="caredaisy_table_thead fixed_thead">
        <tr>
          <th class="result_registration_table_cell result_registration_table_trash" rowspan="3"></th>
          <th class="result_registration_table_cell result_registration_table_service" rowspan="3">サービス内容</th>
          <th class="result_registration_table_cell result_registration_table_unit" rowspan="3">単位数</th>
          <th class="result_registration_table_cell" colspan="2" id="result_registration_table_ddr">月間サービス計画及び実績の記録</th>
        </tr>
        <tr>
          <th id="result_registration_table_date" class="result_registration_table_cell result_registration_table_plan">日付</th>
          <th class="result_registration_table_cell result_registration_table_sum" rowspan="2">合計</th>
        </tr>
        <tr>
          <th id="result_registration_table_dow" class="result_registration_table_cell result_registration_table_result">曜日</th>
        </tr>
      </thead>
      <tbody id="result_registration_tbody" class="caredaisy_table_tbody"></tbody>
    </table>

    {{-- プラスボタン(サービスコードフォームを呼び出す) --}}
    <button id="result_registration_table_plus" title="Add service code.">＋</button>
  </div>

  {{-- 特別診療タブのコンテンツ --}}
  {{-- サービスタブとは実績登録テーブルについて共通することも多いが考え方が異なるので分けた方が良い。 --}}
  <div class="result_registration_hidden" id="result_registration_special_contents">
    {{-- 実績登録テーブル(特殊タブ) --}}
    <table class="caredaisy_table" id="result_registration_table_special">
      <thead class="caredaisy_table_thead fixed_thead">
        <tr>
          <th class="result_registration_table_cell result_registration_table_trash" rowspan="3"></th>
          <th class="result_registration_table_cell result_registration_table_service" rowspan="3">サービス内容</th>
          <th class="result_registration_table_cell result_registration_table_unit" rowspan="3">単位数</th>
          <th class="result_registration_table_cell" colspan="2" id="result_registration_table_ddr_special">月間サービス計画及び実績の記録</th>
        </tr>
        <tr>
          <th id="result_registration_table_date_special" class="result_registration_table_cell result_registration_table_plan">日付</th>
          <th class="result_registration_table_cell result_registration_table_sum" rowspan="2">合計</th>
        </tr>
        <tr>
          <th id="result_registration_table_dow_special" class="result_registration_table_cell result_registration_table_result">曜日</th>
        </tr>
      </thead>
      <tbody id="result_registration_tbody_special" class="caredaisy_table_tbody"></tbody>
    </table>

    {{-- プラスボタン(特別診療コードフォームを呼び出す) --}}
    <button id="rrt_plus_special" title="Add service code.">＋</button>
  </div>

  {{-- 特定入所者サービスタブのコンテンツ --}}
  {{-- サービスタブとは実績登録テーブルについて共通することも多いが考え方が異なるので分けた方が良い。 --}}
  <div class="result_registration_hidden" id="rr_incompetent_resident_contents">
    {{-- 実績登録テーブル(特殊タブ) --}}
    <table class="caredaisy_table" id="rrt_incompetent_resident">
      <thead class="caredaisy_table_thead fixed_thead">
        <tr>
          <th class="result_registration_table_cell result_registration_table_trash" rowspan="3"></th>
          <th class="result_registration_table_cell result_registration_table_service" rowspan="3">サービス内容</th>
          <th class="result_registration_table_cell result_registration_table_unit" rowspan="3">金額</th>
          <th class="result_registration_table_cell" colspan="2" id="rrt_ddr_incompetent_resident">月間サービス計画及び実績の記録</th>
        </tr>
        <tr>
          <th id="rrt_date_incompetent_resident" class="result_registration_table_cell result_registration_table_plan">日付</th>
          <th class="result_registration_table_cell result_registration_table_sum" rowspan="2">合計</th>
        </tr>
        <tr>
          <th id="rrt_dow_incompetent_resident" class="result_registration_table_cell result_registration_table_result">曜日</th>
        </tr>
      </thead>
      <tbody id="result_registration_tbody_incompetent_resident" class="caredaisy_table_tbody"></tbody>
    </table>

    {{-- プラスボタン(特定入所者サービスコードフォームを呼び出す) --}}
    <button id="rrt_plus_incompetent_resident" title="Add incompetent resident.">＋</button>
  </div>
</div>
