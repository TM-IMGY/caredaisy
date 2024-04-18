import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";
import SearchScope from "./search_scope.js";
import JapaneseCalendar from '../../lib/japanese_calendar.js';

const UNSENT = 0; // 未送信
const ACCEPTING = 2; // 受付中
const FORMAT_ERROR = 5; // 様式エラー


/**
 * 伝送請求タブ
 */
export default class Transmit {
    constructor(facilityID, listdata, facility_number) {
        this.elementID = "tm_contents_transmit";
        this.validationDisplayArea = document.getElementById(
            "transmitValidateErrors"
        );
        this.facility_id = facilityID;
        this.transmit_list = listdata;
        this.id = null;

        // 要素
        this.facility_number = document.getElementById(
            "transmit_facility_number"
        );
        this.facility_number.innerHTML = facility_number;
        this.listTBody = document.getElementById("transmit_list");
        this.historyTBody = document.getElementById("history_list");
        this.fromDate = document.getElementById("transmit_from_date");
        this.toDate = document.getElementById("transmit_to_date");

        let search_scope = new SearchScope();
        this.fromDate.value = search_scope.OneYearAgo.replace(/-/g,'/');
        this.toDate.value = search_scope.Today.replace(/-/g,'/');
        document.getElementById("jaCalSTFromDate").innerText = JapaneseCalendar.toJacal(this.fromDate.value);
        document.getElementById("jaCalSTToDate").innerText = JapaneseCalendar.toJacal(this.toDate.value);

        // ボタン要素
        this.setFilterBtn = document.getElementById("transmit_set_filter");
        this.setFilterBtn.addEventListener("click", this.setFilter.bind(this));

        if ('invoice' in listdata) {
            this.makeTable(listdata.invoice);
        }
        if ('history' in listdata) {
            this.makeTable(listdata.history, true);
        }

        // datepicker共通初期設定
        $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );
        $(".ymdatepicker").datepicker({
            changeYear: true,
            yearRange: '2000:2099',
            dateFormat: "yy/mm",
            monthNames: [ "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12" ],
            showOnlyMonths: true,
            onClose: function (dateText, inst) {
                let res = JapaneseCalendar.toJacal(dateText);
                $("#" + inst.id).prev().children('[id^="jaCal"]').text(res);
            }.bind(this)
        });

        this.fromDate.addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));
        this.toDate.addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));
    }

    async checkTransmitPeriod() {
        let res = await CustomAjax.get('/group_home/transmit_info/transmit/check_transmit_period');
        let data = await res.json();

        return data.result;
    }

    callbackFilter(history) {
        if (Array.isArray(history)) {
            this.makeTable(history, true);
        }
    }
    async setFilter() {
        this.clearValidateDisplay()
        let params = {};
        params.facility_id = this.facility_id;
        if (this.fromDate.value.length > 0) {
            params.from_date = this.fromDate.value + "/01";
        }
        if (this.toDate.value.length > 0) {
            params.to_date = this.toDate.value + "/01";
        }
        return await CustomAjax.send(
            "POST",
            "/group_home/transmit_info/transmit/filter",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackFilter",
            this
        );
    }
    callbackInvoiceList(listdata) {
        if ('invoice' in listdata) {
            this.makeTable(listdata.invoice);
        }
    }
    async getInvoicList() {
        let params = {};
        params.facility_id = this.facility_id;
        params.from_date = this.fromDate.value;
        params.to_date = this.toDate.value;
        return await CustomAjax.send(
            "POST",
            "/group_home/transmit_info/transmit/get_invoice",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackInvoiceList",
            this
        );
    }
    /**
     * クリックイベントリスナーを追加する
     * @param {number} id
     * @param {element} element
     * @param {Function} func
     */
    setClickEventListener(id, element, func) {
        element.addEventListener("click", (event) => {
            func(id);
        });
    }
    async getBill(id) {
        console.log({ getBill: id });
    }
    async getReceit(id) {
        console.log({ getReceit: id });
    }

    getDownloadCSV(csv) {
        if (!csv) {
            alert("ファイルがありません。");
            return false;
        }
        const a = document.createElement("a");
        document.body.appendChild(a);
        a.href = "/group_home/transmit_info/transmit/get_file?file_path=" + csv;
        a.click();
        a.remove();
    }
    callbackSentTransmit(ret) {
        if (ret.invoice) {
            this.getInvoicList();
        }
    }
    async transmit() {
        let params = {};
        let transmitData = this.transmit_list.invoice.find((x) => x.id == this.id);
        params.id = this.id;
        params.facilityNumber = this.facility_number.innerHTML;
        params.targetDate = transmitData.target_date;
        params.serviceDate = transmitData.service_date;

        return await CustomAjax.send(
            "POST",
            "/group_home/transmit_info/transmit/sent_invoice",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackSentTransmit",
            this
        );
    }

    async confirmationTransmit(id)
    {
        this.id = id;
        let checkTransmitPeriod =  await this.checkTransmitPeriod();

        if (checkTransmitPeriod) {
            let msg = '請求データを国保連に送信してよろしいですか？'
            this.showPopup(msg, this.transmit.bind(this));
        } else {
            let msg = '伝送請求受付期間外のため、送信できません。'
            this.showPopup(msg);
        }
    }

    callbackCancelTransmit(ret) {
        if (ret.update_result) {
            this.getInvoicList();
        }
    }

    async cancelTransmit() {
        let params = {};
        let cancelData = this.transmit_list.invoice.find((x) => x.id == this.id);
        params.id = this.id;
        params.facilityNumber = this.facility_number.innerHTML;
        params.targetDate = cancelData.target_date;
        params.serviceDate = cancelData.service_date;

        return await CustomAjax.send(
            "POST",
            "/group_home/transmit_info/transmit/cancel_transmit",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackCancelTransmit",
            this
        );
    }

    async confirmationCancelTransmit(id)
    {
        this.id = id;
        let checkTransmitPeriod =  await this.checkTransmitPeriod();

        if (checkTransmitPeriod) {
            let msg = '送信した請求データを取消してよろしいですか？<br>取消完了の処理までに30分程度かかります'
            this.showPopup(msg, this.cancelTransmit.bind(this));
        } else {
            let msg = '伝送請求受付期間外のため、取消できません。'
            this.showPopup(msg);
        }
    }

    callbackDeleteInvoice(ret) {
        if (ret.delete_result) {
            this.getInvoicList();
        }
    }

    async deleteInvoice() {
        let params = {};
        params.id = this.id;
        return await CustomAjax.send(
            "POST",
            "/group_home/transmit_info/transmit/delete_invoice",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackDeleteInvoice",
            this
        );
    }

    confirmationDeleteInvoice(id)
    {
        this.id = id;
        let msg = '請求データ一覧から請求データを削除してよろしいですか？'
        this.showPopup(msg, this.deleteInvoice.bind(this));
    }

    dateFormat(date) {
        let d = date ? date : "";
        let re = /(\d+)\-(\d+)\-(\d+)(.*)/;
        return d.replace(re, "$1年$2月$3日 $4");
    }
    dateFormatMonth(date) {
        let d = date ? date : "";
        let re = /(\d+)\-(\d+)\-(\d+)(.*)/;
        return d.replace(re, "$1年$2月");
    }
    /**
     * 該当事業所の伝送請求テーブル作成
     * @param {object} list
     * @return {Promise}
     */
    async makeTable(list, history = false) {
        let checkTransmitPeriod = await this.checkTransmitPeriod();
        if (!list) {
            return;
        }
        // リストを初期化
        if (!history) {
            this.listTBody.textContent = null;
        } else {
            this.historyTBody.textContent = null;
        }
        list.forEach(function (data) {
            let record = document.createElement("tr");
            record.setAttribute("data-list-id", data.id);
            let td = document.createElement("td");
            td.textContent = this.dateFormatMonth(data.target_date);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.dateFormatMonth(data.service_date);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = data.status.status_name;
            record.appendChild(td);
            td = document.createElement("td");
            td.classList.add("right_cell");
            td.textContent = data.facility_user_count;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.dateFormat(data.sent_at);
            record.appendChild(td);

            // td = document.createElement("td");
            // let button = document.createElement("button");
            // button.textContent = "請求書";
            // button.classList.add("table_btn_gray");
            // this.setClickEventListener(
            //     record.getAttribute("data-list-id"),
            //     button,
            //     this.getBill.bind(this)
            // );
            // td.appendChild(button);
            // record.appendChild(td);

            // td = document.createElement("td");
            // button = document.createElement("button");
            // button.textContent = "領収書";
            // button.classList.add("table_btn_gray");
            // this.setClickEventListener(
            //     record.getAttribute("data-list-id"),
            //     button,
            //     this.getReceit.bind(this)
            // );
            // td.appendChild(button);
            // record.appendChild(td);

            td = document.createElement("td");
            let button = document.createElement("button");
            button.textContent = "CSV出力";
            button.classList.add("table_btn_gray");
            this.setClickEventListener(
                data.csv,
                button,
                this.getDownloadCSV.bind(this)
            );
            td.appendChild(button);
            record.appendChild(td);

            if (!history) {
                td = document.createElement("td");
                button = document.createElement("button");
                button.textContent = "送信";
                button.classList.add("table_btn_yellow");
                button.classList.add("transmit");
                this.setClickEventListener(
                    record.getAttribute("data-list-id"),
                    button,
                    this.confirmationTransmit.bind(this)
                );
                if (data.status.id !== UNSENT || !checkTransmitPeriod) {
                    button.disabled = true;
                }
                td.appendChild(button);
                record.appendChild(td);

                td = document.createElement("td");
                button = document.createElement("button");
                button.textContent = "取消";
                button.classList.add("table_btn_gray");
                button.disabled = true;
                this.setClickEventListener(
                    record.getAttribute("data-list-id"),
                    button,
                    this.confirmationCancelTransmit.bind(this)
                );

                if ((data.status.id == ACCEPTING || data.status.id == FORMAT_ERROR) && checkTransmitPeriod) {
                    button.disabled = false;
                }
                td.appendChild(button);
                record.appendChild(td);

                td = document.createElement("td");
                button = document.createElement("button");
                button.textContent = "削除";
                button.classList.add("table_btn_gray");
                button.disabled = true;
                this.setClickEventListener(
                    record.getAttribute("data-list-id"),
                    button,
                    this.confirmationDeleteInvoice.bind(this)
                );

                if (data.status.id == UNSENT) {
                    button.disabled = false;
                }
                td.appendChild(button);
                record.appendChild(td);
            }

            if (!history) {
                this.listTBody.appendChild(record);
            } else {
                this.historyTBody.appendChild(record);
            }
        }, this);
    }
    /**
     * @param {bool} status 表示のブーリアン値
     */
    setActive(status) {
        //
    }

    validateDisplay(errorBody)
    {
        let createRow = (function(key, value)
        {
            let record = document.createElement('li');
            let validationDisplayArea = document.getElementById("transmitValidateErrors");
            record.textContent = value;
            validationDisplayArea.appendChild(record);
        });

        errorBody = JSON.parse(errorBody);
        let errorList = errorBody.errors;
        Object.keys(errorList).map(key =>
            createRow(key, errorList[key])
        );
    }

    clearValidateDisplay()
    {
        while(this.validationDisplayArea.lastChild) {
            this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
        }
    }

    /**
     * ポップアップを表示する
     * @param {String} msg
     * @param {func} callBack
     * @returns {void}
     */
    showPopup(msg, callBack = null)
    {
        let elementPopup = document.createElement('div');
        elementPopup.id = 'overflow_transmit';

        let elementPopupContents = document.createElement('div');
        elementPopupContents.classList.add('conf');

        let elementPopupMessage = document.createElement('p');
        // このページ特有のmargin設定のためのclass
        elementPopupMessage.classList.add('popup_p_element');
        elementPopupMessage.innerHTML = msg;

        let elementBtnFrame = document.createElement('div');
        elementBtnFrame.classList.add('transmit-btns');
        elementPopup.appendChild(elementPopupContents);
        elementPopupContents.appendChild(elementPopupMessage);
        elementPopupContents.appendChild(elementBtnFrame);
        if (callBack) {
            let elementBtnYes = this.createBtn(elementPopup, 'popup_yes', 'OK', callBack);
            let elementBtnCancel = this.createBtn(elementPopup,'popup_cancel', 'キャンセル');
            elementBtnFrame.appendChild(elementBtnYes);
            elementBtnFrame.appendChild(elementBtnCancel);
        } else {
            let elementBtnYes = this.createBtn(elementPopup, 'popup_yes', 'OK',this.getInvoicList.bind(this));
            elementBtnFrame.appendChild(elementBtnYes);
        }

        document.body.appendChild(elementPopup);
    }

    /**
     * ボタンを作成する
     * @param {object} elementPopup
     * @param {string} className
     * @param {string} btnText
     * @param {func} callBack
     * @returns
     */
    createBtn(elementPopup, className, btnText, callBack = null)
    {
        let elementBtn = document.createElement('button');
        elementBtn.classList.add(className);
        elementBtn.textContent = btnText;
        if (callBack) {
            elementBtn.addEventListener('click', callBack);
        }
        elementBtn.addEventListener('click', () => {elementPopup.parentNode.removeChild(elementPopup);});
        return elementBtn;
    }
}
