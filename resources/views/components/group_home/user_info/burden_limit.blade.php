<link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">

<div class="tm_contents_hidden" id="tm_contents_burden_limit">

    {{-- ヘッダー情報 --}}
    <div class="headers">
        <div class="user_logos" dusk="facility-user-burden-limit-form-label">負担限度額</div>
    </div>

    <div class="burden_limit_area">
        <div class="burden_limit_info">
            <div class="log_info_burden_limit">
                <table class="table_burden_limit">
                        <thead class="table_thead_burden_limit">
                            <tr>
                                <th class="burden_limit_th">適用開始日</th>
                                <th class="burden_limit_th">適用終了日</th>
                                <th class="burden_limit_th">食費（限度額）</th>
                                <th class="burden_limit_th">居住費（限度額）</th>
                            </tr>
                        </thead>
                    <tbody class="table_tbody_burden_limit" id="burden_limit_history_table_body"></tbody>
                </table>
            </div>

            @component('components.facility_user_info_header')@endcomponent

            <div class="button_block blankBtnBurdenLimit" id="bl_button_block">
                {{-- @can('writeFacilityUser2') --}}
                <button class="button_burden_limit" id="burden_limit_register">新規登録</button>
                <button class="button_burden_limit" id="burden_limit_update">保存</button>
                {{-- @endcan --}}
            </div>
            {{-- ワーニングメッセージエリア --}}
            <div>
                <ul class="warning" id="validateErrors_burden_limit">
                </ul>
            </div>
            <div class="info">
                <div class="info_block">
                    <input type="hidden" name='burden_limit_information_id' id="burden_limit_information_id" value="">
                    <div class="second_block">
                        <div class="in-block">
                            <p class="item_name">適用開始日
                                <span class="mandatory-color">*必須</span>
                            </p>
                            <div class="calendar_area bl_calendar_area">
                                <p class="jaCalbox"><span id="jaCalBlStartDate" class="expenditure_date_input"></span><span>年</span></p>
                                <input type="tel" id="burden_limit_start_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                            </div>
                        </div>
                        <div class="in-block">
                            <p class="item_name">適用終了日</p>
                            <div class="calendar_area bl_calendar_area">
                                <p class="jaCalbox"><span id="jaCalBlEndDate" class="expenditure_date_input"></span><span>年</span></p>
                                <input type="tel" id="burden_limit_end_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    {{-- max：9999 --}}
                    <div class="in-block">
                        <p class="item_name">食費（限度額）
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <input class="input-text" id="burden_limit_food_expenses" maxlength="4">
                    </div>
                    <div class="in-block">
                        <p class="item_name">居住費（限度額）
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <input class="input-text" id="burden_limit_living_expenses" maxlength="4">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="overflow_burden_limit">
        <div class="conf">
            <p>変更した内容を更新しますか？</p>
            <div class="burden-limit-btns">
                <button class="burden_limit_popup_btn" id="updatabtn_burden_limit">はい</button>
                <button class="burden_limit_popup_btn" id="cancelbtn_burden_limit">いいえ</button>
            </div>
        </div>
    </div>
    <div id="overflow_burden_limit_yearpopup">
        <div class="conf">
            <p>有効開始日より1年以降の年月になっていますが保存しますか？</p>
            <div class="burden-limit-btns">
                <button class="burden_limit_yearpopup_btn" id="updatabtn_burden_limit_yearpopup">はい</button>
                <button class="burden_limit_yearpopup_btn" id="cancelbtn_burden_limit_yearpopup">いいえ</button>
            </div>
        </div>
    </div>
</div>
