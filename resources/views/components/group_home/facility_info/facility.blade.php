{{-- author: eikeda --}}

<input type="text" value="" id="getIdInstitution" hidden>
<div class="tm_contents_hidden" id="tm_contents_facility">
  {{-- 施設 --}}

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="facility_logos" dusk="institution-form-label">施設</div>
  </div>

  <div class="facility_area">
    <div class="button_block">
      @can('writeFacility')
      <button id="institution_update" type="button" class="savebtn btnposition">保存</button>
      @endcan
    </div>
    <div class="input_container">
      <div class="row">
        <p class="item_name">施設名<span class="mandatory-color">*必須</span></p>
        <input id="institution_name" type="text" class="width_550px">
      </div>
      <div class="row">
        <p class="item_name">施設名略称</p>
        <input id="institution_abbr" type="text" class="width_550px">
      </div>
      <div class="row">
        <p class="item_name">代表者名</p>
        <input id="institution_rep" type="text" class="manager">
      </div>
      <div class="row">
        <p class="item_name">電話番号</p>
        <input id="institution_phone" type="text" class="phone_width">
      </div>
      <div class="row">
        <p class="item_name">FAX番号</p>
        <input id="institution_fax" type="text" class="fax_width">
      </div>
      <div class="row">
        <p class="item_name">郵便番号</p>
        <input id="institution_postalcode" type="text" class="postalcode_width">
      </div>
      <div class="row">
        <p class="item_name">住所</p>
        <input id="institution_address" type="text" class="width_550px">
      </div>
      <div class="row">
        <p class="item_name">備考</p>
        <textarea id="institution_remarks" name="remarks" class="remarks"></textarea>
      </div>
    </div>
  </div>
</div>

<!-- ↓↓　ポップアップ画面　↓↓ -->
<div id="overflow_institution" class="popuparea">
  <div class="conf">
    <p>変更した内容を更新しますか？</p>
    <div class="btns">
    <button class="poppu_ok_btn" id="updatabtn_institution">はい</button>
    <button class="poppu_cancel_btn" id="cancelbtn_institution">いいえ</button>
  </div>
  </div>
</div>
<!-- ↑↑　ポップアップ画面　↑↑ -->
