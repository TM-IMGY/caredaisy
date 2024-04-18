import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';
import ConfirmationDialog from '../../lib/confirmation_dialog.js';

export default class InjuryAndIllnessName{
    constructor(facilityId){
        this.elementID = 'tm_contents_injury_and_illness';
        this.status = false;
        this.facilitityUserInfo = {facilityUserId:null};
        this.request = {};
        this.facilityId = facilityId
        this.historyId = null;
        this.selectedRecord = null;

        this.historyTBody = document.getElementById('tbody_injury_and_illness');
        this.newRegisterButton = document.getElementById('injury_and_illness_new_register');
        this.saveButton = document.getElementById('injury_and_illness_save');
        this.startDate = document.getElementById('injury_and_illness_start_date');
        this.startDateSpan = document.getElementById("jaCalIAIStartDate");
        this.endDate = document.getElementById('injury_and_illness_end_date');
        this.endDateSpan = document.getElementById("jaCalIAIEndDate");
        this.wrapElement = document.querySelectorAll('.wrap');
        this.buttonBlock = document.getElementById('injury_button_block');
        this.validationDisplayArea = document.getElementById('validateErrorsInjuryAndIllness')
        this.jaCalIAI = document.querySelectorAll('.injury_and_illness_date_input');

        this.msg = '利用者のサービス種類が登録されておりません。<br>サービス種類の登録後、傷病名登録をお願いいたします。'

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

        this.newRegisterButton.addEventListener('click', this.clickNewRegister.bind(this))
        this.saveButton.addEventListener('click', function() {
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
    }

    async activate()
    {
        this.historyId = null;
        this.clearValidateDisplay();
        this.clearDuplicationsColor();
        // 保存ボタンの表示・非表示
        this.facilitityUserInfo.facilityUserId ? this.buttonBlock.style.setProperty('visibility','visible') :this.buttonBlock.style.setProperty('visibility','hidden');
        let bool = this.facilitityUserInfo.facilityUserId ? await this.usingServiceInformation() : '';
        // 利用者に利用中のサービスがなかったら、メッセージを表示して、保存ボタンを非表示に
        if (bool === false ) {
            this.showPopup(this.msg)
            this.buttonBlock.style.setProperty('visibility','hidden');
        }
        this.clearText();
        this.removeOptions();
        this.createOptions();
        await this.createHistoryTbody();
    }

    allClear(registerBtn = false)
    {
        this.clearValidateDisplay()
        this.clearDuplicationsColor()
        if (this.selectedRecord) {
            this.selectedRecord.classList.remove('select_record');
        }
        if (!registerBtn) {
            this.historyTBody.textContent = null;
        }
        this.facilitityUserInfo.facilityUserId ? this.buttonBlock.style.setProperty('visibility','visible') :this.buttonBlock.style.setProperty('visibility','hidden');
        this.historyId = null;
        this.clearText();
        this.clearSelects();
    }

    /**
     * 特別診療費の内容が重複しているセレクトボックスの抽出
     * @param {int} ids
     * @returns array
     */
     checkDuplicationSelect(ids)
    {
        let duplication = ids.filter(function(x, i, self) {
            return self.indexOf(x) === i && i !== self.lastIndexOf(x) && x !== ''
        } )
        return duplication;
    }

    /**
     * 特別診療費の内容が重複しているセレクトボックスの背景色初期化
     */
    clearDuplicationsColor()
    {
        document.querySelectorAll('.duplicate').forEach(element => {
            element.classList.remove('duplicate')
        })
    }

    clearText()
    {
        document.querySelectorAll('.injury_and_illness_name').forEach(element => {
            element.value = "";
        });
        this.jaCalIAI.forEach(e => { e.innerHTML = "";})
        this.startDate.value = '';
        this.endDate.value = '';
    }

    /**
     * 傷病名と各セレクトボックスを初期化
     */
    clearSelects()
    {
        document.querySelectorAll('.injury_and_illness_name_item').forEach(e => {
            e.value = "";
        });
        document.querySelectorAll('.special_select').forEach(e => {
            e.options[0].selected = true;
        });
    }

    async clickHistory(element, record)
    {
        this.clearValidateDisplay()
        this.clearDuplicationsColor()
        // ハイライト解除
        if (this.selectedRecord) {
            this.selectedRecord.classList.remove('select_record');
        }
        this.selectedRecord = record;
        // 選択した履歴をハイライト
        this.selectedRecord.classList.add("select_record");
        this.historyId = element.id;
        let response = await CustomAjax.get(
            '/group_home/user_info/injury_and_illness/get_history?'
            + this.createGetSpecialRequestParam()
            + '&id='
            + this.historyId,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let historyDetail = await response.json();
        this.clearSelects();
        this.insertForms(historyDetail)
    }

    /**
     * 新規登録ボタン押下時処理
     */
    async clickNewRegister()
    {
        this.allClear(true);
        let response = await CustomAjax.get(
            '/group_home/user_info/injury_and_illness/get_user_info?'
            + this.createGetSpecialRequestParam(),
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
            this.startDateSpan.innerText = JapaneseCalendar.toJacal(year + '-' + month + '-' + day);
        } else {
            this.startDate.value = info.start_date.replace(/-/g, '/');
            this.startDateSpan.innerText = JapaneseCalendar.toJacal(info.start_date);
        }

    }

    /**
     * 日付の履歴の整形を行う
     * @param {*} text
     * @returns
     */
    createDateTd(text)
    {
        let element = document.createElement('td');
        element.textContent = this.getRuleBasedDateFormat(text)
        element.classList.add('item_of_date')
        return element;
    }

    /**
     * リクエストパラメータの生成
     * @returns
     */
    createGetSpecialRequestParam()
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
        + month
        return requestParam
    }

    /**
     * 履歴のtbodyを生成する
     */
    async createHistoryTbody()
    {
        this.historyTBody.textContent = null;
        // 利用者の履歴リストを取得する
        let response = await CustomAjax.get(
            '/group_home/user_info/injury_and_illness/get_histories?'
            + this.createGetSpecialRequestParam(),
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let histories = await response.json();

        let count = 0;
        let record1;
        await histories.forEach(element => {
            let record = document.createElement('tr');

            let startDate = this.createDateTd(element.start_date);
            let endDate = this.createDateTd(element.end_date);

            let injuryAndLllness1 = document.createElement('td');
            injuryAndLllness1.textContent = element.detail[0] ? element.detail[0].name : '';
            let injuryAndLllness2 = document.createElement('td');
            injuryAndLllness2.textContent = element.detail[1] ? element.detail[1].name : '';
            let injuryAndLllness3 = document.createElement('td');
            injuryAndLllness3.textContent = element.detail[2] ? element.detail[2].name : '';
            let injuryAndLllness4 = document.createElement('td');
            injuryAndLllness4.textContent = element.detail[3] ? element.detail[3].name : '';

            record.appendChild(startDate);
            record.appendChild(endDate);
            record.appendChild(injuryAndLllness1);
            record.appendChild(injuryAndLllness2);
            record.appendChild(injuryAndLllness3);
            record.appendChild(injuryAndLllness4);

            // td要素にクラス付与
            Array.from(record.children).forEach((child)=>{
                child.classList.add('td_of_injury_and_illness');
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
     *
     * @returns プルダウンリスト生成
     */
    async createOptions()
    {
        if (!this.facilitityUserInfo.facilityUserId) {
            return;
        }

        let pullDownList = await CustomAjax.get(
            '/group_home/user_info/injury_and_illness/get_special?'
            + this.createGetSpecialRequestParam(),
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );
        let params = await pullDownList.json();

        // 入力欄の数だけループ
        for (let index = 1; index < 5; index++) {
            // 特別診療費のプルダウンリストを作成
            params.forEach( element => {
                document.querySelectorAll('.special_select_' + index).forEach(e => {
                    let option = document.createElement('option');
                    option.textContent = element.special_medical_name
                    option.value = element.id
                    e.append(option)
                })
            })
        }


        // 既に選択している特別診療費内容はリストから外す？
    }

    /**
     * ルールで決められた日付の書式を返す
     * todo 他の画面でも使用しているので共通化も視野に
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

    /**
     * 各種フォームにデータ挿入
     * @param {object} param
     */
    insertForms(param)
    {
        param[0].forEach(element => {
            document.getElementById('injury_and_illness_name_'+ element.group).value = element.name;
            element.relations.forEach( relation => {
                document.querySelector('.special_select_'+element.group + '.select_num_' + relation.selected_position).value = relation.special_medical_code_id
            })
        });
        this.startDate.value = param.start_date.replace(/-/g, '/');
        this.startDateSpan.innerText = JapaneseCalendar.toJacal(param.start_date);
        this.endDate.value = param.end_date.replace(/-/g, '/');
        this.endDateSpan.innerText = JapaneseCalendar.toJacal(param.end_date);
    }

    /**
     * 特別診療費の重複チェックして、ハイライトする
     * サーバー側でも重複チェックのバリデーションは実施
     * @param {int} index
     * @param {Array} ids
     */
    medicalCodeValidation(selectIds)
    {
        // プルダウンで重複している内容を取得
        let duplication = this.checkDuplicationSelect(selectIds)
        document.querySelectorAll('.special_select').forEach(element => {
            duplication.forEach(e =>{
                // 重複している内容のプルダウンの背景色変更
                if ($(element ,'option:selected').val() === e) {
                    element.classList.add('duplicate')
                }
            })
        })
    }

    /**
     * 利用者情報の格納
     * @param {object} status
     */
    setActive(status)
    {
        this.status = status;
        if(status && this.facilitityUserInfo.facilityUserId){
            this.activate();
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

    /**
     * 登録処理
     */
    save()
    {
        this.clearValidateDisplay();
        this.clearDuplicationsColor();
        let request = {request_special:{}};
        let selectIds = [];
        for (let index = 1; index < this.wrapElement.length + 1; index++) {
            let param = {
                'name': document.getElementById('injury_and_illness_name_' + index).value
            }

            let ids = []
            document.querySelectorAll('.special_select_' + index).forEach(e => {
                ids.push(e.value)
                selectIds.push(e.value);
            })
            param['ids'] = ids;

            // keyは1~4
            request.request_special[index] = param
        }
        this.medicalCodeValidation(selectIds);
        request['start_date'] = this.startDate.value
        request['end_date'] = this.endDate.value ? this.endDate.value : '2024/03/31';
        request['facility_user_id'] = this.facilitityUserInfo.facilityUserId
        request['facility_id'] = this.facilityId
        if (this.historyId) {
            request['id'] = this.historyId
        }

        CustomAjax.send(
            'POST',
            '/group_home/user_info/injury_and_illness/save',
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            request,
            'successfulSaving',
            this
        );
    }

    successfulSaving(response)
    {
        if (response) {
            this.activate();
        }
    }

    /**
     * セレクトボックスのoptionを削除
     */
    removeOptions()
    {
        document.querySelectorAll('.special_select').forEach( element => {
            for (var i = element.length - 1; 0 < i; --i) {
                element.removeChild(element[i]);
            }
        })
    }

    /**
     * 対象月にステータスが利用中のサービスが存在するか判定する
     * @returns
     */
    async usingServiceInformation()
    {
        let res = await CustomAjax.get(
            '/group_home/user_info/injury_and_illness/get_user_info?'
            + this.createGetSpecialRequestParam(),
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        );

        let serviceInfo = await res.json();

        if (!Array.isArray(serviceInfo)) {
            return true;
        }
        return false;
    }

    validateDisplay(errorBody)
    {
        let createRow = (function(key, value){
            let record = document.createElement('li');
            let validationDisplayArea = document.getElementById("validateErrorsInjuryAndIllness");
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

    /**
     * ポップアップを表示する
     * @param {Array} msg
     * @returns {void}
     */
    showPopup(msg)
    {
        let elementPopup = document.createElement('div');
        elementPopup.id = 'overflow';
        let elementPopupContents = document.createElement('div');
        elementPopupContents.classList.add('conf');
        let elementPopupMessage = document.createElement('p');
        elementPopupMessage.innerHTML = msg;
        let elementBtnFrame = document.createElement('div');
        elementBtnFrame.classList.add('btns_frame');
        let elementBtn = document.createElement('button');
        elementBtn.classList.add('popup_cancel');
        elementBtn.textContent = '閉じる';
        elementBtn.addEventListener('click', () => {elementPopup.parentNode.removeChild(elementPopup);});
        elementPopup.appendChild(elementPopupContents);
        elementPopupContents.appendChild(elementPopupMessage);
        elementPopupContents.appendChild(elementBtnFrame);
        elementBtnFrame.appendChild(elementBtn);
        document.body.appendChild(elementPopup);
    }
}