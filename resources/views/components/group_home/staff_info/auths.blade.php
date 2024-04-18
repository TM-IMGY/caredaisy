<div class="tm_contents_hidden" id="tm_contents_auths">

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="user_logos" dusk="staff-auth-form-label">権限設定</div>
  </div>
  <div class="history_table_head">
      <div class="log_info">
          <table class="table_staff_info">
              <thead class="table_thead_staff_info">
                  <tr>
                      <th class="staff_info_column">法人</th>
                      <th class="staff_info_column">施設</th>
                      <th class="staff_info_column">事業所</th>
                      <th class="staff_info_column">権限グループ</th>
                      <th class="staff_info_column">権限開始日</th>
                      <th class="staff_info_column">権限終了日</th>
                      <th class="staff_info_column">作成日</th>
                  </tr>
              </thead>
              <tbody
                  class="table_tbody_staff_info"
                  id="auths_history_table_body"
              ></tbody>
          </table>
      </div>
  </div>
  <div>
      @component('components.staff_info_header')@endcomponent
      <div id="blankBtnAuths">
          {{-- 新規登録/保存ボタン --}}
          <div>
              <button id="auth_new_btn" class="staff_info_btn">
                  新規登録
              </button>
              <button id="auth_save_btn" class="staff_info_btn">
                  保存
              </button>
          </div>
      </div>
      <input type="hidden" id="auth_staff_id">
      <input type="hidden" id="auth_extent_id">

      {{-- ワーニングメッセージエリア --}}
      <div>
          <ul class="warning" id="authValidateErrors"></ul>
      </div>
      {{-- 基本カード --}}     
      <div class="staff_info">
          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              法人
                          </p>
                          <select class="fum_pulldown" id="auth_corporation"></select>
                      </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              施設
                          </p>
                          <select class="fum_pulldown" id="auth_institution"></select>
                      </div>
                  </div>
                  
                  <div class="basic_form_row">                      
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              事業所
                          </p>
                          <select class="fum_pulldown" id="auth_facility"></select>
                      </div>
                  </div>                  
              </div>
          </div>
          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              権限開始日
                              <span class="mandatory-color">*必須</span>
                          </p>
                          <input type="date" min="1900-01-01" max="2100-01-01" name="created" id="auth_start_date" class="fluctuation-input plan_period">
                      </div>
                  </div>
                  <div class="basic_form_row">
                      <div class="basic_form_item">
                          <p class="item_name_service">
                              権限終了日
                          </p>
                          <input type="date" min="1900-01-01" max="2100-01-01" name="created" id="auth_end_date" class="fluctuation-input plan_period">
                      </div>
                  </div>     
              </div>
          </div>

          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                    <div class="basic_form_item">
                        <p class="item_name_service">
                          権限グループ
                        </p>
                        <div>
                          <label for="auth_administrator">                              
                            <input
                                type="checkbox"
                                id="auth_administrator"
                            />管理者
                        </label>
                        </div>
                        <div>
                          <label for="auth_claimant">                              
                            <input
                                type="checkbox"
                                id="auth_claimant"
                            />請求担当者
                          </label>
                        </div>
                        <div>
                          <label for="auth_planner">                              
                            <input
                                type="checkbox"
                                id="auth_planner"
                            />計画作成者
                          </label>
                        </div>
                    </div> 
                  </div>
              </div>
          </div>
          <div class="basic_form_card">
              <div>
                  <div class="basic_form_row">
                    <div class="basic_form_item">
                        <p class="item_name_service">
                          権限状態
                        </p>
                        <table class="auth_info_table">
                            <thead>
                                <tr>
                                    <th>範囲</th>
                                    <th>権限</th>
                                </tr>
                            </thead>
                            <tbody id="auth_info" class="auth_info_tbody">
                                <tr>
                                    <td>請求</td>
                                    <td>なし</td>
                                </tr>
                                <tr>
                                    <td>ケアプラン</td>
                                    <td>閲覧</td>
                                </tr>
                                <tr>
                                    <td>事業所</td>
                                    <td>なし</td>
                                </tr>
                                <tr>
                                    <td>利用者１</td>
                                    <td>閲覧</td>
                                </tr>
                                <tr>
                                    <td>利用者２</td>
                                    <td>なし</td>
                                </tr>
                                <tr>
                                    <td>権限</td>
                                    <td>なし</td>
                                </tr>
                            </tbody>
                        </table>
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

