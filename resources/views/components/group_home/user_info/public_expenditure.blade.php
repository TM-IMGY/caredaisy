{{-- author: hyamada --}}

<link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">


<div class="tm_contents_hidden" id="tm_contents_public_expenditure">

    {{-- ヘッダー情報 --}}
    <div class="headers">
        <div class="user_logos" dusk="facility-user-public-expenditure-form-label">公費情報</div>
    </div>

    <div class="public_expenditure_area">
        {{-- 履歴 --}}
        <div class="public_expenditure_history_table_head">
            <div class="log_info_public">
                <table class="table_public_expenditure">
                    <thead class="table_thead_public_expenditure">
                        <tr>
                            <th class="bearer_number_public">負担者番号</th>
                            <th class="recipient_number_public">受給者番号</th>
                            <th class="legal_name_public">公費略称</th>
                            <th class="effective_start_date_public">有効開始日</th>
                            <th class="expiry_date_public">有効終了日</th>
                            <th class="created_public">公費情報確認日</th>
                            <th class="remarks">本人支払額</th>
                        </tr>
                    </thead>
                    <tbody class="table_tbody_public_expenditure" id="public_expenditure_history_table_body"></tbody>
                </table>
            </div>
            {{-- <div id="from_public_expenditure"></div> --}}
        </div>

        @component('components.facility_user_info_header')@endcomponent

        <div class="public_expenditure_info">
            <div class="public_expenditure_button_block" id="public_expenditure_button_block" style="visibility:hidden">
                @can('writeFacilityUser2')
                    <button type="button" id="expenditure_register">新規登録</button>
                    {{-- <button class="public_expense_copy" id="public_expense_copy">コピーして登録</button> --}}
                    <button type="button" id="expenditure_update">保存</button>
                @endcan
            </div>
            {{-- ワーニングメッセージエリア --}}
            <div>
                <ul class="warning" id="validateErrorsPublicExpenditure">
                </ul>
            </div>

            {{-- 公費情報フォーム --}}
            <div class="wrap-block">
            <div class="public_expenditure_info_block">
                <input type="hidden" name='public_expense_information_id' id="public_expense_information_id" value="">
                <div class="public_expenditure_innrer_block">
                    <div class="first-paragraph">
                        <div class="content-block">
                            <p class="item_name">負担者番号(8桁)
                                <span class="mandatory-color">*必須</span>
                            </p>
                            <input type="text" name="bearer_number" id="bearer_number" class="public_number_input public_expenditure_input" maxlength="8">
                        </div>
                        <div class="content-block">
                            <p class="item_name">受給者番号(7桁)
                                <span class="mandatory-color">*必須</span>
                            </p>
                            <input type="text" name="recipient_number" id="recipient_number" class="public_number_input public_expenditure_input" maxlength="7">
                        </div>
                        <div class="content-block">
                            <p class="item_name">公費略称</p>
                            <p id="legal_name_display" style="margin:0px;">&nbsp;</p>
                            <input type="hidden" name="legal_name" id="legal_name" class="public_expenditure_input" readOnly>
                        </div>
                        <div id="amount_borne_person_block" class="content-block">
                            <p class="item_name">本人支払額</p>
                            <div><input type="text" name="amount_borne_person" id="amount_borne_person" class="public_expenditure_input" maxlength="6" disabled>円</div>
                        </div>
                    </div>
                    <div class="second-paragraph">
                        <div class="content-date-block">
                            <p class="item_name">有効開始日
                                <span class="mandatory-color">*必須</span>
                            </p>
                            <!-- <input type="date" min="1900-01-01" max="2100-01-01" name="public_expense_effective_start_date" id="public_expense_effective_start_date" class="expenditure_date_input"> -->
                            <div class="calendar_area">
                                <p class="jaCalbox"><span id="jaCalPEStartDate" class="expenditure_date_input"></span><span>年</span></p>
                                <input type="tel" id="public_expense_effective_start_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                            </div>
                        </div>
                        <div class="content-date-block">
                            <p class="item_name">有効終了日
                                <span class="mandatory-color">*必須</span>
                            </p>
                            <!-- <input type="date" min="1900-01-01" max="2100-01-01" name="public_expense_expiry_date" id="public_expense_expiry_date" class="expenditure_date_input"> -->
                            <div class="calendar_area">
                                <p class="jaCalbox"><span id="jaCalPEEndDate" class="expenditure_date_input"></span><span>年</span></p>
                                <input type="tel" id="public_expense_expiry_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                            </div>
                        </div>
                        <div class="content-date-block">
                            <p class="item_name">公費情報確認日</p>
                            <!-- <input type="date" min="1900-01-01" max="2100-01-01" name="confirmation_medical_insurance_date" id="confirmation_medical_insurance_date" class="public_expenditure_input"> -->
                            <div class="calendar_area">
                                <p class="jaCalbox"><span id="jaCalPubExpenditureDate" class="expenditure_date_input"></span><span>年</span></p>
                                <input type="tel" id="confirmation_medical_insurance_date" class="expenditure_date_input datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="fourth-paragraph" style="display:none;">
                        <div class="content-block">
                            <p class="item_name">食費負担限度額</p>
                            <input type="text" name="food_expenses_burden_limit" id="food_expenses_burden_limit" class="public_expenditure_input" maxlength="11">
                        </div>
                        <div class="content-block">
                            <p class="item_name">居住費負担限度額</p>
                            <input type="text" name="living_expenses_burden_limit" id="living_expenses_burden_limit" class="public_expenditure_input" maxlength="11">
                        </div>
                        <div class="content-block">
                            <p class="item_name">外来負担金</p>
                            <input type="text" name="outpatient_contribution" id="outpatient_contribution" class="public_expenditure_input" maxlength="11">
                        </div>
                        <div class="content-block">
                            <p class="item_name">入院負担金</p>
                            <input type="text" name="hospitalization_burden" id="hospitalization_burden" class="public_expenditure_input" maxlength="11">
                        </div>
                    </div>
                    <div class="fifth-paragraph" style="display:none;">
                        <div class="content-block">
                            <p class="item_name">申請区分</p>
                            <input type="text" name="application_classification" id="application_classification" class="public_expenditure_input">
                        </div>
                        <div class="content-block">
                            <p class="item_name">特別区分</p>
                            <input type="text" name="special_classification" id="special_classification" class="public_expenditure_input">
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- ポップアップ --}}
    <div id="overflow_public_expenditure">
        <div class="conf">
            <p>変更した内容を更新しますか？</p>
            <div class="btns_public_expenditure">
                <button class="public_expenditure_popup_btn" id="updatabtn_public_expenditure">はい</button>
                <button class="public_expenditure_popup_btn" id="cancelbtn_public_expenditure">いいえ</button>
            </div>
        </div>
    </div>
    <div id="overflow_public_expenditure_yearpopup">
        <div class="conf">
            <p>有効開始日より1年以降の年月になっていますが保存しますか？</p>
            <div class="btns_public_expenditure">
                <button class="public_expenditure_yearpopup_btn" id="updatabtn_public_expenditure_yearpopup">はい</button>
                <button class="public_expenditure_yearpopup_btn" id="cancelbtn_public_expenditure_yearpopup">いいえ</button>
            </div>
        </div>
    </div>
</div>
