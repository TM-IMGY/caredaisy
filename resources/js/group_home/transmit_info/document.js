import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";
import SearchScope from "./search_scope.js";
import JapaneseCalendar from '../../lib/japanese_calendar.js';

/**
 * 通知文書タブ
 */
export default class Document {
    constructor(facilityID, listdata, facility_number) {
        this.elementID = "tm_contents_document";
        this.validationDisplayArea = document.getElementById(
            "documentValidateErrors"
        );
        this.facility_id = facilityID;
        this.document_list = listdata;

        // 要素
        this.facility_number = document.getElementById(
            "transmit_facility_number"
        );
        this.facility_number.innerHTML = facility_number;
        this.listTBody = document.getElementById("document_list");
        this.fromDate = document.getElementById("document_from_date");
        this.toDate = document.getElementById("document_to_date");

        let search_scope = new SearchScope();
        this.fromDate.value = search_scope.OneYearAgo.replace(/-/g,'/');
        this.toDate.value = search_scope.Today.replace(/-/g,'/');
        document.getElementById("jaCalDocFromDate").innerText = JapaneseCalendar.toJacal(this.fromDate.value);
        document.getElementById("jaCalDocToDate").innerText = JapaneseCalendar.toJacal(this.toDate.value);

        // モーダル画面要素
        this.modalCloseBtn = document.getElementById("modal_close");
        this.modalPublishedDate = document.getElementById("modal_published_date");
        this.modalDocumentsTitle = document.getElementById("modal_documents_title");
        this.modalDocumentsContent = document.getElementById("modal_documents_content");
        this.modalFileContainer = document.getElementById("modal_file_container");

        // モーダル画面 閉じるボタンイベント
        this.modalCloseBtn.addEventListener("click", function () {
            let modalContainer = document.getElementById('modal_container');
            modalContainer.classList.remove('active');
        });

        // ボタン要素
        this.setFilterBtn = document.getElementById("document_set_filter");
        this.setFilterBtn.addEventListener("click", this.setFilter.bind(this));

        if (Array.isArray(listdata)) {
            this.makeTable(listdata);
        }

        // datepicker共通初期設定
        $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );
        $(".datepicker").datepicker({
            firstDay: 1,
            changeYear: true,
            yearRange: '2000:2099',
            minDate: new Date(2000, 4 - 1, 1),
            maxDate: new Date(2099, 12 - 1, 31),
            onClose: function (dateText, inst) {
                let res = JapaneseCalendar.toJacal(dateText);
                $("#" + inst.id).prev().children('[id^="jaCal"]').text(res);
            }.bind(this)
        });

        this.fromDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this));
        this.toDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this));

        // 絞り込み期間初期設定
        let dates = this.setInitialDate();
        this.fromDate.value = dates["one_year_ago"];
        this.toDate.value = dates["end_of_month"];

        this.setFilter();
    }
    callbackFilter(history) {
        if (Array.isArray(history)) {
            this.makeTable(history, true);
        }
    }

    setInitialDate() {
        let date = new Date();
        let dates = [];

        // 当月から1年前の月初の日付を取得
        let oneYearAgo = new Date(date.getFullYear()-1, date.getMonth(), 1);
        dates["one_year_ago"] = this.setDateFormat(oneYearAgo);

        // 当月末の日付を取得
        let endOfMonth = new Date(date.getFullYear(), date.getMonth()+1, 0);
        dates["end_of_month"]  = this.setDateFormat(endOfMonth);

        return dates;
    }

    setDateFormat(param) {
        let year = param.getFullYear();
        let month = ("00" + (param.getMonth()+1)).slice(-2);
        let date = ("00" + (param.getDate())).slice(-2);

        return year + "/" + month + "/" + date;
    }

    async setFilter() {
        this.clearValidateDisplay()
        let params = {};
        params.facility_id = this.facility_id;
        if (this.fromDate.value.length > 0) {
            params.from_date = this.fromDate.value;
        }
        if (this.toDate.value.length > 0) {
            params.to_date = this.toDate.value;
        }

        return await CustomAjax.send(
            "POST",
            "/group_home/transmit_info/transmit/get_document",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackFilter",
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
    getBill(id) {
        console.log({ getBill: id });
    }
    getReceit(id) {
        console.log({ getReceit: id });
    }

    dateFormat(date) {
        let d = date ? date : "";
        let re = /(\d+)\-(\d+)\-(\d+)(.*)/;
        return d.replace(re, "$1年$2月$3日 $4");
    }
    dateFormatDate(date) {
        let d = date ? date : "";
        let re = /(\d+)\-(\d+)\-(\d+)(.*)/;
        return d.replace(re, "$1年$2月$3日");
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
        if (!list) {
            return;
        }
        // リストを初期化
        this.listTBody.textContent = null;
        list.forEach(function (data) {
            let record = document.createElement("tr");
            record.setAttribute("data-list-id", data.id);
            let td = document.createElement("td");
            td.textContent = this.dateFormatDate(data.published_at);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = data.document_type;
            record.appendChild(td);
            td = document.createElement("td");
            let anker = document.createElement("a");

            if(data.download_file){
                // 通知文書の場合CSVパース　お知らせの場合ファイルダウンロード
                // HACK:比較対象に文字列を指定しているので今後リファクタ予定
                if(data.document_type == "通知文書"){
                    anker.classList.add("dl_icon");
                    anker.setAttribute('target', '_blank');

                    anker.href =
                        "/group_home/transmit_info/transmit/get_retrundocument?id=" + data.id;
                }
            }else{
                // お知らせ
                // NOTE:お知らせのリンク装飾を有効にするため
                anker.classList.add("link_icon");
                anker.href = "#";

                // 各お知らせに一覧タイトル押下時のイベントを付与
                // NOTE:お知らせ毎に各項目内容を変えるため
                anker.addEventListener("click", (event) => {
                this.createModalDetails(data);
                    let modalContainer = document.getElementById('modal_container');
                    modalContainer.classList.add('active');
                });
            }

            anker.textContent = data.title;
            anker.addEventListener("click", (event) => {
                //thisを拘束するため
                const filter = ()=>{ this.setFilter()};
                //アンカーをキックして処理を実施後に画面を更新
                setTimeout(function(){filter()},2000);
            });

            td.appendChild(anker);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.dateFormatMonth(data.target_date);
            record.appendChild(td);
            td = document.createElement("td");
            if(data.facility_number.includes("*")){
                td.textContent = "━";
                td.style.textAlign = "center";
            }else{
                td.textContent = this.dateFormat(data.checked_at);
            }
            record.appendChild(td);
            this.listTBody.appendChild(record);
        }, this);
    }

    async createModalDetails(data) {
        let res = await CustomAjax.get(
            "/group_home/transmit_info/transmit/get_retrundocumentlist?id=" + data.id,
            {"X-CSRF-TOKEN": CSRF_TOKEN },
        );

        let modalDatas = await res.json();

        this.modalPublishedDate.textContent = this.dateFormatDate(modalDatas["published_at"]);
        this.modalDocumentsTitle.textContent = modalDatas["title"];
        this.modalDocumentsContent.innerHTML = modalDatas["content"];

        let attachments = modalDatas["attachments"];
        this.modalFileContainer.innerHTML = "";

        for(let i = 0; i < attachments.length; i++) {
            let a = document.createElement("a");
            a.textContent = attachments[i]["documentName"];
            a.setAttribute("class", "dl_icon");
            a.href = attachments[i]["downloadUrl"];
            a.target = "_blank";
            this.modalFileContainer.appendChild(a);
        }
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
            let validationDisplayArea = document.getElementById("documentValidateErrors");
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
}
