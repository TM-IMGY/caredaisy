"use strict";
import TabManager from "./tab_manager.js";
import Transmit from "./transmit.js";
import TransmitList from "./transmit_list.js";
import DocumentList from "./document_list.js";
import FacilityPulldown from "../user_info/facility_pulldown.js";
import Document from "./document.js";

document.addEventListener("DOMContentLoaded", async () => {
    // タブマネージャー
    let tabManager = new TabManager();

    // 事業所選択プルダウン
    let facilityPulldown = new FacilityPulldown();
    await facilityPulldown.init();

    //伝送リスト
    let transmitList = new TransmitList(facilityPulldown.getSelectedValue());
    let list = await transmitList.init();

    // 伝送請求
    let transmit = new Transmit(
        facilityPulldown.getSelectedValue(),
        list,
        facilityPulldown.getSelectedFacilityNumber()
    );
    tabManager.addNotification(
        transmit.elementID,
        transmit.setActive.bind(transmit)
    );
    //伝送リスト
    let documentList = new DocumentList(facilityPulldown.getSelectedValue());
    let doclist = await documentList.init();

    // 通知文書
    let document = new Document(
        facilityPulldown.getSelectedValue(),
        doclist,
        facilityPulldown.getSelectedFacilityNumber()
    );
    tabManager.addNotification(
        document.elementID,
        document.setActive.bind(document)
    );

    // サブタブの初期選択
    tabManager.subTabList[0].click();
});
