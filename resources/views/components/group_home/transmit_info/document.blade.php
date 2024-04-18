<div class="tm_contents_hidden" id="tm_contents_document">
    <h2>通知文書</h2>
    <div class="contents">
        <p class="date_subject">発行日</p>
        <div class="target_date_flex">
            <div class="date_wrap">
                <div class="calendar_area">
                    <p class="jaCalbox"><span id="jaCalDocFromDate"></span><span>年</span></p>
                    <input type="tel" id="document_from_date" class="date_area datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                </div>
                <span class="namisen">～</span>
                <div class="calendar_area">
                    <p class="jaCalbox"><span id="jaCalDocToDate"></span><span>年</span></p>
                    <input type="tel" id="document_to_date" class="date_area datepicker" maxlength="10" placeholder="yyyy/mm/dd" autocomplete="off">
                </div>
            </div>
            <div>
                <button class="gray_btn" id="document_set_filter" type="button">絞り込み</button>
            </div>
        </div>
        {{-- ワーニングメッセージエリア --}}
        <div>
            <ul class="warning transmit_warning" id="documentValidateErrors">
            </ul>
        </div>
        <h3>通知文書一覧</h3>
        <table class="invoice_history_list">
            <thead>
                <tr>
                    <th scope="col">発行日</th>
                    <th scope="col">種類</th>
                    <th scope="col">タイトル</th>
                    <th scope="col">処理対象年月</th>
                    <th scope="col">確認日時</th>
                </tr>
            </thead>
            <tbody id="document_list">
            </tbody>
        </table>
        <!-- モーダルエリアここから -->
        <div class="modal_container" id="modal_container">
            <div class="modal_body">

                <div class="modal_content">
                    <h3>お知らせ</h3>
                    <div class="item_wrap">
                        <p class="modal_sub_title">発行日</p>
                        <p id="modal_published_date"></p>
                    </div>
                    <div class="item_wrap">
                        <p class="modal_sub_title" id="modal_documents_title"></p>
                        <div class="modal_transmit_info_box">
                            <p id="modal_documents_content"></p>
                        </div>
                    </div>
                    <div class="item_wrap">
                        <p class="modal_sub_title">添付ファイル</p>
                        <div class="modal_transmit_file_box" id="modal_file_container"></div>
                        <div class="modal_transmit_exp">
                            <p>※審査状況一覧の印刷には、公益社団法人国⺠健康保険中央会の提供している審査情報印刷プログラムが必要です。</p>
                            <a href="https://www.kokuho.or.jp/supporter/care/sys_dl.html" target="_blank" class="modal_transmit_exp_link link_icon">審査情報提供システムダウンロード用ページ</a>
                        </div>
                        <div class="modal_close_wrap"><button class="gray_btn modal_close" id="modal_close">閉じる</button></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- モーダルエリアここまで -->
    </div>
</div>
