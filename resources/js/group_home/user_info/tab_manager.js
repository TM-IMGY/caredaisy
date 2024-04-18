import ChangePopup from '../chnage_popup.js'

export default class TabManager{
  constructor(){
    let subTab = document.getElementById('tm_sub_tab');

    this.selectedSubTab = null;
    this.selectedContents = null;
    this.notification = {};
    this.subTabList = Array.from(subTab.children);
    
    // サブタブにクリックイベントをセット
    this.subTabList.forEach(tab=>tab.addEventListener('click',this.clickSubTab.bind(this,tab)));
  }
  
  // サブタブクリックイベント
  async clickSubTab(tab){
    if (!ChangePopup.popup(tab)){
      return false;
    }
    this.selectedSubTab?.classList.remove('tm_subtab_active');

    if(this.selectedSubTab){// 標準化後、CSSクラス指定に切り替える可能性アリ
      this.selectedSubTab.style.color = '#121212';
    }
    this.selectedContents?.classList.add('tm_contents_hidden');

    let oldContentsID = this.selectedSubTab?.getAttribute('data-contents-id');
    this.notification[oldContentsID] && await this.notification[oldContentsID](false);

    tab.classList.add('tm_subtab_active');
    tab.style.color = '#fafafa';// 標準化後、CSSクラス指定に切り替える可能性アリ
    let contentsID = tab.getAttribute('data-contents-id');
    let contents = document.getElementById(contentsID);
    contents.classList.remove('tm_contents_hidden');

    this.selectedSubTab = tab;
    this.selectedContents = contents;

    this.notification[contentsID] && await this.notification[contentsID](true);
  }

  /**
   * 値変更時の通知先を追加
   * @param {string} id 通知先のid
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   */
  addNotification(id,callBack){
    this.notification[id] = callBack;
  }

  /**
   * 標準化後、CSSクラス指定に切り替える可能性アリ
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  tabActivation(user){
    this.subTabList.forEach(function(tab,index){
      if(user.facilityUserID == null){
        if(index != 0){
          tab.style.color = '#A8A8A8';
          tab.style.pointerEvents = 'none';
        }else{
          tab.style.color = '#fafafa';
          tab.style.pointerEvents = '';
        }
      }else{
        if(tab.classList.contains('tm_subtab_active')){
          tab.style.color = '#fafafa';
        }else{
          tab.style.color = '#121212';
        }
        tab.style.pointerEvents = '';
      }
    });
  }
}
