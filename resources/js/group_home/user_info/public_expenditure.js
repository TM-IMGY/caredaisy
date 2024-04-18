import CSRF_TOKEN from '../../lib/csrf_token.js';
import CustomAjax from '../../lib/custom_ajax.js';
import JapaneseCalendar from '../../lib/japanese_calendar.js';
import PopUp from '../../lib/pop_up';
import PublicExpense from './public_expense.js';

// TODO: 利口なUI。
const SELF_PAY_NUMBER = [12, 15, 25, 54];

/**
 * 公費情報タブ。
 */
export default class PublicExpenditure{
  /**
   * @param {Number} facilityId 事業所ID
   */
  constructor(facilityId){
    this.POP_UP_MESSAGES = [
      '選択した情報を基に表示しております。',
      '訂正がある場合は編集し保存してください。'
    ];

    this.facilityId = facilityId;

    this.elementID = 'tm_contents_public_expenditure';
    this.element = document.getElementById(this.elementID);
    this.elementBearerNumber = document.getElementById('bearer_number');
    // TODO: リリースから外れるため一時除外している。
    // this.elementCopyButton = document.getElementById('public_expense_copy');
    this.elementFormButtons = document.getElementById('public_expenditure_button_block');
    this.elementPublicExpenseId = document.getElementById('public_expense_information_id');
    this.elementRegisterButton = document.getElementById('expenditure_register');
    this.elementRecordTBody = document.getElementById('public_expenditure_history_table_body');

    this.facilityUserId = null;
    this.isActive = false;
    this.selectedRecord = null;

    this.recipientNumber = document.getElementById('recipient_number');
    this.confirmationMedicalInsuranceDate = document.getElementById('confirmation_medical_insurance_date');
    this.publicExpenseEffectiveStartDate = document.getElementById('public_expense_effective_start_date');
    this.publicExpenseExpiryDate = document.getElementById('public_expense_expiry_date');
    this.legalName = document.getElementById('legal_name');
    this.legalNameDisplay = document.getElementById('legal_name_display');
    this.validationDisplayArea = document.getElementById("validateErrorsPublicExpenditure");
    this.overflowPublicExpenditure = document.getElementById('overflow_public_expenditure');
    this.overflowPublicExpenditureYearpopup = document.getElementById('overflow_public_expenditure_yearpopup');
    this.amountBornePerson = document.getElementById('amount_borne_person');

    this.updateData = "";
    this.resultData = null;

    this.getPublicSpending = 'user_info/public_expenditure/get_public_spending';
    this.getExpenditureDataUrl = 'user_info/public_expenditure/public_expenditure_history';
    this.saveUrl = 'user_info/public_expenditure/save';

    this.notificationList = [];

    // 新規登録ボタンにイベントを付与する。
    if (this.elementRegisterButton !== null){
      this.elementRegisterButton.addEventListener('click', this.eventRegisterButton.bind(this));
    }

    // // コピーして登録ボタンにイベントを付与する。
    // if (this.elementCopyButton !== null){
    //   this.elementCopyButton.addEventListener('click', this.eventCopyButton.bind(this));
    // }

    // 保存ボタンにイベントを付与
    if (document.getElementById('expenditure_update') !== null){
      document.getElementById('expenditure_update').addEventListener('click', this.postJson.bind(this));
    }

    // 保存ポップアップ内「はい」にイベント付与
    document.getElementById('updatabtn_public_expenditure').addEventListener('click', this.update.bind(this));

    // 保存ポップアップ内「いいえ」にイベント付与
    document.getElementById('cancelbtn_public_expenditure').addEventListener('click', function(){
      this.overflowPublicExpenditure.style.display = 'none';
    }.bind(this));

    // 年確認保存ポップアップ内「はい」にイベント付与
    document.getElementById('updatabtn_public_expenditure_yearpopup').addEventListener('click', this.update.bind(this));

    // 年確認保存ポップアップ内「いいえ」にイベント付与
    // 閉じた後に有効終了日を選択状態にする
    document.getElementById('cancelbtn_public_expenditure_yearpopup').addEventListener('click', function(){
      this.publicExpenseExpiryDate = document.getElementById('public_expense_expiry_date')
      this.overflowPublicExpenditureYearpopup.style.display = 'none';
      this.publicExpenseExpiryDate.focus();
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

    // キーによる日付入力イベント
    this.publicExpenseEffectiveStartDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    this.publicExpenseExpiryDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    this.confirmationMedicalInsuranceDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))

    // 負担者区分情報を取得
    this.bearerClassificationList = {};
    CustomAjax.send(
      'get',
      this.getPublicSpending,
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      '',
      'storagePublicSpending',
      this);

    // 負担者番号入力時処理
    this.elementBearerNumber.addEventListener('change',this.insertBearerClassification.bind(this));
  }

  /**
   * コピーして登録ボタンを再描画する。
   * @returns {void}
   */
  redrawCopyButton(){
    // 履歴テーブルのレコードが選択されていない場合。
    if (this.selectedRecord === null) {
      // コピーして登録ボタンを非活性化する。
      // this.elementCopyButton.classList.add('public_expense_copy_inactive');
    } else {
      // this.elementCopyButton.classList.remove('public_expense_copy_inactive');
    }
  }

  /**
   * テーブルのレコードをクリックする。
   * @param {Element} record 対象のレコード。
   * @returns {Promise}
   */
  async clickRecord(record){
    // レコードから公費のIDを取得する。
    let id = record.getAttribute('data-public-expense-id');

    // 公費を確保するための変数。
    let publicExpense = null;

    try {
      // 公費をサーバーにリクエストする。
      let params = new URLSearchParams({
        public_expense_information_id : id
      });
      let response = await CustomAjax.get('/group_home/service/public_expense/get?' + params.toString());

      publicExpense = await response.json();
    } catch (error) {
      // TODO: エラー表示方法が決まっていない。
    }

    // レコードを選択する。
    this.selectRecord(record);

    // フォームにデータをセットする。
    this.setFormData(new PublicExpense(
      publicExpense.amount_borne_person,
      publicExpense.bearer_number,
      publicExpense.confirmation_medical_insurance_date,
      publicExpense.effective_start_date,
      publicExpense.expiry_date,
      publicExpense.legal_name,
      publicExpense.public_expense_information_id,
      publicExpense.recipient_number
    ));

    // コピーして登録ボタンを再描画する。
    this.redrawCopyButton();

    // バリデーション表示をクリアする。
    this.clearValidateDisplay();
  }

  /**
   * コピーして登録ボタンイベント。
   */
  async eventCopyButton(){
    // 公費次回分を確保する変数。
    let next = null;
    try {
      // 公費の次回分を取得する。
      let params = new URLSearchParams({public_expense_information_id : this.elementPublicExpenseId.value});
      let response = await CustomAjax.get('/group_home/service/public_expense_next/get?' + params.toString());
      next = await response.json();
    } catch (error) {
      // TODO: エラーを表示するUIが存在しない。
    }

    // 公費の次回分を取得できた場合。
    if (next !== null) {
      // 履歴テーブルの選択を解除する。
      this.selectRecord(null);
      
      // フォームに値をセットする。
      let publicExpenseInformationId = null;
      this.setFormData(new PublicExpense(
        next.amount_borne_person,
        next.bearer_number,
        next.confirmation_medical_insurance_date,
        next.effective_start_date,
        next.expiry_date,
        next.legal_name,
        publicExpenseInformationId,
        next.recipient_number
      ));

      // コピーして登録ボタンを再描画する。
      this.redrawCopyButton();

      // バリデーションメッセージをクリアする。
      this.clearValidateDisplay();

      // ポップアップを出す。
      new PopUp(this.POP_UP_MESSAGES);
    }
  }

  /**
   * 新規登録ボタンイベント。
   * @returns {void}
   */
  async eventRegisterButton(){
    this.selectRecord(null);
    this.formClear();
    this.redrawCopyButton();
    this.clearValidateDisplay();
  }

  /**
   * 施設利用者の公費の記録テーブルを再描画する。
   */
  async redrawRecordTable(){
    this.elementRecordTBody.textContent = null;

    this.selectedRecord = null;

    // 履歴リストのデータを取得
    // TODO: postの意味がない。
    let tableDataList = await CustomAjax.post(
      this.getExpenditureDataUrl,
      {
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':CSRF_TOKEN
      },
      { 'facility_user_id': this.facilityUserId }
    );

    // 取得したデータから記録テーブルのレコードを作成する。
    // TODO: 作成処理は別メソッドに切り離す。
    Object.keys(tableDataList).forEach((key) => {
      let record = document.createElement('tr');
      let createdCell = document.createElement('td');
      let bearerNumberCell = document.createElement('td');
      let recipientNumberCell = document.createElement('td');
      let legalNameCell = document.createElement('td');
      let startCell = document.createElement('td');
      let expiryCell = document.createElement('td');
      let remarksCell = document.createElement('td');

      let bearerNumber = tableDataList[key]['bearer_number'];
      createdCell.textContent = this.changeDateFormat(tableDataList[key]['confirmation_medical_insurance_date']);
      bearerNumberCell.textContent = bearerNumber;
      recipientNumberCell.textContent = tableDataList[key]['recipient_number'];
      legalNameCell.textContent = this.insertLegalName(bearerNumber);

      if (tableDataList[key]['effective_start_date']) {
        startCell.textContent = tableDataList[key]['effective_start_date'].replace(/-/g,'/');
      }

      if (tableDataList[key]['expiry_date']) {
        expiryCell.textContent = tableDataList[key]['expiry_date'].replace(/-/g,'/');
      }
      let legalNum = bearerNumber.slice(0,2);
      // TODO: 利口なUI。
      if (SELF_PAY_NUMBER.includes(Number(legalNum))){
        remarksCell.textContent = tableDataList[key]['amount_borne_person'].toLocaleString() + "円";
      } else {
        remarksCell.textContent = 'ー';
      }

      record.appendChild(bearerNumberCell);
      record.appendChild(recipientNumberCell);
      record.appendChild(legalNameCell);
      record.appendChild(startCell);
      record.appendChild(expiryCell);
      record.appendChild(createdCell);
      record.appendChild(remarksCell);

      // td要素にクラス付与
      Array.from(record.children).forEach((child)=>{
        child.className = 'text_data_public_expenditure';
      });

      // レコードにIDをキャッシュする。
      // TODO: tableDataList を取りまわすのは構造が掴めないので止める。
      record.setAttribute('data-public-expense-id', tableDataList[key]['public_expense_information_id']);

      // レコードにイベントを付与する。
      record.addEventListener('click', this.clickRecord.bind(this, record));

      this.elementRecordTBody.appendChild(record);
    });
  }

  /**
   * 履歴テーブルのレコードを選択する。
   * @param {Element} record 対象のレコード。nullの場合は解除になる。
   */
  selectRecord(record){
    if(this.selectedRecord){
      this.selectedRecord.classList.remove('public_expenditure_select_record');
    }

    if(record !== null){
      record.classList.add("public_expenditure_select_record");
    }

    this.selectedRecord = record;    
  }

  /**
   * フォームに値をセットする。
   * @param {PublicExpense} publicExpense
   */
  setFormData(publicExpense){
    // ID。
    this.elementPublicExpenseId.value = publicExpense.getPublicExpenseInformationId();

    // 負担者番号。
    let bearerNumber = publicExpense.getBearerNumber();
    this.elementBearerNumber.value = bearerNumber;

    // 受給者番号。
    this.recipientNumber.value = publicExpense.getRecipientNumber();

    // 公費情報確認日。
    let confirmationDate = publicExpense.confirmationMedicalInsuranceDate;
    this.confirmationMedicalInsuranceDate.value = confirmationDate ? confirmationDate.replace(/-/g, '/') : null;

    // 有効開始日。
    let effectiveStartDate = publicExpense.getEffectiveStartDate();
    this.publicExpenseEffectiveStartDate.value = publicExpense.getEffectiveStartDate().replace(/-/g, '/');

    // 有効終了日。
    let expiryDate = publicExpense.expiryDate;
    this.publicExpenseExpiryDate.value = expiryDate ? expiryDate.replace(/-/g, '/') : null;

    // 法別番号。
    this.legalName.value = this.insertLegalName(bearerNumber);

    // 本人支払い額。
    this.amountBornePerson.value = publicExpense.getAmountBornePerson();

    document.getElementById("jaCalPEStartDate").innerText = JapaneseCalendar.toJacal(effectiveStartDate);
    document.getElementById("jaCalPEEndDate").innerText = JapaneseCalendar.toJacal(expiryDate);
    document.getElementById("jaCalPubExpenditureDate").innerText = JapaneseCalendar.toJacal(confirmationDate);

    if (this.insertLegalName(bearerNumber)) {
      this.legalNameDisplay.innerHTML = this.insertLegalName(bearerNumber);
    } else {
      this.legalNameDisplay.innerHTML = "&nbsp;";
    }

    // TODO: ここだけ書き方が統一されていない。
    let legalNum = publicExpense.bearerNumber.slice(0,2);
    if(SELF_PAY_NUMBER.includes(Number(legalNum))){
      $('#amount_borne_person').prop('disabled', false);
    }else{
      $('#amount_borne_person').val(0);
      $('#amount_borne_person').prop('disabled', true);
    }
  }

  /**
   * @param {bool} isActive アクティブかどうか。
   */
  async setActive(isActive){
    this.isActive = isActive;
    await this.redraw();
  }

  /**
   * 施設利用者をセットする。
   * TODO: userオブジェクトの構造が保証されていない。
   * @param {Object} user {facilityUserID: string, userName: string}
   * @returns {void}
   */
  async setFacilityUser(user){
    this.facilityUserId = user.facilityUserID;
    await this.redraw();
  }

  /**
   * タブ全体を再描画する。
   */
  async redraw(){
    if(this.isActive){
      // 履歴テーブルを再描画する。
      await this.redrawRecordTable();

      // 履歴テーブルのレコードを全て取得する。
      let children = this.elementRecordTBody.children;

      // レコードが存在する場合。
      if (children.length > 0) {
        // 先頭をクリックする。
        children[0].click();
      } else {
        this.formClear();
      }

      // 施設利用者がない場合。
      if (this.facilityUserId === null) {
        // 新規登録、コピーして登録、保存ボタンを非表示にする。
        this.elementFormButtons.style.setProperty('visibility','hidden');
      } else {
        this.elementFormButtons.style.removeProperty('visibility');
      }

      // 公費情報が選択されていない場合。
      this.redrawCopyButton();

      // フォームバリデーションメッセージをクリアする。
      this.clearValidateDisplay();
    }
  }

  // フォームに入力されている値を初期化
  formClear(){
    this.elementPublicExpenseId.value = null;
    let input_data = document.querySelectorAll('.public_expenditure_input');
    let select_data = document.querySelectorAll('.public_expenditure_select');
    Array.prototype.slice.call(input_data).forEach(data => {data.value = null;})
    Array.prototype.slice.call(select_data).forEach(data => {data.options[0].selected = true;})
    this.publicExpenseEffectiveStartDate.value = null;
    this.publicExpenseExpiryDate.value = null;
    this.confirmationMedicalInsuranceDate.value = null;
    let jaCalAll = this.element.querySelectorAll('[id^="jaCal"]');
    jaCalAll.forEach(e =>{ e.innerHTML = ""; });
    this.legalNameDisplay.innerHTML = "&nbsp;";
    $('#amount_borne_person').val(0);
    $('#amount_borne_person').prop('disabled', true);
  }

  // 新規登録・保存押下時処理
  async postJson(event){
    this.clearValidateDisplay();
    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;
    let update;
    let publicExpenseInformationId = this.elementPublicExpenseId.value;

    if(publicExpenseInformationId){
      update = true;
    }else{
      update = false;
    }

    //権限チェックのために'facilityUserID'だけ改修
    let postData = {
        'facility_user_id': this.facilityUserId,
        'bearer_number': this.elementBearerNumber.value,
        'recipient_number': this.recipientNumber.value,
        'confirmation_medical_insurance_date': this.confirmationMedicalInsuranceDate.value,
        'food_expenses_burden_limit': null,
        'living_expenses_burden_limit': null,
        'outpatient_contribution': null,
        'hospitalization_burden': null,
        'application_classification': null,
        'special_classification': null,
        'effective_start_date': this.publicExpenseEffectiveStartDate.value,
        'expiry_date': this.publicExpenseExpiryDate.value,
        'update_type': update,
        'amount_borne_person': this.amountBornePerson.value ? this.amountBornePerson.value : 0
    };

    if(publicExpenseInformationId !== ''){
      postData['public_expense_information_id'] = publicExpenseInformationId;
    }

    // 必須項目のエラーの有無を確認
    let checkResultRes = await this.saveValuesCheck(postData);

    // 更新時かつcheckResultResがtrueなら通常の更新ポップアップを表示
    // falseなら年確認更新ポップアップ表示
    if(update === true && checkResultRes === true){
      this.updateData = postData;
      return this.overflowPublicExpenditure.style.display = 'block';
    }else if(checkResultRes === false){
      this.updateData = postData;
      return this.overflowPublicExpenditureYearpopup.style.display = 'block';
    }

    await CustomAjax.send(
      'POST',
      this.saveUrl,
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      postData,
      'callRegister',
      this
    );

    if(this.resultData){
      this.overflowPublicExpenditure.style.display = 'none';
      this.overflowPublicExpenditureYearpopup.style.display = 'none';
      await this.redraw();
    };

  }

  callRegister(json){
    this.resultData = null;
    if(json !== void 0){
        this.resultData = json
    }
  }

  // 更新用処理
  async update(){
    await CustomAjax.send(
      'POST',
      this.saveUrl,
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      this.updateData,
      'callRegister',
      this
    );

    this.overflowPublicExpenditure.style.display = 'none';
    this.overflowPublicExpenditureYearpopup.style.display = 'none';

    if(this.resultData){
      await this.redraw();
    };
  }

  storagePublicSpending(json){
    this.resultData = null;
    if(json !== void 0){
        this.bearerClassificationList = json;
    }
  }

  callInsertBearerClassification(json){
    this.clearValidateDisplay();
    let val = this.elementBearerNumber.value.slice(0,2);
    let legal_name = null;
    json.service.service_type.public_spending.forEach((key) =>{
      if (key.legal_number == val){
        legal_name = key.legal_name;
      }
    })

    this.legalName.value = legal_name;
    if (legal_name){
      this.legalNameDisplay.innerHTML = legal_name;
    }else{
      this.validateDisplay('{"errors":{"0":"該当する公費がありません。負担者番号を見直してください"}}')
      this.legalNameDisplay.innerHTML = "&nbsp;";
    }

    // 公費本人負担ありの法別番号かどうか
    if(SELF_PAY_NUMBER.includes(Number(val))){
      $('#amount_borne_person').prop('disabled', false);
    }else{
      $('#amount_borne_person').val(0);
      $('#amount_borne_person').prop('disabled', true);
    }
  }

  // 負担者番号から負担者区分を取得
  async insertBearerClassification(){
    let postData = {
      'facility_user_id': this.facilityUserId,
      'facility_id': this.facilityId
    };
    await CustomAjax.send(
      'POST',
      'user_info/public_expenditure/public_expending_cheked_data',
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      postData,
      'callInsertBearerClassification',
      this
    );
  }

  /**
   * TODO: 賢いUIになっている。
   * @returns 
   */
  insertLegalName(num){
    let val,legal_name
    val = num.slice(0,2);
    Object.keys(this.bearerClassificationList).forEach((key) =>{
      if(this.bearerClassificationList[key].legal_number == val){
        legal_name = this.bearerClassificationList[key].legal_name;
      }
    });
    if(!legal_name){ legal_name = null;}
    return legal_name;
  }

  validateDisplay(errorBody){
    let createRow = (function(key, value){
      let record = document.createElement('li');
      let validationDisplayArea = document.getElementById("validateErrorsPublicExpenditure");
      record.textContent = value;
      validationDisplayArea.appendChild(record);
    });

    errorBody = JSON.parse(errorBody);
    let errorList = errorBody.errors;
    Object.keys(errorList).map(key =>
      createRow(key, errorList[key])
    );
  }

  /**
   * フォームバリデーションメッセージをクリアする。
   * @returns {void}
   */
  clearValidateDisplay(){
      while(this.validationDisplayArea.lastChild){
          this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
      }
  }

  changeDateFormat(dateString){
    if (dateString !== null){
      let date = new Date(dateString);
      let year = date.getFullYear();
      let month = (date.getMonth() + 1).toString().padStart(2, "0");
      let day = (date.getDate()).toString().padStart(2, "0");
      return year + '/' + month + '/' + day;
    }else{
      return ''
    }
  }

  async saveValuesCheck(params){
    let res = await CustomAjax.get(
        'user_info/public_expenditure/values_check_result' + this.setCheckValues(params),
        {'X-CSRF-TOKEN':CSRF_TOKEN},
    );

    let data = await res.json();

    return data;
  }

  setCheckValues(params){
      let paramDatas = '?facility_user_id='
      + params['facility_user_id']
      + '&bearer_number='
      + params['bearer_number']
      + '&recipient_number='
      + params['recipient_number']
      + '&confirmation_medical_insurance_date='
      + params['confirmation_medical_insurance_date']
      + '&food_expenses_burden_limit='
      + params['food_expenses_burden_limit']
      + '&living_expenses_burden_limit='
      + params['living_expenses_burden_limit']
      + '&outpatient_contribution='
      + params['outpatient_contribution']
      + '&hospitalization_burden='
      + params['hospitalization_burden']
      + '&application_classification='
      + params['application_classification']
      + '&special_classification='
      + params['special_classification']
      + '&effective_start_date='
      + params['effective_start_date']
      + '&expiry_date='
      + params['expiry_date']
      + '&update_type='
      + params['update_type']

      // public_expense_information_idがある場合、paramDatasに追加
      if(params['public_expense_information_id']){
          paramDatas += '&public_expense_information_id=' + params['public_expense_information_id'];
      }

      return paramDatas;
  }
}
