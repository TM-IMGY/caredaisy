/**
 * サービス計画書1タブで閲覧できるビュー
 */

import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import facilityUserInfoHeader from '../../lib/facility_user_info_header.js'
import Common from './common.js'

export default class ServicePlan1{
  constructor(){
    this.facilityUserInfoHeader = new facilityUserInfoHeader();
    this.common = new Common();
    Object.defineProperty(this, "DELIVERY_DATE_UPDATABTN_SP1", {
      value: 'delivery_date_updatebtn_service_plan1',
    });
    this.REQUEST_HEADER = {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN};
    this.elementID = 'tm_contents_service_plan1';
    this.element = document.getElementById(this.elementID);
    this.element.querySelectorAll('.header_lbl_independence').forEach(data =>{
      data.style.display = "flex";
    })
    this.elementCarePlanPeriodStart = document.getElementById('sp1_care_plan_period_start');
    this.elementCarePlanPeriodEnd = document.getElementById('sp1_care_plan_period_end');
    this.elementFirstPlanStartPeriod = document.getElementById('first_plan_start_period');

    this.facilityUserData = {facilityUserID: null, userName: null};
    this.i_service_plan_id = null;
    this.saveFlg = false;
    this.saveStatus = null;
    this.pdfPreView = null;
    this.planId = null;
    this.plan2Obj = null;
    this.plan3Obj = null;
    this.latestServicePlanId = null;
    this.facilityUserTableSyncServer = null;
    this.replicateFlg = false;
    this.selectedRecord = null;
    this.facilityServiceInformationsData = null;
    this.NOT_MATCH_SERVICE = ' 利用者のサービス情報が登録されていない、もしくは入居日とサービス開始日が異なる日付のためケアプランが作成できません。';

    // ルーティングする各url
    this.getFacilityFristPlanInput = 'care_plan_info/service_plan1/get_fPlan_input';
    this.getUserInformationUrl = 'care_plan_info/service_plan1/user_information';
    this.getPlan1HistoryDataUrl = 'care_plan_info/service_plan1/get_history';
    this.savePlan1DataUrl = 'care_plan_info/service_plan1/save';
    this.REQUEST_CARE_PLAN_PERIOD_START_DATE = 'care_plan_info/get/care_plan_period';

    // 利用者情報ヘッダ
    this.recognitionDate = this.element.querySelector('.facility_user_info_header_recognition_date')
    this.plan1CarePeriod = this.element.querySelector('.facility_user_info_header_care_period')
    this.plan1CareLevel = this.element.querySelector('.facility_user_info_header_care_level')
    this.plan1CertificationStatus = this.element.querySelector('.facility_user_info_header_certification_status')
    this.plan1IndependenceLevel = this.element.querySelector('.facility_user_info_header_independence_level')
    this.plan1DementiaIndependence = this.element.querySelector('.facility_user_info_header_dementia_independence')

    this.historyTBody = document.getElementById('service_plan1_history_table_body');
    this.firstServicePlanId = document.getElementById('first_service_plan_id');
    this.status = document.getElementById('status');
    this.servicePlanId = document.getElementById('service_plan_id');
    this.planStartPeriod = document.getElementById('plan_start_period');
    this.planEndPeriod = document.getElementById('plan_end_period');
    this.firstTimeDivision = document.getElementById('first_time');
    this.introduceDivision = document.getElementById('introduce');
    this.continuDivision = document.getElementById('continu');

    this.title1 = document.getElementById('title1');
    this.content1 = document.getElementById('content1');
    this.title2 = document.getElementById('title2');
    this.content2 = document.getElementById('content2');
    this.title3 = document.getElementById('title3');
    this.content3 = document.getElementById('content3');
    this.title4 = document.getElementById('title4');
    this.content4 = document.getElementById('content4');
    // 算定理由表示時はthis.livingAloneからthis.otherReasonのコメントアウトを解除する
    // this.livingAlone = document.getElementById('living_alone');
    // this.handicapped = document.getElementById('handicapped');
    // this.other = document.getElementById('other');
    // this.otherReason = document.getElementById('other_reason');
    this.lastUpdateDate = document.getElementById('sp1_last_update_date');
    this.lastUpdateStatus = document.getElementById('sp1_last_update_status');

    this.fixedDate = document.getElementById('fixed_date');
    this.deliveryDate = document.getElementById('delivery_date');
    this.deliveryDateConsent = document.getElementById('delivery_date_consent');
    this.deliveryDatePlace = document.getElementById('delivery_date_place');
    this.deliveryDateRemarks = document.getElementById('delivery_date_remarks');
    this.classification4= document.getElementById('classification4');
    this.validationDisplayArea = document.getElementById("validateErrorsServicePlan1");

    this.selectedRecord = null;
    this.facilityServiceInformationsData = null;

    this.overflowStatusTmp = document.getElementById('overflow_service_plan1_status_tmp')
    this.overflowDeliveryDate = document.getElementById('overflow_service_plan1_delivery_date')
    this.overflowFixedDate = document.getElementById('overflow_service_plan1_fixed_date')
    this.overflowChangeDelivery = document.getElementById('overflow_service_plan1_change_delivery')
    this.changePlanMessage = document.getElementById("change_plan_message");

    this.pdfPreView = document.getElementById('pdf_preview_form');
    this.consecutivePdf = document.getElementById('consecutive_pdf_form');

    // 区分の制御
    this.firstTimeDivision.addEventListener('change',this.divisionChange.bind(this));
    this.introduceDivision.addEventListener('change',this.divisionChange.bind(this));
    this.continuDivision.addEventListener('change',this.divisionChange.bind(this));

    this.validateErrorsServicePopupPlan1Id = document.getElementById('validateErrorsServicePopupPlan1').innerHTML;
    this.validationDisplayAreaPopup = document.getElementById("validateErrorsServicePopupPlan1");

    // 算定理由「その他」にイベントを付与
    // 算定理由表示時はコメントアウトを解除する
    // this.other.addEventListener('change',this.otherStatusChange.bind(this));

    // 次回プランボタンにイベントを付与
    if (document.getElementById('next_plan_button') !== null){
      document.getElementById('next_plan_button').addEventListener('click',this.nextPlan.bind(this));
    }

    // 交付済みボタンにイベントを付与
    // 交付済編集時アラート適用時に"this.checkDelivery"に変更する
    if (document.getElementById('status_done') !== null){
      document.getElementById('status_done').addEventListener('click',this.deliveryBtnClick.bind(this));
    }
    // 交付済みボタンポップアップ内「はい」にイベント付与
    document.getElementById(this.DELIVERY_DATE_UPDATABTN_SP1).addEventListener('click',this.RegisterData.bind(this));
    // 交付済みボタンポップアップ内「いいえ」にイベント付与
    document.getElementById('delivery_date_cancelbtn_service_plan1').addEventListener(
      'click',
      function(){
        this.common.clearValidateDisplay(this.validationDisplayAreaPopup);
        this.overflowDeliveryDate.style.display = 'none';
        document.getElementById('validateErrorsServicePopupPlan1').innerHTML = this.validateErrorsServicePopupPlan1Id;
      }
    .bind(this));

    // 確定ボタンにイベントを付与
    if (document.getElementById('status_confirm') !== null){
      document.getElementById('status_confirm').addEventListener('click',this.fixedBtnClick.bind(this));
    }
    // 確定ボタンポップアップ内「はい」にイベント付与
    // 交付済編集時アラート適用時に"this.checkDelivery"に変更する
    document.getElementById('fixed_date_updatebtn_service_plan1').addEventListener('click',this.RegisterData.bind(this));
    // 確定ボタンポップアップ内「いいえ」にイベント付与
    document.getElementById('fixed_date_cancelbtn_service_plan1').addEventListener('click',function(){
      this.overflowFixedDate.style.display = 'none';
    }.bind(this));

    // 保存ボタンにイベントを付与
    // 交付済編集時アラート適用時に"this.checkDelivery"に変更する
    if (document.getElementById('status_tmp') !== null){
      document.getElementById('status_tmp').addEventListener('click',this.clickStatusTmpBtn.bind(this));
    }
    document.getElementById('status_tmp_updatebtn_service_plan1').addEventListener('click',this.RegisterData.bind(this));
    document.getElementById('status_tmp_cancelbtn_service_plan1').addEventListener('click',function() {
      this.overflowStatusTmp.style.display = 'none';
    }.bind(this));

    document.getElementById('change_delivery_updatebtn_service_plan1').addEventListener('click',this.RegisterData.bind(this));
    document.getElementById('change_delivery_cancelbtn_service_plan1').addEventListener('click',function(){
      this.overflowChangeDelivery.style.display = 'none';
    }.bind(this));

    // pdf出力・プレビューボタンにイベントを付与
    document.getElementById('sp1_copy').addEventListener('click',this.outputPdf.bind(this));
    document.getElementById('sp1_preview').addEventListener('click',this.outputPdf.bind(this));

    // ケアプラン期間の終了日の自動入力UIにクリックイベントを付与する
    let radioBtns = document.getElementsByName('sp1_api_radio_btn');
    Array.from(radioBtns).forEach(element => element.addEventListener('click', this.clickApiRadioBtn.bind(this)));

    // 認定情報フォーム関連
    this.APPLYING_VALUE = 1;
    this.CERTIFIED_VALUE = 2;
    this.HALF_YEAR = 6;
    this.carePeriodStart = document.getElementById('care_period_start');
    this.carePeriodEnd = document.getElementById('care_period_end');
    this.CareLevel = document.getElementById('care_level');
    this.certificationStatus = document.getElementById('certification_status');
    this.approvalFormRecognitionDate = document.getElementById('recognition_date');
    this.disabledTargetDate = document.querySelectorAll('.disabled_target_date');
    this.endDateBtns = document.querySelectorAll('.sp1_approval_api_radio_btn');
    // 認定情報有効終了日の自動入力UIにクリックイベントを付与する
    let approvalRadioBtn = document.getElementsByName('sp1_approval_api_radio_btn');
    Array.from(approvalRadioBtn).forEach(element => element.addEventListener('click', this.clickApprovalApiBtn.bind(this)));
    // 認定状況変更イベント
    this.certificationStatus.addEventListener('change',this.changeStatus.bind(this))
    // 各ボタンを非活性化
    this.switchingActivityForStatusBtns(true);
  }

  /**
   * 保存・確定・交付済 ボタンの活性切替
   * @param {boolean} bool
   */
  switchingActivityForStatusBtns(bool) {
    document.getElementById('status_tmp').disabled = bool;
    document.getElementById('status_confirm').disabled = bool;
    document.getElementById('status_done').disabled = bool;
  }
  /**
   * 確定・交付済 ボタンの活性切替
   * @param {boolean} bool
   */
  switchingActivityForDeactivationTargetBtns(bool) {
    document.querySelectorAll('.deactivation_target').forEach((e) => {
      e.disabled = bool;
    });
    document.getElementById('status_tmp').disabled = false;
  }

  async outputPdf(event)
  {
    let mode = event.target.id;

    if(this.facilityUserData.facilityUserID == null || this.planId == null){
      return;
    }
    this.createParameter('facility_user_id',this.facilityUserData.facilityUserID,mode);
    this.createParameter('service_plan_id',this.planId,mode);

    let serviceCount = await this.checkEffectiveService();

    if(serviceCount == 0) {
      const SERVICE_EMPTY_MSG = 'ケアプラン有効期間内で有効なサービスが<br>利用者へ登録されておりません。'
      this.showPopup(SERVICE_EMPTY_MSG)
      return;
    }

    if (mode == "sp1_preview") {
      this.pdfPreView.submit();
    } else {
      let plan2 = await CustomAjax.post('/group_home/care_plan_info/service_plan1/existence',this.REQUEST_HEADER,{
        service_plan_id:this.planId,
        facility_user_id:this.facilityUserData.facilityUserID
      });
      if(plan2 == 0){return;}
      this.consecutivePdf.submit();
    }
  }

  /**
   * ケアプラン期間内に有効なサービスが存在するかチェックする
   * @returns
   */
  async checkEffectiveService()
  {
    let serviceCount = await CustomAjax.get('/group_home/care_plan_info/check_service?'
      + 'facility_user_id=' + this.facilityUserData.facilityUserID
      + '&service_plan_id=' + this.planId,
      this.REQUEST_HEADER,
    );

    return await serviceCount.json()
  }

  createParameter(name,value,mode)
  {
    let param = document.createElement('input');
    param.type = 'hidden';
    param.name = name;
    param.value = value;

    if (mode == "sp1_preview") {
      this.pdfPreView.appendChild(param);
    } else {
      this.consecutivePdf.appendChild(param);
    }
  }

  /**
   * @param {bool} status 表示のブーリアン値
   */
    setActive(status){
    }

  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  async setFacilityUser(user){
    this.facilityUserData = user;
    this.saveFlg = false;
    let selectServicePlan = null;
    await this.activeServicePlan1();
    await this.getFacilityServiceInformations();
    // ケアプラン期間自動入力ラジオボタンを初期化する
    document.querySelectorAll('.sp1_api_radio_btn').forEach(element => element.checked = false);

    //利用者の選択
    if (user.facilityUserID){
      document.getElementById('table_facility_user_id' + user.facilityUserID).click()
    }

    //履歴の選択
    // 保存処理時
    if (this.i_service_plan_id){
      selectServicePlan = document.getElementById('service_plan_id' + this.i_service_plan_id)
      if (selectServicePlan) selectServicePlan.click()
    // 画面遷移時
    }else{
      selectServicePlan = this.historyTBody.firstElementChild;
      if (selectServicePlan) selectServicePlan.click()
    }
  }

  /**
   * 該当利用者の計画書情報取得処理
   * @return {Promise}
   */
  async activeServicePlan1(){
    // 履歴リストを初期化
    this.historyTBody.textContent = null;
    // 履歴レコードの選択状態をクリアする
    this.clearRecordSelectionState();
    // フォームをクリアする
    this.formClear();
    // 各ボタンを非活性化
    this.switchingActivityForStatusBtns(true);
    // 認定情報フォームを初期化
    this.clearApprovalContents()

    // 4つ目の区分表示
    this.classification4.style.display = 'none';
    if(this.facilityUserData.facilityUserID !== void 0){
      let flg = await CustomAjax.post(this.getFacilityFristPlanInput,{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},{facility_user_id:this.facilityUserData.facilityUserID});
      Object.keys(flg).forEach(key =>{
        if(flg[key]['first_plan_input'] == 1){
          this.classification4.style.display = 'table-row';
        }
      });
    }

    // 選択している施設利用者のケアプラン履歴を取得する
    let createdList = await CustomAjax.post(
      this.getPlan1HistoryDataUrl,
      {'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN},
      {facility_user_id:this.getFacilityUserId()}
    );

    // 履歴テーブルを作成する
    Object.keys(createdList).forEach((key)=>{
      let deliveryDate = '';
      // 交付済み日時を整形する
      if (createdList[key]['delivery_date']) {
        let time = createdList[key]['delivery_date'].slice(11,-3);
        let formatDate = createdList[key]['delivery_date'].slice(0,-9).replace(/-/g,'/');
        deliveryDate = formatDate + " " + time;
      }
      let record = document.createElement('tr');
      record.setAttribute('id','service_plan_id' + createdList[key]['id'])

      let historyTdItems = [
        {displayContent : deliveryDate},
        {displayContent : createdList[key]['start_date'] ? createdList[key]['start_date'].replace(/-/g,'/') : null},
        {
          displayContent : createdList[key]['end_date'] ? createdList[key]['end_date'].replace(/-/g,'/') : null,
          dusk:'tbody_end_date' + createdList[key]['id']
        },
        {displayContent : createdList[key]['care_level_name']},
        {displayContent : createdList[key]['fixed_date'] ? createdList[key]['fixed_date'].replace(/-/g,'/') : ''},
        {displayContent : createdList[key]['plan_start_period'].replace(/-/g,'/')},
        {displayContent : createdList[key]['plan_end_period']}
      ]

      Object.keys(historyTdItems).forEach((item) => {
        let td = document.createElement('td');
        td.textContent = historyTdItems[item].displayContent;
        if ('dusk' in historyTdItems[item]) {
          td.setAttribute('dusk', historyTdItems[item].dusk)
        }
        record.appendChild(td);
      })

      // td要素にクラス付与
      Array.from(record.children).forEach((child)=>{
        child.className = 'text_data_service_plan1';
      });

      // サービス計画書3にサービス情報を送る
      this.plan3Obj.setServiceHistory(createdList);

      // レコードにクリックイベントを付与する
      record.addEventListener('click', async () => {
        if(this.selectedRecord){
          this.selectedRecord.classList.remove('sp1_select_record');
        }
        this.selectedRecord = record;
        this.selectedRecord.classList.add('sp1_select_record');
        this.replicateFlg = false;

        this.servicePlanId.value = createdList[key]['id'];
        this.firstServicePlanId.value = createdList[key]['id'];

        let request = {service_plan_id:createdList[key]['id'], 'facility_user_id':this.getFacilityUserId()};
        let data = await CustomAjax.post(
          this.getPlan1HistoryDataUrl,
          {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
          request
        );
        this.setData(data);

        this.common.clearValidateDisplay(this.validationDisplayArea);
        // 全ての保存ボタンを活性化する
        this.switchingActivityForStatusBtns(false);
        // ケアプラン期間自動入力ラジオボタンを初期化する
        document.querySelectorAll('.sp1_api_radio_btn').forEach(element => element.checked = false);
        // サービス計画書2にサービスIDを送る
        this.plan2Obj.setServiceID(this.getServicePlan2Parameter(createdList[key]['id'],data['status']));
        // サービス計画書3にサービスIDを送る
        await this.plan3Obj.setServiceID(this.getServicePlan2Parameter(createdList[key]['id'],data['status']));

      });

      this.historyTBody.appendChild(record);
      this.historyTBody.firstChild && this.historyTBody.firstChild.click();
    });

    // 履歴があれば、ボタン活性化
    if(createdList.length > 0){
      this.switchingActivityForStatusBtns(false);
    }
  }

  /**
   * 介護計画書のデータをセットする
   * @param {Object} params
   */
  setData(params){
    document.querySelectorAll('.sp1_api_radio_btn').forEach((e) => { e.checked = false;})

    this.planId = params['service_plan_id'];
    this.saveFlg = true;

    this.servicePlanId.value = params['id'];
    this.firstServicePlanId.value = params['id'];
    this.status.value = params['status'];

    this.clearApprovalContents()
    this.createCareLevelForm(params.care_levels);
    this.setLatestApproval(params)

    this.setCertificationStatus(params['recognition_date']);
    this.setCarePeriod(params['care_period_start'],params['care_period_end']);

    this.plan1CareLevel.textContent = params['care_level_name'];
    this.plan1CareLevel.setAttribute('value',params['care_level_name']);

    if(params['certification_status'] == 2){
      this.plan1CertificationStatus.textContent = '認定済'
    }else{
      this.plan1CertificationStatus.textContent = '申請中'
    }
    this.plan1CertificationStatus.setAttribute('value',params['certification_status']);
    params.independenceLevelName = this.setIndependenceLevel(params['independence_level']);
    params.dementiaIndependenceName = this.setDementiaIndependence(params['dementia_level']);

    this.plan1CertificationStatus.value = params['certification_status'];
    this.planStartPeriod.value = params['plan_start_period'];
    this.planEndPeriod.value = params['plan_end_period'];

    this.divisionSelect(params['plan_division']);
    this.title1.value = params['title1'];
    this.content1.value = params['content1'];
    this.title2.value = params['title2'];
    this.content2.value = params['content2'];
    this.title3.value = params['title3'];
    this.content3.value = params['content3'];
    this.deliveryDate.value = params['delivery_date'] ? params['delivery_date'].replace(' ','T').slice(0,-3) : '';
    this.deliveryDateConsent.value = params['consent'];
    this.deliveryDatePlace.value = params['place'];
    this.deliveryDateRemarks.value = params['remarks'];
    this.fixedDate.value = params['fixed_date'];
    // 算定理由表示時はthis.livingAloneからthis.otherReason.valueのコメントアウトを解除する
    // this.livingAlone.checked = params['living_alone'];
    // this.handicapped.checked = params['handicapped'];
    // this.other.checked = params['other'];
    // if (this.other.checked) {
    //  this.otherReason.disabled =false;
    // }
    // this.otherReason.value = params['other_reason'];

    let lastUpdateDate = new Date(params['updated_at']);
    let lastUpdateYear = lastUpdateDate.getFullYear();
    let lastUpdateMonth = (lastUpdateDate.getMonth() + 1).toString().padStart(2, "0");
    let lastUpdateDay = lastUpdateDate.getDate().toString().padStart(2, "0")
    this.lastUpdateDate.textContent = lastUpdateYear + "/" + lastUpdateMonth + "/" +lastUpdateDay;

    this.lastUpdateStatus.textContent = this.statusCheck(params['status']);

    this.latestServicePlanId = null;

    // ケアプラン期間
    this.elementCarePlanPeriodStart.value = params.start_date;
    this.elementCarePlanPeriodEnd.value = params.end_date;
    this.elementFirstPlanStartPeriod.value = params.first_plan_start_period;

    // 計画書2に利用者情報ヘッダに表示するパラメータを送る
    this.plan2Obj.setFacilityUserInformationHeader(params);
  }

  /**
   * 算定理由 その他：理由の制御
   */
  // 算定理由表示時はコメントアウトを解除する
  // otherStatusChange(){
  //   let flg = this.other.checked;
  //   let otherReasonBox = this.otherReason;
  //     if(flg){
  //       otherReasonBox.disabled =false;
  //     }else{
  //       otherReasonBox.disabled = true;
  //       otherReasonBox.value = '';
  //     }
  //   }

  /**
   * 交付済みボタン押下時処理
   * @returns
   */
  deliveryBtnClick(){
    // 認定年月日がない場合は処理を中断
    if (this.recognitionDate.value == '') return false;

    if(this.deliveryDate.value === ''){
      let now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset())
      this.deliveryDate.value = now.toISOString().slice(0, -8);
    }
    return this.overflowDeliveryDate.style.display = 'block';
  }

  /**
   * 確定ボタン押下時処理
   * @param {*} status
   */
  fixedBtnClick(){
    // 認定年月日がない場合は処理を中断
    if (this.recognitionDate.value == '') return false;

    if(this.fixedDate.value === ''){
      let today = new Date();
      this.fixedDate.value = today.getFullYear() + "-" + ("00"+(today.getMonth()+1)).slice(-2) + "-" + ("00"+today.getDate()).slice(-2);
    }

    // 交付済みボタンポップアップ内の値を初期化
    if(this.status.value != 4){
      this.clearDeliveryDateInput();
    }
    return this.overflowFixedDate.style.display = 'block';
  }

  /**
   * 交付済みボタンポップアップ内の値を初期化
   */
  clearDeliveryDateInput(){
    let deliveryDateInput = document.querySelectorAll('.delivery-date-input');
    Array.prototype.slice.call(deliveryDateInput).forEach(data => {data.value = null;});
  }

  /**
   * 履歴のステータスが交付済
   * @param {object} event
   * @returns
   */
  async checkDelivery(event)
  {
    this.saveStatus = event.target.id;
    if (this.status.value != 4) {
      this.RegisterData()
      return;
    }

    this.changePlanMessage.nextElementSibling.className = 'sp1-btns'

    if (event.target.value == 3) {
      this.changePlanMessage.innerHTML = '交付済プランを<br>確定プランに変更しますか？';
    } else if (event.target.value == 4) {
      this.changePlanMessage.textContent = '交付済プランを変更しますか？';
    } else {
      this.changePlanMessage.innerHTML = '交付済プランを変更しますか？<br>変更すると交付済プランではなくなります。';
      this.changePlanMessage.nextElementSibling.className = 'change_plan_message'
    }

    this.overflowDeliveryDate.style.display = 'none';
    this.overflowFixedDate.style.display = 'none';
    this.overflowChangeDelivery.style.display = 'block';
  }

  /**
   * 選択した利用者のサービス情報から要介護度リストを取得する
   */
  async requestFacilityUserServiceInfo(){
    let careLevels = await CustomAjax.get('/group_home/care_plan_info/get_service?'
      + 'facility_user_id=' + this.facilityUserData.facilityUserID,
      this.REQUEST_HEADER,
    );
    careLevels = await careLevels.json();
    // 取得できなかったらfalseを返す
    if (Array.isArray(careLevels)) {
      return false;
    }
    return careLevels;
  }

  /**
   * 要介護度プルダウンを生成する
   * @param {object} careLevels
   */
  createCareLevelForm(careLevels){
    Object.entries(careLevels).forEach(([key, careLevel]) => {
      let option = document.createElement('option');
      option.value = careLevel
      option.textContent = careLevel;
      this.CareLevel.appendChild(option)
    })
  }

  /**
   * 要介護度のプルダウンリストを初期化する
   * @returns
   */
  clearCareLevelList(){
    while(this.CareLevel.lastChild){
      if (this.CareLevel.children.length == 1) {
        return;
      }
      this.CareLevel.removeChild(this.CareLevel.lastChild);
    }
  }

  /**
   * 認定状況プルダウンを初期化する
   */
  clearCertificationStatus(){
    this.certificationStatus.value = "";
  }

  /**
   * 認定情報入力フォームの各種初期化
   */
  clearApprovalContents(){
    document.querySelectorAll('.sp1_approval_api_radio_btn').forEach((e) => { e.checked = false;})
    this.clearCareLevelList();
    this.clearCertificationStatus();
    this.controlCareLevelDispflg(false,true);
  }

  /**
   * 認定情報フォームに認定情報をセットする
   */
  setLatestApproval(params){
    this.certificationStatus.value = params.certification_status ? params.certification_status : '';
    params.certification_status == this.CERTIFIED_VALUE ? this.resetDisable() : this.applyingForApproval();
    $('#care_level').val(params.care_level_name);
    // 認定情報画面で設定されている「認定情報」とケアプラン１表の「認定情報」で表示される内容が一致しない場合はブランク表示する。
    $('#care_level').val() == null ? $('#care_level').val("") : '';
    this.approvalFormRecognitionDate.value = params.recognition_date ? params.recognition_date : '';
    this.carePeriodStart.value = params.care_period_start ? params.care_period_start : '';
    this.carePeriodEnd.value = params.care_period_end ? params.care_period_end : '';
    this.setCareLevelDispflg(params.certification_status,params.care_level_dispflg)
  }

  /**
   * 介護認定度表示判断フラグを設定する
   * @param {integer} certificationStatus
   * @param {*} careLevelDispflg
   */
  setCareLevelDispflg(certificationStatus, careLevelDispflg = 1){
    // 介護認定度表示判断フラグを設定する
    if (certificationStatus === this.CERTIFIED_VALUE){
      this.controlCareLevelDispflg(true,true);
    } else if(certificationStatus === this.APPLYING_VALUE && careLevelDispflg === 0){
      // 申請中・介護認定度表示判断フラグチェックなし
      this.controlCareLevelDispflg(false,false);
    } else if(certificationStatus === this.APPLYING_VALUE && careLevelDispflg === 1){
      // 申請中・介護認定度表示判断フラグチェックあり
      this.controlCareLevelDispflg(true,false);
    } else {
      // 認定情報なし
      this.controlCareLevelDispflg(false,true);
    }
  }

  /**
   * 介護認定度表示判断フラグを制御する
   * @param {boolean} check
   * @param {boolean} disable
   */
  controlCareLevelDispflg(check, disable){
    $('#care_level_dispflg').prop('checked', check).prop('disabled', disable);
  }

  /**
   * 今日の日付をオブジェクトで返す
   */
  returnToday(){
    let date = new Date();
    return {
      year:date.getFullYear(),
      month:(date.getMonth()+1).toString().padStart(2, "0"),
      day:date.getDate().toString().padStart(2, "0")
    };
  }

  /**
   * 日付を整形して返す
   * @param {object} date {year, month, day}
   * @returns
   */
  createDateFormat(date){
    return date.year + '-' + date.month + '-' + date.day;
  }

  /**
   * 次回プランボタン押下時処理
   */
  async nextPlan(){
    // 施設利用者IDを持たない時は何もしない
    if(this.getFacilityUserId() === null){
      return;
    }

    let careLevels = await this.requestFacilityUserServiceInfo();
    // 要介護度リストを取得出来なかったらポップアップを表示する
    if (careLevels === false) {
      this.showPopup(this.NOT_MATCH_SERVICE);
      return;
    }

    // フォームをリセットする
    this.formClear();

    this.replicateFlg = true;

    // 履歴レコードの選択状態をクリアする
    this.clearRecordSelectionState();

    // 認定情報フォームを初期化及び準備する
    this.clearApprovalContents();
    this.createCareLevelForm(careLevels);

    // 保存以外のボタンを非活性にする
    this.switchingActivityForDeactivationTargetBtns(true);

    // 施設利用者情報ヘッダに通知する 自立度情報を取得する
    await this.facilityUserInfoHeader.setFacilityUser(this.getFacilityUserData());

    this.planStartPeriod.value = this.createDateFormat(this.returnToday());

    this.firstTimeDivision.checked = true;
    this.title1.value = '利用者及び家族の生活に対する意向を踏まえた課題分析の結果';
    this.title2.value = '介護認定審査会の意見及びサービスの種類の指定';
    this.title3.value = '総合的な援助の方針';

    // 施設利用者情報を取得する
    let facilityUserData = await this.requestFacilityUserData();

    // 取得した施設利用者情報を利用者情報ヘッダに反映する
    if (facilityUserData.certification_status == this.CERTIFIED_VALUE) {
      this.plan1CertificationStatus.textContent = '認定済';
    } else if(facilityUserData.certification_status == this.APPLYING_VALUE){
      this.plan1CertificationStatus.textContent = '申請中';
    }
    this.plan1CertificationStatus.setAttribute('value',facilityUserData.certification_status);
    this.plan1CareLevel.textContent = facilityUserData.care_level_name;
    this.setCertificationStatus(facilityUserData['recognition_date']);
    this.setCarePeriod(facilityUserData['care_period_start'],facilityUserData['care_period_end']);

    // 認定情報フォームに値をセットする
    this.setLatestApproval(facilityUserData)

    // 最新の交付済みプランを取得する
    let deliveryData = await CustomAjax.post(
      'care_plan_info/service_plan1/get_delivery_plan',
      {'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN},
      {facility_user_id:this.facilityUserData.facilityUserID},
    );

    // 初回施設サービス作成日のデフォルト値をセットするために、交付済みケアプランの履歴があるかで分岐させる
    if (deliveryData.first_plan_start_period == null) {
      // 入居日をセットする
      let params = new URLSearchParams({facility_user_id: this.getFacilityUserId()});
      let res = await CustomAjax.get('care_plan_info/get_user_start_date?' + params.toString());
      let value = await res.json();
      this.elementFirstPlanStartPeriod.value = value;
    } else {
      // 最新の初回施設サービス作成日をセットする
      this.elementFirstPlanStartPeriod.value = deliveryData.first_plan_start_period;
    }

    if (Object.keys(deliveryData).length == 0) {
      // ケアプラン期間の自動選択をする
      await this.autoSelectCarePlanPeriod();
    } else {
      this.latestServicePlanId = deliveryData.id;
      this.planStartPeriod.value = deliveryData.plan_start_period;
      this.planEndPeriod.value = deliveryData.plan_end_period;
      this.divisionSelect(3);
      this.content1.value = deliveryData.content1;
      this.content2.value = deliveryData.content2;
      this.content3.value = deliveryData.content3;
      // ケアプラン期間
      this.elementCarePlanPeriodStart.value = deliveryData.start_date;
      const NOTIFICATION_MSG = '前回プランの情報を元に介護計画書１を表示しています。<br>期間や作成日など必要な情報を編集し保存してください。<br>保存を押すと前回プランの情報で介護計画書２と週間サービス計画書を作成します。'
      this.showPopup(NOTIFICATION_MSG)
    }

    // 介護計画書2に通知する
    this.plan2Obj.setServiceID(this.getServicePlan2Parameter(null));
    await this.plan3Obj.setServiceID(this.getServicePlan2Parameter(null));
    // クラスプロパティを更新する
    this.saveFlg = true;
  }

  /**
   * フォームに入力されている値を初期化
   * @param {integer} num
   * @return void
   */
  formClear(){
    this.common.clearValidateDisplay(this.validationDisplayArea);

    // 初回、紹介、継続ボタンを初期化する
    document.querySelectorAll(".plan_division").forEach((e) => { e.checked = false;});

    let fluctuationInput = document.querySelectorAll('.fluctuation-input');
    Array.prototype.slice.call(fluctuationInput).forEach(data => {data.value = null;})

    // 生活援助中心型の算定理由を初期化する
    // 算定理由表示時はコメントアウトを解除する
    // document.querySelectorAll(".calculate_reason").forEach((e) => { e.checked = false;});
    // this.otherStatusChange();

    // 交付済みボタン用ポップアップを初期化する
    this.clearDeliveryDateInput();
    // 保存、確定、交付済ボタンを初期化する
    document.querySelectorAll('.sp1_last_update_text').forEach((e) => { e.textContent = "";});
    // ケアプラン期間のチェック解除
    document.querySelectorAll('.sp1_api_radio_btn').forEach((e) => { e.checked = false;})

    // 状態を更新する
    this.servicePlanId.value = null;
    this.firstServicePlanId.value = null;
    this.status.value = null;
    this.fixedDate.value = null;
    this.planId = null;
    this.latestServicePlanId = null;
  }

  // 施設利用者のサービス情報を取得する
  async getFacilityServiceInformations(){
    let data = await CustomAjax.post(
      'user_info/service/ajax',
      {'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN},
      {facility_user_id:this.getFacilityUserId()}
    );
    this.facilityServiceInformationsData = data;
  }

  /**
   * ケアプラン開始日と作成日の関係をチェックする
   * @param {object} callBack
   * @returns
   */
  checkBeforeOrAfterTheCarePlanStart(callBack = null){
    // ケアプラン開始日より作成日の方が後の日付なら確認ダイアログを表示
    if (this.planStartPeriod.value > this.elementCarePlanPeriodStart.value) {
      const SELECT_MSG = '作成日がケアプラン開始日より後の日付が入力されていますが保存しますか？';
      this.showSelectPopup(SELECT_MSG, callBack);
      return;
    }

    this.exeCallBack(callBack);
  }

  /**
   * コールバック関数を実行する
   * @param {?object} callBack
   */
  exeCallBack(callBack = null){
    if(callBack){
      callBack();
    }else{
      return;
    }
  }

  /**
   * 各保存押下時処理
   * @param {Event} event
   * @returns {Promise}
   */
  async RegisterData(event){
    this.common.clearValidateDisplay(this.validationDisplayArea);

    // クラスの状態を更新する
    this.saveStatus = event.target.id;

    // 施設利用者がない場合、または保存が偽判定の場合は終了する
    if(this.getFacilityUserId() === null || this.saveFlg == false){
      return;
    }

    if(this.saveStatus === this.DELIVERY_DATE_UPDATABTN_SP1) {
      this.common.clearValidateDisplay(this.validationDisplayAreaPopup);

      if(this.facilityUserData.facilityUserID == null){
        this.noAvailableCareplan();
        return;
      }

      let facilityServiceInformations = this.facilityServiceInformationsData;
      let flg = 1;
      let useStartMin = null;
      let useEndMax = null;
      let useStartBlank = null;
      let useEndBlank = null;
      const FACILITY_INFO_ARRAY = facilityServiceInformations['facility_infomations'];

      // 施設利用者の取得したサービスの日付情報を変数に格納する
      for(let i = 0; i < FACILITY_INFO_ARRAY.length; i++){
        if(useStartMin == null || useStartMin > FACILITY_INFO_ARRAY[i]['use_start']){
          useStartMin = FACILITY_INFO_ARRAY[i]['use_start'];
        }
        if(useEndMax == null || useEndMax < FACILITY_INFO_ARRAY[i]['use_end']){
          useEndMax = FACILITY_INFO_ARRAY[i]['use_end'];
        }
        useEndBlank = FACILITY_INFO_ARRAY[i]['use_end'];
        // サービスが複数ありそれぞれの間にサービス無効期間がある場合は、その期間が交付するケアプランの期間内に含まれていないか確認する
        if(i > 0){
          const MILLISECONDS_OF_A_DAY = 86400000;
          if(MILLISECONDS_OF_A_DAY < (Date.parse(useStartBlank) - Date.parse(useEndBlank))){
            if((this.elementCarePlanPeriodStart.value < useEndBlank && this.elementCarePlanPeriodEnd.value > useEndBlank) ||
              (this.elementCarePlanPeriodStart.value < useStartBlank && this.elementCarePlanPeriodEnd.value > useStartBlank)){
              flg = 0;
            }
          }
        }
        useStartBlank = FACILITY_INFO_ARRAY[i]['use_start'];
      };

      if(!(useStartMin <= this.elementCarePlanPeriodStart.value &&
        useEndMax >= this.elementCarePlanPeriodEnd.value)){
        flg = 0;
      }

      if(flg === 0){
        this.noAvailableCareplan();
        return;
      }
      document.getElementById('validateErrorsServicePopupPlan1').innerHTML = this.validateErrorsServicePopupPlan1Id;
    }
    // 各種ポップアップを非表示にする
    $('.overflow_service_plan1').css('display', 'none');
    this.requestSave();
  }

  /**
   * 登録処理
   */
  async requestSave(){
    await CustomAjax.send(
      'POST',
      this.savePlan1DataUrl,
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      this.createRegisterDataParameter(),
      'callRegister',
      this
    );
  }

  /**
   * 保存ボタン押下時イベント
   * @param {*} event
   */
  clickStatusTmpBtn(event){
    // 現ステータスが"交付済以外"で保存ボタン押下時はポップアップ内の値をクリアする
    if (this.status.value != 4) {
      this.clearDeliveryDateInput();
      this.fixedDate.value = null;
    }

    this.checkBeforeOrAfterTheCarePlanStart(this.checkOneYearDifferenceOfCarePlanPeriod.bind(this, event));
  }

  /**
   * 保存ボタン押下時にケアプラン期間が1年以上になる場合、ポップアップを表示させる
   * @param {*} event
   * @returns
   */
  checkOneYearDifferenceOfCarePlanPeriod(event){
    let carePlanPeriodStart = new Date($('#sp1_care_plan_period_start').val());
    let carePlanPeriodEnd   = new Date($('#sp1_care_plan_period_end').val());
    carePlanPeriodStart.setFullYear(carePlanPeriodStart.getFullYear() + 1);

    if (carePlanPeriodStart <= carePlanPeriodEnd) {
      $('#overflow_service_plan1_status_tmp').show();
      return;
    }
    this.RegisterData(event);
  }

  noAvailableCareplan(){
    const ERROR_MESSAGE_DATE = 'ケアプラン有効期間内で\n有効なサービスが利用者へ登録されておりません。';
    this.simpleValidateDisplay(ERROR_MESSAGE_DATE);
    return;
  }

  simpleValidateDisplay(errorBody){
    const UL = document.createElement('ul');
    const LI = document.createElement('li');
    let validationDisplayUl = this.validationDisplayAreaPopup;
    let errorMessage = document.createTextNode(errorBody);

    LI.appendChild(errorMessage);
    validationDisplayUl.appendChild(UL).appendChild(LI).classList.add('warning');
  }

  /**
   * @param {Object} json {service_plan_id: string}
   * @returns {Promise}
   */
  async callRegister(json){
    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;
    if(json){
      this.i_service_plan_id = json['service_plan_id'];
      await this.facilityUserInfoHeader.setFacilityUser(this.getFacilityUserData());
      // アラート更新のために利用者一覧テーブルを更新する。 nullを送っておく
      this.facilityUserTableSyncServer({facilityID:null});
      // return this.setFacilityUser(this.getFacilityUserData());
    };
  }

  /**
   * @param {Number} id
   * @param {Number} status
   * @returns {Object}
   */
  getServicePlan2Parameter(id, status = null){
    let params = {}
    params['id'] = id;
    params['status'] = status == null ? this.status.value : status
    return params;
  }

  validateDisplay(errorBody){
    this.common.validateDisplay(errorBody, this.validationDisplayArea)
    $('.overflow_service_plan1').css('display', 'none');
  }

  /**
   * 選択した計画書区分別制御
   * @param {*} event
   */
  divisionChange(event){
    let id = event.target.id
    let flg = event.target.checked;
    if(id == 'first_time' && flg){
      this.introduceDivision.checked = false;
      this.continuDivision.checked = false;
    }
    else if((id == 'introduce' || id == 'continu') && flg){
      this.firstTimeDivision.checked = false;
    }
  }

  /**
   * 計画書区分の選択
   * @param {number} num
   * @returns
   */
  divisionSelect(num){
    document.querySelectorAll(".plan_division").forEach((e) => { e.checked = false;});
    if(num == 5){
      this.introduceDivision.checked = true;
      this.continuDivision.checked = true;
      return;
    }
    document.querySelector("input[name='plan_division'][value='" + num +"']").checked = true;
  }

  /**
   * サービス計画書2にサービスIDを送る
   */
  setServicePlan2(obj){
    this.plan2Obj = obj;
  }

  /**
   * サービス計画書3にサービスIDを送る
   */
  setServicePlan3(obj){
    this.plan3Obj = obj;
  }

  statusCheck(param)
  {
    let statusName;
    switch (param) {
      case 1:
      case 2:
        statusName = '保存'
        break;

      case 3:
        statusName = '確定'
        break;

      case 4:
        statusName = '交付済'
        break;

      default:
        break;
    }
    return statusName;
  }

  setCarePeriod(period_start,period_end){
    if (period_start == null && period_end == null) {
      this.plan1CarePeriod.textContent = '';
      return;
    }
    let care_period_start = this.getRuleBasedDateFormat(period_start);
    let care_period_end = this.getRuleBasedDateFormat(period_end);
    this.plan1CarePeriod.textContent = care_period_start + ' - ' + care_period_end;
  }

  setCertificationStatus(param){
    this.recognitionDate.textContent = param != null ? this.getRuleBasedDateFormat(param) : '';
  }

  getRuleBasedDateFormat(dateStr){
    let date = new Date(dateStr);
    return date.toLocaleDateString(); // yyyy/mm/dd
  }

  /**
   * 利用者情報ヘッダの「障害高齢者自立度」にデータをセット
   * @param {number} param
   */
  setIndependenceLevel(param){
    let independenceLevelName = this.facilityUserInfoHeader.getIndependenceLevelName(param);
    this.plan1IndependenceLevel.textContent = independenceLevelName;
    if(param == null){
      param = "";
    }
    this.plan1IndependenceLevel.setAttribute('value',param);
    return independenceLevelName;
  }

  /**
   * 利用者情報ヘッダの「認知症高齢者自立度」にデータをセット
   * @param {number} param
   */
  setDementiaIndependence(param){
    let dementiaIndependenceName = this.facilityUserInfoHeader.getDementiaIndependenceName(param);
    this.plan1DementiaIndependence.textContent = dementiaIndependenceName;
    if(param == null){
      param = "";
    }
    this.plan1DementiaIndependence.setAttribute('value',param);
    return dementiaIndependenceName;
  }

  /**
   * ケアプラン期間の自動選択をする
   */
  async autoSelectCarePlanPeriod(){
    await this.requestCarePlanPeriodStartDate();
  }

  /**
   * 履歴レコードの選択状態をクリアする
   * @return {void}
  */
  clearRecordSelectionState(){
    if(this.selectedRecord){
      this.selectedRecord.classList.remove('sp1_select_record');
      this.selectedRecord = null;
    }
  }

  /**
   * ケアプラン期間の終了日の自動入力UIのクリックイベント
   * @param {Event}
   * @returns {void}
   */
  clickApiRadioBtn(event){
    // ケアプラン期間の開始日がない時は何もしない
    if(!this.elementCarePlanPeriodStart.value){
      return;
    }

    // 押下されたラジオボタンの値から有効終了日のスパン値を取得する
    let spanMonth = 0;
    let spanYear = 0;
    switch (event.target.value) {
      case 'one_month':
        spanMonth = 1;
        break;
      case 'three_month':
        spanMonth = 3;
        break;
      case 'half_year':
        spanMonth = 6;
        break;
      case 'one_year':
        spanYear = 1;
        break;
    }

    this.setEndDate(this.elementCarePlanPeriodStart, this.elementCarePlanPeriodEnd, spanYear, spanMonth);
  }

  /**
   * 介護計画書のケアプラン期間の開始日をリクエストするためのパラメーターを作成して返す
   * @returns {String}
   */
  createCarePlanPeriodStartDateParameter(){
    return '?facility_user_id=' + this.getFacilityUserId();
  }

  /**
   * サーバーに保存をリクエストするためのリクエストデータを作成して返す
   * @returns {Object}
   */
  createRegisterDataParameter(){
    let sumDivision = null;
    document.querySelectorAll(".plan_division").forEach((e) => {
      if(e.checked){
        sumDivision += Number(e.value);
      }
    });
    let postData = {
      facility_user_id: this.getFacilityUserId(),
      plan_start_period: this.planStartPeriod.value,
      plan_end_period: this.planEndPeriod.value,
      certification_status: this.certificationStatus.value,
      recognition_date: this.approvalFormRecognitionDate.value,
      care_period_start: this.carePeriodStart.value,
      care_period_end: this.carePeriodEnd.value,
      care_level_name: this.CareLevel.value,
      care_level_dispflg: document.getElementById('care_level_dispflg').checked ? 1 : 0,
      independence_level: this.plan1IndependenceLevel.getAttribute('value'),
      dementia_level: this.plan1DementiaIndependence.getAttribute('value'),
      plan_division: '',
      plan_division: sumDivision,
      title1: this.title1.value,
      content1: this.content1.value,
      title2: this.title2.value,
      content2: this.content2.value,
      title3: this.title3.value,
      content3: this.content3.value,
      // 算定理由表示時はliving_aloneからother_reasonのコメントアウトを解除する
      // living_alone: this.livingAlone.checked ? 1 : 0,
      // handicapped: this.handicapped.checked ? 1 : 0,
      // other: this.other.checked ? 1 : 0,
      // other_reason: this.otherReason.value,
      // 確定ポップアップ内 入力値
      fixed_date: this.fixedDate.value,
      // 交付済みポップアップ内 入力値
      delivery_date: this.deliveryDate.value,
      consent: this.deliveryDateConsent.value,
      place: this.deliveryDatePlace.value,
      remarks: this.deliveryDateRemarks.value,
      // ケアプラン期間
      start_date: this.elementCarePlanPeriodStart.value,
      end_date: this.elementCarePlanPeriodEnd.value,
      // 最新の交付済みプランのid
      latest_service_plan_id: this.latestServicePlanId,
      first_plan_start_period: this.elementFirstPlanStartPeriod.value
    };

    //選択している履歴のステータスが交付済みの場合
    //交付済み以外のボタンが押下されても履歴のステータスを交付済から変更させない
    if(this.status.value == 4){
      postData['status'] = this.status.value;
    }else{
      postData['status'] = document.getElementById(this.saveStatus).value;
    }

    if(this.servicePlanId.value != ''){
      postData['service_plan_id'] = this.servicePlanId.value;
    }
    if(this.firstServicePlanId.value != ''){
      postData['first_service_plan_id'] = this.firstServicePlanId.value;
    }

    postData['isReplicate'] = this.replicateFlg;

    return postData;
  }

  /**
   * 施設利用者情報を取得する
   * @return {Object} key: facilityUserID,userName
   */
  getFacilityUserData(){
    return this.facilityUserData;
  }

  /**
   * 施設利用者IDを取得する
   * @return {Number}
   */
  getFacilityUserId(){
    return this.getFacilityUserData().facilityUserID;
  }

  /**
   * 介護計画書のケアプラン期間の開始日を取得する
   * @return {Promise}
   */
  async requestCarePlanPeriodStartDate(){
    return await CustomAjax.send(
      'GET',
      this.REQUEST_CARE_PLAN_PERIOD_START_DATE + this.createCarePlanPeriodStartDateParameter(),
      {'X-CSRF-TOKEN':CSRF_TOKEN},
      [],
      "setCarePlanPeriod",
      this
    );
  }

  /**
   * 施設利用者情報をリクエストする
   * @return {Promise}
   */
  async requestFacilityUserData(){
    let date = this.returnToday()
    let data = await CustomAjax.post(
      this.getUserInformationUrl,
      {'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN},
      {facility_user_id: this.getFacilityUserId(), year:date.year, month:date.month}
    );
    return data;
  }

  /**
   * ケアプラン期間をセットする
   * @param {Object} data key: is_init_plan, start_date,
   */
  setCarePlanPeriod(data){
    // 初回作成時
    if(data.is_init_plan){
      this.elementCarePlanPeriodStart.value = data.start_date;
      // 終了日を入力する
      document.getElementById('sp1_api_one_month').click();
    // 2回目以降の作成時
    } else {
      let startDate = new Date(data.start_date);
      let y = startDate.getFullYear();
      let m = ('00' + (startDate.getMonth() + 1)).slice(-2);
      let d = ('00' + (startDate.getDate())).slice(-2);
      this.elementCarePlanPeriodStart.value = y + '-' + m + '-' + d;
      // 終了日を入力する
      document.getElementById('sp1_api_half_year').click();
    }
  }

  /**
   * ポップアップを表示する
   * @param {String} msg
   * @returns {void}
   */
  showPopup(msg){
    let popupParams, btnPrams;
    ({popupParams, btnPrams} = this.createPopupParam());
    this.common.showPopup(msg, popupParams, btnPrams)
  }

  /**
   * OK/キャンセルを選択するポップアップを表示する
   * @param {string} msg
   * @param {object} callBack
   */
  showSelectPopup(msg, callBack = null){
    let popupParams, btnPrams;
    ({popupParams, btnPrams} = this.createPopupParam());
    this.common.showSelectPopup(msg, popupParams, btnPrams, callBack)
  }

  /**
   * ポップアップの要素に設定するパラメータを作成して返す
   * @returns
   */
  createPopupParam(){
    let popupParams, btnPrams;
    popupParams = {
      id : 'overflow_service_plan1_create_notification',
      class : 'conf'
    }
    btnPrams = {
      id : 'notificationbtn_service_plan1_create',
      class : 'popup_cancel_service_plan1'
    }
    return {popupParams,btnPrams}
  }

  /**
   * 利用者一覧の取得関数を設定
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @returns {void}}
   */
  addFacilityUserTableSyncServer(callBack){
    this.facilityUserTableSyncServer = callBack;
  }

  /**
   * 認定情報有効終了日の自動入力UIのクリックイベント
   * @param {*} event
   * @returns
   */
  clickApprovalApiBtn(event){
    // 開始日がない時は何もしない
    if(!this.carePeriodStart.value){
      return;
    }

    let spanMonth = event.target.value == this.HALF_YEAR ? parseInt(event.target.value) : 0;
    let spanYear = event.target.value != this.HALF_YEAR ?  parseInt(event.target.value) : 0;
    this.setEndDate(this.carePeriodStart, this.carePeriodEnd, spanMonth, spanYear)
  }

  /**
   * 対象の終了日に値をセットする
   * @param {element} startDateElement
   * @param {element} endDateElement
   * @param {integer} spanMonth
   * @param {integer} spanYear
   */
  setEndDate(startDateElement, endDateElement, spanMonth, spanYear){
    // スパン値 + 有効開始日の値を有効終了日にセットする
    let endDate = new Date(startDateElement.value);
    endDate.setFullYear(endDate.getFullYear() + spanYear, endDate.getMonth() + spanMonth, (endDate.getDate() - 1));
    let y = endDate.getFullYear();
    let m = ('00' + (endDate.getMonth() + 1)).slice(-2);
    let d =  ('00' + (endDate.getDate())).slice(-2);
    endDateElement.value = y + '-' + m + '-' + d;
  }

  /**
   * 認定状況を申請中に変更した場合の処理
   */
  changeStatus(event){
    this.resetDisable();
    if (event.target.value == 1) {
      this.applyingForApproval()
    }
  }

  /**
   * 認定状況が申請中
   */
  applyingForApproval(){
    this.inactiveEndDateBtn();
    this.disableOfDate();
  }

  /**
   * 認定年月日・有効開始日・有効終了日の選択不可を解除
   */
  resetDisable(){
    this.activeEndDateBtn();
    this.disableCancelOfDate();
  }

  /**
   * 認定年月日・有効開始日・有効終了日を操作不可に
   */
  disableOfDate(){
    this.disabledTargetDate.forEach( e => {
      e.innerText = "";
      e.value = "";
      e.disabled = true;
    })
    this.controlCareLevelDispflg(true,false);
  }

  /**
   * 認定年月日・有効開始日・有効終了日を操作可に
   */
  disableCancelOfDate(){
    this.disabledTargetDate.forEach( e => {
      e.disabled = false;
    })
    this.controlCareLevelDispflg(true,true);
  }

  /**
   * 有効終了日選択ボタンを利用不可に
   */
  inactiveEndDateBtn(){
    this.endDateBtns.forEach(e => {
      e.disabled = true;
      e.checked = false;
    })
  }

  /**
   * 有効終了日選択ボタンを利用可に
   */
    activeEndDateBtn(){
      this.endDateBtns.forEach(e => {
        e.disabled = false;
      })
    }
}
