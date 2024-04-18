
import CustomAjax from '../../lib/custom_ajax.js';

/**
 * 事業所プルダウン
 */
export default class FacilityPulldown{
  constructor(){
    this.element = document.getElementById('facility_pulldown');
  }

  /**
   * 選択肢を作成する。
   * @return {Element}
   */
  createOption(facilityId, facilityName){
    let o = document.createElement('option');
    o.value = facilityId;
    o.textContent = facilityName;
    return o;
  }

  /**
   * ログインアカウントがアクセスできる事業所情報を全て返す。
   * @return {Promise}
   */
  async getFacilities(){
    let res = await CustomAjax.get('/group_home/service/facility');
    let data = await res.json();
    return data;
  }

  /**
   * 選択されている値の表示上のテキストを返す。
   * @return {String}
   */
  getSelectedText(){
    return this.element.textContent;
  }

  /**
   * 選択されている値を返す。
   * @return {String}
   */
  getSelectedValue(){
    return Number(this.element.value);
  }

  /**
   * 初期化
   * @return {Promise}
   */
  async init(){
    await this.syncServer();
  }

  /**
   * サーバーから事業所情報を取得して反映する。
   * @return {Promise}
   */
  async syncServer(){
    let facilities = await this.getFacilities();
    if(facilities === null){
      return;
    }
    
    this.element.textContent = null;

    // 取得した事業所情報を事業所プルダウンにセットする。
    facilities.forEach((facility) => {
      let option = this.createOption(facility.facility_id, facility.facility_name_kanji);
      this.element.appendChild(option);
    });
  }
}
