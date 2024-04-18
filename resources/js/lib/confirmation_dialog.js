/**
 * 確認ダイアログ。
 */
export default class ConfirmationDialog {
  /**
   * コンストラクタ。
   * @param {String} message メッセージ
   * @param {Function} yesBtnEvent はいボタンのイベント
   * @param {Function} noBtnEvent いいえボタンのイベント
   */
  constructor(message, yesBtnEvent, noBtnEvent){
    // 雛型から取得して要素を複製する。
    this.element = document.getElementsByClassName('caredaisy_confirmation_dialog')[0].cloneNode(true);
    this.yesBtnEvent = yesBtnEvent ? yesBtnEvent : null;
    this.noBtnEvent = noBtnEvent ? noBtnEvent : null;

    document.body.appendChild(this.element);

    // はいボタンにイベントを紐づける。
    this.getYesBtn().addEventListener('click', this.clickYesBtn.bind(this));
    // いいえボタンにイベントを紐づける。
    this.getNoBtn().addEventListener('click', this.clickNoBtn.bind(this));

    this.setMessage(message);

    this.getYesBtn().setAttribute('dusk', 'confirmation-dialog-button-yes');

    this.getNoBtn().setAttribute('dusk', 'confirmation-dialog-button-no');
  }

  /**
   * はいボタンのクリックイベント。
   * @return {Promise}
   */
  async clickNoBtn(){
    this.destroy();
    if(this.noBtnEvent){
      await this.noBtnEvent();
    }
  }

  /**
   * はいボタンのクリックイベント。
   * @return {Promise}
   */
  async clickYesBtn(){
    this.destroy();
    if(this.yesBtnEvent){
      await this.yesBtnEvent();
    }
  }

  /**
   * ダイアログを破棄する。
   * @return {void}
   */
  destroy(){
    this.element.parentNode.removeChild(this.element);
  }

  /**
   * いいえボタンの要素を返す。
   * @return {Element}
   */
  getNoBtn(){
    return this.element.getElementsByClassName('caredaisy_confirmation_dialog_no')[0];
  }

  /**
   * はいボタンの要素を返す。
   * @return {Element}
   */
  getYesBtn(){
    return this.element.getElementsByClassName('caredaisy_confirmation_dialog_yes')[0];
  }

  /**
   * メッセージをセットする。
   * @param {String} message 
   * @return {void}
   */
  setMessage(message){
    this.element.getElementsByClassName('caredaisy_confirmation_dialog_message')[0].textContent = message;
  }

  /**
   * ダイアログを表示する。
   * @return {void}
   */
  show(){
    this.element.classList.remove('caredaisy_confirmation_dialog_hidden');
  }
}
