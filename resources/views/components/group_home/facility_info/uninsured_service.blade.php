{{-- author: --}}
<div class="tm_contents_hidden" id="tm_contents_uninsured_service">
  <link rel='stylesheet' href="{{ mix('/css/group_home/facility_info/facility_info.css') }}">

  <!-- ヘッダー情報 -->
  <div class="headers">
    <div class="facility_logos" dusk="uninsured-form-label">保険外費用</div>
  </div>

  <div id="uninsured_contents">
    <div class="uninsured-histroy">
        <table id="table_uninsured_histroy">
          <thead id="table_thead_uninsured_histroy">
            <tr>
              <th class="uninsured-start-month">開始月</th>
              <th class="uninsured-end-month">終了月</th>
            </tr>
          </thead>
          <tbody id="table_tbody_uninsured_histroy"></tbody>
        </table>
    </div>
    {{-- ワーニングメッセージエリア --}}
    {{-- <div>
      <ul class="warning" id="validateErrorsUninsuredCost">
      </ul>
    </div> --}}
    <div class="uninsured-first-block">
      <div class="uninsured-btn first-content-item">
          @can('writeRequest')
            <button type="button" id="uninsured_btn_register">新規登録</button>
          @endcan
          <div class="unisured-btn_updel">
            @can('writeRequest')
              <button type="button" id="uninsured_btn_update" class="click-not-remove">更新</button>
            @endcan
            @can('deleteRequest')
            <button type="button" id="uninsured_btn_cant_delete" class="click-not-remove">削除</button>
            @endcan
            @can('deleteRequest')
              <button type="button" id="uninsured_btn_delete" class="click-not-remove">削除</button>
            @endcan
          </div>
      </div>
      <div class="first-content-item">
        <p class="item-name start-month">開始月</p>
        <input type="text" name="uninsured_start_month" id="uninsured_start_month" readOnly>
      </div>
      <div class="first-content-item">
        <p class="item-name end-month">終了月</p>
        <input type="text" name="uninsured_end_month" id="uninsured_end_month" readOnly>
      </div>
    </div>

    <div class="uninsured-item-list-table click-not-remove">
      <table class="table-uninsured-item-list">
        <thead id="table_thead_uninsured_item_name">
          <tr>
            <th class="item uninsured_item_name">品目</th>
            <th class="unit_cost uninsured_item_name">単価(円)</th>
            <th class="unit uninsured_item_name">単位</th>
            <th class="set_one uninsured_item_name">毎日1を設定</th>
            <th class="fixed_cost uninsured_item_name">固定費</th>
            <th class="variable_cost uninsured_item_name">変動費</th>
            <th class="welfare_equipment uninsured_item_name">福祉用具</th>
            <th class="meal uninsured_item_name">食事</th>
            <th class="daily_necessary uninsured_item_name">日用品</th>
            <th class="hobby uninsured_item_name">趣味・娯楽</th>
            <th class="escort uninsured_item_name">同行・同伴</th>
          </tr>
        </thead>
        <tbody id="table_tbody_uninsured_items"></tbody>
      </table>
    </div>
    <div class="uninsured-table-plus" id="uninsured_table_plus">+</div>
  </div>

  <!-- リスト追加用ポップアップ -->
  <div id="overflow_add_uninsured_list" class="click-not-remove">
        <div class="conf">
            <p>品目登録</p>
            <div>
              <ul class="warning" id="validateErrorsUninsuredCost">
              </ul>
            </div>
            <div>
                <p class="add_uninsured_list_paragraph"><label>品目</label>&nbsp;<span class="mandatory-color">*必須</span></p>
                <input type="text" id="add_item_name" class="add_item_name popup-text" placeholder="品目">
            </div>
            <div class="unit_cost_block">
              <div>
                <p class="add_uninsured_list_paragraph"><label>単価</label></p>
                <input type="text" id="add_unit_cost" class="add_unit_cost popup-text" placeholder="単価">
              </div>
            </div>
            <div>
                <p class="add_uninsured_list_paragraph"><label>単位</label>&nbsp;<span class="mandatory-color">*必須</span></p>
                <select name="" id="add_unit" class="add_unit popup_select">
                  <option value="" selected disabled>選択してください</option>
                  <option value="1">1回</option>
                  <option value="2">1日</option>
                  <option value="3">1セット</option>
                  <option value="4">1ヶ月</option>
                </select>
            </div>
            <div class="set_one_check">
                <input type="checkbox" name="set_one_check" id="set_one_check" class="popup_checkbox"><label for="set_one_check">毎日1を設定</label>
            </div>
            <div>
                <p class="add_uninsured_list_paragraph"><label>グループ</label></p>
                <div class="add_group">
                  <label for="fixed_cost_check"><input type="checkbox" name="" id="fixed_cost_check" class="popup_checkbox">固定費</label>
                  <label for="daily_necessary_check"><input type="checkbox" name="" id="daily_necessary_check" class="popup_checkbox">日用品</label>
                  <label for="variable_cost_check"><input type="checkbox" name="" id="variable_cost_check" class="popup_checkbox">変動費</label>
                  <label for="hobby_check"><input type="checkbox" name="" id="hobby_check" class="popup_checkbox">趣味・娯楽</label>
                  <label for="welfare_equipment_check"><input type="checkbox" name="" id="welfare_equipment_check" class="popup_checkbox">福祉用具</label>
                  <label for="escort_check"><input type="checkbox" name="" id="escort_check" class="popup_checkbox">同行・同伴</label>
                  <label for="meal_check"><input type="checkbox" name="" id="meal_check" class="popup_checkbox">食事</label>
                </div>
              </div>
            <div class="billing_reflect_flg">
              <label for="billing_reflect_flg"><input type="checkbox" name="" id="billing_reflect_flg" class="popup_checkbox">毎月の自動データ反映を停止</label>
            </div>
            <div class="uninsured-service-btns">
                <button class="add_uninsured_list_poppu_ok" id="add_uninsured_list_poppu_ok">はい</button>
                <button class="add_uninsured_list_poppu_cancel click-not-remove" id="add_uninsured_list_poppu_cancel">いいえ</button>
            </div>
        </div>
    </div>

    <!-- 削除用ポップアップ -->
    <div id="overflow_delete_uninsured_item" class="click-not-remove">
        <div class="conf">
            <p>下記の品目を削除しますか？</p>
            <div>
                <p class="fixed_date-paragraph"><label>品目</label></p>
                <label id="delete_item_name" class="delete_item_name"><label>
            </div>
            <div class="uninsured-service-btns">
                <button class="delete_uninsured_item_poppu_ok" id="delete_uninsured_item_poppu_ok">はい</button>
                <button class="delete_uninsured_item_poppu_cancel click-not-remove" id="delete_uninsured_item_poppu_cancel">いいえ</button>
            </div>
        </div>
    </div>

    <!-- 削除不可能用ポップアップ -->
    <div id="overflow_cant_delete_uninsured_item" class="click-not-remove">
        <div class="conf">
            <p>この品目は現在使用中のため削除できません</p>
            <div class="uninsured-service-cant-btns">
                <button class="delete_uninsured_item_poppu_cancel click-not-remove" id="cant_delete_uninsured_item_poppu_cancel">戻る</button>
            </div>
        </div>
    </div>

    <!-- 同月サービス存在時ポップアップ -->
    <div id="overflow_alert_uninsured_item">
        <div class="conf">
            <p><label>すでに今月の保険外費用は登録されています</label></p>
            <div class="uninsured-service-btns">
                <button class="alert_uninsured_item_poppu_cancel" id="alert_uninsured_item_poppu_cancel">閉じる</button>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
