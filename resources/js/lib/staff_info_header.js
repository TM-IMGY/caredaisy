import CSRF_TOKEN from "./csrf_token.js";
import CustomAjax from "./custom_ajax.js";

export default class StaffInfoHeader {
    constructor() {
        this.REQUEST_HEADER = {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF_TOKEN,
        };
        this.staff_id = null;
        this.staff_history_id = null;
    }

    clearUser() {
        document.querySelectorAll(".facility_user_info_text").forEach((e) => {
            e.textContent = "";
        });
    }

    /**
     * ルールで決められた日付の書式を返す
     * @param {String} dateStr
     * @returns {String}
     */
    getRuleBasedDateFormat(dateStr) {
        let date = new Date(dateStr);
        // 利用者情報画面の場合は0埋めをする。
        if (location.href.match(/user_info/g)) {
            let year = date.getFullYear();
            let month = (date.getMonth() + 1).toString().padStart(2, "0");
            let day = date.getDate().toString().padStart(2, "0");
            return year + "/" + month + "/" + day;
        }
        return date.toLocaleDateString(); // yyyy/mm/dd
    }

    /**
     * @return {Promise}
     */
    async requestData() {
        await CustomAjax.send(
            "GET",
            "/group_home/service/staff/header/get_header" +
                this.getRequestGetInfoTableParams(),
            { "X-CSRF-TOKEN": CSRF_TOKEN },
            [],
            "setInfoTable",
            this
        );
    }

    getRequestGetInfoTableParams() {
        return (
            "?staff_history_id=" +
            this.staff_history_id +
            "&staff_id=" +
            this.staff_id
        );
    }

    /**
     * @param {Object} user {facilityUserID: string, userName: string}
     */
    async setStaffInfo(staff) {
        this.clearUser();
        this.staff_id = staff.staff_id;
        this.staff_history_id = staff.staff_history_id;

        if (staff === null) {
            return;
        }
        await this.requestData();
    }

    async setInfoTable(data) {
        if (data === null) {
            return;
        }
        let heads = document.querySelectorAll(".facility_user_info_header");
        heads.forEach((head) => {
            head.querySelectorAll(".staff_info_header_employee_number").forEach(
                (d) => {
                    d.textContent = data.staff.employee_number;
                }
            );
            head.querySelectorAll(".staff_info_header_name").forEach((d) => {
                d.textContent = data.staff_history.name;
            });
            head.querySelectorAll(".staff_info_header_gender").forEach((d) => {
                d.textContent =
                    data.staff_history.gender === 1 ? "男性" : "女性";
            });
            head.querySelectorAll(
                ".staff_info_header_date_of_employment"
            ).forEach((d) => {
                d.textContent = this.getRuleBasedDateFormat(
                    data.staff.date_of_employment
                );
            });
        });
    }
}
