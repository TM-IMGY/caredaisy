import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';
import ConfirmationDialog from '../../lib/confirmation_dialog.js';

export default class BasicAbstract{
    constructor(facilityId){
        this.elementID = 'tm_contents_basic_abstract';
        this.element = document.getElementById(this.elementID);
        this.status = false;
        this.facilitityUserInfo = {facilityUserId:null};
        this.request = {};
        this.facilityId = facilityId
        this.selectedRecord = null;
        this.historyId = null;
        this.mainInjuryAndIllnessNameId = null

        this.historyTBody = document.getElementById('tbody_basic_abstract');
        this.dpcCode = document.getElementById('dpc_code');
        this.mdcGroupName = document.getElementById('main_injury_and_illness_name');
        this.userCircumstanceCode = document.getElementById('user_status_code');
        this.startDate = document.getElementById('basic_abstract_start_date');
        this.startDateSpan = document.getElementById("jaCalBAStartDate");
        this.endDate = document.getElementById('basic_abstract_end_date');
        this.endDateSpan = document.getElementById("jaCalBAEndDate");
        this.newRegister = document.getElementById('new_register');
        this.saveBtn = document.getElementById('basic_abstract_save');
        this.validationDisplayArea = document.getElementById('validateErrorsBasicAbstract');
        this.buttonBlock = document.getElementById('basic_abstract_button_block');

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
        // キーによる日付入力イベント
        this.startDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
        this.endDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))

        this.newRegister.addEventListener('click', this.clickNewRegister.bind(this, true))
        this.saveBtn.addEventListener('click', function() {
            // 更新の場合はポップアップを表示
            if (this.historyId != null) {
                let confirmationDialog = new ConfirmationDialog(
                    '変更した内容を更新しますか？',
                    this.save.bind(this)
                );
                confirmationDialog.show();
                return;
            }
            this.save();
        }.bind(this));
        this.dpcCode.addEventListener('change', this.insertMainInjuryAndIllnessName.bind(this))

        this.createUserStatusCodeSelect()
    }

    /**
     * 入力されたDPCコードから主傷病名を取得する
     * @param {object} event
     * @returns
     */
    async insertMainInjuryAndIllnessName(event)
    {
        let dpcCode = event.target.value;
        let mdcGroupNameInfo = await this.getMdcGroupName(dpcCode);

        if (mdcGroupNameInfo.length == 0) {
            this.mdcGroupName.textContent = '主傷病名が見つかりません';
            this.mdcGroupName.setAttribute('data-contents-value', "");
            return;
        }

        let name = mdcGroupNameInfo[0].name
        this.mdcGroupName.textContent = name;
        this.mdcGroupName.setAttribute('data-contents-value', mdcGroupNameInfo[0].id);
    }

    async activate()
    {
        this.clearValidateDisplay();
        this.historyTBody.textContent = null;
        this.facilitityUserInfo.facilityUserId ? this.buttonBlock.style.setProperty('visibility','visible') :this.buttonBlock.style.setProperty('visibility','hidden');
        this.historyId = null;
        this.clearText()
        this.clearSelect()
        await this.createHistoryTbody()
        this.historyTBody.firstChild && this.historyTBody.firstChild.click();
    }

    /**
     * 各種初期化
     * @param {boolean} registerBtn 新規登録ボタンからかどうか
     */
    allClear(registerBtn = false)
    {
        if (!registerBtn) {
            this.historyTBody.textContent = null;
        }
        this.historyId = null;
        this.mainInjuryAndIllnessNameId = null;
        this.mdcGroupName.textContent = '';
        this.facilitityUserInfo.facilityUserId ? this.buttonBlock.style.setProperty('visibility','visible') :this.buttonBlock.style.setProperty('visibility','hidden');
        if (this.selectedRecord) {
            this.selectedRecord.classList.remove('select_record');
        }
        this.clearValidateDisplay();
        this.clearSelect()
        this.clearText()
    }

    /**
     * プルダウン初期化
     */
    clearSelect()
    {
        this.userCircumstanceCode.options[0].selected = true;
    }

    clearText()
    {
        this.dpcCode.value = '';
        this.mdcGroupName.value = '';
        this.startDate.value = '';
        this.startDateSpan.innerText = '';
        this.endDate.value = '';
        this.endDateSpan.innerHTML = '';
        this.mdcGroupName.textContent = '';
        this.mdcGroupName.removeAttribute('data-contents-value');
    }

    /**
     * 新規登録ボタン押下時処理
     */
    async clickNewRegister()
    {
        this.allClear(true);
        let response = await CustomAjax.get(
            '/group_home/user_info/basic_abstract/get_user_info?'
            + this.createFacilityAndFacilityUserParams(),
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let info = await response.json();

        // 履歴があれば最新履歴の有効終了日の次の日、履歴がなければ入居日を挿入
        if (info.latest_history) {
            let tomorrow = new Date(info.latest_history.end_date);
            tomorrow.setDate(tomorrow.getDate()+ 1)
            let year = tomorrow.getFullYear()
            let month = (tomorrow.getMonth() + 1).toString().padStart(2, "0");
            let day = (tomorrow.getDate()).toString().padStart(2, "0");
            this.startDate.value = year + '/' + month + '/' + day;
            this.startDateSpan.innerText = JapaneseCalendar.toJacal(year + '/' + month + '/' + day);
        } else {
            this.startDate.value = info.start_date.replace(/-/g, '/');
            this.startDateSpan.innerText = JapaneseCalendar.toJacal(info.start_date);
        }
    }

    /**
     * 利用者状態等コードプルダウンを生成する
     */
    async createUserStatusCodeSelect()
    {
        // 「利用者状況等コードマスタテーブル」からリストを取得する
        let response = await CustomAjax.get(
            '/group_home/user_info/basic_abstract/get_code',
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let pullDownList = await response.json();

        pullDownList.forEach(element => {
            let option = document.createElement('option');
            option.textContent = element;
            this.userCircumstanceCode.append(option);
        })

    }

    createDateTd(text)
    {
        let element = document.createElement('td');
        element.textContent = this.getRuleBasedDateFormat(text)
        element.classList.add('item_of_date')
        return element;
    }

    /**
     * 共通のリクエストパラメータを作成
     * @returns
     */
    createFacilityAndFacilityUserParams()
    {
        let today = new Date();
        let month = today.getMonth()+1
        let requestParam =
        'facility_id='
        + this.facilityId
        + '&facility_user_id='
        + this.facilitityUserInfo.facilityUserId
        + '&year='
        + today.getFullYear()
        + '&month='
        + month;

        return requestParam;
    }

    /**
     * 履歴クリック時処理
     * @param {object} element
     * @param {*} record
     */
    clickHistory(element, record)
    {
        // ハイライト解除
        if (this.selectedRecord) {
            this.selectedRecord.classList.remove('select_record');
        }
        this.selectedRecord = record;
        // 選択した履歴をハイライト
        this.selectedRecord.classList.add("select_record");

        this.historyId = element.id;
        this.clearValidateDisplay();
        this.insertForms(element)
    }

    /**
     * 履歴のtd部分を作成
     */
    async createHistoryTbody()
    {
        // 利用者の履歴リストを取得する
        let response = await CustomAjax.get(
            '/group_home/user_info/basic_abstract/get_histories?'
            + this.createFacilityAndFacilityUserParams(),
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let histories = await response.json();

        let count = 0;
        let record1;
        await histories.forEach(element => {
            let record = document.createElement('tr');

            let startDate = this.createDateTd(element.start_date)
            let endDate = this.createDateTd(element.end_date)
            let dpc = document.createElement('td');
            dpc.textContent = element.dpc_code;
            let userStatus = document.createElement('td');
            userStatus.textContent = element.user_circumstance_code;

            record.appendChild(startDate);
            record.appendChild(endDate);
            record.appendChild(dpc);
            record.appendChild(userStatus);


            // td要素にクラス付与
            Array.from(record.children).forEach((child)=>{
                child.classList.add('td_of_basic_abstract');
            });

            record.addEventListener('click', this.clickHistory.bind(this, element, record))
            if(count == 0){
                record1 = startDate
            }
            this.historyTBody.appendChild(record);
            count++;
        })
        record1 && record1.click();
    }

    /**
     * MDC分類名称を取得する
     * @param {string} dpcCode
     * @returns
     */
    async getMdcGroupName(dpcCode)
    {
        let response = await CustomAjax.get(
            '/group_home/user_info/basic_abstract/mdc_group_names?'
            + this.createFacilityAndFacilityUserParams()
            + '&dpc_code='
            + dpcCode,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let mdcGroupNameInfo = await response.json();
        return mdcGroupNameInfo;
    }

    /**
     * ルールで決められた日付の書式を返す
     * @param {String} dateStr
     * @returns {String}
     */
     getRuleBasedDateFormat(dateStr){
        let date = new Date(dateStr);
        // 利用者情報画面の場合は0埋めをする。
        let year = date.getFullYear();
        let month = (date.getMonth() + 1).toString().padStart(2, "0");
        let day = (date.getDate()).toString().padStart(2, "0");
        return year + '/' + month + '/' + day;
    }

    async insertForms(element)
    {
        this.dpcCode.value = element.dpc_code;
        let mdcGroupNameInfo = await this.getMdcGroupName(element.dpc_code);
        let name = mdcGroupNameInfo[0].name
        this.mdcGroupName.textContent = name;
        this.mdcGroupName.setAttribute('data-contents-value', mdcGroupNameInfo[0].id);
        this.userCircumstanceCode.value = element.user_circumstance_code ? element.user_circumstance_code : "";
        this.startDate.value = element.start_date.replace(/-/g, '/');
        this.startDateSpan.innerText = JapaneseCalendar.toJacal(element.start_date);
        this.endDate.value = element.end_date.replace(/-/g, '/');
        this.endDateSpan.innerText = JapaneseCalendar.toJacal(element.end_date);
    }

    async save()
    {
        this.clearValidateDisplay();
        let params = {
            facility_id: this.facilityId,
            facility_user_id: this.facilitityUserInfo.facilityUserId,
            dpc_code: this.dpcCode.value,
            main_injury_and_illness_name: this.mdcGroupName.getAttribute('data-contents-value'),
            user_circumstance_code: this.userCircumstanceCode.value,
            start_date: this.startDate.value,
            end_date: this.endDate.value ? this.endDate.value : '2024/03/31',
        }

        if (this.historyId != null) {
            params['id'] = this.historyId;
        }

        CustomAjax.send(
            'POST',
            '/group_home/user_info/basic_abstract/save',
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            params,
            'successfulSaving',
            this
        );
    }

    setActive(status)
    {
        this.status = status;
        if(status && this.facilitityUserInfo.facilityUserId != null){
            this.activate();
        }else{
            this.allClear();
        }
    }

    /**
     * 選択された利用者情報を格納する
     * @param {object} infos
     */
    setFacilityUserInfo(infos)
    {
        this.facilitityUserInfo.facilityUserId = infos.facilityUserID;
        if (this.status && this.facilitityUserInfo.facilityUserId != null) {
            this.activate();
            return;
        }
        this.allClear();
    }

    successfulSaving(response)
    {
        if (response) {
            this.activate();
        }
    }

    validateDisplay(errorBody)
    {
        let createRow = (function(key, value){
            let record = document.createElement('li');
            let validationDisplayArea = document.getElementById("validateErrorsBasicAbstract");
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
        while(this.validationDisplayArea.lastChild){
            this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
        }
    }
}
