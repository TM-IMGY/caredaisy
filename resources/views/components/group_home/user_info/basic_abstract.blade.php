<link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">

<div class="tm_contents_hidden" id="tm_contents_basic_abstract">

    {{-- ヘッダー情報 --}}
    <div class="headers">
        <div class="user_logos" dusk="facility-user-basic-abstract-form-label">基本摘要</div>
    </div>

    <div id="basic_abstract_history">
        <table class="table_basic_abstract">
            <thead class="thead_basic_abstract">
                <tr>
                    <th class="basic_abstract_history_th">適用開始日</th>
                    <th class="basic_abstract_history_th">適用終了日</th>
                    <th class="basic_abstract_history_th">DPCコード</th>
                    <th class="basic_abstract_history_th">利用者状態コード</th>
                </tr>
            </thead>
            <tbody class="tbody_basic_abstract" id="tbody_basic_abstract"></tbody>
        </table>
    </div>
    @component('components.facility_user_info_header')@endcomponent

    <div class="button_block" id="basic_abstract_button_block" style="visibility:hidden">
        @can('writeFacilityUser2')
        <button type="button" id="new_register">新規登録</button>
        <button Type="button" id="basic_abstract_save">保存</button>
        @endcan
    </div>
    {{-- ワーニングメッセージエリア --}}
    <div>
        <ul class="warning" id="validateErrorsBasicAbstract">
        </ul>
    </div>

    <div class="block1">
        <div id="">
            <p class="item_name">DPCコード(上6桁)
                <span class="mandatory-color">*必須</span>
            </p>
            <input type="text" name="" id="dpc_code" maxlength="6">
        </div>
        <div>
            <p class="item_name">主傷病名</p>
            <span id="main_injury_and_illness_name"></span>
        </div>

    </div>

    <div class="block2">
        <div id="">
            <p class="item_name">利用者状態等コード</p>
            <select name="" id="user_status_code">
                <option value="" selected>選択してください</option>
            </select>
        </div>
        <div id="">
            <p class="item_name">適用開始日
                <span class="mandatory-color">*必須</span>
            </p>
            <div class="calendar_area BA_calendar_area">
                <p class="jaCalbox"><span id="jaCalBAStartDate" class="basic_abstract_date_input"></span><span>年</span></p>
                <input type="text" id="basic_abstract_start_date" class="basic_abstract_start_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
        <div id="">
            <p class="item_name">適用終了日</p>
            <div class="calendar_area BA_calendar_area">
                <p class="jaCalbox"><span id="jaCalBAEndDate" class="basic_abstract_date_input"></span><span>年</span></p>
                <input type="text" id="basic_abstract_end_date" class="basic_abstract_end_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
            </div>
        </div>
    </div>

</div>