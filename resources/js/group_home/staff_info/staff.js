import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";

/**
 * 入社情報タブ
 */
export default class Staff {
    constructor(facilityID) {
        this.validationDisplayArea = document.getElementById("validateErrors");
        this.facility_id = facilityID;
        this.staff = null;
        this.notificationAddresses = [];

        //履歴
        this.historyTBody = document.getElementById(
            "staff_info_history_table_body"
        );
        this.selectedRecord = null;

        //入力項目
        this.staff_id = document.querySelector('[name="staff_id"]');
        this.staff_history_id = document.querySelector(
            '[name="staff_history_id"]'
        );

        this.elementID = "tm_contents_staff";
        this.validationDisplayArea = document.getElementById("validateErrors");

        this.employee_number = document.querySelector("#stf_employee_number");
        this.date_of_employment = document.querySelector(
            "#stf_date_of_employment"
        );
        this.password = document.querySelector("#stf_password");
        this.passwordChanged = document.querySelector("#password_changed");
        this.passwordChanged.value = false;
        this.password.addEventListener(
            "change",
            this.passwordChange.bind(this)
        );
        this.login_id = document.querySelector("#stf_login_id");

        this.corporation_id = "";
        this.institution_id = "";
        this.name = document.querySelector("#stf_name");
        this.name_kana = document.querySelector("#stf_name_kana");

        this.setCheckedData = function () {
            this.gender = document.querySelector(
                'input:checked[name="stf_gender"]'
            );
            this.employment_status = document.querySelector(
                'input:checked[name="stf_employment_status"]'
            );
            this.employment_class = document.querySelector(
                'input:checked[name="stf_employment_class"]'
            );
            this.working_status = document.querySelector(
                'input:checked[name="stf_working_status"]'
            );
        };
        this.setCheckedData();

        this.location = document.querySelector("#stf_location");
        this.phone_number = document.querySelector("#stf_phone_number");
        this.emergency_contact_information = document.querySelector(
            "#stf_emergency_contact_information"
        );

        // ボタン要素
        this.newBtn = document.getElementById("stf_new_btn");
        this.saveBtn = document.getElementById("stf_save_btn");
        this.newBtn.addEventListener("click", this.newBtnClick.bind(this));
        this.saveBtn.addEventListener("click", this.saveBtnClick.bind(this));
    }

    /**
     * レコード選択イベントの通知先を設定する
     * @param {Function} callBack 通知先として呼ぶコールバック関数
     * @returns {void}
     */
    addNotification(callBack) {
        this.notificationAddresses.push(callBack);
    }
    /**
     * 通知する
     * @param {Object} data key: facilityUserID, userName
     * @returns {Promise}
     */
    async notification(data) {
        for (let i = 0, len = this.notificationAddresses.length; i < len; i++) {
            await this.notificationAddresses[i](data);
        }
    }
    passwordChange() {
        this.passwordChanged.value = true;
    }

    async newBtnClick() {
        this.clear();
        this.clearValidateDisplay();
    }

    async saveBtnClick() {
        this.clearValidateDisplay();
        this.save();
    }
    clear() {
        this.employee_number.value = "";
        this.date_of_employment.value = "";
        this.password.value = "";
        this.staff_history_id.value = "";

        this.name.value = "";
        this.name_kana.value = "";
        document.querySelector(
            'input[name="stf_gender"][value="1"]'
        ).checked = true;
        document.querySelector(
            'input[name="stf_employment_status"][value="1"]'
        ).checked = true;
        document.querySelector(
            'input[name="stf_employment_class"][value="1"]'
        ).checked = true;
        document.querySelector(
            'input[name="stf_working_status"][value="1"]'
        ).checked = true;
        this.location.value = "";
        this.phone_number.value = "";
        this.emergency_contact_information.value = "";
    }

    async validateDisplay(errorBody) {
        let createRow = function (key, value) {
            let record = document.createElement("li");
            let validationDisplayArea =
                document.getElementById("validateErrors");
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
        this.staff_history_id.value = ret.staff_history_id;
        this.makeStaffHistory(ret.staff_history, this.staff_history_id.value);
        await this.notification(ret);
        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
    }
    async save() {
        this.setCheckedData();
        let params = {};
        params["employee_number"] = this.employee_number.value;
        params["date_of_employment"] = this.date_of_employment.value;
        params["password"] = this.password.value;
        params["password_changed"] = this.passwordChanged.value === "true";

        params["staff_id"] = this.staff_id.value;
        params["staff_history_id"] = this.staff_history_id.value;
        params["facility_id"] = this.facility_id;
        params["name"] = this.name.value;
        params["name_kana"] = this.name_kana.value;
        params["gender"] = this.gender.value;
        params["employment_status"] = this.employment_status.value;
        params["employment_class"] = this.employment_class.value;
        params["working_status"] = this.working_status.value;
        params["location"] = this.location.value;
        params["phone_number"] = this.phone_number.value;
        params["emergency_contact_information"] =
            this.emergency_contact_information.value;
        console.log(params);

        return await CustomAjax.send(
            "POST",
            "/group_home/staff_info/staff/save",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params,
            "callbackSave",
            this
        );
    }
    /**
     * 男か女を返す
     * @param {int} gender
     * @return {string}
     */
    getGenderTXT(gender) {
        return gender === 1 ? "男" : "女";
    }
    /**
     * 常勤か非常勤を返す
     * @param {int} employment_status
     * @return {string}
     */
    getEmploymentStatusTXT(employment_status) {
        return employment_status === 1 ? "常勤" : "非常勤";
    }
    /**
     * 正社員かパートを返す
     * @param {int} employment_class
     * @return {string}
     */
    getEmploymentClassTXT(employment_class) {
        return employment_class === 1 ? "正社員" : "パート";
    }
    /**
     * 専従か兼務を返す
     * @param {int} working_status
     * @return {string}
     */
    getWorkingStatusTXT(working_status) {
        return working_status === 1 ? "専従" : "兼務";
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
     * @param {object} staff_history
     * @param {int} target_history_id
     * @return {Promise}
     */
    async makeStaffHistory(staff_history, target_history_id) {
        // 履歴リストを初期化
        this.historyTBody.textContent = null;
        // 履歴レコードの選択状態をクリアする
        this.clearRecordSelectionState();
        //idが大きい順にソート
        staff_history.sort(function (a, b) {
            return b.id - a.id;
        });
        staff_history.forEach(function (staff) {
            let record = document.createElement("tr");
            record.setAttribute("data-staff-history-id", staff.id);
            if (target_history_id == staff.id) {
                record.classList.add("stf_select_record");
                this.selectedRecord = record;
            }
            let td = document.createElement("td");
            td.textContent = staff.name;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.getEmploymentStatusTXT(
                staff.employment_status
            );
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.getEmploymentClassTXT(staff.employment_class);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = this.getWorkingStatusTXT(staff.working_status);
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = staff.location;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = staff.emergency_contact_information;
            record.appendChild(td);
            td = document.createElement("td");
            td.textContent = staff.updated_at;
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
                let id = record.getAttribute("data-staff-history-id");
                this.setInputData(this.staff, id);
            });
            this.historyTBody.appendChild(record);
        }, this);
    }
    checkNull(checkdata){
        if (!checkdata){
            return "";
        }
        return checkdata;
    }
    /**
     * 入力値を設定する
     * @param {Object} staff
     * @param {Number} staff_history_id
     */
    setInputData(staff, staff_history_id) {
        this.passwordChanged.value = false;
        this.password.value = "";
        let found;
        if (staff_history_id) {
            found = staff.history.find((staff) => staff.id == staff_history_id);
        } else {
            found = staff.history.find(
                (staff) => staff.id == this.staff_history_id.value
            );
        }
        this.staff_history_id.value = found.id;
        this.employee_number.value = this.checkNull(staff.employee_number);
        if (staff.account){
            this.login_id.innerHTML = this.checkNull(staff.account.employee_number);
        }
        this.date_of_employment.value = this.dateFormat(
            staff.date_of_employment
        );

        this.name.value = this.checkNull(found.name);
        this.name_kana.value = this.checkNull(found.name_kana);
        document.querySelector(
            'input[name="stf_gender"][value="' + found.gender + '"]'
        ).checked = true;
        document.querySelector(
            'input[name="stf_employment_status"][value="' +
                found.employment_status +
                '"]'
        ).checked = true;
        document.querySelector(
            'input[name="stf_employment_class"][value="' +
                found.employment_class +
                '"]'
        ).checked = true;
        document.querySelector(
            'input[name="stf_working_status"][value="' +
                found.working_status +
                '"]'
        ).checked = true;
        this.location.value = this.checkNull(found.location);
        this.phone_number.value = this.checkNull(found.phone_number);
        this.emergency_contact_information.value = this.checkNull(found.emergency_contact_information);
    }
    async callbackGetStaff(staff) {
        this.staff = staff;
        //履歴を生成
        this.makeStaffHistory(staff.history, this.staff_history_id.value);

        //設定値を復元
        this.setInputData(staff, this.staff_history_id.value);
    }
    /**
     * スタッフ一覧で選択した値をセットする
     * @param {Object} param key: saff_history_id, staff_id, staffName
     *     値を消すときは明示的にnullをセットする
     * @returns {Promise}
     */
    async setParam(param) {
        this.staff_history_id.value = param.staff_history_id;
        this.staff_id.value = param.staff_id;
        return CustomAjax.send(
            "GET",
            "/group_home/staff_info/staff/get_staff_history?staff_id=" +
                param.staff_id,
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            [],
            "callbackGetStaff",
            this
        );
    }

    /**
     * @param {bool} status 表示のブーリアン値
     */
    setActive(status) {
        //
    }
}
