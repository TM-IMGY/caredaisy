{{-- author: eikeda --}}

<input type="text" value="" id="getIdOffice" hidden>
<div class="tm_contents_hidden" id="tm_contents_office">
  {{-- 事業所 --}}

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="facility_logos" dusk="facility-form-label">事業所</div>
  </div>

  <div class="office_area">
    <div class="button_block">
      @can('writeFacility')
      <button type="button" id="office_update" class="savebtn btnposition">保存</button>
      @endcan
    </div>
    <div class="input_container info_office">
      <div class="info_left_office">
        <div class="row">
          <p class="item_name">介護事業所番号<span class="mandatory-color">*必須</span></p>
          <input id="facility_number" type="text" class="facility_number_width">
        </div>
        <div class="row">
          <p class="item_name">事業所名称<span class="mandatory-color">*必須</span></p>
          <input id="facility_name_kanji" type="text" class="width_390px">
        </div>
        <div class="row">
          <p class="item_name">事業所名称(カナ)<span class="mandatory-color">*必須</span></p>
          <input id="facility_name_kana" type="text" class="width_390px">
        </div>
        <div class="row">
          <p class="item_name">事業所名略称</p>
          <input id="facility_name_abbr" type="text" class="width_390px">
        </div>
        <div class="row">
          <p class="item_name">管理者氏名</p>
          <input id="facility_manager" type="text" class="manager">
        </div>
        <div class="row">
          <p class="item_name">職名</p>
          <input id="job_title" type="text" class="job_title">
        </div>
      </div>
      <div>
        <div class="row">
          <p class="item_name">保険者番号<span class="mandatory-color">*必須</span></p>
          <input id="insurer_no" type="text" class="insurer_no_width">
        </div>
        <!-- <div class="row">
          <p class="item_name">保険者名<span class="mandatory-color">*必須</span></p>
          <input id="" type="text" class="width_390px">
        </div> -->
        <div class="row">
          <p class="item_name">郵便番号<span class="mandatory-color">*必須</span></p>
          <input id="postal_code" type="text" class="postalcode_width">
        </div>
        <div class="row">
          <p class="item_name">住所<span class="mandatory-color">*必須</span></p>
          <input id="location" type="text" class="width_550px">
        </div>
        <div class="row">
          <p class="item_name">電話番号<span class="mandatory-color">*必須</span></p>
          <input id="phone_number" type="text" class="phone_width">
        </div>
        <div class="row">
          <p class="item_name">FAX番号</p>
          <input id="fax_number" type="text" class="fax_width">
        </div>
        <div class="row">
          <p class="item_name">備考</p>
          <textarea id="remarks" name="remarks" class="remarks"></textarea>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ↓↓　ポップアップ画面　↓↓ -->
<div id="overflow_office" class="popuparea">
  <div class="conf">
    <p>変更した内容を更新しますか？</p>
    <div class="btns">
    <button class="poppu_ok_btn" id="updatabtn_office">はい</button>
    <button class="poppu_cancel_btn" id="cancelbtn_office">いいえ</button>
  </div>
  </div>
</div>
<!-- ↑↑　ポップアップ画面　↑↑ -->
