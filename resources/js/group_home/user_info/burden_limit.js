import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';
import Common from '../care_plan_info/common.js';

export default class BurdenLimit{
  constructor(){
    this.elementID = 'tm_contents_burden_limit';
    this.element = document.getElementById(this.elementID);
    this.historyTBody = document.getElementById('burden_limit_history_table_body');
    this.common = new Common();

    this.saveUrl = 'user_info/burden_limit/save';
    this.getBurdenLimitDataUrl = 'user_info/burden_limit/get_histories';
    this.selectedRecord = null;
    this.historyId = null;
    this.userInformation = {};
    this.status = false;
    this.updateFlg = false;

    this.startDate = document.getElementById('burden_limit_start_date');
    this.endDate = document.getElementById('burden_limit_end_date');
    this.foodLimit = document.getElementById('burden_limit_food_expenses');
    this.livingLimit = document.getElementById('burden_limit_living_expenses');
    this.burdenLimitBtn = document.getElementById('bl_button_block');

    this.validationDisplayArea = document.getElementById("validateErrors_burden_limit");

    // 新規登録ボタンにイベントを付与
    if (document.getElementById('burden_limit_register') !== null){
      document.getElementById('burden_limit_register').addEventListener('click', this.clickNewRegister.bind(this));
    }
    // 保存ボタンにイベントを付与
    if (document.getElementById('burden_limit_update') !== null){
      document.getElementById('burden_limit_update').addEventListener('click', this.checkUpdateAndDateDiff.bind(this));
    }

    // 保存ポップアップ内「はい」にイベント付与
    document.getElementById('updatabtn_burden_limit').addEventListener('click', this.sendSave.bind(this));
    // 保存ポップアップ内「いいえ」にイベント付与
    document.getElementById('cancelbtn_burden_limit').addEventListener('click', function(){
      this.chengeDisplayOfUpdatePopUp('none');
    }.bind(this));
    // 年確認保存ポップアップ内「はい」にイベント付与
    document.getElementById('updatabtn_burden_limit_yearpopup').addEventListener('click', this.sendSave.bind(this));
    // 年確認保存ポップアップ内「いいえ」にイベント付与
    // 閉じた後に有効終了日を選択状態にする
    document.getElementById('cancelbtn_burden_limit_yearpopup').addEventListener('click', function(){
      this.chengeDisplayOfYearDiffPopUp('none');
      this.endDate.focus();
    }.bind(this));

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

  /**
   * @param {bool} status 表示のブーリアン値
   */
  setActive(status){
    this.status = status;
    if(status && !this.isNull(this.userInformation.facilityUserID)){
        this.activate();
    }
  }

  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  setFacilityUser(user){
    this.userInformation = user;
    if(this.status &&  !this.isNull(this.userInformation.facilityUserID)){
      this.activate();
    }
  }

  createGetRequestParam(){
    return '?facility_user_id=' + this.userInformation.facilityUserID
  }

  async activate(){
    this.allClear();

    let response = "";
    response = await CustomAjax.get(
      'user_info/burden_limit/get_histories' + this.createGetRequestParam(),
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
    );
    let histories = await response.json();

    histories.forEach(history => {
      let record = this.creteHistoryRecord(history);
      this.clickHistoryRecord(record, history);
      this.addHistoryRecord(record);
      this.historyTBody.firstChild && this.historyTBody.firstChild.click();
    })
  }

  creteHistoryRecord(history){
    let record = document.createElement('tr');
    record.setAttribute('id',history.id)
    let historyTdItems = [
      {displayContent: this.changeDateFormat(history.start_date)},
      {displayContent: this.changeDateFormat(history.end_date)},
      {displayContent: history.food_expenses_burden_limit},
      {displayContent: history.living_expenses_burden_limit}
    ];

    Object.keys(historyTdItems).forEach((item) => {
      let td = document.createElement('td');
      td.textContent = historyTdItems[item].displayContent;
      record.appendChild(td);
    })

    // td要素にクラス付与
    Array.from(record.children).forEach((child)=>{
      child.className = 'text_data_burden_limit'
    })
    return record;
  }

  addHistoryRecord(record){
    this.historyTBody.appendChild(record);
  }

  /**
   * 履歴選択時処理
   * @param {HTMLElement} record
   * @param {object} history
   */
  clickHistoryRecord(record, history){
    //履歴から情報を取得するイベントを付与
    record.addEventListener('click',async function(){
      this.common.clearValidateDisplay(this.validationDisplayArea);
      this.updateFlg = true;
      if(this.selectedRecord){
        this.selectedRecordRemove();
      }
      this.selectedRecord = record;
      this.selectedRecord.classList.add("burden_limit_select_record");
      this.setData(history);
    }.bind(this))
  }

  /**
   * フォームに値をセット
   * @param {object} param
   */
  setData(param){
    this.historyId = param.id
		this.startDate.value = param.start_date.replace(/-/g, '/');
    this.endDate.value = param.end_date.replace(/-/g, '/');
    document.getElementById("jaCalBlStartDate").innerText = JapaneseCalendar.toJacal(param.start_date);
    document.getElementById("jaCalBlEndDate").innerText = JapaneseCalendar.toJacal(param.end_date);
    this.foodLimit.value = param.food_expenses_burden_limit;
    this.livingLimit.value = param.living_expenses_burden_limit;
  }

  allClear(){
    this.formValueClear();
    this.selectedRecordRemove();
    this.clearHistoryId();
    this.common.clearValidateDisplay(this.validationDisplayArea);
    this.clearHistoryTbody();
    this.resetUpdateFlg();
  }

  /**
   * フォームに入力されている値を初期化
   */
  formValueClear(){
    this.startDate.value = null;
    this.endDate.value = null;
    document.getElementById("jaCalBlStartDate").innerText = null;
    document.getElementById("jaCalBlEndDate").innerText = null;
    this.foodLimit.value = null;
    this.livingLimit.value = null;
  }

  /**
   * 履歴リストを初期化
   */
  clearHistoryTbody(){
    this.historyTBody.textContent = null;
  }

  selectedRecordRemove(){
    if (this.selectedRecord) {
      this.selectedRecord.classList.remove('burden_limit_select_record');
    }
  }

  clearHistoryId(){
    this.historyId = null;
  }

  resetUpdateFlg(){
    this.updateFlg = false;
  }

  /**
   * 新規登録ボタン押下時処理
   */
  async clickNewRegister(){
    this.formValueClear();
    this.selectedRecordRemove();
    this.clearHistoryId();
    this.resetUpdateFlg();
    this.common.clearValidateDisplay(this.validationDisplayArea);

    let response  = await CustomAjax.get(
      'user_info/burden_limit/get_user_info' + this.createGetRequestParam(),
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
    );
    let userInfo = await response.json();
    this.startDate.value = userInfo.start_date.replace(/-/g, '/');
    document.getElementById("jaCalBlStartDate").innerText = JapaneseCalendar.toJacal(userInfo.start_date);
    // 履歴が存在するなら最新履歴の適用終了日の次の日を適当開始日にセットする
    if (!this.isNull(userInfo.latest_history)) {
      let tomorrow = new Date(userInfo.latest_history.end_date);
      tomorrow.setDate(tomorrow.getDate()+ 1)
      let year = tomorrow.getFullYear()
      let month = (tomorrow.getMonth() + 1).toString().padStart(2, "0");
      let day = (tomorrow.getDate()).toString().padStart(2, "0");
      this.startDate.value = year + '/' + month + '/' + day;
      document.getElementById("jaCalBlStartDate").innerText
        = JapaneseCalendar.toJacal(year + '-' + month + '-' + day);
    }
  }

  isNull(value){
    return value === null;
  }

  isEmpty(value){
    return value === '';
  }

  // 保存・保存ポップアップ押下時処理
  async checkUpdateAndDateDiff(event){
		this.common.clearValidateDisplay(this.validationDisplayArea);
    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // 適用開始日と適用終了日の差が1年以上あるかのチェック
    let startCheck = new Date(this.startDate.value)
    startCheck.setFullYear(startCheck.getFullYear()+1)
    let year = startCheck.getFullYear()
    let month = (startCheck.getMonth() + 1).toString().padStart(2, "0");
    let day = (startCheck.getDate()).toString().padStart(2, "0");
    let limitYear = year + '/' + month + '/' + day;

    if(this.endDate.value > limitYear){
      return this.chengeDisplayOfYearDiffPopUp('block');
    }

    // 更新時のポップアップ表示
    if(this.updateFlg === true){
      return this.chengeDisplayOfUpdatePopUp('block');
    }

    this.sendSave();
  }

  // 年確認ポップアップの表示・非表示を切り替える
  chengeDisplayOfYearDiffPopUp(operation){
    document.getElementById('overflow_burden_limit_yearpopup').style.display = operation;
  }

  // 更新についてポップアップの表示・非表示を切り替える
  chengeDisplayOfUpdatePopUp(operation){
    document.getElementById('overflow_burden_limit').style.display = operation;
  }

  // データの保存を実行する
  sendSave(){
      let params = {
        facility_user_id: this.userInformation.facilityUserID,
        start_date: this.startDate.value,
        end_date: this.endDate.value,
        food_expenses_burden_limit: this.foodLimit.value,
        living_expenses_burden_limit: this.livingLimit.value,
    };

    if(!this.isNull(this.historyId)){
      params['id'] = this.historyId; 
    }

    if(this.isEmpty(params.end_date)){
      params.end_date = '2024/03/31';
    }

    CustomAjax.send(
			'POST',
			this.saveUrl,
			{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
			params,
			'callRegister',
			this
		);
  }

	callRegister(json){
		if(json !== void 0){
			this.chengeDisplayOfUpdatePopUp('none');
      this.chengeDisplayOfYearDiffPopUp('none');
      return this.activate();
		}
	}

	validateDisplay(errorBody){
    this.common.validateDisplay(errorBody, this.validationDisplayArea);

		this.chengeDisplayOfUpdatePopUp('none');
    this.chengeDisplayOfYearDiffPopUp('none');
	}

  changeDateFormat(dateString)
  {
    let date = new Date(dateString);
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, "0");
    let day = (date.getDate()).toString().padStart(2, "0");
    return year + '/' + month + '/' + day;
  }
}
