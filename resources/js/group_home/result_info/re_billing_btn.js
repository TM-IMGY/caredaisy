export default class ReBillingBtn{
  constructor(){
    this.CONFIRM_TXT_CANCEL = '再請求編集を中止しますか?';
    this.CONFIRM_TXT_OK = '再請求データを作成しますか?';

    // 再請求ボタン
    this.element = document.getElementById('rb_btn');
    // 再請求モード
    this.isReBillingMode = null;
    // 通知先
    this.notificationAddresses = [];

    // 再請求ボタンにクリックイベントを紐づける
    this.element.addEventListener('click', this.confirmChangeReBillingMode.bind(this));
    // ページ遷移時のイベントを設定する
    window.addEventListener('beforeunload', this.confirmPageReload.bind(this));

    // 再請求モードを偽で設定する
    this.setReBillingMode(false);
  }

  /**
   * 通知先を設定する
   * @param {Function} callBack 通知先となるコールバック関数
   * @returns {void}
   */
  addNotification(callBack){
    this.notificationAddresses.push(callBack);
  }

  /**
   * 再請求ボタンポップアップのはいボタンのクリックイベント
   * @return {Promise}
   */
  async clickRbBtn() {
    await this.setReBillingMode(!this.isReBillingMode);
  }

  /**
   * 再請求モードの解除を確認する
   * @returns {Boolean}
   */
  async confirmCancelReBillingMode(){
    // 現状が通常モードであれば確認は不要
    if(!this.isReBillingMode){
      return true;
    }

    let result = confirm(this.CONFIRM_TXT_CANCEL);
    if (result) {
      await this.setReBillingMode(!this.isReBillingMode);
    }

    return result;
  }

  /**
   * 再請求モードを変更する
   * @returns {Promise}
   */
  async confirmChangeReBillingMode(){
    // 確認テキストを作成する
    let text = this.isReBillingMode ? this.CONFIRM_TXT_CANCEL : this.CONFIRM_TXT_OK;
    if (confirm(text)) {
      await this.setReBillingMode(!this.isReBillingMode);
    }
  }

  /**
   * ページリロード時のイベント
   * @param {Event} event 
   */
  confirmPageReload(event){
    // 請求モードの時は確認アラートを出す
    if(this.isReBillingMode){
      event.preventDefault();
      // 非推奨だが設定しないとアラートが出現しない
      event.returnValue = this.CONFIRM_TXT_CANCEL;
    }
  }

  /**
   * 通知先に再請求モードの変更を通知する
   * @returns {Promise}
   */
  async notification() {
    for (let i=0,len=this.notificationAddresses.length; i<len; i++) {
      let sendData = {is_re_billing_mode: this.isReBillingMode};
      await this.notificationAddresses[i](sendData);
    }
  }

  /**
   * 再請求モードをセットする
   * @param {Boolean} isReBillingMode 再請求モードにするかどうか
   * @return {Promise}
   */
  async setReBillingMode(isReBillingMode){
    this.isReBillingMode = isReBillingMode;
    // 再請求ボタンの文言を切り替える
    this.element.textContent = this.isReBillingMode ? '通常' : '再請求';
    // 変更を通知する
    await this.notification();
  }
}
