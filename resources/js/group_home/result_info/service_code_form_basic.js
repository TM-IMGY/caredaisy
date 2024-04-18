
import CustomAjax from '../../lib/custom_ajax.js'

/**
 * サービスコードフォーム(サービス)に責任を持つクラス。
 * 特別診療のテーブルと共通項も多いが考え方が異なるので切り離した。
 * 名称自体はサービスだが名前被りが分かりづらかったのでBasicと役割から命名した。
 */
export default class ServiceCodeFormBasic{
  /**
   * コンストラクタ。
   * @param {Function} notifyRegistration 
   */
  constructor(notifyRegistration = ()=>{}){
    this.element = document.getElementById('service_code_form_basic');
    this.elementCover = document.getElementById('result_registration_cover');
    this.elementFacility = document.getElementById('service_code_form_basic_facility');
    this.elementServiceType = document.getElementById('service_code_form_basic_service_type');
    this.elementTbody = document.getElementById('service_code_form_basic_tbody');
    this.notifyRegistration = notifyRegistration;
    this.selectedTableRecord = null;
    this.year = null;
    this.month = null;

    // サービスコードフォームのキャンセルボタンにクリックイベントを追加する。
    document.getElementById('service_code_form_cancel_basic').addEventListener('click', this.hide.bind(this));
    // サービスコードフォームの登録ボタンにクリックイベントを追加する。
    document.getElementById('service_code_form_register_basic').addEventListener('click', this.clickRegisterButton.bind(this));
  }

  /**
   * サービスコードテーブルに追加する。
   * @param {Array} codes
   * @return {void}
   */
  addServiceCodeTbody(codes){
    codes.forEach((code) => {
      let record = document.createElement('tr');
      record.setAttribute('data-service-synthetic-unit', code.service_synthetic_unit);

      let elementCode = this.createCell(this.elementServiceType.value + code.service_item_code, 'service_code_form_basic_code', null);
      elementCode.setAttribute('service-item-code-id', code.service_item_code_id);
      
      let name = this.createCell(code.service_item_name, 'service_code_form_basic_name', null);

      record.appendChild(elementCode);
      record.appendChild(name);
      this.elementTbody.appendChild(record);

      // レコードにイベントを付与する。
      record.addEventListener('click', this.clickTableRecord.bind(this, record));
    });
  }

  /**
   * @returns {void}
   */
  clickRegisterButton(){
    if(!this.elementServiceType.value || this.selectedTableRecord === null){
      return;
    }

    let dateDailyRate = "0000000000000000000000000000000";
    let serviceItemCodeId = Number(this.selectedTableRecord.children[0].getAttribute('service-item-code-id'));
    let serviceCountDate = '0';
    let serviceItemName = this.selectedTableRecord.children[1].textContent;
    let targetDate = new Date(this.year, this.month - 1, 1);
    let unitNumber = Number(this.selectedTableRecord.getAttribute('data-service-synthetic-unit'));

    this.notifyRegistration(
      dateDailyRate,
      dateDailyRate,
      dateDailyRate,
      dateDailyRate,
      serviceCountDate,
      serviceItemCodeId,
      serviceItemName,
      targetDate,
      unitNumber
    );

    this.hide();
  }

  /**
   * テーブルのレコードのクリックイベント。
   * @param {Element} record
   * @return {void}
   */
  clickTableRecord(record){
    // 表示の切り替えをする。
    this.selectedTableRecord?.classList.remove('scfb_table_record_selected');
    this.selectedTableRecord = record;
    this.selectedTableRecord.classList.add('scfb_table_record_selected');
  }

  /**
   * セルを作成して返す。
   * @param {String} text
   * @param {Number} className
   * @param {Number} rowSpan
   * @returns {Element}
   */
  createCell(text, className = null, rowSpan = null){
    let cell = document.createElement('td');
    cell.classList.add('caredaisy_table_cell');
    cell.textContent = text;
    if(rowSpan){
      cell.rowSpan = rowSpan;
    }
    if(className){
      cell.classList.add(className);
    }
    return cell;
  }

  /**
   * サービスコードテーブルのレコードを削除する。
   * @return {void}
   */
  deleteServiceCodeTbody(){
    this.elementTbody.textContent = null;
  }

  /**
   * サービスコードを取得して返す。
   * @param {*} serviceTypeCode サービス種別コード。
   * @returns {Promise}
   */
  async getServiceCode(serviceTypeCode){
    // パラメーターを作成する
    let params = new URLSearchParams({service_type_code: serviceTypeCode, year: this.year, month: this.month});
    let res = await CustomAjax.get('/group_home/service/service_code/get?' + params.toString(),);
    return await res.json();
  }

  /**
   * 施設利用者が事業所から提供を受けているサービス種別について、対象月に利用中のものを全て取得する。
   * @param {*} facilityUserId
   * @return {Promise}
   */
  async getServiceTypes(facilityUserId){
    // パラメーターを作成する。
    let params = new URLSearchParams({facility_user_id: facilityUserId, month: this.month, year: this.year});
    let res = await CustomAjax.get('result_info/service_type?' + params.toString());
    return await res.json();
  }

  /**
   * 非表示にする。
   * @return {void}
   */
  hide(){
    this.element.classList.add('result_registration_hidden');
    this.elementCover.classList.add('result_registration_hidden');
    this.selectedTableRecord = null;
  }

  /**
   * 対象年月をセットする。
   * @param {Number} year
   * @param {Number} month
   * @return {void}
   */
  setYm(year, month){
    this.year = year;
    this.month = month;
  }

  /**
   * 表示する。
   * @return {void}
   */
  show(){
    // カバーを表示する。
    this.elementCover.classList.remove('result_registration_hidden');
    // フォームを表示する。
    this.element.classList.remove('result_registration_hidden');
    // フォームの位置を中心に調整する。
    let cRect = this.elementCover.getBoundingClientRect();
    let fRect = this.element.getBoundingClientRect();
    this.element.style.left = Math.floor((cRect.width-fRect.width) / 2) + 'px';
    this.element.style.top = Math.floor((cRect.height-fRect.height) / 2) + 'px';
  }

  /**
   * 事業所情報を更新する。
   * @param {*} facilityId
   * @param {*} facilityName
   */
  updateFacility(facilityId, facilityName){
    let facility = document.createElement('option');
    facility.value = facilityId;
    facility.textContent = facilityName;
    this.elementFacility.appendChild(facility);
  }

  /**
   * サービス種別を更新する。
   * @param {*} facilityUserId
   * @return {Promise}
   */
  async updateServiceType(facilityUserId){
    this.elementServiceType.textContent = null;

    // 施設利用者のサービス種別を取得する。
    let types = await this.getServiceTypes(facilityUserId);

    if(types.length > 0){
      types.forEach(type => {
        // 種別をプルダウンに追加する。
        let option = document.createElement('option');
        option.value = type.service_type_code;
        option.textContent = type.service_type_name;
        this.elementServiceType.appendChild(option);
      });
    }
  }

  /**
   * サービスコードテーブルを更新する。
   * @param {Array} serviceItemCodeIds 除外するサービス項目コードID
   * @return {Promise}
   */
  async updateServiceCodeTable(serviceItemCodeIds = []){
    this.deleteServiceCodeTbody();

    // サービス種別がない場合は終了する。
    if(!this.elementServiceType.value){
      return;
    }

    // サービスコードを取得する。
    let serviceCodes = [];
    try {
      serviceCodes = await this.getServiceCode(this.elementServiceType.value);
    } catch (error) {
      // (標準化未対応のため仮実装)エラー表示する箇所がないため握りつぶす
    }

    // 指定のサービスコードを除外する。
    let filterServiceCodes = serviceCodes.filter(code => !serviceItemCodeIds.includes(code.service_item_code_id));

    // サービスコードテーブルに追加する。
    this.addServiceCodeTbody(filterServiceCodes);
  }
}
