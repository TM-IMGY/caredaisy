import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

export default class FacilityPulldown{
  constructor(){
    this.element = document.getElementById('facility_pulldown');
    this.list = null;
  }

  /**
   * 初期化。サーバーから事業所情報を取得して反映する
   * @returns {Promise}}
   */
  async init(){
    let fList = await this.getFacilityList();
    this.list = fList;
    if(fList===null){return;}

    // 取得した事業所情報を事業所選択プルダウンにセット(※グループホームの場合、事業所は1つのみが想定されている)
    fList.forEach((facility)=>{
      let option = this.createOption(facility.facility_id,facility.facility_name_kanji);
      this.element.appendChild(option);
    });

    // 伝送情報でもこのjsを呼んでたのでページを指定する
    if (location.pathname == '/group_home/user_info') {
      this.visibilityTab();
    }
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
    return this.element.value;
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
   * 選択されている値のfacility_numberを取得
   * @returns {string}
   */
  getSelectedFacilityNumber(){
    let found = this.list.find((facility) => facility.facility_id == this.getSelectedValue());
    return found.facility_number;
  }

  /**
   * 表示している事業所が利用しているサービスを取得する
   * @returns {array}
   */
  async getFacilityUseService(){
    let param = this.list.find((facility) => facility.facility_id == this.getSelectedValue())
    let res = await CustomAjax.get(
      '/group_home/user_info/get_facility_use_service?'
        + 'facility_id=' + param.facility_id,
      {'X-CSRF-TOKEN':CSRF_TOKEN}
    );
    let data = await res.json();
    return data;
  }

  /**
   * 利用しているサービスによって表示されるタブを表示
   */
  async visibilityTab(){
    let serviceTypeIds = await this.getFacilityUseService();
    document.querySelectorAll('.medical_clinic_only').forEach(element => {element.style.visibility = 'hidden';})
    if (serviceTypeIds.includes(6)) {
      document.querySelectorAll('.medical_clinic_only').forEach(element => {element.style.visibility = 'visible';})
    }
  }
}
