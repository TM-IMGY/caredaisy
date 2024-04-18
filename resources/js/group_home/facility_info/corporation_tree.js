
import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import ChangePopup from '../chnage_popup.js'

export default class CorporationTree{
  constructor(){
    this.bodyElement = document.getElementById('corporate_list');
    this.selectedRecord = null;
    this.notificationList = [];
    // 事業所関連情報全ての通知先
    this.notificationAddresses = [];

    let subTab = document.getElementById('tm_sub_tab');
    this.subTabList = Array.from(subTab.children);
    this.selectedSubTab = this.subTabList[0];
    let contentID = this.selectedSubTab.getAttribute('data-contents-id');
    let content = document.getElementById(contentID)
    this.selectedContents = content;
    this.oldSelectedSubTab = null;
    this.li = null;
    this.label = null;

    // レコードクリックイベントのキャッシュ
    // TODO: クリックイベントの融通が効かないのでキャッシュをとっているが整理する。
    this.corporationRecordClicks = [];
    this.facilityRecordClicks = [];
    this.institutionRecordClicks = [];
    this.serviceRecordClicks = [];

    this.tabManager = null;
  }

  /**
   * 法人・施設・事業所更新時のツリーリフレッシュ及び選択状態の復元
   */
  async refreshTree()
  {
    // リフレッシュ前に共通部の情報を削除
    this.corporationRecordClicks = [];
    this.facilityRecordClicks = [];
    this.institutionRecordClicks = [];
    this.serviceRecordClicks = [];

    let selectedLabelDataName = this.selectedRecord.attributes[1].name
    let selectedLabelId = this.selectedRecord.attributes[1].value
    await this.syncServer();
    let selectLabel = document.querySelector('[' + selectedLabelDataName + '=' + '"' + selectedLabelId + '"' + ']')
    selectLabel.classList.add('active_target');
  }

  /**
   * ツリーのレコードを表示する
   * @param {Object}
   * @returns {Promise}
   */
  async syncServer()
  {
    // レコードを削除
    this.bodyElement.textContent = null;

    // 法人情報・施設情報・事業所情報・サービス種別情報を取得する
    let corporationList = await this.corporationList();
    if (corporationList === null) {return;}


    // 法人名昇順に並び変える
    corporationList.sort((a, b) => {
      if(a.name !== b.name){
        return a.name.localeCompare(b.name,'ja');
      }
      return 0;
    });

    let ul = document.createElement('ul');
    ul.className = 'corporate';
    corporationList.forEach((data => {
      let corporationRecord = this.createCorporationBlock(data)
      ul.appendChild(corporationRecord);
      this.bodyElement.appendChild(ul);
    }));
  }

  createListItem(typeName,id, serviceTypeName)
  {
    this.li = document.createElement('li');
    this.label = document.createElement('label');
    this.label.className = 'corporate_item ' + typeName + '_label';
    this.label.setAttribute('data-' + typeName + '-id',id);
    if (typeName == 'service'){
      this.label.setAttribute('id', 'serviceInfoId' + id);
      // 自動テスト用にduskデータを追加する
      this.label.setAttribute('dusk', serviceTypeName);
    }
  }

  /**
   * 共通部のサービス表示部分を作成する
   * @param {object} facilityInfo
   * @param {object} facilityListItem
   */
  createServiceBlock(facilityInfo,facilityListItem)
  {
    let serviceUnorderedList = document.createElement('ul');
    serviceUnorderedList.className = 'survice';
    facilityInfo.service.forEach((serviceInfo) => {
      this.createListItem('service',serviceInfo.service_id,serviceInfo.service_type_name)
      let serviceListItem = this.li
      let serviceLabel = this.label
      this.label.textContent = serviceInfo.service_type_name;
      serviceListItem.appendChild(this.label);

      let informationToSend = {
        facilityId:serviceInfo.facility_id,
        serviceId:serviceInfo.service_id,
        serviceTypeCodeId:serviceInfo.service_type_code_id,
        serviceTypeName:serviceInfo.service_type_name
      }

      let addtionStatusOnly = {
        facilityInfo,
        serviceInfo
      }

      let clickRecordEvent = this.clickRecord.bind(this, serviceLabel, informationToSend, 'service', true, addtionStatusOnly);
      let clickRecordEventCache = this.clickRecord.bind(this, serviceLabel, informationToSend, 'service', false, addtionStatusOnly);
      serviceListItem.addEventListener('click', clickRecordEvent);
      this.serviceRecordClicks.push(clickRecordEventCache);
      serviceUnorderedList.appendChild(serviceListItem);
    })
    facilityListItem.appendChild(serviceUnorderedList);
  }

  /**
   * クリックイベントを設定する
   * @param {object} labelElement
   * @param {object} informationToSend
   * @param {string} layer
   * @param {boolean} flag 自動タブ遷移をするかのフラグ
   * @param {*} addtionStatusOnly
   */
  clickRecord(labelElement, informationToSend, layer, flag, addtionStatusOnly = null){
    event.stopPropagation();// イベントの伝播を止める、そうしないと4回呼ばれる場合がある。
    let changed_flg = document.getElementById("changed_flg").value;
    if (changed_flg == 'true'
        && informationToSend.serviceId
        && layer == 'service'
        && !ChangePopup.popup({id:'serviceInfoId' + informationToSend.serviceId})){
      return false
    }
    let oldSelectedRecord = this.selectEvent(labelElement);
    // 選択レコードに変更があれば通知する
    if(oldSelectedRecord !== this.selectedRecord){
      this.notificationList.forEach(callBack=>callBack(informationToSend));

      if (addtionStatusOnly != null) {
        // 加算状況向け
        this.notificationAddresses.forEach(callBack=>callBack(
          {},
          {},
          addtionStatusOnly.facilityInfo,
          addtionStatusOnly.serviceInfo
        ));
      }

      event.stopPropagation(); // イベントの伝播を止める
    }else if(oldSelectedRecord === this.selectedRecord){
      event.stopPropagation();
    }

    // 古いレイヤーを確保する。
    let oldLayer = this.selectedLayer;

    // レイヤーを変更し、かつこの事業所情報ツリーのレコードを選択した場合に自動のタブ遷移を行う。
    if(oldLayer !== layer && flag){
      this.autoTabTransition(layer);
    }

    // レイヤーをキャッシュする。
    this.selectedLayer = layer;
  }

  /**
   * 共通部の事業所表示部分を作成する
   * @param {object} facilitiesInfo
   * @param {object} institutionListItem
   */
  createFacilityBlock(facilitiesInfo,institutionListItem)
  {
    let facilityUnorderedList = document.createElement('ul');
    facilityUnorderedList.className = 'facility';

    facilitiesInfo.forEach((facilityInfo) => {
      this.createListItem('facility',facilityInfo.facility_id)
      let facilityListItem = this.li
      let facilityLabel = this.label
      this.abbreviationCheck(facilityInfo.facility_name_kanji,facilityInfo.facility_abbreviation);
      facilityListItem.appendChild(this.label)

      let informationToSend = {
        facilityId:facilityInfo.facility_id,
        facilityName:facilityInfo.facility_abbreviation,
        facility_name:facilityInfo.facility_name_kanji
      }

      let clickRecordEvent = this.clickRecord.bind(this, facilityLabel, informationToSend, 'facility', true);
      let clickRecordEventCache = this.clickRecord.bind(this, facilityLabel, informationToSend, 'facility', false);
      facilityListItem.addEventListener('click', clickRecordEvent);
      this.facilityRecordClicks.push(clickRecordEventCache);
      this.createServiceBlock(facilityInfo,facilityListItem);
      facilityUnorderedList.appendChild(facilityListItem);
    })

    institutionListItem.appendChild(facilityUnorderedList);
  }

  /**
   * 自動のタブ移動のユースケース。
   * @param {string} layer
   * @return {void}
   */
  autoTabTransition(layer)
  {
    this.tabManager.clickSubTabByLayer(layer, false);
  }

  /**
   * 共通部の施設表示部分を作成する
   * @param {object} institutionsInfo
   * @param {object} cell
   */
  createInstitutionBlock(institutionsInfo,cell)
  {
    let institutionUnorderedList = document.createElement('ul');
    institutionUnorderedList.className = 'institution';

    institutionsInfo.forEach((institutionInfo) => {
      this.createListItem('institution',institutionInfo.institution_id)
      let institutionListItem = this.li
      let institutionLabel = this.label
      this.abbreviationCheck(institutionInfo.institution_name,institutionInfo.institution_abbreviation);
      institutionListItem.appendChild(this.label);

      let informationToSend = {
        institutionId:institutionInfo.institution_id,
        InstitutionAbbreviation:institutionInfo.institution_abbreviation
      }

      let clickRecordEvent = this.clickRecord.bind(this, institutionLabel, informationToSend, 'institution', true);
      let clickRecordEventCache = this.clickRecord.bind(this, institutionLabel, informationToSend, 'institution', false);
      institutionListItem.addEventListener('click', clickRecordEvent);
      this.institutionRecordClicks.push(clickRecordEventCache);
      this.createFacilityBlock(institutionInfo.facility,institutionListItem)
      institutionUnorderedList.appendChild(institutionListItem);
    })
    cell.appendChild(institutionUnorderedList);
  }

  /**
   * 共通部の法人名表示部分を作成する
   * @param {object} data
   * @returns
   */
  createCorporationBlock(data)
  {
    let row = 1;
    this.createListItem('corporation',data.id)
    let cell = this.li
    let corporationLabel = this.label
    this.abbreviationCheck(data.name,data.abbreviation);
    cell.appendChild(this.label);

    let informationToSend = {
      corporationId:data.id,
      corporationAbbreviation:data.name
    }

    let clickRecordEvent = this.clickRecord.bind(this, corporationLabel, informationToSend, 'corporation', true);
    let clickRecordEventCache = this.clickRecord.bind(this, corporationLabel, informationToSend, 'corporation', false);
    cell.addEventListener('click', clickRecordEvent);
    this.corporationRecordClicks.push(clickRecordEventCache);
    this.createInstitutionBlock(data.institution,cell)
    row++;

    return cell;
  }

  /**
   * サーバーからユーザーが参照可能な情報を取得して返す
   * @returns {?Promise}
   */
  async corporationList(){
    return await CustomAjax.post('/group_home/service/corporation_tree',{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN});
  }

  /**
   * レコード選択イベントの通知先を設定
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @returns {element}
   */
  addNotification(callBack){
    this.notificationList.push(callBack);
  }

  /**
   * 事業所関連情報の通知先を設定する
   * @param {Function} callBack 通知先のコールバック関数
   * @returns {void}
   */
  addNotificationAddress(callBack){
    this.notificationAddresses.push(callBack);
  }

  selectEvent(label)
  {
    let oldSelectedRecord = document.querySelector('.active_target');
    oldSelectedRecord?oldSelectedRecord.classList.remove('active_target'):"";
    if(document.querySelector('.active_target')){
      document.querySelector('.active_target').classList.remove('active_target');
    }
    this.selectedRecord = label;
    this.selectedRecord.classList.add('active_target');

    return oldSelectedRecord;
  }

  /**
   * 略称がない場合は名称を表示
   * @param {string} name
   * @param {string} abbr
   */
  abbreviationCheck(name,abbr)
  {
    if (abbr == null) {
      this.label.textContent = name;
    } else {
      this.label.textContent = abbr;
    }
  }

  /**
   * レイヤー変更のユースケース。
   * @param {String} layer
   */
  changeLayer(layer){
    if(this.selectedLayer === layer){
      return;
    }

    // レイヤーが変わった場合に初期選択するレコードのイベントを取得する。
    let recordClick = null;
    switch (layer) {
      case 'corporation':
        // 基本的には最上位のレコードを取得する。(以下同様)
        // TODO: 複数施設対応時には既に選択していたレコードと親子関係にあるものを取得するように変更する。
        recordClick = this.corporationRecordClicks[0];
        break;
      case 'institution':
        recordClick = this.institutionRecordClicks[0];
        break;
      case 'facility':
        recordClick = this.facilityRecordClicks[0];
        break;
      case 'service':
        recordClick = this.serviceRecordClicks[0];
        break;
      default:
        break;
    }

    recordClick();
  }

  /**
   * タブマネージャーと連携する。
   * @param {TabManager} tabManager
   */
  setTabManager(tabManager){
    this.tabManager = tabManager;
  }
}
