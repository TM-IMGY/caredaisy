export default class TabManager {
    constructor() {
        let subTab = document.getElementById("tm_sub_tab");

        this.selectedSubTab = null;
        this.selectedContents = null;
        this.notification = {};
        this.subTabList = Array.from(subTab.children);

        // サブタブにクリックイベントをセット
        this.subTabList.forEach((tab) =>
            tab.addEventListener("click", this.clickSubTab.bind(this, tab))
        );
    }

    // サブタブクリックイベント
    async clickSubTab(tab) {
        this.selectedSubTab?.classList.remove("tm_subtab_active");
        this.selectedContents?.classList.add("tm_contents_hidden");

        let oldContentsID =
            this.selectedSubTab?.getAttribute("data-contents-id");
        this.notification[oldContentsID] &&
            (await this.notification[oldContentsID](false));

        tab.classList.add("tm_subtab_active");
        let contentsID = tab.getAttribute("data-contents-id");
        let contents = document.getElementById(contentsID);
        contents.classList.remove("tm_contents_hidden");

        this.selectedSubTab = tab;
        this.selectedContents = contents;

        this.notification[contentsID] &&
            (await this.notification[contentsID](true));
    }

    /**
     * 値変更時の通知先を追加
     * @param {string} id 通知先のid
     * @param {Function} callBack 通知先として呼ぶコールバック関数
     */
    addNotification(id, callBack) {
        this.notification[id] = callBack;
    }
}
