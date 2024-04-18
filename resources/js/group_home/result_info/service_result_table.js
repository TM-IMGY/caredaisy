
import ConfirmationDialog from '../../lib/confirmation_dialog.js';
import CSRF_TOKEN from '../../lib/csrf_token.js';
import CustomAjax from '../../lib/custom_ajax.js';
import ResultRegistrationTableBasic from './result_registration_table_basic.js';
import ResultRegistrationTableIncompetentResident from './result_registration_table_incompetent_resident.js';
import ResultRegistrationTableSpecial from './result_registration_table_special.js';
import ServiceCodeFormBasic from './service_code_form_basic.js';
import ServiceCodeFormIncompetentResident from './service_code_form_incompetent_resident.js';
import ServiceCodeFormSpecial from './service_code_form_special.js';
import SaveServiceResultRequest from './save_service_result_request.js';

/**
 * 実績登録ビューに責任を持つクラス。
 * TODO: 実体はサービス実績登録テーブルではないのでファイル名をリネームする。
 */
export default class ResultRegistration{
  constructor(){
    this.tableBasic = new ResultRegistrationTableBasic();
    this.tableSpecial = new ResultRegistrationTableSpecial();
    this.tableIncompetentResident = new ResultRegistrationTableIncompetentResident();
    this.serviceCodeFormBasic = new ServiceCodeFormBasic(this.tableBasic.addRecord.bind(this.tableBasic));
    this.serviceCodeFormSpecial = new ServiceCodeFormSpecial(this.tableSpecial.addRecord.bind(this.tableSpecial));
    this.serviceCodeFormIncompetentResident = new ServiceCodeFormIncompetentResident(this.tableIncompetentResident.addRecord.bind(this.tableIncompetentResident));

    // タブを何も表示しないサービス種別。
    this.SERVICE_TO_DISPLAY_NOTHING = ['32', '33', '35', '36', '37'];
    // 特別診療タブとして表示するサービス種類。
    this.SERVICE_TO_DISPLAY_SPECIAL = ['55'];
    // 特定入所者サービスとして表示するサービス種類。
    this.SERVICE_TO_DISPLAY_INCOMPETENT_RESIDENT = ['55'];

    this.elementID = 'tm_contents_1';

    this.element = document.getElementById(this.elementID);
    this.elementLabelYear = document.getElementById('result_registration_year');
    this.elementLabelMonth = document.getElementById('result_registration_month');
    this.elementSelectedTab = null;
    this.elementTabs = Array.from(document.getElementById('result_registration_tabs').children);
    this.elementTabBasic = document.getElementById('result_registration_basic');
    this.elementTabSpecial = document.getElementById('result_registration_special');
    this.elementTabIncompetentResident = document.getElementById('rr_incompetent_resident');
    this.facilityId = null;
    this.facilityUserId = null;
    this.isActive = false;
    this.notifications = [];
    this.serviceTypeCodeLatest = null;
    this.stayOutDays = [];
    this.year = null;
    this.month = null;
    this.startDates = [];
    this.endDates = [];

    this.element.querySelector('.facility_info_head').style.display = 'none';
    this.element.querySelector('.result_info_head').style.display = 'block';

    // サービスタブにクリックイベントを追加する。
    this.elementTabBasic.addEventListener('click', this.clickTab.bind(this));

    // 特別診療タブにクリックイベントを追加する。
    this.elementTabSpecial.addEventListener('click', this.clickTab.bind(this));

    // 特定入所者タブにクリックイベントを追加する。
    this.elementTabIncompetentResident.addEventListener('click', this.clickTab.bind(this));

    // プラスボタン(サービス)にイベントを付与する。
    // TODO: サービス種類ごとにプラスボタンが増えていくようであれば別クラスとして切り出す。
    document.getElementById('result_registration_table_plus').addEventListener('click', this.clickPlusBtnBasic.bind(this));

    // プラスボタン(特別診療タブ)にイベントを付与する。
    document.getElementById('rrt_plus_special').addEventListener('click', this.clickPlusBtnSpecial.bind(this));

    // プラスボタン(特定入所者サービス)にイベントを付与する。
    document.getElementById('rrt_plus_incompetent_resident').addEventListener('click', this.clickPlusBtnIncompetentResident.bind(this));

    // 再集計ボタンにイベントを追加する。
    this.recountButton = document.getElementById('re_count_button');
    this.recountButton.addEventListener('click', this.clickReCountButton.bind(this));

    if(document.getElementById('result_registration_save_btn') !== null){
      document.getElementById('result_registration_save_btn').addEventListener('click', this.clickSaveBtn.bind(this));
    }
  }

  /**
   * 実績情報の更新の通知先を追加する。
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @return {void}
   */
  addNotification(callBack){
    this.notifications.push(callBack);
  }

  /**
   * サービス種類によって画面デザインを変更する。
   * TODO: 一画面の中に複数画面が存在する構造になっているための実装だが不都合があれば新規ページとして切り離すことを検討する。
   * @param {String} serviceTypeCode
   * @return {void}
   */
  changeDesign(serviceTypeCode){
    if(this.SERVICE_TO_DISPLAY_NOTHING.includes(serviceTypeCode)){
      this.elementTabBasic.classList.add('result_registration_hidden');
      this.elementTabIncompetentResident.classList.add('result_registration_hidden');
      this.elementTabSpecial.classList.add('result_registration_hidden');
      this.recountButton.style.display = 'block';
    // 特別診療タブと特定入所者サービスを表示するサービス種類の場合。
    }else if(this.SERVICE_TO_DISPLAY_SPECIAL.includes(serviceTypeCode) && this.SERVICE_TO_DISPLAY_INCOMPETENT_RESIDENT.includes(serviceTypeCode)){
      this.elementTabBasic.classList.remove('result_registration_hidden');
      this.elementTabIncompetentResident.classList.remove('result_registration_hidden');
      this.elementTabSpecial.classList.remove('result_registration_hidden');
      this.recountButton.style.display = 'none';
    }
  }

  /**
   * プラスボタン(サービス)のクリックイベント。
   * @return {Promise}
   */
  async clickPlusBtnBasic(){
    // 活性化しているかつ、パラメーターがある場合
    if(this.hasAllParam() && this.isActive){
      await this.serviceCodeFormBasic.updateServiceType(this.facilityUserId);

      // 既に登録されているサービスコードIDを全て取得する。
      let userEditedData = this.tableBasic.getUserEditedData();
      let serviceItemCodeIds = userEditedData.map(value => Number(value.service_item_code_id));
      await this.serviceCodeFormBasic.updateServiceCodeTable(serviceItemCodeIds);

      this.serviceCodeFormBasic.show();
    }
  }

  /**
   * プラスボタン(特定入所者サービス)のクリックイベント。
   * @return {Promise}
   */
  async clickPlusBtnIncompetentResident(){
    // 活性化しているかつ、パラメーターがある場合
    if(this.hasAllParam() && this.isActive){
      // 既に登録されている特定入所者サービスのコードのIDを全て取得する。
      let userEditedData = this.tableIncompetentResident.getUserEditedData();
      let registeredIds = userEditedData.map(value => Number(value.service_item_code_id));
      await this.serviceCodeFormIncompetentResident.updateServiceCodeTable(registeredIds);
      this.serviceCodeFormIncompetentResident.show();
    }
  }

  /**
   * プラスボタン(特別診療)のクリックイベント。
   * @return {Promise}
   */
  async clickPlusBtnSpecial(){
    // 活性化しているかつ、パラメーターがある場合
    if(this.hasAllParam() && this.isActive){
      // 既に登録されている特別診療コードのIDを全て取得する。
      let userEditedData = this.tableSpecial.getUserEditedData();
      let registeredIds = userEditedData.map(value => Number(value.special_medical_code_id));

      await this.serviceCodeFormSpecial.updateServiceCodeTable(registeredIds);

      this.serviceCodeFormSpecial.show();
    }
  }

  /**
   * @returns {void}
   */
  async clickReCountButton(){
    // 連打対策としてイベント中はボタンを無効にする
    // イベント終了時に再び有効化
    this.recountButton.disabled = true;

    // 活性化していない場合、またはパラメーターを持たない場合は何もしない。
    if(!(this.hasAllParam() && this.isActive)){
      this.recountButton.disabled = false;
      return;
    }

    // テーブルのレコードをクリアする。
    this.tableBasic.deleteRecord();

    let serviceCodes = [];
    try{
      // 自動サービスコード機能から取得する。
      serviceCodes = await this.getAutoServiceCode();
    } catch (error) {
      // モーダルオープン。
      this.showErrorPopup(error.message);
      this.recountButton.disabled = false;
      return;
    }

    // 取得したサービスコードをテーブルに挿入する。
    serviceCodes.forEach(code => this.tableBasic.addRecord(
      code.date_daily_rate,
      code.date_daily_rate_one_month_ago,
      code.date_daily_rate_two_month_ago,
      code.date_daily_rate_schedule,
      code.service_count_date,
      code.service_item_code_id,
      code.service_item_name,
      code.target_date,
      code.unit_number
    ));

    this.recountButton.disabled = false;
  }

  /**
   * @returns {Promise}
   */
  async clickSaveBtn(){
    if(!(this.hasAllParam() && this.isActive)){
      return;
    }

    let confirmationDialog = new ConfirmationDialog('この内容で保存しますか', this.save.bind(this));
    confirmationDialog.show();
  }

  /**
   * タブのクリックイベント(共通)。
   * @param {Event} event
   * @return {void}
   */
  async clickTab(event){
    if(this.elementSelectedTab){
      // 選択されていたタブの色を変える。
      this.elementSelectedTab.classList.remove('rr_tab_active');
    }

    // クリックされたタブに紐づくUIのみを表示し、それ以外を非表示にする。
    this.elementTabs.forEach((tab)=>{
      let contents = document.getElementById(tab.getAttribute('data-rr-contents-id'));
      if(event.target == tab){
        contents.classList.remove('result_registration_hidden');
      } else {
        contents.classList.add('result_registration_hidden');
      }
    });

    // 選択されたタブの色を変える。
    event.target.classList.add('rr_tab_active');

    // 状態を更新する。
    this.elementSelectedTab = event.target;
  }

  /**
   * @returns {Promise}
   */
  async getAutoServiceCode(){
    let params = new URLSearchParams({
      facility_id: this.facilityId,
      facility_user_id: this.facilityUserId,
      month: this.month,
      year: this.year
    });

    let res = await CustomAjax.get('/group_home/service/auto_service_code/get?' + params.toString());

    return await res.json();
  }

  /**
   * @returns {Promise}
   */
  async getServiceResults() {
    let params = new URLSearchParams({facility_user_id : this.facilityUserId, year : this.year, month : this.month});
    let response = await CustomAjax.get('/group_home/service/service_result/get?' + params.toString(),);
    return await response.json();
  }

  /**
   * 施設利用者が事業所から提供を受けているサービス種別について、対象月に利用中のものを全て返す。
   * @return {Promise}
   */
  async getServiceTypes(){
    // パラメーターを作成する。
    let params = new URLSearchParams({facility_user_id: this.facilityUserId, month: this.month, year: this.year});
    let res = await CustomAjax.get('result_info/service_type?' + params.toString());
    let data = await res.json();
    return data;
  }

  /**
   * 実績登録画面が持てる全てのパラメーターが存在するかを返す。
   * TODO: 全てのというと範囲が見ただけではわからないので修正する。
   * @returns {Boolean}
   */
  hasAllParam(){
    return ![this.facilityUserId, this.year, this.month].includes(null);
  }

  /**
   * 実績情報が変わったことを通知する。
   * @return {Promise}
   */
  async notification() {
    for (let i = 0, len = this.notifications.length; i < len; i++) {
      await this.notifications[i]();
    }
  }

  /**
   * リロードする。
   * @return {Promise}
   */
  async reload(){
    // テーブルをリロードする。
    this.tableBasic.reloadDateCell(this.year, this.month, this.stayOutDays, this.startDates, this.endDates);
    this.tableBasic.deleteRecord();
    this.tableSpecial.reloadDateCell(this.year, this.month, this.stayOutDays, this.startDates, this.endDates);
    this.tableSpecial.deleteRecord();
    this.tableIncompetentResident.reloadDateCell(this.year, this.month, this.stayOutDays, this.startDates, this.endDates);
    this.tableIncompetentResident.deleteRecord();

    // 活性化していない場合は何もしない。
    if(!this.isActive){
      return;
    }

    // パラメーターを全て持つ場合。
    if(this.hasAllParam()){
      // 施設利用者が事業所から提供を受けているサービス種類について対象年月に利用中のものを全て取得する。
      let serviceTypes = await this.getServiceTypes();

      // サービス種類の最新を取得する。
      let serviceTypeCodeLatest = serviceTypes[0] ? serviceTypes[0]['service_type_code'] : null;

      // サービス種類が変わる場合、初期タブとして「サービス」を表示する。
      if(this.serviceTypeCodeLatest !== serviceTypeCodeLatest){
        this.elementTabBasic.click();
      }

      // サービス種類によって画面デザインを変更する。
      this.changeDesign(serviceTypeCodeLatest);

      // 状態を更新する。
      this.serviceTypeCodeLatest = serviceTypeCodeLatest;

      this.tableBasic.setServiceTypeCode(serviceTypeCodeLatest);

      let results = await this.getServiceResults();

      // サービス実績をサービスコードと特別診療コードに分ける。
      let serviceCodes = results.filter(result => result.result_kind === 1);
      let specialMedicalCodes = results.filter(result => result.result_kind === 2);
      let incompetentResidents = results.filter(result => result.result_kind === 3);

      serviceCodes.forEach(serviceCode => this.tableBasic.addRecord(
        serviceCode.date_daily_rate,
        serviceCode.date_daily_rate_one_month_ago,
        serviceCode.date_daily_rate_two_month_ago,
        serviceCode.date_daily_rate_schedule,
        serviceCode.service_count_date,
        serviceCode.service_item_code_id,
        serviceCode.service_item_name,
        serviceCode.target_date,
        serviceCode.unit_number
      ));

      // 特別診療で表示するサービス種類の場合。
      if(this.SERVICE_TO_DISPLAY_SPECIAL.includes(this.serviceTypeCodeLatest)){
        // 取得した実績(特別診療)からレコードを作成して追加する。
        specialMedicalCodes.forEach(specialMedicalCode => this.tableSpecial.addRecord(
          specialMedicalCode.date_daily_rate,
          specialMedicalCode.service_count_date,
          specialMedicalCode.special_medical_code_id,
          specialMedicalCode.special_medical_name,
          specialMedicalCode.target_date,
          specialMedicalCode.unit
        ));
      }

      // 特定入所者サービスで表示するサービス種類の場合。
      if(this.SERVICE_TO_DISPLAY_INCOMPETENT_RESIDENT.includes(this.serviceTypeCodeLatest)){
        // 取得した実績(特定入所者サービス)からレコードを作成して追加する。
        incompetentResidents.forEach(incompetentResident => this.tableIncompetentResident.addRecord(
          incompetentResident.burden_limit,
          incompetentResident.date_daily_rate,
          incompetentResident.service_count_date,
          incompetentResident.service_item_code_id,
          incompetentResident.service_item_name,
          incompetentResident.target_date,
          incompetentResident.unit_number
        ));
      }
    }
  }

  /**
   * @returns {Promise}
   */
  async save(){
    let request = new SaveServiceResultRequest(this.facilityId, this.facilityUserId, this.year, this.month);

    // ユーザーが編集したデータを取得する。
    let serviceCodes = this.tableBasic.getUserEditedData();
    let specialMedicalCodes = this.tableSpecial.getUserEditedData();
    let incompetentResidents = this.tableIncompetentResident.getUserEditedData();
    let userEditedData = serviceCodes.concat(specialMedicalCodes, incompetentResidents);

    // ユーザー編集データをリクエストに追加する。
    let dateDailyRateInitial = '0000000000000000000000000000000';
    for (let i = 0, len = userEditedData.length; i < len; i++) {
      request.addServiceResult(
        userEditedData[i].burden_limit === void 0 ? null : userEditedData[i].burden_limit,
        userEditedData[i].date_daily_rate,
        userEditedData[i].date_daily_rate_one_month_ago === void 0 ? dateDailyRateInitial : userEditedData[i].date_daily_rate_one_month_ago,
        userEditedData[i].date_daily_rate_two_month_ago === void 0 ? dateDailyRateInitial : userEditedData[i].date_daily_rate_two_month_ago,
        userEditedData[i].service_count_date,
        userEditedData[i].service_item_code_id,
        userEditedData[i].special_medical_code_id === void 0 ? null : userEditedData[i].special_medical_code_id
      );
    }

    try {
      await CustomAjax.post(
        '/group_home/service/service_result/save',
        {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
        request.data
      );

      await this.reload();

      await this.notification();

      document.getElementById("changed_flg").value = false;
    } catch (error) {
      this.showErrorPopup(error.message);
    }
  }

  /**
   * 活性化状態をセットする。
   * @param {Boolean} isActive
   * @return {Promise}
   */
  async setActive(isActive){
    this.isActive = isActive;
    await this.reload();
  }

  /**
   * 事業所情報をセットする。
   * @param {*} facilityId
   * @param {*} facilityName
   * @return {void}
   */
  setFacilityId(facilityId, facilityName){
    this.facilityId = facilityId;
    this.serviceCodeFormBasic.updateFacility(facilityId, facilityName);
    this.serviceCodeFormSpecial.updateFacility(facilityId, facilityName);
    this.serviceCodeFormIncompetentResident.updateFacility(facilityId, facilityName);
  }

  /**
   * 施設利用者情報をセットする。
   * @param {Object} facilityUser
   * @return {Promise}
   */
  async setFacilityUser(facilityUser){
    this.facilityUserId = facilityUser.facilityUserID;
  }

  /**
   * 外泊日をセットする。
   * @param {Array} stayOutDays
   * @return {void}
   */
  setStayOutDays(stayOutDays){
    this.stayOutDays = stayOutDays;
  }

  /**
   * 入居日までの日付をセットする。
   * @param {Array} startDates
   * @return {void}
   */
  setStartDates(startDates){
    this.startDates = startDates;
  }

  /**
   * 退去日からの日付をセットする。
   * @param {Array} endDates
   * @return {void}
   */
  setEndDates(endDates){
    this.endDates = endDates;
  }

  /**
   * 年月をセットする。
   * @param {String} year
   * @param {String} month
   * @return {void}
   */
  setYm(year, month){
    this.elementLabelYear.textContent = year;
    this.elementLabelMonth.textContent = month;
    // 状態を更新する。
    this.serviceCodeFormBasic.setYm(year, month);
    this.serviceCodeFormSpecial.setYm(year, month);
    this.serviceCodeFormIncompetentResident.setYm(year, month);
    this.year = year;
    this.month = month;
  }

  /**
   * ポップアップを表示する。
   * @param {String} msg
   * @return {void}
   */
  showErrorPopup(msg){
    let elementPopup = document.createElement('div');
    // TODO: 加算状況の名称になっているので修正する。
    elementPopup.id = 'overflow_addition_status3';

    let elementPopupContents = document.createElement('div');
    elementPopupContents.classList.add('conf');

    let elementPopupMessage = document.createElement('p');
    elementPopupMessage.textContent = msg;

    let elementBtnFrame = document.createElement('div');
    elementBtnFrame.classList.add('popup_btn_frame');

    let elementBtn = document.createElement('button');
    elementBtn.classList.add('poppu_close_addition_status');
    elementBtn.id = 'errorbtn_addition_status';
    elementBtn.textContent = '閉じる';
    elementBtn.addEventListener('click', () => {elementPopup.parentNode.removeChild(elementPopup);});

    elementPopup.appendChild(elementPopupContents);
    elementPopupContents.appendChild(elementPopupMessage);
    elementPopupContents.appendChild(elementBtnFrame);
    elementPopupContents.appendChild(elementBtn);

    document.body.appendChild(elementPopup);
  }
}
