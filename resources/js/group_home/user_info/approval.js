import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';

const APPROVAL_STRING = '申請中';

/**
 * 認定情報タブで閲覧できるビュー
 * @author ttakenaka
 */

export default class Approval{
  constructor(facilityID){
    this.expirationMonthByHalfYear = 6;
    this.expirationMonthBy1Year  = 12;
    this.expirationMonthBy3Year  = 36;
    this.expirationMonthBy4Year  = 48;
    this.elementID = 'tm_contents_approval';
    this.element = document.getElementById(this.elementID);
    this.user_care_info_id = null;
    this.facilityUserTableSyncServer = null;
    this.facilityID = facilityID;

    this.mandatoryExclusions = document.querySelectorAll('.mandatory_exclusion');
    this.approvalDate = document.getElementById('text_item1_approval');
    this.startDate = document.getElementById('text_item2_approval');
    this.endDate = document.getElementById('text_item3_approval');
    this.dateQualification = document.getElementById('text_item4_approval');
    this.dateCfmInsCard = document.getElementById('text_item5_approval');
    this.disabledTargetDate = document.querySelectorAll('.disabled_target_date');
    this.endDateBtns = document.querySelectorAll('.end_date_btn_approval');
    this.validationDisplayArea = document.getElementById("validateErrorsApproval");

    document.getElementById('text_item2_approval').addEventListener('change',this.inputChangeApproval.bind(this))
    document.querySelectorAll('.end_date_btn_approval').forEach( e =>{
      e.addEventListener('change',this.endDateBtn.bind(this))
    });
    if (document.getElementById('clearBtn_approval') !== null){
      document.getElementById('clearBtn_approval').addEventListener('click',this.submitApproval.bind(this))
    }
    if (document.getElementById('js-updata-popup_approval') !== null){
      document.getElementById('js-updata-popup_approval').addEventListener('click',this.submitApproval.bind(this))
    }
    document.getElementById('updatabtn_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('cancelbtn_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('updatebtn2_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('cancelbtn2_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('updatebtn5_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('cancelbtn5_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('errorbtn_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('errorbtn2_approval').addEventListener('click',this.submitApproval.bind(this))
    document.getElementById('select_list2_approval').addEventListener('change',this.changeStatus.bind(this))

    this.approvalDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    this.startDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    this.endDate.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    this.dateQualification.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
    this.dateCfmInsCard.addEventListener('input', JapaneseCalendar.inputChangeJaCal.bind(this))
  }

  /**
   * 認定状況を申請中に変更した場合の処理
   */
  changeStatus(event)
  {
    this.resetDisable();

    if (event.target.value == 1) {
      this.applyingForApproval()
    }
  }

  /**
   * 認定状況が申請中
   */
  applyingForApproval()
  {
    this.hiddenMandatory();
    this.inactiveEndDateBtn();
    this.disableOfDate();
  }

  /**
   * 認定年月日・有効開始日・有効終了日の選択不可を解除
   */
  resetDisable()
  {
    this.visibleMandatory();
    this.activeEndDateBtn();
    this.disableCancelOfDate();
  }

  /**
   * 認定年月日・有効開始日・有効終了日を操作不可に
   */
  disableOfDate()
  {
    this.disabledTargetDate.forEach( e => {
      e.innerText = "";
      e.value = "";
      e.disabled = true;
    })
  }

   /**
   * 認定年月日・有効開始日・有効終了日を操作可に
   */
  disableCancelOfDate()
  {
    this.disabledTargetDate.forEach( e => {
      e.disabled = false;
    })
  }

  /**
   * 有効終了日選択ボタンを利用不可に
   */
  inactiveEndDateBtn()
  {
    this.endDateBtns.forEach(e => {
      e.disabled = true;
      e.checked = false;
    })
  }

  /**
   * 有効終了日選択ボタンを利用可に
   */
   activeEndDateBtn()
   {
     this.endDateBtns.forEach(e => {
       e.disabled = false;
     })
   }

  /**
   * 必須の文字を非表示にする
   */
  hiddenMandatory()
  {
    this.mandatoryExclusions.forEach(e => {
      e.style.visibility = 'hidden'
    })
  }

  /**
   * 必須の文字を表示する
   */
  visibleMandatory()
  {
    this.mandatoryExclusions.forEach(e => {
      e.style.visibility = 'visible'
    })
  }

  /**
   * バリデーションメッセージを表示
   */
  validateDisplay(errorBody)
  {
    let createRow = (function(key, value){
      let record = document.createElement('li');
      let validationDisplayArea = document.getElementById("validateErrorsApproval");
      record.textContent = value;
      validationDisplayArea.appendChild(record);
    });

    let errorList = errorBody.errors;
    Object.keys(errorList).map(key =>
      createRow(key, errorList[key])
    );
  }

  /**
   * バリデーションメッセージを削除
   */
  clearValidateDisplay()
  {
      while(this.validationDisplayArea.lastChild){
          this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
      }
  }

  formClear(){
    document.getElementById("select_list1_approval").options[0].selected = true;
    document.getElementById("select_list2_approval").options[0].selected = true;
    document.getElementById("text_item1_approval").value = "";
    document.getElementById("text_item2_approval").value = "";
    document.getElementById("text_item3_approval").value = "";
    document.getElementById("text_item4_approval").value = "";
    document.getElementById("text_item5_approval").value = "";
    document.getElementById("newApprovalData").value = 1;//新規登録として準備

    let jaCalAll = this.element.querySelectorAll('[id^="jaCal"]');
    jaCalAll.forEach(e =>{ e.innerHTML = ""; });

    let elements = document.getElementsByClassName("selectTableApproval");
    for(let i=0; i<elements.length; i++){
      elements[i].style.backgroundColor ="rgb(250,250,250)";
    }
    // 年数ボタン非活性
    let endDateBtn = document.getElementsByClassName("end_date_btn_approval");
    for (let i = 0; i < endDateBtn.length; i++) {
      endDateBtn[i].disabled = true;
      endDateBtn[i].checked = false;
    }
    this.resetDisable();
  }

  /**
   * 新規登録・保存ボタン押下時処理
   * @param {object} event
   * @returns
   */
  async submitApproval(event){
    let submitBtn = event.target.id
    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;
    this.clearValidateDisplay();
    if( submitBtn == "clearBtn_approval"){
      // 新規登録押下処理
      this.formClear();
      return;
    }else if(submitBtn == "js-updata-popup_approval"){
      // 保存ボタン押下処理
      let careLevel = document.getElementById("select_list1_approval").value;
      let certificationStatus = document.getElementById("select_list2_approval").value;
      let recognitionDate = document.getElementById("text_item1_approval").value;
      let startDate = document.getElementById("text_item2_approval").value;
      let endDate = document.getElementById("text_item3_approval").value;
      let facilityUserId = document.getElementById("getFacilityIdApproval").value;
      let saveIdApproval = document.getElementById("saveGetIdApproval").value;

      let params = {
        'care_level': careLevel,
        'certification_status': certificationStatus,
        'recognition_date': recognitionDate,
        'start_date': startDate,
        'end_date': endDate,
        'facility_user_id': facilityUserId,
        'save_id_approval': saveIdApproval,
      }

      // 申請中を選択した場合、または新規登録する場合にすでに申請中がリストにあった場合にエラーのポップアップを表示させる
      if (document.getElementById("select_list2_approval").value == 1 || $('#newApprovalData').val() == 1) {
        let certificationStatus = document.getElementsByClassName("text_certification_status_approval");
        let saveId = document.getElementsByClassName("save_id");
        for (let i = 0; i < certificationStatus.length; i++) {
          // 申請中が存在した場合。ただし、自分自身を更新する場合は除外する
          if (certificationStatus[i].innerHTML === APPROVAL_STRING
            && (saveIdApproval != saveId[i].value || $('#newApprovalData').val() == 1)) {
            $("#overflow_approval4").show();
            return;
          }
        }
      }

      // 必須項目のエラーの有無を確認
      let checkResultRes = await this.saveValuesCheck(params);

      // checkResultResがtrueなら通常の更新ポップアップ表示
      // falseなら年確認更新ポップアップ表示
      if(checkResultRes === true){
        // 申請中を認定済へ変更する場合には「変更した内容を更新しますか？」の表示とする。

        // todo: チケット1336の修正タイミングで1336と同じように申請中の文字列を定数化する。
        let selectedTable = document.getElementsByClassName("selectTableApproval");
        for (let i = 0; i < selectedTable.length; i++){
          if(selectedTable[i].style.backgroundColor == 'rgb(255, 255, 238)'){
            if(selectedTable[i].getElementsByClassName('text_certification_status_approval')[0].innerHTML == '申請中'){
              $("#overflow_approval5").show();
              return;
            }
          }
        }
        // 入力に矛盾が無いかチェック
        // 「上書かれてしまいますが～」の表示。
        if(document.getElementById("newApprovalData").value == 0){
          $("#overflow_approval").show();
          return;
        }
        // 「有効開始日より4年以降の年月～」の表示。
      }else if(checkResultRes === false){
        $("#overflow_approval3").show();
        return;
      }

    }else if(submitBtn == "updatabtn_approval"){
      // 更新ポップアップ処理
    }else if(submitBtn == "cancelbtn_approval"){
      $("#overflow_approval").hide();
      return;
    }else if(submitBtn == "errorbtn_approval"){
      // 更新ポップ閉じるボタン処理
      $("#overflow_approval").hide();
      $("#overflow_approval2").hide();
      return;
    }else if(submitBtn == "errorbtn2_approval"){
      // 申請中エラー閉じるボタンの処理
      $("#overflow_approval4").hide();
      return;
      // 更新ポップアップ処理
    }else if(submitBtn == "cancelbtn5_approval"){
      $("#overflow_approval5").hide();
      return;
    }else if(submitBtn == "updatebtn2_approval"){
      // 年確認更新ポップアップ処理
    }else if(submitBtn == "cancelbtn2_approval"){
      // 年確認更新ポップ閉じるボタン処理
      // 閉じた後に有効終了日を選択状態にする
      $("#overflow_approval3").hide();
      this.endDate.focus();
      return;
    }

    let saveIdApproval = document.getElementById("saveGetIdApproval").value;
    if(document.getElementById("newApprovalData").value == 1){
      saveIdApproval = 0;
    }
    let careLevel = document.getElementById("select_list1_approval").value;
    let certificationStatus = document.getElementById("select_list2_approval").value;
    let recognitionDate = document.getElementById("text_item1_approval").value;
    let startDate = document.getElementById("text_item2_approval").value;
    let endDate = document.getElementById("text_item3_approval").value;
    let dateQualification = document.getElementById("text_item4_approval").value;
    let dateConfirmationInsuranceCard = document.getElementById("text_item5_approval").value;
    let facilityUserId = document.getElementById("getFacilityIdApproval").value;
    //↓↓　DB登録処理　↓↓----------------------------------------------------------------------------------------
    $.ajaxSetup({
      headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
    });
    // 権限チェックのために"facilityId"だけ'facility_user_id'に改修
    $.ajax({
      url:'user_info/approval/store',
      type:'POST',
      data:{'careLevel': careLevel,
            'certificationStatus': certificationStatus,
            'recognitionDate': recognitionDate,
            'startDate': startDate,
            'endDate': endDate,
            'date_confirmation_insurance_card': dateConfirmationInsuranceCard,
            'date_qualification': dateQualification,
            'facility_user_id': facilityUserId,
            'saveGetIdApproval': saveIdApproval},
    })
    .done(function(data){
      // DBにすでに申請中があった場合にエラーを出す
      if (data == false) {
        $("#overflow_approval4").show();
        if(submitBtn == "errorbtn2_approval"){
          // 申請中エラー閉じるボタンの処理
          $("#overflow_approval4").hide();
        }
        return;
      }
      //利用者ユーザ更新
      this.facilityUserTableSyncServer({facilityID:this.facilityID});
      // テーブル情報クリア
      let tableElem = document.getElementById("table_approval");
      let tbodyElem = document.getElementById("table_tbody_approval");
      // テーブル情報の削除
      while( tableElem.rows[ 1 ] ){
        tableElem.deleteRow( 1 );
      }
      // 保存していたIDを削除
      for(let i = document.getElementById("saveIdNumMaxApproval").value; i > 0; i--){
        document.getElementById(`saveIdApproval${i}`)?.remove();
      }
      // ID保存初期化
      let saveApproval = document.getElementById("saveApproval");
      var sum = 0;
      // 取得情報を元にテーブルにセルを生成
      for(let i = 0; i < data.maximum_items; i++){
        // セルに挿入
        let row = tbodyElem.insertRow(-1);
        row.setAttribute("class","selectTableApproval")
        // ID保存用 input hidden 作成
        let newInput = document.createElement("input");
        newInput.setAttribute("id",`saveIdApproval${i + 1}`);
        newInput.setAttribute("class",'save_id');
        newInput.setAttribute("type","hidden");
        newInput.setAttribute("value",data.user_care_data[i].user_care_info_id);
        sum++;
        saveApproval.appendChild(newInput);

        let care_level_approval = row.insertCell(-1);
        care_level_approval.setAttribute("id","approvalHistoryId" + data.user_care_data[i].user_care_info_id);
        let certification_status_approval = row.insertCell(-1);
        let recognition_date_approval = row.insertCell(-1);
        let start_date_approval = row.insertCell(-1);
        let end_date_approval = row.insertCell(-1);
        let insurer_confirmation_date_approval = row.insertCell(-1);
        let qualification_date_approval = row.insertCell(-1);
        row.appendChild(newInput)
        row.addEventListener('click',this.clickHistory.bind(this))
        // 入力情報をセルに出力 classも追加する
        if(!(data.care_level_name?.[i]?.care_level_name == undefined)){
              care_level_approval.innerHTML = data.care_level_name[i].care_level_name;
              care_level_approval.classList.add('text_care_level_approval');
        }else{
          care_level_approval.innerHTML = "";
        }
        if(!(data.user_care_data?.[i]?.care_level_id === undefined)){
          // certification_statusの値変換
          let changeData = data.user_care_data[i].certification_status;
          if(changeData == 1){
            certification_status_approval.innerHTML = "申請中";
          }else if(changeData == 2){
            certification_status_approval.innerHTML = "認定済";
          }else{
            certification_status_approval.innerHTML = "";
          }
          certification_status_approval.classList.add('text_certification_status_approval');
        }else{
          certification_status_approval.innerHTML = "";
        }
        if(!(data.user_care_data?.[i]?.recognition_date == undefined)){
          // 日付のフォーマット変更
          let setData = data.user_care_data[i].recognition_date;
          let year = setData.substr(0,4);
          let month = setData.substring(5,7);
          let day = setData.substring(8,10);
          let yMD = year+"/"+month+"/"+day;
          recognition_date_approval.innerHTML = yMD;
          recognition_date_approval.classList.add('text_recognition_date_approval');
        }else{
          recognition_date_approval.innerHTML = "";
          recognition_date_approval.classList.add('text_recognition_date_approval');
        }
        if(!(data.user_care_data?.[i]?.care_period_start == undefined)){
          let setData = data.user_care_data[i].care_period_start;
          let year = setData.substr(0,4);
          let month = setData.substring(5,7);
          let day = setData.substring(8,10);
          let yMD = year+"/"+month+"/"+day;
          start_date_approval.innerHTML = yMD;
          start_date_approval.classList.add('text_start_date_approval');
        }else{
          start_date_approval.innerHTML = "";
          start_date_approval.classList.add('text_start_date_approval');
        }
        if(!(data.user_care_data?.[i]?.care_period_end == undefined)){
          let setData = data.user_care_data[i].care_period_end;
          let year = setData.substr(0,4);
          let month = setData.substring(5,7);
          let day = setData.substring(8,10);
          let yMD = year+"/"+month+"/"+day;

          end_date_approval.innerHTML = yMD;
          end_date_approval.classList.add('text_end_date_approval');
        }else{
          end_date_approval.innerHTML = "";
          end_date_approval.classList.add('text_end_date_approval');
        }
        if(!(data.user_care_data?.[i]?.date_confirmation_insurance_card == undefined)){
          let setData = data.user_care_data[i].date_confirmation_insurance_card;
          let year = setData.substr(0,4);
          let month = setData.substring(5,7);
          let day = setData.substring(8,10);
          let yMD = year+"/"+month+"/"+day;

          insurer_confirmation_date_approval.innerHTML = yMD;
          insurer_confirmation_date_approval.classList.add('text_confirmation_insurance_card_approval');
        }else{
          insurer_confirmation_date_approval.innerHTML = "";
          insurer_confirmation_date_approval.classList.add('text_confirmation_insurance_card_approval');
        }
        if(!(data.user_care_data?.[i]?.date_qualification == undefined)){
          let setData = data.user_care_data[i].date_qualification;
          let year = setData.substr(0,4);
          let month = setData.substring(5,7);
          let day = setData.substring(8,10);
          let yMD = year+"/"+month+"/"+day;

          qualification_date_approval.innerHTML = yMD;
          qualification_date_approval.classList.add('text_qualification_approval');
        }else{
          qualification_date_approval.innerHTML = "";
          qualification_date_approval.classList.add('text_qualification_approval');
        }
      }
      document.getElementById("saveIdNumMaxApproval").value = sum;
      this.resetDisable();
      // 開始日が最新のものをフォームにセット
      document.getElementById("saveGetIdApproval").value = data.user_care_data[0].user_care_info_id;
      let elements = document.getElementsByClassName("selectTableApproval");
      let recognitionDate = data.user_care_data[0].recognition_date;
      let carePeriodStart = data.user_care_data[0].care_period_start;
      let carePeriodEnd = data.user_care_data[0].care_period_end;
      let dateQualification = data.user_care_data[0].date_qualification;
      let dateCfmInsCard = data.user_care_data[0].date_confirmation_insurance_card;
      document.getElementById("select_list1_approval").value = data.care_level_name[0].care_level;
      document.getElementById("select_list2_approval").value = data.user_care_data[0].certification_status;
      document.getElementById("text_item1_approval").value = recognitionDate ? recognitionDate.replace(/-/g, '/') : null;
      document.getElementById("text_item2_approval").value = carePeriodStart ? carePeriodStart.replace(/-/g, '/') : null;
      document.getElementById("text_item3_approval").value = carePeriodEnd ? carePeriodEnd.replace(/-/g, '/') : null;
      document.getElementById("text_item4_approval").value = dateQualification ? dateQualification.replace(/-/g, '/') : null;
      document.getElementById("text_item5_approval").value = dateCfmInsCard ? dateCfmInsCard.replace(/-/g, '/') : null;
      document.getElementById("jaCalApprovalDate").innerText = JapaneseCalendar.toJacal(recognitionDate);
      document.getElementById("jaCalApprovalStartDate").innerText = JapaneseCalendar.toJacal(carePeriodStart);
      document.getElementById("jaCalApprovalEndDate").innerText = JapaneseCalendar.toJacal(carePeriodEnd);
      document.getElementById("jaCalDateCfmInsCard").innerText = JapaneseCalendar.toJacal(dateCfmInsCard);
      document.getElementById("jaCalDateQualification").innerText = JapaneseCalendar.toJacal(dateQualification);
      document.getElementById("newApprovalData").value = 0;
      this.inputChangeApproval();
      elements[0].style.backgroundColor ="#ffffee";
      let endDateBtn = document.querySelectorAll('.end_date_btn_approval')
      endDateBtn.forEach(e =>{ e.checked = false; });

      if (data.user_care_data[0].certification_status === 1) {
        this.applyingForApproval();
      }
      $("#overflow_approval").hide();
      $("#overflow_approval3").hide();
      $("#overflow_approval5").hide();
    }.bind(this))
    .fail(function(xhr){
      $("#overflow_approval").hide();
      $("#overflow_approval3").hide();
      $("#overflow_approval5").hide();
      if(xhr.status == 419){
        location.href = location.href;
      }
      this.validateDisplay(xhr.responseJSON)
    }.bind(this));
  }

  /**
   * 年数ボタン処理
   * 有効開始日からN年なら、有効開始日＋N年 -１日を有効終了日とする
   * @param {object} event
   */
  endDateBtn(event){
    var date = new Date(document.getElementById("text_item2_approval").value);
    var getDateY = (date.getFullYear());
    var getDateM = (date.getMonth() + 1);
    var getDateD = (date.getDate());
    var changeDate;
    // 月の部分だけ各ボタンに対応した数値に設定
    // 日にちが前日になる。
    switch(event.target.value){
      case "半年":
        changeDate = new Date(getDateY , getDateM + 5, getDateD - 1);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth() + 1)).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        break;
      case "１年":
        // 月日が 01/01の場合 もしくは 日付が 1日の場合
        if(getDateD == 1 && getDateM == 1 || getDateD == 1){
          changeDate = new Date(getDateY + 1, getDateM - 1, 0);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth() + 1)).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        }else{
          changeDate = new Date(getDateY + 1, getDateM, getDateD - 1);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth())).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        }
        break;
      case "３年":
        // 月日が 01/01の場合 もしくは 日付が 1日の場合
        if(getDateD == 1 && getDateM == 1 || getDateD == 1){
          changeDate = new Date(getDateY + 3, getDateM - 1, 0);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth() + 1)).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        }else{
          changeDate = new Date(getDateY + 3, getDateM, getDateD - 1);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth())).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        }
        break;
      case "４年":
        // 月日が 01/01の場合 もしくは 日付が 1日の場合
        if(getDateD == 1 && getDateM == 1 || getDateD == 1){
          changeDate = new Date(getDateY + 4, getDateM - 1, 0);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth() + 1)).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        }else{
          changeDate = new Date(getDateY + 4, getDateM, getDateD - 1);
          getDateY = (changeDate.getFullYear());
          getDateM = ("0"+(changeDate.getMonth())).slice(-2);
          getDateD = ("0"+(changeDate.getDate())).slice(-2);
        }
        break;
      default:break;
    }
    if(getDateM == '00'){
      getDateY -= 1;
      getDateM = 12
    }
    date = [getDateY, getDateM, getDateD].join('/');
    document.getElementById("jaCalApprovalEndDate").innerText = JapaneseCalendar.toJacal(date);
    document.getElementById("text_item3_approval").value = date;
  }

  /**
   * 利用開始日を基に終了日選択制限
   */
  inputChangeApproval(){
    let endDateBtn = document.querySelectorAll('.end_date_btn_approval')
    endDateBtn.forEach(e =>{ e.checked = false; });

    let date = new Date(document.getElementById("text_item2_approval").value);
    if(date != "Invalid Date"){
      date.setDate(date.getDate() + 1);
      date = [date.getFullYear(), ("0"+(date.getMonth() + 1)).slice(-2), ("0"+(date.getDate())).slice(-2)].join('-');
      document.getElementById("text_item3_approval").min = date;
      let endDateBtn = document.getElementsByClassName("end_date_btn_approval");
      for (let i = 0; i < endDateBtn.length; i++) {
        endDateBtn[i].disabled = false;
      }
    }else{
      // 年数ボタン非活性
      let endDateBtn = document.getElementsByClassName("end_date_btn_approval");
      for (let i = 0; i < endDateBtn.length; i++) {
        endDateBtn[i].disabled = true;
      }
    }
  }

  /**
   * 履歴選択
   */
  clickHistory(event){
    this.clearValidateDisplay();
    if(document.getElementById("tabApproval").value == 'true'){
      let elements = document.getElementsByClassName("selectTableApproval");
      for(let i=0; i<elements.length; i++){
        elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
      }

      event.target.parentNode.style.backgroundColor ="#ffffee";
      let saveId = event.target.parentNode.querySelector('.save_id').value
      document.getElementById("saveGetIdApproval").value = saveId;
      // ポップアップ画面に表示する情報準備
      let facilityUserId = document.getElementById("getFacilityIdApproval").value;
      document.getElementById("onBtnApproval").value = 1;

      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      // 権限チェックのために'facility_user_id'を追加
      $.ajax({
        url:'user_info/popup_updata_approval/ajax',//各自使用するＵＲＬに書き換える
        type:'POST',
        data:{'user_care_info_id': saveId, 'facility_user_id': facilityUserId},
      })
      .done(function(data){
        this.user_care_info_id = data.m_user_care_infomations[0].user_care_info_id;

        let option;
        let recognitionDate = data.m_user_care_infomations[0].recognition_date;
        let carePeriodStart = data.m_user_care_infomations[0].care_period_start;
        let carePeriodEnd = data.m_user_care_infomations[0].care_period_end;
        let dateQualification = data.m_user_care_infomations[0].date_qualification;
        let dateCfmInsCard = data.m_user_care_infomations[0].date_confirmation_insurance_card;
        this.resetDisable();
        // 入力欄を選択した情報に書き換える処理
        document.getElementById("select_list1_approval").value = data.care_level[0].care_level;
        document.getElementById("select_list2_approval").value = data.m_user_care_infomations[0].certification_status;
        document.getElementById("text_item1_approval").value = recognitionDate ? recognitionDate.replace(/-/g, '/') : null;
        document.getElementById("text_item2_approval").value = carePeriodStart ? carePeriodStart.replace(/-/g, '/') : null;
        document.getElementById("text_item3_approval").value = carePeriodEnd ? carePeriodEnd.replace(/-/g, '/') : null;
        document.getElementById("text_item4_approval").value = dateQualification ? dateQualification.replace(/-/g, '/') : null;
        document.getElementById("text_item5_approval").value = dateCfmInsCard ? dateCfmInsCard.replace(/-/g, '/') : null;
        document.getElementById("jaCalApprovalDate").innerText = JapaneseCalendar.toJacal(recognitionDate);
        document.getElementById("jaCalApprovalStartDate").innerText = JapaneseCalendar.toJacal(carePeriodStart);
        document.getElementById("jaCalApprovalEndDate").innerText = JapaneseCalendar.toJacal(carePeriodEnd);
        document.getElementById("jaCalDateCfmInsCard").innerText = JapaneseCalendar.toJacal(dateCfmInsCard);
        document.getElementById("jaCalDateQualification").innerText = JapaneseCalendar.toJacal(dateQualification);
        document.getElementById("newApprovalData").value = 0;//更新として準備
        this.inputChangeApproval();
        if (data.m_user_care_infomations[0].certification_status === 1) {
          this.applyingForApproval();
        }
      }.bind(this))
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
      });
      let endDateBtn = document.querySelectorAll('.end_date_btn_approval')
      endDateBtn.forEach(e =>{ e.checked = false; });
    }
  }

  // DBから情報取得しテーブルに情報を挿入
  tabCheckApproval(){
    if( document.getElementById("tabApproval").value == 'true'){
      this.clearValidateDisplay();
      $(function(){
        let getNum = document.getElementById('getFacilityIdApproval').value;
        $.ajaxSetup({
          headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
        });
        // 権限チェックのために"postData"を'facility_user_id'に改修
        $.ajax({
          url:'user_info/approval/ajax',
          type:'POST',
          data:{'facility_user_id':getNum},
        })
        .done(function(data){
          // テーブル情報クリア
          let tableElem = document.getElementById("table_approval");
          let tbodyElem = document.getElementById("table_tbody_approval");
          // テーブル情報の削除方法変更
          while( tableElem.rows[ 1 ] ){
            tableElem.deleteRow( 1 );
          }
          // 保存していたIDを削除 2021/08/23
          for(let i = document.getElementById("saveIdNumMaxApproval").value; i > 0; i--){
            document.getElementById(`saveIdApproval${i}`)?.remove();
          }
          // ID保存初期化
          let saveApproval = document.getElementById("saveApproval");
          var sum = 0;
          // 取得情報を元にテーブルにセルを生成
          for(let i = 0; i < data.maximum_items; i++){
            // セルに挿入
            let row = tbodyElem.insertRow(-1);
            row.setAttribute("class","selectTableApproval");
            // ID保存用 input hidden 作成
            let newInput = document.createElement("input");
            newInput.setAttribute("id",`saveIdApproval${i + 1}`);
            newInput.setAttribute("class",'save_id');
            newInput.setAttribute("type","hidden");
            newInput.setAttribute("value",data.user_care_data[i].user_care_info_id);
            sum++;
            saveApproval.appendChild(newInput);

            let care_level_approval = row.insertCell(-1);
            care_level_approval.setAttribute("id","approvalHistoryId" + data.user_care_data[i].user_care_info_id);
            let certification_status_approval = row.insertCell(-1);
            let recognition_date_approval = row.insertCell(-1);
            let start_date_approval = row.insertCell(-1);
            let end_date_approval = row.insertCell(-1);
            let insurer_confirmation_date_approval = row.insertCell(-1);
            let qualification_date_approval = row.insertCell(-1);
            row.appendChild(newInput)
            row.addEventListener('click',this.clickHistory.bind(this))

            // 入力情報をセルに出力 classも追加する
            if(!(data.care_level_name?.[i]?.care_level_name == undefined)){
              care_level_approval.innerHTML = data.care_level_name[i].care_level_name;
              care_level_approval.classList.add('text_care_level_approval');
            }else{
              care_level_approval.innerHTML = "";
            }
            if(!(data.user_care_data?.[i]?.certification_status === undefined)){
              // certification_statusの値変換
              let changeData = data.user_care_data[i].certification_status;
              if(changeData == 1){
                certification_status_approval.innerHTML = "申請中";
              }else if(changeData == 2){
                certification_status_approval.innerHTML = "認定済";
              }else{
                certification_status_approval.innerHTML = "";
              }
              certification_status_approval.classList.add('text_certification_status_approval');
            }else{
              certification_status_approval.innerHTML = "";
            }
            if(!(data.user_care_data?.[i]?.recognition_date == undefined)){
              // 日付のフォーマット変更
              let setData = data.user_care_data[i].recognition_date;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              recognition_date_approval.innerHTML = yMD;
              recognition_date_approval.classList.add('text_recognition_date_approval');
            }else{
              recognition_date_approval.innerHTML = "";
              recognition_date_approval.classList.add('text_recognition_date_approval');
            }
            if(!(data.user_care_data?.[i]?.care_period_start == undefined)){
              let setData = data.user_care_data[i].care_period_start;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              start_date_approval.innerHTML = yMD;
              start_date_approval.classList.add('text_start_date_approval');
            }else{
              start_date_approval.innerHTML = "";
              start_date_approval.classList.add('text_start_date_approval');
            }
            if(!(data.user_care_data?.[i]?.care_period_end == undefined)){
              let setData = data.user_care_data[i].care_period_end;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              end_date_approval.innerHTML = yMD;
              end_date_approval.classList.add('text_end_date_approval');
            }else{
              end_date_approval.innerHTML = "";
              end_date_approval.classList.add('text_end_date_approval');
            }
            if(!(data.user_care_data?.[i]?.date_confirmation_insurance_card == undefined)){
              let setData = data.user_care_data[i].date_confirmation_insurance_card;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              insurer_confirmation_date_approval.innerHTML = yMD;
              insurer_confirmation_date_approval.classList.add('text_confirmation_insurance_card_approval');
            }else{
              insurer_confirmation_date_approval.innerHTML = "";
              insurer_confirmation_date_approval.classList.add('text_confirmation_insurance_card_approval');
            }
            if(!(data.user_care_data?.[i]?.date_qualification == undefined)){
              let setData = data.user_care_data[i].date_qualification;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              qualification_date_approval.innerHTML = yMD;
              qualification_date_approval.classList.add('text_qualification_approval');
            }else{
              qualification_date_approval.innerHTML = "";
              qualification_date_approval.classList.add('text_qualification_approval');
            }
          }
          // ↓↓　初期選択処理　↓↓　━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
          var index = 0;
          let elements = document.getElementsByClassName("selectTableApproval");
          document.getElementById("saveIdNumMaxApproval").value = sum;
          for(let i=0; i<elements.length; i++){
            elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
          }
          if(elements.length != 0){
            elements[index].style.backgroundColor ="#ffffee";//選択した際に水色に代わる処理
          }else{
            // 履歴情報がない場合
            document.getElementById("select_list1_approval").options[0].selected = true;
            document.getElementById("select_list2_approval").options[0].selected = true;
            document.getElementById("text_item1_approval").value = "";
            document.getElementById("text_item2_approval").value = "";
            document.getElementById("text_item3_approval").value = "";
            document.getElementById("text_item4_approval").value = "";
            document.getElementById("text_item5_approval").value = "";
            document.getElementById("newApprovalData").value = 1;//新規登録として準備

            let jaCalAll = this.element.querySelectorAll('[id^="jaCal"]');
            jaCalAll.forEach(e =>{ e.innerHTML = ""; });

            return;
          }
          let row = elements[index];
          let cells = row.getElementsByTagName("td");
          index += 1;
          let saveId = document.getElementById(`saveIdApproval${index}`).value;
          document.getElementById("saveGetIdApproval").value = saveId;
          // 入力画面準備
          let facilityUserId = document.getElementById("getFacilityIdApproval").value;
          document.getElementById("onBtnApproval").value = 1;
          $.ajaxSetup({
            headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
          });
          $.ajax({
            url:'user_info/popup_updata_approval/ajax',//各自使用するＵＲＬに書き換える
            type:'POST',
            data:{'user_care_info_id': saveId, 'facility_user_id': facilityUserId},
          })
          .done(function(data){
            let option;
            let recognitionDate = data.m_user_care_infomations[0].recognition_date;
            let carePeriodStart = data.m_user_care_infomations[0].care_period_start;
            let carePeriodEnd = data.m_user_care_infomations[0].care_period_end;
            let dateQualification = data.m_user_care_infomations[0].date_qualification;
            let dateCfmInsCard = data.m_user_care_infomations[0].date_confirmation_insurance_card;
            this.resetDisable()
            // 入力フォームの情報挿入処理
            document.getElementById("select_list1_approval").value = data.care_level[0].care_level;
            document.getElementById("select_list2_approval").value = data.m_user_care_infomations[0].certification_status;
            document.getElementById("text_item1_approval").value = recognitionDate ? recognitionDate.replace(/-/g, '/') : null;
            document.getElementById("text_item2_approval").value = carePeriodStart ? carePeriodStart.replace(/-/g, '/') : null;
            document.getElementById("text_item3_approval").value = carePeriodEnd ? carePeriodEnd.replace(/-/g, '/') : null;
            document.getElementById("text_item4_approval").value = dateQualification ? dateQualification.replace(/-/g, '/') : null;
            document.getElementById("text_item5_approval").value = dateCfmInsCard ? dateCfmInsCard.replace(/-/g, '/') : null;
            document.getElementById("jaCalApprovalDate").innerText = JapaneseCalendar.toJacal(recognitionDate);
            document.getElementById("jaCalApprovalStartDate").innerText = JapaneseCalendar.toJacal(carePeriodStart);
            document.getElementById("jaCalApprovalEndDate").innerText = JapaneseCalendar.toJacal(carePeriodEnd);
            document.getElementById("jaCalDateCfmInsCard").innerText = JapaneseCalendar.toJacal(dateCfmInsCard);
            document.getElementById("jaCalDateQualification").innerText = JapaneseCalendar.toJacal(dateQualification);
            this.inputChangeApproval();

            if (data.m_user_care_infomations[0].certification_status === 1) {
              this.applyingForApproval();
            }
            if (document.getElementById("approvalHistoryId" + this.user_care_info_id)){
              document.getElementById("approvalHistoryId" + this.user_care_info_id).click();
            }
          }.bind(this))
          .fail(function(xhr){
            if(xhr.status == 419){
              location.href = location.href;
            }
          });
        }.bind(this))
        .fail(function(xhr){
          if(xhr.status == 419){
            location.href = location.href;
          }
        });
        document.getElementById("newApprovalData").value = 0;//更新として準備
        $("#overflow_approval").hide();//ポップアップ非表示にする処理
        // ↑↑　初期選択処理　↑↑　━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
      }.bind(this))
    }
  }

  /**
   * @param {bool} status 表示のブーリアン値
   */
  setActive(status){
    document.getElementById("tabApproval").value = status;
    if(status && document.getElementById("getFacilityIdApproval").value != ""){
      this.tabCheckApproval();//DBから情報取得しテーブルに情報を挿入
    }
  }

  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  setFacilityUser(user){
    //　左サイドバー利用者取得　
    let json = JSON.stringify(user);
    let getId = JSON.parse(json);
    document.getElementById("getFacilityIdApproval").value = getId.facilityUserID;

    user.facilityUserID ? $(".button_approval").show() : $(".button_approval").hide();//登録、編集ボタンを表示する処理

    if(document.getElementById("tabApproval").value == "true" && user.facilityUserID != null){
      this.tabCheckApproval();//DBから情報取得しテーブルに情報を挿入
    }else{
      this.formClear();
      document.getElementById("table_tbody_approval").textContent = null;
    }
  }

  async saveValuesCheck(params)
  {
      let res = await CustomAjax.get(
        'user_info/approval/values_check_result' + this.setCheckValues(params),
        {'X-CSRF-TOKEN':CSRF_TOKEN},
      );

      let data = await res.json();

      return data;
  }

  setCheckValues(params)
  {
    return '?care_level='
      + params['care_level']
      + '&certification_status='
      + params['certification_status']
      + '&recognition_date='
      + params['recognition_date']
      + '&start_date='
      + params['start_date']
      + '&end_date='
      + params['end_date']
      + '&facility_user_id='
      + params['facility_user_id']
      + '&save_id_approval='
      + params['save_id_approval']
  }

  /**
   * 利用者一覧の取得関数を設定
   * @param {Function} callBack 通知先として呼ぶコールバック関数
   * @returns {void}}
   */
   addFacilityUserTableSyncServer(callBack){
    this.facilityUserTableSyncServer = callBack;
  }
}
