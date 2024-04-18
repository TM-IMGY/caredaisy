
import CustomAjax from '../../lib/custom_ajax.js'

/**
 * 特別診療コードフォームに責任を持つクラス。
 * サービスのテーブルと共通項も多いが考え方が異なるので切り離した。
 * TODO: 設計半ばで作り始めたためServiceCodeという名称が多用されている。
 */
export default class ServiceCodeFormSpecial{
  /**
   * コンストラクタ。
   * @param {Function} notifyRegistration 登録を通知する関数。
   */
  constructor(notifyRegistration = ()=>{}){
    this.element = document.getElementById('service_code_form_special');
    this.elementCover = document.getElementById('result_registration_cover');
    this.elementFacility = document.getElementById('service_code_form_special_facility');
    this.elementTbody = document.getElementById('service_code_form_special_tbody');
    this.facilityId = null;
    this.notifyRegistration = notifyRegistration;
    this.selectedTableRecord = null;
    this.year = null;
    this.month = null;

    // サービスコードフォームのキャンセルボタンにクリックイベントを追加する。
    document.getElementById('service_code_form_cancel_special').addEventListener('click', this.hide.bind(this));
    // サービスコードフォームの登録ボタンにクリックイベントを追加する。
    document.getElementById('service_code_form_register_special').addEventListener('click', this.clickRegisterButton.bind(this));
  }

  /**
   * 特別診療費コードテーブルに行を追加する。
   * @param {Array} codes 追加する行の元データ。
   * @return {void}
   */
  addServiceCodeTbody(codes){
    codes.forEach((code) => {
      let record = document.createElement('tr');
      record.setAttribute('data-unit', code.unit);

      // 特別診療識別番号セルを作成する。
      let identificationNum = this.createCell(code.identification_num, 'service_code_form_special_name', null);
      identificationNum.setAttribute('data-scfp-id', code.id);

      // 特別診療費の内容セルを作成する。
      let contents = this.createCell(code.special_medical_name, 'service_code_form_special_contents', null);

      record.appendChild(identificationNum);
      record.appendChild(contents);
      this.elementTbody.appendChild(record);

      // レコードにイベントを付与する。
      record.addEventListener('click', this.clickTableRecord.bind(this, record));
    });
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
    let unit = Number(this.selectedTableRecord.getAttribute('data-unit'));

    // 登録を通知する。
    // TODO: 通知先は ResultRegistrationTableSpecial の addRecord だが見えづらくなっているで整理する。
    this.notifyRegistration(
      "0000000000000000000000000000000",
      0,
      Number(this.selectedTableRecord.children[0].getAttribute('data-scfp-id')),
      this.selectedTableRecord.children[1].textContent,
      this.year + '-' + this.month + '-' + '1',
      unit
    );

    this.hide();

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
    this.selectedTableRecord?.classList.remove('scfs_table_record_selected');
    this.selectedTableRecord = record;
    this.selectedTableRecord.classList.add('scfs_table_record_selected');
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
   * 特別診療費コードを取得して返す。
   * @param {Number} facilityId 事業所ID
   * @return {Promise}
   */
  async getSpecialMedicalCodes(facilityId){
    // パラメーターを作成する。
    // TODO: アプリケーションが種類80以外も対応する場合は改修する。
    let params = new URLSearchParams({facility_id: facilityId, service_type_code: '80', year: this.year, month: this.month});
    let res = await CustomAjax.get('/group_home/service/special_medical_code/get?' + params.toString());
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

    // 特別診療費コードを取得する。
    let specialMedicalCodes = [];
    try {
      specialMedicalCodes = await this.getSpecialMedicalCodes(this.facilityId);
    } catch (error) {
      // TODO: エラー発生時に内容を表示するUIが存在しない。
    }

    // 除外指定の特別診療費コードを除外する。
    let filterCodes = specialMedicalCodes.filter(code => !excludedIds.includes(code.id));

    // サービスコードテーブルに追加する。
    this.addServiceCodeTbody(filterCodes);
  }
}
