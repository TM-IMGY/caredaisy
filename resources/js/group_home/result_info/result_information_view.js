import CustomAjax from '../../lib/custom_ajax.js';

/**
 * 実績情報画面の管理クラス。
 * 選択している施設利用者の情報のやり取りなどを一元管理する。
 */
export default class ResultInformationView{
  /**
   * コンストラクタ
   * @param {FacilityUserTable} facilityUserTable 施設利用者テーブル
   * @param {NationalHealth} nationalHealth 国保連請求
   * @param {ResultRegistration} resultRegistration 実績登録タブ
   * @param {Stayout} stayOut 外泊日登録タブ
   * @param {TabManager} tabManager タブ管理クラス
   * @param {YearMonthPulldown} ymPulldown 対象年月プルダウン
   * @param {FacilityUserInfoHeader} facilityUserInfoHeader 利用者情報ヘッダ
   */
  constructor(facilityUserTable, nationalHealth, resultRegistration, stayOut, tabManager, ymPulldown, facilityUserInfoHeader){
    this.facilityUserTable = facilityUserTable;
    this.nationalHealth = nationalHealth;
    this.resultRegistration = resultRegistration;
    this.stayOut = stayOut;
    this.tabManager = tabManager;
    this.facilityUserInfoHeader = facilityUserInfoHeader;

    let ym = ymPulldown.getSelectedValue();

    this.facilityUserId = null;
    this.year = ym.year;
    this.month = ym.month;

    // タブ管理クラスに各タブをセットする。
    tabManager.addNotification(stayOut.elementID, stayOut.setActive.bind(stayOut));
    tabManager.addNotification(resultRegistration.elementID, resultRegistration.setActive.bind(resultRegistration));
    tabManager.addNotification(nationalHealth.elementID, nationalHealth.setActive.bind(nationalHealth));

    // 対象年月プルダウンの情報の変更があった場合の通知先として自身を設定する。
    ymPulldown.addNotification(this.changeYm.bind(this));

    // 施設利用者テーブルの情報の変更があった場合の通知先として自身を設定する。
    facilityUserTable.addNotification(this.changeFacilityUser.bind(this));

    // 外泊登録日タブの情報の変更があった場合の通知先として自身を設定する。
    stayOut.setNotification(this.changeStayOut.bind(this));

    // 実績登録タブの情報の変更があった場合の通知先として自身を設定する。
    resultRegistration.addNotification(this.changeResult.bind(this));

    // 国保連請求タブの情報の変更があった場合の通知先を設定する。
    nationalHealth.addNotification(this.changeApproval.bind(this));

    // タブは初期は一番左を選択する(左から右の流れで登録を進めていく設計思想になっている)。
    this.tabManager.clickWithIndex(0);

    let record = null;
    let facilityUserId = sessionStorage.getItem('selectedUserId');

    // 前画面で選択した利用者を選択、選択した利用者がいないまたは利用者を選択していない場合は最上部の利用者選択
    if(document.getElementById("table_facility_user_id" + facilityUserId)){
      record = document.getElementById("table_facility_user_id" + facilityUserId);
      record.scrollIntoView();
    }else{
      if(document.getElementById("facility_user_tbody").childElementCount > 0){
        record = document.getElementById("facility_user_tbody").firstElementChild;
      }
    }
    // レコードの有無を確認する。
    if(record) {
      facilityUserTable.clickRecord(record);
    }
  }

  async changeApproval(){
    await this.facilityUserTable.reload();
    await this.facilityUserInfoHeader.setFacilityUserForResultInfo({
      facilityUserID: this.facilityUserId,
      userName: null,
      year: this.year,
      month: this.month
    });
  }

  /**
   * 施設利用者の変更を管理する。
   * TODO: 実績情報画面で取りまわすfacilityUserオブジェクトの構造が何でもありになっているのでクラスとして切り出す。
   * @param {Object} facilityUser key: careLevelName, facilityUserID, userName
   * @return {Promise}
   */
  async changeFacilityUser(facilityUser){
    // 状態を更新する。
    this.facilityUserId = facilityUser.facilityUserID;

    // 施設利用者の介護情報が非該当の場合、国保連請求タブと実績情報タブを非表示にする。
    if(facilityUser.careLevelName === '非該当'){
      // 参照しているタブが非表示の対象の場合は外泊日登録に切り替える。
      if([this.nationalHealth.elementID, this.resultRegistration.elementID].includes(this.tabManager.getActiveContentsId())){
        this.tabManager.clickWithIndex(0);
      }
      this.tabManager.hideWithContentsId(this.nationalHealth.elementID);
      this.tabManager.hideWithContentsId(this.resultRegistration.elementID);

      // 外泊日登録タブに施設利用者の変更を通知する。
      this.stayOut.setParam(facilityUser);

    // 施設利用者の介護情報が非該当以外の場合
    } else {
      // 国保連請求タブと実績情報タブを表示する。
      this.tabManager.showWithContentsId(this.nationalHealth.elementID);
      this.tabManager.showWithContentsId(this.resultRegistration.elementID);

      // 外泊日登録タブに施設利用者の変更を通知する。
      this.stayOut.setParam(facilityUser);

      // 実績登録タブに施設利用者の変更を通知する。
      await this.resultRegistration.setFacilityUser(facilityUser);
      // 施設利用者が選択されている場合
      if(this.facilityUserId){
        // 実績登録タブに施設利用者の外泊日をセットする。
        let stayOutDays = await this.getFacilityUserStayOutDays();
        this.resultRegistration.setStayOutDays(stayOutDays);
        // 実績登録タブに施設利用者の入居日までの日付をセットする。
        let startDates = await this.getFacilityUserStartDates();
        this.resultRegistration.setStartDates(startDates);
        // 実績登録タブに施設利用者の退去日からの日付をセットする。
        let endDates = await this.getFacilityUserEndDates();
        this.resultRegistration.setEndDates(endDates);
      }else{
        this.resultRegistration.setStayOutDays([]);
        this.resultRegistration.setStartDates([]);
        this.resultRegistration.setEndDates([]);
      }

      // 実績登録タブをリロードする。
      await this.resultRegistration.reload();

      // 国保連請求に施設利用者の変更を通知する。
      await this.nationalHealth.setFacilityUser(facilityUser);
    }
  }

  /**
   * 実績登録タブの情報の変更を管理する。
   * @return {Promise}
   */
  async changeResult(){
    await this.facilityUserTable.reload();
  }

  /**
   * 外泊日登録タブの情報の変更を管理する。
   * TODO: 通知側から更新内容を共有されるのが理想。
   * @return {Promise}
   */
  async changeStayOut(){
    // 施設利用者テーブルの施設利用者情報ヘッダに更新を通知する。
    await this.facilityUserTable.facilityUserInfoHeader.reload();

    // 実績登録タブに施設利用者の外泊日をセットする。
    let stayOutDays = await this.getFacilityUserStayOutDays();
    this.resultRegistration.setStayOutDays(stayOutDays);
      // 実績登録タブに施設利用者の入居日までの日付をセットする。
      let startDates = await this.getFacilityUserStartDates();
      this.resultRegistration.setStartDates(startDates);
      // 実績登録タブに施設利用者の退去日からの日付をセットする。
      let endDates = await this.getFacilityUserEndDates();
      this.resultRegistration.setEndDates(endDates);

    // 実績登録タブをリロードする。
    await this.resultRegistration.reload();
  }

  /**
   * 対象年月の変更を管理する。
   * @param {Object} ym
   * @return {Promise}
   */
  async changeYm(ym){
    this.year = ym.year;
    this.month = ym.month;
    // 施設利用者テーブルに対象年月の変更を通知する。
    this.facilityUserTable.setYm(ym.year, ym.month);
    // 施設利用者テーブルをリロードする。
    await this.facilityUserTable.reload();

    // 実績登録タブに対象年月の変更を通知する。
    await this.resultRegistration.setYm(ym.year, ym.month);
    // 施設利用者が選択されている場合
    if(this.facilityUserId){
      // TODO: 非該当による表示切替の処理をまとめる(同じことをしている箇所が他にある)。
      let selectfaclityUser = this.facilityUserTable.facilityUsers.find(facilityUser => facilityUser.facility_user_id === this.facilityUserId);
      if(selectfaclityUser.care_level_name === '非該当'){
        if([this.nationalHealth.elementID, this.resultRegistration.elementID].includes(this.tabManager.getActiveContentsId())){
          this.tabManager.clickWithIndex(0);
        }
        this.tabManager.hideWithContentsId(this.nationalHealth.elementID);
        this.tabManager.hideWithContentsId(this.resultRegistration.elementID);
      // 施設利用者の介護情報が非該当以外の場合
      } else {
        // 国保連請求タブと実績情報タブを表示する。
        this.tabManager.showWithContentsId(this.nationalHealth.elementID);
        this.tabManager.showWithContentsId(this.resultRegistration.elementID);
      }
      
      // 実績登録タブに施設利用者の変更を通知する。
      await this.resultRegistration.setFacilityUser({
        facilityUserID: this.facilityUserId});
      // 実績登録タブに施設利用者の外泊日をセットする。
      let stayOutDays = await this.getFacilityUserStayOutDays(this.facilityUserId);
      this.resultRegistration.setStayOutDays(stayOutDays);
      // 実績登録タブに施設利用者の入居日までの日付をセットする。
      let startDates = await this.getFacilityUserStartDates();
      this.resultRegistration.setStartDates(startDates);
      // 実績登録タブに施設利用者の退去日からの日付をセットする。
      let endDates = await this.getFacilityUserEndDates();
      this.resultRegistration.setEndDates(endDates);
    }
    // 実績登録タブをリロードする。
    await this.resultRegistration.reload();

    // 国保連請求タブに対象年月の変更を通知する。
    await this.nationalHealth.setYm(ym);

    // 利用者情報ヘッダを更新する。
    await this.facilityUserInfoHeader.setFacilityUserForResultInfo({
      facilityUserID: this.facilityUserId,
      userName: null,
      year: this.year,
      month: this.month
    });

    if(!this.facilityUserId){
      if(this.facilityUserTable.getVisibleFacilityUsers().length > 0) {
        let record = document.getElementById("facility_user_tbody").firstElementChild;
        record.scrollIntoView();
        await this.facilityUserTable.clickRecord(record);
      }
    }
  }

  /**
   * 施設利用者の外泊日を全て返す。
   * 以前は個々のタブで取得をしていたがパフォーマンス上よくないのと、同じものを呼ぶのであれば一か所の方がデグレ防止になるので、
   * 可能な限りここにまとめることにした。
   * @return {Promise}
   */
  async getFacilityUserStayOutDays(){
      // 施設利用者情報を取得する。
      let params = new URLSearchParams({facility_user_id: this.facilityUserId, year: this.year, month: this.month});
      let res = await CustomAjax.get('/group_home/service/facility_user/stay_out_days/get?' + params.toString());
      let data = await res.json();
      return data;
  }

  async getFacilityUserStartDates(){
      // 施設利用者の入居日までの日付を数値で取得する。
      let params = new URLSearchParams({facility_user_id: this.facilityUserId, year: this.year, month: this.month});
      let res = await CustomAjax.get('/group_home/service/facility_user/start_dates/get?' + params.toString());
      let data = await res.json();
      return data;
  }

  async getFacilityUserEndDates(){
      // 施設利用者の退去日からの日付を数値で取得する。
      let params = new URLSearchParams({facility_user_id: this.facilityUserId, year: this.year, month: this.month});
      let res = await CustomAjax.get('/group_home/service/facility_user/end_dates/get?' + params.toString());
      let data = await res.json();
      return data;
  }
}
