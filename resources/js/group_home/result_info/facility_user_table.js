
// TODO: changeの誤字である場合は修正する。
import ChangePopup from '../chnage_popup.js';
import CustomAjax from '../../lib/custom_ajax.js';
import FacilityUserInfoHeader from '../../lib/facility_user_info_header.js';
const CARE_LEVEL_1 = '非該当';

/**
 * 実績情報画面の施設利用者テーブルについて責任を持つクラス。
 */
export default class FacilityUserTable{
  constructor(){
    this.facilityUserInfoHeader = new FacilityUserInfoHeader();

    // 再請求対象に追加された施設利用者全て。
    this.addedReBillingFacilityUsers = [];
    this.element = document.getElementById('facility_user_tbody');
    this.elementPlusBtn = document.getElementById('fu_table_plus_btn');
    this.elementSelectUserPopup = document.getElementById('fu_table_select_user_popup');
    this.elementSelectUserPopupGrayOut = document.getElementById('fu_table_select_user_popup_grayout');
    // sup = select user popup
    // TODO: 施設利用者選択ポップアップを別クラスとして切り離す。
    this.elementSupTbody = document.getElementById('fu_table_sup_tbody');
    this.elementSupOkBtn = document.getElementById('fu_table_sup_ok_btn');
    this.elementSupCancelBtn = document.getElementById('fu_table_sup_cancel_btn');
    this.elementSupSearchBox = document.getElementById('fu_table_sup_search_box');
    this.elementSupSearchBtn = document.getElementById('fu_table_sup_search_btn');
    this.elementSupAllCancelBtn = document.getElementById('fu_table_sup_all_cancel_btn');
    this.elementSupAllSelectBtn = document.getElementById('fu_table_sup_all_select_btn');
    this.facilityId = null;
    this.facilityUsers = [];
    // 初期の再請求対象の施設利用者全て。
    this.initRebillingFacilityUsers = [];
    this.isReBillingMode = false;
    this.year = null;
    this.month = null;
    this.selectedRecord = null;
    // 情報が変わった場合の通知先全て。
    this.notifications = [];

    // プラスボタンにクリックイベントを紐づける。
    this.elementPlusBtn.addEventListener('click', this.clickPlusBtn.bind(this));

    // 施設利用者選択ポップアップの検索ボタンにクリックイベントを追加する。
    this.elementSupSearchBtn.addEventListener('click', this.clickSupSearchBtn.bind(this));

    // 施設利用者選択ポップアップの一括選択ボタンにクリックイベントを追加する。
    this.elementSupAllSelectBtn.addEventListener('click', this.handleSupTable.bind(this, true));

    // 施設利用者選択ポップアップの一括キャンセルボタンにクリックイベントを追加する。
    this.elementSupAllCancelBtn.addEventListener('click', this.handleSupTable.bind(this, false));

    // 施設利用者選択ポップアップの確定ボタンにクリックイベントを追加する。
    this.elementSupOkBtn.addEventListener('click', this.clickSupOkBtn.bind(this));

    // 施設利用者選択ポップアップのキャンセルボタンにクリックイベントを追加する。
    this.elementSupCancelBtn.addEventListener('click', this.setSelectUserPopupStatus.bind(this, false));
  }

  /**
   * レコード選択イベントの通知先を設定する。
   * @param {Function} callBack 通知先として呼ぶコールバック関数。
   * @return {void}
   */
  addNotification(callBack){
    this.notifications.push(callBack);
  }

  /**
   * プラスボタンのクリックイベント。
   * @param {Event}
   * @return {void}
   */
  clickPlusBtn(){
    // 施設利用者テーブルの施設利用者選択ポップアップを表示する。
    this.setSelectUserPopupStatus(true);
  }

  /**
   * レコードのクリックイベント。
   * @param {Element} record
   * @return {Promise}
   */
  async clickRecord(record){
    // 選択された施設利用者のIDと、選択されていた施設利用者のIDを取得する。
    let facilityUserId = Number(record.getAttribute('data-facility-user-id'));
    let facilityUserIdOld = null;
    if(this.selectedRecord){
      facilityUserIdOld = Number(this.selectedRecord.getAttribute('data-facility-user-id'));
    }

    let changed_flg = document.getElementById("changed_flg").value;
    if (changed_flg == 'true' && !ChangePopup.popup({id:'table_facility_user_id' + facilityUserId})){
      return false
    }

    // 選択されているレコードの状態を解除する。
    if(this.selectedRecord){
      this.selectedRecord.classList.remove('fu_table_selected_record');
      this.selectedRecord.children[0].classList.remove('fu_table_selected_cell');
    }

    // レコードを選択状態にする。
    record.classList.add('fu_table_selected_record');
    record.children[0].classList.add('fu_table_selected_cell');

    // 状態を更新する。
    this.selectedRecord = record;

    // 選択されたレコードに変更があった場合に通知する。
    if(facilityUserId !== facilityUserIdOld){
      let facilityUser = this.facilityUsers.find(facilityUser => facilityUser.facility_user_id === facilityUserId);
      await this.notification();
      // 施設利用者情報ヘッダに通知する。
      // TODO: 施設利用者テーブルが各タブの中に共通して配置される施設利用者情報に責任を負うべきではないので切り離す。
      this.facilityUserInfoHeader.setFacilityUserForResultInfo({
        facilityUserID: facilityUser.facility_user_id,
        userName: facilityUser.last_name + facilityUser.first_name,
        year: this.year,
        month: this.month
      });
    }

    // 選択された利用者IDを保持
    sessionStorage.selectedUserId = facilityUserId;
  }

  /**
   * 施設利用者選択ポップアップの確定ボタンのクリックイベント。
   * @param {Event}
   * @return {Promise}
   */
  async clickSupOkBtn(){
    // 再請求対象の追加施設利用者のキャッシュをクリアする。
    this.addedReBillingFacilityUsers = [];

    // チェックボックスにマークされている施設利用者を再請求対象に追加する。
    let records = this.elementSupTbody.children;
    for (let i = 0, len = records.length; i < len; i++) {
      let checkBox = records[i].children[0].children[0];
      if(checkBox.checked){
        this.addedReBillingFacilityUsers.push(Number(checkBox.value));
      }
    }
    // ユニーク化
    this.addedReBillingFacilityUsers = Array.from(new Set(this.addedReBillingFacilityUsers));

    // 施設利用者テーブルをリセットする
    await this.draw();
    
    if(!this.selectedRecord){
      if(this.getVisibleFacilityUsers().length > 0) {
        let record = document.getElementById("facility_user_tbody").firstElementChild;
        record.scrollIntoView();
        await this.clickRecord(record);
      }
    }

    // 施設利用者選択ポップアップを閉じる
    this.setSelectUserPopupStatus(false);
  }

  /**
   * 施設利用者選択ポップアップの検索ボタンのクリックイベント。
   * @return {void}
   */
  clickSupSearchBtn(){
    this.drawSupTable();
  }

  /**
   * レコードを作成する
   * @param {Boolean} approval
   * @param {String} careLevelName
   * @param {Number} facilityUserId
   * @param {String} name
   * @return {Element}
   */
  createRecord(approval, careLevelName, facilityUserId, name){
    // レコードを作成する
    let record = document.createElement('tr');
    record.classList.add('facility_user_tr');
    record.setAttribute('data-facility-user-id',facilityUserId);
    record.setAttribute('id','table_facility_user_id' + facilityUserId);

    // 名前セルを作成する
    let nameCell = document.createElement('td');
    nameCell.textContent = name;
    nameCell.classList.add('facility_user_td');

    // 承認情報を格納するセルを作成する。
    let approvalCell = document.createElement('td');
    approvalCell.classList.add('facility_user_td');
    // 非該当の場合は承認情報を表示しない。
    if(careLevelName != '非該当'){
      this.setCellApprovalMode(approvalCell, approval);
    }

    record.appendChild(nameCell);
    record.appendChild(approvalCell);
    record.addEventListener('click', this.clickRecord.bind(this, record));

    return record;
  }

  /**
   * 施設利用者選択ポップアップのテーブルのレコードを生成して返す。
   * @param {Object} user key: facility_user_id, first_name, last_name
   * @return {Element}
   */
  createSupTableRecord(user){
    // レコードを生成する
    let record = document.createElement('div');
    record.classList.add('fu_table_sup_table_record');

    // チェックボックスセルを作成する
    let checkBoxCell = document.createElement('div');
    let checkBox = document.createElement('input');
    checkBox.type = 'checkbox';
    checkBox.value = user.facility_user_id;
    checkBoxCell.appendChild(checkBox);

    // 名前セルを作成する
    let nameCell = document.createElement('div');
    nameCell.textContent = user.last_name + ' ' + user.first_name;

    record.appendChild(checkBoxCell);
    record.appendChild(nameCell);

    return record;
  }

  /**
   * レコードを削除する。
   * @return {void}
   */
  deleteRecord(){
    this.element.textContent = null;
  }

  /**
   * 施設利用者テーブルを描画する。
   * @return {Promise}
   */
  async draw(){
    // 選択されているレコードから施設利用者のIDを取得する。
    let facilityUserId = null;
    if(this.selectedRecord){
      facilityUserId = Number(this.selectedRecord.getAttribute('data-facility-user-id'));
    }

    // レコードを削除する。
    this.deleteRecord();

    // レコードを作成して追加する。
    this.facilityUsers.forEach((user)=>{
      if(this.isVisibleUser(user)){
        let record = this.createRecord(
          user.approval === 1,
          user.care_level_name,
          user.facility_user_id,
          user.last_name + user.first_name,
        );
        this.element.appendChild(record);
      }
    });

    // 施設利用者の選択状態を復元し通知する。
    let records = this.element.children;
    let isFound = false;
    for (let i = 0, len = records.length; i < len; i++) {
      if(Number(records[i].getAttribute('data-facility-user-id')) === facilityUserId){
        await this.clickRecord(records[i]);
        isFound = true;
        let data = {careLevelName: null, facilityUserID: null, userName: null, facilityUsers: this.getVisibleFacilityUsers()};
        let facilityUser = this.facilityUsers.find(facilityUser => facilityUser.facility_user_id === facilityUserId);
        data.careLevelName = facilityUser.care_level_name;
        data.facilityUserID = facilityUser.facility_user_id;
        data.userName = facilityUser.last_name + facilityUser.first_name;
        await this.notifications[0](data);
        break;
      }
    }
    // 復元する施設利用者が見つからなかった場合は選択をクリアし通知する。
    if(!isFound){
      this.selectedRecord = null;
      await this.notification();
      this.facilityUserInfoHeader.clearUser();
    }
  }

  /**
   * 施設利用者選択ポップアップのテーブルのレコードを再描画する。
   * @return {void}
   */
  drawSupTable(){
    this.elementSupTbody.textContent = null;

    this.facilityUsers.forEach((user)=>{
      if(this.isSupTargetUser(user)){
        let record = this.createSupTableRecord(user);
        this.elementSupTbody.appendChild(record);
      }
    });
  }

  /**
   * 施設利用者を返す。
   * @return {Arary}
   */
  getFacilityUsers(){
    return this.facilityUsers;
  }

  /**
   * 施設利用者の合計人数を返す。
   * @return {Number}
   */
  getUserCnt(){
    return this.element.children.length;
  }

  /**
   * 表示されている施設利用者を返す。
   * @return {Array}
   */
  getVisibleFacilityUsers(){
    return this.facilityUsers.filter((user) => this.isVisibleUser(user));
  }

  /**
   * 施設利用者選択ポップアップのテーブルのレコードを一括操作する。
   * @param {Boolean} checked チャックボックスにチェックするかのフラグ。
   * @return {void}
   */
  handleSupTable(checked){
    let records = this.elementSupTbody.children;
    for (let i = 0, len = records.length; i < len; i++) {
      let checkBox = records[i].children[0].children[0];
      checkBox.checked = checked;
    }
  }

  /**
   * パラメーターを全て持つかを返す。
   * @return {Boolean}
   */
  hasAllParam(){
    return ![this.facilityId, this.year, this.month].includes(null);
  }

  /**
   * 未承認の施設利用者を持っているかを返す。
   * @return {Boolean}
   */
  hasUnApprovedUser(){
    let len = this.facilityUsers.length;
    let cnt = 0;
    for (let i = 0; i < len; i++) {
      // 承認済か要介護度が非該当
      if(this.facilityUsers[i].approval === 1 || this.facilityUsers[i].care_level_name == CARE_LEVEL_1){
        cnt++;
      }
    }
    return len !== cnt;
  }

  /**
   * 施設利用者選択ポップアップのテーブルの表示対象かを返す。
   * @param {Object} user
   * @return {void}
   */
  isSupTargetUser(user){
    // 検索ボックスのテキストが空、または曖昧に一致すれば真
    let serarchTxt = this.elementSupSearchBox.value;
    return serarchTxt === '' || (user.last_name_kana + user.first_name_kana).match(serarchTxt) !== null;
  }

  /**
   * 描画対象の施設利用者かを返す。
   * @param {Object} user
   * @return {Boolean}
   */
  isVisibleUser(user){
    // 通常モードまたは、再請求モードかつ非承認で要介護度が非該当ではない施設利用者または、追加された再請求対象の場合に真
    let isReBillingTarget = this.isReBillingMode && (user.approval !== 1 && user.care_level_name !== CARE_LEVEL_1);
    let isAddedReBillingTarget = this.addedReBillingFacilityUsers.includes(user.facility_user_id);
    let isInitRebillingFacilityUser = this.initRebillingFacilityUsers.includes(user.facility_user_id);

    return !this.isReBillingMode || isReBillingTarget || isAddedReBillingTarget || isInitRebillingFacilityUser;
  }

  /**
   * 通知する。
   * @return {Promise}
   */
  async notification(){
    // 通知データの初期値を作成する。
    let data = {careLevelName: null, facilityUserID: null, userName: null, facilityUsers: this.getVisibleFacilityUsers()};

    if (this.selectedRecord !== null) {
      let facilityUserId = Number(this.selectedRecord.getAttribute('data-facility-user-id'));
      let facilityUser = this.facilityUsers.find(facilityUser => facilityUser.facility_user_id === facilityUserId);
      data.careLevelName = facilityUser.care_level_name;
      data.facilityUserID = facilityUser.facility_user_id;
      data.userName = facilityUser.last_name + facilityUser.first_name;
    }

    for (let i = 0, len = this.notifications.length; i < len; i++) {
      await this.notifications[i](data);
    }
  }

  /**
   * セルの承認状態をセットする。
   * @param {Element} cell
   * @param {Boolean} isApproved
   * @returns {void}
   */
  setCellApprovalMode(cell, isApproved){
    cell.textContent = isApproved ? '承認' : '未承認';
    let classList = cell.classList;
    if(isApproved){
      classList.add('fu_table_cell_approved');
      classList.remove('fu_table_cell_not_approved');
    } else {
      classList.add('fu_table_cell_not_approved');
      classList.remove('fu_table_cell_approved');
    }
  }

  /**
   * 事業所情報をセットする。
   * @param {Number} facilityId
   * @return {void}
   */
  async setFacility(facilityId){
    this.facilityId = facilityId;
  }

  /**
   * 施設利用者をセットする。
   * @param {Object} facilityUsers
   * @return {Promise}
   */
  setFacilityUsers(facilityUsers){
    this.facilityUsers = facilityUsers;
  }

  /**
   * 対象年月情報をセットする。
   * @param {Number} year
   * @param {Number} month
   * @return {void}
   */
  async setYm(year, month){
    this.year = year;
    this.month = month;
  }

  /**
   * 再請求モードをセットする
   * @param {Object} data key: is_re_billing_mode TODO: Objectで渡す必要なし。
   * @return {Promise}
   */
  async setReBillingMode(data) {
    this.isReBillingMode = data.is_re_billing_mode;
    // 通常モードになる時は状態をクリアする
    if(!this.isReBillingMode){
      this.addedReBillingFacilityUsers = [];
      this.initRebillingFacilityUsers = [];
    }
    // 再請求モードになる時は初期の対象者をキャッシュする
    else {
      for (let i = 0, len = this.facilityUsers.length; i < len; i++) {
        if(this.facilityUsers[i].care_level_name !== CARE_LEVEL_1 && !this.facilityUsers[i].approval){
          this.initRebillingFacilityUsers.push(this.facilityUsers[i].facility_user_id);
        }
      }
    }

    await this.draw();

    if(!this.selectedRecord){
      if(this.getVisibleFacilityUsers().length > 0) {
        let record = document.getElementById("facility_user_tbody").firstElementChild;
        record.scrollIntoView();
        await this.clickRecord(record);
      }
    }

    // 再請求モードでかつ、未承認者がいない場合は施設利用者選択ポップアップを出現させる
    if(this.isReBillingMode && !this.hasUnApprovedUser()){
      this.clickPlusBtn();
    }
  }

  /**
   * 施設利用者選択ポップアップの表示状態をセットする。
   * @param {Boolean} isActive
   * @return {void}
   */
  setSelectUserPopupStatus(isActive){
    if(isActive){
      this.elementSelectUserPopup.classList.remove('fu_table_hidden');
      this.elementSelectUserPopupGrayOut.classList.remove('fu_table_hidden');
      this.elementSelectUserPopup.classList.add('fu_table_active');
      this.elementSelectUserPopupGrayOut.classList.add('fu_table_active');
      // テーブルのレコードをリセットする
      this.drawSupTable();
    } else {
      this.elementSelectUserPopup.classList.remove('fu_table_active');
      this.elementSelectUserPopupGrayOut.classList.remove('fu_table_active');
      this.elementSelectUserPopup.classList.add('fu_table_hidden');
      this.elementSelectUserPopupGrayOut.classList.add('fu_table_hidden');
    }
  }

  /**
   * 施設利用者をソートする
   * @return {void}
   */
  sortFacilityUsers(){
    this.facilityUsers.sort((a, b) => {
      if(a.last_name_kana !== b.last_name_kana){
        return a.last_name_kana.localeCompare(b.last_name_kana, 'ja');
      }
      if(a.first_name_kana !== b.first_name_kana){
        return a.first_name_kana.localeCompare(b.first_name_kana, 'ja');
      }
      return 0;
    });
  }

  /**
   * リロードする。
   * @return {Promise}
   */
  async reload(){
    let facilityUsers = [];
    try {
      // 施設利用者情報を取得する。
      let params = new URLSearchParams({facility_id: this.facilityId, year: this.year, month: this.month});
      let res = await CustomAjax.get('/group_home/service/facility_user/billing_target/get?' + params.toString());
      facilityUsers = await res.json();
    } catch (error) {
      // TODO: エラーを表示するUIが存在しない。
    }
    this.setFacilityUsers(facilityUsers);

    // 施設利用者情報をソートする。
    this.sortFacilityUsers();

    // 再描画する。
    await this.draw();
  }
}
