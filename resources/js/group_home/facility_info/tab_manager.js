import ChangePopup from '../chnage_popup.js'

/**
 * 事業所情報画面のサブタブに責任を持つクラス
 */
export default class TabManager {
  constructor(){
    this.layer = null;
    this.layerNotificationAddresses = [];
    this.notification = {};
    this.selectedSubTab = null;
    this.selectedContents = null;
    this.subTabList = Array.from(document.getElementById('tm_sub_tab').children);
    this.changedFlg = document.getElementById("changed_flg");

    // サブタブにクリックイベントをセット
    this.subTabList.forEach(tab => tab.addEventListener('click', this.clickSubTab.bind(this, tab, true)));
  }

  /**
   * サブタブのクリックイベント
   * @param {Element} tab クリックされたサブタブの要素
   * @param {Boolean} changeLayerNotifyFlag 通知するかのフラグ
   * @return {Promise}
   */
  async clickSubTab(tab, changeLayerNotifyFlag){
    if (!ChangePopup.popup(tab)){
      return false;
    }
    // キャッシュ情報を確保する。
    let oldSelectedSubTab = this.selectedSubTab;
    let oldContentsId = oldSelectedSubTab?.getAttribute('data-contents-id');
    let oldLayer = this.layer;

    // 選択状態が変わらなければ終了する。
    if(oldSelectedSubTab === tab){
      return;
    }

    // 選択されていたタブの状態をクリアする。
    this.selectedSubTab?.classList.remove('tm_subtab_active');
    this.selectedContents?.classList.add('tm_contents_hidden');

    // 選択されたタブをタブ選択常態にする。
    let contentsId = tab.getAttribute('data-contents-id');
    let contents = document.getElementById(contentsId);
    tab.classList.add('tm_subtab_active');
    contents.classList.remove('tm_contents_hidden');

    // 状態をキャッシュする。
    this.selectedSubTab = tab;
    this.selectedContents = contents;

    // 事業所情報画面は内部的にレイヤー構造になっている。
    // そのため選択したタブのレイヤーに変更があった場合は通知する必要がある。
    let layer = 'corporation';
    // TODO: ここのfacilityは施設の意味で使われているので注意する。
    if(contentsId === 'tm_contents_facility'){
      layer = 'institution';
    }
    else if(contentsId === 'tm_contents_office' || contentsId === 'tm_contents_service_type'){
      layer = 'facility';
    }
    else if(contentsId === 'tm_contents_addition_status' || contentsId === 'tm_contents_uninsured_service'){
      layer = 'service';
    }

    // 状態をキャッシュする。
    this.layer = layer;

    // 選択状態の変更を通知する。
    if(this.notification[oldContentsId]){
      await this.notification[oldContentsId](false);
    }
    if(this.notification[contentsId]){
      await this.notification[contentsId](true);
    }

    // レイヤーの参照先に変更があったことを通知する。
    if(oldLayer !== layer && changeLayerNotifyFlag){
      await this.changeLayerNotify(layer);
    }
  }

  /**
   * レイヤーからサブタブを選択する。
   * @param {String} layer
   * @return {Promise} 
   */
  async clickSubTabByLayer(layer, changeLayerNotifyFlag){
    let contentsId = 'tm_contents_corporation';
    switch (layer) {
      case 'service':
        contentsId = 'tm_contents_addition_status';
        break;
      case 'facility':
        contentsId = 'tm_contents_office';
        break;
      case 'institution':
        contentsId = 'tm_contents_facility';
        break;
      default:
        break;
    }

    let tab = null;
    for (let i = 0, len = this.subTabList.length; i < len; i++) {
      if(this.subTabList[i].getAttribute('data-contents-id') === contentsId){
        tab = this.subTabList[i];
        break;
      }
    }
    this.clickSubTab(tab, changeLayerNotifyFlag);
  }

  /**
   * 値変更時の通知先を追加する
   * @param {String} id 通知先のid
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   */
  addNotification(id, callBack){
    this.notification[id] = callBack;
  }

  /**
   * レイヤーに変更があったことを通知する。
   * @param {String} layer
   * @returns {Promise} 
   */
  async changeLayerNotify(layer){
    for (let i = 0, len = this.layerNotificationAddresses.length; i < len; i++) {
      await this.layerNotificationAddresses[i](layer);
    }
  }

  /**
   * レイヤーの参照先に変更があった場合の通知先に追加する。
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   */
  addChangeLayerNotificationAddress(callBack){
    this.layerNotificationAddresses.push(callBack);
  }
}
