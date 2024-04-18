
import ChangePopup from '../chnage_popup.js'

/**
 * タブ管理クラス。
 */
export default class TabManager{
  constructor(){
    this.notifications = {};
    this.selectedContents = null;
    this.selectedSubTab = null;
    this.subTabs = Array.from(document.getElementById('tm_sub_tab').children);
    // サブタブにクリックイベントを追加する。
    this.subTabs.forEach(tab => tab.addEventListener('click', this.clickSubTab.bind(this, tab)));
  }

  /**
   * タブ変更時の通知先を追加する。
   * @param {String} id 通知先のid
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   */
  addNotification(id, callBack){
    this.notifications[id] = callBack;
  }

  /**
   * サブタブのクリックイベント。
   * @param {Element} tab
   * @return {Promise}
   */
  async clickSubTab(tab){
    if (!ChangePopup.popup(tab)){
      return false;
    }
    this.selectedSubTab?.classList.remove('tm_subtab_active');
    this.selectedContents?.classList.add('tm_contents_hidden');

    let oldContentsID = this.selectedSubTab?.getAttribute('data-contents-id');
    this.notifications[oldContentsID] && await this.notifications[oldContentsID](false);

    tab.classList.add('tm_subtab_active');
    let contentsID = tab.getAttribute('data-contents-id');
    let contents = document.getElementById(contentsID);
    contents.classList.remove('tm_contents_hidden');

    this.selectedSubTab = tab;
    this.selectedContents = contents;

    this.notifications[contentsID] && await this.notifications[contentsID](true);
  }

  /**
   * タブのインデックスからクリックする。
   * @param {Number} index 
   * @return {void}
   */
  clickWithIndex(index){
    this.subTabs[index].click();
  }

  /**
   * 活性化しているタブのコンテンツのIDを返す。
   * @return {String}
   */
  getActiveContentsId(){
    return this.selectedSubTab.getAttribute('data-contents-id');
  }

  /**
   * コンテンツのIDで非表示にする。
   * @param {String} id
   */
  hideWithContentsId(id){
    let subTabs = this.subTabs;
    for (let i = 0, len = subTabs.length; i < len; i++) {
      if(subTabs[i].getAttribute('data-contents-id') === id){
        subTabs[i].classList.add('tm_subtab_hidden');
      }      
    }
  }

  /**
   * コンテンツのIDで表示する。
   * @param {Number} id
   * @return {void}
   */
  showWithContentsId(id){
    let subTabs = this.subTabs;
    for (let i = 0, len = subTabs.length; i < len; i++) {
      if(subTabs[i].getAttribute('data-contents-id') === id){
        subTabs[i].classList.remove('tm_subtab_hidden');
      }      
    }
  }
}
