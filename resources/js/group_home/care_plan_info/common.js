export default class Common {
    constructor() {
    }

    /**
     * バリデーション結果を表示する
     * @param {object} errorBody
     * @param {Element} validationDisplayArea
     */
    validateDisplay(errorBody, validationDisplayArea){
      let createRow = (function(key, value){
        let record = document.createElement('li');
        record.textContent = value;
        validationDisplayArea.appendChild(record);
      });

      errorBody = JSON.parse(errorBody);
      let errorList = errorBody.errors;
      Object.keys(errorList).map(key =>
          createRow(key, errorList[key])
      );
    }

    /**
     * バリデーション結果の表示をクリアする
     * @return {void}
     */
    clearValidateDisplay(validationDisplayArea){
      while(validationDisplayArea.lastChild){
        validationDisplayArea.removeChild(validationDisplayArea.lastChild);
      }
    }

    /**
   * ポップアップを表示する
   * @param {String} msg
   * @param {Object} popupParams {id, class}
   * @param {Object} btnPrams {id, class}
   * @returns {void}
   */
  showPopup(msg, popupParams, btnPrams)
  {
    let elementPopup = document.createElement('div');
    elementPopup.id = popupParams.id;

    let elementPopupContents = document.createElement('div');
    elementPopupContents.classList.add(popupParams.class);

    let elementPopupMessage = document.createElement('p');
    elementPopupMessage.innerHTML = msg;

    let elementBtnFrame = document.createElement('div');
    elementBtnFrame.classList.add('close_btn');

    let elementBtn = document.createElement('button');
    elementBtn.id = btnPrams.id;
    elementBtn.classList.add(btnPrams.class);
    elementBtn.textContent = '閉じる';
    elementBtn.addEventListener('click', () => {elementPopup.parentNode.removeChild(elementPopup);});

    elementPopup.appendChild(elementPopupContents);
    elementPopupContents.appendChild(elementPopupMessage);
    elementPopupContents.appendChild(elementBtnFrame);
    elementBtnFrame.appendChild(elementBtn);

    document.body.appendChild(elementPopup);
  }

  /**
   * 選択式のポップアップを表示する
   * @param {String} msg
   * @param {func} callBack
   * @returns {void}
   */
  showSelectPopup(msg, popupParams, btnPrams, callBack = null)
  {
    let elementPopup = document.createElement('div');
    elementPopup.id = popupParams.id;

    let elementPopupContents = document.createElement('div');
    elementPopupContents.classList.add(popupParams.class);

    let elementPopupMessage = document.createElement('p');
    elementPopupMessage.innerHTML = msg;

    let elementBtnFrame = document.createElement('div');
    elementBtnFrame.classList.add('popup_btn_position');
    elementPopup.appendChild(elementPopupContents);
    elementPopupContents.appendChild(elementPopupMessage);
    elementPopupContents.appendChild(elementBtnFrame);
    let elementBtnYes = this.createBtn(elementPopup, 'popup_yes', 'OK', callBack);
    let elementBtnCancel = this.createBtn(elementPopup,'popup_cancel', 'キャンセル');
    elementBtnFrame.appendChild(elementBtnYes);
    elementBtnFrame.appendChild(elementBtnCancel);

    document.body.appendChild(elementPopup);
  }

  /**
   * ボタンを作成する
   * @param {object} elementPopup
   * @param {string} className
   * @param {string} btnText
   * @param {func} callBack
   * @returns
   */
  createBtn(elementPopup, className, btnText, callBack = null)
  {
      let elementBtn = document.createElement('button');
      elementBtn.classList.add(className);
      elementBtn.textContent = btnText;
      if (callBack) {
          elementBtn.addEventListener('click', callBack);
      }
      elementBtn.addEventListener('click', () => {elementPopup.parentNode.removeChild(elementPopup);});
      return elementBtn;
  }
}
