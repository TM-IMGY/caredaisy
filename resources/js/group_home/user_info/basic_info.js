
import InsurerTable from './insurer_table.js';
import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import facilityUserInfoHeader from '../../lib/facility_user_info_header.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js'
import ConfirmationDialog from '../../lib/confirmation_dialog.js';

/**
 * 基本情報タブ
 */
export default class BasicInfo{
  constructor(facilityID){
    this.insurerTable = new InsurerTable();

    this.elementID = 'tm_contents_basic';
    this.element = document.getElementById(this.elementID);
    this.elementForm = document.getElementById('basic_form');
    this.elementFormInsurerName = document.getElementById('bi_insurer_name');
    this.elementFormInsurerNo  = document.getElementById('bi_insurer_no');
    this.elementSubmitDialog = document.getElementById('bi_dialog');
    this.insurer = document.getElementById('bi_insurer_no');
    this.insured = document.getElementById('bi_insured_no');
    this.facilityID = null;
    this.isNewTenants = true;
    this.notificationList = [];
    this.facilityUserInfoHeader = new facilityUserInfoHeader();
    this.request = null;
    this.validationDisplayArea = document.getElementById('validateErrorsBasicInfo')
    this.select = null // ポップアップ内「いいえ」押下時に選択する対象保持用

    this.biStartDate = document.getElementById('bi_start_date');
    this.biEndDate = document.getElementById('bi_end_date');

    // 事業所IDをセット
    this.setFacilityID(facilityID);

    // 入居ボタンイベント
    if (document.getElementById('basic_create') !== null){
      document.getElementById('basic_create').addEventListener('click',this.clickMovingIntoBtn.bind(this));
    }
    // 保存ボタンイベント
    if (document.getElementById('basic_update') !== null){
      document.getElementById('basic_update').addEventListener('click',this.clickSaveBtn.bind(this));
    }
    // 無効フラグボタンイベント
    document.getElementById('bi_invalid_flag_check').addEventListener('click',this.clickInvalidFlag.bind(this));
    // 住所地特例チェックボックスイベント
    // 住所地特例について仕様の検討のため一時凍結する
    // document.getElementById('bi_spacial_address_flag_check').addEventListener('click',this.clickSpacialAddressFlag.bind(this));
    // サブミットダイアログはいボタン
    document.getElementById('bi_dialog_yes').addEventListener('click',this.clickDialogYes.bind(this));
    // サブミットダイアログいいえボタン
    document.getElementById('bi_dialog_no').addEventListener('click',this.clickDialogNo.bind(this));
    // フォームの保険者番号入力UIのイベント
    this.elementFormInsurerNo.addEventListener('change', this.changeFormInsurerNo.bind(this));
    // 保険者番号・被保険者番半角変換イベント
    this.insurer.addEventListener('blur', this.autoHalfWidthChange);
    this.insured.addEventListener('blur', this.autoHalfWidthChange);

    // datepicker共通初期設定
    $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );
    // 生年月日カレンダー
    $(".bi_bd_datepicker").datepicker({
      firstDay: 1,
      changeYear: true,
      yearRange: '1900:+0',
      minDate: new Date(1900, 1 - 1, 1),
      maxDate: '-1',
      defaultDate: '1950/01/01',
      onSelect: function (dateText, inst) {
        document.getElementById(inst.id).setCustomValidity('');
      },
      onClose: function (dateText, inst) {
        if(dateText.length == 10){
          let res = JapaneseCalendar.toJacal(dateText);
          $("#" + inst.id).prev().children('[id^="jaCal"]').text(res);
        }else{
          $("#" + inst.id).prev().children('[id^="jaCal"]').text('');
        }
      }.bind(this)
    });
    // 生年月日以外のカレンダー
    $(".bi_datepicker").datepicker({
      firstDay: 1,
      changeYear: true,
      yearRange: '2000:2099',
      minDate: new Date(2000, 4 - 1, 1),
      maxDate: new Date(2099, 12 - 1, 31),
      onSelect: function (dateText, inst) {
        document.getElementById(inst.id).setCustomValidity('');
      },
      onClose: function (dateText, inst) {
        if(dateText.length == 10){
          let res = JapaneseCalendar.toJacal(dateText);
          $("#" + inst.id).prev().children('[id^="jaCal"]').text(res);
        }else{
          $("#" + inst.id).prev().children('[id^="jaCal"]').text('');
        }
      }.bind(this)
    });

    // キーによる日付入力イベント
    // フォーマット変換＆和暦変換
    document.getElementById('bi_birthday').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    document.getElementById('bi_start_date').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    document.getElementById('bi_end_date').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    document.getElementById('bi_diagnosis_date').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    document.getElementById('bi_consent_date').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    document.getElementById('bi_death_date').addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    // バリデーションチェック
    document.getElementById('bi_birthday').addEventListener('input', this.validateDate.bind(this))
    document.getElementById('bi_start_date').addEventListener('input', this.validateDate.bind(this))
    document.getElementById('bi_end_date').addEventListener('input', this.validateDate.bind(this))
    document.getElementById('bi_diagnosis_date').addEventListener('input', this.validateDate.bind(this))
    document.getElementById('bi_consent_date').addEventListener('input', this.validateDate.bind(this))
    document.getElementById('bi_death_date').addEventListener('input', this.validateDate.bind(this))
  }

  /**
   * 承認情報の更新の通知先を設定
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @returns {void}}
   */
  addNotification(callBack){
    this.notificationList.push(callBack);
  }

  /**
   * フォームの保険者番号の入力UIのチェンジイベント
   * @param {Event}
   * @returns {Promise}
   */
  async changeFormInsurerNo(event){
    // 保険者番号を取得する
    let insurerNo = event.target.value;

    if(!insurerNo) {
      // 保険者番号が入力されていない場合はテキストを消す
      this.clearInsurerName();
      return;
    }

    if(insurerNo.length != 6) {
      // 6桁でない場合、処理を終了する。
      return;
    }

    // 保険者名をセットする
    await this.setInsurerName(insurerNo);
  }

  /**
   * 保険者名をクリアする
   * @return {void}
   */
  clearInsurerName(){
    this.elementFormInsurerName.textContent = null;
  }

  clickDialogNo(){
    this.select && this.select.select();
    this.hideSubmitDialog();
  }

  async clickDialogYes(){
    this.elementSubmitDialog.classList.add('bi_dialog_hidden');
    this.request['facility_user_id'] = document.getElementById('bi_facility_user_id').value;
    let route = 'service/facility_user/update_form';
    await this.postSaveRequest(route)
  }

  async postSaveRequest(route)
  {
    await CustomAjax.send(
      'POST',
      route,
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      this.request,
      '',
      this
    );
  }

  clickInvalidFlag(event){
    document.getElementById('bi_invalid_flag').value = event.target.checked ? 1 : 0;
  }

  /**
   * 住所地特例チェックボックスクリックイベント
   * @param {Event} event
   * @returns {void}
   */
  clickSpacialAddressFlag(event){
    document.getElementById('bi_spacial_address_flag').value = event.target.checked ? 1 : 0;
  }

  clickMovingIntoBtn(){
    let data = {'contractor_number':'', 'after_out_status_id':'','before_in_status_id':'','birthday':'','cell_phone_number':'',
    'consent_date':'','consenter':'','consenter_phone_number':'','death_date':'','diagnosis_date':'',
    'diagnostician':'','end_date':'','first_name':'','first_name_kana':'','insured_no':'','insurer_no':'',
    'invalid_flag':0,'last_name':'','last_name_kana':'','location1':'','location2':'','phone_number':'',
    'postal_code':'','spacial_address_flag':0,'start_date':'','blood_type':'','gender':'','rh_type':'','death_reason':''};
    this.setFormData(data);
    this.setFacilityUserID(null);
    this.setFormAction(true);
    this.isNewTenants = true;
    sessionStorage.isNewTenants = this.isNewTenants;
    this.request = null;
    this.facilityUserInfoHeader.clearUser();
    // 選択しているユーザーの情報が更新されたことを他のUIに通知
    this.notificationList.forEach(callBack=>callBack(null));
    this.clearValidateDisplay();
  }

  async clickSaveBtn(){
    this.clearValidateDisplay()

    let request = {
      'facility_id':this.facilityID,
      'invalid_flag':document.getElementById('bi_invalid_flag').value,
      // 'invalid_flag':document.getElementById('bi_invalid_flag_check').checked ? 1 : 0,
      'spacial_address_flag':document.getElementById('bi_spacial_address_flag').value,
      'contractor_number':document.getElementById('bi_contractor_number').value,
      'last_name':document.getElementById('bi_last_name').value,
      'first_name':document.getElementById('bi_first_name').value,
      'last_name_kana':document.getElementById('bi_last_name_kana').value,
      'first_name_kana':document.getElementById('bi_first_name_kana').value,
      'gender':document.querySelector('[name="gender"]:checked').value,
      'birthday':document.getElementById('bi_birthday').value,
      'blood_type':document.querySelector('[name="blood_type"]:checked').value,
      'rh_type':document.querySelector('[name="rh_type"]:checked').value,
      'insured_no':document.getElementById('bi_insured_no').value,
      'insurer_no':document.getElementById('bi_insurer_no').value,
      'postal_code':document.getElementById('bi_postal_code').value,
      'location1':document.getElementById('bi_location1').value,
      'location2':document.getElementById('bi_location2').value,
      'phone_number':document.getElementById('bi_phone_number').value,
      'cell_phone_number':document.getElementById('bi_cell_phone_number').value,
      'start_date':document.getElementById('bi_start_date').value,
      'end_date':document.getElementById('bi_end_date').value,
      'before_in_status_id':document.getElementById('bi_before_in_status_id').value,
      'after_out_status_id':document.getElementById('bi_after_out_status_id').value,
      'diagnosis_date':document.getElementById('bi_diagnosis_date').value,
      'diagnostician':document.getElementById('bi_diagnostician').value,
      'consent_date':document.getElementById('bi_consent_date').value,
      'consenter':document.getElementById('bi_consenter').value,
      'consenter_phone_number':document.getElementById('bi_consenter_phone_number').value,
      'death_date':document.getElementById('bi_death_date').value,
      'death_reason':document.getElementById('bi_death_reason').value,
    }

    this.request = request;

    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;
    if(this.isNewTenants){
      // 施設利用者の登録が0件の時に確認ダイアログを表示する。
      let route = 'service/facility_user/insert_form'
      let confirmationDialog = new ConfirmationDialog(
        'この内容で保存しますか',
        await this.postSaveRequest.bind(this, route)
      );
      confirmationDialog.show();
    }else{
      this.showSubmitDialog();
    }
  }

  validateDisplay(errorBody){
    let createRow = (function(key, value){
      let record = document.createElement('li');
      let validationDisplayArea = document.getElementById("validateErrorsBasicInfo");
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
   * @returns {Promise}
   */
  async getFacilityUser(facilityUserID){
    return await CustomAjax.post('/group_home/service/facility_user/get_data',
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      {
        'facility_user_id_list':[facilityUserID],
        'clm':[
          'after_out_status_id','before_in_status_id','birthday','cell_phone_number',
          'consent_date','consenter','consenter_phone_number','death_date','diagnosis_date',
          'diagnostician','end_date','facility_user_id','first_name','first_name_kana','insured_no','insurer_no',
          'invalid_flag','last_name','last_name_kana','location1','location2','phone_number',
          'postal_code','spacial_address_flag','start_date','blood_type','gender','rh_type','death_reason'
        ]
      }
    );
  }

  hideSubmitDialog(){
    this.elementSubmitDialog.classList.add('bi_dialog_hidden');
  }

  /**
   * @param {bool} status 表示のブーリアン値
   */
  setActive(status){
    //
  }

  setFacilityID(facilityID){
    document.getElementById('basic_form_facility_id').value = facilityID;
    this.facilityID = facilityID;
  }

  /**
   * @param {string} facilityUserID nullを渡すと初期化する
   */
  setFacilityUserID(facilityUserID){
    // フォームの施設利用者ID要素は毎回除去する
    let facilityUserIdElement = document.getElementById('bi_facility_user_id');
    facilityUserIdElement && facilityUserIdElement.parentNode.removeChild(facilityUserIdElement);
    if(facilityUserID !== null){
      let elementFacilityUserID = document.createElement('input');
      elementFacilityUserID.id = 'bi_facility_user_id';
      elementFacilityUserID.name = 'facility_user_id';
      elementFacilityUserID.type = 'hidden';
      elementFacilityUserID.value = facilityUserID;
      this.elementForm.appendChild(elementFacilityUserID);
    }
  }

  setFormData(data){
    this.clearValidateDisplay()
    document.getElementById('bi_contractor_number').value = data['contractor_number'];
    document.getElementById('bi_after_out_status_id').value = data['after_out_status_id'];
    document.getElementById('bi_before_in_status_id').value = data['before_in_status_id'];
    document.getElementById('bi_birthday').value = data['birthday'].replace(/-/g, '/');
    document.getElementById('bi_cell_phone_number').value = data['cell_phone_number'];
    document.getElementById('bi_consent_date').value = data['consent_date'] ? data['consent_date'].replace(/-/g, '/') : null;
    document.getElementById('bi_consenter').value = data['consenter'];
    document.getElementById('bi_consenter_phone_number').value = data['consenter_phone_number'];
    document.getElementById('bi_death_date').value = data['death_date'] ? data['death_date'].replace(/-/g, '/') : null;
    document.getElementById('bi_diagnosis_date').value = data['diagnosis_date'] ? data['diagnosis_date'].replace(/-/g, '/') : null;
    document.getElementById('bi_diagnostician').value = data['diagnostician'];
    document.getElementById('bi_end_date').value = data['end_date'] ? data['end_date'].replace(/-/g, '/') : null;
    document.getElementById('bi_first_name').value = data['first_name'];
    document.getElementById('bi_first_name_kana').value = data['first_name_kana'];
    document.getElementById('bi_insured_no').value = data['insured_no'];
    this.elementFormInsurerNo.value = data['insurer_no'];
    document.getElementById('bi_invalid_flag').value = data['invalid_flag'];
    document.getElementById('bi_last_name').value = data['last_name'];
    document.getElementById('bi_last_name_kana').value = data['last_name_kana'];
    document.getElementById('bi_location1').value = data['location1'];
    document.getElementById('bi_location2').value = data['location2'];
    document.getElementById('bi_phone_number').value = data['phone_number'];
    document.getElementById('bi_postal_code').value = data['postal_code'];
    document.getElementById('bi_spacial_address_flag').value = data['spacial_address_flag'];
    document.getElementById('bi_death_reason').value = data['death_reason'];
    document.getElementById('bi_start_date').value = data['start_date'].replace(/-/g, '/');

    if(data['blood_type']){
      document.getElementById('bi_blood_type_'+data['blood_type']).checked = true;
    } else {
      document.getElementById('bi_blood_type_').checked = true;
    }

    if(data['gender']){
      document.getElementById('bi_gender_'+data['gender']).checked = true;
    } else {
      document.getElementById('bi_gender_').checked = true;
      document.getElementById('bi_gender_1').checked = false;
      document.getElementById('bi_gender_2').checked = false;
    }

    if(data['rh_type']){
      document.getElementById('bi_rh_type_'+data['rh_type']).checked = true;
    } else {
      document.getElementById('bi_rh_type_').checked = true;
    }

    document.getElementById("jaCalBirthday").innerText = JapaneseCalendar.toJacal(data['birthday']);
    document.getElementById("jaCalMovingInDate").innerText = JapaneseCalendar.toJacal(data['start_date']);
    document.getElementById("jaCalMovingOutDate").innerText = JapaneseCalendar.toJacal(data['end_date']);
    document.getElementById("jaCalDiagnosisDate").innerText = JapaneseCalendar.toJacal(data['diagnosis_date']);
    document.getElementById("jaCalConsentDate").innerText = JapaneseCalendar.toJacal(data['consent_date']);
    document.getElementById("jaCalDeathDate").innerText = JapaneseCalendar.toJacal(data['death_date']);

    document.getElementById('bi_invalid_flag_check').checked = data['invalid_flag'];
    // 住所地特例について仕様の検討のため一時凍結する
    // document.getElementById('bi_spacial_address_flag_check').checked = data['spacial_address_flag'];

    // 保険者番号がある時は保険者名をセットし、ない時はクリアする
    data['insurer_no'] ? this.setInsurerName(data['insurer_no']) : this.clearInsurerName();
  }

  setFormAction(isNewTenants){
    this.elementForm.action = isNewTenants ?
      '/group_home/service/facility_user/insert_form' :
      '/group_home/service/facility_user/update_form';
  }

  /**
   * 保険者名をセットする
   * @param insurerNo 保険者番号
   * @return {Promise}
   */
  async setInsurerName(insurerNo){
    try {
      // 保険者情報を取得する
      let today = new Date();
      // 対象年月はユーザーのブラウザの時刻から取得する
      let insurer = await this.insurerTable.get(insurerNo, today.getFullYear(), today.getMonth() + 1);

      // 保険者名を反映する
      let insurerName = insurer.insurer_name;
      this.elementFormInsurerName.textContent = insurerName ? insurerName : '保険者名が見つかりません';
    } catch (error) {
      this.elementFormInsurerName.textContent = null;
      // 標準化され次第エラー内容を表示する。
      throw error;
    }
  }

  showSubmitDialog(){
    this.checkOfStartDateAndEndDate();
    this.elementSubmitDialog.classList.remove('bi_dialog_hidden');
  }

  /**
   * 入居日・退居日の入力日付をチェックする
   * 半年後以降ならメッセージを変更する
   */
  checkOfStartDateAndEndDate()
  {
    let sixMonthAfter = new Date();
    let maxDate = new Date('2099/12/31');
    sixMonthAfter.setMonth(sixMonthAfter.getMonth() + 6);
    let startDate = new Date(document.getElementById('bi_start_date').value);
    let endDate = document.getElementById('bi_end_date').value ? new Date(document.getElementById('bi_end_date').value) : '';
    let deathDate = document.getElementById('bi_death_date').value ? new Date(document.getElementById('bi_death_date').value) : '';

    if (sixMonthAfter < startDate && maxDate >= startDate) {
      if(deathDate && !endDate){
        this.setPopUpMsg('・入居日が現在より離れています<br>・退居日 と 退居後の状況の入力がありません<br>このまま内容を保存してよろしいですか？', this.biEndDate)
      } else {
        this.setPopUpMsg('入居日が現在より離れていますが保存しますか', this.biStartDate)
      }
    } else if(sixMonthAfter < endDate && maxDate >= endDate) {
      this.setPopUpMsg('退居日が現在より離れていますが保存しますか', this.biEndDate)
    } else {
      if(deathDate && !endDate){
        this.setPopUpMsg('退居日 と 退居後の状況の入力がありませんが、<br>このまま内容を保存してよろしいですか', this.biEndDate)
      } else {
        this.setPopUpMsg('変更した内容を更新しますか？')
      }
    }
  }

  /**
   * ポップアップにメッセージをセットする
   * @param {string} msg
   * @param {object} element
   */
  setPopUpMsg(msg, element = null)
  {
    document.getElementById('bi_dialog_msg').innerHTML = msg;
    this.select = element;
  }

  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  async setFacilityUser(user){
    if(user.facilityUserID===null){return;}
    let result = await this.getFacilityUser(user.facilityUserID);
    if(result!==null || result.length===1){
      let data = result[0];
      this.setFormData(data);
      this.setFacilityUserID(data['facility_user_id']);
      this.setFormAction(false);
      this.isNewTenants = false;
    }
  }

  autoHalfWidthChange(event){
    // 自動半角変換
    event.target.value = event.target.value.replace(/[！-～]/g, function(s){
      return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
    });
  }

  // 既存のバリデーションをカバー
  // date, min, max, (pattern)
  isCorrectDate(val){
    let year = val.split('/')[0];
    let month = val.split('/')[1] - 1;
    let day = val.split('/')[2];
    let date = new Date(year, month, day);
    if(date.getFullYear() == year && date.getMonth() == month && date.getDate() == day){
      return true;
    }
    return false;
  }

  isOver(min, val){
    let minDate = new Date(min);
    let date = new Date(val);
    if(date.getTime() >= minDate.getTime()){
      return true;
    }
    return false;
  }

  isUnder(max, val){
    let maxDate = new Date(max);
    let date = new Date(val);
    if(maxDate.getTime() > date.getTime()){
      return true;
    }
    return false;
  }

  validateDate(event){
    const regex = /^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/;
    const minDate = '1900/01/01';
    const maxDate = '2100/01/01';
    let targetItem = document.getElementById(event.target.id);
    let inputVal = event.target.value;

    targetItem.setCustomValidity('');
    if(regex.test(inputVal)){
      // 日付妥当性チェック
      if(!this.isCorrectDate(inputVal)){
        targetItem.setCustomValidity('有効な値を入力してください。フィールドが不完全であるか、無効な日付が指定されています。');
        return;
      }
      // 入力範囲チェック
      if(!this.isOver(minDate, inputVal)){
        targetItem.setCustomValidity('値は ' + minDate + ' 以降にする必要があります。');
        return;
      }
      if(!this.isUnder(maxDate, inputVal)){
        targetItem.setCustomValidity('値は ' + maxDate + ' より前にする必要があります。');
        return;
      }
    }else{
      if(inputVal != ''){
        targetItem.setCustomValidity('要求された形式に一致させてください。');
      }
    }
  }
}
