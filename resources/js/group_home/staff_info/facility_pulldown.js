
import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

export default class FacilityPulldown{
  constructor(){
    this.element = document.getElementById('facility_pulldown');;
  }

  /**
   * 初期化
   * @returns {Promise}
   */
  async init(){
    await this.syncServer();
  }

  /**
   * サーバーからユーザーが参照可能な事業所情報を取得して返す。
   * @return {Promise}
   */
  async getFacilityList(){
    let res = await CustomAjax.get('/group_home/service/facility');
    let data = await res.json();
    return data;
  }

  /**
   * 選択されている値を取得
   * @returns {string}
   */
  getSelectedValue(){
    return Number(this.element.value);
  }
  /**
   * 選択されている値の表示上のテキストを取得
   * @returns {string}
   */
  getSelectedText(){
    return this.element.textContent;
  }

  /**
   * 選択肢を作成する
   * @returns {element}
   */
  createOption(facilityId,facilityName){
    let o = document.createElement('option');
    o.value = facilityId;
    o.textContent = facilityName;
    return o;
  }

  /**
   * サーバーから事業所情報を取得して反映する
   * @returns {Promise}
   */
   async syncServer(){
    let fList = await this.getFacilityList();
    if(fList===null){return;}
    
    this.element.textContent = null;
    // 取得した事業所情報を事業所選択プルダウンにセット(※グループホームの場合、事業所は1つのみが想定されている)
    fList.forEach((facility)=>{
      let option = this.createOption(facility.facility_id,facility.facility_name_kanji);
      this.element.appendChild(option);
    });
  }
}
