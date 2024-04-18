import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js'

export default class uninsured{
    constructor(year,month,facilityID)
    {
        this.LEGAL_NUMBERS = [12, 25, 15, 54, 51];
        this.TABLE_CELL_UN_COL3 = "caredaisy_table_cell un_col3";
        this.ITEM_NAME_HEADER = 'head caredaisy_table_cell un_col3 table_header';

        this.elementID = 'tm_contents_3';
        this.element = document.getElementById(this.elementID);
        this.element.querySelector('.facility_info_head').style.display = 'none'
        this.element.querySelector('.result_info_head').style.display = 'block'

        this.elementYm = document.getElementById('un_table_ym');

        this.elementSItemPulldown = document.getElementById('un_table_s_item_pulldown');
        this.elementServiceItemForm = document.getElementById('un_table_s_item_form');
        this.elementCover = document.getElementById('un_table_cover');
        this.elementSubName = document.getElementById('item_sub_name');
        this.elementSubUnit = document.getElementById('item_sub_unit');
        this.elementSubDate = document.getElementById('item_sub_date');

        this.calendarDateArea = document.getElementById("day_area");
        this.calendarDOWArea = document.getElementById("DOW_area");

        this.itemFormRegister = document.getElementById("item_form_register");
        this.addAgreement = document.getElementById("add_agreement");
        this.validationDisplayArea = document.getElementById("validateErrorsUninsuredItem");
        this.originalRow = document.getElementById("original_row");

        this.calendar = document.getElementById("calendar");
        this.selectList = document.getElementById("select_list");

        this.facilityID = facilityID;
        this.facilityUserID = null;
        this.isActive = false;
        this.notificationList = [];
        this.year = year;
        this.month = month;
        this.userInfoStartDate = null;
        this.userInfoEndDate = null;
        this.uninsuredRequestId = null;

        this.itemList = [];
        this.sortList = [];
        this.stayoutList = [];

        this.selectedSItemRecord = null;
        this.addedItemList = [];
        this.uninsuredTotal = document.getElementById("un_table_uninsured_total");
        this.uninsuredSelfTotal = document.getElementById("un_table_uninsured_self_total");
        this.publicPaymentArea = document.getElementById("un_table_public_payment_area");
        this.publicPayment = document.getElementById("un_table_public_payment");

        // プラスボタンにイベントを付与
        document.getElementById('un_table_plus_btn').addEventListener('click',this.unClickPlusBtn.bind(this));
        // サービスコードフォームのキャンセルボタンにイベントを付与
        document.getElementById('item_form_cancel').addEventListener('click',this.hideServiceItemForm.bind(this));
        // サービスコードフォームの登録ボタンにイベントを付与
        document.getElementById('item_form_register').addEventListener('click',this.itemFormRegisterClick.bind(this));
        // 承認するボタンにイベントを付与
        if (this.addAgreement !== null){
            this.addAgreement.addEventListener('click',this.addAgreementClick.bind(this));
        }

        this.agreement = false;

        $("#calendar").sortable({
            cancel: "#day_area, #DOW_area, :input, button",
            items: " > tr",
            stop: function() {
                this.sortEnd();
            }.bind(this)
        });

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

        document.getElementById('item_sub_date').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this));

        // モーダル内のラジオボタンによる表示制御
        document.getElementById('select_list').addEventListener('input',this.entryChangeRadio.bind(this));
        document.getElementById('select_add').addEventListener('input',this.entryChangeRadio.bind(this));
    }

    /**
     * プラスボタンのクリックイベント
     * @returns {void}
     */
    async unClickPlusBtn() {
        // 活性化しているかつ、パラメーターがある場合
        if( this.hasAllParam() && this.isActive){
            this.showServiceItemForm();
            this.popupFormClear();
            this.entryChangeRadio();
        }
    }

    /**
   * サービスコードフォーム表示
   * @returns {void}
   */
    async showServiceItemForm() {
    // カバーを表示
    this.elementCover.classList.remove('un_table_hidden');
    // フォームを表示
    this.elementServiceItemForm.classList.remove('un_table_hidden');
    // フォームの位置を中心に調整
    let cRect = this.elementCover.getBoundingClientRect();
    let fRect = this.elementServiceItemForm.getBoundingClientRect();
    this.elementServiceItemForm.style.left = Math.floor((cRect.width-fRect.width)/2)+'px';
    this.elementServiceItemForm.style.top = Math.floor((cRect.height-fRect.height)/2)+'px';

    this.clearValidateDisplay();
    }

    hideServiceItemForm(){
    this.elementCover.classList.add('un_table_hidden');
    this.elementServiceItemForm.classList.add('un_table_hidden');
    this.selectedSItemRecord = null;
    }

    /**
     * ポップアップ内のフォームを初期化
     */
    popupFormClear(){
    let itemNameClear = this.elementSubName;
    let itemUnitClear = this.elementSubUnit;
    let itemListPull = this.elementSItemPulldown;
    let selectListRadio = this.selectList;

    itemListPull.options[0].selected = true;
    itemNameClear.value = "";
    itemUnitClear.value = "";
    selectListRadio.checked = true;
    }

    /**
     * モーダル内のラジオボタンによる表示制御
     */
    entryChangeRadio(){
        var radio = document.getElementsByName('modal_radio')
        if(radio[0].checked) {
            document.getElementById('add_item').style.display = "none";
            // 非表示時のカレンダー入力値
            let itemSubDate = this.elementSubDate;
            itemSubDate.value = "";
            this.elementSubName.value = '';
            this.elementSubUnit.value = '';
        }else if(radio[1].checked) {
            let itemListPull = this.elementSItemPulldown;
            itemListPull.options[0].selected = true;
            document.getElementById('add_item').style.display = "";
            // 表示時のカレンダー入力値
            $("#item_sub_date").datepicker("setDate", this.year + "/" + this.month + "/" + "01");
            let selectMonth = document.getElementById("item_sub_date").value;
            document.getElementById("jaCalPubExpenditureDate").innerText = JapaneseCalendar.toJacal(selectMonth);
        }
    }

    async init(year,month)
    {
        await this.removeCalendarHeaders();
        this.setYearMonth(year,month);
    }

    setYearMonth(year,month){
        this.year = year;
        this.month = month;
        this.elementYm.textContent = year+'年'+(('0'+month).slice(-2))+'月';
    }

    clear(){
        this.addedItemList = [];
        this.stayoutList = [];

        // 保険外請求品目追加プルダウン
        let options = document.querySelectorAll('#un_table_s_item_pulldown > option');
        for (let [key, value] of Object.entries(options)) {
             value.remove();
        }
        // 画面の承認済み状態を初期化する
        this.callbackCheckAgreement({approval_flag:false});

        this.uninsuredTotal.textContent = "";
        this.publicPayment.textContent = "";
        this.publicPaymentArea.style.display = "flex";
        this.uninsuredSelfTotal.textContent = "";

    }

    async setActive(isActive)
    {
        await this.notification();
        if(isActive && this.hasAllParam()){
            await this.syncWithServer();
        }else{
            this.removeCalendarHeaders();
            this.clear();
            this.NoSelectCalendarHeaders(this.year,this.month);
        }
        this.isActive = isActive;
    }

    async setParam(param)
    {
        await this.removeCalendarHeaders();

        this.facilityUserID = 'facilityUserID' in param ? param.facilityUserID : this.facilityUserID;

        if('year' in param && 'month' in param){
            this.setYearMonth(param.year,param.month);
        }

        // setActiveとsetParamの処理がかぶっているので注意
        // 活性化している場合
        if(this.isActive && this.hasAllParam()){
            await this.syncWithServer();
        }else{
            this.clear();
            this.NoSelectCalendarHeaders(this.year,this.month);
        }
    }

    hasAllParam()
    {
        return ![this.facilityUserID,this.year,this.month].includes(null);
    }

    addNotification(callBack){
        this.notificationList.push(callBack);
    }

    async syncWithServer()
    {
        this.addedItemList = [];
        this.uninsuredTotal.textContent = "";
        this.stayoutList = [];
        await this.getUserInfo();
        await this.initItemBox();
        await this.checkAgreement();
        await this.reCalc();
        await this.syncUserInfor();
    }

    async checkAgreement()
    {
        if(this.facilityUserID === null){
            // 画面の承認済み状態を初期化する
            this.callbackCheckAgreement({approval_flag:false});
            return false;
        } else {
            return CustomAjax.send(
                'GET',
                '/group_home/result_info/uninsured/check_agreement?facility_user_id=' + this.facilityUserID + '&facility_id=' + this.facilityID + '&year=' + this.year + '&month=' + this.month,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                'callbackCheckAgreement',
                this
            );
        }
    }

    callbackCheckAgreement(result) {
        this.agreement = result.approval_flag;
        this.addAgreement.textContent = this.agreement == false ? "承認する" : "承認済";
    }

    async callbackInitItemList(result)
    {
        this.itemList = [];

        // 「選択してください」Optionを用意
        let opt = document.createElement("option");
        opt.setAttribute("value", "");
        opt.textContent = "選択してください";

        await this.elementSItemPulldown.appendChild(opt);

        result.forEach(row => this.addItemList(row));

        this.getUserRecords();
    }

    getUserRecords()
    {
        let trList = document.querySelectorAll('#calendar tr[id^="row_"]');
        for (let [key, value] of Object.entries(trList)) {
            value.remove();
        }

        if(this.facilityUserID === null){
            return false;
        } else {
            return CustomAjax.send(
                'GET',
                '/group_home/result_info/uninsured/list?facility_user_id=' + this.facilityUserID + '&year=' + this.year + '&month=' + this.month,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                'callbackUninsuredList',
                this
            );
        }
    }

    async callbackUninsuredList(result)
    {
        let i = 1;
        for (let [key, value] of Object.entries(result)) {
            let item = value.item;
            let details = value.details;
            this.createItemRow(item, i, details, false);
            this.sortList[i] = item.uninsured_request_id;
            this.addedItemList.push(item.uninsured_history_id);
            i = i + 1;
        }
    }

    addItemList(row)
    {
        let opt = document.createElement("option");
        opt.setAttribute("value", row.id);
        opt.textContent = row.item;

        let item = [];
        for (let [key, value] of Object.entries(row)) {
            item[key] = value;
        }
        if(Object.keys(item).length != 0){
            if(item.unit_cost == null){
                item.unit_cost = 0;
            }
            item.uninsured_request_id = null;
            item.uninsured_item_history_id = item.id;
            item.name = item.item;
            this.itemList[row.id] = item;
        }
        this.elementSItemPulldown.appendChild(opt);
    }

    async initItemBox()
    {
        // 保険外請求品目追加プルダウン
        let options = document.querySelectorAll('#un_table_s_item_pulldown > option');
        for (let [key, value] of Object.entries(options)) {
            value.remove();
        }
        let target_year = this.year;
        let target_month = this.month;

        if(this.facilityUserID === null){
            return false;
        } else {
            return CustomAjax.send(
                'GET',
                '/group_home/result_info/uninsured/item_list?facility_user_id=' + this.facilityUserID + '&target_month=' + target_month + '&target_year=' + target_year,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                'callbackInitItemList',
                this
            );
        }
    }

    removeCalendarHeaders()
    {
        let trList = document.querySelectorAll('#calendar tr[id^="row_"]');
        for (let [key, value] of Object.entries(trList)) {
            value.remove();
        }
        for (let [key, value] of Object.entries( document.getElementsByClassName("header_date"))) {
            value.remove();
        };
        for (let [key, value] of Object.entries( document.getElementsByClassName("header_dow"))) {
            value.remove();
        };
    }

    async getUserInfo()
    {
        await this.removeCalendarHeaders();

        if(this.facilityUserID === null){
            return false;
        } else {
            return CustomAjax.send(
                'GET',
                '/group_home/result_info/uninsured/user_info?facility_user_id=' + this.facilityUserID + '&year=' + this.year + '&month=' + this.month,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                "callbackUserInfo",
                this
            );
        }
    }

    async callbackUserInfo (json) {
        if(json !== null){
            json.forEach(result => this.addRecord(result));
        }
    };

    NoSelectCalendarHeaders(year, month){
        let dateCnt = new Date(year, month, 0).getDate();
        let dowList = [ '日', '月', '火', '水', '木', '金', '土' ];
        // 日付セルを作成して追加する。
        for (let i = 0; i < dateCnt; i++) {
            let date = document.createElement('td');
            date.textContent = i+1;
            date.classList.add("caredaisy_table_cell");
            date.classList.add("table_header");
            date.classList.add("header_date");
            this.calendarDateArea.appendChild(date);

            let dow = document.createElement('td');
            let dow_date = new Date(year, month-1, i+1);
            let dow_day = dow_date.getDay();
            dow.textContent = dowList[dow_day];
            dow.classList.add("caredaisy_table_cell");
            dow.classList.add("header_dow");
            if(dow_day == 0){
                dow.classList.add("sunday_color");
            } else if (dow_day == 6){
                dow.classList.add("saturday_color");
            }
            this.calendarDOWArea.appendChild(dow);
        }
    }

    addRecord(result)
    {
        let date = document.createElement('td');
        date.textContent = result.day;
        date.classList.add("caredaisy_table_cell");
        date.classList.add("table_header");
        date.classList.add("header_date");
        if(result.is_out_dates == true){
            date.classList.add("out_date_grey");
            this.stayoutList.push(result.day);
        }

        let dow = document.createElement('td');
        dow.textContent = result.DOW;
        dow.classList.add("caredaisy_table_cell");
        dow.classList.add("header_dow");
        if(result.DOW == "土") {
            dow.classList.add("saturday_color");
        }
        if(result.DOW == "日") {
            dow.classList.add("sunday_color");
        }

        this.calendarDateArea.appendChild(date);
        this.calendarDOWArea.appendChild(dow);
    }

    addAgreementClick()
    {
        // 活性化していない場合、またはパラメーターを持たない場合は何もしない。
        if(!(this.hasAllParam() && this.isActive)){
            event.preventDefault();
            return;
        }

        let bool = false;
        let message = "";
        if(this.agreement == true){
            message = "当データは承認済です\n未承認にしますか?";
            bool = false;
        }else{
            message = "当データは未承認です\n承認しますか?";
            bool = true;
        }

        if(confirm(message)){
            this.changeAgreement(bool);
        }
    }

    async changeAgreement(bool)
    {
        if(this.agreement == bool){
            return;
        }

        return CustomAjax.send(
            'POST',
            '/group_home/result_info/uninsured/agreement',
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            {
                facility_user_id: this.facilityUserID,
                facility_id: this.facilityID,
                year: this.year,
                month: this.month,
                flag: bool
            },
            'callbackCheckAgreement',
            this
        );
    }

    async itemFormRegisterClick()
    {
        this.clearValidateDisplay();
        this.changeAgreement(false);

        let itemName = this.elementSubName.value;
        let itemUnit = Number.parseInt(this.elementSubUnit.value);
        let itemDate = this.elementSubDate.value;
        let countList = [];
        let index = this.elementSItemPulldown.selectedIndex;
        let options = this.elementSItemPulldown.options;
        let item = [];
        let query = '';

        // 請求対象の年月および入力された年月の取得
        let targetYearMonth = this.getDateFormatYearMonth();
        let itemDateYearMonth = this.getDateFormatYearMonth(itemDate);

        // 数値型でないなら0入れておく
        // グローバル関数のisFiniteは挙動が変わるのでNumberClassのものを使う
        // 参考URL:  https://qiita.com/taku-0728/items/329e0bee1c49b7ce7cd1
        if (Number.isFinite(itemUnit) == false) {
            itemUnit = 0;
        }

        // 全角だった場合のバリデーション
        if (itemDate.match(/^[^\x01-\x7E\uFF61-\uFF9F]+$/)) {
            const ERROR_MESSAGE_DATE = '入力日は半角で入力してください';

            this.simpleValidateDisplay(ERROR_MESSAGE_DATE);

            return;
        }

        // 以下、jsで簡易的なバリデーションを複数実装
        if (targetYearMonth !== itemDateYearMonth) {
            // 現状、入力日を元に実績を反映するロジックの過程全般がjs側で行われているため
            // いったんjs側でチェック。将来的には他画面同様Requestでチェック予定。

            // 保険外請求は仕様上、請求対象年月以外の実績を反映できないため
            // それ以外の年月を登録不可にする
            const ERROR_MESSAGE_DATE = '該当月の日付を入力してください';

            this.simpleValidateDisplay(ERROR_MESSAGE_DATE);

            return;
        }

        // 保険外費用に登録されていない品目の新規追加
        if (options[index].value == '') {
            if (!itemName) {
                this.simpleValidateDisplay('品目名は必須項目です。');
                return;
            }

            let uninsuredRequest = await CustomAjax.post(
                '/group_home/result_info/uninsured/save_item',
                {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
                {facility_user_id: this.facilityUserID, item_name: itemName, year: this.year, month: this.month}
            );

            // 新規品目追加の場合、親情報はダミーとなる
            item = {
                'uninsured_item_history_id': null,
                'uninsured_request_id': uninsuredRequest.id,
                'name': uninsuredRequest.name,
                'unit_cost': itemUnit
            };

            let dateString = itemDate;
            countList[dateString] = {'quantity': 1}
        } else if (options[index].value && itemName || itemUnit || itemDate) {
            const ERROR_MESSAGE_ITEM_NAME = '品目リストか品目名を設定して下さい。';

            this.simpleValidateDisplay(ERROR_MESSAGE_ITEM_NAME);

            return;
        } else {
            item = this.itemList[options[index].value];

            if(this.addedItemList.includes(item.id)){
                const ERROR_MESSAGE_DUPLICATION = 'この項目は既に追加済です';

                this.simpleValidateDisplay(ERROR_MESSAGE_DUPLICATION);

                return;
            }
        }

        let sort = document.getElementById("calendar").rows.length - 1;

        let querys = {
            'facility_user_id': this.facilityUserID,
            'unit_cost': item.unit_cost,
            'month': this.year + '-' + this.month + '-01',
            'sort': sort,
            'uninsured_item_history_id': item.uninsured_item_history_id,
            'uninsured_request_id': item.uninsured_request_id
        };

        for (let [key, value] of Object.entries(querys)) {
            query += key;
            query += '=';
            query += encodeURI(value);
            query += '&';
        }

        await CustomAjax.send(
            'GET',
            '/group_home/result_info/uninsured/save_row?' + query,
            {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
            [],
            'callbackSaveRow',
            this
        );

        // プルダウンから品目選択した場合は「uninsured_request_id」がnullなので登録idを設定する
        if (item.uninsured_request_id == null) {
            item.uninsured_request_id = this.uninsuredRequestId;
        }

        await this.addedItemList.push(item.uninsured_item_history_id);
        let uninsuredItemHistoryId = options[index].value;
        this.createItemRow(item, sort, countList, true);
        this.hideServiceItemForm();
    }

    createItemRow(item, sort, countList, isAdd)
    {
        let cp = this.originalRow.cloneNode();
        cp.removeAttribute("id");
        cp.setAttribute("id", "row_" + item.uninsured_request_id);

        // 項目名などのエリアコピー
        for (let [key, value] of Object.entries(this.originalRow.childNodes)) {
            let cloneDom = value.cloneNode(true);
            if(cloneDom.length == undefined){
                if(key == 1){
                    // ゴミ箱
                    cloneDom.setAttribute("data-sort-index", sort);
                    cloneDom.addEventListener("click", this.deleteRow.bind(this));
                    cloneDom.classList.add("un_col2");
                }else if(key == 3) {
                    cloneDom.textContent = item.name;
                    cloneDom.classList.add("un_col3");
                }else if(key == 5) {
                    cloneDom.classList.add("un_col4");
                    let param = document.createElement('input');

                    param.setAttribute("type", 'number');
                    param.setAttribute("name", "unit_cost");
                    param.setAttribute("class", "un_unitcost_input");

                    if(item.unit_cost) {
                        param.value = item.unit_cost;
                    }else{
                        param.value = 0;
                    }

                    param.addEventListener("change", this.changeRow.bind(this));
                    param.setAttribute("data-sort-index", sort);
                    cloneDom.appendChild(param);
                }else if(key == 7) {
                    cloneDom.classList.add("un_col5");
                    cloneDom.setAttribute("data-sort-index", sort);
                    cloneDom.setAttribute("id", "summary");
                    cloneDom.textContent = "0";
                }else if(key == 9) {
                    cloneDom.classList.add("un_col6");
                    cloneDom.setAttribute("data-sort-index", sort);
                    cloneDom.setAttribute("id", "total");
                    cloneDom.textContent = "0";
                }
                cp.appendChild(cloneDom);
            }
        }


            let date = new Date();
            date.setFullYear(this.year);
            date.setMonth(this.month-1); // JSのDate:Monthは0始まり
            date.setDate(1);

        // 個数欄コピー
        for (let [key, value] of Object.entries(this.calendarDOWArea.childNodes)) {
            let cloneDom = value.cloneNode(true);
            cloneDom.textContent = "";

            if(!cloneDom.hasChildNodes() && cloneDom.length != 0){
                let param = document.createElement('input');
                let dateStringOfJaCal = this.year + "/" + this.month.toString().padStart(2, '0') + "/" + date.getDate().toString().padStart(2, '0');
                let dateString = this.year + "-" + this.month.toString().padStart(2, '0') + "-" + date.getDate().toString().padStart(2, '0');

                let cnt = 0;
                if(countList.length == 0 && ((item.set_one == 1 && this.stayoutList.indexOf(date.getDate()) == -1) || (item.unit == 4 && key == 1))) {
                    //利用者の入居日と退去日の期間に１を設定
                    if (this.userInfoStartDate <= date){
                        cnt = 1;
                        if (this.userInfoEndDate &&  this.userInfoEndDate < date){
                            cnt = 0;
                        }
                    }
                }else{
                    if ((dateString in countList) == true) {
                        cnt = countList[dateString]["quantity"];
                    } else if ((dateStringOfJaCal in countList) == true) {
                        cnt = countList[dateStringOfJaCal]["quantity"];
                    }
                }

                param.setAttribute("data-uninsured_item_history_id", item.id);
                param.setAttribute("data-date", dateStringOfJaCal);
                param.setAttribute("type", 'number');
                param.setAttribute("name", "date_of_use");
                param.value = cnt;
                param.setAttribute("min", 0);
                param.setAttribute("data-sort-index", sort);
                param.classList.add("un_number_input");
                param.setAttribute("id", "data-" + item.id + "-" + dateStringOfJaCal);

                param.addEventListener("change", this.changeValue.bind(this));
                cloneDom.appendChild(param);

                if(isAdd == true){
                    let event = new Event("change");
                    param.dispatchEvent(event);
                }

                date.setDate(date.getDate() + 1);
            }
            cp.appendChild(cloneDom);
        }
        this.calendar.appendChild(cp);

        this.reCalc();
    }

    async deleteRow(event)
    {
        // 選択したレコードのIDを取得する。
        let selectedRowId = document.getElementById(event.composedPath()[2].id);
        // レコード内の品目名を取得する。
        let item = selectedRowId.getElementsByClassName(this.TABLE_CELL_UN_COL3)[0].innerText;
        // テーブルの枠をクリックしていた場合(画像要素をクリックしていない場合)、何もせず処理を終える。
        if(item === document.getElementsByClassName(this.ITEM_NAME_HEADER)[0].innerText){
            return;
        }
        if(confirm(`${item}を削除してよろしいですか？`)){
            this.changeAgreement(false);
            let elm = event.target.parentElement;

            let value = elm.value;
            let sort = elm.getAttribute("data-sort-index");
            let month = this.year + "-" + this.month + "-01";

            let uninsuredRequestId = this.sortList[sort];

            return CustomAjax.send(
                'DELETE',
                '/group_home/result_info/uninsured/delete?id=' + uninsuredRequestId + '&month=' + month + "&facility_user_id=" +  this.facilityUserID,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                'callbackDeleteRow',
                this
            );
        }
    }

    async callbackDeleteRow(result)
    {
        this.syncWithServer();
    }

    async changeRow(event)
    {
        this.changeAgreement(false);
        let elm = event.target;

        let value = elm.value;
        let sort = elm.getAttribute("data-sort-index");
        let month = this.year + "-" + this.month + "-01";

        let uninsuredRequestId = this.sortList[sort];

        return CustomAjax.send(
            'GET',
            '/group_home/result_info/uninsured/save_row?uninsured_request_id=' + uninsuredRequestId + "&unit_cost=" + value + "&facility_user_id=" + this.facilityUserID + '&sort=' + sort + '&month=' + month,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            [],
            'callbackSaveRow',
            this
        );
    }

    async changeValue(event)
    {
        this.changeAgreement(false);
        let elm = event.target;

        let urID = elm.getAttribute("data-uninsured_item_history_id");
        let value = parseInt(elm.value);
        if(Number.isNaN(value)){
            value = 0;
        }
        elm.value = value;
        let date = elm.getAttribute("data-date");
        let sort = elm.getAttribute("data-sort-index");

        let uninsuredRequestId = this.sortList[sort];

        return CustomAjax.send(
            'GET',
            '/group_home/result_info/uninsured/save_cell?id=' + uninsuredRequestId + "&quantity=" + value + "&date=" + date + "&facility_user_id=" + this.facilityUserID,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            [],
            'callbackSaveCell',
            this
        );
    }

    async callbackSaveCell()
    {
        this.reCalc();
    }

    async callbackSaveRow(result)
    {
        this.uninsuredRequestId = result['id']
        this.sortList[result["sort"]] = result["id"];
        this.reCalc();
    }

    clearValidateDisplay()
    {
        while(this.validationDisplayArea.lastChild){
            this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
        }
    }

    reCalc()
    {
        let selfTotal = 0;
        let trCount = document.getElementById("calendar").rows.length - 1;
        for(let i = 1; i < trCount; i++){
            let count = 0;
            let searchIndex = 'data-sort-index';
            let unitCostInput = document.querySelector('input['+ searchIndex +'="'+ i +'"]:not([name="date-of_use"])');
            let unitCost = parseInt(unitCostInput.value);
            if(isNaN(unitCost)){
                unitCost = 0;
            }
            let days = document.querySelectorAll('input['+ searchIndex +'="'+ i +'"]:not([name="unit_cost"])');
            for(let c = 0; c < days.length; c++){
                let dayElm = days[c];
                count = count + parseInt(dayElm.value);
            }


            let totalPrice = unitCost * count;

            let sum = document.querySelector('['+ searchIndex +'="'+ i +'"][id="summary"]');
            let total = document.querySelector('['+ searchIndex +'="'+ i +'"][id="total"]');

            sum.textContent = count;
            total.textContent = '\xA5' + String(totalPrice).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');

            selfTotal = selfTotal + totalPrice;
        }
        this.uninsuredSelfTotal.textContent = '\xA5' + String(selfTotal).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
        this.getBenefitBilling();
        this.getUserPublicInfo();
    }

    async getBenefitBilling(){
        if(this.facilityUserID === null){
            return false;
        } else {
            return await CustomAjax.send(
                'POST',
                '/group_home/service/service_result/get_benefit_billing',
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                {facility_user_id:this.facilityUserID,year:this.year,month:this.month},
                'callbackGetBenefitBilling',
                this
            );
        }
    }

    async callbackGetBenefitBilling(result)
    {
        let uninsuredTotal = "";
        this.uninsuredTotal.textContent = "";
        let publicPayment = "";
        this.publicPayment.textContent = "";

        if (result !== null && result.length !== 0) {
            let data = result[0]
            if (data.approval == 1) {
                uninsuredTotal = Number(data.part_payment).toLocaleString();
                this.uninsuredTotal.textContent = '\xA5' + String(uninsuredTotal).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
                publicPayment = Number(data.public_payment).toLocaleString();
                this.publicPayment.textContent = '\xA5' + String(publicPayment).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
            }
        }
    }

    async getUserPublicInfo(){
        if (this.facilityUserID === null){
            return false;
        } else {
            await CustomAjax.send(
                'GET',
                '/group_home/result_info/uninsured/get_user_public_info' + this.getRequestGetUserInfoParams(),
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                'callbackGetUserPublicInfo',
                this
            );
        }
    }

    async callbackGetUserPublicInfo(result)
    {
        let legalNum = result['legal_number'];
        // TODO: 利口なUIになってしまっている。
        if (this.LEGAL_NUMBERS.includes(legalNum)){
            this.publicPaymentArea.style.display = "flex";
        }else{
            this.publicPaymentArea.style.display = "none";
        }
    }

    async sortEnd()
    {
        let elms = $("#calendar").sortable("toArray");
        let sort = [];
        for(let i = 0; i < elms.length; i++){
            let uninsuredRequestId = elms[i].replace("row_", "");
            sort.push(uninsuredRequestId);
        }
        let query = "";
        let querys = [];
        querys['facility_user_id'] = this.facilityUserID;
        querys['month'] = this.year + "-" + this.month + "-01";
        querys['uninsured_request_id_list'] = sort.join(",");

        for (let [key, value] of Object.entries(querys)) {
            query += key;
            query += '=';
            query += encodeURI(value);
            query += '&';
        }

        await CustomAjax.send(
            'GET',
            '/group_home/result_info/uninsured/save_sort?' + query,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            [],
            'callbackSaveSort',
            this
        );
    }

    async callbackSaveSort()
    {

    }

    number_format(num)
    {
        return num.toString().replace(
            /(\d+?)(?=(?:\d{3})+$)/g,
            function (x) {
                return x + ',';
            }
        );
    }

    getDateFormatYearMonth(param)
    {
        let dateYear = '';
        let dateMonth = '';

        if(param){
            let date = new Date(param);

            dateYear = date.getFullYear();
            dateMonth = ('00' + (date.getMonth()+1)).slice(-2);
        }else{
            // NOTE:.padStart()で0埋めをするためstring型へ変換
            dateYear = this.year;
            dateMonth = String(this.month).padStart(2, '0');
        }

        let dateYearMonth = dateYear + '-' + dateMonth;

        return dateYearMonth;
    }

    simpleValidateDisplay(errorBody)
    {
        const LI = document.createElement('li');
        let validationDisplayUl = this.validationDisplayArea;
        let errorMessage = document.createTextNode(errorBody);

        LI.appendChild(errorMessage);
        validationDisplayUl.appendChild(LI);
    }

    /**
     * パラメーターを返す
     * @returns {String}
     */
    getRequestGetUserInfoParams(){
        return '?facility_user_id='
        + this.facilityUserID
        + '&year='
        + this.year
        + '&month='
        + this.month;
    }

    /**
     * ユーザ情報のデータをサーバーと同期させる
     * @returns {void}}
     */
    async syncUserInfor(){
        if (this.facilityUserID){
            await CustomAjax.send(
                'GET',
                '/group_home/service/facility_user/header/get' + this.getRequestGetUserInfoParams(),
                {'X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                "setUserInfo",
                this
            );
        }
    }

    /**
     * 利用者情報のデータをセットする
     * @param {Object} response
     * @returns {void}
     */
    setUserInfo(response){
        this.userInfoStartDate = response.start_date ? new Date(response.start_date) : null;
        this.userInfoEndDate = response.end_date ? new Date(response.end_date) : null;
        if (this.userInfoEndDate){
            this.userInfoEndDate.setHours(23);
        }
    }

    /**
     * 実績情報が変わったことを通知する。
     * @return {Promise}
     */
    async notification() {
        for (let i = 0, len = this.notificationList.length; i < len; i++) {
            await this.notificationList[i]();
        }
    }
}
