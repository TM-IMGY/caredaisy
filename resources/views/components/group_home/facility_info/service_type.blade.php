{{-- author: ttakenaka --}}

<div class="tm_contents_hidden" id="tm_contents_service_type">
  {{-- サービス種別 --}}
  <link rel='stylesheet' href="{{ mix('/css/group_home/facility_info/facility_info.css') }}">
  <body>

    <!-- ヘッダー情報 -->
    <div class="headers">
      <div class="facility_logos" dusk="service-type-form-label">サービス種別</div>
    </div>

    <div>
      <!-- ID等の保存用（基本はinputで値保存に使用） -->
      <input type="text" value="0" id="getFacilityIdServiceType" hidden>
      <input id="saveIdNumMaxServiceType" type="text" value=0 hidden>
      <input id="saveGetIdServiceType" type="text" value=0 hidden>
      <input id="saveLeftGetIdServiceType" type="text" value=0 hidden>
      <input id="tabServiceType" type="text" value="false" hidden>
      <input id="onBtnServiceType" type="text" value=0 hidden>
      <input id="newServiceTypeData" type="text" value="false" value=0 hidden>
    </div>
      <!-- DB情報更新用のデータ控え -->
      <div id="saveServiceType"></div>
      <div id="container1_service_type">
        <div id="contents1_service_type">
        <table id="table_service_type">
          <thead id="table_thead_service_type">
            <tr>
              <th class="table_value0_service_type">変更月</th>
              <th class="table_value1_service_type">サービス種別</th>
              <th class="table_value2_service_type">サービス種別名称</th>
              <th class="table_value3_service_type">地域区分</th>
              <th class="table_value4_service_type">単価</th>
            </tr>
          </thead>
          <!-- ↓↓　tbody作成　↓↓ -->
          <tbody id="table_tbody_service_type"></tbody>
        </table>
      </div>
      </div>
      <div id="from_service_type"></div>

    <!--↓↓ 画面下部情報 ↓↓-->
    <div id="blankBtnServiceType">
      @can('writeFacility')
      <button class="button_service_type" id="clearBtn_service_type">新規登録</button>
      <button class="button_service_type" id="js-updata-popup_service_type">保存</button>
      @endcan
    </div>
    <div>
    <div class="form_frame_service_type">
      <div class="form_left_service_type">
      <div class="inline_block_service_type">
        <p class="item_name_service_type">サービス種別<span class="mandatory-color">*必須</span></p>
        <select class="input_style" name="select_list1_service_type" id="select_list1_service_type">
          <option value="">選択してください</option>
          </select>
        </div>
      <div class="inline_block_service_type">
        <p class="item_name_service_type">地域区分<span class="mandatory-color">*必須</span></p>
        <select class="input_style" name="select_list2_service_type" id="select_list2_service_type">
          <option value="">選択してください</option>
          <option value="1">１級地</option>
          <option value="2">２級地</option>
          <option value="3">３級地</option>
          <option value="4">４級地</option>
          <option value="5">５級地</option>
          <option value="6">６級地</option>
          <option value="7">７級地</option>
          <option value="8">その他</option>
        </select>
      </div>
      <div class="inline_block_service_type">
        <p class="item_name_service_type">変更月<span class="mandatory-color">*必須</span></p>
        <div class="calendar_area">
          <p class="jaCalbox"><span id="jaCalSTStartDate"></span><span>年</span></p>
          <input type="tel" id="text_item1_service_type" class="input_style ymdatepicker" maxlength="7" placeholder="yyyy/mm" autocomplete="off">
        </div>
      </div>
    </div>
    <div class="form_left_service_type" style="display:none;">
      <div class="inline_block_service_type">
          <input type="checkbox" name="first_plan_input" id="first_plan_input">
          <label for="first_plan_input">計画書１の入力欄が４つ<br>&ensp; （チェックなしの場合３つ）</label>
      </div>
    </div>
    </div>
    <!--↑↑ 画面下部情報 ↑↑-->
  </body>

<!-- ↓↓　ポップアップ画面　↓↓ -->
<div id="overflow_service_type">
  <div class="conf">
    <p>変更した内容を更新しますか？</p>
    <div class="btns">
    <button class="poppu_ok_service_type" id="updatabtn_service_type">はい</button>
    <button class="poppu_cancel_service_type" id="cancelbtn_service_type">いいえ</button>
  </div>
  </div>
</div>
<!-- ↑↑　ポップアップ画面　↑↑ -->

<!-- ↓↓　一時的に使用　↓↓　-->
<script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>

</div>
</div>

