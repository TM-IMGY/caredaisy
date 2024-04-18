<div class="tm_contents_hidden" id="tm_contents_addition_status">
  <link rel='stylesheet' href="{{ mix('/css/group_home/facility_info/facility_info.css') }}">
    {{-- ヘッダー情報 --}}
    <div class="headers">
      <div class="facility_logos" dusk="addition-status-form-label">加算状況</div>
    </div>

    <div id="change_view_tab">
      <a class="view_tab addition_view_tab active" id="addtion_status_view">加算状況</a>
      <a class="view_tab special_medical_expenses_view_tab inactive" id="special_medical_expenses_view">特別診療費</a>
    </div>

    <div id="addition_view">
      <div>
        {{-- ID等の保存用（基本はinputで値保存に使用） --}}
        <input type="text" value="0" id="getFacilityIdAdditionStatus" hidden>
        <input id="saveIdNumMaxAdditionStatus" type="text" value=0 hidden>
        <input id="saveGetServiceIdAdditionStatus" type="text" value=0 hidden>
        <input id="saveGetCareHistoriesIdAdditionStatus" type="text" value=0 hidden>
        <input id="saveLeftGetIdAdditionStatus" type="text" value=0 hidden>
        <input id="saveStartMonthAdditionStatus" type="text" hidden>
        <input id="saveEndMonthAdditionStatus" type="text" hidden>
        {{-- サービス種別番号　保存 --}}
        <input id="saveAdditionStatusCodeAdditionStatus" type="text" hidden>
        <input id="tabAdditionStatus" type="text" value="false" hidden>
        <input id="newAdditionStatusData" type="text" value="false" value=0 hidden>
        <input id="saveServiceTypeCodeAdditionStatus" type="text" value="false" value=0 hidden>
        <input id="saveGetCareRewardIdAdditionStatus" type="text" value=0 hidden>
      </div>

      {{-- DB情報更新用のデータ控え --}}
      <div id="saveAdditionStatus"></div>

      {{-- 介護報酬履歴テーブル --}}
      <div id="container1_addition_status">
        <div id="contents1_addition_status">
          <table id="table_addition_status">
            <thead id="table_thead_addition_status">
              <tr>
                <th class="table_value_addition_status1">事業所</th>
                <th class="table_value_addition_status2">サービス種別</th>
                <th class="table_value_addition_status3">開始月</th>
                <th class="table_value_addition_status4">終了月</th>
              </tr>
            </thead>
            <tbody id="table_tbody_addition_status"></tbody>
          </table>
        </div>
      </div>

      <div id="from_addition_status"></div>

      {{-- 画面下部情報 --}}
      <div id="blankBtnAdditionStatus">
        @can('writeFacility')
          <button class="button_addition_status" id="js-new_addition_status">新規登録</button>
          <button class="button_addition_status" id="js-updata-popup_addition_status">保存</button>
        @endcan
        <span class="text_frame_addition_status">
          <div class="text_start_addition_status">開始月
            <span class="mandatory-color text_start_addition_status">*必須</span>
          </div>
          <div class="calendar_area">
            <p class="jaCalbox"><span id="jaCalASStartMonth"></span><span>年</span></p>
            <input type="tel" id="search_start_addition_status" class="ymdatepicker" maxlength="7" placeholder="yyyy/mm" required autocomplete="off">
          </div>
        </span>
        <span class="text_frame_addition_status">
          <div class="text_end_addition_status">終了月</div>
          <div class="calendar_area">
            <p class="jaCalbox"><span id="jaCalASEndMonth"></span><span>年</span></p>
            <input type="tel" id="search_end_addition_status" class="ymdatepicker" maxlength="7" placeholder="yyyy/mm" required autocomplete="off">
          </div>
        </span>
        <button style="display:none;">PDF出力</button>
      </div>

      {{-- 加算状況フォーム --}}
      <div>
        <form id="form_addition_status" name="radio_form_addition_status">
        </form>
      </div>
    </div>

    <div id="special_medical_expenses">
      {{-- 特別診療費履歴 --}}
      <div id="special_medical_expenses_history">
        <table id="special_medical_expenses_history_table">
          <thead id="special_medical_expenses_history_thead">
            <tr>
              <th class="addtion_start_month">加算開始月</th>
              <th class="addtion_end_month">加算終了月</th>
              <th class="special_medical_start_month">特別診療開始月</th>
              <th class="special_medical_end_month">特別診療終了月</th>
            </tr>
          </thead>
          <tbody id="special_medical_expenses_history_tbody"></tbody>
        </table>
      </div>

      {{-- 画面下部情報 --}}
      <div id="blankBtnSpecialMedical">
        @can('writeFacility')
          <button class="special_medical_expenses_btns" id="special_medical_expenses_new_register">新規登録</button>
          <button class="special_medical_expenses_btns" id="special_medical_expenses_save">保存</button>
        @endcan
        <span class="btn_wrap">
          <div class="special_medical_expenses_target_month">開始月
            <span class="mandatory-color ">*必須</span>
          </div>
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalSMEStartMonth"></span><span>年</span></p>
              <input type="tel" id="special_medical_expenses_start" class="ymdatepicker" maxlength="7" placeholder="yyyy/mm" required autocomplete="off">
            </div>
        </span>
        <span class="btn_wrap">
          <div class="special_medical_expenses_target_month">終了月</div>
          <div class="calendar_area">
            <p class="jaCalbox"><span id="jaCalSMEEndMonth"></span><span>年</span></p>
            <input type="tel" id="special_medical_expenses_end" class="ymdatepicker" maxlength="7" placeholder="yyyy/mm" required autocomplete="off">
          </div>
        </span>
      </div>

      {{-- 特別診療内容チェックフォーム --}}
      <div>
        <form id="special_medical_expenses_list" name="special_medical_expenses_list">
        </form>
      </div>
    </div>

  <!-- ↓↓　ポップアップ画面　↓↓ -->
  <div id="overflow_addition_status">
    <div class="conf">
      <p>変更した内容を更新しますか？</p>
      <div class="btns">
        <button id="updatabtn_addition_status" class="poppu_ok_addition_status">はい</button>
        <button id="cancelbtn_addition_status" class="poppu_cancel_addition_status">いいえ</button>
      </div>
    </div>
  </div>
  <!-- ↑↑　ポップアップ画面　↑↑ -->
</div>
