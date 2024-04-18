'use strict';

/**
 * メインメソッド。ここで依存性を解決する。
 */

import Approval from "./approval.js";
import BasicInfo from "./basic_info.js";
import Benefit from "./benefit.js";
import FacilityPulldown from "./facility_pulldown.js"
import FacilityUserTable from "./facility_user_table.js"
import TabManager from "./tab_manager.js"
import Independence from "./independence.js";
import PublicExpenditure from "./public_expenditure.js";
import Service from "./service.js";
import BillingAddress from "./billing_address.js";
import ChangeMonitoring from '../change_monitoring.js'
import InjuryAndIllnessName from "./injury_and_illness_name.js";
import BasicAbstract from "./basic_abstract.js";
import BurdenLimit from "./burden_limit.js";

document.addEventListener('DOMContentLoaded',async()=>{
    // タブマネージャー
    let tabManager = new TabManager();

    // 事業所選択プルダウン
    let facilityPulldown = new FacilityPulldown();
    await facilityPulldown.init();

    let facilityUserTable = new FacilityUserTable();
    facilityUserTable.syncServer({facilityID:facilityPulldown.getSelectedValue()});

    // 基本情報
    let basicInfo = new BasicInfo(facilityPulldown.getSelectedValue());
    tabManager.addNotification(basicInfo.elementID,basicInfo.setActive.bind(basicInfo));
    facilityUserTable.addNotification(basicInfo.setFacilityUser.bind(basicInfo));
    basicInfo.addNotification(facilityUserTable.clearSelect.bind(facilityUserTable));

    // サービス
    let service = new Service();
    tabManager.addNotification(service.elementID,service.setActive.bind(service));
    facilityUserTable.addNotification(service.setFacilityUser.bind(service));

    // 認定情報
    let approval = new Approval(facilityPulldown.getSelectedValue());
    tabManager.addNotification(approval.elementID,approval.setActive.bind(approval));
    facilityUserTable.addNotification(approval.setFacilityUser.bind(approval));
    approval.addFacilityUserTableSyncServer(facilityUserTable.syncServer.bind(facilityUserTable));

    // 自立度
    let independence = new Independence();
    tabManager.addNotification(independence.elementID,independence.setActive.bind(independence));
    facilityUserTable.addNotification(independence.setFacilityUser.bind(independence));

    // 公費情報
    let publicExpenditure = new PublicExpenditure(facilityPulldown.getSelectedValue());
    tabManager.addNotification(publicExpenditure.elementID,publicExpenditure.setActive.bind(publicExpenditure));
    facilityUserTable.addNotification(publicExpenditure.setFacilityUser.bind(publicExpenditure));

    // 給付率
    let benefit = new Benefit();
    tabManager.addNotification(benefit.elementID,benefit.setActive.bind(benefit));
    facilityUserTable.addNotification(benefit.setFacilityUser.bind(benefit));

    // 請求書情報
    let billingAddress = new BillingAddress(facilityPulldown.getSelectedValue());
    tabManager.addNotification(billingAddress.elementID,billingAddress.setActive.bind(billingAddress));
    facilityUserTable.addNotification(billingAddress.setFacilityUser.bind(billingAddress));

    // 傷病名
    let injuryAndIllnessName = new InjuryAndIllnessName(facilityPulldown.getSelectedValue());
    tabManager.addNotification(injuryAndIllnessName.elementID,injuryAndIllnessName.setActive.bind(injuryAndIllnessName));
    facilityUserTable.addNotification(injuryAndIllnessName.setFacilityUserInfo.bind(injuryAndIllnessName));

    // 基本摘要
    let basicAbstract = new BasicAbstract(facilityPulldown.getSelectedValue());
    tabManager.addNotification(basicAbstract.elementID,basicAbstract.setActive.bind(basicAbstract));
    facilityUserTable.addNotification(basicAbstract.setFacilityUserInfo.bind(basicAbstract));

    facilityUserTable.addNotification(tabManager.tabActivation.bind(tabManager));

    // 負担限度額
    let burdenLimit = new BurdenLimit();
    tabManager.addNotification(burdenLimit.elementID,burdenLimit.setActive.bind(burdenLimit));
    facilityUserTable.addNotification(burdenLimit.setFacilityUser.bind(burdenLimit));

    // サブタブの初期選択
    tabManager.subTabList[0].click();

    let cm = new ChangeMonitoring();

    // 前画面に利用者一覧がない場合選択された利用者をクリア
    let matchUrl = document.referrer.match(/transmit_info|facility_info|top/);
    if(matchUrl) sessionStorage.clear();
});
