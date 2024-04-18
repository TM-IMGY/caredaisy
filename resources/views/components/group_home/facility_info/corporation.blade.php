{{-- author: eikeda --}}

<link rel='stylesheet' href="{{ mix('/css/group_home/facility_info/facility_info.css') }}">

<input type="text" value="" id="getIdCorporation" hidden>
<div class="tm_contents_hidden" id="tm_contents_corporation">
  {{-- 法人 --}}

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="facility_logos" dusk="corporation-form-label">法人</div>
  </div>

  <div class="corporation_area">
    <div class="button_block">
      @can('writeFacility')
      <button id="corporation_update" type="button" class="savebtn btnposition">保存</button>
      @endcan
    </div>
    <div class="input_container">
      <div class="row">
        <p class="item_name">法人名<span class="mandatory-color">*必須</span></p>
        <input id="corporation_name" type="text" class="width_550px">
      </div>
      <div class="row">
        <p class="item_name">法人名略称</p>
        <input id="corporation_name_abbr" type="text" class="width_550px">
      </div>
      <div class="row">
        <p class="item_name">代表者名</p>
        <input id="corporation_rep" type="text" class="manager">
      </div>
      <div class="row">
        <p class="item_name">電話番号</p>
        <input id="corporation_phone" type="text" class="phone_width">
      </div>
      <div class="row">
        <p class="item_name">FAX番号</p>
        <input id="corporation_fax" type="text" class="fax_width">
      </div>
      <div class="row">
        <p class="item_name">郵便番号</p>
        <input id="corporation_postalcode" type="text" class="postalcode_width">
      </div>
      <div class="row">
        <p class="item_name">住所</p>
        <input id="corporation_address" type="text" class="width_550px">
      </div>
      <div class="row">
        <p class="item_name">備考</p>
        <textarea id="corporation_remarks" name="remarks" class="remarks"></textarea>
      </div>
    </div>
  </div>
</div>

<!-- ↓↓　ポップアップ画面　↓↓ -->
<div id="overflow_corporation" class="popuparea">
  <div class="conf">
    <p>変更した内容を更新しますか？</p>
    <div class="btns">
    <button class="poppu_ok_btn" id="updatabtn_corporation">はい</button>
    <button class="poppu_cancel_btn" id="cancelbtn_corporation">いいえ</button>
  </div>
  </div>
</div>
<!-- ↑↑　ポップアップ画面　↑↑ -->
