<div class="tm_contents_hidden" id="tm_contents_transmit">
    <h2>伝送請求</h2>
    <div class="contents">
        <h3>請求データ一覧</h3>
        <table class="invoice_date_list">
            <thead>
                <tr>
                    <th scope="col">処理対象年月</th>
                    <th scope="col">サービス提供年月</th>
                    <th scope="col">状態</th>
                    <th class="right_cell" scope="col">対象人数</th>
                    <th scope="col">請求データ送信日時</th>
                    <!-- <th scope="col" colspan="2">介護給付費</th> -->
                    <th scope="col">請求データ</th>
                    <th scope="col" colspan="2">請求データ送信</th>
                    <th scope="col">請求データ削除</th>
                </tr>
            </thead>
            <tbody id="transmit_list">
            </tbody>
        </table>
    </div>
    <div class="contents">
        <h3>請求履歴一覧</h3>
        <p class="date_subject">処理対象年月</p>
        <div class="target_date_flex">
            <div class="date_wrap">
                {{-- <input class="date_area" id="transmit_from_date" max="2100-01-01" min="1900-01-01" type="month"> --}}
                <div class="calendar_area">
                    <p class="jaCalbox"><span id="jaCalSTFromDate"></span><span>年</span></p>
                    <input type="tel" id="transmit_from_date" class="date_area ymdatepicker" maxlength="7" placeholder="yyyy/mm" autocomplete="off">
                </div>
                <span class="namisen">～</span>
                {{-- <input class="date_area" id="transmit_to_date" max="2100-01-01" min="1900-01-01" type="month"> --}}
                <div class="calendar_area">
                    <p class="jaCalbox"><span id="jaCalSTToDate"></span><span>年</span></p>
                    <input type="tel" id="transmit_to_date" class="date_area ymdatepicker" maxlength="7" placeholder="yyyy/mm" autocomplete="off">
                </div>
            </div>
            <div>
                <button class="gray_btn" id="transmit_set_filter" type="button">絞り込み</button>
            </div>
        </div>
        {{-- ワーニングメッセージエリア --}}
        <div>
            <ul class="warning transmit_warning" id="transmitValidateErrors">
            </ul>
        </div>
        <table class="invoice_history_list">
            <thead>
                <tr>
                    <th scope="col">処理対象年月</th>
                    <th scope="col">サービス提供年月</th>
                    <th scope="col">状態</th>
                    <th class="right_cell" scope="col">対象人数</th>
                    <th scope="col">請求データ送信日時</th>
                    <!-- <th scope="col" colspan="2">介護給付費</th> -->
                    <th scope="col">請求データ</th>
                </tr>
            </thead>
            <tbody id="history_list">
            </tbody>
        </table>
    </div>
</div>
