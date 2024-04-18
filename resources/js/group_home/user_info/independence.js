import JapaneseCalendar from '../../lib/japanese_calendar.js';
import CustomAjax from '../../lib/custom_ajax.js'

/**
 * 自立度タブで閲覧できるビュー
 * @author ttakenaka
 */

 export default class Independence{
  constructor(){
    this.elementID = 'tm_contents_independence';
    this.element = document.getElementById(this.elementID);
    this.validationDisplayArea = document.getElementById("validateErrorsIndependence");

    if (document.getElementById('clearBtn_independence') !== null){
      document.getElementById('clearBtn_independence').addEventListener('click',this.submitIndependence.bind(this))
    }
    if (document.getElementById('js-updata-popup_independence') !== null){
      document.getElementById('js-updata-popup_independence').addEventListener('click',this.submitIndependence.bind(this))
    }
    document.getElementById('updatabtn_independence').addEventListener('click',this.submitIndependence.bind(this))
    document.getElementById('cancelbtn_independence').addEventListener('click',this.submitIndependence.bind(this))

    document.getElementById('text_item1_independence').addEventListener('input',JapaneseCalendar.inputChangeJaCal.bind(this));
  }

  formClear(){
    document.getElementById("select_list1_independence").options[0].selected = true;
    document.getElementById("select_list2_independence").options[0].selected = true;
    document.getElementById("text_item1_independence").value = "";
    document.getElementById("text_item2_independence").value = "";
    document.getElementById("jaCalInStartDate").innerText = "";
    document.getElementById("newIndependenceData").value = 1;//新規登録として準備
    let elements = document.getElementsByClassName("selectTableIndependence");
    for(let i=0; i<elements.length; i++){
      elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
    }
  }

  async submitIndependence(event){
    //変更フラグをリセット
    document.getElementById("changed_flg").value = false;
    let submitBtn = event.target.id
    let value6;
    this.clearValidateDisplay();
    if( submitBtn == "clearBtn_independence"){
      // 新規登録押下処理
      this.formClear();
      return;
    }else if( submitBtn == "js-updata-popup_independence"){
      // 保存押下処理
      let confirmFlag = false;
      // 判断日によってダイアログを表示するか判断する
      if ($("#text_item1_independence").length > 0) {
        // 判断日をYYYY/MM/DDの形式からYYYYMMDDの形式に変換
        let judgmentDateArray = $("#text_item1_independence").val().split("/");
        if (judgmentDateArray.length >= 3) {
          let judgmentDateStr = 
            judgmentDateArray[0] +
            ("00" + judgmentDateArray[1]).slice(-2) +
            ("00" + judgmentDateArray[2]).slice(-2);
          let judgmentDate = parseInt(judgmentDateStr);
          if (!Number.isNaN(judgmentDate)) {
            // 非同期処理で登録済みの自立度情報を取得する
            let getNum = $('#getFacilityIdIndependence').val();
            let data = await CustomAjax.post(
              'user_info/independence/ajax',
              {'Content-Type':'application/json','X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
              {'facility_user_id':getNum}
            );
            // 取得した自立度情報をもとに判定処理を行う
            for (let i = 0; i < data.maximum_items; i++) {
              // 選択している自立度情報以外で比較する
              if ($("#newIndependenceData").val() == 1 ||
                $("#saveGetIdIndependence").val() != data.independence_infomations[i].user_independence_informations_id) {
                // 登録済みの判断日をYYYYMMDDの形式に変換
                let registeredJudgmentDateStr = data.independence_infomations[i].judgment_date;
                let registeredJudgmentDate = parseInt(
                  registeredJudgmentDateStr.substr(0,4) +
                  registeredJudgmentDateStr.substring(5,7) +
                  registeredJudgmentDateStr.substring(8,10)
                );
                // 入力した判断日と登録済みの判断日を比較
                if(!Number.isNaN(registeredJudgmentDate) && judgmentDate < registeredJudgmentDate){
                  // 入力した判断日が登録済みの判断日より過去の場合、ダイアログで確認する
                  confirmFlag = true;
                }
              }
            }
          }
        }
      }
      if (confirmFlag) {
        // 登録済みの判断日より過去の判断日を設定した場合、ダイアログで確認する
        $("#popup_confirm_message").html("前回の判断日より前の<br/>日付が入力されていますが<br/>保存しますか？");
        $("#overflow_independence").show();
        return;
      } else if($("#newIndependenceData").val() == 0) {
        // 更新処理の場合、ダイアログで確認する
        $("#popup_confirm_message").html("変更した内容を更新しますか？");
        $("#overflow_independence").show();
        return;
      }

    }else if( submitBtn == "updatabtn_independence"){
      // 更新ポップアップ処理
    }else if( submitBtn == "cancelbtn_independence"){
      $("#overflow_independence").hide();
      return;
    }
    if(document.getElementById("newIndependenceData").value == 1){
      value6 = 0;
    }else{
      value6 = document.getElementById("saveGetIdIndependence").value;
    }
    let select_list1_independence = document.getElementById("select_list1_independence");
    let value1 = select_list1_independence.value;
    let select_list2_independence = document.getElementById("select_list2_independence");
    let value2 = select_list2_independence.value;
    let text_item1_independence = document.getElementById("text_item1_independence");
    let value3 = text_item1_independence.value;
    let text_item2_independence = document.getElementById("text_item2_independence");
    let value4 = text_item2_independence.value;
    let value5 = document.getElementById("getFacilityIdIndependence").value;
    //↓↓　DB登録処理　↓↓----------------------------------------------------------------------------------------
    $.ajaxSetup({
      headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
    });
    // 権限チェックのために"facilityId"だけ'facility_user_id'に改修
    $.ajax({
      url:'user_info/independence/store',
      type:'POST',
      data:{'independentIndependence':value1,
            'dementiaIndependence':value2,
            'judgmentDateIndependence':value3,
            'judgeIndependence':value4,
            'facility_user_id':value5,
            'saveGetIdIndependence':value6},
    })
    .done(function(data){
      // テーブル情報クリア
      let tableElem = document.getElementById("table_independence");
      let tbodyElem = document.getElementById("table_tbody_independence");
      // ↓↓テーブル情報の削除方法変更↓↓　■修正箇所
      while( tableElem.rows[ 1 ] ){
        tableElem.deleteRow( 1 );
      }
      // ↑↑テーブル情報の削除方法変更↑↑　■修正箇所
      // 保存していたIDを削除 2021/08/23
      for(let i = document.getElementById("saveIdNumMaxIndependence").value; i > 0; i--){
        document.getElementById(`saveIdIndependence${i}`)?.remove();
      }
      // ID保存初期化
      let saveIndependence = document.getElementById("saveIndependence");
      var sum = 0;
      // 取得情報を元にテーブルにセルを生成
      for(let i = 0; i < data.maximum_items; i++){
      // セルに挿入
      let row = tbodyElem.insertRow(-1);
      row.setAttribute("id","selectIdIndependence");
      row.setAttribute("class","selectTableIndependence");
      row.setAttribute("dusk",`selectIdIndependence${i + 1}`);
      // ID保存用 input hidden 作成
      let newInput = document.createElement("input");
      newInput.setAttribute("id",`saveIdIndependence${i + 1}`);
      newInput.setAttribute("class",'save_id');
      newInput.setAttribute("type","hidden");
      newInput.setAttribute("value",data.independence_infomations[i].user_independence_informations_id);
      sum++;
      saveIndependence.appendChild(newInput);

      let independent_independence = row.insertCell(-1);
      let Dementia_independence = row.insertCell(-1);
      let Judgment_date_independence = row.insertCell(-1);
      let Judge_independence = row.insertCell(-1);
      row.appendChild(newInput)
      row.addEventListener('click',this.clickHistory.bind(this))

      // 入力情報をセルに出力 classも追加する
      if(!(data.independence_infomations?.[i]?.independence_level == undefined)){
        // independence_levelの値変換
        let changeData = data.independence_infomations[i].independence_level;
        if(changeData == 1){
          independent_independence.innerHTML = "自立";
        }else if(changeData == 2){
          independent_independence.innerHTML = "交通機関利用可";
        }else if(changeData == 3){
          independent_independence.innerHTML = "近隣の外出可";
        }else if(changeData == 4){
          independent_independence.innerHTML = "介助で外出可";
        }else if(changeData == 5){
          independent_independence.innerHTML = "外出頻度少";
        }else if(changeData == 6){
          independent_independence.innerHTML = "車いす利用";
        }else if(changeData == 7){
          independent_independence.innerHTML = "移乗介助で車いす利用";
        }else if(changeData == 8){
          independent_independence.innerHTML = "自力で寝返り可";
        }else if(changeData == 9){
          independent_independence.innerHTML = "自力で寝返り不可";
        }else{
          independent_independence.innerHTML = "";
        }
        independent_independence.classList.add('text_independent_independence');
      }else{
        independent_independence.innerHTML = "";
        independent_independence.classList.add('text_independent_independence');
      }
      if(!(data.independence_infomations?.[i]?.dementia_level == undefined)){
        // dementia_levelの値変換
        let changeData = data.independence_infomations[i].dementia_level;
        if(changeData == 1){
          Dementia_independence.innerHTML = "自立";
        }else if(changeData == 2){
          Dementia_independence.innerHTML = "認知症有自立";
        }else if(changeData == 3){
          Dementia_independence.innerHTML = "多少意思疎通難自立";
        }else if(changeData == 4){
          Dementia_independence.innerHTML = "Ⅱの家庭外";
        }else if(changeData == 5){
          Dementia_independence.innerHTML = "Ⅱの家庭内";
        }else if(changeData == 6){
          Dementia_independence.innerHTML = "日常生活支障有";
        }else if(changeData == 7){
          Dementia_independence.innerHTML = "Ⅲの日中中心";
        }else if(changeData == 8){
          Dementia_independence.innerHTML = "Ⅲの夜間中心";
        }else if(changeData == 9){
          Dementia_independence.innerHTML = "日常生活支障頻繁";
        }else if(changeData == 10){
          Dementia_independence.innerHTML = "専門医療必要";
        }else{
          Dementia_independence.innerHTML = "";
        }
        Dementia_independence.classList.add('text_dementia_independence');
      }else{
        Dementia_independence.innerHTML = "";
        Dementia_independence.classList.add('text_dementia_independence');
      }
      if(!(data.independence_infomations?.[i]?.judgment_date == undefined)){
        // ↓↓日付のフォーマット変更↓↓
        let setData = data.independence_infomations[i].judgment_date;
        let year = setData.substr(0,4);
        let month = setData.substring(5,7);
        let day = setData.substring(8,10);
        let yMD = year+"/"+month+"/"+day;
        // ↑↑日付のフォーマット変更↑↑
        Judgment_date_independence.innerHTML = yMD;
        Judgment_date_independence.classList.add('text_judgment_date_independence');
      }else{
        Judgment_date_independence.innerHTML = "";
        Judgment_date_independence.classList.add('text_judgment_date_independence');
      }
      if(!(data.independence_infomations?.[i]?.judger == undefined)){
        Judge_independence.innerHTML = data.independence_infomations[i].judger;
        Judge_independence.classList.add('text_judge_independence');
      }else{
        Judge_independence.innerHTML = "";
        Judge_independence.classList.add('text_judge_independence');
      }
    }
    document.getElementById("saveIdNumMaxIndependence").value = sum;
    let elements = document.getElementsByClassName("selectTableIndependence");
    for(let i=0; i<elements.length; i++){
      elements[i].style.backgroundColor ="rgb(250,250,250)";
    }
    elements[0].style.backgroundColor ="#ffffee";
    document.getElementById("saveGetIdIndependence").value = data.independence_infomations[0].user_independence_informations_id;
    document.getElementById("select_list1_independence").value = data.independence_infomations[0].independence_level;
    document.getElementById("select_list2_independence").value = data.independence_infomations[0].dementia_level
    // 最新の情報のフォーマット変更
    let decisionDate = data.independence_infomations[0].judgment_date;
    let num =  decisionDate.replace(/[^0-9]/g, '');
    if(num.length == 8){
      var y = num.substr(0,4);
      var m = num.substr(4,2);
      var d = num.substr(6,2);
      decisionDate = y + "/" + m + "/" + d;
    }
    document.getElementById("text_item1_independence").value = decisionDate;
    document.getElementById("text_item2_independence").value = data.independence_infomations[0].judger;
    document.getElementById("newIndependenceData").value = 0;
    document.getElementById('table_tbody_independence').scrollTop = 0;
    $("#overflow_independence").hide();
    }.bind(this))
    .fail(function(xhr){
      $("#overflow_independence").hide();
      if(xhr.status == 419){
        location.href = location.href;
      }
      this.validateDisplay(xhr.responseJSON)

    }.bind(this));
  }

  clickHistory(event){
    this.clearValidateDisplay();
    if(document.getElementById("tabIndependence").value == 'true'){
      let elements = document.getElementsByClassName("selectTableIndependence");
      for(let i=0; i<elements.length; i++){
        elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
      }
      event.target.parentNode.style.backgroundColor ="#ffffee";//選択した際に水色に代わる処理
      let saveId = event.target.parentNode.querySelector('.save_id').value
      document.getElementById("saveGetIdIndependence").value = saveId;
      let facilityUserId = document.getElementById("getFacilityIdIndependence").value;
      // 下部に表示する情報準備
      document.getElementById("onBtnIndependence").value = 1;

      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      // 権限チェックのために'facility_user_id'を追加
      $.ajax({
        url:'user_info/popup_updata_independence/ajax',
        type:'POST',
        data:{'user_independence_informations_id':saveId,'facility_user_id':facilityUserId},
      })
      .done(function(data){
        let option;
        // 事業所名、サービス種類プルダウンメニュー初期化
        document.getElementById("select_list1_independence").value = data.user_independence_infomations[0].independence_level;
        document.getElementById("select_list2_independence").value = data.user_independence_infomations[0].dementia_level;
        let StartDate = data.user_independence_infomations[0].judgment_date.replace(/-/g, '/');
        document.getElementById("text_item1_independence").value = StartDate;
        document.getElementById("jaCalInStartDate").innerText = JapaneseCalendar.toJacal(StartDate);
        document.getElementById("text_item2_independence").value = data.user_independence_infomations[0].judger;
        document.getElementById("newIndependenceData").value = 0;//更新として準備
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
      });
    }
  };

  // DBから情報取得しテーブルに情報を挿入
  tabCheckIndependence(){
    if(document.getElementById("tabIndependence").value == 'true'){
      this.clearValidateDisplay();
      $(function(){
        let getNum = document.getElementById('getFacilityIdIndependence').value;
          $.ajaxSetup({
            headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
          });
          // 権限チェックのために"postData"を'facility_user_id'に改修
          $.ajax({
            url:'user_info/independence/ajax',
            type:'POST',
            data:{'facility_user_id':getNum},
          })
          .done(function(data){
            // テーブル情報クリア
            let tableElem = document.getElementById("table_independence");
            let tbodyElem = document.getElementById("table_tbody_independence");
            // ↓↓テーブル情報の削除方法変更↓↓　■修正箇所
            while( tableElem.rows[ 1 ] ){
              tableElem.deleteRow( 1 );
            }
            // ↑↑テーブル情報の削除方法変更↑↑　■修正箇所
            // 保存していたIDを削除 2021/08/23
            for(let i = document.getElementById("saveIdNumMaxIndependence").value; i > 0; i--){
              document.getElementById(`saveIdIndependence${i}`)?.remove();
            }
            // ID保存初期化
            let saveIndependence = document.getElementById("saveIndependence");
            var sum = 0;
            // 取得情報を元にテーブルにセルを生成
            for(let i = 0; i < data.maximum_items; i++){
              // セルに挿入
              let row = tbodyElem.insertRow(-1);
              row.setAttribute("id","selectIdIndependence");
              row.setAttribute("class","selectTableIndependence");
              row.setAttribute("dusk",`selectIdIndependence${i + 1}`);
              // ID保存用 input hidden 作成
              let newInput = document.createElement("input");
              newInput.setAttribute("id",`saveIdIndependence${i + 1}`);
              newInput.setAttribute("class",'save_id');
              newInput.setAttribute("type","hidden");
              newInput.setAttribute("value",data.independence_infomations[i].user_independence_informations_id);
              sum++;
              saveIndependence.appendChild(newInput);

              let independent_independence = row.insertCell(-1);
              let Dementia_independence = row.insertCell(-1);
              let Judgment_date_independence = row.insertCell(-1);
              let Judge_independence = row.insertCell(-1);
              row.appendChild(newInput)
              row.addEventListener('click',this.clickHistory.bind(this))

              // 入力情報をセルに出力 classも追加する
              if(!(data.independence_infomations?.[i]?.independence_level == undefined)){
                // independence_levelの値変換
                let changeData = data.independence_infomations[i].independence_level;
                if(changeData == 1){
                  independent_independence.innerHTML = "自立";
                }else if(changeData == 2){
                  independent_independence.innerHTML = "交通機関利用可";
                }else if(changeData == 3){
                  independent_independence.innerHTML = "近隣の外出可";
                }else if(changeData == 4){
                  independent_independence.innerHTML = "介助で外出可";
                }else if(changeData == 5){
                  independent_independence.innerHTML = "外出頻度少";
                }else if(changeData == 6){
                  independent_independence.innerHTML = "車いす利用";
                }else if(changeData == 7){
                  independent_independence.innerHTML = "移乗介助で車いす利用";
                }else if(changeData == 8){
                  independent_independence.innerHTML = "自力で寝返り可";
                }else if(changeData == 9){
                  independent_independence.innerHTML = "自力で寝返り不可";
                }else{
                  independent_independence.innerHTML = "";
                }
                independent_independence.classList.add('text_independent_independence');
              }else{
                independent_independence.innerHTML = "";
                independent_independence.classList.add('text_independent_independence');
              }
              if(!(data.independence_infomations?.[i]?.dementia_level == undefined)){
                // dementia_levelの値変換
                let changeData = data.independence_infomations[i].dementia_level;
                if(changeData == 1){
                  Dementia_independence.innerHTML = "自立";
                }else if(changeData == 2){
                  Dementia_independence.innerHTML = "認知症有自立";
                }else if(changeData == 3){
                  Dementia_independence.innerHTML = "多少意思疎通難自立";
                }else if(changeData == 4){
                  Dementia_independence.innerHTML = "Ⅱの家庭外";
                }else if(changeData == 5){
                  Dementia_independence.innerHTML = "Ⅱの家庭内";
                }else if(changeData == 6){
                  Dementia_independence.innerHTML = "日常生活支障有";
                }else if(changeData == 7){
                  Dementia_independence.innerHTML = "Ⅲの日中中心";
                }else if(changeData == 8){
                  Dementia_independence.innerHTML = "Ⅲの夜間中心";
                }else if(changeData == 9){
                  Dementia_independence.innerHTML = "日常生活支障頻繁";
                }else if(changeData == 10){
                  Dementia_independence.innerHTML = "専門医療必要";
                }else{
                  Dementia_independence.innerHTML = "";
                }
                Dementia_independence.classList.add('text_dementia_independence');
              }else{
                Dementia_independence.innerHTML = "";
                Dementia_independence.classList.add('text_dementia_independence');
              }
              if(!(data.independence_infomations?.[i]?.judgment_date == undefined)){
                // ↓↓日付のフォーマット変更↓↓
                let setData = data.independence_infomations[i].judgment_date;
                let year = setData.substr(0,4);
                let month = setData.substring(5,7);
                let day = setData.substring(8,10);
                let yMD = year+"/"+month+"/"+day;
                // ↑↑日付のフォーマット変更↑↑
                Judgment_date_independence.innerHTML = yMD;
                Judgment_date_independence.classList.add('text_judgment_date_independence');
              }else{
                Judgment_date_independence.innerHTML = "";
                Judgment_date_independence.classList.add('text_judgment_date_independence');
              }
              if(!(data.independence_infomations?.[i]?.judger == undefined)){
                Judge_independence.innerHTML = data.independence_infomations[i].judger;
                Judge_independence.classList.add('text_judge_independence');
              }else{
                Judge_independence.innerHTML = "";
                Judge_independence.classList.add('text_judge_independence');
              }
            }
            // ↓↓　初期選択処理　↓↓　━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            var index = 0;
            let elements = document.getElementsByClassName("selectTableIndependence");
              document.getElementById("saveIdNumMaxIndependence").value = sum;
              for(let i=0; i<elements.length; i++){
                elements[i].style.backgroundColor ="rgb(250,250,250)";//選択以外の物を白に戻す処理
              }
              // 履歴情報が１つ以上ある場合
              if(elements.length != 0){
                elements[index].style.backgroundColor ="#ffffee";
              }else{
                // 履歴情報がない場合
                document.getElementById("select_list1_independence").options[0].selected = true;
                document.getElementById("select_list2_independence").options[0].selected = true;
                document.getElementById("text_item1_independence").value = "";
                document.getElementById("text_item2_independence").value = "";
                document.getElementById("jaCalInStartDate").innerText = "";
                document.getElementById("newIndependenceData").value = 1;//新規登録として準備
                return;
              }
              let row = elements[index];
              let cells = row.getElementsByTagName("td");
              index += 1;
              let saveId = document.getElementById(`saveIdIndependence${index}`).value;
              document.getElementById("saveGetIdIndependence").value = saveId;
              let facilityUserId = document.getElementById("getFacilityIdIndependence").value;
              // 下部に表示する情報準備
                document.getElementById("onBtnIndependence").value = 1;

                $.ajaxSetup({
                  headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
                });
                $.ajax({
                  url:'user_info/popup_updata_independence/ajax',
                  type:'POST',
                  data:{'user_independence_informations_id':saveId, 'facility_user_id':facilityUserId},
                })
                .done(function(data){
                  let option;
                  // 入力フォームの情報挿入処理
                  document.getElementById("select_list1_independence").value = data.user_independence_infomations[0].independence_level;
                  document.getElementById("select_list2_independence").value = data.user_independence_infomations[0].dementia_level;
                  
                  // 初期表示のフォーマット変更
                  let item1 = data.user_independence_infomations[0].judgment_date;
                  let num =  item1.replace(/[^0-9]/g, '');
                  if(num.length == 8){
                    var y = num.substr(0,4);
                    var m = num.substr(4,2);
                    var d = num.substr(6,2);
                    item1 = y + "/" + m + "/" + d;
                  }

                  document.getElementById("jaCalInStartDate").innerText = JapaneseCalendar.toJacal(item1);

                  document.getElementById("text_item1_independence").value = item1;
                  document.getElementById("text_item2_independence").value = data.user_independence_infomations[0].judger;
                })
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
          let scroll = document.getElementById('table_tbody_independence');
          scroll.scrollTop = 0;
          document.getElementById("newIndependenceData").value = 0;//更新として準備
          $("#overflow_independence").hide();//ポップアップ非表示にする処理
          // ↑↑　初期選択処理　↑↑　━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
      }.bind(this))
    }
  }

  /**
   * @param {bool} status 表示のブーリアン値
   */
  setActive(status){
    document.getElementById("tabIndependence").value = status;
    if(status && document.getElementById("getFacilityIdIndependence").value != ""){
      this.tabCheckIndependence();//DBから情報取得しテーブルに情報を挿入
    }
  }

  /**
   * @param {Object} user {facilityUserID: string, userName: string}
   */
  setFacilityUser(user){
    // 利用者テーブルのレコードが選択された時、その利用者の情報をビューに追加するサンプル
    // this.element.insertAdjacentHTML('beforeend',JSON.stringify(user));
    // this.element.appendChild(document.createElement('br'));
    //　左サイドバー利用者取得　
    let json = JSON.stringify(user);
    let getId = JSON.parse(json);
    document.getElementById("getFacilityIdIndependence").value = getId.facilityUserID;

    user.facilityUserID ? $(".button_independence").show() : $(".button_independence").hide();//登録、編集ボタンを表示する処理

    if(document.getElementById("tabIndependence").value == "true" && user.facilityUserID != null) {
      this.tabCheckIndependence();//DBから情報取得しテーブルに情報を挿入
    }else{
      this.formClear();
      document.getElementById("table_tbody_independence").textContent = null;
    }
  }

  validateDisplay(errorBody)
  {
    let createRow = (function(key, value){
      let record = document.createElement('li');
      let validationDisplayArea = document.getElementById("validateErrorsIndependence");
      record.textContent = value;
      validationDisplayArea.appendChild(record);
    });

    // errorBody = JSON.parse(errorBody);
    let errorList = errorBody.errors;
    Object.keys(errorList).map(key =>
      createRow(key, errorList[key])
    );
  }

  clearValidateDisplay()
  {
      while(this.validationDisplayArea.lastChild){
          this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
      }
  }
}
