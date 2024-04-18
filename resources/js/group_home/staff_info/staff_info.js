"use strict";
import TabManager from "./tab_manager.js";
import FacilityPulldown from "./facility_pulldown.js";
import StaffTable from "./staff_table.js";

import Staff from "./staff.js";
import Auths from "./auths.js";
import ChangeMonitoring from '../change_monitoring.js'

document.addEventListener("DOMContentLoaded", async () => {
    // タブマネージャー
    let tabManager = new TabManager();

    // 事業所選択プルダウン
    let facilityPulldown = new FacilityPulldown();
    await facilityPulldown.init();

    // スタッフテーブル
    let staffTable = new StaffTable();
    // ymPulldown.addNotification(staffTable.setParam.bind(staffTable));
    await staffTable.setParam({
        facility_id: facilityPulldown.getSelectedValue(),
    });

    // 入社情報
    let staff = new Staff(facilityPulldown.getSelectedValue());
    tabManager.addNotification(staff.elementID, staff.setActive.bind(staff));
    staffTable.addNotification(staff.setParam.bind(staff));
    staff.addNotification(staffTable.setStaffParam.bind(staffTable));

    // 権限設定
    let auths = new Auths(facilityPulldown.getSelectedValue());
    tabManager.addNotification(auths.elementID, auths.setActive.bind(auths));
    staffTable.addNotification(auths.setParam.bind(auths));

    // サブタブの初期選択
    tabManager.subTabList[0].click();
    
    let cm = new ChangeMonitoring();
});
