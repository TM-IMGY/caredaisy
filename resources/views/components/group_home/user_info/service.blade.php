{{-- author: ttakenaka --}}
<div class="tm_contents_hidden" id="tm_contents_service">
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">
<body>

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="user_logos" dusk="facility-user-service-form-label">サービス</div>
  </div>

  <div>
    <!-- ID等の保存用 -->
    <input type="text" value="" id="getFacilityIdService" hidden>
    <input id="saveIdNumMaxService" type="text" value=0 hidden>
    <input id="onBtnService" type="text" value=0 hidden>
    <input id="saveGetIdService" type="text" value=0 hidden>
    <input id="tabService" type="text" value="false" hidden>
    <input id="save_select_list1_service" type="text" value="false" hidden>
    <input id="save_select_list2_service" type="text" value="false" hidden>
    <input id="save_select_list3_service" type="text" value="false" hidden>
    <input id="save_text_item1_service" type="text" value="false" hidden>
    <input id="save_text_item2_service" type="text" value="false" hidden>
    <input id="service_code_change_service" type="text" value=0 hidden>
    <input id="newServiceData" type="text" value="false" value=0 hidden>
  </div>
  <!-- DB情報更新用のデータ控え -->
  <div id="saveService"></div>
  <div id="container1_service">
    <div id="contents1_service">
      <table id="table_service">
        <thead id="table_thead_service">
          <tr>
            <th class="facility_name_service">事業所名</th>
            <th class="service_type_name_service">サービス種類</th>
            <th class="usage_situation_service">利用状況</th>
            <th class="start_date_service">利用開始日</th>
            <th class="end_date_service">利用終了日</th>
          </tr>
        </thead>
        <!-- ↓↓　tbody作成　↓↓ 　■修正箇所-->
        <tbody id="table_tbody_service"></tbody>
      </table>
    </div>
  </div>
  {{-- <div id="from_service"></div> --}}
  @component('components.facility_user_info_header')@endcomponent

  <!--↓↓ 画面下部情報 ↓↓-->
  <div class="blankBtnService">
    @can('writeFacilityUser1')
    <button class="button_service" id="clearBtn_service">新規登録</button>
    <button class="button_service" id="js-updata-popup_service">保存</button>
    @endcan
  </div>

  {{-- ワーニングメッセージエリア --}}
  <div>
    <ul class="warning" class="warning" id="validateErrorsService">
    </ul>
  </div>

    <div class="form_frame_service">
      <div class="form_left_service">
        <div class="inline-block_service">
          <p class="item_name_service">事業所名
            <span class="mandatory-color">*必須</span>
          </p>
          <select name="select_item_service" id="select_list1_service">
            <option class="option_service" value="">選択してください</option>
          </select>
        </div>
        <div class="inline-block_service">
          <p class="item_name_service">サービス種類
            <span class="mandatory-color">*必須</span>
          </p>
          <select id="select_list2_service">
            <option class="option_service" value="">選択してください</option>
          </select>
        </div>
      <div class="inline-block_service">
        <p class="item_name_service">利用状況
          <span class="mandatory-color">*必須</span>
        </p>
        <select id="select_list3_service">
          <option class="option_service" value="">選択してください</option>
          <option class="option_service" value=1>利用中</option>
          <option class="option_service" value=2>未利用</option>
        </select>
      </div>
      </div>
      <div class="form_left_service">
      <div class="inline-block_service">
          <p class="item_name_service">利用開始日
            <span class="mandatory-color">*必須</span>
          </p>
          {{-- <input type="date" id="text_item1_service" min="1900-01-01" max="2100-01-01"> --}}
          <div class="calendar_area">
            <p class="jaCalbox"><span id="jaCalSeStartDate" class="expenditure_date_input"></span><span>年</span></p>
            <input type="tel" id="text_item1_service" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
          </div>
      </div>
      <div class="inline-block_service">
          <p class="item_name_service">利用終了日</p>
          {{-- <input type="date" id="text_item2_service" min="1900-01-01" max="2100-01-01"> --}}
          <div class="calendar_area">
            <p class="jaCalbox"><span id="jaCalSeEndDate" class="expenditure_date_input"></span><span>年</span></p>
            <input type="tel" id="text_item2_service" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
          </div>
      </div>
      </div>
    </div>
</body>
<!--↑↑ 画面下部情報 ↑↑-->


<!-- ↓↓　ポップアップ画面　↓↓ -->
<div id="overflow_service">
  <div class="conf">
    <p>変更した内容を更新しますか？</p>
    <div class="btns">
    <button class="poppu_ok_service" id="updatabtn_service">はい</button>
    <button class="poppu_cancel_service" id="cancelbtn_service">いいえ</button>
  </div>
  </div>
</div>
<!-- ↑↑　ポップアップ画面　↑↑ -->

</div>
