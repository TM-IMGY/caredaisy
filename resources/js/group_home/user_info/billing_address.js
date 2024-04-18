
import ConfirmationDialog from '../../lib/confirmation_dialog.js';
import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

/**
 * 請求先情報タブ
 */
export default class BillingAddress{
  constructor(facilityID){
    this.elementID = 'tm_contents_billing_address';
    this.validationDisplayArea = document.getElementById("validateErrors");

    // 事業所IDをセット
    this.setFacilityID(facilityID);
    
    this.facilityUserID = null;
    this.userInformation = "";
    this.status = false;

    this.name = document.querySelector('#uba_name');
    this.phoneNumber = document.querySelector('#uba_phone_number');
    this.faxNumber = document.querySelector('#uba_fax_number');
    this.postalCode = document.querySelector('#uba_postal_code');
    this.location1 = document.querySelector('#uba_location1');
    this.location2 = document.querySelector('#uba_location2');
    this.bankNumber = document.querySelector('#uba_bank_number');
    this.bank = document.querySelector('#uba_bank');
    this.branchNumber = document.querySelector('#uba_branch_number');
    this.branch = document.querySelector('#uba_branch');
    this.bankAccount = document.querySelector('#uba_bank_account');
    this.depositor = document.querySelector('#uba_depositor');
    this.remarksForReceipt = document.querySelector('#uba_remarks_for_receipt');
    this.remarksForBill = document.querySelector('#uba_remarks_for_bill');

    this.newBtn = document.getElementById('uba_new_btn');
    this.saveBtn = document.getElementById('uba_save_btn');
    this.getFacilityUserBtn = document.getElementById('uba_get_facility_user_btn');

    if (this.newBtn !== null){
      this.newBtn.addEventListener('click',this.newBtnClick.bind(this));
    }
    if (this.saveBtn !== null){
      this.saveBtn.addEventListener('click',this.saveBtnClick.bind(this));
    }
    this.getFacilityUserBtn.addEventListener('click',this.getUserInfo.bind(this));

  }
  clear(){
    document.querySelector('input[name="uba_payment_method"]').checked  = true;
    this.name.value = '';
    this.phoneNumber.value = '';
    this.faxNumber.value = '';
    this.postalCode.value = '';
    this.location1.value = '';
    this.location2.value = '';
    this.bankNumber.value = '';
    this.bank.value = '';
    this.branchNumber.value = '';
    this.branch.value = '';
    this.bankAccount.value = '';
    document.querySelector('input[name="uba_type_of_account"]').checked  = true;
    this.depositor.value = '';
    this.remarksForReceipt.value = '';
    this.remarksForBill.value = '';   
  }

  async newBtnClick()
  {      
    this.clear();
    this.clearValidateDisplay();
  }

  /**
   * 保存ボタンのクリックイベント
   * @return {Promise}
   */
  async saveBtnClick(){
    this.clearValidateDisplay();
    // 確認ダイアログを表示する
    let confirmationDialog = new ConfirmationDialog(
      'この内容で保存しますか',
      this.save.bind(this)
    );
    confirmationDialog.show();
  }

  async callbackSave()
  {
      //変更フラグをリセット
      document.getElementById("changed_flg").value = false;
  }

  async save()
  {
      if(this.facilityUserID != null) {
          let params = {}
          params["facility_id"] = this.facilityID;
          params["facility_user_id"] = this.facilityUserID;
          params["payment_method"] = document.querySelector('input:checked[name="uba_payment_method"]').value;
          params["name"] = this.name.value;
          params["phone_number"] = this.phoneNumber.value;
          params["fax_number"] = this.faxNumber.value;
          params["postal_code"] = this.postalCode.value;
          params["location1"] = this.location1.value;
          params["location2"] = this.location2.value;
          params["bank_number"] = this.bankNumber.value;
          params["bank"] = this.bank.value;
          params["branch_number"] = this.branchNumber.value;
          params["branch"] = this.branch.value;
          params["bank_account"] = this.bankAccount.value;
          params["type_of_account"] = document.querySelector('input:checked[name="uba_type_of_account"]').value;
          params["depositor"] = this.depositor.value;
          params["remarks_for_receipt"] = this.remarksForReceipt.value;
          params["remarks_for_bill"] = this.remarksForBill.value;


          return await CustomAjax.send(
              "POST",
              "/group_home/user_info/uninsured_billing_address/save",
              {"Content-Type":"application/json", "X-CSRF-TOKEN":CSRF_TOKEN},
              params,
              "callbackSave",
              this
          );
      }
  }
  
  async validateDisplay(errorBody)
  {
      let createRow = (function(key, value){
          let record = document.createElement('li');
          let validationDisplayArea = document.getElementById("validateErrors");
          record.textContent = value;
          validationDisplayArea.appendChild(record);
      });

      errorBody = JSON.parse(errorBody);
      let errorList = errorBody.errors;
      Object.keys(errorList).map(key => 
          createRow(key, errorList[key])
      );
  }

  async clearValidateDisplay()
  {
      while(this.validationDisplayArea.lastChild){
          this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
      }
  }
  callbackGetUserInfo(json){
		if(json !== void 0){
      this.name.value = json.last_name + " " + json.first_name;
      this.phoneNumber.value = json.phone_number;
      this.faxNumber.value = json.cell_phone_number;
      this.postalCode.value = json.postal_code;
      this.location1.value = json.location1;
      this.location2.value = json.location2;
    }
  }
  async getUserInfo()
  {
    if (this.facilityUserID){
      return CustomAjax.send(
        'GET',
        '/group_home/user_info/uninsured_billing_address/get_facility_user?facility_user_id=' + this.facilityUserID,
        {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        [],
        "callbackGetUserInfo",
        this
      );
    }
      
  }

  setFacilityID(facilityID){
    this.facilityID = facilityID;
  }
  async getData(){
    return CustomAjax.send(
      'GET',
      '/group_home/user_info/uninsured_billing_address/get_billing_address?facility_user_id=' + this.facilityUserID + '&facility_id=' + this.facilityID,
      {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      [],
      "callbackGetUninsuredBillingAddress",
      this
    );
  }
  /**
   * @param {bool} status 表示のブーリアン値
   */
   async setActive(status){
    // タブ切り替え時に情報を取得する    
    this.status = status;
    if (status && this.facilityUserID != null){
        this.getData();
    }
  }
  callbackGetUninsuredBillingAddress(json){
    this.clear();
    if(json !== void 0){
      
      this.name.value = json[0].name;
      this.phoneNumber.value = json[0].phone_number;
      this.faxNumber.value = json[0].fax_number;
      this.postalCode.value = json[0].postal_code;
      this.location1.value = json[0].location1;
      this.location2.value = json[0].location2;
      this.bankNumber.value = json[0].bank_number;
      this.bank.value = json[0].bank;
      this.branchNumber.value = json[0].branch_number;
      this.branch.value = json[0].branch;
      this.bankAccount.value = json[0].bank_account;
      this.depositor.value = json[0].depositor;
      this.remarksForReceipt.value = json[0].remarks_for_receipt;
      this.remarksForBill.value = json[0].remarks_for_bill;
      document.querySelector('input[name="uba_type_of_account"][value="' + json[0].type_of_account + '"]').checked = true; 
      document.querySelector('input[name="uba_payment_method"][value="' + json[0].payment_method + '"]').checked = true;
    }
  }
  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  setFacilityUser(user){
    this.userInformation = user;
    this.facilityUserID = user.facilityUserID;

    user.facilityUserID ? $(".billing_address_btn").show() : $(".billing_address_btn").hide();//登録、編集ボタンを表示する処理

    if(this.status && user.facilityUserID != null){
      this.getData();
    }else{
      this.clear();
      this.clearValidateDisplay();
    }
  }
}
