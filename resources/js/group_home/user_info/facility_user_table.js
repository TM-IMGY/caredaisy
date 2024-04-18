import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import facilityUserInfoHeader from '../../lib/facility_user_info_header.js'
import ChangePopup from '../chnage_popup.js'

const ONE_MONTH_MIN = 0;
const ONE_MONTH_MAX = 30;
const TWO_MONTH_MIN = 31;
const TWO_MONTH_MAX = 60;
const THREE_MONTH_MIN = 61;
const THREE_MONTH_MAX = 90;

export default class FacilityUserTable{
  constructor(){
    this.elementBody = document.getElementById('user_info_fu_tbody');
    this.facilityID = null;
    this.year = null;
    this.month = null;
    this.selectedRecord = null;
    this.notificationList = [];
    this.facilityUserInfoHeader = new facilityUserInfoHeader();
    this.diff = null;
  }

  /**
   * テーブルのレコードを更新する
   * @param {Object} param key: facilityID,year,month
   * @returns {void}
   */
  async syncServer(param){
    if(param.facilityID){this.facilityID = param.facilityID;}
    if(param.year){this.year = param.year;}
    if(param.month){this.month = param.month;}

    // レコードを削除
    this.elementBody.textContent = null;

    let fUserList = await this.getFacilityUserList();
    if(fUserList===null){return;}
    if(fUserList.length == 0){
      sessionStorage.clear();
    }else{
      if(sessionStorage.getItem('selectedUserId')){
        if(!fUserList.map(item => item["facility_user_id"]).includes(parseInt(sessionStorage.getItem('selectedUserId'),10))){
          sessionStorage.clear();
        }
      }
    }

    // 取得した利用者情報を姓名でソート
    fUserList.sort((a, b) => {
      if(a.last_name_kana !== b.last_name_kana){
        return a.last_name_kana.localeCompare(b.last_name_kana,'ja');
      }
      if(a.first_name_kana !== b.first_name_kana){
        return a.first_name_kana.localeCompare(b.first_name_kana,'ja');
      }
      return 0;
    });

    // 取得した利用者情報からレコードを生成して追加
    fUserList.forEach((user)=>{
      let userCareInfo = user.care_info;
      let record = this.createRecord({
        facilityUserID: user.facility_user_id,
        name: user.last_name + user.first_name,
        careLevel: userCareInfo ? userCareInfo.care_level.care_level : null,
        carePeriodEnd : userCareInfo ? userCareInfo.care_period_end : null,
        certificationStatus : userCareInfo ? userCareInfo.certification_status : null,
        deathDate : user.death_date ? user.death_date : null,
        endDate : user.end_date ? user.end_date : null,
      });
      this.elementBody.appendChild(record);
    });
    if (document.getElementById("getFacilityIdApproval") &&
        document.getElementById("table_facility_user_id" + document.getElementById("getFacilityIdApproval").value)){
          document.getElementById("table_facility_user_id" + document.getElementById("getFacilityIdApproval").value).click();
    }
    // 遷移元の画面で利用者を選択していた場合利用者を引き継ぎ
    let selectedUserId = sessionStorage.getItem('selectedUserId');
    if (selectedUserId) {
      // 新規追加の場合は追加された利用者を選択
      if (sessionStorage.isNewTenants == "true") {
        sessionStorage.isNewTenants = false;

        // 新規追加された最新レコードからIDを取得
        let facilityUserIdList = fUserList.map(function(list) {
          return list.facility_user_id;
        });
        let latestFacilityUserId = Math.max(...facilityUserIdList);

        document.getElementById("table_facility_user_id" + latestFacilityUserId).click();
        document.getElementById("table_facility_user_id" + latestFacilityUserId).scrollIntoView();
      } else {
        document.getElementById("table_facility_user_id" + selectedUserId).click();
        document.getElementById("table_facility_user_id" + selectedUserId).scrollIntoView();
      }
    } else {
      if(document.getElementById("user_info_fu_tbody").childElementCount != 0) {
        document.getElementById("user_info_fu_tbody").firstElementChild.click();
       } else {
         //標準化後、CSSクラス指定に切り替える可能性アリ
         let subTab = Array.from(document.getElementById('tm_sub_tab').children);
         subTab.forEach(function(tab,index){
           if(index != 0) {
             tab.style.color = '#A8A8A8';
             tab.style.pointerEvents = 'none';
           } else {
             tab.style.color = '#fafafa';
             tab.style.pointerEvents = '';
           }
         });
       }
    }
  }

  /**
   * サーバーから利用者情報を取得して返す
   * @returns {Promise}
   */
  async getFacilityUserList(){
    let date = new Date();
    return await CustomAjax.post('/group_home/service/facility_user/get_data',
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      {
      clm:['facility_user_id','last_name_kana','first_name_kana','last_name','first_name','end_date','death_date'],
      care_info:{
        clm_list: ['care_level_id','care_period_end','care_period_start','facility_user_id','certification_status'],
        month: null,
        year: null,
        with:{
          care_level:['care_level_id','care_level']
        }
      }
    });
  }

  clearSelect(){
    let oldSelectedRecord = this.selectedRecord;

    if(this.selectedRecord){
      this.selectedRecord.classList.remove('fu_table_selected_record');
      this.selectedRecord.children[0].classList.remove('fu_table_selected_cell');
    }

    this.selectedRecord = null;

    // 選択レコードに変更があれば通知する
    if(oldSelectedRecord !== this.selectedRecord){
      this.notificationList.forEach(callBack=>callBack({facilityUserID:null,userName:null}));
    }
  }

  /**
   * レコードを作成する
   * @param {Object} data key: facilityUserID,name,careLevel,carePeriodEnd
   * @returns {element}
   */
  createRecord(data){
    let {carePeriodEnd,facilityUserID,name,certificationStatus,deathDate,endDate} = data;

    let record = document.createElement('tr');
    let cell = document.createElement('td');
    record.classList.add('facility_user_tr');
    record.setAttribute('data-facility-user-id',facilityUserID);
    record.setAttribute('id','table_facility_user_id' + facilityUserID);
    record.setAttribute('dusk', 'tr_facility_user_id' + facilityUserID);
    record.appendChild(this.createCell(name, cell));

    // 退居日と看取り日に値が存在する
    if (deathDate !== null || endDate !== null) {
    }
    // 申請中フラグ
    else if (certificationStatus == 1) {
      this.setAlert('shinsei',cell);
    }
    // 認定情報入力済で退居日と看取り日が空
    else if (carePeriodEnd !== null && (deathDate === null && endDate === null)) {
      let endDay = new Date(carePeriodEnd);
      let today = new Date();
      let timeDiff = endDay.getTime() - today.getTime();
      // 日付の差分を出す
      this.diff = Math.round(timeDiff / (24 * 60 * 60 * 1000))
      let isEnd = this.diff < 0;

      // 終了フラグ
      if (isEnd) {
        this.setAlert('alert_ninteigire',cell);
      }
      // 期限日当日フラグ
      else if (this.diff == -0) {
        this.setAlert('alert_today',cell);
      }
      // 1ヶ月以内終了フラグ
      else if (this.diff >= ONE_MONTH_MIN && this.diff <= ONE_MONTH_MAX) {
        this.setAlert('alert_one_month',cell);
      }
      // 2ヶ月以内終了フラグ
      else if (this.diff >= TWO_MONTH_MIN && this.diff <= TWO_MONTH_MAX) {
        this.setAlert('alert_two_months',cell);
      }
      // 3月以内終了フラグ
      else if (this.diff >= THREE_MONTH_MIN && this.diff <= THREE_MONTH_MAX) {
        this.setAlert('alert_three_months',cell);
      }
    } else {
      // 未登録フラグ
      this.setAlert('alert_ninteigire',cell);
    }

    record.addEventListener('click',() => {
      let oldSelectedRecord = this.selectedRecord;
      let changed_flg = document.getElementById("changed_flg").value;
      if (changed_flg == 'true' && !ChangePopup.popup({id:'table_facility_user_id' + facilityUserID})){
        return false
      }
      if(this.selectedRecord){
        this.selectedRecord.classList.remove('fu_table_selected_record');
        this.selectedRecord.children[0].classList.remove('fu_table_selected_cell');
      }

      this.selectedRecord = record;

      this.selectedRecord.classList.add('fu_table_selected_record');
      this.selectedRecord.children[0].classList.add('fu_table_selected_cell');

      if(oldSelectedRecord !== this.selectedRecord){
        this.notificationList.forEach(callBack=>callBack({facilityUserID:facilityUserID,userName:name}));
        this.facilityUserInfoHeader.setFacilityUser({facilityUserID:facilityUserID,userName:name});
      }

      // 選択された利用者IDを保持
      sessionStorage.selectedUserId = facilityUserID;
      // 新規追加フラグ初期化
      sessionStorage.isNewTenants = false;
    });

    return record;
  }

  /**
   * アラートの作成
   * @param {string} className
   * @returns
   */
  setAlert(className, cell)
  {
    let alertMessageWrap = document.createElement('div');
    alertMessageWrap.classList.add('alert_message_wrap');

    let alertMessageIcon  = document.createElement('span');
    alertMessageIcon.classList.add(className + '_pop_icon','pop_icon');

    let alertMessage  = document.createElement('div');
    alertMessage.classList.add('alert_message');
    alertMessage.innerHTML = this.createAlertMessage(className);

    alertMessageWrap.appendChild(alertMessageIcon);
    alertMessageWrap.appendChild(alertMessage);

    let alertIcon = document.createElement('span');
    alertIcon.classList.add(className,'alert_icon');

    let first = cell.firstChild;

    cell.insertBefore(alertIcon, first);
    alertIcon.appendChild(alertMessageWrap)
  }

  /**
   * アラートで表示する内容
   * @param {string} className
   * @returns
   */
  createAlertMessage(className)
  {
    let message;
    switch (className) {
      case 'shinsei':
        message = '介護認定を申請中です。'
        break;

      case 'alert_ninteigire':
        message = '有効な認定情報がありません。<br>' + '<span class="alert_ninteigire_color">'+'新しい認定情報を登録' + "</span>" + 'してください。'
        break;

      case 'alert_today':
        message = '介護認定期限日<br>当日です。'
        break;

      case 'alert_one_month':
        message = '介護認定期限切れまで残り' + this.diff + '日です。'
        break;

      case 'alert_two_months':
        message = '介護認定期限切れまで残り2ヶ月です。'
        break;

      case 'alert_three_months':
        message = '介護認定期限切れまで残り3ヶ月です。'
        break;

      default:
        break;
    }
    return message;
  }

  /**
   * レコード選択イベントの通知先を設定
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @returns {element}
   */
  addNotification(callBack){
    this.notificationList.push(callBack);
  }

  createCell(data, cell, classList=[]){
    cell.textContent = data;
    cell.classList.add('facility_user_td');

    classList.forEach(c=>cell.classList.add(c));
    return cell;
  }
}
