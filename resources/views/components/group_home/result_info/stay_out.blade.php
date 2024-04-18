<div class="tm_contents_hidden" id="tm_contents_5">
  <div class="info_table_list">
    {{-- 情報テーブル --}}
    <div class="info_table">
      <div class="item_name_stayout">外泊情報一覧</div>
      <table class="caredaisy_table" id="stayout_table">
        <thead class="caredaisy_table_thead">
          <tr>
            <th class="caredaisy_table_cell caredaisy_table_header so_table_cell">外泊開始日時</td>
            {{-- <th class="caredaisy_table_cell caredaisy_table_header so_table_cell">当日の欠食(開始日)</td> --}}
            <th class="caredaisy_table_cell caredaisy_table_header so_table_cell">外泊終了日時</td>
            {{-- <th class="caredaisy_table_cell caredaisy_table_header so_table_cell">当日の欠食(終了日)</td> --}}
            <th class="caredaisy_table_cell caredaisy_table_header so_table_cell_reason">外泊理由</td>
            <th class="caredaisy_table_cell caredaisy_table_header so_table_cell_remarks">備考</td>
          </tr>
        </thead>
        <tbody id="stay_out_result_table_body" class="caredaisy_stayout_table_tbody"></tbody>
      </table>
    </div>
  </div>

  {{-- 承認ボタン --}}
  <div id="so_agreement_form">
    @can('writeRequest')
    <button id="so_new_btn">新規登録</button>
    <button id="so_save_btn">保存</button>
    @endcan
    @can('deleteRequest')
    <button id="so_delete_btn">削除</button>
    @endcan
  </div>

  {{-- ワーニングメッセージエリア --}}
  <div>
    <ul class="warning" id="validateErrors">
    </ul>
  </div>

  <div class="stayout_info">
    {{-- 外泊情報 --}}
    <input type="hidden" id="so_id" name="id" value="">
    <table class="stayout_info_table">
      <tbody id="bs_table_body">
        <caption class="title_stayout" dusk="stay-out-form-label">外泊情報</caption>
        <tr>
          <td class="item_name_stayout">
            開始日
            <span class="bi_required">*必須</span>
          </td>
          <td class="item_name_stayout">
            開始時間
            <span class="bi_required">*必須</span>
          </td>
          {{-- <td class="item_name_stayout">当日の欠食</td> --}}
        </tr>
        <tr>
          <td>
            {{-- <input type="date" name="start_date" id="so_start_date" value="" min="1900-01-01" max="2100-01-01"> --}}
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalSOStartDate"></span><span>年</span></p>
              <input type="tel" id="so_start_date" class="stayout_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
          </td>
          <td><input type="time" name="start_time" class="stay_out_time" id="so_start_time" value=""></td>
          
          <!-- inputごと非表示にすると挙動が正常に動作しなくなるのでhiddenで対応。表示するときはcheckboxに変更する。-->
          <td>
            <span><input type="hidden" name="meal_of_the_day_start_morning" value="1" id="so_meal_of_the_day_start_morning"><label for="so_meal_of_the_day_start_morning">{{-- 朝食 --}}</label></span>
            <span><input type="hidden" name="meal_of_the_day_start_lunch" value="1" id="so_meal_of_the_day_start_lunch"><label for="so_meal_of_the_day_start_lunch">{{-- 昼食 --}}</label></span>
            <span><input type="hidden" name="meal_of_the_day_start_snack" value="1" id="so_meal_of_the_day_start_snack"><label for="so_meal_of_the_day_start_snack">{{-- おやつ --}}</label></span>
            <span><input type="hidden" name="meal_of_the_day_start_dinner" value="1" id="so_meal_of_the_day_start_dinner"><label for="so_meal_of_the_day_start_dinner">{{-- 夕食 --}}</label></span>
          </td>
        </tr>
        <tr>
          <td class="item_name_stayout">終了日</td>
          <td class="item_name_stayout" id="end_time_title">終了時間</td>
          {{-- <td class="item_name_stayout">当日の欠食</td> --}}
        </tr>
        <tr>
          <td>
            {{-- <input type="date" name="end_date" id="so_end_date" value="" min="1900-01-01" max="2100-01-01"> --}}
            <div class="calendar_area">
              <p class="jaCalbox"><span id="jaCalSOEndDate"></span><span>年</span></p>
              <input type="tel" id="so_end_date" class="stayout_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
          </td>
          <td><input type="time" name="end_time" class="stay_out_time" id="so_end_time" value=""></td>
          
          <!-- inputごと非表示にすると挙動が正常に動作しなくなるのでhiddenで対応。表示するときはcheckboxに変更する。-->
          <td>
            <span><input type="hidden" name="meal_of_the_day_end_morning" value="1" id="so_meal_of_the_day_end_morning"><label for="so_meal_of_the_day_end_morning">{{-- 朝食 --}}</label></span>
            <span><input type="hidden" name="meal_of_the_day_end_lunch" value="1" id="so_meal_of_the_day_end_lunch"><label for="so_meal_of_the_day_end_lunch">{{-- 昼食 --}}</label></span>
            <span><input type="hidden" name="meal_of_the_day_end_snack" value="1" id="so_meal_of_the_day_end_snack"><label for="so_meal_of_the_day_end_snack">{{-- おやつ --}}</label></span>
            <span><input type="hidden" name="meal_of_the_day_end_dinner" value="1" id="so_meal_of_the_day_end_dinner"><label for="so_meal_of_the_day_end_dinner">{{-- 夕食 --}}</label></span>
          </td>
        </tr>
        <tr>
          <td class="title_stayout" colspan=3>外泊理由
            <span class="bi_required">*必須</span>
          </td>
        </tr>
        <tr>
          <td colspan=3>
            <span><input type="radio" name="reason_for_stay_out" value="1" id="so_reason_for_stay_out_1" dusk="stay-out-radio1"><label for="so_reason_for_stay_out_1">外出</label></span>
            <span><input type="radio" name="reason_for_stay_out" value="2" id="so_reason_for_stay_out_2" dusk="stay-out-radio2"><label for="so_reason_for_stay_out_2">外泊</label></span>
            <span><input type="radio" name="reason_for_stay_out" value="3" id="so_reason_for_stay_out_3" dusk="stay-out-radio3"><label for="so_reason_for_stay_out_3">入院</label></span>
            <span><input type="radio" name="reason_for_stay_out" value="5" id="so_reason_for_stay_out_5" dusk="stay-out-radio5"><label for="so_reason_for_stay_out_5">入所(介護老人保健施設、介護医療院)</label></span>
            <span><input type="radio" name="reason_for_stay_out" value="4" id="so_reason_for_stay_out_4" dusk="stay-out-radio4"><label for="so_reason_for_stay_out_4">その他</label></span>
          </td>
        </tr>
        <tr>
          <td class="title_stayout" colspan=3>備考</td>
        </tr>
        <tr>
          <td colspan=3>
            <textarea name="remarks" rows=5 cols=80></textarea>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
