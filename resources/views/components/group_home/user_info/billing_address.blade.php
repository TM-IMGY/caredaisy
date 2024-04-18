<div class="tm_contents_hidden" id="tm_contents_billing_address">

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="user_logos" dusk="facility-user-billing-address-form-label">請求先情報</div>
  </div>

  <div>
    @component('components.facility_user_info_header')@endcomponent
    <div id="blankBtnBillingAddress">
      {{-- 新規登録/保存ボタン --}}
      <div>
        @can('writeFacilityUser2')
        <button id="uba_new_btn" class="billing_address_btn">新規登録</button>
        <button id="uba_save_btn" class="billing_address_btn">保存</button>
        @endcan
      </div>
    </div>
    
    {{-- ワーニングメッセージエリア --}}
    <div>
      <ul class="warning" id="validateErrors">
      </ul>
    </div>
    {{-- 基本カード --}}
    <div class="basic_form_card">
      <div class="basic_form_item">
        <span class="item_name_service">支払い方法</span>
        <label><input class="bi_input_radio_btn" type="radio" name="uba_payment_method" value="1" checked>口座振込</label>
        <label><input class="bi_input_radio_btn" type="radio" name="uba_payment_method" value="2" >口座引落</label>
        <label><input class="bi_input_radio_btn" type="radio" name="uba_payment_method" value="3">現金</label>
      </div>
    </div>
    <div class="billing_address">
      <div class="basic_form_card">        
        <div class="basic_form_lbl">請求先<button id="uba_get_facility_user_btn">情報転記</button></div>
        <div>
          <div class="basic_form_row">
            <div class="basic_form_item">
              <p class="item_name_service">氏名
              </p>
              <input id="uba_name" max="255">
            </div>
          </div>
          <div class="basic_form_row">
            <div class="basic_form_item">
              <p class="item_name_service">電話番号</p>
              <input id="uba_phone_number" max="255" >
            </div>
            <div class="basic_form_item">
              <p class="item_name_service">携帯番号</p>
              <input id=uba_fax_number max="255" >
            </div>
          </div>
          <div class="basic_form_row">
            <div class="basic_form_item">
              <div class="item_name_service">住所</div>
              <div>
              〒<input id=uba_postal_code class="basic_postal_code"  max="8" placeholder="">
              </div>
              <div>
                <input id=uba_location1 class="basic_location" max="255"  placeholder="例）東京都豊島区*********">
              </div>
              <div>
                <input id=uba_location2 class="basic_location" max="255"  placeholder="例）池袋*********">
              </div>
            </div>
          </div>
          <div>
            <div class="basic_form_item">
              <p class="item_name_service">請求書の備考(78文字まで)</p>
              <textarea class="remarks_textarea_receipt_and_bill" id=uba_remarks_for_bill cols="39" maxlength="78" rows="2"></textarea>
            </div>
            <div class="basic_form_item">
              <p class="item_name_service">領収書の備考(78文字まで)</p>
              <textarea class="remarks_textarea_receipt_and_bill" id=uba_remarks_for_receipt cols="39" maxlength="78" rows="2"></textarea>
            </div>
          </div>
        </div>

      </div>

      <div>

        <div class="basic_form_card" hidden>
          <p class="basic_form_lbl">引き落とし口座</p>
          <div>
            <div class="basic_form_row">
              <div class="basic_form_item">
                <p class="item_name_service">銀行番号
                  <span class="mandatory-color">*必須</span>
                </p>
                <input id=uba_bank_number type="number" max="255" >
              </div>
              <div class="basic_form_item">
                <p class="item_name_service">銀行名</p>
                <input id=uba_bank max="255" >
              </div>
            </div>
            <div class="basic_form_row">
              <div class="basic_form_item">
                <p class="item_name_service">支店番号
                  <span class="mandatory-color">*必須</span>
                </p>
                <input id=uba_branch_number type="number" max="255" required>
              </div>
              <div class="basic_form_item">
                <p class="item_name_service">支店名</p>
                <input id=uba_branch max="255" >
              </div>
            </div>
            <div class="basic_form_row">
              <div class="basic_form_item">
                <p class="item_name_service">口座情報
                  <span class="mandatory-color">*必須</span>
                </p>
                <input id=uba_bank_account type="number" max="255" required>
              </div>
              <div class="basic_form_item">
                <p class="item_name_service">科目</p>
                <label><input class="bi_input_radio_btn" type="radio" name="uba_type_of_account" value="1" checked>普通</label>
                <label><input class="bi_input_radio_btn" type="radio" name="uba_type_of_account" value="2">当座</label>
              </div>
            </div>
            <div class="basic_form_row">
              <div class="basic_form_item">
                <p class="item_name_service">預金者名（カナ）
                  <span class="mandatory-color">*必須</span>
                </p>
                <input id=uba_depositor max="255" >
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
      
  </div>

</div>

