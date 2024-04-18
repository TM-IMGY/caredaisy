
'use strict';

import ChangeMonitoring from '../change_monitoring.js';
import DataExportPullDownMenu from "./data_export_pulldown_menu.js";
import FacilityPulldown from "./facility_pulldown.js";
import FacilityUserTable from "./facility_user_table.js";
import NationalHealth from "./national_health.js";
import ReBillingBtn from './re_billing_btn.js';
import ResultInformationView from './result_information_view.js';
import ResultRegistration from "./service_result_table.js";
import Stayout from "./stayOut.js";
import TabManager from "./tab_manager.js";
import Uninsured from "./uninsured.js";
import YearMonthPulldown from "./year_month_pulldown.js";
import FacilityUserInfoHeader from '../../lib/facility_user_info_header.js';

document.addEventListener('DOMContentLoaded', async () => {
    let tabManager = new TabManager();

    let facilityUserInfoHeader = new FacilityUserInfoHeader();

    // 事業所プルダウン。
    let facilityPulldown = new FacilityPulldown();
    await facilityPulldown.init();

    // 対象年月プルダウン。
    let ymPulldown = new YearMonthPulldown();
    let ym = ymPulldown.getSelectedValue();

    // 施設利用者テーブル。
    let facilityUserTable = new FacilityUserTable();
    facilityUserTable.setFacility(facilityPulldown.getSelectedValue());
    facilityUserTable.setYm(ym.year, ym.month);
    await facilityUserTable.reload();

    // 再請求ボタン。
    let reBillingBtn = new ReBillingBtn();
    reBillingBtn.addNotification(facilityUserTable.setReBillingMode.bind(facilityUserTable));
    // TODO: addNotificationと分離する意味がない。
    ymPulldown.addConfirm(reBillingBtn.confirmCancelReBillingMode.bind(reBillingBtn));

    // 外泊日登録タブ。
    let stayOut = new Stayout(facilityPulldown.getSelectedValue());

    // 実績登録タブ。
    let resultRegistration = new ResultRegistration();
    // 事業所情報をセットする。
    resultRegistration.setFacilityId(facilityPulldown.getSelectedValue(), facilityPulldown.getSelectedText());
    // 対象年月情報をセットする。
    resultRegistration.setYm(ym.year, ym.month);

    // 国保連請求タブ
    let nh = new NationalHealth(ym.year, ym.month, facilityPulldown.getSelectedValue());

    // データを出力。
    let dataExportPullDownMenu = new DataExportPullDownMenu(
        facilityPulldown.getSelectedValue(),
        ym.year,
        ym.month,
        facilityUserTable.getUserCnt(),
        nh
    );
    dataExportPullDownMenu.setFacilityUsers(facilityUserTable.getFacilityUsers());
    ymPulldown.addNotification(dataExportPullDownMenu.updateYearMonth.bind(dataExportPullDownMenu));
    facilityUserTable.addNotification(dataExportPullDownMenu.setFacilityUserData.bind(dataExportPullDownMenu));

// 機能追加ごとに行う記述 start
    // 保険外請求
    let ui = new Uninsured(ym.year,ym.month,facilityPulldown.getSelectedValue());
    ui.init(ym.year,ym.month);
    ui.addNotification(facilityUserTable.reload.bind(facilityUserTable));
    tabManager.addNotification(ui.elementID,ui.setActive.bind(ui));
    facilityUserTable.addNotification(ui.setParam.bind(ui));
    ymPulldown.addNotification(ui.setParam.bind(ui));
// 機能追加ごとに行う記述 end

    // 前画面に利用者一覧がない場合選択された利用者をクリア
    let matchUrl = document.referrer.match(/transmit_info|facility_info|top/);
    if(matchUrl) sessionStorage.clear();

    // 実績情報画面全体の管理クラス。
    new ResultInformationView(facilityUserTable, nh, resultRegistration, stayOut, tabManager, ymPulldown, facilityUserInfoHeader);

    new ChangeMonitoring();
});
