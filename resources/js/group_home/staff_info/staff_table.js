import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";
import StaffInfoHeader from "../../lib/staff_info_header.js";

export default class StaffTable {
    constructor() {
        this.element = document.getElementById("staff_tbody");
        this.staffs = "";
        this.selected_staff_history_id = "";
        this.notificationAddresses = [];
        this.selectedRecord = null;
        this.staffInfoHeader = new StaffInfoHeader();
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

    /**
     * レコードのクリックイベント
     * @param {Element} record
     */
    async clickRecord(record) {
        // 選択されているレコードの状態を解除する
        if (this.selectedRecord) {
            this.selectedRecord.classList.remove("stf_table_selected_record");
            this.selectedRecord.children[0].classList.remove(
                "stf_table_selected_cell"
            );
        }

        // レコードを選択状態にする
        record.classList.add("stf_table_selected_record");
        record.children[0].classList.add("stf_table_selected_cell");

        // 選択されたレコードに変更があった場合
        if (this.selectedRecord !== record) {
            let staff_history_id = Number(
                record.getAttribute("data-staff-history-id")
            );
            let staff_id = Number(record.getAttribute("data-staff-id"));
            let staffName = record.getAttribute("data-staff-name");
            let authExtentID = record.getAttribute("data-auth-extent-id");
            this.selectedRecord = record;
            await this.notification({
                staff_history_id: staff_history_id,
                staff_id: staff_id,
                staffName: staffName,
                auth_extent_id: authExtentID,
            });
            this.staffInfoHeader.setStaffInfo({
                staff_history_id: staff_history_id,
                staff_id: staff_id,
            });
        }
    }
    /**
     * レコードを作成する
     * @param {Number} staff_id
     * @param {string} name
     * @returns {Element}
     */
    createRecord(staff_history_id, staff_id, name, auth_extent_id) {
        // レコードを作成する
        let record = document.createElement("tr");
        record.classList.add("staff_list_tr");
        record.setAttribute("data-staff-history-id", staff_history_id);
        record.setAttribute("data-auth-extent-id", auth_extent_id);
        record.setAttribute("data-staff-id", staff_id);
        record.setAttribute("data-staff-name", name);

        // 名前セルを作成する
        let nameCell = document.createElement("td");
        nameCell.textContent = name;
        nameCell.classList.add("staff_list_td");

        record.appendChild(nameCell);
        record.addEventListener("click", this.clickRecord.bind(this, record));

        if (this.selected_staff_history_id == staff_history_id) {
            // レコードを選択状態にする
            record.classList.add("stf_table_selected_record");
            record.children[0].classList.add("stf_table_selected_cell");
            this.selectedRecord = record;
        }
        return record;
    }

    /**
     * レコードを削除する
     * @returns {void}
     */
    deleteRecord() {
        this.element.textContent = null;
        this.selectedRecord = null;
    }

    /**
     * スタッフテーブルを描画する
     * @returns {Promise}
     */
    async drawStaffTable() {
        // レコードを削除する
        this.deleteRecord();

        // レコードを作成して追加する
        this.staffs.forEach((user) => {
            let record = this.createRecord(
                user.last_id,
                user.staff_id,
                user.name,
                user.last_extent_id
            );
            this.element.appendChild(record);
        });

        this.selected_staff_history_id = null;

        // 施設利用者の選択状態を復元する
        let records = this.element.children;
        let isFound = false;
        for (let i = 0, len = records.length; i < len; i++) {
            if (
                records[i].getAttribute("data-facility-user-id") ==
                facilityUserId
            ) {
                this.clickRecord(records[i]);
                isFound = true;
                break;
            }
        }
        // 復元する施設利用者が見つからなかった場合は通知する
        if (!isFound) {
            await this.notification({ facilityUserID: null, userName: null });
        }
    }
    /**
     * スタッフをセットする
     * @param {Object} staffs
     * @returns {Promise}
     */
    async setStaffTable(staffs) {
        this.staffs = staffs;
        // 施設利用者情報を姓名でソートする
        // this.sortFacilityUsers();
        await this.drawStaffTable();
    }
    /**
     * 取得リクエストのパラメーターを返す
     * @returns {Object}
     */
    getRequestStaffParams() {
        return "?facility_id=" + this.facility_id;
    }

    /**
     * サーバーにスタッフ情報をリクエストする
     * @returns {Promise}
     */
    async requestStaff() {
        return await CustomAjax.send(
            "GET",
            "/group_home/staff_info/staff/get_staff_list" +
                this.getRequestStaffParams(),
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            [],
            "setStaffTable",
            this
        );
    }
    /**
     * パラメーターをセットする
     * @param {Object} params key: facility_id
     *   値を消すときは明示的にnullをセットする
     *   facilityIDはグループホームでは変更されない
     * @returns {Promise}
     */
    async setStaffParam(params) {
        this.selected_staff_history_id = params.staff_history_id;
        this.facility_id =
            "facility_id" in params ? params.facility_id : this.facility_id;
        await this.requestStaff();
    }
    /**
     * パラメーターをセットする
     * @param {Object} params key: facility_id
     *   値を消すときは明示的にnullをセットする
     *   facilityIDはグループホームでは変更されない
     * @returns {Promise}
     */
    async setParam(params) {
        this.facility_id =
            "facility_id" in params ? params.facility_id : this.facility_id;
        await this.syncWithServer();
    }
    /**
     * サーバーと同期する
     * @returns {Promise}
     */
    async syncWithServer() {
        // サーバーにスタッフ情報をリクエストする
        await this.requestStaff();
    }
}
