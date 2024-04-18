
import CustomAjax from '../../lib/custom_ajax.js';

/**
 * 特定入所者サービスコードフォームに責任を持つクラス。
 * サービスのテーブルと共通項も多いが考え方が異なるので切り離した。
 */
export default class ServiceCodeFormIncompetentResident{
  /**
   * コンストラクタ。
   * @param {Function} notifyRegistration 登録を通知する関数。
   */
  constructor(notifyRegistration = ()=>{}){
    this.element = document.getElementById('service_code_form_incompetent_resident');
    this.elementCover = document.getElementById('result_registration_cover');
    this.elementFacility = document.getElementById('scf_ir_facility');
    this.elementPayerLimt = document.getElementById('scf_ir_payer_limit');
    this.elementTbody = document.getElementById('scf_ir_tbody');
    this.facilityId = null;
    this.notifyRegistration = notifyRegistration;
    this.selectedTableRecord = null;
    this.year = null;
    this.month = null;

    this.elementPayerLimt.addEventListener('change', this.changePartLimit.bind(this));
    // サービスコードフォームのキャンセルボタンにクリックイベントを追加する。
    document.getElementById('scf_cancel_incompetent_resident').addEventListener('click', this.hide.bind(this));
    // サービスコードフォームの登録ボタンにクリックイベントを追加する。
    document.getElementById('scf_register_incompetent_resident').addEventListener('click', this.clickRegisterButton.bind(this));
  }

  /**
   * 特定入所者サービスコードテーブルに行を追加する。
   * @param {Array} codes 追加する行の元データ。
   * @return {void}
   */
  addServiceCodeTbody(codes){
    codes.forEach((code) => {
      let record = document.createElement('tr');
      record.setAttribute('data-scf-ir-service-synthetic-unit', code.service_synthetic_unit);

      let elementCode = this.createCell('59' + code.service_item_code, 'scf_ir_code', null);
      elementCode.setAttribute('data-scf-ir-service-item-code-id', code.service_item_code_id);

      let name = this.createCell(code.service_item_name, 'scf_ir_name', null);

      record.appendChild(elementCode);
      record.appendChild(name);
      this.elementTbody.appendChild(record);

      // レコードにイベントを付与する。
      record.addEventListener('click', this.clickTableRecord.bind(this, record));
    });
  }

  /**
   * 負担者限度額の入力欄のチェンジイベント。
   * @param {Event}
   * @return {void}
   */
  changePartLimit(event){
    let target = event.target;
    let targetValue = Number(target.value);

    // 入力値が最小を下回る場合。
    if(targetValue < target.min){
      // 最小値に丸める。
      targetValue = target.min;
    // 入力値が最大を超える場合。
    } else if(targetValue > target.max) {
      // 最大値に丸める。
      targetValue = target.max;
    }

    target.value = targetValue;
  }

  /**
   * 登録ボタンのクリックイベント。
   * @return {void}
   */
  clickRegisterButton(){
    // サービスコードが選択されていない場合は終了する。
    if(this.selectedTableRecord === null){
      return;
    }

    // 単位数を取得する。
    let unit = Number(this.selectedTableRecord.getAttribute('data-scf-ir-service-synthetic-unit'));

    // 登録を通知する。
    // TODO: 通知先は ResultRegistrationTableIncompetentResident の addRecord だが見えづらくなっているで整理する。
    this.notifyRegistration(
      Number(this.elementPayerLimt.value),
      '0000000000000000000000000000000',
      0,
      Number(this.selectedTableRecord.children[0].getAttribute('data-scf-ir-service-item-code-id')),
      this.selectedTableRecord.children[1].textContent,
      this.year + '-' + this.month + '-' + '1',
      unit
    );

    this.hide();

    // 状態をクリアする。
    this.elementPayerLimt.value = 0;

    // 変更フラグをセット
    // document.getElementById('changed_flg').value = true;
  }

  /**
   * テーブルのレコードのクリックイベント。
   * @param {Element} record
   * @return {void}
   */
  clickTableRecord(record){
    // 表示の切り替えをする。
    this.selectedTableRecord?.classList.remove('scf_ir_table_record_selected');
    this.selectedTableRecord = record;
    this.selectedTableRecord.classList.add('scf_ir_table_record_selected');
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
   * 特定入所者サービスのコードを取得して返す。
   * @param {Number} facilityId 事業所ID
   * @return {Promise}
   */
  async listIncompetentResidents(facilityId){
    // パラメーターを作成する
    let params = new URLSearchParams({facility_id: facilityId, year: this.year, month: this.month});
    let res = await CustomAjax.get('/group_home/service/service_code/incompetent_resident/list?' + params.toString());
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
   * @param {Number} facilityId
   * @param {String} facilityName
   * @return {void}
   */
  updateFacility(facilityId, facilityName){
    let facility = document.createElement('option');
    facility.value = facilityId;
    facility.textContent = facilityName;
    this.elementFacility.appendChild(facility);
    this.facilityId = facilityId;
  }

  /**
   * サービスコードテーブルを更新する。
   * @param {Array} excludedIds
   * @return {Promise}
   */
  async updateServiceCodeTable(excludedIds=[]){
    this.deleteServiceCodeTbody();

    // 特定入所者サービスコードを取得する。
    let incompetentResidentCodes = [];
    try {
      incompetentResidentCodes = await this.listIncompetentResidents(this.facilityId);
    } catch (error) {
      // TODO: エラー発生時に内容を表示するUIが存在しない。
    }

    // 除外指定の特別診療費コードを除外する。
    let filterCodes = incompetentResidentCodes.filter(code => !excludedIds.includes(code.service_item_code_id));

    // サービスコードテーブルに追加する。
    this.addServiceCodeTbody(filterCodes);
  }
}
