import CSRF_TOKEN from "../../lib/csrf_token.js";
import CustomAjax from "../../lib/custom_ajax.js";

class Pulldown {
    constructor(elementID, url) {
        this.element = document.getElementById(elementID);
        this.url = url;
    }

    /**
     * サーバーからユーザーが参照可能な情報を取得して返す。
     * @returns {?Promise}
     */
    async getList() {
        // TODO: 一時対応。処理が入り組んでいるので本実装のタイミングで修正が必要。
        if(this.url === '/group_home/service/facility'){
            let res = await CustomAjax.get(this.url);
            let data = await res.json();
            return data;
        } else {
            return await CustomAjax.post(this.url, {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": CSRF_TOKEN,
            });
        }
    }

    /**
     * 選択されている値を取得
     * @returns {string}
     */
    getSelectedValue() {
        return Number(this.element.value);
    }
    /**
     * 選択されている値の表示上のテキストを取得
     * @returns {string}
     */
    getSelectedText() {
        return this.element.textContent;
    }

    /**
     * 選択肢を作成する
     * @returns {element}
     */
    createOption(ID, name) {
        let o = document.createElement("option");
        o.value = ID;
        o.textContent = name;
        return o;
    }
}

export class CorporationPulldown extends Pulldown {
    constructor(elementID, url) {
        super(elementID, url);
    }
    /**
     * サーバーから事業所情報を取得して反映する
     * @param {Function} callback
     * @param {Object} ipd -- InstitutionPulldownインスタンス
     * @returns {Promise}
     */
    async syncServer(callback, ipd) {
        let fList = await this.getList();
        if (fList === null) {
            return;
        }

        this.element.textContent = null;
        // プルダウンにセット
        // let option = this.createOption(0, "選択して下さい");
        // this.element.appendChild(option);
        fList.forEach((lst) => {
            let option = this.createOption(lst.id, lst.name);
            this.element.appendChild(option);
        });
        callback(this.getSelectedValue(), ipd);
    }
}
export class InstitutionPulldown extends Pulldown {
    constructor(elementID, url) {
        super(elementID, url);
    }
    /**
     * サーバーからユーザーが参照可能な情報を取得して返す
     * @returns {?Promise}
     */
    async getList(json) {
        return await CustomAjax.post(
            this.url,
            {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": CSRF_TOKEN,
            },
            json
        );
    }
    /**
     * サーバーから事業所情報を取得して反映する
     * @returns {Promise}
     */
    async syncServer(json) {
        let fList = await this.getList(json);
        if (fList === null) {
            return;
        }

        this.element.textContent = null;
        // プルダウンにセット
        // let option = this.createOption(0, "選択して下さい");
        // this.element.appendChild(option);
        fList.forEach((lst) => {
            let option = this.createOption(lst.id, lst.name);
            this.element.appendChild(option);
        });
    }
}
export class FacilityPulldown extends Pulldown {
    constructor(elementID, url) {
        super(elementID, url);
    }
    /**
     * サーバーから事業所情報を取得して反映する
     * @returns {Promise}
     */
    async syncServer() {
        let fList = await this.getList();
        if (fList === null) {
            return;
        }

        this.element.textContent = null;

        // プルダウンにセット
        // let option = this.createOption(0, "選択して下さい");
        // this.element.appendChild(option);
        fList.forEach((facility) => {
            let option = this.createOption(
                facility.facility_id,
                facility.facility_name_kanji
            );
            this.element.appendChild(option);
        });
    }
}
