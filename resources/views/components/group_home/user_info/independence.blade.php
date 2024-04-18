<div class="tm_contents_hidden" id="tm_contents_independence">
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">
<body>
<div>

    <!-- ヘッダー情報 -->
    <div class="headers">
      <div class="user_logos" dusk="facility-user-independence-form-label">自立度</div>
    </div>

    <!-- ID等の保存用 -->
    <input type="text" value="" id="getFacilityIdIndependence" hidden>
    <input id="saveIdNumMaxIndependence" type="text" value=0 hidden>
    <input id="onBtnIndependence" type="text" value=0 hidden>
    <input id="saveGetIdIndependence" type="text" value=0 hidden>
    <input id="tabIndependence" type="text" value="false" hidden>
    <input id="save_select_list1_independence" type="text" value="false" hidden>
    <input id="save_select_list2_independence" type="text" value="false" hidden>
    <input id="save_text_item1_independence" type="text" value="false" hidden>
    <input id="save_text_item2_independence" type="text" value="false" hidden>
    <input id="newIndependenceData" type="text" value="false" value=0 hidden>
  </div>
  <!-- DB情報更新用のデータ控え -->
  <div id="saveIndependence"></div>
  <div id="container1_independence">
    <div id="contents1_independence">
      <table id="table_independence">
        <thead id="table_thead_independence">
          <tr>
            <th class="independent_independence">障害高齢自立度</th>
            <th class="dementia_independence">認知症高齢者自立度</th>
            <th class="judgment_date_independence">判断日</th>
            <th class="judge_independence">判断者</th>
          </tr>
        </thead>
        <!-- ↓↓　tbody作成　↓↓ 　■修正箇所-->
        <tbody id="table_tbody_independence"></tbody>
      </table>
    </div>
  </div>
  {{-- <div id="from_independence"></div> --}}

  @component('components.facility_user_info_header')@endcomponent

<!--↓↓ 画面下部情報 ↓↓-->
  <div id="blankBtnIndependence">
    @can('writeFacilityUser1')
    <button class="button_independence" id="clearBtn_independence">新規登録</button>
    <button class="button_independence" id="js-updata-popup_independence">保存</button>
    @endcan
  </div>
  <div>
    {{-- ワーニングメッセージエリア --}}
    <div>
      <ul class="warning" id="validateErrorsIndependence">
      </ul>
    </div>
    <div class="form_frame_independence">
      <div class="form_left_independence">
        <div class="inline-block_independence">
          <p class="item_name_independence">障害高齢者自立度
            <span class="mandatory-color">*必須</span>
          </p>
          <select name="independent_list" id="select_list1_independence">
            <option value="">選択してください</option>
            <option value=1>自立</option>
            <option value=2>J1：交通機関利用可</option>
            <option value=3>J2：近隣の外出可</option>
            <option value=4>A1：介助で外出可</option>
            <option value=5>A2：外出頻度少</option>
            <option value=6>B1：車いす利用</option>
            <option value=7>B2：移乗介助で車いす利用</option>
            <option value=8>C1：自力で寝返り可</option>
            <option value=9>C2：自力で寝返り不可</option>
          </select>
          </div>
          <div class="inline-block_independence">
            <p class="item_name_independence">認知症高齢者自立度
              <span class="mandatory-color">*必須</span>
            </p>
            <select name="Dementia_list" id="select_list2_independence">
                <option value="">選択してください</option>
                <option value=1>自立</option>
                <option value=2>Ⅰ：認知症有自立</option>
                <option value=3>Ⅱ：多少意思疎通難自立</option>
                <option value=4>Ⅱa：Ⅱの家庭外</option>
                <option value=5>Ⅱb：Ⅱの家庭内</option>
                <option value=6>Ⅲ：日常生活支障有</option>
                <option value=7>Ⅲa：Ⅲの日中中心</option>
                <option value=8>Ⅲb：Ⅲの夜間中心</option>
                <option value=9>Ⅳ：日常生活支障頻繁</option>
                <option value=10>M：専門医療必要</option>
              </select>
            </div>
          </div>
          <div class="form_left_independence">
          <div class="inline-block_independence">
            <p class="item_name_independence">判断日
              <span class="mandatory-color">*必須</span>
            </p>
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalInStartDate" class="expenditure_date_input"></span><span>年</span></p>
              <input type="tel" id="text_item1_independence" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
          </div>
          <div class="inline-block_independence">
            <p class="item_name_independence">判断者
              <span class="mandatory-color">*必須</span>
            </p>
            <input type="text" id="text_item2_independence" maxlength="30">
          </div>
        </div>
      </div>
    </div>
  </body>
  <!--↑↑ 画面下部情報 ↑↑-->


  <!-- ↓↓　ポップアップ画面　↓↓ -->
  <div id="overflow_independence">
    <div class="conf">
      <p><span id="popup_confirm_message" class="popup_message"></span></p>
      <div class="btns">
      <button class="poppu_ok_independence" id="updatabtn_independence">はい</button>
      <button class="poppu_cancel_independence" id="cancelbtn_independence">いいえ</button>
    </div>
    </div>
  </div>
  <!-- ↑↑　ポップアップ画面　↑↑ -->

</div>
