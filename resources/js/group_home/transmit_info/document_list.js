import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";
import SearchScope from "./search_scope.js";

export default class DocumentList {
    constructor(facilityID) {
        this.facility_id = facilityID;
        this.search_scope = new SearchScope();
    }

    /**
     * 初期化。サーバーから伝送一覧を取得する
     * @returns {Promise}}
     */
    async init() {
        let params = {};
        params.facility_id = this.facility_id;
        params.from_date = this.search_scope.OneYearAgo.replace(/-/g,'/') + "/01";
        params.to_date = this.search_scope.Today.replace(/-/g,'/') + "/01";
        return await CustomAjax.post(
            "/group_home/transmit_info/transmit/get_document",
            { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            params
        );
    }
}
