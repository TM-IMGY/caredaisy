import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js';
import SpecialMedicalExpensesTable from './special_medical_expenses_table.js';
import JapaneseCalendar from '../../lib/japanese_calendar.js';

export default class SpecialMedicalExpenses {
    constructor()
    {
        this.specialTable = new SpecialMedicalExpensesTable();
        this.careRewardHistory = {};
        this.additionStatusId = null;
        this.facilityId = null;
        this.careRewardHistoryId = null;
        this.historyId = null;
        this.elementCacheHistorySelected = null;
        this.checkedCache = [];
        this.targetOfSpecialMedicalExpenses = null;
        this.targetOfShortRehabilitationList = [
            'occupational_therapy',
            'physical_therapy',
            'speech_hearing_therapy',
            'other_rehabilitation_provision'
        ];

        this.specialMedicalExpensesHistoryTbody = document.getElementById('special_medical_expenses_history_tbody');
        this.newRegisterBtn = document.getElementById('special_medical_expenses_new_register');
        this.saveBtn = document.getElementById('special_medical_expenses_save');
        this.elementForm = document.getElementById('special_medical_expenses_list');
        this.specialStartMonth = document.getElementById('special_medical_expenses_start');
        this.FormJaCalStartMonth = document.getElementById('jaCalSMEStartMonth');
        this.specialEndMonth = document.getElementById('special_medical_expenses_end');
        this.FormJaCalEndMonth = document.getElementById('jaCalSMEEndMonth');

        this.newRegisterBtn && this.newRegisterBtn.addEventListener('click', this.allClear.bind(this,false));
        this.saveBtn && this.saveBtn.addEventListener('click', this.save.bind(this));

        // 手入力時日付フォーマット＆和暦変換イベント
        this.specialStartMonth.addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));
        this.specialEndMonth.addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));

        // 事業所情報画面内タブ遷移監視 加算状況タブが非表示になったら特別診療費画面は隠す
        this.observer = new MutationObserver(() => {
            $('#addtion_status_view').removeClass('inactive').addClass('active');
            $('#special_medical_expenses_view').removeClass('active').addClass('inactive');
            document.getElementById('addition_view').style.display = 'block';
            document.getElementById('special_medical_expenses').style.display = 'none';
        })
        this.observer.observe(document.getElementById('tm_contents_addition_status'), {
            attributeFilter: ['class']
        })
    }

    /**
     * 要素を全て初期化する
     * @param {boolean} flg 加算状況側の新規登録ボタンからの作動かどうか
     */
    allClear(flg)
    {
        this.historyId = null;
        if (this.elementCacheHistorySelected) {
            this.elementCacheHistorySelected.classList.remove('as_special_medical_expenses_history_selected');
        }
        this.clearCheckBox();
        // 開始月・終了月のリセット
        this.clearMonth();

        // 加算状況から該当する項目にチェックを入れる
        if (!flg) {
            this.checkedCache.forEach(value => {
                this.elementForm[value].checked = true;
            });
        } else {
            this.checkedCache = []
        }
    }

    /**
     * 加算状況タブ側で新規登録ボタンが押されたら作動
     * 画面及び保持情報を初期化する
     */
    clickAdditionStatusInsertBtn()
    {
        this.careRewardHistory = {};
        this.careRewardHistoryId = null;
        this.allClear(true);
        this.specialMedicalExpensesHistoryTbody.textContent = null;
    }

    /**
     * 履歴テーブルを構築する
     */
    async constructionHistory()
    {
        let histories = await this.getSpecialMedicalExpensesHistories();
        this.targetOfSpecialMedicalExpenses = histories.autoCheckedFlg;
        this.createHistoryTable(histories);
    }

    callBackResponce($res)
    {
        if ($res) {
            this.constructionHistory()
        }
    }

    async save()
    {
        let startAndEndDate = this.getFormsDateValue(this.specialStartMonth.value, this.specialEndMonth.value)

        let data = {
            'facility_id':this.facilityId,
            'start_month':startAndEndDate.start_month,
            'end_month':startAndEndDate.end_month,
            'checked':{}
        };

        if (this.historyId) {
            data['special_medical_selects_id'] = this.historyId;
        };

        if (this.careRewardHistoryId) {
            data['care_reward_id'] = this.careRewardHistoryId;
        };

        let i = 0;
        this.elementForm.querySelectorAll('.special_medical_checkbox').forEach(e => {
            if (e.checked) {
                // 値の持ち方は要検討
                data.checked[i] = e.value;
                i++;
            }
        })

        await CustomAjax.send(
            'POST',
            '/group_home/facility_info/special_medical_expenses/save',
            {'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN},
            data,
            'callBackResponce',
            this
        );
    }

    /**
     * チェックがついている項目の登録用valueを返す
     * @param {object} element
     * @returns
     */
    cutOfServiceName(element)
    {
        let str = element.closest('.special_medical_expenses_use').previousElementSibling.textContent
        return str.substring(str.indexOf('　')+1)
    }

    /**
     * 特別診療費テーブルを作成する
     */
    createForm()
    {
        // フォームの初期化
        this.clearCheckBox();
        this.clearMonth();
        this.elementForm.textContent = null;

        // テーブルを取得する
        let specialMedicalExpenseTable = this.specialTable.getSpecialMedicalExpenseItem();

        // メインのフォームを作る
        let formElements = this.createFormContent('special_medical_expenses_service', specialMedicalExpenseTable);
        this.elementForm.appendChild(formElements)

    }

    async clickSpecialMedicalExpensesHistory(id, record)
    {
        let data = await this.getSpecialMedicalExpensesHistory(id);

        // レコードの選択状態を変更する
        if(this.elementCacheHistorySelected){
            this.elementCacheHistorySelected.classList.remove('as_special_medical_expenses_history_selected');
        }
        record.classList.add('as_special_medical_expenses_history_selected');
        this.elementCacheHistorySelected = record;

        // 介護報酬履歴データからフォームにデータをセットする
        this.setFormData(data.codesInfo);
        this.setFormDate(data.selectInfo);
    }

    /**
     * 履歴テーブルを作成する
     * @param {*} histories
     */
    createHistoryTable(histories)
    {
        // テーブルのレコードを全て削除する
        this.specialMedicalExpensesHistoryTbody.textContent = null;

        let record1 = null;
        for (let i=0,len=histories.spMedicalSelect.length; i<len; i++) {
            let tr = document.createElement('tr');
            // tr.id = '';
            tr.addEventListener('click', this.clickSpecialMedicalExpensesHistory.bind(this,histories.spMedicalSelect[i].id,tr));
            tr.classList.add('select_table_special_medical_expenses');

            // 加算開始月(仮)
            let tdAdditionStart = document.createElement('td');
            tdAdditionStart.classList.add('history_of_addtion_status_start_date');
            tdAdditionStart.textContent = this.getDateFormdefinedByRule(histories.careRewardHistory.start_month);

            // 加算終了月(仮)
            let tdAdditionEnd = document.createElement('td');
            tdAdditionEnd.classList.add('history_of_addtion_status_end_date');
            tdAdditionEnd.textContent = this.getDateFormdefinedByRule(histories.careRewardHistory.end_month);

            // 特別診療開始月(仮)
            let tdStartMonth = document.createElement('td');
            tdStartMonth.classList.add('history_of_special_start_date');
            tdStartMonth.textContent = this.getDateFormdefinedByRule(histories.spMedicalSelect[i].start_month);
            // 特別診療終了月(仮)
            let tdEndMonth = document.createElement('td');
            tdEndMonth.classList.add('history_of_special_end_date');
            tdEndMonth.textContent = this.getDateFormdefinedByRule(histories.spMedicalSelect[i].end_month);

            tr.appendChild(tdAdditionStart);
            tr.appendChild(tdAdditionEnd);
            tr.appendChild(tdStartMonth);
            tr.appendChild(tdEndMonth);
            this.specialMedicalExpensesHistoryTbody.appendChild(tr);

            // 1レコード目はキャッシュする
            if(i == 0){
                record1 = tr;
            }
        }
        record1 && record1.click();

        // 履歴が1件もなかったら新規登録ボタンを押下したことにする
        if (histories.length == 0 && this.newRegisterBtn) {
            this.newRegisterBtn.click()
        }
    }

    /**
     * 各フォームテーブルを作る
     * @param {*} className
     * @param {*} serviceCode
     * @returns
     */
    createFormContent(className, serviceCode)
    {
        let div = document.createElement('div');
        div.className = className;

        let table = document.createElement('table');
        let tbody = document.createElement('tbody');

        div.appendChild(table);
        table.appendChild(tbody);

        serviceCode.forEach(element => {
            let nameItems = this.createFormServiceItem(element)
            let checkBoxItems = this.createFormCheckBox(element)

            nameItems.appendChild(checkBoxItems);
            tbody.appendChild(nameItems);
        });

        // チェック内容のキャッシュを取っておく
        tbody.querySelectorAll('.special_medical_checkbox').forEach(e => {
            if (e.checked) {
                this.checkedCache.push(e.name)
            }
        })

        return div;
    }

    /**
     * チェックボックスを作成する
     * @param {object} serviceCode
     * @returns
     */
     createFormCheckBox(serviceCode)
    {
        let td = document.createElement('td');
        td.classList.add('special_medical_expenses_use');
        let label = document.createElement('label');
        label.classList.add('special_medical_expenses_checkbox');

        let input = document.createElement('input');
        input.name = serviceCode.name;
        input.value = serviceCode.value
        input.type = 'checkbox';
        input.classList.add('special_medical_checkbox')

        if (this.targetOfSpecialMedicalExpenses[serviceCode.addition_name] == 2) {
            input.checked = true;
        }

        // 短期集中リハビリテーション
        if (serviceCode.name == 'short_concentration_rehabilitation' && this.inspectionShortRehabilitation()) {
            input.checked = true;
        }

        input.addEventListener("change", function(){
            document.getElementById("changed_flg").value = true;
        });

        let span = document.createElement('span');
        span.classList.add('special_medical_expenses_check_span');

        label.appendChild(input);
        label.appendChild(span);
        td.appendChild(label);

        return td;
    }

    /**
     * リスト左部の名称部分を作成
     * @param {object} serviceCode
     * @returns
     */
    createFormServiceItem(serviceCode)
    {
        let tr = document.createElement('tr');
        tr.classList.add(serviceCode.tr_class_name);

        let td = document.createElement('td');
        td.classList.add('special_medical_expenses_sevice_name');
        td.textContent = serviceCode.service_code_name;

        tr.appendChild(td);

        return tr;
    }

    /**
     * チェックボックスを初期化する
     */
    clearCheckBox()
    {
        let checkBox = document.querySelectorAll('.special_medical_checkbox');
        checkBox.forEach(e => { e.checked = false; });
    }

    /**
     * 開始月・終了月を初期化する
     */
    clearMonth()
    {
        this.specialStartMonth.value = "";
        this.FormJaCalStartMonth.innerText = "";
        this.specialEndMonth.value = "";
        this.FormJaCalEndMonth.innerText = "";
    }

    /**
     * 加算状況画面で標準化された日付書式を返す。
     * todo addition_status.js と同じ処理のため共通化したい
     * @param {String} dateStr 日付
     * @returns {String}
     */
    getDateFormdefinedByRule(dateStr)
    {
        let date = new Date(dateStr);
        let year = date.getFullYear();
        let month = date.getMonth() + 1;
        return year + '/' + ('0' + month).slice(-2);
    }

    /**
     * 開始月・終了月のフォーマット修正
     * @param {*} start
     * @param {*} end
     * @returns
     */
    getFormsDateValue(start, end)
    {
        let startMonth = new Date(start);
        // 終了日がnullの場合は2024年3月として扱う
        let endMonth = end ? end : '2024/03/31';
        endMonth = new Date(endMonth);
        // 開始日は月初に、終了日は月末にして返す
        startMonth.setDate(1);
        endMonth.setFullYear(endMonth.getFullYear(), endMonth.getMonth() + 1, 0);
        let dates = {
            'start_month':startMonth,
            'end_month':endMonth
        };
        let format = {};
        Object.keys(dates).forEach(element => {
            let y = dates[element].getFullYear();
            let m = ('00' + (dates[element].getMonth() + 1)).slice(-2);
            let d =  ('00' + (dates[element].getDate())).slice(-2);

            format[element] =  y + '/' + m + '/' + d;
        });

        return format;
    }

    /**
     * 該当加算状況履歴の特別診療費履歴リストを取得
     */
    async getSpecialMedicalExpensesHistories()
    {
        try {
            let res = await CustomAjax.get(
            '/group_home/facility_info/special_medical_expenses/get/histories?'
                + 'facility_id=' + this.facilityId
                + '&' + 'care_reward_id=' + this.careRewardHistory.care_reward_id
                + '&' + 'care_reward_history_id=' + this.careRewardHistory.id,
            {'X-CSRF-TOKEN':CSRF_TOKEN}
            );
            let data = await res.json();
            return data;
        } catch (error) {
            throw error;
        }
    }

    /**
     * 特別診療費情報を取得
     */
    async getSpecialMedicalExpensesHistory(id)
    {
        try {
            let res = await CustomAjax.get(
            '/group_home/facility_info/special_medical_expenses/get/special_medical_information?'
                + 'facility_id=' + this.facilityId
                + '&' + 'id=' + id,
            {'X-CSRF-TOKEN':CSRF_TOKEN}
            );
            let data = await res.json();
            return data;
        } catch (error) {
            throw error;
        }
    }

    /**
     * 短期集中リハビリテーションにチェックを付けるか判定する
     * @returns {boolean} $bool
     */
    inspectionShortRehabilitation()
    {
        let bool = false;
        this.targetOfShortRehabilitationList.find(e => {
            if (this.targetOfSpecialMedicalExpenses[e] == 2) {
                bool = true;
            }
        })
        return bool;
    }

    /**
     * 加算状況タブから送られた加算状況情報を処理する
     * @param {*} careRewardHistory
     * @param {*} facilityId
     */
    async prepareSpecialMedicalExpenses(careRewardHistory, facilityId)
    {
        this.checkedCache = [];
        this.facilityId = facilityId;
        this.historyId = null;
        this.careRewardHistory = careRewardHistory;
        this.careRewardHistoryId = careRewardHistory.care_reward_id;

        await this.constructionHistory()
        this.createForm();
    }

    /**
     * チェックボックスを設定する
     * @param {object} careRewardHistory
     */
    setFormData(careRewardHistory)
    {
        this.clearCheckBox();
        Object.keys(careRewardHistory).forEach(element => {
            this.elementForm.querySelector('input[value="'+ careRewardHistory[element].identification_num +'"]').checked = true;
        })
    }

    /**
     * 開始月・終了月に値を入れる
     * @param {object} data
     */
     setFormDate(data)
     {
         let startDate = new Date(data.start_month);
         let endDate = new Date(data.end_month);
         let startY = startDate.getFullYear();
         let startM = ('00' + (startDate.getMonth() + 1)).slice(-2);
         let startD =  '01';
         let startYm = startY + '/' + startM;
         let startYmd = startYm + '/' + startD;

         endDate.setFullYear(endDate.getFullYear(), endDate.getMonth() + 1, 0);
         let endY = endDate.getFullYear();
         let endM = ('00' + (endDate.getMonth() + 1)).slice(-2);
         let endD =  ('00' + (endDate.getDate())).slice(-2);;

         let endYm = endY + '/' + endM;
         let endYmd = endYm + '/' + endD;

         // フォームの開始日と終了日をセットする
         this.specialStartMonth.value = startYm;
         this.specialEndMonth.value = endYm;
         this.specialEndMonth.min = startYm;
         // 和暦をセットする
        this.FormJaCalStartMonth.innerText = JapaneseCalendar.toJacal(startYmd);
        this.FormJaCalEndMonth.innerText = JapaneseCalendar.toJacal(endYmd);

         this.historyId = data.id;
     }

    validateDisplay(errorBody)
    {
        errorBody = JSON.parse(errorBody);
        let errorList = errorBody.errors;
        let msgList = []
        Object.keys(errorList).map(key =>
            msgList.push(errorList[key])
        );

        this.showPopup(msgList)
    }

    /**
   * ポップアップを表示する
   * @param {Array} msg
   * @returns {void}
   */
   showPopup(msgList)
   {
        let elementPopup = document.createElement('div');
        elementPopup.id = 'overflow_sp_medical_expenses_notification';

        let elementPopupContents = document.createElement('div');
        elementPopupContents.classList.add('conf');

        let elementPopupMessage = []
        msgList.forEach( e => {
            let message = document.createElement('p');
            message.innerHTML = e + '<br>';
            elementPopupMessage.push(message)
        })

        let elementBtnFrame = document.createElement('div');
        elementBtnFrame.classList.add('sp_medical_btns');

        let elementBtn = document.createElement('button');
        elementBtn.classList.add('popup_cancel_sp_medical');
        elementBtn.textContent = '閉じる';
        elementBtn.addEventListener('click', () => {elementPopup.parentNode.removeChild(elementPopup);});

        elementPopup.appendChild(elementPopupContents);
        elementPopupMessage.forEach(element => {
            elementPopupContents.appendChild(element)
        })
        elementPopupContents.appendChild(elementBtnFrame);
        elementBtnFrame.appendChild(elementBtn);

        document.body.appendChild(elementPopup);
   }

}