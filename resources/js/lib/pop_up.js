/**
 * 確認ダイアログ。
 */
export default class PopUp {
  /**
   * コンストラクタ。
   * @param {Array} messages メッセージ
   */
  constructor(messages){
    document.body.insertAdjacentHTML('beforeend', `
<div id="caredaisy_popup">
  <div id="caredaisy_popup_window">
    <div id="caredaisy_popup_close">×</div>
    <div id="caredaisy_popup_messages"></div>
  </div>
</div>
`);
    this.element = document.getElementById('caredaisy_popup');

    // メッセージをセットする。
    this.setMessage(messages);

    // 閉じるボタンにイベントを追加する。
    document.getElementById('caredaisy_popup_close').addEventListener('click', this.destroy.bind(this));
  }

  /**
   * ダイアログを破棄する。
   * @returns {void}
   */
  destroy(){
    this.element.parentNode.removeChild(this.element);
  }

  /**
   * メッセージをセットする。
   * @param {Array} messages
   * @returns {void}
   */
  setMessage(messages){
    for (let i = 0; i < messages.length; i++) {
      let elementMessage = document.createElement('div');
      elementMessage.textContent = messages[i];
      document.getElementById('caredaisy_popup_messages').appendChild(elementMessage);      
    }
  }
}
