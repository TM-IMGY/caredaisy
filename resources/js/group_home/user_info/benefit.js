/**
 * 給付率タブで閲覧できるビュー
 */

import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';

export default class Benefit{
  constructor(){
    this.elementID = 'tm_contents_benefit';
    this.element = document.getElementById(this.elementID);
    this.historyTBody = document.getElementById('benefit_history_table_body');

    this.userId = "";
    this.facilityUserData = "";

    this.saveUrl = 'user_info/benefit/save';
    this.getBenefiDataUrl = 'user_info/benefit/benefit_history';

    this.selectedRecord = null;
    this.notificationList = [];

    this.benefitInformationId = document.getElementById('benefit_information_id')
    this.benefitType = document.getElementById('benefit_type');
    this.benefitRate = document.getElementById('benefit_rate');
    this.percent = document.getElementById('percent');
    this.effectiveStartDate = document.getElementById('benefit_effective_start_date');
    this.expiryDate = document.getElementById('benefit_expiry_date');
    this.validationDisplayArea = document.getElementById("validateErrorsBenefit");
    this.messageArea = document.getElementById('benefit_message');
    this.benefitBtn = document.getElementById('button_block');

    // 新規登録ボタンにイベントを付与
    this.createButton = document.getElementById('benefit_register');
    if (this.createButton !== null){
      this.createButton.addEventListener('click', this.formClear.bind(this));
    }
    // 保存ボタンにイベントを付与
    this.saveButton = document.getElementById('benefit_update');
    if (this.saveButton !== null){
      this.saveButton.addEventListener('click', this.postJson.bind(this));
    }
    // 保存ポップアップ内「はい」にイベント付与
    document.getElementById('updatabtn_benefit').addEventListener('click', this.postJson.bind(this));
    // 保存ポップアップ内「いいえ」にイベント付与
    document.getElementById('cancelbtn_benefit').addEventListener('click', function(){
      document.getElementById('overflow_benefit').style.display = 'none';
    });
    // 年確認保存ポップアップ内「はい」にイベント付与
    document.getElementById('updatabtn_benefit_yearpopup').addEventListener('click', this.postJson.bind(this));
    // 年確認保存ポップアップ内「いいえ」にイベント付与
    // 閉じた後に有効終了日を選択状態にする
    document.getElementById('cancelbtn_benefit_yearpopup').addEventListener('click', function(){
      this.expiryDate = document.getElementById('benefit_expiry_date');
      document.getElementById('overflow_benefit_yearpopup').style.display = 'none';
      this.expiryDate.focus();
    });

    this.userInformation = {};
    this.status = false;

    // 負担割合から給付率を表示
    this.benefitRate.addEventListener('change',function(){
      let burden = this.benefitRate.value;
      this.percent.textContent = burden;
    }.bind(this));

    this.effectiveStartDate.addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));
    this.expiryDate.addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));

    this.interactiveElements = [this.createButton, this.saveButton, this.benefitType, this.benefitRate, this.effectiveStartDate, this.expiryDate];
  }

  /**
  * @param {bool} status 表示のブーリアン値
  */
  setActive(status){
    this.status = status;

    this.enabledAll();
    this.clearMessage();
    if(status && this.userInformation.facilityUserID != null){
        this.activeBenefit();
        this.deactivateIfInsuredNoH(this.userInformation.facilityUserID);
    }
  }

  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  setFacilityUser(user){
    this.userInformation = user;

    if (user.facilityUserID == null) {
      this.benefitBtn.style.setProperty('visibility','hidden');
    } else {
      // 新規登録・保存ボタンを表示
      this.benefitBtn.style.removeProperty('visibility');
    }

    this.enabledAll();
    this.clearMessage();
    if(this.status && user.facilityUserID != null){
      this.activeBenefit();
      this.deactivateIfInsuredNoH(this.userInformation.facilityUserID);
    }else{
      this.historyTBody.textContent = null;
      this.formClear();
    }
  }

  async activeBenefit(){
    this.facilityUserData = this.userInformation;

    let json = JSON.stringify(this.userInformation); // ex. {"facilityUserID":11,"userName":"青森申吾"}
    let getId = JSON.parse(json); // ex. object facilityUserID: 11 userName: "青森申吾"

    this.userId = getId.facilityUserID;


    // 履歴リストを初期化
    this.historyTBody.textContent = null;

    this.formClear();

    let createdList = "";
    let fUserId = {'facility_user_id':this.userId}

    createdList = await CustomAjax.post(this.getBenefiDataUrl,{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},fUserId);

    Object.keys(createdList).forEach((key) => {
      let record = document.createElement('tr');
      let createdCell = document.createElement('td');
      let typeCell = document.createElement('td');
      let rateCell = document.createElement('td');
      let percentCell = document.createElement('td');
      let startCell = document.createElement('td');
      let expiryCell = document.createElement('td');

      let benefitInformationId = createdList[key]['benefit_information_id'];
      let benefitName = this.benefitTypeCheck(createdList[key]['benefit_type']);

      record.setAttribute('data-benefit-information-id', benefitInformationId);
      typeCell.textContent = benefitName;
      createdCell.textContent = this.changeDateFormat(createdList[key]['created_at'])
      if(createdList[key]['benefit_rate'] == '70'){
        rateCell.textContent = '3割'
      }else if(createdList[key]['benefit_rate'] == '80'){
        rateCell.textContent = '2割'
      }else if(createdList[key]['benefit_rate'] == '90'){
        rateCell.textContent = '1割'
      }else if(createdList[key]['benefit_rate'] == '100'){
        rateCell.textContent = '0割'
      }else{
        rateCell.textContent = '10割'
      };
      percentCell.textContent = createdList[key]['benefit_rate']+'%';

      if(createdList[key]['effective_start_date']){
        startCell.textContent = createdList[key]['effective_start_date'].replace(/-/g,'/');
      }

      if(createdList[key]['expiry_date']){
        expiryCell.textContent = createdList[key]['expiry_date'].replace(/-/g,'/');
      }

      record.appendChild(typeCell);
      record.appendChild(rateCell);
      record.appendChild(percentCell);
      record.appendChild(startCell);
      record.appendChild(expiryCell);

      // td要素にクラス付与
      Array.from(record.children).forEach((child)=>{
        child.className = 'text_data_benefit'
      })

      // 最新の履歴情報をフォームにセット
      if(key == '0'){
        this.setData(createdList[key]);
        this.selectedRecord = record;
        this.selectedRecord.classList.add("benefit_select_record");
      }

      //レコードに選択イベントを付与
      record.addEventListener('click',(event)=>{
        if(this.selectedRecord){this.selectedRecord.classList.remove('benefit_select_record');}
        this.selectedRecord = record;
        this.selectedRecord.classList.add("benefit_select_record");
      });

      //履歴から情報を取得するイベントを付与
      record.addEventListener('click',async function(event) {
        // 権限チェックのために'facility_user_id'を追加
        let benefitInformationId = $(event.target).parent().data('benefitInformationId');
        var requestParams = {'benefit_information_id': benefitInformationId, 'facility_user_id': this.userId};
        let result = await CustomAjax.post('user_info/benefit/benefit_data', {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN}, requestParams);
        this.setData(result);
      }.bind(this));

      this.historyTBody.appendChild(record);
    });
  }

  /**
   * フォームに値をセット
   * @param {object} param
   */
  setData(param)
  {
		this.benefitInformationId.value = param['benefit_information_id'];
		this.benefitType.options[param.benefit_type].selected = true;
		document.querySelector("select[name='benefit_rate'] option[value='"+ param['benefit_rate'] +"']").selected = true;
		this.percent.textContent = param['benefit_rate'];
		this.effectiveStartDate.value = param['effective_start_date'].replace(/-/g, '/');
    this.expiryDate.value = param['expiry_date'].replace(/-/g, '/');
    document.getElementById("jaCalBeStartDate").innerText = JapaneseCalendar.toJacal(param['effective_start_date']);
    document.getElementById("jaCalBeEndDate").innerText = JapaneseCalendar.toJacal(param['expiry_date']);
  }

  // フォームに入力されている値を初期化
  formClear(){
		this.clearValidateDisplay();
    this.benefitInformationId.value = null;
    this.benefitType.options[0].selected = true;
    this.benefitRate.options[0].selected = true;
    this.percent.textContent = null;
    this.effectiveStartDate.value = null;
    this.expiryDate.value = null;
    document.getElementById("jaCalBeStartDate").innerText = null;
    document.getElementById("jaCalBeEndDate").innerText = null;

    if (this.selectedRecord) {
      this.selectedRecord.classList.remove('benefit_select_record');
    }
  }

  // 保存・保存ポップアップ押下時処理
  async postJson(event){
		this.clearValidateDisplay();
    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;
    let post;
    let benefitInformationId = this.benefitInformationId.value;

    if(!benefitInformationId){
        post = 'register';
    }else{
        post = 'update';
    }

    let postData = {
        //権限チェックのために'facility_user_id'だけ改修
        'facility_user_id': this.userId,
        'benefit_type': this.benefitType.value,
        'benefit_rate': this.benefitRate.value,
        'effective_start_date': this.effectiveStartDate.value,
        'expiry_date': this.expiryDate.value,
        'post_type': post
    };

    if(benefitInformationId !== ''){
      postData['benefit_information_id'] = benefitInformationId
    }

    // 必須項目のエラーの有無を確認
    let checkResultRes = await this.saveValuesCheck(postData);

    // 更新時の保存ボタンを押された場合、checkResultResがtrueなら通常の更新ポップアップ表示
    // falseなら年確認更新ポップアップ表示
    if(event.target.id === 'benefit_update' && post === 'update' && checkResultRes === true){
      return document.getElementById('overflow_benefit').style.display = 'block';
    }else if(event.target.id === 'benefit_update' && checkResultRes === false){
      return document.getElementById('overflow_benefit_yearpopup').style.display = 'block';
    }

		await CustomAjax.send(
			'POST',
			this.saveUrl,
			{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
			postData,
			'callRegister',
			this
		);
  }

	callRegister(json){
		if(json !== void 0){
			document.getElementById('overflow_benefit').style.display = 'none';
      document.getElementById('overflow_benefit_yearpopup').style.display = 'none';
      return this.setFacilityUser(this.facilityUserData);
		}
	}

	validateDisplay(errorBody){
		let createRow = (function(key, value){
      let record = document.createElement('li');
      let validationDisplayArea = document.getElementById("validateErrorsBenefit");
      record.textContent = value;
      validationDisplayArea.appendChild(record);
    });

		errorBody = JSON.parse(errorBody);
		let errorList = errorBody.errors;
    Object.keys(errorList).map(key =>
      createRow(key, errorList[key])
    );
		document.getElementById('overflow_benefit').style.display = 'none';
    document.getElementById('overflow_benefit_yearpopup').style.display = 'none';
	}

	clearValidateDisplay()
  {
      while(this.validationDisplayArea.lastChild){
          this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
      }
  }

  changeDateFormat(dateString)
  {
    let date = new Date(dateString);
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, "0");
    let day = (date.getDate()).toString().padStart(2, "0");
    return year + '/' + month + '/' + day;
  }

  // 負担段階の設定
  // ハードコーディングになっているので方法は要検討
  benefitTypeCheck(int){
    let value
    switch(int){
      case 1:
        value = '介護保険負担割合証';
        break;

      case 2:
        value = '給付制限';
        break;

      case 3:
        value = '特例措置';
        break;

      default:
        value = ''
        break;
    }
    return value;
  }

  async saveValuesCheck(params)
  {
    let res = await CustomAjax.get(
      'user_info/benefit/values_check_result' + this.setCheckValues(params),
      {'X-CSRF-TOKEN':CSRF_TOKEN},
    );

    let data = await res.json();

    return data;

  }

  setCheckValues(params)
  {
    let paramDatas = '?benefit_type='
      + params['benefit_type']
      + '&benefit_rate='
      + params['benefit_rate']
      + '&effective_start_date='
      + params['effective_start_date']
      + '&expiry_date='
      + params['expiry_date']
      + '&facility_user_id='
      + params['facility_user_id']
      + '&post_type='
      + params['post_type']

    // benefit_information_idがある場合、paramDatasに追加
    if(params['benefit_information_id']){
      paramDatas += '&benefit_information_id=' + params['benefit_information_id'];
    }

    return paramDatas;

  }
  
  /**
   * 対象の被保険者番号を取得する
   * 
   * @param {number} facilityUserId - 利用者ID
   * @return {promise} 被保険者番号
   */
  async getInsuredNo(facilityUserId) {
    try {
      let res = await CustomAjax.get(
        'user_info/benefit/get_insured_no?facility_user_id=' + facilityUserId,
        {'X-CSRF-TOKEN': CSRF_TOKEN},
      );
      let insuredNo = await res.json();
      return insuredNo;
    }
    catch (err) {
      return '';
    }
  }

  /**
   * 全てのボタン、入力フォームを活性化する
   */
  enabledAll() {
    this.interactiveElements.forEach((interactiveElement) => {
      interactiveElement.disabled = false;
    });
  }

  /**
   * 全てのボタン、入力フォームを非活性化する
   */
  disabledAll() {
    this.interactiveElements.forEach((interactiveElement) => {
      interactiveElement.disabled = true;
    });
  }

  /**
   * 通常のメッセージを表示する
   * 
   * @param {string[]} messages - 表示テキスト
   */
  displayMessage(messages) {
    this.clearMessage();

    messages.forEach(message => {
      let record = document.createElement('li');
      record.textContent = message;
      this.messageArea.appendChild(record);
    });
	}

  /**
   * 表示されている全ての通常のメッセージを削除する
   */
  clearMessage() {
    while(this.messageArea.lastChild){
      this.messageArea.removeChild(this.messageArea.lastChild);
    }
  }

  /**
   * 被保険者番号が"H"から始まる場合、画面を無効化する
   * 
   * @param {number} facilityUserId - 利用者ID
   */
  async deactivateIfInsuredNoH(facilityUserId) {
    const INSURED_NO_H = 'H'
    const INSURED_NO_H_MSG = '当該利用者は給付率情報の入力が不要です。'
    
    let InsuredNo =  await this.getInsuredNo(facilityUserId);   
    if (InsuredNo.charAt(0) === INSURED_NO_H) {
      this.displayMessage([INSURED_NO_H_MSG]);
      this.disabledAll();
    }
  }
}
