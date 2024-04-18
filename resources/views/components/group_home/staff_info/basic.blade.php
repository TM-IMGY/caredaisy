<div class="tm_contents_hidden" id="tm_contents_staff">
  <!-- ヘッダー情報 -->
  <div class="headers">
      <div class="user_logos" dusk="staff-basic-form-label">基本情報</div>
  </div>
  <div class="history_table_head">
      <div class="log_info">
          <table class="table_staff_info">
              <thead class="table_thead_staff_info">
                  <tr>
                      <th class="staff_info_column">氏名</th>
                      <th class="staff_info_column">雇用形態</th>
                      <th class="staff_info_column">雇用区分</th>
                      <th class="staff_info_column">勤務形態</th>
                      <th class="staff_info_column">住所</th>
                      <th class="staff_info_column">緊急連絡先</th>
                      <th class="staff_info_column">変更日</th>
                  </tr>
              </thead>
              <tbody
                  class="table_tbody_staff_info"
                  id="staff_info_history_table_body"
              ></tbody>
          </table>
      </div>
  </div>
  <div>
      @component('components.staff_info_header')@endcomponent
      <div id="blankBtnStaffInfo">
          {{-- 新規登録/保存ボタン --}}
          <div>
              <button id="stf_new_btn" class="staff_info_btn">
                  新規登録
              </button>
              <button id="stf_save_btn" class="staff_info_btn">
                  保存
              </button>
          </div>
      </div>
      <input type="hidden" name="staff_id">
      <input type="hidden" name="staff_history_id">
      <input type="hidden" id="password_changed">
      {{-- ワーニングメッセージエリア --}}
      <div>
          <ul class="warning" id="validateErrors"></ul>
      </div>
      {{-- 基本カード --}}     
      <div class="staff_info">
          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              氏名
                              <span class="mandatory-color">*必須</span>
                          </p>
                          <input id="stf_name" max="255" required />
                      </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              フリガナ
                              <span class="mandatory-color">*必須</span>
                          </p>
                          <input id="stf_name_kana" max="255" required />
                      </div>
                  </div>
                  
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">性別</p>
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_gender"
                                  value="1"
                                  checked
                              />男</label
                          >
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_gender"
                                  value="2"
                              />女</label
                          >
                      </div>                      
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">雇用形態</p>
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_employment_status"
                                  value="1"
                                  checked
                              />常勤</label
                          >
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_employment_status"
                                  value="2"
                              />非常勤</label
                          >
                      </div>                      
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">雇用区分</p>
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_employment_class"
                                  value="1"
                                  checked
                              />正社員</label
                          >
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_employment_class"
                                  value="2"
                              />パート</label
                          >
                      </div>                      
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">勤務形態</p>
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_working_status"
                                  value="1"
                                  checked
                              />専従</label
                          >
                          <label
                              ><input
                                  class="bi_input_radio_btn"
                                  type="radio"
                                  name="stf_working_status"
                                  value="2"
                              />兼務</label
                          >
                      </div>                      
                  </div>
                  <div class="basic_form_row">
                      
                  </div>                  
              </div>
          </div>
          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              社員番号
                              <span class="mandatory-color">*必須</span>
                          </p>
                          <input id="stf_employee_number" max="255" required />
                      </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              入社日
                              <span class="mandatory-color">*必須</span>
                          </p>
                          <input type="date" min="1900-01-01" max="2100-01-01" name="created" id="stf_date_of_employment" class="fluctuation-input plan_period">
                      </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              ログインID
                          </p>
                          <p id="stf_login_id" class="item_name_service">
                              
                          </p>
                      </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              パスワード
                              <span class="mandatory-color">*必須</span>
                          </p>
                          <input id="stf_password" type="password" max="255" required />
                      </div>
                  </div>        
              </div>
          </div>

          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                    <div class="basic_form_item">
                        <p class="item_name_service">
                          住所  
                        </p>
                        <input
                            id="stf_location"
                            class="basic_location"
                            max="255"
                            placeholder="例）東京都豊島区*********"
                        />
                    </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              電話番号
                          </p>
                          <input
                              id="stf_phone_number"
                              type="number"
                              max="255"
                              required
                          />
                      </div>
                  </div>                
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">緊急連絡先</p>
                          <input id="stf_emergency_contact_information" max="255" />
                      </div>
                  </div> 
              </div>
          </div>
      </div>
  </div>

  <div id="bi_dialog" class="bi_dialog_hidden">
      <div id="bi_dialog_window">
          <p>変更した内容を更新しますか？</p>
          <div>
              <button class="caredaisy_submit_btn" id="bi_dialog_yes">
                  はい
              </button>
              <button class="caredaisy_submit_btn" id="bi_dialog_no">
                  いいえ
              </button>
          </div>
      </div>
  </div>
</div>