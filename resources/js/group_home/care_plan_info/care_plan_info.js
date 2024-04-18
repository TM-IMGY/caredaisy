'use strict';

/**
 * メインメソッド。ここで依存性を解決する。
 */

import FacilityPulldown from "./facility_pulldown.js"
import FacilityUserTable from "./facility_user_table.js"
import TabManager from "./tab_manager.js"
import ServicePlan1 from "./service_plan1.js";
import ServicePlan2 from "./service_plan2.js";
import ChangeMonitoring from '../change_monitoring.js'
import ServicePlan3 from "./service_plan3.js";

document.addEventListener('DOMContentLoaded',async()=>{
    // タブマネージャー
    let tabManager = new TabManager();

    // 事業所選択プルダウン
    let facilityPulldown = new FacilityPulldown();
    await facilityPulldown.init();

    let facilityUserTable = new FacilityUserTable();
    facilityUserTable.syncServer({facilityID:facilityPulldown.getSelectedValue()});

    // サービス計画書1
    let servicePlan1 = new ServicePlan1();
    tabManager.addNotification(servicePlan1.elementID,servicePlan1.setActive.bind(servicePlan1));
    facilityUserTable.addNotification(servicePlan1.setFacilityUser.bind(servicePlan1));
    servicePlan1.addFacilityUserTableSyncServer(facilityUserTable.syncServer.bind(facilityUserTable));

    // サービス計画書2
    let servicePlan2 = new ServicePlan2();
    tabManager.addNotification(servicePlan2.elementID,servicePlan2.setActive.bind(servicePlan2));
    facilityUserTable.addNotification(servicePlan2.setFacilityUser.bind(servicePlan2));
    servicePlan1.setServicePlan2(servicePlan2);
    servicePlan2.setServicePlan1(servicePlan1);

    // サービス計画書3
    let servicePlan3 = new ServicePlan3();
    // tabManager.addNotification(servicePlan3.elementID,servicePlan3.setActive.bind(servicePlan3));
    facilityUserTable.addNotification(servicePlan3.setFacilityUser.bind(servicePlan3));
    servicePlan1.setServicePlan3(servicePlan3);
    servicePlan3.setServicePlan1(servicePlan1);
    servicePlan3.setFacilityId(facilityPulldown.getSelectedValue());

    // サブタブの初期選択
    tabManager.subTabList[0].click();

    let cm = new ChangeMonitoring();

    // 前画面に利用者一覧がない場合選択された利用者をクリア
    let matchUrl = document.referrer.match(/transmit_info|facility_info|top/);
    if(matchUrl) sessionStorage.clear();
});
