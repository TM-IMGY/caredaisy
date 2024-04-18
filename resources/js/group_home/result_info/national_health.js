import CSRF_TOKEN from '../../lib/csrf_token.js';
import CustomAjax from '../../lib/custom_ajax.js';

export default class NationalHealth{
  /**
   * コンストラクタ
   * @param {Number} year
   * @param {Number} month
   * @param {Number} facilityId
   */
  constructor(year, month, facilityId){
    // TODO: 利口なUIになっている。
    // 様式6の3を出力するサービス種類コードID。
    this.FORM_6_3_SERVICE_TYPE_CODE_IDS = [3, 4];
    // 様式6の4を出力するサービス種類コードID。
    this.FORM_6_4_SERVICE_TYPE_CODE_IDS = [5];
    // 介護医療院のサービス種類コードID。
    this.TYPE_55_SERVICE_TYPE_CODE_ID = 6;
    // 給付費明細欄の合計列の結合幅
    this.BILLING_DETAIL_TOTAL_ROWSPAN_VALUE = 4;
    // 特別診療費の合計列の結合幅
    this.BILLING_SPECIAL_MEDICAL_TOTAL_ROWSPAN_VALUE = 5;

    this.elementTotalTable = document.getElementById('nh_claim_for_benefits_tbody');
    this.elementLabelYear = document.getElementById('nh_label_year');
    this.elementLabelMonth = document.getElementById('nh_label_month');
    // 給付額請求テーブルの単位数合計ラベル
    this.elementTotalTableCreditsLabel = document.getElementById('nh_claim_for_benefits_table_total_credits_label');
    // 給付額請求テーブルの単位数単価ラベル
    this.elementUnitTableCreditsLabel = document.getElementById('nh_claim_for_benefits_table_unit_credits_label');

    this.billingDetailTBody = document.getElementById('national_health_tbody');
    this.billingServiceUnitAmount = document.getElementById('nh_billing_unit_amount');
    this.billingUnitPrice = document.getElementById('nh_billing_unit_price');
    this.billingBenefitRate = document.getElementById('nh_billing_benefit_rate');
    this.billingInsuranceBenefit = document.getElementById('nh_billing_insurance_benefit');
    this.billingPartPayment = document.getElementById('nh_billing_part_payment');
    this.billingPublicExpenditureUnit = document.getElementById('nh_billing_public_unit_amount');
    this.billingPublicBenefitRate = document.getElementById('nh_billing_public_benefit_rate');
    this.billingPublicSpendingAmount = document.getElementById('nh_billing_public_insurance_benefit');
    this.billingPublicPayment = document.getElementById('nh_billing_public_part_payment');

    this.billingSpecialMedicalTBody = document.getElementById('special_medical_tbody');
    this.billingSpServiceUnitAmount = document.getElementById('nh_billing_sp_service_unit_amount');
    this.billingSpServiceUnitNumber = document.getElementById('nh_billing_public_sp_spending_unit_number');
    this.billingSpBenefitRate = document.getElementById('nh_billing_sp_benefit_rate');
    this.billingSpPublicBenefitRate = document.getElementById('nh_billing_sp_public_benefit_rate');
    this.billingSpInsuranceBenefit = document.getElementById('nh_billing_sp_insurance_benefit');
    this.billingSpPublicSpendingAmount = document.getElementById('nh_billing_sp_public_spending_amount');
    this.billingSpPartPayment = document.getElementById('nh_billing_sp_part_payment');
    this.billingSpPublicPayment = document.getElementById('nh_billing_sp_public_payment');

    this.benefitBillingLabel = document.getElementById('nh_benefit_billing_lbl');
    this.billingIncompetentResidentTBody = document.getElementById('incompetent_resident_tbody');

    this.infoName = document.getElementById('nh_table_info_name');
    this.infoNameKana = document.getElementById('nh_table_info_name_kana');
    this.infoBirth = document.getElementById('nh_table_info_birthday');
    this.infoGender = document.getElementById('nh_table_info_gender');
    this.infoInsurerNo = document.getElementById('nh_table_insurer_no');
    this.infoInsuredNo = document.getElementById('nh_table_insured_no');
    this.infoCareLevel = document.getElementById('nh_table_care_level');
    this.infoCarePeriodStart = document.getElementById('nh_table_care_period_start');
    this.infoCarePeriodEnd = document.getElementById('nh_table_care_period_end');
    this.infoStartDate = document.getElementById('nh_table_start_date');
    this.infoActualdays = document.getElementById('nh_table_actual_days');
    this.infoStayout = document.getElementById('nh_table_stay_out');

    this.agreementOkBtn = document.getElementById('nh_agreement_ok_btn');
    this.agreementCancelBtn = document.getElementById('nh_agreement_cancel_btn');
    this.printFormParam = document.getElementById('nh_print_form_param');

    if (this.agreementOkBtn !== null){
      this.agreementOkBtn.addEventListener('click', this.eventAgreement.bind(this, 1));
    }
    if (this.agreementCancelBtn !== null){
      this.agreementCancelBtn.addEventListener('click', this.eventAgreement.bind(this, 0));
    }

    this.elementID = 'tm_contents_2';

    this.facilityId = facilityId;
    this.facilityUserId = null;
    this.isActive = false;
    this.notification = async () => {};
    this.year = year;
    this.month = month;
    // 情報が変わった場合の通知先全て。
    this.notifications = [];
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
   * 給付額請求テーブルのレコードを作成して返す。
   * @param {String} classification 区分
   * @param {String} insurance 保険分
   * @param {String} publicExpenditure 公費分
   * @return {Element}
   */
  createTotalTableRecord(classification, insurance, publicExpenditure){
    let record = document.createElement('tr');

    // 区分セル
    let classificationCell = document.createElement('td');
    classificationCell.classList.add('nh_billing_td', 'nh_table_cell');
    classificationCell.textContent = classification;

    // 保険分セル
    let insuranceCell = document.createElement('td');
    insuranceCell.classList.add('nh_billing_td', 'nh_table_cell', 'number_cell');
    insuranceCell.colSpan = 2;
    insuranceCell.textContent = insurance;

    // 公費分セル
    let publicExpenditureCell = document.createElement('td');
    publicExpenditureCell.classList.add('nh_billing_td', 'nh_table_cell', 'number_cell');
    publicExpenditureCell.colSpan = 2;
    publicExpenditureCell.textContent = publicExpenditure;

    record.appendChild(classificationCell);
    record.appendChild(insuranceCell);
    record.appendChild(publicExpenditureCell);

    return record;
  }

  /**
   * 給付額請求テーブルにデータをセットする。
   * @param {Object} data 給付額請求データ
   * @return {void}
   */
  reloadTotalTable(data){
    this.billingBenefitRate.textContent = Number(data.benefit_rate);
    this.billingInsuranceBenefit.textContent = Number(data.insurance_benefit).toLocaleString();
    this.billingPartPayment.textContent = Number(data.part_payment).toLocaleString();
    this.billingPublicBenefitRate.textContent = Number(data.public_benefit_rate);
    this.billingPublicExpenditureUnit.textContent = Number(data.public_expenditure_unit).toLocaleString();
    this.billingPublicPayment.textContent = Number(data.public_payment).toLocaleString();
    this.billingPublicSpendingAmount.textContent = Number(data.public_spending_amount).toLocaleString();
    this.billingServiceUnitAmount.textContent = Number(data.service_unit_amount).toLocaleString();
    this.billingUnitPrice.textContent = Number(data.unit_price) / 100;
  }

  /**
   * 承認イベント
   * @param {Number} flag 承認フラグ(0か1)。
   * @param {Event} イベント
   * @return {Promise}
   */
  async eventAgreement(flag, event){
    // 活性化していない場合、またはパラメーターを持たない場合は何もしない。
    if(!(this.hasAllParam() && this.isActive)){
      event.preventDefault();
      return;
    }
    await this.requestAgreement(flag);
  }

  /**
   * 種類55用の要素の表示・非表示を切り替える
   * @param {int} serviceTypeCodeId
   */
  changeTheDisplayOfElementsOfType55(serviceTypeCodeId){
    if (serviceTypeCodeId == this.TYPE_55_SERVICE_TYPE_CODE_ID) {
      this.visibleElementsOfType55();
    } else {
      // 基本的に種類55は他の種類と共存しないが、念のため他種類の場合は非表示にする処理を入れておく
      this.hideElementsOfType55();
    }
  }

  /**
   * 種類55用の要素を表示する
   */
  visibleElementsOfType55(){
    // 「給付額請求欄」を「請求額集計欄」に変更する
    this.benefitBillingLabel.textContent = '請求額集計欄';
    document.querySelectorAll('.special_medical_table_element_hidden').forEach(e => {e.classList.add('special_medical_table_element_visible')})
    document.querySelectorAll('.special_medical_table_element_hidden').forEach(e => {e.classList.remove('special_medical_table_element_hidden')})
    document.querySelectorAll('.special_medical_element_hidden').forEach(e => {e.classList.add('special_medical_element_visible')})
    document.querySelectorAll('.special_medical_element_hidden').forEach(e => {e.classList.remove('special_medical_element_hidden')})
  }

  /**
   * 種類55用の要素を非表示にする
   */
  hideElementsOfType55(){
    document.querySelectorAll('.special_medical_table_element_visible').forEach(e => {e.classList.add('special_medical_table_element_hidden')})
    document.querySelectorAll('.special_medical_table_element_visible').forEach(e => {e.classList.remove('special_medical_table_element_visible')})
    document.querySelectorAll('.special_medical_element_visible').forEach(e => {e.classList.add('special_medical_element_hidden')})
    document.querySelectorAll('.special_medical_element_visible').forEach(e => {e.classList.remove('special_medical_element_visible')})
  }

  /**
   * リロードする。
   * @return {Promise}
   */
  async reload(){
    // 活性化していない場合でも年月表示を更新する。
    this.elementLabelYear.textContent = this.year;
    this.elementLabelMonth.textContent = this.month;

    this.clearDetailTable();
    this.clearTotalTable();
    this.clearInformationTable();

    this.clearSpecialMedicalTable();
    this.clearType55TotalTable();
    this.clearIncompetentResidentTable();

    // 活性化していない場合、またはパラメーターを持たない場合は何もしない。
    if(!(this.isActive && this.hasAllParam())){
      return;
    }

    // 様式データをリクエストする。
    let form = null;
    try {
      form = await this.requestForm();
    } catch (error) {
      // TODO: 標準化で対応する。
      throw error;
    }

    this.changeTheDisplayOfElementsOfType55(form.service_type_code_id)
    this.reloadForm(form.service_type_code_id);

    // 様式に明細情報がある場合。
    if (form.details.length) {
      this.addServiceRecords(form.details)
      this.createBillingTotalRow(form.total, this.BILLING_DETAIL_TOTAL_ROWSPAN_VALUE, this.billingDetailTBody);
    }

    // 特別診療費
    if (form.special_medicals.length) {
      this.addSpecialMedicalTableRecords(form.special_medicals);
      this.createBillingTotalRow(form.total_special_medical, this.BILLING_SPECIAL_MEDICAL_TOTAL_ROWSPAN_VALUE, this.billingSpecialMedicalTBody);
    }

    // 給付額請求テーブルをリロードする。
    if(form.total !== null){
      this.reloadTotalTable(form.total);
    }
    // 給付額請求テーブルの種類55用箇所をリロードする
    if (form.total_special_medical !== null) {
      this.reloadType55TotalTable(form.total_special_medical)
    }

    // 特定入所者介護サービス費
    if (form.incompetent_residents.length) {
      this.addIncompetentResidentTableRecords(form.incompetent_residents)
    }

    // 特定入所者介護サービス費 合計以下
    if (form.total_incompetent_resident !== null) {
      this.addIncompetentResidentTableTotalRecord(form.total_incompetent_resident)
    }

    await this.syncInfoTable();
  }

  /**
   * テーブルのセルを作成する。
   * @param {string} data セルの値
   * @param {Array} classNames
   * @param {Array} attribute
   * @return {Element}
   */
  createCell(data, classNames = [], attribute = []){
    let cell = document.createElement('td');
    cell.innerText = data;
    cell.classList.add('caredaisy_table_cell');
    classNames.forEach(className => cell.classList.add(className));
    attribute.forEach(object => {cell.setAttribute(object.name, object.value)})
    return cell;
  }

  /**
   * 介護給付費明細欄に表示する合計列を作成する
   * @param {object} params
   */
  createBillingTotalRow(params, rowspanValue, tbody){
    let record = document.createElement('tr');
    // 斜線用の要素を作成する
    let diagonal = this.createCell('');
    let hr = document.createElement('hr');
    hr.classList.add('diagonal_line');
    diagonal.appendChild(hr)
    // サービス単位合計
    let serviceUnitAmountTotal = Number(params.service_unit_amount).toLocaleString();
    // 公費対象単位合計
    let publicExpenditureAmountTotal = params.public_expenditure_unit === null ? null : Number(params.public_expenditure_unit).toLocaleString();
    record.appendChild(this.createCell('合計',['total'], [{name:'colspan',value:rowspanValue}]));
    record.appendChild(this.createCell(serviceUnitAmountTotal, ['service_unit_amount_total', 'number_cell']));
    record.appendChild(diagonal);
    record.appendChild(this.createCell(publicExpenditureAmountTotal, ['public_expenditure_amount_total', 'number_cell']));
    tbody.appendChild(record);
  }

  /**
   * 給付費明細テーブルにレコードを追加する。
   * @param {array} details
   */
  addServiceRecords(details){
    details.forEach((detail) => {
      let record = document.createElement('tr');

      // 単位数、サービス単位数、公費対象単位はカンマ区切りに変換する。
      let unitNumber = Number(detail.unit_number).toLocaleString();
      let serviceUnitAmount = Number(detail.service_unit_amount).toLocaleString();
      let publicExpenditureAmount = detail.public_expenditure_unit === null ? null : Number(detail.public_expenditure_unit).toLocaleString();

      record.appendChild(this.createCell(detail.service_item_name,['service_item_name']));
      record.appendChild(this.createCell(detail.service_code,['service_code']));
      record.appendChild(this.createCell(unitNumber,['unit_number','number_cell']));
      record.appendChild(this.createCell(detail.service_count_date,['service_count_date','number_cell']));
      record.appendChild(this.createCell(serviceUnitAmount,['service_unit_amount','number_cell']));
      record.appendChild(this.createCell(detail.public_spending_count,['public_expenditure_cnt','number_cell']));
      record.appendChild(this.createCell(publicExpenditureAmount,['public_expenditure_amount','number_cell']));

      this.billingDetailTBody.appendChild(record);
    });
  }

  /**
   * 特別診療費テーブルにレコードを追加する
   * @param {array} specialMedicals
   */
  addSpecialMedicalTableRecords(specialMedicals){
    specialMedicals.forEach((specialMedical) => {
      let record = document.createElement('tr');

      // 単位数、サービス単位数、公費分回数等、公費対象単位はカンマ区切りに変換する。
      let unitNumber = Number(specialMedical.unit_number).toLocaleString();
      let serviceUnitAmount = Number(specialMedical.service_unit_amount).toLocaleString();
      let publicSpendingAmount = specialMedical.public_spending_count === null ? null : Number(specialMedical.public_spending_count).toLocaleString();
      let publicExpenditureAmount = specialMedical.public_expenditure_unit === null ? null : Number(specialMedical.public_expenditure_unit).toLocaleString();

      if (!this.cacheOfDetailId || this.cacheOfDetailId !== specialMedical.detail_id) {
        let topRecord = this.createCell(specialMedical.name,['sp_injury_and_illness_name']);
        // 傷病名のセルをキャッシュする
        this.cacheOfTopRecord = topRecord
        // detail_idのカウント数をリセットする
        this.detailIdCnt = 1;
        record.appendChild(topRecord);
      } else {
        this.detailIdCnt++
        // detail_idがキャッシュと同一ならrowspanを増やす
        this.cacheOfTopRecord.setAttribute('rowspan',this.detailIdCnt);
      }
      record.appendChild(this.createCell(specialMedical.identification_num,['sp_identification_num','number_cell']));
      record.appendChild(this.createCell(specialMedical.special_medical_name,['sp_special_medical_name']));
      record.appendChild(this.createCell(unitNumber,['sp_unit_number','number_cell']));
      record.appendChild(this.createCell(specialMedical.service_count_date,['sp_number_of_days','number_cell']));
      record.appendChild(this.createCell(serviceUnitAmount,['sp_service_unit_amount','number_cell']));
      record.appendChild(this.createCell(publicSpendingAmount,['sp_public_expenditure_cnt','number_cell']));
      record.appendChild(this.createCell(publicExpenditureAmount,['sp_public_expenditure_amount','number_cell']));

      this.billingSpecialMedicalTBody.appendChild(record);

      this.cacheOfDetailId = specialMedical.detail_id;
    });
  }

  /**
   * 特定入所者介護サービス費にレコードを追加する
   * @param {array} incompetentResidents
   */
  addIncompetentResidentTableRecords(incompetentResidents){
    incompetentResidents.forEach((incompetentResident) => {
      let record = document.createElement('tr');

      // 費用単価、負担限度額、費用額、保険分、公費分、利用者負担額はカンマ区切りに変換する。
      let unitNumber = Number(incompetentResident.unit_number).toLocaleString();
      let burdenLimit = Number(incompetentResident.burden_limit).toLocaleString();
      let totalCost = Number(incompetentResident.total_cost).toLocaleString();
      let insuranceBenefit = Number(incompetentResident.insurance_benefit).toLocaleString();
      let publicSpendingAmount = Number(incompetentResident.public_spending_amount).toLocaleString();
      let partPayment = Number(incompetentResident.part_payment).toLocaleString();

      record.appendChild(this.createCell(incompetentResident.service_item_name, ['srs_service_item_name']));
      record.appendChild(this.createCell(incompetentResident.service_code, ['srs_service_item_code']));
      record.appendChild(this.createCell(unitNumber, ['srs_unit_number','number_cell']));
      record.appendChild(this.createCell(burdenLimit, ['srs_burden_limit','number_cell']));
      record.appendChild(this.createCell(incompetentResident.service_count_date, ['srs_service_count_date','number_cell']));
      record.appendChild(this.createCell(totalCost, ['srs_total_cost','number_cell']));
      record.appendChild(this.createCell(insuranceBenefit, ['srs_insurance_benefit','number_cell']));
      record.appendChild(this.createCell(incompetentResident.public_spending_count, ['srs_public_spending_count','number_cell']));
      record.appendChild(this.createCell(publicSpendingAmount, ['srs_public_spending_amount','number_cell']));
      record.appendChild(this.createCell(partPayment, ['srs_part_payment','number_cell']));

      this.billingIncompetentResidentTBody.appendChild(record)
    });
  }

  /**
   * 特定入所者介護サービス費の合計レコードを追加する
   * @param {array} totalIncompetentResident
   */
  addIncompetentResidentTableTotalRecord(totalIncompetentResident){
    // TODO: var ではなく let にする。
    var record = document.createElement('tr');
    let partPayment = Number(totalIncompetentResident.part_payment).toLocaleString();
    let publicSpendingAmount = Number(totalIncompetentResident.public_spending_amount).toLocaleString();
    let totalCost = Number(totalIncompetentResident.total_cost).toLocaleString();

    record.appendChild(this.createCell('合計', ['total'], [{name:'colspan',value:5}]));
    record.appendChild(this.createCell(totalCost, ['srs_total_cost','number_cell']));
    record.appendChild(this.createCell(null, ['invalid_cell'], [{name:'colspan',value:2}]));
    record.appendChild(this.createCell(publicSpendingAmount, ['srs_total_public_spending_amount','number_cell']));
    record.appendChild(this.createCell(partPayment, ['srs_total_part_payment','number_cell']));

    this.billingIncompetentResidentTBody.appendChild(record);

    var record = document.createElement('tr');
    let insuranceBenefit = Number(totalIncompetentResident.insurance_benefit).toLocaleString();

    record.appendChild(this.createCell(null,['invalid_cell'],[{name:'colspan',value:5},{name:'rowspan',value:2}]));
    record.appendChild(this.createCell('保険分\n請求額(円)',[], [{name:'rowspan',value:2}]));
    record.appendChild(this.createCell(insuranceBenefit,['number_cell'],[{name:'rowspan',value:2}]));
    record.appendChild(this.createCell('公費分\n請求額',[], [{name:'rowspan',value:2}]));
    record.appendChild(this.createCell(publicSpendingAmount,['number_cell'],[{name:'rowspan',value:2}]));
    record.appendChild(this.createCell('公費分\n本人負担月額'));
    this.billingIncompetentResidentTBody.appendChild(record);

    var record = document.createElement('tr');
    let publicPayment = Number(totalIncompetentResident.public_payment).toLocaleString();
    record.appendChild(this.createCell(publicPayment,['number_cell']));
    this.billingIncompetentResidentTBody.appendChild(record);
  }

  /**
   * テーブルのセルを作成する。
   * @param {string} data セルの値
   * @param {Array} classNames
   * @param {Array} attribute
   * @return {Element}
   */
  createCell(data, classNames = [], attribute = []){
    let cell = document.createElement('td');
    cell.innerText = data;
    cell.classList.add('caredaisy_table_cell');
    classNames.forEach(className => cell.classList.add(className));
    attribute.forEach(object => {cell.setAttribute(object.name, object.value)})
    return cell;
  }

  /**
   * 給付額請求テーブルの種類55用箇所にデータをセットする。
   * @param {Object} data 給付額請求データ
   * @return {void}
   */
  reloadType55TotalTable(data){
    this.billingSpServiceUnitAmount.textContent = Number(data.service_unit_amount).toLocaleString();
    this.billingSpServiceUnitNumber.textContent = Number(data.public_spending_unit_number).toLocaleString();
    this.billingSpBenefitRate.textContent = Number(data.benefit_rate);
    this.billingSpPublicBenefitRate.textContent = Number(data.public_benefit_rate);
    this.billingSpInsuranceBenefit.textContent = Number(data.insurance_benefit).toLocaleString();
    this.billingSpPublicSpendingAmount.textContent = Number(data.public_spending_amount).toLocaleString();
    this.billingSpPartPayment.textContent = Number(data.part_payment).toLocaleString();
    this.billingSpPublicPayment.textContent = Number(data.public_payment).toLocaleString();
  }

  /**
   * 給付額請求テーブルの種類55用箇所のデータをクリアする。
   * @return {void}
   */
  clearType55TotalTable(){
    this.billingSpServiceUnitAmount.textContent = null;
    this.billingSpServiceUnitNumber.textContent = null;
    this.billingSpBenefitRate.textContent = null;
    this.billingSpPublicBenefitRate.textContent = null;
    this.billingSpInsuranceBenefit.textContent = null;
    this.billingSpPublicSpendingAmount.textContent = null;
    this.billingSpPartPayment.textContent = null;
    this.billingSpPublicPayment.textContent = null;
  }

  /**
   * 様式をリロードする。
   * @param serviceTypeCodeId サービス種類コードID
   * @return {void}
   */
  reloadForm(serviceTypeCodeId){
    // 様式六の三または六の四の場合。
    if(this.FORM_6_3_SERVICE_TYPE_CODE_IDS.includes(serviceTypeCodeId) || this.FORM_6_4_SERVICE_TYPE_CODE_IDS.includes(serviceTypeCodeId)){
      // 必要な追加差分レコードの区分を定義する。
      let classifications = ['①外部利用型給付上限単位数', '②外部利用型上限管理対象単位数', '③外部利用型外給付単位数'];
      for (let i = 0, len = classifications.length; i < len; i++) {
        // TODO: 保険分、公費分を全てブランクとして出力する暫定対応のまま放置されている。
        let record = this.createTotalTableRecord(classifications[i], '', '');
        record.classList.add('nh_claim_for_benefits_form_additional_diff');

        // 公費を無効にする。
        record.children[2].classList.add('invalid_cell');

        this.elementTotalTable.prepend(record);
      }

      // 単位数合計は給付単位数になる。
      this.elementTotalTableCreditsLabel.textContent = '④給付単位数';
    }
    // 介護医療院の場合
    if (this.TYPE_55_SERVICE_TYPE_CODE_ID == serviceTypeCodeId) {
      // 単位数合計は点数・給付単位数になる。
      this.elementTotalTableCreditsLabel.textContent = '①点数・給付単位数';
      // 単位数単価は点数・単位数単価
      this.elementUnitTableCreditsLabel.textContent = '②点数・単位数単価';
    }

    // 給付額請求テーブルのレコードのインデックス番号を振る。
    this.indexBilling();
  }

  /**
   * 給付額請求テーブルのレコードのインデックスを振る。
   * @return {void}
   */
  indexBilling(){
    let records  = Array.from(this.elementTotalTable.children);
    let indexLabels = ['①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨'];
    for (let i = 0, len = records.length; i < len; i++) {
      let cell = records[i].children[0];
      let indexLabel = indexLabels[i];
      cell.textContent = indexLabel + cell.textContent.substring(1);
    }
  }

  /**
   * ルールで決められた日付の書式を返す。
   * @param {String} dateStr
   * @return {String}
   */
  getRuleBasedDateFormat(dateStr){
    let date = new Date(dateStr);
    // yyyy/mm/dd
    return date.toLocaleDateString();
  }

  /**
   * パラメーターを全て持つか。
   * TODO: hasAllParamでは結局何のパラメーターがあるのか分からない。
   * @return {Boolean}
   */
  hasAllParam(){
    return ![this.facilityUserId, this.year, this.month].includes(null);
  }

  /**
   * 国保連請求の様式データをリクエストする。
   * @return {Promise}
   */
  async requestForm(){
    let params = new URLSearchParams({
      facility_id: this.facilityId,
      facility_user_id : this.facilityUserId,
      year : this.year,
      month : this.month
    });
    let res = await CustomAjax.get('/group_home/service/service_result/form/get?' + params.toString());
    let json = await res.json();
    return json;
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
   * 施設利用者情報テーブルにデータをセットする。
   * @param {Object} response
   * @return {void}
   */
  setInfoTable(response){
    let careInfo = response.care_info;

    this.infoBirth.textContent = this.getRuleBasedDateFormat(response.birthday);
    this.infoGender.textContent = response.gender == 1 ? '男性' : '女性';
    this.infoInsurerNo.textContent = response.insurer_no;
    this.infoInsuredNo.textContent = response.insured_no;
    this.infoName.textContent = response.last_name + response.first_name;
    this.infoNameKana.textContent = '(' + response.last_name_kana + response.first_name_kana + ')';

    this.infoStartDate.textContent = response.start_date ? this.getRuleBasedDateFormat(response.start_date) + '～' : null;

    if(careInfo){
      this.infoCareLevel.textContent = careInfo.care_level.care_level_name;
      this.infoCarePeriodEnd.textContent = this.getRuleBasedDateFormat(careInfo.care_period_end);
      this.infoCarePeriodStart.textContent = this.getRuleBasedDateFormat(careInfo.care_period_start) + '～';
    }

    this.infoStayout.textContent = response.stay_out + '泊';
    this.infoActualdays.textContent = response.actualDays + '日';
  }

  /**
   * 施設利用者をセットする。
   * @param {Object} params key: careLevelName, facilityUserID, userName
   * @return {Promise}
   */
  async setFacilityUser(params){
    this.facilityUserId = 'facilityUserID' in params ? params.facilityUserID : this.facilityUserId;
    await this.reload();
  }

  /**
   * 対象年月をセットする。
   * @param {Object} params key: year, month
   * @return {Promise}
   */
  async setYm(params){
    this.year = 'year' in params ? params.year : this.year;
    this.month = 'month' in params ? params.month : this.month;
    await this.reload();
  }

  /**
   * 施設利用者情報テーブルのデータをサーバーと同期させる。
   * @return {Promise}
   */
  async syncInfoTable(){
    let parameter = 'facility_user_id=' + this.facilityUserId + '&year=' + this.year  + '&month=' + this.month;
    await CustomAjax.send(
      'GET',
      '/group_home/service/facility_user/header/get?' + parameter,
      {},
      [],
      "setInfoTable",
      this
    );
  }

  /**
   * 給付費明細テーブルの値をクリアする。
   * @return {void}
   */
  clearDetailTable(){
    this.billingDetailTBody.textContent = null;
  }

  /**
   * 給付額請求テーブルのデータをクリアする。
   * @return {void}
   */
  clearTotalTable(){
    this.billingBenefitRate.textContent = null;
    this.billingPartPayment.textContent = null;
    this.billingInsuranceBenefit.textContent = null;
    this.billingServiceUnitAmount.textContent = null;
    this.billingUnitPrice.textContent = null;
    this.billingPublicExpenditureUnit.textContent = null;
    this.billingPublicBenefitRate.textContent = null;
    this.billingPublicSpendingAmount.textContent = null;
    this.billingPublicPayment.textContent = null;

    let records  = Array.from(this.elementTotalTable.children);
    for (let i = 0, len = records.length; i < len; i++) {
      records[i].classList.contains('nh_claim_for_benefits_form_additional_diff') && records[i].parentNode.removeChild(records[i]);
    }

    // 単位数合計と単位数単価の表記を元に戻す
    this.elementTotalTableCreditsLabel.textContent = '④単位数合計';
    this.elementUnitTableCreditsLabel.textContent = '②単位数単価';
    //ラベルを「給付額請求欄」に戻す
    this.benefitBillingLabel.textContent = '給付額請求欄';

    this.indexBilling();
  }

  /**
   * 施設利用者情報テーブルのデータをクリアする。
   * @returns {void}
   */
  clearInformationTable(){
    this.infoBirth.textContent = null;
    this.infoCareLevel.textContent = null;
    this.infoCarePeriodEnd.textContent = null;
    this.infoCarePeriodStart.textContent = null;
    this.infoGender.textContent = null;
    this.infoInsurerNo.textContent = null;
    this.infoInsuredNo.textContent = null;
    this.infoName.textContent = null;
    this.infoNameKana.textContent = null;
    this.infoStartDate.textContent = null;
    this.infoActualdays.textContent = null;
    this.infoStayout.textContent = null;
  }

  /**
   * 特定入所者介護サービス費を初期化する
   */
  clearIncompetentResidentTable(){
    this.billingIncompetentResidentTBody.textContent = null;
  }

  /**
   * 特別診療費を初期化する
   */
  clearSpecialMedicalTable(){
    this.cacheOfDetailId = null;
    this.billingSpecialMedicalTBody.textContent = null;
  }

  /**
   * 承認をリクエストする。
   * @param {Number} flag 承認フラグ(0か1)
   * @return {Promise}
   */
  async requestAgreement(flag) {
    try {
      await CustomAjax.post(
        'service/national_health_billing/agreement/update',
        {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
        {facility_user_id:this.facilityUserId, year:this.year, month:this.month, flag:flag}
      );
      for (let i = 0, len = this.notifications.length; i < len; i++) {
        await this.notifications[i]();
      }
    } catch (error) {
      // TODO: 例外を表示するUIの設計をする。
    }
  }

  /**
   * バリデーション時エラー表示
   * @return {void}
   */
  validateDisplay(errorBody){
    // errorBody = JSON.parse(errorBody);
  }
}
