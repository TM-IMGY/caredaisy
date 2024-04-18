<div class="tm_contents_hidden" id="tm_contents_3">

    <div id="un_table_meta">
        <div class="un_table_top_header">
            <div class="un_table_lbl_area">
                <div dusk="facility-user-billing-form-label">保険外請求</div>
                {{-- 対象月ラベル --}}
                <div class="un_table_lbl">
                    <div>【</div>
                    <div class="un_table_lbl_header">対象月:</div>
                    <div class="un_table_lbl_data" id="un_table_ym"></div>
                    <div>】</div>
                </div>
            </div>
        <!-- <div id="un_table_submit_btn_area">
                {{-- 保存ボタン --}}
                <form id="un_save_btn_form" method="POST" action="{{ route('service_result.save') }}">
                    @csrf
                    <button class="un_submit_btn">保存</button>
                </form>
            </div> 
        -->
        </div>
        <div class="un_table_lbl_amount_area">
            <div class="un_table_amount_lbl">
                <div class="un_table_lbl_header">保険分自己負担額</div>
                <div class="un_table_lbl_data" id="un_table_uninsured_total"></div>
            </div>
            <div id="un_table_public_payment_area" class="un_table_amount_lbl" style="display: none">
                <div class="un_table_lbl_header">公費自己負担額</div>
                <div class="un_table_lbl_data" id="un_table_public_payment"></div>
            </div>
            <div class="un_table_amount_lbl">
                <div class="un_table_lbl_header">保険外分請求額</div>
                <div class="un_table_lbl_data" id="un_table_uninsured_self_total"></div>
            </div>
        </div>
    </div>

    @component('components.facility_user_info_header')@endcomponent

    <div class="info_table">
        <div id="un_button_area">
            @can('approveRequest')
            <button id="add_agreement">承認する</button>
            @endcan
        </div>
        <div id="un_calendar_area">
            <table class="caredaisy_table" id="calendar">
                <tr id="day_area">
                    <td class="head caredaisy_table_cell un_col2" rowspan="2"></td>
                    <td class="head caredaisy_table_cell un_col3 table_header" rowspan="2">品目</td>
                    <td class="head caredaisy_table_cell un_col4 table_header" rowspan="2">単価</td>
                    <td class="head caredaisy_table_cell un_col5 table_header" rowspan="2">合計数</td>
                    <td class="head caredaisy_table_cell un_col6 table_header" rowspan="2">合計金額</td>
                </tr>
                <tr id="DOW_area">
                </tr>
            </table>
        </div>
        <div style="display:none">
            <table>
                <tr id="original_row">
                    <td class="caredaisy_table_cell"><img src="/sozai/delete_icon.png"></td>
                    <td class="caredaisy_table_cell"><input type="number" min="0" pattern=="^[1-9][0-9]*$"></td>
                    <td class="caredaisy_table_cell"></td>
                    <td class="caredaisy_table_cell"></td>
                    <td class="caredaisy_table_cell"></td>
                </tr>
            </table>
        </div>
        <button id="un_table_plus_btn" dusk="un-table-button">＋</button> 
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
