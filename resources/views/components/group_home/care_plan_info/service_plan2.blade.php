<div class="tm_contents_hidden" id="tm_contents_service_plan2">

  {{-- ヘッダー情報 --}}
  <div class="headers">
    <div class="user_logos" dusk="care-plan-2-form-label">介護計画書2</div>
    {{-- PDF出力、新規作成のプレースホルダ --}}
    <div></div>
  </div>

  @component('components.facility_user_info_header')@endcomponent

  <div id="sp2_ui_row">
    <div id="sp2_meta_info">
      <div>
        <div class="sp2_meta_info_lbl">作成日</div>
        <input class="sp2_meta_info_lbl" id="sp2_create_date" type="date" min="1900-01-01" max="2100-01-01" disabled>
      </div>
      <div>
        <div class="sp2_meta_info_lbl">作成者</div>
        <input class="sp2_meta_info_lbl" id="sp2_author" disabled>
      </div>
    </div>

    {{-- ケアプラン有効期間 --}}
    <div id="sp2_care_plan_period">
      <p id="sp2_care_plan_period_label">ケアプラン期間</p>
      <input id="sp2_care_plan_period_start" min="1900-01-01" max="2100-01-01" type="date" disabled>
      <span>～</span>
      <input id="sp2_care_plan_period_end" min="1900-01-01" max="2100-01-01" type="date" disabled>
    </div>

    <div id="sp2_submit_btn_list">
      @can('decideCarePlan')
      <div class="sp2_submit_btn" id="sp2_issued_btn" style="display:none;">交付済</div>
      <div class="sp2_submit_btn" id="sp2_decision_btn" style="display:none;">確定</div>
      @endcan
      @can('writeCarePlan')
      <div class="sp2_submit_btn" id="sp2_create_btn" style="display:none;">提出</div>
      <div class="sp2_submit_btn" id="sp2_save_btn">保存</div>
      @endcan
      <div class="sp2_submit_btn" id="sp2_output_btn">プレビュー</div>
    </div>
  </div>

  <div class="sp2_table_wrap">
    <table class="caredaisy_table" id="sp2_table">
      <thead class="caredaisy_table_thead">
        <tr>
          <th class="sp2_table_header sp2_table_needs_header" rowspan="2">
            <div>生活全般の解決すべき<br>課題(ニーズ)</div>
          </th>
          <th class="sp2_table_header sp2_table_assistance_goal_header" colspan="4">目標</th>
          <th class="sp2_table_header sp2_table_assistance_contents_header" colspan="4">援助内容</th>
        </tr>
        <tr>
          <th class="sp2_table_header sp2_table_long_header">長期目標</th>
          <th class="sp2_table_header sp2_table_long_period_header">期間</th>
          <th class="sp2_table_header sp2_table_short_header">短期目標</th>
          <th class="sp2_table_header sp2_table_short_period_header">期間</th>
          <th class="sp2_table_header sp2_table_contents_header">サービス内容</th>
          <th class="sp2_table_header sp2_table_staff_header">担当者</th>
          <th class="sp2_table_header sp2_table_frequency_header">頻度、曜日</th>
          <th class="sp2_table_header sp2_table_contents_period_header">期間</th>
        </tr>
      </thead>
      <tbody class="caredaisy_table_tbody" id="sp2_tbody">
      </tbody>
    </table>
  </div>

  <!-- 削除用ポップアップ -->
  <div id="overflow_sp2" class="overflow_sp2">
    <div class="conf_sp2">
        <p class="sp2_popup_message" id="sp2_popup_message_success" style="display:none;">保存しました。<br>「提出」・「確定」・「交付済」 とする場合は<br>「介護計画書1」より操作を行ってください。</p>
        <p class="sp2_popup_message" id="sp2_popup_message_false" style="display:none;">保存に失敗しました。</p>
        <div class="uninsured-service-btns">
            <button class="close_sp2_popup" id="close_sp2_popup">閉じる</button>
        </div>
    </div>
  </div>

  <!-- 交付済プラン変更ポップアップ -->
  <div id="overflow_sp2_change_delivery" class="overflow_sp2">
    <div class="sp2_change_delivery_conf">
        <p>交付済プランを変更しますか？</p>
        <div class="sp2-btns">
            <button class="popup_ok_sp2" id="change_delivery_updatabtn_sp2">はい</button>
            <button class="popup_cancel_sp2" id="change_delivery_cancelbtn_sp2">いいえ</button>
        </div>
    </div>
  </div>

  <!-- ↓↓　ポップアップ画面　↓↓ -->
  <div id="overflow_sp2_delete_row" class='overflow_sp2'>
    <div class="conf_sp2">
      <p class="sp2_popup_message">選択した行を削除してよろしいですか？</p>
      <p class="sp2_popup_message">ニーズ・長期・短期の場合</p>
      <p class="sp2_popup_message">その内容に紐づく行も削除されます</p>
      <div class="sp2-btns">
      <button class="popup_ok_sp2" id="delete_row_confirm_yes">はい</button>
      <button class="popup_cancel_sp2" id="delete_row_confirm_no">いいえ</button>
    </div>
    </div>
  </div>
  <!-- ↑↑　ポップアップ画面　↑↑ -->


</div>
