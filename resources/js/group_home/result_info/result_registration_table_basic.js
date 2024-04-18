/**
 * 実績登録テーブル(サービス)に責任を持つクラス。
 * 特別診療のテーブルと共通項も多いが考え方が異なるので切り離した。
 */
export default class ResultRegistrationTableBasic{
  constructor(){
    this.elementTbody = document.getElementById('result_registration_tbody');
    this.elementDateCell = document.getElementById('result_registration_table_date');
    this.elementDowCell = document.getElementById('result_registration_table_dow');
    this.serviceTypeCode = null;
  }

  /**
   * レコードを追加する。
   * @param {String} dateDailyRate 
   * @param {String} dateDailyRateOneMonthAgo
   * @param {String} dateDailyRateTwoMonthAgo
   * @param {String} dateDailyRateSchedule 
   * @param {Number} serviceCountDate 
   * @param {Number} serviceItemCodeId 
   * @param {String} serviceItemName 
   * @param {String} targetDate 
   * @param {Number} unitNumber 
   * @return {void}
   */
  addRecord(
    dateDailyRate,
    dateDailyRateOneMonthAgo,
    dateDailyRateTwoMonthAgo,
    dateDailyRateSchedule,
    serviceCountDate,
    serviceItemCodeId,
    serviceItemName,
    targetDate,
    unitNumber
  ) {
    this.elementTbody.appendChild(this.createPlanRow(dateDailyRateSchedule, serviceItemName, targetDate, unitNumber));

    let resultRow = this.createResultRow(
      dateDailyRate,
      dateDailyRateOneMonthAgo,
      dateDailyRateTwoMonthAgo,
      serviceCountDate,
      serviceItemCodeId,
      targetDate
    );
    this.elementTbody.appendChild(resultRow);
  }

  /**
   * 実績行のフラグセルの入力欄のチェンジイベント。
   * @param {Element} resultRow 対象の実績行
   * @return {void}
   */
  calcDateDailyRateSum(resultRow){
    // フラグセルを全て取得する。
    let dateDailyRatecells = resultRow.children;

    // フラグセルの合計を計算する。
    let sum = 0;
    let sumCellIndex = Number(dateDailyRatecells.length)-1;
    // 1は実績ラベルのセルを飛ばすため。
    for (let i = 1; i < sumCellIndex; i++) {
      let cellInputArea = this.getResultFlgCellInputArea(dateDailyRatecells[i])
      sum += Number(cellInputArea.value);
    }
    dateDailyRatecells[sumCellIndex].textContent = sum;
  }

  /**
   * 実績行のフラグセルの入力欄のチェンジイベント。
   * @param {Element} cell 対象のフラグセル
   * @return {void}
   */
  changeDateDailyRateCell(cell){
    let cellInputArea = this.getResultFlgCellInputArea(cell);
    let cellInputAreaValue = cellInputArea.value;

    // 入力値が0以下の場合。
    if(cellInputAreaValue <= 0){
      // ブランクとして扱う。
      cellInputAreaValue = null;
    // 介護医療院以外で入力値が1を超える場合。
    } else if(!this.isHospital() && cellInputAreaValue > 1) {
      cellInputAreaValue = 1;
    // 介護医療院で入力値が9を超える場合。
    } else if(this.isHospital() && cellInputAreaValue > 9) {
      cellInputAreaValue = 9;
    }

    cellInputArea.value = cellInputAreaValue;

    // 合計を再計算する
    this.calcDateDailyRateSum(cell.parentNode);
    // // 変更フラグをセット
    // document.getElementById('changed_flg').value = true;
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
   * @param {String} dateDailyRate
   * @returns {Element}
   */
  createCellDateDailyRate(dateDailyRate){
    // セルを作成する。
    let cell = document.createElement('td');
    cell.classList.add('caredaisy_table_cell', 'rrt_date_daily_rate');

    // セル入力欄を作成する。
    let cellInputArea = document.createElement('input');
    cellInputArea.addEventListener('change', this.changeDateDailyRateCell.bind(this, cell));
    cellInputArea.classList.add('result_registration_table_ddr_input');
    cellInputArea.type = 'number';
    cellInputArea.min = '0';
    cellInputArea.max = this.isHospital() ? '9' : '1';
    cellInputArea.value = Number(dateDailyRate) >= 1 ? dateDailyRate : null;

    cell.appendChild(cellInputArea);

    return cell;
  }

  /**
   * 予定行を作成する。
   * @param {String} dateDailyRateSchedule 
   * @param {String} serviceItemName 
   * @param {String} targetDate 
   * @param {Number} unitNumber 
   * @return {Element}
   */
  createPlanRow(dateDailyRateSchedule, serviceItemName, targetDate, unitNumber){
    let plans = dateDailyRateSchedule;
    let date = new Date(targetDate);

    // 予定のサービス回数を算出する。
    let serviceCount = 0;
    serviceCount = plans.split('').reduce(
      (previousValue, currentValue) => Number(previousValue) + Number(currentValue),
      serviceCount
    );

    // 対象年月の日数を取得する。
    let targetMonthDateCnt = (new Date(date.getFullYear(), date.getMonth() + 1, 0)).getDate();

    // 予定行を作成する。
    let planRow = document.createElement('tr');

    // ゴミ箱セルを作成する。
    let trash = this.createCell('', 'result_registration_table_trash', 2);
    let trashIcon = document.createElement('img');
    trashIcon.src = '/sozai/delete_icon.png';
    trashIcon.addEventListener('click', this.hideRecord.bind(this, planRow));
    trash.appendChild(trashIcon);

    // サービス内容セルを作成する。
    let serviceItemNameCell = this.createCell(serviceItemName, 'result_registration_table_service', 2);

    // 単位数セルを作成する。
    let unitNumberCell = this.createCell(unitNumber, 'result_registration_table_unit', 2);

    // 予定ラベルセルを作成する。
    let planLbl = this.createCell('予定', 'result_registration_table_plan');

    // フラグセルを作成する。
    let dateDailyRateCells = [];
    for (let i = 0; i < targetMonthDateCnt; i++) {
      let cellValue = plans[i] == '1' ? plans[i] : null;
      dateDailyRateCells.push(this.createCell(cellValue, 'rrt_date_daily_rate'));
    }

    // フラグ合計セルを作成する。
    let serviceCountCell = this.createCell(serviceCount, 'result_registration_table_sum');

    // 作成したセルを予定行に追加する。
    let planRowCells = [trash, serviceItemNameCell, unitNumberCell, planLbl];
    planRowCells.forEach(el => planRow.appendChild(el));
    dateDailyRateCells.forEach(el => planRow.appendChild(el));
    planRow.appendChild(serviceCountCell);

    return planRow;
  }

  /**
   * @param {String} dateDailyRate
   * @param {String} dateDailyRateOneMonthAgo
   * @param {String} dateDailyRateTwoMonthAgo
   * @param {Number} serviceCountDate
   * @param {Number} serviceItemCodeId
   * @param {String} targetDate
   * @returns {Element}
   */
  createResultRow(
    dateDailyRate,
    dateDailyRateOneMonthAgo,
    dateDailyRateTwoMonthAgo,
    serviceCountDate,
    serviceItemCodeId,
    targetDate
  ) {
    let date = new Date(targetDate);

    // 実績行を作成する。
    let resultRow = document.createElement('tr');

    // 実績ラベルを作成する。
    let resultLbl = this.createCell('実績', 'result_registration_table_result');
    resultLbl.setAttribute('data-service-code-id', serviceItemCodeId);

    // 実績フラグ合計セルを作成する。
    let serviceCount = this.createCell(serviceCountDate, 'result_registration_table_sum');

    // 実績フラグセルを作成する。
    let dateDailyRateCells = [];
    // 対象年月の日数を取得する。
    let targetMonthDateCnt = (new Date(date.getFullYear(), date.getMonth() + 1, 0)).getDate();
    for (let i = 0; i < targetMonthDateCnt; i++) {
      let c = this.createCellDateDailyRate(dateDailyRate[i]);
      dateDailyRateCells.push(c);
    }
    resultLbl.setAttribute('data-date-daily-rate-one-month-ago', dateDailyRateOneMonthAgo);
    resultLbl.setAttribute('data-date-daily-rate-two-month-ago', dateDailyRateTwoMonthAgo);

    // 実績行にセルを追加する。
    let resultRowCells = [resultLbl];
    resultRowCells.forEach(el => resultRow.appendChild(el));
    dateDailyRateCells.forEach(el => resultRow.appendChild(el));
    resultRow.appendChild(serviceCount);

    return resultRow;
  }

  /**
   * レコードを削除する。
   * @returns {void}
   */
  deleteRecord(){
    this.elementTbody.textContent = null;
  }

  /**
   * @return {Array}
   */
  getUserEditedData(){
    // レコードを取得する。
    let records = Array.from(this.elementTbody.children);

    return records
      // 予定行を除外する(予定行は全て偶数行に存在する)。
      .filter((record, index)=>{ return index % 2 > 0; })
      // ゴミ箱アイコンから論理削除したレコードを除外する。
      .filter(record => !record.classList.contains('rrt_record_hidden'))
      .map(record => {
        let cells = Array.from(record.children);
        let dateDailyRate = '';
        let dateDailyRateOneMonthAgo = '';
        let dateDailyRateTwoMonthAgo = '';
        let serviceCount = Number(cells[cells.length-1].textContent);

        // i = 1は実績のdate_daily_rateのインデックス。-1は合計列の除外。
        for (let i = 1, len = cells.length - 1; i < len; i++) {
          let cellInputAreaValue = cells[i].children[0].value;
          dateDailyRate += cellInputAreaValue ? cellInputAreaValue : '0';
        }
        dateDailyRate += dateDailyRate.length < 31 ? '0'.repeat(31 - dateDailyRate.length) : '';
        dateDailyRateOneMonthAgo = cells[0].getAttribute('data-date-daily-rate-one-month-ago');
        dateDailyRateTwoMonthAgo = cells[0].getAttribute('data-date-daily-rate-two-month-ago');

        return {
          date_daily_rate: dateDailyRate,
          date_daily_rate_one_month_ago: dateDailyRateOneMonthAgo,
          date_daily_rate_two_month_ago: dateDailyRateTwoMonthAgo,
          // 0は実績ラベルセルのインデックス。
          service_item_code_id: Number(cells[0].getAttribute('data-service-code-id')),
          service_count_date: serviceCount
        };
      });
  }

  /**
   * 実績フラグセルから入力欄を取得して返す。
   * @param {Element} cell 実績フラグセル
   * @returns {Element}
   */
  getResultFlgCellInputArea(cell){
    return cell.children[0];
  }

  /**
   * レコードを隠す。
   * @param {Element} planRow 予定行
   * @return {void}
   */
  hideRecord(planRow){
    // 実績行を取得する。
    let resultRow = planRow.nextElementSibling;
    // 予定行と実績行を非表示にする。
    planRow.classList.add('rrt_record_hidden');
    resultRow.classList.add('rrt_record_hidden');
    // 変更フラグをセットする。
    // document.getElementById('changed_flg').value = true;
  }

  /**
   * 介護医療院かを返す。
   * @return {bool}
   */
  isHospital(){
    return this.serviceTypeCode === '55';
  }

  /**
   * 日付セルを更新する。
   * @param {string} year
   * @param {string} month
   * @param {Array} stayOutDays
   * @param {Array} startDates
   * @param {Array} endDates
   * @return {void}
   */
  reloadDateCell(year, month, stayOutDays, startDates, endDates){
    let dateCnt = new Date(year, month, 0).getDate();

    // 日付セルを全て削除する。
    let dateCells = Array.from(document.getElementsByClassName('rrt_header_date'));
    dateCells.forEach(element => element.parentNode.removeChild(element));

    // 日付セルを作成して追加する。
    for (let i = dateCnt; i > 0; i--) {
      let cell = document.createElement('td');
      cell.textContent = i;
      cell.className = 'result_registration_table_cell result_registration_table_header rrt_header_date';

      // 外泊日の場合はグレーアウトする。
      if(stayOutDays.includes(i)){
        cell.classList.add('rrt_date_grey');
      }

      // 入居日までの日付の場合はグレーアウトする。
      if(startDates.includes(i)){
        cell.classList.add('rrt_date_grey');
      }

      // 退去日からの日付の場合はグレーアウトする。
      if(endDates.includes(i)){
        cell.classList.add('rrt_date_grey');
      }

      this.elementDateCell.after(cell);
    }

    // 曜日セルを削除する。
    let dowCells = Array.from(document.getElementsByClassName('result_registration_table_header_dow'));
    dowCells.forEach(element => element.parentNode.removeChild(element));

    // 曜日セルを作成して追加する。
    let dow = [ '日', '月', '火', '水', '木', '金', '土' ];
    for (let i = dateCnt; i > 0; i--) {
      let date = new Date(year, month-1, i);
      let day = date.getDay();
      let cell = document.createElement('td');
      cell.textContent = dow[day];
      cell.className = 'result_registration_table_cell result_registration_table_header result_registration_table_header_dow';
      if(day == 0){
        cell.style.color = 'red';
      } else if (day == 6){
        cell.style.color = 'blue';
      }

      this.elementDowCell.after(cell);
    }

    // 月間サービス計画及び実績の記録ヘッダの長さを変える。
    // 日付/曜日列と合計列を加味して2を固定で足す。
    document.getElementById('result_registration_table_ddr').colSpan = dateCnt + 2;
  }

  /**
   * サービス種類コードをセットする。
   * @param {String} serviceTypeCode 
   */
  setServiceTypeCode(serviceTypeCode){
    this.serviceTypeCode = serviceTypeCode;
  }
}
