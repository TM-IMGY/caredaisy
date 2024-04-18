{{-- author: hyamada --}}

<link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">

<div class="tm_contents_hidden" id="tm_contents_benefit">

    {{-- ヘッダー情報 --}}
    <div class="headers">
        <div class="user_logos" dusk="facility-user-benefit-form-label">給付率</div>
    </div>

    <div class="benefit_area">
        <div class="benefit_info">
            <div class="log_block">
                <div class="log_info_benefit">
                    <table class="table_benefit">
                            <thead class="table_thead_benefit">
                                <tr>
                                    <th class="benefit_type">給付種類</th>
                                    <th class="benefit_rate">負担割合</th>
                                    <th class="percent_benefit">給付率</th>
                                    <th class="effective_start_date_benefit">有効開始日</th>
                                    <th class="expiry_date_benefit">有効終了日</th>
                                </tr>
                            </thead>
                        <tbody class="table_tbody_benefit" id="benefit_history_table_body"></tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- <div id="from_benefit"></div> --}}

        @component('components.facility_user_info_header')@endcomponent

        <div class="button_block" id="button_block" style="visibility:hidden">
            @can('writeFacilityUser2')
            <button type="button" id="benefit_register">新規登録</button>
            <button Type="button" id="benefit_update">保存</button>
            @endcan
        </div>
        {{-- メッセージエリア --}}
        <div>
            <ul class="warning" id="validateErrorsBenefit"></ul>
            <ul id="benefit_message"></ul>
        </div>
        <div class="info">
            <div class="info_block">
                <input type="hidden" name='benefit_information_id' id="benefit_information_id" value="">
                <div class="first_block">
                    <div class="in-block">
                        <p class="item_name">給付種類
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <select name="benefit_type" id="benefit_type">
                            <option class="" value="" disabled selected></option>
                            <option class="" value="1">介護保険負担割合証</option>
                            <option class="" value="2">給付制限</option>
                            <option class="" value="3">特例措置</option>
                        </select>
                    </div>
                    <div class="in-block rate_block">
                        <p class="item_name">負担割合(給付率:<span id="percent"></span>%)
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <select name="benefit_rate" id="benefit_rate">
                            <option class="" value="" disabled selected></option>
                            <option class="rate_100" value="100">0割</option>
                            <option class="rate_90" value="90">1割</option>
                            <option class="rate_80" value="80">2割</option>
                            <option class="rate_70" value="70">3割</option>
                            <option class="rate_0" value="0">10割</option>
                        </select>
                    </div>
                </div>
                <div class="second_block">
                    <div class="in-block">
                        <p class="item_name">有効開始日
                            <span class="mandatory-color">*必須</span>
                        </p>
                        {{-- <input type="date" min="1900-01-01" max="2100-01-01" name="effective_start_date" id="benefit_effective_start_date" class="benefit_date_input"> --}}
                        <div class="calendar_area">
                            <p class="jaCalbox"><span id="jaCalBeStartDate" class="expenditure_date_input"></span><span>年</span></p>
                            <input type="tel" id="benefit_effective_start_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                        </div>
                    </div>
                    <div class="in-block">
                        <p class="item_name">有効終了日
                            <span class="mandatory-color">*必須</span>
                        </p>
                        {{-- <input type="date" min="1900-01-01" max="2100-01-01" name="expiry_date" id="benefit_expiry_date" class="benefit_date_input" maxlength=10> --}}
                        <div class="calendar_area">
                            <p class="jaCalbox"><span id="jaCalBeEndDate" class="expenditure_date_input"></span><span>年</span></p>
                            <input type="tel" id="benefit_expiry_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="overflow_benefit">
        <div class="conf">
            <p>変更した内容を更新しますか？</p>
            <div class="benefit-btns">
                <button class="benefit_popup_btn" id="updatabtn_benefit">はい</button>
                <button class="benefit_popup_btn" id="cancelbtn_benefit">いいえ</button>
            </div>
        </div>
    </div>
    <div id="overflow_benefit_yearpopup">
        <div class="conf">
            <p>有効開始日より1年以降の年月になっていますが保存しますか？</p>
            <div class="benefit-btns">
                <button class="benefit_yearpopup_btn" id="updatabtn_benefit_yearpopup">はい</button>
                <button class="benefit_yearpopup_btn" id="cancelbtn_benefit_yearpopup">いいえ</button>
            </div>
        </div>
    </div>
</div>
