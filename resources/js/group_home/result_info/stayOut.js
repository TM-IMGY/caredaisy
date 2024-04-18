
import CSRF_TOKEN from '../../lib/csrf_token.js';
import CustomAjax from '../../lib/custom_ajax.js';
import JapaneseCalendar from '../../lib/japanese_calendar.js';

/**
 * 外泊日登録タブに責任を持つクラス。
 */
export default class Stayout{
    constructor(facilityID)
    {
        this.elementID = 'tm_contents_5';
        this.elementBody = document.getElementById("stay_out_result_table_body");
        this.validationDisplayArea = document.getElementById("validateErrors");

        this.soId = document.querySelector("#so_id");

        this.startDate = document.querySelector('#so_start_date');
        this.startTime = document.querySelector('#so_start_time');
        this.mealStartMorning = document.querySelector('#so_meal_of_the_day_start_morning');
        this.mealStartLunch = document.querySelector('#so_meal_of_the_day_start_lunch');
        this.mealStartSnack = document.querySelector('#so_meal_of_the_day_start_snack');
        this.mealStartDinner = document.querySelector('#so_meal_of_the_day_start_dinner');
        this.endDate = document.querySelector('#so_end_date');
        this.endTime = document.querySelector('#so_end_time');
        this.mealEndMorning = document.querySelector('#so_meal_of_the_day_end_morning');
        this.mealEndLunch = document.querySelector('#so_meal_of_the_day_end_lunch');
        this.mealEndSnack = document.querySelector('#so_meal_of_the_day_end_snack');
        this.mealEndDinner = document.querySelector('#so_meal_of_the_day_end_dinner');

        this.reason1 = document.querySelector('#so_reason_for_stay_out_1');
        this.reason2 = document.querySelector('#so_reason_for_stay_out_2');
        this.reason3 = document.querySelector('#so_reason_for_stay_out_3');
        this.reason5 = document.querySelector('#so_reason_for_stay_out_5');
        this.reason4 = document.querySelector('#so_reason_for_stay_out_4');

        this.remarks = document.querySelector("textarea[name='remarks']");

        this.newBtn = document.getElementById('so_new_btn');
        this.saveBtn = document.getElementById('so_save_btn');
        this.deleteBtn = document.getElementById('so_delete_btn');

        this.facilityID = facilityID;
        this.facilityUserID = null;
        this.isActive = false;
        this.notification = null;
        this.selectedRecord = null;
        if (this.newBtn !== null){
            this.newBtn.addEventListener('click',this.newBtnClick.bind(this));
        }
        if (this.saveBtn !== null){
            this.saveBtn.addEventListener('click',this.saveBtnClick.bind(this));
        }
        if (this.deleteBtn !== null){
            this.deleteBtn.addEventListener('click',this.deleteBtnClick.bind(this));
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

        this.startDate.addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));
        this.endDate.addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));
    }

    addRecord(result)
    {
        this.elementBody.appendChild(this.createStayoutRow(result));

        // 1件目の履歴に色を付ける。
        this.selectedRecord = this.elementBody.firstElementChild;
        this.selectedRecord.classList.add('stay_select_record');

        // 1件目の履歴を選択状態にする。
        let trList = $("#stay_out_result_table_body").children();
        let targetId = trList[0].getAttribute('data-id');
        this.setEditData(targetId)
    }

    infoTableRowClick(e)
    {
        this.stayout.setEditData(this.id);
    }

    async setEditData(id)
    {
        return CustomAjax.send(
            'GET',
            '/group_home/result_info/stay_out/stay_out_detail?id=' + id,
            {'Content-Type':'application/json'},
            [],
            "reflectEditArea",
            this
        );
    }

    reflectEditArea(json)
    {
        this.formClear();

        this.soId.value = json.id;
        let meals = [
            "meal_of_the_day_start_morning",
            "meal_of_the_day_start_lunch",
            "meal_of_the_day_start_snack",
            "meal_of_the_day_start_dinner",
            "meal_of_the_day_end_morning",
            "meal_of_the_day_end_lunch",
            "meal_of_the_day_end_snack",
            "meal_of_the_day_end_dinner",
        ];
        for(var i=0; i < meals.length; i++){
            if(json[meals[i]] == 1){
                document.querySelector("#so_" + meals[i]).checked = true;
            }
        }

        let [startDate, startTime] = json.start_date.split(" ");
        let [endDate, endTime] = json.end_date.split(" ");
        this.startDate.value = startDate.replace(/-/g,'/');
        this.startTime.value = startTime;
        this.endDate.value = endDate.replace(/-/g,'/');
        this.endTime.value = endTime;

        document.getElementById("jaCalSOStartDate").innerText = JapaneseCalendar.toJacal(this.startDate.value);
        document.getElementById("jaCalSOEndDate").innerText = JapaneseCalendar.toJacal(this.endDate.value);    

        if (json.reason_for_stay_out > 0) {
            let elm = eval("this.reason" + json.reason_for_stay_out);
            elm.checked = true;
        }
        this.remarks.value = json.remarks;
    }

    createStayoutRow(result)
    {
        let stayOutRow = document.createElement("tr");

        let startMeal = [];
        let endMeal = [];
        if(result.meal_of_the_day_start_morning == 1){
            startMeal.push("朝食");
        }
        if(result.meal_of_the_day_start_lunch == 1){
            startMeal.push("昼食");
        }
        if(result.meal_of_the_day_start_snack == 1){
            startMeal.push("おやつ");
        }
        if(result.meal_of_the_day_start_dinner == 1){
            startMeal.push("夕食");
        }

        if(result.meal_of_the_day_end_morning == 1){
            endMeal.push("朝食");
        }
        if(result.meal_of_the_day_end_lunch == 1){
            endMeal.push("昼食");
        }
        if(result.meal_of_the_day_end_snack == 1){
            endMeal.push("おやつ");
        }
        if(result.meal_of_the_day_end_dinner == 1){
            endMeal.push("夕食");
        }

        let reasonForStayOut = "";
        if(result.reason_for_stay_out == 1){ reasonForStayOut = "外出"; }
        if(result.reason_for_stay_out == 2){ reasonForStayOut = "外泊"; }
        if(result.reason_for_stay_out == 3){ reasonForStayOut = "入院"; }
        if(result.reason_for_stay_out == 5){ reasonForStayOut = "入所(介護老人保健施設、介護医療院)"; }
        if(result.reason_for_stay_out == 4){ reasonForStayOut = "その他"; }

        let cellList = [];
        cellList.push(this.createCell(result.start_date, "so_table_cell"));
        // 履歴の「当日の欠食(開始日)」非表示
        // cellList.push(this.createCell(startMeal.join("/"), "so_table_cell"));
        cellList.push(this.createCell(result.end_date, "so_table_cell"));
        // 履歴の「当日の欠食(終了日)」非表示
        // cellList.push(this.createCell(endMeal.join("/"), "so_table_cell"));
        cellList.push(this.createCell(reasonForStayOut, "so_table_cell_reason"));
        cellList.push(this.createCell(result.remarks.length > 10 ? result.remarks.slice(0, 10) : result.remarks, "so_table_cell_remarks"));

        cellList.forEach(el=>stayOutRow.appendChild(el));

        stayOutRow.setAttribute("data-id", result.id);
        stayOutRow.setAttribute("dusk", 'stayout_row' + document.getElementById('stayout_table').rows.length);
        stayOutRow.addEventListener("click", {stayout: this, id: result.id, handleEvent: this.infoTableRowClick});

        if(result.stayout_now == true){
            let event = new Event("click");
            stayOutRow.dispatchEvent(event);
        }

        // レコードにクリックイベントを付与する
        stayOutRow.addEventListener('click', (event) => {
            if(this.selectedRecord){
                this.selectedRecord.classList.remove('stay_select_record');
            }
            this.selectedRecord = stayOutRow;
            this.selectedRecord.classList.add('stay_select_record');
        });

        return stayOutRow;
    }

    createCell(data, className=null){
        let cell = document.createElement('td');
        cell.textContent = data;
        cell.classList.add('caredaisy_table_cell');
        if(className){cell.classList.add(className);}
        return cell;
    }

    /**
     * 通知先のコールバック関数をセットする。
     * @param {Function} callBack 通知先となるコールバック関数。 
     * @return {void}
     */
    setNotification(callBack)
    {
        this.notification = callBack;
    }

    async syncWithServer()
    {
        await this.getUserInfo();
    }

    async callbackUserInfo (json) {
        if(json !== null){
            json.forEach(result => this.addRecord(result));
        }
    };

    async getUserInfo()
    {
        this.newBtnClick();
        while(this.elementBody.lastChild){
            this.elementBody.removeChild(this.elementBody.lastChild);
        }

        return CustomAjax.send(
            'GET',
            '/group_home/result_info/stay_out/user_info?facility_user_id=' + this.facilityUserID,
            {'Content-Type':'application/json'},
            [],
            "callbackUserInfo",
            this
        );
    }

    async setActive(isActive)
    {
        if(this.hasAllParam() && isActive){
            await this.syncWithServer();
        }else{
            this.formClear();
            while(this.elementBody.lastChild){
                this.elementBody.removeChild(this.elementBody.lastChild);
            }
        }
        this.isActive = isActive;
    }

    async setParam(param)
    {
        this.facilityUserID = 'facilityUserID' in param ? param.facilityUserID : this.facilityUserID;
        this.year = 'year' in param ? param.year : this.year;
        this.month = 'month' in param ? param.month : this.month;

        // setActiveとsetParamの処理がかぶっているので注意
        // 活性化している場合
        if(this.isActive && this.hasAllParam()){
            await this.syncWithServer();
        }else{
            this.formClear();
            while(this.elementBody.lastChild){
                this.elementBody.removeChild(this.elementBody.lastChild);
            }
        }
    }

    async newBtnClick()
    {
        this.formClear();
        this.clearRecordSelectionState();
        this.clearValidateDisplay();
    }

    /**
     * 保存ボタンのクリックイベント。
     * @return {Promise}
     */
    async saveBtnClick()
    {
        await this.save();
    }

    /**
     * 保存処理後のコールバック関数。
     * @param {Object} result 空配列固定なので注意する。
     * @return {void}
     */
    async callbackSave(result)
    {
        //エラーじゃなかったら実行（正常なら何かしら結果が返る）
        if (result){
            this.getUserInfo();
            // TODO: getUserInfo内でnewBtnClickが呼ばれて処理が重複している。
            this.newBtnClick();
            // TODO: サーバーから何も返されないため更新後のデータを送れず通知先で取り直すことになっている。
            await this.notification();
        }

        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
    }

    /**
     * 保存処理。
     * @return {Promise}
     */
    async save()
    {
        if(this.facilityUserID !== null && this.isActive) {
            this.clearValidateDisplay();

            let params = {}
            params['id'] = this.soId.value;
            params["facility_user_id"] = this.facilityUserID;
            params['start_date'] = this.startDate.value;
            params['start_time'] = this.startTime.value;

            //本来であればcheckedを判定してvalueを代入するのが自然だが、挙動は問題ないので現状はこのままにする(by中澤さん)
            params['meal_of_the_day_start_morning'] = this.mealStartMorning.checked;
            params['meal_of_the_day_start_lunch'] = this.mealStartLunch.checked;
            params['meal_of_the_day_start_snack'] = this.mealStartSnack.checked;
            params['meal_of_the_day_start_dinner'] = this.mealStartDinner.checked;
            params['end_date'] = this.endDate.value;
            params['end_time'] = this.endTime.value;
            params['meal_of_the_day_end_morning'] = this.mealEndMorning.checked;
            params['meal_of_the_day_end_lunch'] = this.mealEndLunch.checked;
            params['meal_of_the_day_end_snack'] = this.mealEndSnack.checked;
            params['meal_of_the_day_end_dinner'] = this.mealEndDinner.checked;

            let reason= document.querySelector("input[name='reason_for_stay_out']:checked");
            params['reason_for_stay_out'] = "";
            if (reason != null) {
                params['reason_for_stay_out'] = parseInt(reason.value);
            }

            params['remarks'] = this.remarks.value;

            return await CustomAjax.send(
                "POST",
                "/group_home/result_info/stay_out/save",
                {"Content-Type":"application/json", "X-CSRF-TOKEN":CSRF_TOKEN},
                params,
                "callbackSave",
                this
            );
        }
    }

    async validateDisplay(errorBody)
    {
        let createRow = (function(key, value){
            let record = document.createElement('li');
            let validationDisplayArea = document.getElementById("validateErrors");
            record.textContent = value;
            validationDisplayArea.appendChild(record);
        });

        errorBody = JSON.parse(errorBody);
        let errorList = errorBody.errors;
        Object.keys(errorList).map(key =>
            createRow(key, errorList[key])
        );
    }

    async clearValidateDisplay()
    {
        while(this.validationDisplayArea.lastChild){
            this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
        }
    }

    async deleteBtnClick()
    {
        if(this.facilityUserID !== null && this.isActive) {
            let id = this.soId.value;
            return CustomAjax.send(
                'DELETE',
                '/group_home/result_info/stay_out/delete?id=' + id,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                "deleteCallback",
                this
            );
        }
    }

    /**
     * @return {Promise}
     */
    async deleteCallback()
    {
        this.syncWithServer();
        this.newBtnClick();
        await this.notification();
    }

    hasAllParam()
    {
        return ![this.facilityUserID,this.year,this.month].includes(null);
    }

    formClear(){
        this.soId.value = "";
        this.startDate.value = "";
        document.getElementById("jaCalSOStartDate").innerText = null;
        this.startTime.value = "";
        this.endDate.value = "";
        document.getElementById("jaCalSOEndDate").innerText = null;
        this.endTime.value = "";
        this.mealStartMorning.checked = false;
        this.mealStartLunch.checked = false;
        this.mealStartSnack.checked = false
        this.mealStartDinner.checked = false;
        this.mealEndMorning.checked = false;
        this.mealEndLunch.checked = false;
        this.mealEndSnack.checked = false;
        this.mealEndDinner.checked = false;
        this.reason1.checked = false;
        this.reason2.checked = false;
        this.reason3.checked = false;
        this.reason4.checked = false;
        this.remarks.value = "";
    }

    /**
     * 履歴レコードの選択状態をクリアする
     * @return {void}
    */
    clearRecordSelectionState(){
        if(this.selectedRecord){
            this.selectedRecord.classList.remove('stay_select_record');
            this.selectedRecord = null;
        }
    }
}
