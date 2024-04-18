{{-- author: ttakenaka --}}
<div class="tm_contents_hidden" id="tm_contents_approval">
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">
<body>

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="user_logos" dusk="facility-user-care-form-label">認定情報</div>
  </div>

  <div>
    <!-- ID等の保存用 -->
    <input type="text" value="" id="getFacilityIdApproval" hidden>
    <input id="saveIdNumMaxApproval" type="text" value=0 hidden>
    <input id="onBtnApproval" type="text" value=0 hidden>
    <input id="saveGetIdApproval" type="text" value=0 hidden>
    <input id="tabApproval" type="text" value="false" hidden>
    <input id="save_select_list1_approval" type="text" value="false" hidden>
    <input id="save_select_list2_approval" type="text" value="false" hidden>
    <input id="save_select_list3_approval" type="text" value="false" hidden>
    <input id="save_text_item1_approval" type="text" value="false" hidden>
    <input id="save_text_item2_approval" type="text" value="false" hidden>
    <input id="save_text_item3_approval" type="text" value="false" hidden>
    <input id="save_text_item4_approval" type="text" value="false" hidden>
    <input id="save_text_item5_approval" type="text" value="false" hidden>
    <input id="newApprovalData" type="text" value="false" value=0 hidden>
  </div>
  <!-- ↓↓　テーブル部分　↓↓　共有部分　 -->
  <!-- DB情報更新用のデータ控え -->
  <div id="saveApproval"></div>
  <div id="container1_approval">
    <div id="contents1_approval">
      <table id="table_approval">
        <thead id="table_thead_approval">
          <tr class="tr_approval">
            <th class="care_level_approval">要介護度</th>
            <th class="certification_status_approval">認定状況</th>
            <th class="recognition_date_approval">認定年月日</th>
            <th class="start_date_approval">有効開始日</th>
            <th class="end_date_approval">有効終了日</th>
            <th class="date_confirmation_insurance_card_approval">保険証確認日</th>
            <th class="date_qualification_approval">交付年月日</th>
          </tr>
        </thead>
        <!-- ↓↓　tbody作成　↓↓ 　■修正箇所-->
        <tbody id="table_tbody_approval"></tbody>
      </table>
    </div>
  </div>
  {{-- <div id="from_approval"></div> --}}

  @component('components.facility_user_info_header')@endcomponent

  <!--↓↓ 画面下部情報 ↓↓-->
<div id="blankBtnApproval">
  @can('writeFacilityUser1')
  <button class="button_approval" id="clearBtn_approval" dusk="clearBtn_approval">新規登録</button>
  <button class="button_approval" id="js-updata-popup_approval" dusk="js-updata-popup_approval">保存</button>
  @endcan
</div>
<div class="form_frame_approval">
  {{-- ワーニングメッセージエリア --}}
  <div>
    <ul class="warning" id="validateErrorsApproval">
    </ul>
  </div>
  <div class="form_left_approval">
      <div class="inline-block_approval">
        <p class="item_name_approval">要介護度
          <span class="mandatory-color">*必須</span>
        </p>
        <select id="select_list1_approval" dusk="select_list1_approval">
          <option class="option_approval" value="">選択してください</option>
          <option class="option_approval" value=1>01：非該当</option>
          <option class="option_approval" value=6>06：事業対象者</option>
          <option class="option_approval" value=11>11：要支援（経過的要介護）</option>
          <option class="option_approval" value=12>12：要支援１</option>
          <option class="option_approval" value=13>13：要支援２</option>
          <option class="option_approval" value=21>21：要介護１</option>
          <option class="option_approval" value=22>22：要介護２</option>
          <option class="option_approval" value=23>23：要介護３</option>
          <option class="option_approval" value=24>24：要介護４</option>
          <option class="option_approval" value=25>25：要介護５</option>

        </select>
      </div>
      <div>
        <div class="inline-block_approval">
          <p class="item_name_approval">認定状況
            <span class="mandatory-color">*必須</span>
          </p>
          <select id="select_list2_approval" dusk="select_list2_approval">
            <option class="option_approval" value="">選択してください</option>
            <option class="option_approval" value=1>申請中</option>
            <option class="option_approval" value=2>認定済</option>
          </select>
        </div>
          <div class="inline-block_approval">
            <p class="item_name_approval">認定年月日
              <span class="mandatory-color mandatory_exclusion">*必須</span>
            </p>
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalApprovalDate" class=" disabled_target_date"></span><span>年</span></p>
              <input type="tel" id="text_item1_approval" class="disabled_target_date datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" dusk="text-item1-approval">
           </div>
          </div>
        </div>
      </div>

      <div class="form_left_approval">
        <div class="inline-block_approval">
            <p class="item_name_approval">有効開始日
              <span class="mandatory-color mandatory_exclusion">*必須</span>
            </p>
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalApprovalStartDate" class=" disabled_target_date"></span><span>年</span></p>
              <input type="tel" id="text_item2_approval" class="disabled_target_date datepicker" dusk="expiration_start_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
        <div class="inline-block_approval">
          <p class="item_name_approval">有効終了日
            <span class="mandatory-color mandatory_exclusion">*必須</span>
          </p>
          <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalApprovalEndDate" class=" disabled_target_date"></span><span>年</span></p>
              <input type="tel" id="text_item3_approval" class="disabled_target_date datepicker" dusk="expiration_end_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
          </div>
        </div>
        <div class="expiration_date">
          <div class="inline-block_approval">
            <div class="select_expiration_date">
              <!-- ↓↓　有効終了日設定ボタン　↓↓ -->
              <p class="item_name_approval">選択すると有効終了日が自動で入力されます。</p>
              <label for="half_year"><input type="radio" class="end_date_btn_approval" id="half_year" name="expiration_date" value="半年" disabled><span class="end_date_label" dusk="expiration_end_date-btn1">半年</span></label>
              <label for="one_year"><input type="radio" class="end_date_btn_approval" id="one_year" name="expiration_date" value="１年" disabled><span class="end_date_label" dusk="expiration_end_date-btn2">１年</span></label>
              <label for="three_years"><input type="radio" class="end_date_btn_approval" id="three_years" name="expiration_date" value="３年" disabled><span class="end_date_label" dusk="expiration_end_date-btn3">３年</span></label>
              <label for="four_years"><input type="radio" class="end_date_btn_approval" id="four_years" name="expiration_date" value="４年" disabled><span class="end_date_label" dusk="expiration_end_date-btn4">４年</span></label>
            </div>
          </div>
        </div>
      </div>

      <div class="form_left_approval">
        <div class="inline-block_approval">
            <p class="item_name_approval">保険証確認日</p>
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalDateCfmInsCard"></span><span>年</span></p>
              <input type="tel" id="text_item5_approval" class="datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
        <div class="inline-block_approval">
            <p class="item_name_approval">交付年月日</p>
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalDateQualification"></span><span>年</span></p>
              <input type="tel" id="text_item4_approval" class="datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
      </div>
</div>

<!--↑↑ 画面下部情報 ↑↑-->
</body>


<!-- ↓↓　ポップアップ画面　↓↓ -->
<div id="overflow_approval">
  <div class="conf">
    <p class="error_text_approval">登録していた認定情報が<br>上書かれてしまいますが<br>よろしいですか？<br><br>認定更新・区分変更の場合は<br>新規登録にて登録してください</p>
    <div class="btns">
    <button class="poppu_ok_approval" id="updatabtn_approval">はい</button>
    <button class="poppu_cancel_approval" id="cancelbtn_approval">いいえ</button>
  </div>
  </div>
</div>
<div id="overflow_approval2">
  <div class="conf">
    <p class="error_text_approval">必須項目を入力してください</p>
    <div class="popup_btn_frame">
    <button class="poppu_close_approval" id="errorbtn_approval">閉じる</button>
  </div>
  </div>
</div>
<div id="overflow_approval3">
  <div class="conf">
    <p class="error_text_approval">有効開始日より4年以降の年月になっていますが保存しますか？</p>
    <div class="btns2">
    <button class="poppu_ok2_approval" id="updatebtn2_approval">はい</button>
    <button class="poppu_cancel2_approval" id="cancelbtn2_approval">いいえ</button>
  </div>
  </div>
</div>
<div id="overflow_approval4">
  <div class="conf">
    <p class="error_text_approval">申請中の情報が既に登録されているため保存できません</p>
    <div class="popup_btn_frame">
    <button class="poppu_close_approval2" id="errorbtn2_approval" dusk="errorbtn2_approval">閉じる</button>
    </div>
  </div>
</div>
<div id="overflow_approval5">
  <div class="conf">
    <p class="error_text_approval">変更した内容を更新しますか？</p>
    <div class="btns">
    <button class="poppu_ok_approval" id="updatebtn5_approval">はい</button>
    <button class="poppu_cancel_approval" id="cancelbtn5_approval">いいえ</button>
  </div>
  </div>
</div>
<!-- ↑↑　ポップアップ画面　↑↑ -->

</div>
