/**
 * サービス種別
 * @author ttakenaka
 */

import JapaneseCalendar from '../../lib/japanese_calendar.js';

export default class ServiceType {
    constructor() {
        this.elementID = "tm_contents_service_type";
        this.element = document.getElementById(this.elementID);

        document.getElementById('clearBtn_service_type').addEventListener('click',this.submitServiceType.bind(this))
        document.getElementById('js-updata-popup_service_type').addEventListener('click',this.submitServiceType.bind(this))
        document.getElementById('updatabtn_service_type').addEventListener('click',this.submitServiceType.bind(this))
        document.getElementById('cancelbtn_service_type').addEventListener('click',this.submitServiceType.bind(this))

        // datepicker共通初期設定
        $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );
        $(".ymdatepicker").datepicker({
            changeYear: true,
            yearRange: '2000:2099',
            dateFormat: "yy/mm",
            monthNames: [ "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12" ],
            showOnlyMonths: true,
            onClose: function (dateText, inst) {
                let res = JapaneseCalendar.toJacal(dateText);
                $("#" + inst.id).prev().children('[id^="jaCal"]').text(res);
            }.bind(this)
        });

        document.getElementById('text_item1_service_type').addEventListener('input', JapaneseCalendar.inputChangeJaCalYearMonth.bind(this));
    }

    /**
     * 既に登録されているサービス種別は新規登録の際にプルダウンリストに表示しない
     */
    async registeredServiceTypeCheck()
    {
        await this.serviceTypeList()
        let seviceData = await this.getList()

        let registeredServiceTypeIds = []
        seviceData.service_type_all.forEach(element => {
            registeredServiceTypeIds.push(element.service_type_code)
        });
        let serviceTypes = document.querySelectorAll('.option_service');

        serviceTypes.forEach(element => {
            registeredServiceTypeIds.forEach(e => {
                if(element.value === e){
                    element.remove()
                }
            })
        })
    }

    /**
     * 各種ボタン押下時処理
     * @param {object} event
     * @returns
     */
    submitServiceType(event)
    {
        let submitBtn = event.target.id;
        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
        if (submitBtn == "clearBtn_service_type") {
            // 新規登録押下処理
            // 本来タブマネージャー側で管理すべき箇所ですが、暫定的にここに書いています
            if(!document.querySelector('.active_target').classList.contains("facility_label")){
                document.querySelector('.active_target').style.color = '';
                document.querySelector('.active_target').classList.remove('active_target');
            }

            this.registeredServiceTypeCheck();

            document.getElementById("select_list1_service_type").options[0].selected = true;
            document.getElementById("select_list2_service_type").options[0].selected = true;
            document.getElementById("text_item1_service_type").value = "";
            document.getElementById("newServiceTypeData").value = 1; //新規登録として準備
            let elements = document.getElementsByClassName("selectTableServiceType");
            for (let i = 0; i < elements.length; i++) {
                elements[i].style.backgroundColor = "rgb(250,250,250)"; //選択以外の物を白に戻す処理
            }
            return;
        } else if (submitBtn == "js-updata-popup_service_type") {
            // 保存ボタン押下処理
            let select_list1_service_type = document.getElementById("select_list1_service_type");
            let value1 = select_list1_service_type.value;
            let select_list2_service_type = document.getElementById("select_list2_service_type");
            let value2 = select_list2_service_type.value;
            let text_item1_service_type = document.getElementById("text_item1_service_type");
            let value3 = text_item1_service_type.value;
            if (value1 != "" && value2 != "" && value3 != "") {
                if (document.getElementById("newServiceTypeData").value == 0) {
                    $("#overflow_service_type").show();
                    return;
                } else {
                }
            } else {
                return;
            }
        } else if (submitBtn == "updatabtn_service_type") {
            // 更新ポップアップ処理
        } else if (submitBtn == "cancelbtn_service_type") {
            $("#overflow_service_type").hide();
            return;
        }
        let value5;
        if (document.getElementById("newServiceTypeData").value == 1) {
            value5 = 0;
        } else {
            value5 = document.getElementById("saveGetIdServiceType").value;
        }
        let select_list1_service_type = document.getElementById(
            "select_list1_service_type"
        );
        let value1 = select_list1_service_type.value;
        console.log(`サービス種別ID：${value1}`);
        let select_list2_service_type = document.getElementById(
            "select_list2_service_type"
        );
        let value2 = select_list2_service_type.value;
        let text_item1_service_type = document.getElementById(
            "text_item1_service_type"
        );
        let value3 = text_item1_service_type.value;
        let value4 = document.getElementById("getFacilityIdServiceType").value;
        let value6 = document.getElementById("saveLeftGetIdServiceType").value;
        let first_plan_input = $("[name=first_plan_input]").prop("checked") ? 1 : 0;
        //↓↓バリデーション↓↓
        if (value1 != "" && value2 != "" && value3 != "") {
            // ↓↓日付のフォーマット変更↓↓
            let setData = value3;
            let year = setData.substr(0, 4);
            let month = setData.substring(5, 7);
            let yMD = year + "/" + month + "/" + "01";
            value3 = yMD;
            // ↑↑日付のフォーマット変更↑↑
            //↓↓　DB登録処理　↓↓----------------------------------------------------------------------------------------
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            $.ajax({
                url: "facility_info/service_type/store",
                type: "POST",
                data: {
                    service_type_code: value1,
                    area: value2,
                    change_date: value3,
                    facility_id: value4,
                    first_plan_input: first_plan_input,
                    saveGetIdServiceType: value5,
                    saveLeftGetIdServiceType: value6,
                },
            })
            .done(function (data) {
                // テーブル情報クリア
                let tableElem = document.getElementById("table_service_type");
                let tbodyElem = document.getElementById("table_tbody_service_type");
                // ↓↓テーブル情報の削除方法変更↓↓　■修正箇所
                while (tableElem.rows[1]) {
                    tableElem.deleteRow(1);
                }
                // ↑↑テーブル情報の削除方法変更↑↑　■修正箇所
                // 保存していたIDを削除 2021/08/23
                for (let i = document.getElementById("saveIdNumMaxServiceType").value;i > 0;i--) {
                    document.getElementById(`saveIdServiceType${i}`)?.remove();
                }
                // ID保存初期化
                let saveServiceType =
                    document.getElementById("saveServiceType");
                var sum = 0;
                // 取得情報を元にテーブルにセルを生成
                for (let i = 0; i < data.maximum_items; i++) {
                    // セルに挿入
                    let row = tbodyElem.insertRow(-1);
                    row.setAttribute("id", "selectTdServiceType");
                    row.setAttribute("class", "selectTableServiceType");
                    // ID保存用 input hidden 作成
                    let newInput = document.createElement("input");
                    newInput.setAttribute("id", `saveIdServiceType${i + 1}`);
                    newInput.setAttribute("class",'save_id');
                    newInput.setAttribute("type", "hidden");
                    newInput.setAttribute("value", data.get_service_type[i].id);
                    sum++;
                    saveServiceType.appendChild(newInput);
                    let text_value0_service_type = row.insertCell(-1);
                    let text_value1_service_type = row.insertCell(-1);
                    let text_value2_service_type = row.insertCell(-1);
                    let text_value3_service_type = row.insertCell(-1);
                    let text_value4_service_type = row.insertCell(-1);
                    row.appendChild(newInput)
                    row.addEventListener('click',this.clickTableServiceType.bind(this))

                    // 入力情報をセルに出力 classも追加する
                    if (!(data.get_service_type?.[i]?.change_date == undefined)) {
                        // ↓↓日付のフォーマット変更↓↓
                        let setData = data.get_service_type[i].change_date;
                        let year = setData.substr(0, 4);
                        let month = setData.substring(5, 7);
                        let yMD = year + "/" + month;
                        // ↑↑日付のフォーマット変更↑↑
                        text_value0_service_type.innerHTML = yMD;
                        text_value0_service_type.classList.add("text_value0_service_type");
                    } else {
                        text_value0_service_type.innerHTML = "";
                        text_value0_service_type.classList.add("text_value0_service_type");
                    }

                    if (!(data.get_service_type2?.[i]?.service_type_code == undefined)) {
                        text_value1_service_type.innerHTML = data.get_service_type2[i].service_type_code;
                        text_value1_service_type.classList.add("text_value1_service_type");
                    } else {
                        text_value1_service_type.innerHTML = "";
                        text_value1_service_type.classList.add("text_value1_service_type");
                    }

                    if (!(data.get_service_type2?.[i]?.service_type_name == undefined)) {
                        text_value2_service_type.innerHTML = data.get_service_type2[i].service_type_name;
                        text_value2_service_type.classList.add("text_value2_service_type");
                    } else {
                        text_value2_service_type.innerHTML = "";
                        text_value2_service_type.classList.add("text_value2_service_type");
                    }

                    if (!(data.get_service_type?.[i]?.area == undefined)) {
                        text_value3_service_type.innerHTML = data.get_service_type[i].area;
                        var area_service_type = data.get_service_type[i].area;
                        text_value3_service_type.classList.add("text_value3_service_type");
                    } else {
                        text_value3_service_type.innerHTML = "";
                        text_value3_service_type.classList.add("text_value3_service_type");
                    }

                    let calc; //単価計算用
                    // area数により単価出力先を変える
                    switch (area_service_type) {
                        case "１級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_1 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_1;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "２級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_2 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_2;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "３級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_3 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_3;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "４級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_4 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_4;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "５級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_5 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_5;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "６級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_6 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_6;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "７級地":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_7 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_7;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        case "その他":
                            if (
                                !(
                                    data.get_service_type2?.[i]
                                        ?.area_unit_price_8 == undefined
                                )
                            ) {
                                calc =
                                    data.get_service_type2[i].area_unit_price_8;
                                calc = calc / 100; // 単価計算
                                text_value4_service_type.innerHTML =
                                    calc.toFixed(2);
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            } else {
                                text_value4_service_type.innerHTML = "";
                                text_value4_service_type.classList.add(
                                    "text_value4_service_type"
                                );
                            }
                            break;
                        default:
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                            break;
                    }
                }
                document.getElementById("saveIdNumMaxServiceType").value = sum;
                document.getElementById("saveGetIdServiceType").value = 0;
                let elements = document.getElementsByClassName("selectTableServiceType");
                for (let i = 0; i < elements.length; i++) {
                    elements[i].style.backgroundColor = "rgb(250,250,250)";
                }
                document.getElementById("select_list1_service_type").options[0].selected = true;
                document.getElementById("select_list2_service_type").options[0].selected = true;
                document.getElementById("text_item1_service_type").value = "";
                $("#overflow_service_type").hide();
            }.bind(this))
            .fail(function (xhr) {
                if(xhr.status == 419){
                    location.href = location.href;
                }
            });
        }
    }

    /**
     * サービス種別プルダウンメニュー項目追加処理
     */
    async serviceTypeList()
    {
        $.ajaxSetup({
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        });
        await $.ajax({
            url: "facility_info/service_type/list",
            type: "GET",
        })
        .done(function (data) {
            let option;
            let selectlist;
            // サービス種類メニュー項目追加処理
            selectlist = document.getElementById("select_list1_service_type");
            for (let i = selectlist.length; i > 0; i--) {
                selectlist.remove(i);
            }
            selectlist.options[0].selected = true;
            for (let i = 0; i < data.maximum_items; i++) {
                selectlist = document.getElementById("select_list1_service_type");
                option = document.createElement("option");
                option.text = data.get_service_type[i].service_type_code + "：" + data.get_service_type[i].service_type_name;
                option.value = data.get_service_type[i].service_type_code;
                selectlist.appendChild(option).classList.add("option_service");
            }
        })
        .fail(function (xhr) {
            if(xhr.status == 419){
                location.href = location.href;
            }
        });
    }

    /**
     * 登録済サービス種別を取得
     */
    async getList()
    {
        let facilityId = document.getElementById("getFacilityIdServiceType").value;
        let serviceType = document.getElementById("saveLeftGetIdServiceType").value;

        $.ajaxSetup({
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")}
        });

        return await $.ajax({
            url: "facility_info/service_type/ajax",
            type: "POST",
            data: {
                facility_id: facilityId,
                postData2: serviceType,
            },
        }).done(function (data) {
            return data;
        })
    }

    /**
     * 種別プルダウンの内容を変更する
     */
    async editServiceTypePulldown(serviceTypeId)
    {
        await this.serviceTypeList()
        let serviceTypes = document.querySelectorAll('.option_service');

        serviceTypes.forEach(element => {
            if(element.value != serviceTypeId){
                element.remove()
            }
        })
        serviceTypes.value = serviceTypeId;
    }

    /**
     * 履歴選択時処理
     */
    async clickTableServiceType(event)
    {
        if (document.getElementById("tabServiceType").value == "true") {
            let elements = document.getElementsByClassName("selectTableServiceType");
            for (let i = 0; i < elements.length; i++) {
                elements[i].style.backgroundColor = "rgb(250,250,250)"; //選択以外の物を白に戻す処理
            }
            event.target.parentNode.style.backgroundColor = "var(--table-select-color)"; //選択した際に水色に代わる処理
            let saveId = event.target.parentNode.querySelector('.save_id').value

            await this.editServiceTypePulldown(event.target.parentNode.querySelector('.text_value1_service_type').textContent)

            let facilityId= document.getElementById("getFacilityIdServiceType").value;
            document.getElementById("saveGetIdServiceType").value = saveId;
            // 下部に表示する情報準備
            document.getElementById("onBtnServiceType").value = 1;

            $.ajaxSetup({
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            });
            // 権限チェックのために"facility_id"を追加
            await $.ajax({
                url: "facility_info/service_type/update_from",
                type: "POST",
                data: { 'service_id': saveId,'facility_id':facilityId },
            })
            .done(function (data) {
                let option;
                // 事業所名、サービス種類プルダウンメニュー初期化
                switch (data.get_service_type[0].area) {
                    case "１級地":
                        document.getElementById("select_list2_service_type").value = 1;
                        break;
                    case "２級地":
                        document.getElementById("select_list2_service_type").value = 2;
                        break;
                    case "３級地":
                        document.getElementById("select_list2_service_type").value = 3;
                        break;
                    case "４級地":
                        document.getElementById("select_list2_service_type").value = 4;
                        break;
                    case "５級地":
                        document.getElementById("select_list2_service_type").value = 5;
                        break;
                    case "６級地":
                        document.getElementById("select_list2_service_type").value = 6;
                        break;
                    case "７級地":
                        document.getElementById("select_list2_service_type").value = 7;
                        break;
                    case "その他":
                        document.getElementById("select_list2_service_type").value = 8;
                        break;
                    default:
                        document.getElementById("select_list2_service_type").value = 0;
                        break;
                }
                document.getElementById("select_list1_service_type").value = data.get_service_type2[0].service_type_code;
                // ↓↓日付のフォーマット変更↓↓
                let setData = data.get_service_type[0].change_date;
                let year = setData.substr(0, 4);
                let month = setData.substring(5, 7);
                let yM = year + "/" + month;
                // ↑↑日付のフォーマット変更↑↑
                document.getElementById("text_item1_service_type").value = yM;
                document.getElementById("jaCalSTStartDate").innerText = JapaneseCalendar.toJacal(yM + "/01");
                document.getElementById("newServiceTypeData").value = 0; //更新として準備
            })
            .fail(function (xhr) {
                if(xhr.status == 419){
                    location.href = location.href;
                }
            });
        }
    }

    async tabCheckServiceType() {
        await this.serviceTypeList()
        let getFacilityIdNum = document.getElementById("getFacilityIdServiceType").value;
        let getServiceTypeNum = document.getElementById("saveLeftGetIdServiceType").value;
        // 利用者IDが取得していない場合処理終了。
        // typeofで確認したところstringだったため「"undefined"」としている。
        if (getFacilityIdNum == 0 || getFacilityIdNum == "" || getFacilityIdNum == "undefined") {
            return;
        }
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
        // 権限チェックのために"postData1"を'facility_id'改修
        await $.ajax({
            url: "facility_info/service_type/ajax",
            type: "POST",
            data: {
                facility_id: getFacilityIdNum,
                postData2: getServiceTypeNum,
            },
        })
        .done(function (data) {

            // プルダウンリストから選択されたサービス以外を取り除く
            if(data.get_service_type2.length > 0){
                let serviceTypeId = data.get_service_type2[0].service_type_code

                let serviceTypes = document.querySelectorAll('.option_service');
                serviceTypes.forEach(element => {
                    if(element.value != serviceTypeId){
                        element.remove()
                    }
                })
            }

            // テーブル情報クリア
            let tableElem =
                document.getElementById("table_service_type");
            let tbodyElem = document.getElementById(
                "table_tbody_service_type"
            );
            // ↓↓テーブル情報の削除方法変更↓↓　■修正箇所
            while (tableElem.rows[1]) {
                tableElem.deleteRow(1);
            }
            // ↑↑テーブル情報の削除方法変更↑↑　■修正箇所
            // 保存していたIDを削除 2021/08/23
            for (let i = document.getElementById("saveIdNumMaxServiceType").value; i > 0; i--) {
                document.getElementById(`saveIdServiceType${i}`)?.remove();
            }
            // ID保存初期化
            let saveServiceType =
                document.getElementById("saveServiceType");
            var sum = 0;
            // 取得情報を元にテーブルにセルを生成
            for (let i = 0; i < data.maximum_items; i++) {
                // セルに挿入
                let row = tbodyElem.insertRow(-1);
                row.setAttribute("id", "selectTdServiceType");
                row.setAttribute("class", "selectTableServiceType");
                // ID保存用 input hidden 作成
                let newInput = document.createElement("input");
                newInput.setAttribute("id", `saveIdServiceType${i + 1}`);
                newInput.setAttribute("class",'save_id');
                newInput.setAttribute("type", "hidden");
                newInput.setAttribute("value", data.get_service_type[i].id);
                sum++;
                saveServiceType.appendChild(newInput);
                let text_value0_service_type = row.insertCell(-1);
                let text_value1_service_type = row.insertCell(-1);
                let text_value2_service_type = row.insertCell(-1);
                let text_value3_service_type = row.insertCell(-1);
                let text_value4_service_type = row.insertCell(-1);
                row.appendChild(newInput)
                row.addEventListener('click',this.clickTableServiceType.bind(this))

                // 入力情報をセルに出力 classも追加する
                if (!(data.get_service_type?.[i]?.change_date == undefined)) {
                    // ↓↓日付のフォーマット変更↓↓
                    let setData = data.get_service_type[i].change_date;
                    let year = setData.substr(0, 4);
                    let month = setData.substring(5, 7);
                    let yM = year + "/" + month;
                    // ↑↑日付のフォーマット変更↑↑
                    text_value0_service_type.innerHTML = yM;
                    text_value0_service_type.classList.add(
                        "text_value0_service_type"
                    );
                } else {
                    text_value0_service_type.innerHTML = "";
                    text_value0_service_type.classList.add(
                        "text_value0_service_type"
                    );
                }
                if (
                    !(
                        data.get_service_type2?.[i]
                            ?.service_type_code == undefined
                    )
                ) {
                    text_value1_service_type.innerHTML =
                        data.get_service_type2[i].service_type_code;
                    text_value1_service_type.classList.add(
                        "text_value1_service_type"
                    );
                } else {
                    text_value1_service_type.innerHTML = "";
                    text_value1_service_type.classList.add(
                        "text_value1_service_type"
                    );
                }
                if (
                    !(
                        data.get_service_type2?.[i]
                            ?.service_type_name == undefined
                    )
                ) {
                    text_value2_service_type.innerHTML =
                        data.get_service_type2[i].service_type_name;
                    text_value2_service_type.classList.add(
                        "text_value2_service_type"
                    );
                } else {
                    text_value2_service_type.innerHTML = "";
                    text_value2_service_type.classList.add(
                        "text_value2_service_type"
                    );
                }
                if (!(data.get_service_type?.[i]?.area == undefined)) {
                    text_value3_service_type.innerHTML =
                        data.get_service_type[i].area;
                    var area_service_type =
                        data.get_service_type[i].area;
                    text_value3_service_type.classList.add(
                        "text_value3_service_type"
                    );
                } else {
                    text_value3_service_type.innerHTML = "";
                    text_value3_service_type.classList.add(
                        "text_value3_service_type"
                    );
                }
                let calc; //単価計算用
                // area数により単価出力先を変える
                switch (area_service_type) {
                    case "１級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_1 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_1;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "２級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_2 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_2;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "３級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_3 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_3;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "４級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_4 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_4;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "５級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_5 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_5;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "６級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_6 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_6;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "７級地":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_7 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_7;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    case "その他":
                        if (
                            !(
                                data.get_service_type2?.[i]
                                    ?.area_unit_price_8 == undefined
                            )
                        ) {
                            calc =
                                data.get_service_type2[i]
                                    .area_unit_price_8;
                            calc = calc / 100; // 単価計算
                            text_value4_service_type.innerHTML =
                                calc.toFixed(2);
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        } else {
                            text_value4_service_type.innerHTML = "";
                            text_value4_service_type.classList.add(
                                "text_value4_service_type"
                            );
                        }
                        break;
                    default:
                        text_value4_service_type.innerHTML = "";
                        text_value4_service_type.classList.add(
                            "text_value4_service_type"
                        );
                        break;
                }
            }
            // ↓↓　初期選択処理　↓↓　━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            var index = 0;
            let elements = document.getElementsByClassName(
                "selectTableServiceType"
            );
            document.getElementById("saveIdNumMaxServiceType").value =
                sum;
            for (let i = 0; i < elements.length; i++) {
                elements[i].style.backgroundColor = "rgb(250,250,250)"; //選択以外の物を白に戻す処理
            }
            // 履歴情報が１つ以上ある場合
            if (elements.length != 0) {
                elements[index].style.backgroundColor =
                    "#ffffee";
            } else {
                // 履歴情報がない場合
                document.getElementById(
                    "select_list1_service_type"
                ).options[0].selected = true;
                document.getElementById(
                    "select_list2_service_type"
                ).options[0].selected = true;
                document.getElementById(
                    "text_item1_service_type"
                ).value = "";
                document.getElementById("newServiceTypeData").value = 1; //新規登録として準備
                return;
            }
            let row = elements[index];
            let cells = row.getElementsByTagName("td");
            index += 1;
            let saveId = document.getElementById(
                `saveIdServiceType${index}`
            ).value;
            document.getElementById("saveGetIdServiceType").value =
                saveId;
            // 下部に表示する情報準備
            document.getElementById("onBtnServiceType").value = 1;
            let option;
            // 入力フォームの情報挿入処理
            switch (data.get_service_type[0].area) {
                case "１級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 1;
                    break;
                case "２級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 2;
                    break;
                case "３級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 3;
                    break;
                case "４級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 4;
                    break;
                case "５級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 5;
                    break;
                case "６級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 6;
                    break;
                case "７級地":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 7;
                    break;
                case "その他":
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 8;
                    break;
                default:
                    document.getElementById(
                        "select_list2_service_type"
                    ).value = 0;
                    break;
            }
            document.getElementById("select_list1_service_type").value =
                data.get_service_type2[0].service_type_code;
            // ↓↓日付のフォーマット変更↓↓
            let setData = data.get_service_type[0].change_date;
            let year = setData.substr(0, 4);
            let month = setData.substring(5, 7);
            let yM = year + "/" + month;
            // ↑↑日付のフォーマット変更↑↑
            document.getElementById("text_item1_service_type").value =
                yM;
            document.getElementById("jaCalSTStartDate").innerText = JapaneseCalendar.toJacal(yM + "/01");
            $("[name=first_plan_input]").prop(
                "checked",
                data.get_service_type[0].first_plan_input ? true : false
            );
        }.bind(this))
        .fail(function (xhr) {
            if(xhr.status == 419){
                location.href = location.href;
            }
        });
        let scroll = document.getElementById("table_tbody_service_type");
        scroll.scrollTop = 0;
        document.getElementById("newServiceTypeData").value = 0; //更新として準備
        document.getElementById("tabServiceType").value = true; //左サイドバー実装後削除
        $("#overflow_service_type").hide();
    }

    /**
     * @param {bool} status 表示のブーリアン値
     */
    setActive(status) {
        document.getElementById("tabServiceType").value = status;
    }

    /**
     * @param {Object} user {facilityUserID: string, userName: string}
     */
    setFacilityData(user) {
        //　左サイド事業所取得
        let json = JSON.stringify(user);
        let getId = JSON.parse(json);
        if (getId.serviceId == undefined) {
            document.getElementById("saveGetIdServiceType").value = 0;
            document.getElementById("saveLeftGetIdServiceType").value = 0;
            this.clearAll()
        } else {
            document.getElementById("saveGetIdServiceType").value =
                getId.serviceId;
            document.getElementById("saveLeftGetIdServiceType").value =
                getId.serviceTypeCodeId;
        }
        document.getElementById("getFacilityIdServiceType").value =
            getId.facilityId;
        // $(".button_service_type").show(); //登録、編集ボタンを表示する処理
        this.tabCheckServiceType(); //DBから情報取得しテーブルに情報を挿入
    }

    clearAll()
    {
        let parentTable = document.getElementById('table_tbody_service_type');
        while(parentTable.lastChild){
            parentTable.removeChild(parentTable.lastChild);
        }

        document.getElementById("select_list1_service_type").options[0].selected = true;
        document.getElementById("select_list2_service_type").options[0].selected = true;
        document.getElementById("text_item1_service_type").value = null;
    }
}
