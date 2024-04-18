import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import JapaneseCalendar from '../../lib/japanese_calendar.js';

export default class Service{
  constructor(){
    this.elementID = 'tm_contents_service';
    this.element = document.getElementById(this.elementID);
    this.validationDisplayArea = document.getElementById("validateErrorsService");

    document.getElementById('select_list1_service').addEventListener('change',this.selectChange.bind(this))

    document.getElementById('clearBtn_service').addEventListener('click',this.submitService.bind(this));
    document.getElementById('js-updata-popup_service').addEventListener('click',this.submitService.bind(this));
    document.getElementById('updatabtn_service').addEventListener('click',this.submitService.bind(this));
    document.getElementById('cancelbtn_service').addEventListener('click',this.submitService.bind(this));

    document.getElementById('text_item1_service').addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));
    document.getElementById('text_item2_service').addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));
  }

  tabCheckService(){
    if(document.getElementById("tabService").value == 'true'){
      this.clearValidateDisplay();
      $(function(){
        let getNum = document.getElementById('getFacilityIdService').value;

        $.ajaxSetup({
          headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
        });
        // 権限チェックのために"postData"を'facility_user_id'に改修
        $.ajax({
          url:'user_info/service/ajax',
          type:'POST',
          data:{'facility_user_id':getNum},
        })
        .done(function(data){
          // テーブル情報クリア
          let tableElem = document.getElementById("table_service");
          let tbodyElem = document.getElementById("table_tbody_service");
          // テーブル情報の削除
          while( tableElem.rows[ 1 ] ){
            tableElem.deleteRow( 1 );
          }
          for(let i = document.getElementById("saveIdNumMaxService").value; i > 0; i--){
            document.getElementById(`saveIdService${i}`)?.remove();
          }
          // ID保存初期化
          let saveService = document.getElementById("saveService");
          var sum = 0;
          // 取得情報を元にテーブルにセルを生成
          for(let i = 0; i < data.maximum_items; i++){
            // セルに挿入
            let row = tbodyElem.insertRow(-1);
            row.setAttribute("id","selectTdService");
            row.setAttribute("class","selectTableService")
            // ID保存用 input hidden 作成
            let newInput = document.createElement("input");
            newInput.setAttribute("id",`saveIdService${i + 1}`);
            newInput.setAttribute("class","save_service_id");
            newInput.setAttribute("type","hidden");
            newInput.setAttribute("value",data.facility_infomations[i].user_facility_service_information_id);
            sum++;
            saveService.appendChild(newInput);

            let facility_name_service = row.insertCell(-1);
            let service_type_name_service = row.insertCell(-1);
            let usage_situation_service = row.insertCell(-1);
            let start_date_service = row.insertCell(-1);
            let end_date_service = row.insertCell(-1);
            row.appendChild(newInput)
            row.addEventListener('click',this.clickTableTbodyService.bind(this))

            // 入力情報をセルに出力 classも追加する
            if(!(data.facility_name_kanji?.[i]?.facility_name_kanji == undefined)){
              facility_name_service.innerHTML = data.facility_name_kanji[i].facility_name_kanji;
              facility_name_service.classList.add('text_data_facility_name_service');
            }else{
              facility_name_service.innerHTML = "";
              facility_name_service.classList.add('text_data_facility_name_service');
            }
            if(!(data.service_type_name?.[i]?.service_type_name == undefined)){
              service_type_name_service.innerHTML = data.service_type_name[i].service_type_name;
              service_type_name_service.classList.add('text_data_service_type_name_service');
            }else{
              service_type_name_service.innerHTML = "";
              service_type_name_service.classList.add('text_data_service_type_name_service');
            }
            if(!(data.facility_infomations?.[i]?.usage_situation == undefined)){
              // 利用状況の値から文字に変換
              let changeData = data.facility_infomations[i].usage_situation;
              if(changeData == 1){
                usage_situation_service.innerHTML = '利用中';
              }else if(changeData == 2){
                usage_situation_service.innerHTML = '未利用';
              }else{
                usage_situation_service.innerHTML = "";
              }
              usage_situation_service.classList.add('text_data_usage_situation_service');
            }else{
              usage_situation_service.innerHTML = "";
              usage_situation_service.classList.add('text_data_usage_situation_service');
            }
            if(!(data.facility_infomations?.[i]?.use_start == undefined)){
              // 日付のフォーマット変更
              let setData = data.facility_infomations[i].use_start;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              start_date_service.innerHTML = yMD;
              start_date_service.classList.add('text_data_start_date_service');
            }else{
              start_date_service.innerHTML = "";
              start_date_service.classList.add('text_data_start_date_service');
            }
            if(!(data.facility_infomations?.[i]?.use_end == undefined)){
              // 日付のフォーマット変更
              let setData = data.facility_infomations[i].use_end;
              let year = setData.substr(0,4);
              let month = setData.substring(5,7);
              let day = setData.substring(8,10);
              let yMD = year+"/"+month+"/"+day;
              end_date_service.innerHTML = yMD;
              end_date_service.classList.add('text_data_end_date_service');
            }else{
              end_date_service.innerHTML = "";
              end_date_service.classList.add('text_data_end_date_service');
            }
          }
          // ↓↓　初期選択処理　↓↓　━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            var index = 0;
            let elements = document.getElementsByClassName("selectTableService");
            // 事業所名、サービス種類プルダウンメニュー初期化
            let selectlist1 = document.getElementById("select_list1_service");
            let selectlist2 = document.getElementById("select_list2_service");
            for( let i = selectlist1.length; i > 0 ; i--){
              selectlist1.remove(i);
            }
            for( let i = selectlist2.length; i > 0 ; i--){
              selectlist2.remove(i);
            }
            for(let i=0; i<elements.length; i++){
              elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
            }
            // 履歴情報が１つ以上ある場合
            if(elements.length != 0){
              elements[index].style.backgroundColor ="#ffffee";//選択した際に水色に代わる処理
              var row = elements[index];
              var cells = row.getElementsByTagName("td");
              index += 1;
              var saveId = document.getElementById(`saveIdService${index}`).value;
              document.getElementById("saveGetIdService").value = saveId;
              var getIdNum = document.getElementById("saveGetIdService").value;
              document.getElementById("onBtnService").value = 1;
            }else{
              // 履歴情報がない場合
              this.clearForms();
              var getIdNum = 0;
            }
            let getFacilityIdNum = document.getElementById('getFacilityIdService').value;
            $.ajaxSetup({
              headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
            });
             // 権限チェックのために"postData1"を'facility_user_id'に改修
            $.ajax({
              url:'user_info/popup_updata_service/ajax',
              type:'POST',
              data:{'facility_user_id':getFacilityIdNum,'postData2':getIdNum},
            })
            .done(function(data){
              let option;
              if(data.facility_infomations != 0){
                var getFacilityId = data.facility_infomations[0].facility_id;
                var getServiceTypeCode = data.service_type_name[0].service_type_code;
                var getServiceTypeCodeId = data.service_type_name[0].service_type_code_id;
              }
              // 事業所名、サービス種類プルダウンメニュー追加
              for( let i = 0; i < data.maximum_facility_data ; i++ ){
                selectlist1 = document.getElementById("select_list1_service");
                option = document.createElement("option");
                option.text = data.facility_data[i].facility_name_kanji;
                option.value = data.facility_data[i].facility_id;
                selectlist1.appendChild(option).classList.add('option_service');
              }
              if(data.facility_infomations != 0){
                for( let i = 0; i < data.maximum_service_type_code ; i++ ){
                  selectlist2 = document.getElementById("select_list2_service");
                  option = document.createElement("option");
                  option.text = data.service_type_name[i].service_type_code + "：" + data.service_type_name[i].service_type_name;
                  option.value = data.service_type_name[i].service_type_code_id;
                  selectlist2.appendChild(option).classList.add('option_service');
                }
                let selectlist3 = document.getElementById("select_list3_service");
                selectlist1.value = getFacilityId;
                selectlist2.value = data.service_type_code_id.service_type_code_id;
                selectlist3.value = data.facility_infomations[0].usage_situation;

                // 初期表示のフォーマット変更
                let item1 = data.facility_infomations[0].use_start;
                let num =  item1.replace(/[^0-9]/g, '');
                if(num.length == 8){
                  var y = num.substr(0,4);
                  var m = num.substr(4,2);
                  var d = num.substr(6,2);
                  item1 = y + "/" + m + "/" + d;
                }

                let item2 = data.facility_infomations[0].use_end;
                let num2 =  item2.replace(/[^0-9]/g, '');
                if(num2.length == 8){
                  var y = num2.substr(0,4);
                  var m = num2.substr(4,2);
                  var d = num2.substr(6,2);
                  item2 = y + "/" + m + "/" + d;
                }

                document.getElementById("jaCalSeStartDate").innerText = JapaneseCalendar.toJacal(item1);
                document.getElementById("jaCalSeEndDate").innerText = JapaneseCalendar.toJacal(item2);

                document.getElementById("text_item1_service").value = item1;
                document.getElementById("text_item2_service").value = item2;

                document.getElementById("save_select_list1_service").value = document.getElementById("select_list1_service").value;
                document.getElementById("save_select_list2_service").value = document.getElementById("select_list2_service").value;
                document.getElementById("save_select_list3_service").value = document.getElementById("select_list3_service").value;
                document.getElementById("save_text_item1_service").value = document.getElementById("text_item1_service").value;
                document.getElementById("save_text_item2_service").value = document.getElementById("text_item2_service").value;
                document.getElementById("newServiceData").value = 0;//更新として準備
              }
              this.inputChangeService();
            }.bind(this))
            .fail(function(xhr){
                if(xhr.status == 419){
                location.href = location.href;
                }
            });
          document.getElementById("saveIdNumMaxService").value = sum;
        }.bind(this))
        .fail(function(xhr){
          if(xhr.status == 419){
            location.href = location.href;
          }
          //DBが空の状態でエラーが出た場合
        });
        let scroll = document.getElementById('table_tbody_service');
        scroll.scrollTop = 0;
        $("#overflow_service").hide();//ポップアップ非表示にする処理
      }.bind(this))
    }
  }

    clearForms()
    {
        document.getElementById("select_list1_service").options[0].selected = true;
        document.getElementById("select_list2_service").options[0].selected = true;
        document.getElementById("select_list3_service").options[0].selected = true;
        document.getElementById("text_item1_service").value = "";
        document.getElementById("text_item2_service").value = "";
        document.getElementById("jaCalSeStartDate").innerText = "";
        document.getElementById("jaCalSeEndDate").innerText = "";
        document.getElementById("newServiceData").value = 1;//新規登録として準備
    }

    clearSelectTableService()
    {
        let elements = document.getElementsByClassName("selectTableService");
        for(let i=0; i<elements.length; i++){
            elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
        }
    }

    async submitService(event)
    {
        this.clearValidateDisplay()        
        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
        let submitBtn = event.target.id
        let value1 = document.getElementById("select_list1_service").value;
        let value2 = document.getElementById("select_list2_service").value;
        let value3 = document.getElementById("select_list3_service").value;
        // 日付フォーマットを戻す
        let value4 = document.getElementById("text_item1_service").value;
        // let num =  value4.replace(/[^0-9]/g, '');
        // if(num.length == 8){
        //   var y = num.substr(0,4);
        //   var m = num.substr(4,2);
        //   var d = num.substr(6,2);
        //   value4 = y + "-" + m + "-" + d;
        // }

        let value5 = document.getElementById("text_item2_service").value;
        // let num2 =  value5.replace(/[^0-9]/g, '');
        // if(num2.length == 8){
        //   var y = num2.substr(0,4);
        //   var m = num2.substr(4,2);
        //   var d = num2.substr(6,2);
        //   value5 = y + "-" + m + "-" + d;
        // }
        
        if( submitBtn == "clearBtn_service"){
            // 新規登録押下処理
            this.clearForms();
            this.clearSelectTableService();
            let facilityUserId = document.getElementById("getFacilityIdService").value;

            $.ajaxSetup({
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
            });
            // 入居日を取得
            await $.ajax({
                url:'user_info/service/start_date',
                type:'GET',
                data:{'facility_user_id':facilityUserId}
            })
            .done(function(data){
                // 新規登録時のフォーマット変換
                let num =  data.replace(/[^0-9]/g, '');
                if(num.length == 8){
                  var y = num.substr(0,4);
                  var m = num.substr(4,2);
                  var d = num.substr(6,2);
                  data = y + "/" + m + "/" + d;
                }
                // 入居日を利用開始日に設定
                document.getElementById("text_item1_service").value = data;
                document.getElementById("jaCalSeStartDate").innerText = JapaneseCalendar.toJacal(data);

            })
            .fail(function(xhr){
                if(xhr.status == 419){
                    location.href = location.href;
                }
            })
            return;
        }else if( submitBtn == "js-updata-popup_service"){
            // 保存ボタン押下処理
            if(document.getElementById("newServiceData").value == 0){
                $("#overflow_service").show();
                return;
            }
        }else if( submitBtn == "cancelbtn_service"){
            // キャンセルボタン処理
            $("#overflow_service").hide();
            return;
        }
        let value7 = document.getElementById("saveGetIdService").value;
        if(document.getElementById("newServiceData").value == 1){
            value7 = 0;
        }

        // 利用終了日が空だったら固定の日付を登録する
        if (value5 == "") {
            value5 = '2024/03/31'
            document.getElementById("jaCalSeEndDate").innerText = JapaneseCalendar.toJacal(value5);
        }

        let value6 = document.getElementById("getFacilityIdService").value;

        //↓↓　DB登録処理　↓↓----------------------------------------------------------------------------------------
        $.ajaxSetup({
            headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
        });
        // 権限チェックのために"facilityId"を'facility_id'に、"facilityUserId"を'facility_user_id'改修
        $.ajax({
            url:'user_info/service/store',
            type:'POST',
            data:{
                'facility_id':value1,
                'serviceTypeCodeId':value2,
                'usageSituation':value3,
                'useStart':value4,
                'useEnd':value5,
                'facility_user_id':value6,
                'saveGetIdService':value7,
            },
        })
        .done(function(data){
            // テーブル情報クリア
            let tableElem = document.getElementById("table_service");
            let tbodyElem = document.getElementById("table_tbody_service");
            // テーブル情報の削除
            while( tableElem.rows[ 1 ] ){
                tableElem.deleteRow( 1 );
            }
            // 保存していたIDを削除
            for(let i = document.getElementById("saveIdNumMaxService").value; i > 0; i--){
                document.getElementById(`saveIdService${i}`)?.remove();
            }
            // ID保存初期化
            let saveService = document.getElementById("saveService");
            var sum = 0;
            // 取得情報を元にテーブルにセルを生成
            for(let i = 0; i < data.maximum_items; i++){
                // セルに挿入
                let row = tbodyElem.insertRow(-1);
                row.setAttribute("id","selectTdService");
                row.setAttribute("class","selectTableService")
                // ID保存用 input hidden 作成
                let newInput = document.createElement("input");
                newInput.setAttribute("id",`saveIdService${i + 1}`);
                newInput.setAttribute("class","save_service_id");
                newInput.setAttribute("type","hidden");
                newInput.setAttribute("value",data.facility_infomations[i].user_facility_service_information_id);
                sum++;
                saveService.appendChild(newInput);

                let facility_name_service = row.insertCell(-1);
                let service_type_name_service = row.insertCell(-1);
                let usage_situation_service = row.insertCell(-1);
                let start_date_service = row.insertCell(-1);
                let end_date_service = row.insertCell(-1);
                row.appendChild(newInput)
                row.addEventListener('click',this.clickTableTbodyService.bind(this))
                // 入力情報をセルに出力 classも追加する
                if(!(data.facility_name_kanji?.[i]?.facility_name_kanji == undefined)){
                    facility_name_service.innerHTML = data.facility_name_kanji[i].facility_name_kanji;
                    facility_name_service.classList.add('text_data_facility_name_service');
                }else{
                    facility_name_service.innerHTML = "";
                    facility_name_service.classList.add('text_data_facility_name_service');
                }
                if(!(data.service_type_name?.[i]?.service_type_name == undefined)){
                    service_type_name_service.innerHTML = data.service_type_name[i].service_type_name;
                    service_type_name_service.classList.add('text_data_left_service');
                    service_type_name_service.classList.add('text_data_service_type_name_service');
                }else{
                    service_type_name_service.innerHTML = "";
                    service_type_name_service.classList.add('text_data_service_type_name_service');
                }
                if(!(data.facility_infomations?.[i]?.usage_situation == undefined)){
                    // 利用状況の値から文字に変換
                    let changeData = data.facility_infomations[i].usage_situation;
                    if(changeData == 1){
                        usage_situation_service.innerHTML = '利用中';
                    }else if(changeData == 2){
                        usage_situation_service.innerHTML = '未利用';
                    }else{
                        usage_situation_service.innerHTML = "";
                    }
                    usage_situation_service.classList.add('text_data_usage_situation_service');
                    }else{
                        usage_situation_service.innerHTML = "";
                        usage_situation_service.classList.add('text_data_usage_situation_service');
                    }
                if(!(data.facility_infomations?.[i]?.use_start == undefined)){
                    // ↓↓日付のフォーマット変更↓↓
                    let setData = data.facility_infomations[i].use_start;
                    let year = setData.substr(0,4);
                    let month = setData.substring(5,7);
                    let day = setData.substring(8,10);
                    let yMD = year+"/"+month+"/"+day;
                    // ↑↑日付のフォーマット変更↑↑
                    start_date_service.innerHTML = yMD;
                    start_date_service.classList.add('text_data_start_date_service');
                }else{
                    start_date_service.innerHTML = "";
                    start_date_service.classList.add('text_data_start_date_service');
                }
                if(!(data.facility_infomations?.[i]?.use_end == undefined)){
                    // ↓↓日付のフォーマット変更↓↓
                    let setData = data.facility_infomations[i].use_end;
                    let year = setData.substr(0,4);
                    let month = setData.substring(5,7);
                    let day = setData.substring(8,10);
                    let yMD = year+"/"+month+"/"+day;
                    // ↑↑日付のフォーマット変更↑↑
                    end_date_service.innerHTML = yMD;
                    end_date_service.classList.add('text_data_end_date_service');
                }else{
                    end_date_service.innerHTML = "";
                    end_date_service.classList.add('text_data_end_date_service');
                }
            }
            this.clearSelectTableService();
            if(data.facility_infomations.length == 0) {
                this.clearForms();
            } else {
                let elements = document.getElementsByClassName("selectTableService");
                document.getElementById("saveIdNumMaxService").value = sum;
                document.getElementById("saveGetIdService").value = data.facility_infomations[0].user_facility_service_information_id;
                // 開始日が最新の情報をフォームにセット
                document.getElementById("select_list1_service").value = data.facility_infomations[0].facility_id;
                document.getElementById("select_list2_service").value = data.service_type_code_id.service_type_code_id;
                document.getElementById("select_list3_service").value = data.facility_infomations[0].usage_situation;
                // 最新の情報のフォーマット変更
                let item1 = data.facility_infomations[0].use_start;
                let num =  item1.replace(/[^0-9]/g, '');
                if(num.length == 8){
                  var y = num.substr(0,4);
                  var m = num.substr(4,2);
                  var d = num.substr(6,2);
                  item1 = y + "/" + m + "/" + d;
                }

                let item2 = data.facility_infomations[0].use_end;
                let num2 =  item2.replace(/[^0-9]/g, '');
                if(num2.length == 8){
                  var y = num2.substr(0,4);
                  var m = num2.substr(4,2);
                  var d = num2.substr(6,2);
                  item2 = y + "/" + m + "/" + d;
                }
                document.getElementById("text_item1_service").value = item1;
                document.getElementById("text_item2_service").value = item2;
                document.getElementById("newServiceData").value = 0;
                elements[0].style.backgroundColor ="#ffffee";
                document.getElementById('table_tbody_service').scrollTop = 0;
                document.getElementById("save_select_list1_service").value = document.getElementById("select_list1_service").value;
                document.getElementById("save_select_list2_service").value = document.getElementById("select_list2_service").value;
                document.getElementById("save_select_list3_service").value = document.getElementById("select_list3_service").value;
                document.getElementById("save_text_item1_service").value = document.getElementById("text_item1_service").value;
                document.getElementById("save_text_item2_service").value = document.getElementById("text_item2_service").value;
                this.inputChangeService();
            }
            $("#overflow_service").hide();
        }.bind(this))
        .fail(function(xhr){
            if(xhr.status == 419){
                location.href = location.href;
            }
            document.getElementById('overflow_service').style.display = 'none'
            this.validateDisplay(xhr.responseJSON)
        }.bind(this));
    }

    /**
     * 事業所に紐づくサービスを取得
     */
    selectChange()
    {
        let getSelectNum = document.getElementById("select_list1_service").value;
        this.getFaciltyServiceInformation(getSelectNum);
    }

    /**
     * フォームの事業所名・サービス種類を更新する
     */
    async getFaciltyServiceInformation(facilityId)
    {
        $.ajaxSetup({
            headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
        });
        // 権限チェックのために"postData"を'facility_id'改修
        await $.ajax({
            url:'user_info/popup_facility_service/ajax',
            type:'POST',
            data:{'facility_id':facilityId},
        })
        .done(function(data){
            let option;
            let selectlist2;
            // 事業所名、サービス種類プルダウンメニュー初期化
            selectlist2 = document.getElementById("select_list2_service");
            for( let i = selectlist2.length; i > 0 ; i--){
                selectlist2.remove(i);
            }
            selectlist2.options[0].selected = true;
            document.getElementById("service_code_change_service").value = 1;//サービスコードが変更された場合 1を代入
            for( let i = 0; i < data.maximum_items ; i++ ){
                selectlist2 = document.getElementById("select_list2_service");
                option = document.createElement("option");
                option.text = data.service_type_name[i].service_type_code + "：" + data.service_type_name[i].service_type_name;
                option.value = data.service_type_name[i].service_type_code_id;
                selectlist2.appendChild(option).classList.add('option_service');
            }
        })
        .fail(function(xhr){
            if(xhr.status == 419){
                location.href = location.href;
            }
        });
    }

    /**
     * 選択された履歴情報を取得する
     */
    async clickTableTbodyService(event)
    {
        this.clearValidateDisplay();
        if(document.getElementById("tabService").value == 'true'){
            let elements = document.getElementsByClassName("selectTableService");
            for(let i=0; i<elements.length; i++){
                elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
            }
            event.target.parentNode.style.backgroundColor ="#ffffee";//選択した際に水色に代わる処理
            let saveId = event.target.parentNode.querySelector('.save_service_id').value
            document.getElementById("saveGetIdService").value = saveId;

            // 事業所名、サービス種類呼び出し準備
            let getFacilityIdNum = document.getElementById('getFacilityIdService').value;
            let getIdNum = document.getElementById("saveGetIdService").value;
            document.getElementById("onBtnService").value = 1;

            CustomAjax.send(
                'get',
                'user_info/service/hisotry_service_info?facility_user_id='+ getFacilityIdNum + '&user_facility_service_information_id='+ getIdNum,
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                [],
                'setServiceInformation',
                this
            );
        }
    }

    /**
     * フォームにデータをセットする
     * @param {object} data
     */
    async setServiceInformation(data)
    {
        await this.getFaciltyServiceInformation(data.facility_id)
        let selectlist1 = document.getElementById("select_list1_service");
        let selectlist2 = document.getElementById("select_list2_service");
        let selectlist3 = document.getElementById("select_list3_service");

        selectlist1.value = data.facility_id;
        selectlist2.value = data.service_type_code_id;
        selectlist3.value = data.usage_situation;

        let StartDate = data.use_start.replace(/-/g, '/');
        document.getElementById("text_item1_service").value = StartDate;
        document.getElementById("jaCalSeStartDate").innerText = JapaneseCalendar.toJacal(StartDate);
        let EndDate = data.use_end.replace(/-/g, '/');
        document.getElementById("text_item2_service").value = EndDate;
        document.getElementById("jaCalSeEndDate").innerText = JapaneseCalendar.toJacal(EndDate);

        document.getElementById("save_select_list1_service").value = document.getElementById("select_list1_service").value;
        document.getElementById("save_select_list2_service").value = document.getElementById("select_list2_service").value;
        document.getElementById("save_select_list3_service").value = document.getElementById("select_list3_service").value;
        document.getElementById("save_text_item1_service").value = document.getElementById("text_item1_service").value;
        document.getElementById("save_text_item2_service").value = document.getElementById("text_item2_service").value;
        document.getElementById("newServiceData").value = 0;//更新として準備
        this.inputChangeService();
    }

    /**
     * 利用終了日のカレンダー制御
     */
    inputChangeService()
    {
        let date = new Date(document.getElementById("text_item1_service").value);
        date.setDate(date.getDate() + 1);
        date = [date.getFullYear(), ("0"+(date.getMonth() + 1)).slice(-2), ("0"+(date.getDate())).slice(-2)].join('-');
        document.getElementById("text_item2_service").min = date;
    }

    validateDisplay(errorBody)
    {
        let createRow = (function(key, value){
        let record = document.createElement('li');
        let validationDisplayArea = document.getElementById("validateErrorsService");
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

    /**
     * @param {bool} status 表示のブーリアン値
     */
    setActive(status){
        document.getElementById("tabService").value = status;
        if(status && document.getElementById("getFacilityIdService").value != ""){
          this.tabCheckService();
        }
    }

    /**
     * @param {Object} user {facilityUserID: string, userName: string}
     */
    setFacilityUser(user){
        let json = JSON.stringify(user);
        let getId = JSON.parse(json);
        document.getElementById("getFacilityIdService").value = getId.facilityUserID;

        user.facilityUserID ? $(".button_service").show() : $(".button_service").hide();

        if(document.getElementById("tabService").value == "true" && user.facilityUserID != null){
          this.tabCheckService();
        }else{
          this.clearForms();
          document.getElementById("table_tbody_service").textContent = null;
        }
    }
}
