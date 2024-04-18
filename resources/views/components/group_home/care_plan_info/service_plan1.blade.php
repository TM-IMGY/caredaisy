<div class="tm_contents_hidden" id="tm_contents_service_plan1">

    {{-- ヘッダー情報 --}}
    <div class="headers" id="sp1_header">
        <div class="user_logos" dusk="care-plan-1-form-label">介護計画書1</div>
        {{-- PDF出力、新規作成のプレースホルダ --}}
        <div class="plan1_head_btns">
            <div class="">
                <button type="button" id="sp1_preview" class="sp1_pdf_btn">プレビュー</button>
                <form action="{{route('group_home.service_plan1_pdf_preview')}}" id="pdf_preview_form" method="get" rel="noopener noreferrer" target="_blank">
                    @csrf
                </form>
            </div>
            <div class="plan1_copy">
                <button type="button" id="sp1_copy" class="sp1_pdf_btn">PDF出力</button>
                <form action="{{route('group_home.service_plan_pdf_consecutive')}}" id="consecutive_pdf_form" method="get" rel="noopener noreferrer" target="_blank">
                    @csrf
                </form>
            </div>
            <div id="next_plan">
                @can('writeCarePlan')
                <button type="button" class="next-plan" id="next_plan_button">次回プラン作成</button>
                @endcan
            </div>
        </div>
    </div>
        <div class="service_plan1_history_table_head">
            <div class="log_info_plan1">
                <table class="table_service_plan1">
                    <thead class="table_thead_service_plan1">
                        <tr>
                            <th class="delivary-date-history history_th">交付日</th>
                            <th class="plan1-care-period-start-history history_th">ケアプラン開始日</th>
                            <th class="plan1-care-period-end-history history_th">ケアプラン終了日</th>
                            <th class="care-level-history history_th">介護度</th>
                            <th class="certification-done-history history_th">確定日</th>
                            <th class="created-histroy history_th">作成日</th>
                            <th class="author-history history_th">作成者</th>
                        </tr>
                    </thead>
                    <tbody class="table_tbody_service_plan1" id="service_plan1_history_table_body"></tbody>
                </table>
            </div>
        </div>

        <div class="plan_info">
        <!-- 値保持用 -->
        <input type="hidden" name='service_plan_id' id="service_plan_id" value="">
        <input type="hidden" name='first_service_plan_id' id="first_service_plan_id" value="">
        <input type="hidden" id="status" value="">

            <div class="sp1_facility_user_info_header">
                @component('components.facility_user_info_header')@endcomponent
            </div>

            {{-- ワーニングメッセージエリア --}}
            <div>
                <ul class="warning" id="validateErrorsServicePlan1">
                </ul>
            </div>

            <div class="care-plan-info-block block">
                {{-- 初回、紹介、継続ボタン --}}
                <div class = "plan-division-item second-content-item" id = "plan-division-item">
                    <label for = "first_time"><input type = "checkbox" name = "plan_division" class = "plan_division" id = "first_time" value = 1><span class = "sp1_label">初回</span></label>
                    <label for = "introduce"><input type = "checkbox" name = "plan_division" class = "plan_division" id = "introduce" value = 2><span class = "sp1_label">紹介</span></label>
                    <label for = "continu"><input type = "checkbox" name = "plan_division" class = "plan_division" id = "continu" value = 3><span class = "sp1_label" dusk="btn_continue">継続</span></label>
                </div>

                {{-- ケアプラン有効期間 --}}
                <div class="block-content-item">
                    <p class="item-name">ケアプラン期間
                        <span class="mandatory-color">*必須</span>
                    </p>
                    <input id="sp1_care_plan_period_start" class="fluctuation-input plan_period" min="1900-01-01" max="2100-01-01" type="date">
                    <span>～</span>
                    <input id="sp1_care_plan_period_end" class="fluctuation-input plan_period" min="1900-01-01" max="2100-01-01" type="date">
                    {{-- ケアプラン有効期間自動入力 --}}
{{--
                    <div class="sp1_api">
                        <p class="sp1_api_lbl">選択すると有効終了日が自動で入力されます。</p>
                        <label for="sp1_api_one_month">
                            <input type="radio" class="sp1_api_radio_btn" id="sp1_api_one_month" name="sp1_api_radio_btn" value="one_month">
                            <span class="sp1_api_radio_btn_lbl">１か月</span>
                        </label>
                        <label for="sp1_api_three_month">
                            <input type="radio" class="sp1_api_radio_btn" id="sp1_api_three_month" name="sp1_api_radio_btn" value="three_month" >
                            <span class="sp1_api_radio_btn_lbl">３か月</span>
                        </label>
                        <label for="sp1_api_half_year">
                            <input type="radio" class="sp1_api_radio_btn" id="sp1_api_half_year" name="sp1_api_radio_btn" value="half_year">
                            <span class="sp1_api_radio_btn_lbl">半年</span>
                        </label>
                        <label for="sp1_api_one_year">
                            <input type="radio" class="sp1_api_radio_btn" id="sp1_api_one_year" name="sp1_api_radio_btn" value="one_year">
                            <span class="sp1_api_radio_btn_lbl">１年</span>
                        </label>
                    </div>
--}}
                </div>

                {{-- 保存、提出、確定、交付済ボタン --}}
                <div class="block-content-item status-wrap">
                    <p class="sp1_last_update">最終更新：<span id="sp1_last_update_date" class="sp1_last_update_text"></span>&nbsp;<span id="sp1_last_update_status" class="sp1_last_update_text"></span></p>
                    @can('writeCarePlan')
                    <button type="button" class="status" id="status_tmp" value="1">保存</button>
                    @endcan
                    @can('decideCarePlan')
                    <button type="button" class="status deactivation_target" id="status_confirm" value="3">確定</button>
                    <button type="button" class="status deactivation_target" id="status_done" value="4">交付済</button>
                    @endcan
                </div>
            </div>

            <div class="block">
                {{-- 作成、作成者 --}}
                <div id="sp1_creation_author_domain">
                    <div class="block-content-item plan_end_period">
                        <p class="item-name">作成者
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <input type="text" name="author" id="plan_end_period" class="fluctuation-input plan_period">
                    </div>
                    <div class="block-content-item plan_start_period">
                        <p class="item-name">作成日
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <input type="date" min="1900-01-01" max="2100-01-01" name="created" id="plan_start_period" class="fluctuation-input plan_period">
                    </div>
                    <div class="block-content-item first_plan_start_period">
                        <p class="item-name">初回施設サービス計画作成日
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <input type="date" min="1900-01-01" max="2100-01-01" id="first_plan_start_period" class="fluctuation-input plan_period">
                    </div>
                </div>
            </div>

            <div class="approval_info_block block">
                <div class="approval">
                    <p class="item-name">認定状況
                        <span class="mandatory-color">*必須</span>
                    </p>
                    <select id="certification_status">
                        <option class="option_approval" value="">選択してください</option>
                        <option class="option_approval" value=1>申請中</option>
                        <option class="option_approval" value=2>認定済</option>
                    </select>
                </div>
                <div class="approval">
                    <div>
                        <p class="item-name">要介護度
                            <span class="mandatory-color">*必須</span>
                        </p>
                        <select id="care_level">
                            <option class="option_approval" value="">選択してください</option>
                        </select>
                    </div>
                    <div>
                        <input type="checkbox" name="care_level_dispflg" id="care_level_dispflg"><label for="care_level_dispflg">要介護状態区分に〇を付ける</label>
                    </div>
                </div>
                <div class="approval">
                    <p class="item-name">認定年月日</p>
                    <input type="date" min="1900-01-01" max="2100-01-01" name="recognition_date" id="recognition_date" class="fluctuation-input approval_date disabled_target_date">
                </div>
                <div class="approval">
                    <p class="item-name">有効開始日</p>
                    <input id="care_period_start" class="fluctuation-input approval_date disabled_target_date" min="1900-01-01" max="2100-01-01" type="date">
                </div>
                <div>
                    <p class="item-name">有効終了日</p>
                    <input id="care_period_end" class="fluctuation-input approval_date disabled_target_date" min="1900-01-01" max="2100-01-01" type="date">
                    <div class="sp1_approval_api">
                        <p class="sp1_approval_api_lbl">選択すると有効終了日が自動で入力されます。</p>
                        <label for="sp1_approval_api_half_year">
                            <input type="radio" class="sp1_approval_api_radio_btn" id="sp1_approval_api_half_year" name="sp1_approval_api_radio_btn" value = 6>
                            <span class="sp1_approval_api_radio_btn_lbl">半年</span>
                        </label>
                        <label for="sp1_approval_api_one_year">
                            <input type="radio" class="sp1_approval_api_radio_btn" id="sp1_approval_api_one_year" name="sp1_approval_api_radio_btn" value = 1>
                            <span class="sp1_approval_api_radio_btn_lbl">１年</span>
                        </label>
                        <label for="sp1_approval_api_three_year">
                            <input type="radio" class="sp1_approval_api_radio_btn" id="sp1_approval_api_three_year" name="sp1_approval_api_radio_btn" value = 3>
                            <span class="sp1_approval_api_radio_btn_lbl">３年</span>
                        </label>
                        <label for="sp1_approval_api_four_year">
                            <input type="radio" class="sp1_approval_api_radio_btn" id="sp1_approval_api_four_year" name="sp1_approval_api_radio_btn" value = 4>
                            <span class="sp1_approval_api_radio_btn_lbl">４年</span>
                        </label>
                    </div>
                </div>

            </div>

            <div class="title_and_content_block block">
                <div>
                    <table id="service_plan1_table">
                        <tbody>
                            <tr class="service-plan1-classification-tr">
                                <td class="service-plan1-classification-td" id="title1_td">
                                    <textarea name="" id="title1" class="classification-title fluctuation-input" rows="3" maxlength="255" readOnly></textarea>
                                </td>
                                <td class="service-plan1-contents-td">
                                    <textarea name="" id="content1" rows="5" class="first-plan-text fluctuation-input"></textarea>
                                </td>
                            </tr>
                            <tr class="service-plan1-classification-tr">
                                <td class="service-plan1-classification-td">
                                    <textarea name="" id="title2" class="classification-title fluctuation-input" rows="3" maxlength="255" readOnly></textarea>
                                </td>
                                <td class="service-plan1-contents-td">
                                    <textarea name="" id="content2" rows="5" class="first-plan-text fluctuation-input"></textarea>
                                </td>
                            </tr class="service-plan1-classification-tr">
                            <tr>
                                <td class="service-plan1-classification-td">
                                    <textarea name="" id="title3" class="classification-title fluctuation-input" rows="3" maxlength="255" readOnly></textarea>
                                </td>
                                <td class="service-plan1-contents-td">
                                    <textarea name="" id="content3" rows="5" class="first-plan-text fluctuation-input"></textarea>
                                </td>
                            </tr>
                            <tr id="classification4">
                                <td class="service-plan1-classification-td">
                                    <textarea name="" id="title4" class="classification-title fluctuation-input" rows="3" maxlength="255" placeholder="機能開発中" readonly></textarea>
                                </td>
                                <td class="service-plan1-contents-td">
                                    <textarea name="" id="content4" rows="5" maxlength="255" class="first-plan-text fluctuation-input" placeholder="機能開発中" readonly></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {{-- 生活援助中心型の算定理由 --}}
{{--
                <div class="calculate_reason">
                    <p class="item-name-calculate_reason">生活援助中心型の算定理由</p>
                    <div class="calculate-reason-wrap">
                        <input type="checkbox" name="living_alone" class="calculate_reason living_alone" id="living_alone"><label for="living_alone" class="calculate-reason-label">一人暮らし</label>
                        <input type="checkbox" name="handicapped" class="calculate_reason handicapped" id="handicapped"><label for="handicapped" class="calculate-reason-label">家族等の障害、疾病</label>
                        <input type="checkbox" name="other" class="calculate_reason other" id="other"><label for="other" class="calculate-reason-label">その他</label><textarea name="" class="other_reason" id="other_reason" maxlength="30" disabled></textarea>
                    </div>
                </div>
--}}
            </div>
        </div>

    {{-- 交付済みボタン用ポップアップ --}}
    <div id="overflow_service_plan1_delivery_date" class="overflow_service_plan1">
        <div class="conf">
            {{-- ワーニングメッセージエリア --}}
            <div id="validateErrorsServicePopupPlan1">
                <p>このプランを本当に<br>交付済のプランにしますか？</p>
            </div>
            <div>
                <p class="delivery-date-paragraph"><label>交付日時</label><span class="mandatory-color">*必須</span></p>
                <input type="datetime-local" id="delivery_date" class="delivery-date-input delivery_date" min="1900-01-01T00:00" max="2100-01-01T00:00">
            </div>
            <div>
                <p class="delivery-date-paragraph"><label>同意者</label><span class="mandatory-color">*必須</span></p>
                <input type="text" id="delivery_date_consent" class="delivery-date-input delivery-date-consent">
            </div>
            <div>
                <p class="delivery-date-paragraph"><label>場所</label></p>
                <input type="text" id="delivery_date_place" class="delivery-date-input delivery-date-place">
            </div>
            <div>
                <p class="delivery-date-paragraph"><label>備考</label></p>
                <textarea id="delivery_date_remarks" class="delivery-date-input delivery-date-remarks" rows="5"></textarea>
            </div>
            <div class="sp1-btns">
                <button class="popup_ok_service_plan1" id="delivery_date_updatebtn_service_plan1" value="4">はい</button>
                <button class="popup_cancel_service_plan1" id="delivery_date_cancelbtn_service_plan1">いいえ</button>
            </div>
        </div>
    </div>
    {{-- 保存ボタン用ポップアップ --}}
    <div id="overflow_service_plan1_status_tmp" class="overflow_service_plan1" dusk="care_plan_dialog">
        <div class="conf">
            <p>ケアプラン終了日が開始日より1年以上先の日付で入力されていますが保存しますか？</p>
            <div class="sp1-btns">
                <button class="popup_ok_service_plan1" id="status_tmp_updatebtn_service_plan1" value="1">はい</button>
                <button class="popup_cancel_service_plan1" id="status_tmp_cancelbtn_service_plan1">いいえ</button>
            </div>
        </div>
    </div>
    <!-- 確定ボタン用ポップアップ -->
    <div id="overflow_service_plan1_fixed_date" class="overflow_service_plan1">
        <div class="conf">
            <p>このプランを本当に<br>確定済のプランにしますか？</p>
            <div>
                <p class="fixed_date-paragraph"><label>確定日</label><span class="mandatory-color">*必須</span></p>
                <input type="date" id="fixed_date" class="fixed_date-input fixed_date" min = "1900-01-01" max = "2100-01-01">
            </div>
            <div class="sp1-btns">
                <button class="popup_ok_service_plan1" id="fixed_date_updatebtn_service_plan1" value="3">はい</button>
                <button class="popup_cancel_service_plan1" id="fixed_date_cancelbtn_service_plan1">いいえ</button>
            </div>
        </div>
    </div>

    <!-- 交付済プラン変更ポップアップ -->
    <div id="overflow_service_plan1_change_delivery" class="overflow_service_plan1">
        <div class="conf">
            <p id="change_plan_message"></p>
            <div class="sp1-btns">
                <button class="popup_ok_service_plan1" id="change_delivery_updatebtn_service_plan1">はい</button>
                <button class="popup_cancel_service_plan1" id="change_delivery_cancelbtn_service_plan1">いいえ</button>
            </div>
        </div>
    </div>

    <!-- pdf出力・プレビュー ポップアップ -->
    <div id="overflow_service_plan1_pdf_dl" class = "overflow_service_plan1">
        <div class="conf">
            <p>PDFダウンロード</p>
            <div class="pdf-radio">
                <input type="radio" name="service_plan1_pdf_dl" id="service_plan1_year_dl"><label for="service_plan1_year_dl" class="dl-label">西暦表示</label>
                <input type="radio" name="service_plan1_pdf_dl" id="service_plan1_japanese_year_dl" checked="checked"><label for="service_plan1_japanese_year_dl" class="dl-label">和暦表示</label>
            </div>
            <div class="sp1-btns">
                <button class="popup_ok_service_plan1" id="pdf_dl_updatabtn_service_plan1" value='true'>はい</button>
                <button class="popup_cancel_service_plan1" id="pdf_dl_cancelbtn_service_plan1" value="false">いいえ</button>
            </div>
        </div>
    </div>
    <div id="overflow_service_plan1_pdf_preview" class = "overflow_service_plan1">
        <div class="conf">
            <p>PDFプレビュー</p>
            <div class="pdf-radio">
                <input type="radio" name="service_plan1_pdf_preview" id="service_plan1_year_preview"><label for="service_plan1_year_preview" class="preview-label">西暦表示</label>
                <input type="radio" name="service_plan1_pdf_preview" id="service_plan1_japanese_year_preview" checked="checked"><label for="service_plan1_japanese_year_preview" class="preview-label">和暦表示</label>
            </div>
            <div class="sp1-btns">
                <button class="popup_ok_service_plan1" id="pdf_preview_updatabtn_service_plan1" value='true'>はい</button>
                <button class="popup_cancel_service_plan1" id="pdf_preview_cancelbtn_service_plan1" value="false">いいえ</button>
            </div>
        </div>
    </div>

</div>
