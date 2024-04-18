import Billing from "./billing.js"
import CareBenefitStatement from "./care_benefit_statement.js"
import PdfDemoAll from "./pdf_demo_all.js"
import PdfDemoFacility from "./pdf_demo_facility.js"
import UsageFeeAll from "./usage_fee_all.js"
import MakeInvoice from "./make_invoice.js"

import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';

export default class DataExportPullDownMenu {

    constructor(facilityId, year, month, userCnt, nationalHealth) {
        // 現在対象としている事業所のID
        this.facilityId = facilityId;
        // 現在選択している施設利用者のID
        this.facilityUserId = null;
        // 現在対象としている施設利用者のリスト
        this.facilityUsers = [];
        // 現在の対象年月
        this.year = year;
        this.month = month;
        this.userCnt = userCnt;

        // 国保連請求データを出力メニューにクリックイベントを紐づける
        this.elementMenuBillingBtn = document.getElementById('dep_billing');
        this.elementMenuBillingBtn.addEventListener('click', this.clickBillingBtn.bind(this));

        // 後消し：イベントの書き換えを行う
        // 介護給付費請求書と利用者全員分の介護給付費明細書出力イベント
        this.elementMenuPdfDemoAllBtn = document.getElementById('dep_pdf_demo_all');
        // this.elementUsageFeeInvoiceBtn = document.getElementById('dep_usage_fee_invoice');
        this.elementMenuPdfDemoAllBtn.addEventListener('click', this.clickPdfDemoAllBtn.bind(this));

        // 介護給付費請求書出力イベント
        this.elementMenuPdfDemoFacilityBtn = document.getElementById('dep_pdf_demo_facility');
        this.elementMenuPdfDemoFacilityBtn.addEventListener('click', this.clickPdfDemoFacilityBtn.bind(this));

        // 選択中の利用者の介護給付費明細書出力イベント
        this.elementCareBenefitStatementBtn = document.getElementById('dep_care_benefit_statement');
        this.elementCareBenefitStatementBtn.addEventListener('click', this.clickCareBenefitStatementBtn.bind(this));

        // 発行日入力モーダル関連要素
        this.elementIssueDateModal = document.getElementById('dep_table_s_item_form');
        this.elementCover = document.getElementById('dep_table_cover');
        this.validationDisplayArea = document.getElementById("validateErrorsDep");

        // エラーメッセージモーダル
        this.elementErrorModal =  document.getElementById('dep_table_error');

        // 発行日入力フォームのキャンセルボタンにイベントを付与
        document.getElementById('dep_form_cancel').addEventListener('click', this.hideIssueDateModal.bind(this));
        // 発行日入力フォームの確定ボタンにイベントを付与
        document.getElementById('dep_form_submit').addEventListener('click', this.clickIssueDateModalSubmit.bind(this));

        // エラーメッセージモーダルのOKボタンにイベントを付与
        document.getElementById('dep_error_close').addEventListener('click', this.hideErrorModal.bind(this));

        // 利用者全員分の利用料請求書出力イベント
        this.elementUsageFeeInvoiceBtn = document.getElementById('dep_usage_fee_invoice');
        this.elementUsageFeeInvoiceBtn.addEventListener('click', this.clickUsageFeeBtn.bind(this));

        // 選択中の利用者の利用料請求書出力イベント
        this.elementUsageFeeInvoiceIndividualBtn = document.getElementById('dep_usage_fee_invoice_individual');
        this.elementUsageFeeInvoiceIndividualBtn.addEventListener('click', this.clickUsageFeeBtn.bind(this));

        // 利用者全員分の利用料領収書出力イベント
        this.elementUsageFeeReceiptBtn = document.getElementById('dep_usage_fee_receipt');
        this.elementUsageFeeReceiptBtn.addEventListener('click', this.clickUsageFeeBtn.bind(this));

        // 選択中の利用者の利用料領収書出力イベント
        this.elementUsageFeeReceiptIndividualBtn = document.getElementById('dep_usage_fee_receipt_individual');
        this.elementUsageFeeReceiptIndividualBtn.addEventListener('click', this.clickUsageFeeBtn.bind(this));

        // 利用料請求書一覧メニュー表示切り替え
        this.switchDisplayUsageFeeInvoiceList();
        // 利用料請求書一覧PDF出力イベント
        this.elementUsageFeeInvoiceListPdfBtn = document.getElementById('dep_usage_fee_invoice_list_pdf');
        this.elementUsageFeeInvoiceListPdfBtn.addEventListener('click', this.clickUsageFeeBtn.bind(this));
        // 利用料請求書一覧CSV出力イベント
        this.elementUsageFeeInvoiceListCsvBtn = document.getElementById('dep_usage_fee_invoice_list_csv');
        this.elementUsageFeeInvoiceListCsvBtn.addEventListener('click', this.clickUsageFeeBtn.bind(this));

        // 伝送用国保連請求データ作成イベント
        this.elementMenuMakeInvoiceBtn = document.getElementById('dep_make_invoice');
        this.elementMenuMakeInvoiceBtn.addEventListener('click', this.clickMakeInvoiceBtn.bind(this));

        this.billing = new Billing();
        this.careBenefitStatement = new CareBenefitStatement(nationalHealth);
        this.pdfDemoAll = new PdfDemoAll();
        this.pdfDemoFacility = new PdfDemoFacility();
        this.usageFeeAll = new UsageFeeAll();
        this.makeInvoice = new MakeInvoice();

        this.syncTransmissionMode();

        // datepicker共通初期設定
        this.elementIssueDate = document.getElementById('issue_date');
        $.datepicker.setDefaults($.datepicker.regional["ja"]);
        $("#issue_date").datepicker({
            firstDay: 1,
            changeYear: true,
            yearRange: '2000:2099',
            onClose: function (dateText, inst) {
                let res = JapaneseCalendar.toJacal(dateText);
                $("#" + inst.id).prev().children('[id^="jaCal"]').text(res);
            }.bind(this)
        });

        this.elementIssueDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this));
    }

    // 伝送請求が有効の時のメニューを切り替える
    async syncTransmissionMode() {
        try {
            let result = await CustomAjax.post('/group_home/result_info/transmission_mode',
                { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                { facility_id: this.facilityId }
            );
            if (result.allow_transmission) {
                this.elementMenuMakeInvoiceBtn.style.display = '';
            } else {
                this.elementMenuBillingBtn.style.display = '';
            }
        } catch (e) {
            console.log(e);
        }
    }

    clickCareBenefitStatementBtn() {
        if (this.facilityUserId === null) { return; }
        this.careBenefitStatement.printPreview(this.facilityId, this.facilityUserId, this.year, this.month);
    }

    /**
     * 国保連請求データを出力メニュークリックイベント
     * facilityUsersはクライアント側で管理する。
     * 年月プルダウンを変更した際にはnotification()で最新状態のfacilityUsersを取得する。
     * @returns {Promise}
     */
    async clickBillingBtn() {
        if (this.facilityUsers.length > 0) {
            // 施設利用者のIDリストを作成する
            let facilityUserIds = this.facilityUsers.map(user => user.facility_user_id);
            await this.billing.submit(facilityUserIds, this.year, this.month);
        }
    }

    clickPdfDemoAllBtn() {
        if (this.facilityUsers.length > 0) {
            let facilityUserIds = this.facilityUsers.map(user => user.facility_user_id);
            this.pdfDemoAll.printPreview(facilityUserIds, this.facilityId, this.year, this.month);
        }
    }

    clickPdfDemoFacilityBtn() {
        if (this.facilityUsers.length > 0) {
            let facilityUserIds = this.facilityUsers.map(user => user.facility_user_id);
            this.pdfDemoFacility.printPreview(facilityUserIds, this.facilityId, this.year, this.month);
        }
    }

    async clickMakeInvoiceBtn() {
        if (this.facilityUsers.length > 0) {
            // 施設利用者のIDリストを作成する
            let facilityUserIds = this.facilityUsers.map(user => user.facility_user_id);
            // 請求登録処理の実行
            await this.makeInvoice.submit(facilityUserIds, this.year, this.month, this.facilityId);
        }
    }

    updateYearMonth(ym) {
        this.year = ym.year;
        this.month = ym.month;
    }

    /**
     * 施設利用者の情報をセットする
     * @param {Object} data {facilityUserID: Number, userName: String, facilityUsers: Array}
     * @returns {void}
     */
    setFacilityUserData(data) {
        this.facilityUserId = data.facilityUserID;
        this.facilityUsers = data.facilityUsers;
    }

    /**
     * 施設利用者のリストをセットする
     * @param {Array} users
     * @returns {void}
     */
    setFacilityUsers(users) {
        this.facilityUsers = users;
    }

    /**
     * 利用料関連書類出力ボタンのクリックイベント
     */
    async clickUsageFeeBtn(event) {
        let eventType = event.target.id;
        let facilityUserIds = [];

        if (eventType == 'dep_usage_fee_invoice_individual' || eventType == 'dep_usage_fee_receipt_individual') {
           if (this.facilityUserId === null) {
            // 利用者指定出力ボタン押下時に利用者未選択の場合はモーダルを表示しない
            return;
           }
           // 出力対象のユーザIDを設定する
           facilityUserIds = [this.facilityUserId];

        } else {
            // 出力対象のユーザIDをすべて設定する
            this.facilityUsers.forEach(e => {
                facilityUserIds.push(e.facility_user_id)
            });
        }

        // 出力対象ユーザから承認済みのユーザを取得する
        let ret = await CustomAjax.post(
            '/group_home/result_info/getApprovedUsers',
            { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            {
                facility_id: this.facilityId,
                year: this.year,
                month: this.month,
                facility_user_ids: facilityUserIds
            }
        );

        // 承認済みのユーザが取得できない場合、警告モーダルを表示し処理を終了する
        if (ret.facility_user_ids.length === 0) {
            // エラーメッセージモーダルを表示
            this.showErrorModal("国保連請求と保険外請求の承認がされていないため、出力できません");
            return;
        }

        if (eventType == 'dep_usage_fee_invoice_list_pdf' || eventType == 'dep_usage_fee_invoice_list_csv') {
            // 利用料請求書一覧の場合、ダイアログを表示しない
            // 発行日を初期化し、当日日付を設定する
            this.initializeIssueDateModal();
            document.getElementById("dep_table_s_item_form").dataset.eventType = eventType;
            // ファイル出力処理を実行する
            this.submitUsageFee();
        } else {
            // 利用料請求書一覧以外の場合、ダイアログを表示する
            this.showIssueDateModal(eventType);
            this.initializeIssueDateModal();
        }
    }

    /**
     * 発行日入力モーダルを表示する
     */
    async showIssueDateModal(eventType) {
        // カバーを表示
        this.elementCover.classList.remove('dep_table_hidden');
        // フォームを表示
        this.elementIssueDateModal.classList.remove('dep_table_hidden');
        // フォームの位置を中心に調整
        let cRect = this.elementCover.getBoundingClientRect();
        let fRect = this.elementIssueDateModal.getBoundingClientRect();
        this.elementIssueDateModal.style.left = Math.floor((cRect.width - fRect.width) / 2) + 'px';
        this.elementIssueDateModal.style.top = Math.floor((cRect.height - fRect.height) / 2) + 'px';

        // ラベル変更
        let label = '';
        if (eventType == 'dep_usage_fee_invoice_individual') {
            label = '選択中の利用者の利用料請求書を出力する';
        } else if (eventType == 'dep_usage_fee_invoice') {
            label = '利用者全員分の利用料請求書を出力する';
        } else if (eventType == 'dep_usage_fee_receipt_individual') {
            label = '選択中の利用者の利用料領収書を出力する';
        } else if (eventType == 'dep_usage_fee_receipt') {
            label = '利用者全員分の利用料領収書を出力する';
        }
        document.getElementById('dep_form_lbl').innerText = label;

        // eventTypeをモーダル内に埋め込み
        this.elementIssueDateModal.dataset.eventType = eventType;

        // バリデーションメッセージをクリア
        this.clearValidateDisplay();
    }

    /**
     * 発行日入力モーダルを非表示にする
     */
    hideIssueDateModal() {
        this.elementCover.classList.add('dep_table_hidden');
        this.elementIssueDateModal.classList.add('dep_table_hidden');
        this.selectedSItemRecord = null;
    }

    /**
     * 発行日入力モーダルのフォームを初期化
     */
    initializeIssueDateModal() {
        let date = new Date();
        let today = date.getFullYear()
            + '/' + ('00' + (date.getMonth()+1)).slice(-2)
            + '/' + ('00' + date.getDate()).slice(-2);

        // 今日の年月日を入れる
        this.elementIssueDate.value = today;

        // 和暦を入れる
        document.getElementById('jaCalIssueDate').innerText = JapaneseCalendar.toJacal(today);
    }

    /**
     * 発行日入力モーダルのバリデーションメッセージをクリア
     */
    clearValidateDisplay() {
        while (this.validationDisplayArea.lastChild) {
            this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
        }
    }

    /**
     * 発行日入力モーダルのsubmitボタン押下時のイベント
     */
    clickIssueDateModalSubmit() {
        // バリデーションメッセージをクリア
        this.clearValidateDisplay();

        // バリデーション
        if (!this.validateIssueDate(new Date(document.getElementById('issue_date').value))) {
            const LI = document.createElement('li');
            let validationDisplayUl = this.validationDisplayArea;
            let errorMessage = document.createTextNode('2000/04/01から2099/12/31までの日付を入力してください');
            LI.appendChild(errorMessage);
            validationDisplayUl.appendChild(LI);

            return;
        }

        // eventTypeごとにリクエスト
        let eventType = document.getElementById("dep_table_s_item_form").dataset.eventType;
        if (eventType == 'dep_usage_fee_invoice' || eventType == 'dep_usage_fee_receipt') {
            // 利用者全員分
            this.submitUsageFee();
        } else {
            // 選択中利用者
            this.submitUsageFeeSelect();
        }

        // モーダルを閉じる
        this.hideIssueDateModal();
    }

    /**
     * 選択中利用者の請求書/領収書をリクエストする
     */
    submitUsageFeeSelect() {
        // 利用者未選択の場合、処理を通さない
        if (this.facilityUserId === null) {
            return;
        }

        // 現在選択中の利用者をリストに格納
        let facilityUserIds = [];
        facilityUserIds.push(this.facilityUserId)

        let eventType = document.getElementById("dep_table_s_item_form").dataset.eventType;

        let issueDate = document.getElementById("issue_date").value;

        this.usageFeeAll.submit(this.facilityId, this.year, this.month, facilityUserIds, eventType, issueDate);
    }

    /**
     * 全利用者の請求書/領収書をリクエストする
     */
    async submitUsageFee() {
        let facilityUserIds = [];
        this.facilityUsers.forEach(e => {
            facilityUserIds.push(e.facility_user_id)
        });

        // 処理対象ユーザーから承認済みユーザーを取得する
        let approvedUserList = await CustomAjax.post(
            '/group_home/result_info/getApprovedUsers',
            { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            {
                facility_id: this.facilityId,
                year: this.year,
                month: this.month,
                facility_user_ids: facilityUserIds
            }
        );

        // 承認済みユーザーを配列に詰めなおす
        let checkedFacilityUserIds = [];
        approvedUserList.facility_user_ids.forEach(e => {
            checkedFacilityUserIds.push(e.facility_user_id)
        });

        if (checkedFacilityUserIds.length > 0) {
            let eventType = document.getElementById("dep_table_s_item_form").dataset.eventType;

            let issueDate = document.getElementById("issue_date").value;

            this.usageFeeAll.submit(this.facilityId, this.year, this.month, checkedFacilityUserIds, eventType, issueDate);
        }
    }

    /**
     * エラーメッセージモーダルを表示にする
     */
    showErrorModal(message) {
        this.elementErrorModal.classList.remove('dep_table_hidden');
        this.elementCover.classList.remove('dep_table_hidden');
        let cRect = this.elementCover.getBoundingClientRect();
        let fRect = this.elementErrorModal.getBoundingClientRect();
        this.elementErrorModal.style.left = Math.floor((cRect.width - fRect.width) / 2) + 'px';
        this.elementErrorModal.style.top = Math.floor((cRect.height - fRect.height) / 2) + 'px';
        $("#error_message").html(message);
    }

    /**
     * エラーメッセージモーダルを非表示にする
     */
     hideErrorModal() {
        this.elementCover.classList.add('dep_table_hidden');
        this.elementErrorModal.classList.add('dep_table_hidden');
    }

    /**
     * 発行日が2000/04/01から2099/12/31の間か判定する
     *
     * @param {Date} issueDate 発行日
     * @returns {bool}
     */
    validateIssueDate(issueDate) {
        let minDate = new Date('2000/04/01');
        let maxDate = new Date('2099/12/31');

        if (minDate <= issueDate && maxDate >= issueDate) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 利用料請求書一覧メニュー表示切り替え
     */
    async switchDisplayUsageFeeInvoiceList() {
        // 事業所IDを取得
        let facilityId = document.getElementById("facility_pulldown").value;

        $.ajaxSetup({
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")}
        });

        // サービス種別を取得
        await $.ajax({
            url: "facility_info/service_type/ajax",
            type: "POST",
            data: {
                facility_id: facilityId,
                postData2: 0,
            },
        }).done(function (data) {
            // 介護医療院の場合はメニューを非表示にする
            let serviceTypeAll = data.service_type_all;
            for (let i in serviceTypeAll) {
                if (serviceTypeAll[i].service_type_code == '55') {
                    $('#dep_usage_fee_invoice_list').css('display', 'none');
                    return;
                }
            }
        });
    }
}
