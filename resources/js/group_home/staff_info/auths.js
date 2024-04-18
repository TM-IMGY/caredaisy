import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";
import {
    FacilityPulldown,
    CorporationPulldown,
    InstitutionPulldown,
} from "./pulldown.js";

/**
 * 権限設定タブ
 */
export default class Staff {
    constructor(facilityID) {
        //定数を定義
        Object.defineProperty(this, "ADMINISTRATOR", {
            value: 1,
        });
        Object.defineProperty(this, "CLAIMANT", {
            value: 2,
        });
        Object.defineProperty(this, "PLANNER", {
            value: 3,
        });
        Object.defineProperty(this, "PLANNER_AND_CLAIMANT", {
            value: 4,
        });
        this.validationDisplayArea = document.getElementById("authValidateErrors");
        this.facility_id = facilityID;
        this.auth_extent = null;
        //履歴
        this.historyTBody = document.getElementById("auths_history_table_body");
        this.selectedRecord = null;
        //権限情報
        this.authInfoTBody = document.getElementById("auth_info");

        //入力項目
        this.staff_id = document.getElementById("auth_staff_id");
        this.auth_extent_id = document.getElementById("auth_extent_id");

        this.corporation_id = document.getElementById("auth_corporation");
        this.institution_id = document.getElementById("auth_institution");
        this.facility_id = document.getElementById("auth_facility");
        this.start_date = document.getElementById("auth_start_date");
        this.end_date = document.getElementById("auth_end_date");
        this.administrator = document.getElementById("auth_administrator");
        this.claimant = document.getElementById("auth_claimant");
        this.planner = document.getElementById("auth_planner");
        this.administrator.addEventListener(
            "change",
            this.authChange.bind(this)
        );
        this.claimant.addEventListener(
            "change",
            this.authChange.bind(this)
        );
        this.planner.addEventListener(
            "change",
            this.authChange.bind(this)
        );
        // ボタン要素
        this.newBtn = document.getElementById("auth_new_btn");
        this.saveBtn = document.getElementById("auth_save_btn");
        this.newBtn.addEventListener("click", this.newBtnClick.bind(this));
        this.saveBtn.addEventListener("click", this.saveBtnClick.bind(this));

        //コンボボックス
        this.cpd = new CorporationPulldown(
            "auth_corporation",
            "/group_home/service/corporation"
        );

        this.ipd = new InstitutionPulldown(
            "auth_institution",
            "/group_home/service/institution"
        );

        let setInstituion = function (corporation_id, ipd) {
            var postData = { id: corporation_id };
            ipd.syncServer(postData);
        };
        this.cpd.syncServer(setInstituion, this.ipd);

        this.fpd = new FacilityPulldown(
            "auth_facility",
            "/group_home/service/facility"
        );
        this.fpd.syncServer();
    }
    newBtnClick() {
        this.clearValidateDisplay();
        this.clear();
    }
    saveBtnClick() {
        this.clearValidateDisplay();
        this.save();
    }

    clear() {
        this.auth_extent_id.value = "";

        // this.corporation_id.value = "";
        // this.institution_id.value = "";
        // this.facility_id.value = "";
        this.start_date.value = "";
        this.end_date.value = "";
        this.setAuthGroup(0);
    }

    async validateDisplay(errorBody) {
        let createRow = function (key, value) {
            let record = document.createElement("li");
            let validationDisplayArea =
                document.getElementById("authValidateErrors");
            record.textContent = value;
            validationDisplayArea.appendChild(record);
        };

        errorBody = JSON.parse(errorBody);
        let errorList = errorBody.errors;
        Object.keys(errorList).map((key) => createRow(key, errorList[key]));
    }

    async clearValidateDisplay() {
        while (this.validationDisplayArea.lastChild) {
            this.validationDisplayArea.removeChild(
                this.validationDisplayArea.lastChild
            );
        }
    }
    async callbackSave(ret) {
        this.auth_extent = ret.auth_extent;
        this.makeAuthExtentHistory(ret.auth_extent, ret.auth_extent_id);
        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
    }
    async save() {
        let params = {};
        params["staff_id"] = this.staff_id.value;
        params["auth_extent_id"] = this.auth_extent_id.value;

        params["corporation_id"] = this.corporation_id.value;
        params["institution_id"] = this.institution_id.value;
        params["facility_id"] = this.facility_id.value;
        params["start_date"] = this.start_date.value;
        params["end_date"] = this.end_date.value;
        params["administrator"] = this.administrator.checked;
        params["claimant"] = this.claimant.checked;
        params["planner"] = this.planner.checked;

        return await CustomAjax.send(
            "POST",
            "/group_home/staff_info/auth_extent/save",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackSave",
            this
        );
    }
    /**
     * 履歴レコードの選択状態をクリアする
     * @return {void}
     */
    clearRecordSelectionState() {
        if (this.selectedRecord) {
            this.selectedRecord.classList.remove("sp1_select_record");
            this.selectedRecord = null;
        }
    }

    dateFormat(date) {
        let d = date ? date : "";
        let re = /(\d+\-\d+\-\d+).*/;
        return d.replace(re, "$1");
    }
    /**
     * 該当スタッフの情報取得処理
     * @param {object} history
     * @return {Promise}
     */
    async makeAuthExtentHistory(history, target_history_id) {
        // 履歴リストを初期化
        this.historyTBody.textContent = null;
        // 履歴レコードの選択状態をクリアする
        this.clearRecordSelectionState();
        //idが大きい順にソート
        history.sort(function (a, b) {
            return b.id - a.id;
        });
        history.forEach(function (data) {
            let record = document.createElement("tr");
            record.setAttribute("data-history-id", data.id);
            if (target_history_id == data.id) {
                record.classList.add("stf_select_record");
                this.selectedRecord = record;
            }
            let td = document.createElement("td");
            td.textContent = data.corporation.name;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = data.institution.name;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = data.facility.facility_name_kanji;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.getAuthName(data.auth_id);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.dateFormat(data.start_date);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.dateFormat(data.end_date);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.dateFormat(data.updated_at);
            record.appendChild(td);
            // td要素にクラス付与
            Array.from(record.children).forEach((child) => {
                child.className = "text_data_staff";
            });

            // レコードにクリックイベントを付与する
            record.addEventListener("click", (event) => {
                if (this.selectedRecord) {
                    this.selectedRecord.classList.remove("stf_select_record");
                }
                this.selectedRecord = record;
                this.selectedRecord.classList.add("stf_select_record");
                let id = record.getAttribute("data-history-id");
                this.setInputData(this.auth_extent, id);
            });
            this.historyTBody.appendChild(record);
        }, this);
    }
    /**
     * 権限グループをセットする
     * @param {int} auth_id
     * @return {void}
     */
    setAuthGroup(auth_id) {
        this.administrator.checked = false;
        this.planner.checked = false;
        this.claimant.checked = false;
        switch (auth_id) {
            case this.ADMINISTRATOR:
                this.administrator.checked = true;
                break;
            case this.CLAIMANT:
                this.claimant.checked = true;
                break;
            case this.PLANNER:
                this.planner.checked = true;
                break;
            case this.PLANNER_AND_CLAIMANT:
                this.planner.checked = true;
                this.claimant.checked = true;
                break;
        }
    }
    /**
     * 権限グループの名前を返す
     * @param {int} auth_id
     * @return {void}
     */
    getAuthName(auth_id) {
        switch (auth_id) {
            case this.ADMINISTRATOR:
                return "管理者 ";
            case this.CLAIMANT:
                return "請求担当者";
            case this.PLANNER:
                return "計画作成者";
            case this.PLANNER_AND_CLAIMANT:
                return "請求かつ計画作成者";
            default:
                return "";
        }
    }
    /**
     * 入力値を設定する
     * @param {Object} data
     * @param {Number} history_id
     */
    setInputData(data, history_id) {
        this.clear();
        let found;
        if (history_id) {
            found = data.find((data) => data.id == history_id);
        } else {
            found = data.find((data) => data.id == this.auth_extent_id.value);
        }
        this.auth_extent_id.value = found.id;

        this.corporation_id.value = found.corporation_id;
        this.institution_id.value = found.institution_id;
        this.facility_id.value = found.facility_id;
        this.start_date.value = this.dateFormat(found.start_date);
        this.end_date.value = this.dateFormat(found.end_date);
        this.setAuthGroup(found.auth_id);
        this.authChange();
    }
    async callbackGetAuthExtent(auth_Extent) {
        this.auth_extent = auth_Extent;
        //履歴を生成
        this.makeAuthExtentHistory(auth_Extent, this.auth_extent_id.value);

        //設定値を復元
        this.setInputData(auth_Extent, this.auth_extent_id.value);
    }

    /**
     * スタッフ一覧で選択した値をセットする
     * @param {Object} param key: saff_history_id, staff_id, staffName
     *     値を消すときは明示的にnullをセットする
     * @returns {Promise}
     */
    async setParam(param) {
        this.staff_id.value = param.staff_id;
        this.auth_extent_id.value = param.auth_extent_id;
        let params = {};
        params["staff_id"] = this.staff_id.value;

        return await CustomAjax.send(
            "POST",
            "/group_home/staff_info/auth_extent/get_history",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackGetAuthExtent",
            this
        );
    }
    /**
     * 権限の状態を表示する
     */
    authChange(){
        this.authInfoTBody.textContent = null;
        let datas;
        if(this.administrator.checked){
            datas=[
                {scope:"請求",auth:"閲覧、登録、承認、伝送"},
                {scope:"ケアプラン",auth:"閲覧、登録、確定、削除"},
                {scope:"事業所",auth:"閲覧、登録、削除"},
                {scope:"利用者１",auth:"閲覧、登録、削除"},
                {scope:"利用者２",auth:"閲覧、登録、削除"},
                {scope:"権限",auth:"閲覧、登録、削除"},
            ];
        }else if(this.claimant.checked && this.planner.checked){
            datas=[
                {scope:"請求",auth:"閲覧、登録、承認、伝送"},
                {scope:"ケアプラン",auth:"閲覧、登録、確定、削除"},
                {scope:"事業所",auth:"閲覧"},
                {scope:"利用者１",auth:"閲覧、登録"},
                {scope:"利用者２",auth:"閲覧、登録"},
                {scope:"権限",auth:"なし"},
            ];
        }else if(this.claimant.checked){
            datas=[
                {scope:"請求",auth:"閲覧、登録、承認、伝送"},
                {scope:"ケアプラン",auth:"閲覧"},
                {scope:"事業所",auth:"閲覧"},
                {scope:"利用者１",auth:"閲覧、登録"},
                {scope:"利用者２",auth:"閲覧、登録"},
                {scope:"権限",auth:"なし"},
            ];
        }else if(this.planner.checked){
            datas=[
                {scope:"請求",auth:"閲覧"},
                {scope:"ケアプラン",auth:"閲覧、登録、確定、削除"},
                {scope:"事業所",auth:"閲覧"},
                {scope:"利用者１",auth:"閲覧"},
                {scope:"利用者２",auth:"閲覧"},
                {scope:"権限",auth:"なし"},
            ];
        }else{
            datas=[
                {scope:"請求",auth:"なし"},
                {scope:"ケアプラン",auth:"閲覧"},
                {scope:"事業所",auth:"なし"},
                {scope:"利用者１",auth:"閲覧"},
                {scope:"利用者２",auth:"なし"},
                {scope:"権限",auth:"なし"},
            ];
        }
        datas.forEach(function(data){
            let record = document.createElement("tr");
            let td = document.createElement("td");
            td.textContent = data.scope;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = data.auth;
            record.appendChild(td);
            this.authInfoTBody.appendChild(record);
        },this)
    }
    /**
     * @param {bool} status 表示のブーリアン値
     */
    setActive(status) {
        //
    }
}
