<div class="tm_contents_hidden" id="tm_contents_injury_and_illness">
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">

    <!-- ヘッダー情報 -->
    <div class="headers">
        <div class="user_logos" dusk="facility-user-injury-and-illness-label">傷病名</div>
    </div>

    <div id="injury_and_illness_history">
        <table class="table_injury_and_illness">
            <thead class="thead_injury_and_illness">
                <tr>
                    <th class="injury_and_illness_history_th">適用開始日</th>
                    <th class="injury_and_illness_history_th">適用終了日</th>
                    <th class="injury_and_illness_history_th">傷病名1</th>
                    <th class="injury_and_illness_history_th">傷病名2</th>
                    <th class="injury_and_illness_history_th">傷病名3</th>
                    <th class="injury_and_illness_history_th">傷病名4</th>
                </tr>
            </thead>
            <tbody class="tbody_injury_and_illness" id="tbody_injury_and_illness"></tbody>
        </table>
    </div>

    @component('components.facility_user_info_header')@endcomponent

    <div>
        <div class="injury_and_illness_button_block" id="injury_button_block" style="visibility:hidden">
            @can('writeFacilityUser2')
            <button type="button" id="injury_and_illness_new_register" class="save_button">新規登録</button>
            <button Type="button" id="injury_and_illness_save" class="save_button">保存</button>
            @endcan
        </div>

        <div id="" class="injury_and_illness_button_block">
            <p class="item_name">適用開始日
                <span class="mandatory-color">*必須</span>
            </p>
            <div class="calendar_area injury_and_illness_calendar_area">
                <p class="jaCalbox"><span id="jaCalIAIStartDate" class="injury_and_illness_date_input"></span><span>年</span></p>
                <input type="text" id="injury_and_illness_start_date" class="injury_and_illness_start_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
        <div id="" class="injury_and_illness_button_block">
            <p class="item_name">適用終了日</p>
            <div class="calendar_area injury_and_illness_calendar_area">
                <p class="jaCalbox"><span id="jaCalIAIEndDate" class="injury_and_illness_date_input"></span><span>年</span></p>
                <input type="text" id="injury_and_illness_end_date" class="injury_and_illness_end_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- ワーニングメッセージエリア --}}
    <div>
        <ul class="warning" id="validateErrorsInjuryAndIllness">
        </ul>
    </div>

    <div id="injury_and_illness_name">
    @for ($i = 1; $i < 5; $i++)
        <div class="wrap">
            <div class="names">
                <label for="name" class="injury_and_illness_name_item">傷病名{{$i}}</label>
                <input type="text" name="" id="injury_and_illness_name_{{$i}}" class="injury_and_illness_name_item injury_and_illness_name" maxlength="100" >
            </div>
            <div class="special">
                <label for="" class="special_item">特別診療費</label>
                <select name="" id="" class="special_item special_select special_select_{{$i}} select_num_1" >
                    {{-- option自体はjs側で作成 --}}
                    <option value="" selected>選択してください</option>
                </select>
                <select name="" id="" class="special_item special_select special_select_{{$i}} select_num_2">
                    <option value="" selected>選択してください</option>
                </select>
                <select name="" id="" class="special_item special_select special_select_{{$i}} select_num_3">
                    <option value="" selected>選択してください</option>
                </select>
                <select name="" id="" class="special_item special_select special_select_{{$i}} select_num_4">
                    <option value="" selected>選択してください</option>
                </select>
                <select name="" id="" class="special_item special_select special_select_{{$i}} select_num_5">
                    <option value="" selected>選択してください</option>
                </select>
            </div>
        </div>
    @endfor
    </div>

</div>
