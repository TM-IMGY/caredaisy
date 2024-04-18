<div class="tm_contents_hidden" id="tm_contents_basic">

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="user_logos" dusk="facility-user-basic-form-label">基本情報</div>
  </div>

  <div>
    <div class="basic_top_ui">
      {{-- 入居/保存ボタン --}}
      <div>
        @can('writeFacilityUser1')
        <button id="basic_create" class="caredaisy_submit_btn" dusk="facility-user-moving-into-button">入居</button>
        <button id="basic_update" class="caredaisy_submit_btn" dusk="facility-user-save-button">保存</button>
        @endcan
      </div>

      <div>
        <label><input class="bi_input" id="bi_invalid_flag_check" type="checkbox">無効フラグ</label>
      </div>
    </div>

    @component('components.facility_user_info_header')@endcomponent
    {{-- ワーニングメッセージエリア --}}
    <div>
      <ul class="warning" id="validateErrorsBasicInfo">
      </ul>
    </div>

    <form action="{{ route('facility_user.insert_form') }}" id="basic_form" method="post">
      @csrf
      {{-- 事業所ID --}}
      <input type="hidden" id='basic_form_facility_id' name="facility_id">
      {{-- 無効フラグ --}}
      <input id="bi_invalid_flag" name="invalid_flag" type="hidden" value="0">
      {{-- 住所地特例 --}}
      <input id="bi_spacial_address_flag" name="spacial_address_flag" type="hidden" value="0">

      {{-- 基本カード --}}
      <div class="basic_form_card">
        <div class="basic_form_row">
          <div class="basic_form_item">
            <div class="bi_form_item_lbl">契約者番号</div>
            <input class="bi_input" id="bi_contractor_number" maxlength="10" name="contractor_number" dusk="facility-user-contractor-number">
          </div>
        </div>
        <p class="basic_form_lbl">基本</p>
        <div>
          <div class="basic_form_row">
            <div class="basic_form_item">
              <div class="bi_form_item_lbl bi_required">利用者姓</div>
              <input class="bi_input" id="bi_last_name" max="255" name="last_name" required dusk="facility-user-form-last-name">
            </div>
            <div class="basic_form_item">
              <div class="bi_form_item_lbl bi_required">利用者名</div>
              <input class="bi_input" id="bi_first_name" max="255" name="first_name" required dusk="facility-user-form-first-name">
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <div class="bi_form_item_lbl bi_required">セイ（フリガナ）</div>
              <input class="bi_input" id="bi_last_name_kana" max="255" name="last_name_kana" required dusk="facility-user-form-last-name-kana">
            </div>
            <div class="basic_form_item">
              <div class="bi_form_item_lbl bi_required">メイ（フリガナ）</div>
              <input class="bi_input" id="bi_first_name_kana" max="255" name="first_name_kana" required dusk="facility-user-form-first-name-kana">
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <span class="bi_form_item_lbl bi_required_radio">性別</span>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_gender_" name="gender" value="" checked hidden></label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_gender_1" name="gender" value="1" dusk="facility-user-form-gender-male" required>男</label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_gender_2" name="gender" value="2" dusk="facility-user-form-gender-female">女</label>
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <p class="bi_form_item_lbl bi_required">生年月日</p>
              <div class="bi_calendar_area">
                <p class="jaCalbox"><span id="jaCalBirthday" class="bi_date_input"></span><span>年</span></p>
                <input type="tel" id="bi_birthday" class="bi_date_input bi_bd_datepicker" name="birthday" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" required dusk="facility-user-form-birthday">
              </div>
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <span class="bi_form_item_lbl">血液型</span>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_blood_type_" name="blood_type" value="" checked hidden></label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_blood_type_1" name="blood_type" value="1">A</label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_blood_type_2" name="blood_type" value="2">B</label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_blood_type_3" name="blood_type" value="3">O</label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_blood_type_4" name="blood_type" value="4">AB</label>
              <label><input class="bi_input_radio_btn" type="radio" id="bi_blood_type_5" name="blood_type" value="5">不明</label>
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <span class="bi_form_item_lbl">RH</span>
              <label><input class="bi_input_radio_btn" id="bi_rh_type_" type="radio" name="rh_type" value="" checked hidden></label>
              <label><input class="bi_input_radio_btn" id="bi_rh_type_1" type="radio" name="rh_type" value="1">＋</label>
              <label><input class="bi_input_radio_btn" id="bi_rh_type_2" type="radio" name="rh_type" value="2">－</label>
              <label><input class="bi_input_radio_btn" id="bi_rh_type_3" type="radio" name="rh_type" value="3">不明</label>
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <div class="bi_form_item_lbl bi_required">被保険者番号</div>
              <input class="bi_input" id="bi_insured_no" name="insured_no" maxlength='10' required dusk="facility-user-form-insured-no">
            </div>
            <div class="basic_form_item">
              <div class="bi_form_item_lbl bi_required">保険者番号</div>
              <input class="bi_input" id="bi_insurer_no" name="insurer_no" maxlength='6' required dusk="facility-user-form-insurer-no">
            </div>
            {{-- 保険者名 --}}
            <div id="bi_insurer_name">
            </div>
          </div>

          <div class="basic_form_row">
            <div class="basic_form_item">
              <div class="bi_form_item_lbl">住所</div>
              <div>
                <input class="bi_input basic_postal_code" id="bi_postal_code" name="postal_code" max="8" placeholder="〒" dusk="facility-user-form-postal-code">
              </div>
              <div>
                <input class="bi_input basic_location" id="bi_location1" maxlength="20" name="location1" placeholder="例）東京都豊島区*********" dusk="facility-user-form-location-1">
              </div>
              <div>
                <input class="bi_input basic_location" id="bi_location2" maxlength="20" name="location2" placeholder="例）池袋*********" dusk="facility-user-form-location-2">
              </div>
            </div>

            <div class="basic_form_item">
              {{-- 住所地特例について仕様調整のため一時凍結する --}}
              {{-- <span class="bi_form_item_lbl">住所地特例</span>
              <input class="bi_input" id="bi_spacial_address_flag_check" type="checkbox"> --}}
            </div>
          </div>
          <div class="basic_form_row">
            <div class="basic_form_item">
              <div class="bi_form_item_lbl">電話番号</div>
              <input class="bi_input" id="bi_phone_number" name="phone_number" max="15" dusk="facility-user-form-phone-number">
            </div>
            <div class="basic_form_item">
              <div class="bi_form_item_lbl">携帯番号</div>
              <input class="bi_input" id="bi_cell_phone_number" max="255" name="cell_phone_number" dusk="facility-user-form-cell-phone-number">
            </div>
          </div>
        </div>
      </div>

      <div>
        <div class="basic_form_card">
          <p class="basic_form_lbl">入退居</p>
          <div>
            <div class="basic_form_row">
              <div class="basic_form_item">
                <div class="bi_form_item_lbl bi_required">入居日(利用開始)</div>
                <div class="bi_calendar_area">
                  <p class="jaCalbox"><span id="jaCalMovingInDate" class="bi_date_input"></span><span>年</span></p>
                  <input type="tel" id="bi_start_date" class="bi_date_input bi_datepicker" name="start_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" required dusk="facility-user-form-start-date">
                </div>
              </div>
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">退居日(利用終了)</div>
                <div class="bi_calendar_area">
                  <p class="jaCalbox"><span id="jaCalMovingOutDate" class="bi_date_input"></span><span>年</span></p>
                  <input type="tel" id="bi_end_date" class="bi_date_input bi_datepicker" name="end_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" dusk="facility-user-form-end-date">
                </div>
              </div>
            </div>

            <div class="basic_form_row">
              <div class="basic_form_item">
                <div class="bi_form_item_lbl bi_required">入居前の状況</div>
                <select class="bi_input" id="bi_before_in_status_id" name="before_in_status_id" required dusk="facility-user-form-before-status">
                  <option  disabled selected></option>
                  <option value="1">居宅</option>
                  <option value="2">医療機関</option>
                  <option value="3">介護老人福祉施設</option>
                  <option value="4">介護老人保健施設</option>
                  <option value="5">介護療養型医療施設</option>
                  <option value="6">認知症対応型共同生活介護</option>
                  <option value="7">特定施設入居者生活介護</option>
                  <option value="8">その他</option>
                  <option value="9">介護医療院</option>
                </select>
              </div>
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">退居後の状況</div>
                <select class="bi_input" id="bi_after_out_status_id" name="after_out_status_id" dusk="facility-user-form-after-status">
                  <option value="" selected></option>
                  <option value="1">居宅</option>
                  <option value="2">医療機関入院</option>
                  <option value="3">死亡</option>
                  <option value="4">その他</option>
                  <option value="5">介護老人福祉施設入所</option>
                  <option value="6">介護老人保健施設入所</option>
                  <option value="7">介護医療型医療施設入院</option>
                  <option value="8">介護医療院入所</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        {{-- 看取り情報カード --}}
        <div class="basic_form_card">
          <p class="basic_form_lbl">看取り情報</p>
          <div>
            <div class="basic_form_row">
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">診断日</div>
                <div class="bi_calendar_area">
                  <p class="jaCalbox"><span id="jaCalDiagnosisDate" class="bi_date_input"></span><span>年</span></p>
                  <input type="tel" id="bi_diagnosis_date" class="bi_date_input bi_datepicker" name="diagnosis_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" dusk="facility-user-form-diagnosis-date">
                </div>
              </div>
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">診断者</div>
                <input class="bi_input" id="bi_diagnostician" max="255" name="diagnostician" dusk="facility-user-form-diagnostician">
              </div>
            </div>

            <div class="basic_form_row">
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">同意日</div>
                <div class="bi_calendar_area">
                  <p class="jaCalbox"><span id="jaCalConsentDate" class="bi_date_input"></span><span>年</span></p>
                  <input type="tel" id="bi_consent_date" class="bi_date_input bi_datepicker" name="consent_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" dusk="facility-user-form-consent-date">
                </div>
              </div>
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">同意者</div>
                <input class="bi_input" id="bi_consenter" max="255" name="consenter" dusk="facility-user-form-consenter">
              </div>
            </div>

            <div class="basic_form_row">
              <div class="basic_form_item">
                <div class="bi_form_item_lbl">同意者連絡先</div>
                <input class="bi_input" id="bi_consenter_phone_number" max="255" name="consenter_phone_number" dusk="facility-user-form-consenter-phone-number">
              </div>
            </div>

            <div class="basic_form_row">
              <div class="basic_form_item">
                <p class="bi_form_item_lbl">看取り日</p>
                <div class="bi_calendar_area">
                  <p class="jaCalbox"><span id="jaCalDeathDate" class="bi_date_input"></span><span>年</span></p>
                  <input type="tel" id="bi_death_date" class="bi_date_input bi_datepicker" name="death_date" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off" dusk="facility-user-form-death-date">
                </div>
              </div>
            </div>

            <div>
              <div class="basic_form_item">
                <p class="bi_form_item_lbl">看取り理由</p>
                <textarea class="bi_input" cols="40" id="bi_death_reason" maxlength="255" name="death_reason" rows="4" dusk="facility-user-form-death-reason"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div id="bi_dialog" class="bi_dialog_hidden">
    <div id="bi_dialog_window">
      <p id="bi_dialog_msg"></p>
      <div id="bi_dialog_btns">
        <button class="caredaisy_submit_btn" id="bi_dialog_yes">はい</button>
        <button class="caredaisy_submit_btn" id="bi_dialog_no">いいえ</button>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
</div>

