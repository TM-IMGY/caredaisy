import AdditionStatusTable from './addition_status_table.js';
import SpecialMedicalExpenses from './special_medical_expenses.js';
import JapaneseCalendar from '../../lib/japanese_calendar.js';

export default class AdditionStatus {
  constructor() {
    this.elementID = 'tm_contents_addition_status';
    this.element = document.getElementById(this.elementID);

    this.elementCrhTableRecordSelected = null;
    this.elementForm = document.getElementById('form_addition_status');
    this.elementFormStartMonth = document.getElementById('search_start_addition_status');
    this.elementFormJaStartMonth = document.getElementById('jaCalASStartMonth');
    this.elementFormEndMonth = document.getElementById('search_end_addition_status');
    this.elementFormJaEndMonth = document.getElementById('jaCalASEndMonth');
    this.elementInsertBtn = document.getElementById('js-new_addition_status');

    // 介護報酬履歴ID
    this.careRewardHistoryTableBody = document.getElementById('table_tbody_addition_status');
    this.elementSaveBtn = document.getElementById('js-updata-popup_addition_status');

    this.careRewardHistoryId = null;
    this.facilityId = null;
    this.facilityName = null;
    this.serviceId = null;
    this.serviceTypeCodeId = null;
    this.serviceTypeName = null;
    this.selectedRecordRow = null;
    // 元々の開始日と終了日
    this.startMonthOrigin = null;
    this.endMonthOrigin = null;

    this.special = new SpecialMedicalExpenses();
    this.table = new AdditionStatusTable();

    // 種類55関連
    this.changeViewBtn = document.getElementById('change_view_tab');
    this.addtionStatusViewBtn = document.getElementById('addtion_status_view');
    this.specialMedicalExpensesViewBtn = document.getElementById('special_medical_expenses_view');

    // 新規登録ボタンにイベントを紐づける(権限によりある場合とない場合がある)
    this.elementInsertBtn && this.elementInsertBtn.addEventListener('click', this.insertEvent.bind(this));
    // 保存ボタンにイベントを紐づける(権限によりある場合とない場合がある)
    this.elementSaveBtn && this.elementSaveBtn.addEventListener('click', this.saveEvent.bind(this));
    // ポップアップの「はい」にイベントを紐づける
    document.getElementById('updatabtn_addition_status').addEventListener('click', this.saveEvent.bind(this));
    // ポップアップの「いいえ」にイベントを紐づける
    document.getElementById('cancelbtn_addition_status').addEventListener('click', this.saveEvent.bind(this));
    // 開始日に変更イベントを紐づける(権限によりある場合とない場合がある)
    this.elementFormStartMonth &&
      this.elementFormStartMonth.addEventListener('change', this.changeFormStartMonth.bind(this));
    this.addtionStatusViewBtn.addEventListener('click', this.viewChange.bind(this));
    this.specialMedicalExpensesViewBtn.addEventListener('click', this.viewChange.bind(this));

    // 手入力時日付フォーマット＆和暦変換イベント
    this.elementFormStartMonth.addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));
    this.elementFormEndMonth.addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));
  }

  /**
   * 加算状況/特別診療費の画面切り替え
   * @param {string} styleOfAddition
   * @param {string} styleOfSpecial
   */
  changeDisplay(styleOfAddition, styleOfSpecial) {
    document.getElementById('addition_view').style.display = styleOfAddition;
    document.getElementById('special_medical_expenses').style.display = styleOfSpecial;
  }

  /**
   * 介護医療院 選択された「サービス形態」で表示する「施設区分」の表示を切り替える
   * @param {object} value サービス形態のvalue値
   * @param {boolean} click クリックイベントからかどうか
   */
  changeDisplayType55Section(value, click = true) {
    if (value == 3) {
      document.querySelectorAll('[data-contents-name="unit"]').forEach((e) => {
        e.style.display = 'none';
      });
      document.querySelectorAll('[data-contents-name="conventional"]').forEach((e) => {
        e.style.display = '';
      });
    } else {
      document.querySelectorAll('[data-contents-name="unit"]').forEach((e) => {
        e.style.display = '';
      });
      document.querySelectorAll('[data-contents-name="conventional"]').forEach((e) => {
        e.style.display = 'none';
      });
    }
    if (click) {
      document.querySelector('input[name="section"][class="default_check_radio"]').checked = true;
    }
  }

  /**
   * 開始日の変更イベント
   * @returns {void}
   */
  changeFormStartMonth(event) {
    this.elementFormEndMonth.min = event.target.value;
  }

  /**
   * 加算状況と特別診療費の活性化切り替え
   * @param {object} targetToActivate
   */
  changeTabBtnActivate(targetToActivate) {
    document.querySelector('.active').classList.add('inactive');
    document.querySelector('.active').classList.remove('active');
    targetToActivate.classList.remove('inactive');
    targetToActivate.classList.add('active');
  }

  /**
   * 介護報酬履歴テーブルの選択状態をクリアする
   * @returns {void}
   */
  clearCareRewardHistoryTableSelection() {
    if (this.elementCrhTableRecordSelected) {
      this.elementCrhTableRecordSelected.classList.remove('as_care_reward_history_table_record_selected');
    }
  }

  /**
   * フォームをクリアする
   * @returns {void}
   */
  cleareForm() {
    // デフォルトでチェックが入るようにクラスを設定したラジオボタンを全て取得する
    let radioBtns = document.getElementsByClassName('default_check_radio');
    radioBtns = Array.from(radioBtns);
    radioBtns.forEach((e) => {
      e.checked = true;
    });
    if (this.serviceTypeCodeId == 6) {
      this.changeDisplayType55Section(3, false);
    }
  }

  /**
   * フォームの開始日と終了日をクリアする
   * @returns {void}
   */
  clearFormDate() {
    this.elementFormStartMonth.value = null;
    this.elementFormJaStartMonth.innerText = null;
    this.elementFormEndMonth.value = null;
    this.elementFormEndMonth.min = null;
    this.elementFormJaEndMonth.innerText = null;
    this.startMonthOrigin = null;
    this.endMonthOrigin = null;
  }

  /**
   * 介護報酬履歴テーブルのレコードをクリックする
   * @param {Number} id 介護報酬履歴ID
   * @param {Element} record レコード
   * @throw
   * @returns {Promise}
   */
  async clickCareRewardHistoryTableRecord(id, record) {
    try {
      // 介護報酬履歴のデータを取得する
      let careRewardHistory = await this.table.getCareRewardHistory(this.facilityId, id, this.serviceTypeCodeId);

      // レコードの選択状態を変更する
      this.clearCareRewardHistoryTableSelection();

      record.classList.add('as_care_reward_history_table_record_selected');
      this.elementCrhTableRecordSelected = record;

      // 選択されている履歴のインデックスをキャッシュする
      this.selectedRecordRow = record.rowIndex - 1;

      // 介護報酬履歴データからフォームにデータをセットする
      this.setFormData(careRewardHistory);

      // 特別診療費側に加算状況履歴情報を送る
      if (this.serviceTypeCodeId == 6) {
        let checked = document.querySelectorAll('input[name="service_form"]:checked');
        checked.forEach((e) => {
          this.changeDisplayType55Section(e.value, false);
        });
        this.special.prepareSpecialMedicalExpenses(careRewardHistory, this.facilityId);
      }
    } catch (error) {
      this.showErrorPopup('介護報酬履歴データの取得に失敗しました。');
    }
  }

  /**
   * フォームのコンテンツ要素を作成して返す
   * @param {String} className
   * @param {Object} serviceCodes
   * @returns {Element}
   */
  createFormContentsElement(className, serviceCodes) {
    let elementForm = document.createElement('div');
    elementForm.classList.add(className);

    let elementFormTable = document.createElement('table');
    let elementFormTbody = document.createElement('tbody');

    elementForm.appendChild(elementFormTable);
    elementFormTable.appendChild(elementFormTbody);

    // フォームにサービスコード項目を追加する
    for (let i = 0, len = serviceCodes.length; i < len; i++) {
      let serviceCode = serviceCodes[i];

      // フォームのサービスコード項目を作成して取得する
      let serviceCodeItemEl = this.createFormServiceCodeItemElement(serviceCode);

      // フォームのサービスコード項目のフラグの入力ラジオボタンを作成する
      let radioBtnEl = this.createFormServiceCodeRadioBtnElement(serviceCode);

      serviceCodeItemEl.appendChild(radioBtnEl);
      elementFormTbody.appendChild(serviceCodeItemEl);
    }

    return elementForm;
  }

  /**
   * フォームのサービスコード項目の要素を作成して返す
   * @param {Object} serviceCode
   * @returns {Element}
   */
  createFormServiceCodeItemElement(serviceCode) {
    let tr = document.createElement('tr');
    tr.classList.add(serviceCode.tr_class_name);
    tr.style.display = serviceCode.display;

    let td = document.createElement('td');
    td.classList.add('radio_addition_status1');
    td.textContent = serviceCode.service_code_name;

    tr.appendChild(td);

    return tr;
  }

  /**
   * フォームのサービスコード項目のラジオボタンの要素を作成して返す
   * @param {Object} serviceCode
   * @returns {Element}
   */
  createFormServiceCodeRadioBtnElement(serviceCode) {
    let td = document.createElement('td');
    td.classList.add('radio_addition_status2');

    let values = serviceCode.values;
    for (let i = 0, len = values.length; i < len; i++) {
      let label = document.createElement('label');
      label.classList.add('radio_design_addition_status');
      label.setAttribute('data-contents-name', values[i].content_name);

      let input = document.createElement('input');
      input.name = values[i].name;
      input.type = 'radio';
      input.value = values[i].value;

      if (i === 0) {
        input.checked = true;
        input.classList.add('default_check_radio');
      }
      input.addEventListener('change', function () {
        document.getElementById('changed_flg').value = true;
      });
      let span = document.createElement('span');
      span.classList.add('radio_mark_addition_status');
      span.textContent = values[i].label;

      label.appendChild(input);
      label.appendChild(span);
      td.appendChild(label);

      // (リリース1.6暫定対応)
      if (values[i].invalid) {
        input.disabled = true;
        label.style.opacity = 0.2;
      }
    }

    return td;
  }

  /**
   * フォームを描画する
   * @returns {void}
   */
  drawForm() {
    // マスターテーブルを取得する
    let serviceCodeMasterTable = this.table.getMaster()[this.serviceTypeCodeId];
    let serviceCodeBasic = this.table.getMasterBasic()[this.serviceTypeCodeId];
    let serviceExclusiveChoice = this.table.getExclusiveChoice()[this.serviceTypeCodeId];
    let serviceCodeDiscount = this.table.getServiceCodeDiscount();

    // フォームを初期化する
    this.elementForm.textContent = null;

    // フォームの基本のサービスコード部分を作成して取得する
    let formContentsBasicEl = this.createFormContentsElement('form_left_addition_status1', serviceCodeBasic);

    // サービス種別36の場合はマスタのフォームは非表示にする
    if (this.serviceTypeCodeId == 4) {
      formContentsBasicEl.style.display = 'none';
    }

    // フォームを作成して取得する
    let formContentsEl = this.createFormContentsElement('form_left_addition_status2', serviceCodeMasterTable);
    // フォームの割引部分を作成して取得する
    let formContentsDiscountEl = this.createFormContentsElement('form_left_addition_status1', serviceCodeDiscount);
    // 2022年6月2日現在「割引」の機能は提供していないため非表示にする
    formContentsDiscountEl.style.display = 'none';

    // フォームを追加する
    this.elementForm.appendChild(formContentsBasicEl);

    // 種類55用のフォームを作成する
    // todo 同様の処理が必要な種類が増えたらif条件を「.include()」に変更等を検討する
    if (this.serviceTypeCodeId == 6) {
      let type55UseSection = this.createFormContentsElement('form_left_addition_status1', serviceExclusiveChoice);
      this.elementForm.appendChild(type55UseSection);
      this.orthopaedyAndAddChangeEventOfType55();
    }
    this.elementForm.appendChild(formContentsEl);
    this.elementForm.appendChild(formContentsDiscountEl);
  }

  /**
   * 加算状況画面で標準化された日付書式を返す。
   * @param {String} dateStr 日付
   * @returns {String}
   */
  getDateFormdefinedByRule(dateStr) {
    let date = new Date(dateStr);
    let year = date.getFullYear();
    let month = date.getMonth() + 1;
    return year + '/' + ('0' + month).slice(-2);
  }

  /**
   * フォームのデータを取得して返す
   * @returns {Object}
   */
  getFormData() {
    let data = {
      facility_id: this.facilityId,
      // 以前登録していた時の開始日と終了日
      start_date: this.startMonthOrigin,
      end_date: this.endMonthOrigin,
      service_id: this.serviceId,
      service_type_code_id: this.serviceTypeCodeId,
      care_reward_history: {
        start_month: this.getFormStartDateValue(),
        end_month: this.getFormEndDateValue(),
      },
    };

    // 介護報酬履歴のIDがあれば取得する
    if (this.careRewardHistoryId) {
      data.care_reward_history.care_reward_histories_id = this.careRewardHistoryId;
    }

    // フォームから介護報酬履歴のデータを作成する
    for (let i = 0, len = this.elementForm.length; i < len; i++) {
      let careRewardHistoryColumnName = this.elementForm[i].name;
      data.care_reward_history[careRewardHistoryColumnName] = this.elementForm[careRewardHistoryColumnName].value;
    }

    return data;
  }

  /**
   * フォームの終了日を取得して返す
   * @return {String}
   */
  getFormEndDateValue() {
    // 終了日がnullの場合は2024年3月として扱う
    let endMonth = this.elementFormEndMonth.value ? this.elementFormEndMonth.value : '2024-03-31';
    let date = new Date(endMonth);
    // 終了日は月末にして返す
    date.setFullYear(date.getFullYear(), date.getMonth() + 1, 0);
    let y = date.getFullYear();
    let m = ('00' + (date.getMonth() + 1)).slice(-2);
    let d = ('00' + date.getDate()).slice(-2);
    return y + '/' + m + '/' + d;
  }

  /**
   * フォームの開始日を取得して返す
   * @return {String}
   */
  getFormStartDateValue() {
    let date = new Date(this.elementFormStartMonth.value);
    // 開始日は月初にして返す
    date.setDate(1);
    let y = date.getFullYear();
    let m = ('00' + (date.getMonth() + 1)).slice(-2);
    let d = ('00' + date.getDate()).slice(-2);
    return y + '/' + m + '/' + d;
  }

  /**
   * 最新の履歴を取得して各種フォームにセットする
   * @returns
   */
  async getLatestCareRewardHistory() {
    let latestCareRewardHistory = await this.table.getLatestCareRewardHistory(this.facilityId, this.serviceTypeCodeId);
    if (latestCareRewardHistory.length == 0) {
      return;
    }
    // ラジオボタンを設定する
    this.setFormRadio(latestCareRewardHistory);

    // 開始月に最新履歴の翌月をセットする
    let date = new Date(latestCareRewardHistory.end_month);
    let baseDate = new Date(date.getFullYear(), date.getMonth()+1, '01');
    let year = baseDate.getFullYear();
    let month = ('00' + (baseDate.getMonth() + 1)).slice(-2);
    let day = '01';
    let startYm = year + '/' + month;
    this.elementFormStartMonth.value = startYm;
    let startYmd = startYm + '/' + day;
    this.elementFormJaStartMonth.innerText = JapaneseCalendar.toJacal(startYmd);
  }

  /**
   * 新規挿入イベント
   * @returns {void}
   */
  insertEvent() {
    // 介護報酬履歴のIDをクリアする
    this.careRewardHistoryId = null;

    // 選択している履歴のインデックスキャッシュをクリアする
    this.selectedRecordRow = null;

    // 介護報酬履歴テーブルの選択状態をクリアする
    this.clearCareRewardHistoryTableSelection();

    // フォームをクリアする
    this.cleareForm();

    // フォームの開始日と終了日をクリアする
    this.clearFormDate();

    // 最新履歴を取得してフォームの選択状態を更新する
    this.getLatestCareRewardHistory();

    // 特別診療費側も初期化する(種類55以外は関係なし)
    this.special.clickAdditionStatusInsertBtn();
  }

  /**
   * 種類55用のフォームを作成する
   */
  orthopaedyAndAddChangeEventOfType55() {
    // 行区切りのデザインが不格好なためスタイルを調整する
    document.querySelectorAll('.radio_design_addition_status').forEach((e) => {
      e.style.marginLeft = '2px';
    });

    let serviceForm = document.querySelectorAll('input[name="service_form"]');
    Array.prototype.slice.call(serviceForm).forEach((e) => {
      e.addEventListener('change', this.changeDisplayType55Section.bind(this, e.value));
    });
  }

  /**
   * 介護報酬履歴テーブルをリロードする
   * @returns {Promise}
   */
  async reloadCareRewardHistoryTable() {
    try {
      let careRewardHistories = await this.table.getCareRewardHistories(this.facilityId, this.serviceTypeCodeId);
      this.setCareRewardHistoryTableData(careRewardHistories);
    } catch (error) {
      this.showErrorPopup('介護報酬履歴データの取得に失敗しました。');
    }
  }

  /**
   * 保存イベント
   * @throws {error}
   * @returns {Promise}
   */
  async saveEvent(event) {
    //変更フラグをリセット
    document.getElementById('changed_flg').value = false;
    // フォームのデータを取得する
    let res;
    let data = this.getFormData();
    let targetBtn = event.target.id

    if (targetBtn == 'js-updata-popup_addition_status') {
      // フォームが介護報酬履歴IDをもつ場合(新規挿入)
      if (this.careRewardHistoryId) {
        $("#overflow_addition_status").show();
        return;
      } else {
        // 介護報酬履歴を新規挿入する
        res = await this.table.insert(data);

        // エラー時に返されたメッセージを表示
        if (res.errors) {
          // Object.valuesでerrors配下の値を直接取得
          let errorMessage = Object.values(res.errors);
          this.showErrorPopup(errorMessage[0][0]);
          return;
        }

        this.reloadCareRewardHistoryTable();
      }
    } else if (targetBtn == 'cancelbtn_addition_status') {
      $("#overflow_addition_status").hide();
      return;
    } else if (targetBtn == 'updatabtn_addition_status') {
      $("#overflow_addition_status").hide();

      // フォームが介護報酬履歴IDをもつ場合(更新)
      if (this.careRewardHistoryId) {
        res = await this.table.update(data);
        if (res.errors) {
          // Object.valuesでerrors配下の値を直接取得
          let errorMessage = Object.values(res.errors);
          this.showErrorPopup(errorMessage[0][0]);
          return;
        }
        this.reloadCareRewardHistoryTable();
      }
    }
  }

  /**
   * 介護報酬履歴テーブルにデータをセットする
   * @param {Object} data
   * @returns {void}
   */
  setCareRewardHistoryTableData(data) {
    // テーブルのレコードを全て削除する
    this.careRewardHistoryTableBody.textContent = null;

    // テーブルにレコードを追加する
    let record1 = null;
    for (let i = 0, len = data.length; i < len; i++) {
      let tr = document.createElement('tr');
      // 既存のコードに合わせたが本来IDは一意なので改修する
      tr.id = 'selectTdAdditionStatus';
      tr.addEventListener('click', this.clickCareRewardHistoryTableRecord.bind(this, data[i].id, tr));
      tr.classList.add('selectTableAdditionStatus');

      // 事業所名
      let tdFacilityName = document.createElement('td');
      tdFacilityName.classList.add('text_value_addition_status1');
      tdFacilityName.textContent = this.facilityName;

      // サービス種別
      let tdServiceType = document.createElement('td');
      tdServiceType.classList.add('text_value_addition_status2');
      tdServiceType.textContent = this.serviceTypeName;

      // 開始日と終了日
      let tdStartMonth = document.createElement('td');
      tdStartMonth.classList.add('text_value_addition_status3');
      tdStartMonth.textContent = this.getDateFormdefinedByRule(data[i].start_month);
      let tdEndMonth = document.createElement('td');
      tdEndMonth.classList.add('text_value_addition_status4');
      tdEndMonth.textContent = this.getDateFormdefinedByRule(data[i].end_month);

      tr.appendChild(tdFacilityName);
      tr.appendChild(tdServiceType);
      tr.appendChild(tdStartMonth);
      tr.appendChild(tdEndMonth);
      this.careRewardHistoryTableBody.appendChild(tr);

      // 1レコード目はキャッシュする
      if (i == 0) {
        record1 = tr;
      }
    }

    if (this.selectedRecordRow) {
      // レコードインデックスのキャッシュがある場合は対象インデックスの履歴を選択する。主に更新時に利用。
      this.careRewardHistoryTableBody.children[this.selectedRecordRow].click();
    } else if (record1) {
      // データをセットしたら1レコード目を選択状態にする
      record1.click()
    }

    // 履歴が1件もなかったら新規登録ボタンを押下したことにする
    if (data.length == 0 && this.elementInsertBtn) {
      this.elementInsertBtn.click();
    }
  }

  /**
   * 事業所関連の情報をセットする
   * @param {Object} corporation
   * @param {Object} institute
   * @param {Object} facility
   * @param {Object} service
   */
  setFacilityRelatedData(corporation, institute, facility, service) {
    if (facility) {
      // キー名が違うので対応する。要修正
      if (facility.facility_id) {
        this.facilityId = facility.facility_id;
      } else if (facility.facilityId) {
        this.facilityId = facility.facilityId;
      }

      // キー名が違うので対応する。要修正
      if (facility.facility_name_kanji) {
        this.facilityName = facility.facility_name_kanji;
      } else if (facility.facility_name) {
        this.facilityName = facility.facility_name;
      }
    }

    // サービス情報をセット
    if (service) {
      // キー名が違うので対応する。要修正
      if (service.serviceTypeName) {
        this.serviceTypeName = service.serviceTypeName;
      } else if (service.service_type_name) {
        this.serviceTypeName = service.service_type_name;
      }

      // キー名が違うので対応する。要修正
      if (service.serviceTypeCodeId) {
        this.serviceTypeCodeId = service.serviceTypeCodeId;
      } else if (service.service_type_code_id) {
        this.serviceTypeCodeId = service.service_type_code_id;
      }

      // キー名が違うので対応する。要修正
      if (service.serviceId) {
        this.serviceId = service.serviceId;
      } else if (service.service_id) {
        this.serviceId = service.service_id;
      }

      this.drawForm(service.serviceTypeCodeId);
    }

    // 種別55だった場合、切り替え用のタブを表示する
    if (this.serviceTypeCodeId == 6) {
      this.changeTabBtnActivate(this.addtionStatusViewBtn);
      this.changeViewBtn.style.display = 'flex';
    } else {
      this.changeViewBtn.style.display = 'none';
      this.changeDisplay('block', 'none');
    }

    if (this.facilityId && this.serviceTypeCodeId) {
      // レコードインデックスのキャッシュをクリアする
      this.selectedRecordRow = null;
      // 介護報酬履歴テーブルをサーバーと同期させる
      this.reloadCareRewardHistoryTable();
    }

    this.clearFormDate();
  }

  /**
   * フォームにデータをセットする
   * @param {Object} careRewardHistory
   * @returns {void}
   */
  setFormData(careRewardHistory) {
    this.setFormRadio(careRewardHistory);

    this.setFormDate(new Date(careRewardHistory.start_month), new Date(careRewardHistory.end_month));

    // 介護報酬履歴IDをキャッシュする
    this.careRewardHistoryId = careRewardHistory.id;
  }

  /**
   * ラジオボタンの設定をセットする
   * @param {object} careRewardHistory
   */
  setFormRadio(careRewardHistory) {
    // 介護報酬履歴データの一つ一つの値を参照していく
    for (let key in careRewardHistory) {
      // 介護報酬履歴データのキーをフォームが持たない場合はエスケープする
      if (!this.elementForm[key]) {
        continue;
      }

      // 介護報酬履歴データのキーをフォームが持つ場合は、ラジオボタンにチェックをつける
      for (let i = 0, len = this.elementForm[key].length; i < len; i++) {
        if (this.elementForm[key][i].value == careRewardHistory[key]) {
          this.elementForm[key][i].checked = true;
        }
      }
    }
  }

  /**
   * フォームの日付をセットする
   * @param {Date} startDate
   * @param {Date} endDate
   * @returns {void}
   */
  setFormDate(startDate, endDate) {
    let startY = startDate.getFullYear();
    let startM = ('00' + (startDate.getMonth() + 1)).slice(-2);
    let startD = '01';
    let startYm = startY + '/' + startM;
    let startYmd = startYm + '/' + startD;

    endDate.setFullYear(endDate.getFullYear(), endDate.getMonth() + 1, 0);
    let endY = endDate.getFullYear();
    let endM = ('00' + (endDate.getMonth() + 1)).slice(-2);
    let endD = ('00' + endDate.getDate()).slice(-2);

    let endYm = endY + '/' + endM;
    let endYmd = endYm + '/' + endD;

    // フォームの開始日と終了日をセットする
    this.elementFormStartMonth.value = startYm;
    this.elementFormEndMonth.value = endYm;
    this.elementFormEndMonth.min = startYm;
    // 和暦をセットする
    this.elementFormJaStartMonth.innerText = JapaneseCalendar.toJacal(startYmd);
    this.elementFormJaEndMonth.innerText = JapaneseCalendar.toJacal(endYmd);

    // 元々の開始日と終了日をキャッシュする
    this.startMonthOrigin = startYmd;
    this.endMonthOrigin = endYmd;
  }

  /**
   * @param {boolean} status 表示のブーリアン値
   */
  setActive(status) {
    document.getElementById('tabAdditionStatus').value = status;
  }

  /**
   * @param {Object} data タイミングによって引数の中身が異なるので非推奨
   */
  setFacilityData(data) {
    //
  }

  /**
   * ポップアップを表示する
   * @param {String} msg
   * @returns {void}
   */
  showErrorPopup(msg) {
    let elementPopup = document.createElement('div');
    elementPopup.id = 'overflow_addition_status3';

    let elementPopupContents = document.createElement('div');
    elementPopupContents.classList.add('conf');

    let elementPopupMessage = document.createElement('p');
    elementPopupMessage.insertAdjacentHTML('afterbegin', msg);

    let elementBtnFrame = document.createElement('div');
    elementBtnFrame.classList.add('popup_btn_frame');

    let elementBtn = document.createElement('button');
    elementBtn.classList.add('poppu_close_addition_status');
    elementBtn.id = 'errorbtn_addition_status';
    elementBtn.textContent = '閉じる';
    elementBtn.addEventListener('click', () => {
      elementPopup.parentNode.removeChild(elementPopup);
    });

    elementPopup.appendChild(elementPopupContents);
    elementPopupContents.appendChild(elementPopupMessage);
    elementPopupContents.appendChild(elementBtnFrame);
    elementBtnFrame.appendChild(elementBtn);

    document.body.appendChild(elementPopup);
  }

  /**
   * 加算状況と特別診療費の表示切替
   * @param {*} event
   */
  viewChange(event) {
    this.changeTabBtnActivate(event.target);

    if (event.target == this.specialMedicalExpensesViewBtn) {
      this.changeDisplay('none', 'block');
    } else {
      this.changeDisplay('block', 'none');
    }
  }
}
