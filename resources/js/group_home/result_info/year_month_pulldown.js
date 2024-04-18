/**
 * 年月プルダウン
 */
export default class YearMonthPulldown{
  constructor(){
    this.cacheValue = null;
    this.confirms = [];
    this.element = document.getElementById('year_month_pulldown');
    this.notifications = [];

    // プルダウンにクリックイベントを追加する。
    this.element.addEventListener('mousedown', this.click.bind(this));
    // 値変更時のイベントを追加する。
    this.element.addEventListener('change', this.change.bind(this));

    // 訪問者の端末のシステム時刻を参照して対象月を変更する(月を10日終わり11日始まりとして計算する)。
    let today = new Date();
    let targetYear = today.getFullYear();
    let targetMonth = today.getDate() <= 10 ? today.getMonth() : today.getMonth() + 1;
    if(targetMonth === 0){
        targetYear -= 1;
        targetMonth = 12;
    }
    targetMonth = (('0' + targetMonth).slice(-2));
    this.setYm(targetYear, targetMonth);
  }

  /**
   * 値変更前の確認先を追加する。
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   */
  addConfirm(callBack){
    this.confirms.push(callBack);
  }

  /**
   * 値変更時の通知先を追加する。
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @return {void}
   */
  addNotification(callBack){
    this.notifications.push(callBack);
  }

  /**
   * チェンジイベント。
   * @return {Promise}
   */
  async change(){
    // 値を取得する。
    let value = this.getSelectedValue();
    // 値がキャッシュと異なっている場合の処理。
    if(value !== this.cacheValue){
      // 背景色を更新する。
      this.updateBackgroundColor(value.year, value.month);

      // 値の変更を通知する。
      for (let i = 0, len = this.notifications.length; i < len; i++) {
        await this.notifications[i](value);
      }
      this.cacheValue = value;
    }
  }

  /**
   * クリックイベント
   * @return {Promise}
   */
  async click(){
    for (let i = 0, len = this.confirms.length; i < len; i++) {
      await this.confirms[i]();
    }
  }

  /**
   * 選択されている値を返す。
   * @returns {Object} key: year, month
   */
  getSelectedValue(){
    let find = this.element.value.match(/([0-9]{4})\/([0-9]{1,2})/);
    return {year: Number(find[1]), month: Number(find[2])};
  }

  /**
   * 対象年月をセットする。
   * @param {Number} year
   * @param {Number} month
   */
  setYm(year, month){
    let options = this.element.children;
    let target = year + '/' + month;
    for (let i = 0, len = options.length; i < len; i++) {
      if(options[i].value === target){
        options[i].selected = true;
        break;
      }
    }
  }

  /**
   * 背景色を更新する。当月かそうでないかで色が変わる。
   * @param {Number} year
   * @param {Number} month
   * @return {void}
   */
  updateBackgroundColor(year, month){
    let today = new Date();
    if(today.getFullYear() === year && today.getMonth() + 1 === month){
      this.element.classList.remove('ym_pulldown_inactive');
    } else {
      this.element.classList.add('ym_pulldown_inactive');
    }
  }
}
