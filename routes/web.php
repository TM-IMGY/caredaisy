<?php

Auth::routes(['register' => false,]);

Route::redirect('/', '/login');

// ログイン後にアクセスできる画面はキャッシュさせない
Route::middleware('nocache')->group(function () {
    /**
     * トップページ
     * @author ttakenaka
     */
    Route::prefix('top')->middleware('auth')->group(function () {
        Route::get('/', 'TopController@index')->name('top');
        Route::get('/operation_manual_download', 'TopController@downloadOperationManual')->name('operation_manual_download');
        Route::get('/transmission_manual_download', 'TopController@downloadTransmissionManual')->name('transmission_manual_download');
    });

    Route::prefix('admin')->middleware('admin_auth')->group(function () {
        Route::get('/', 'AdminAuthController@index')->name('external_capture');
        Route::get('/get_facility_list', 'ExternalCaptureController@getFacilityList');
        Route::get('/external_datas', 'ExternalCaptureController@getExternalDatas');
        Route::get('/csv/external_user_id_associations', 'ExternalCaptureController@getExternalRelationCsv');
        Route::post('/get_facility_id', 'ExternalCaptureController@getFacilityId');
        Route::post('/service_type_list', 'ExternalCaptureController@getServiceTypeList');
        Route::post('/csv_regist', 'ExternalCaptureController@csvRegist')->name('csv_regist');
    });

    Route::prefix('group_home/own_uninsurance_bill')->namespace('GroupHome\OwnUninsuranceBill')->middleware('auth')->group(function () {
        Route::get('ledger_sheets', 'LedgerSheetController@index')->name('own_non_insurance_bill_ledger_sheets');
    });

    // グループホーム(ケアプラン画面)
    Route::prefix('group_home/care_plan_info')->namespace("GroupHome\CarePlanInfo")->middleware('auth')->group(function () {
        //ケアプラン画面ページ
        Route::get('/', 'CarePlanInfoController@index')->name('group_home.care_plan_info');
        Route::get('/service_plan', 'CarePlanInfoController@getPlanEndDates');

        //計画書1
        Route::get('/get/care_plan_period', 'ServicePlanController@getCarePlanPeriod');
        Route::post('/service_plan1/get_fPlan_input', 'ServicePlanController@getFacilityFristPlanInput');
        Route::post('/service_plan1/user_information', 'ServicePlanController@getUserInformation');
        Route::post('/service_plan1/get_history', 'ServicePlanController@getPlan1History');
        Route::post('/service_plan1/save', 'ServicePlanController@save');
        Route::get('/service_plan1/pdf/preview', 'ServicePlanController@pdf')->name('group_home.service_plan1_pdf_preview');
        Route::get('/service_plan1/pdf/consecutive', 'ServicePlanController@consecutivePdf')->name('group_home.service_plan_pdf_consecutive');
        Route::post('service_plan1/existence', 'ServicePlanController@SecondServicePlanExistence');
        Route::post('service_plan1/get_delivery_plan', 'ServicePlanController@getIssuedData');
        Route::get('/check_service', 'ServicePlanController@checkEffectiveService');
        Route::get('/get_service', 'ServicePlanController@getEffectiveService');
        Route::get('/get_user_start_date', 'ServicePlanController@getUserStartDate');

        //計画書2
        Route::get('service_plan2/pdf', 'SecondServicePlanController@outputServicePlan2Pdf');
        Route::post('service_plan/get', 'ServicePlanListController@get')->name('service_plan.get');
        Route::post('service_plan2/get', 'SecondServicePlanController@get')->name('second_service_plan.get');
        Route::post('service_plan2/insert', 'SecondServicePlanController@insertRecord')->name('second_service_plan.insertRecord');
        Route::post('service_plan2/update', 'SecondServicePlanController@update')->name('second_service_plan.update');
        Route::post('/service_plan2/get_history', 'ServicePlanController@getPlan1HistorySelected');
        Route::delete('service_plan2/delete', 'SecondServicePlanController@delete')->name('second_service_plan.delete');

        //計画書2
        Route::get('schedule/{service_plan}', 'ThirdServicePlanController@schedule')->name('thired_service_plan.schedule');
        Route::post('schedule/{service_plan}', 'ThirdServicePlanController@update')->name('thired_service_plan.schedule.update');
        Route::get('weekly_service_master', 'ThirdServicePlanController@weeklyServiceMaster')->name('thired_service_plan.weeklyServiceMaster');
        Route::get('main_work_service_master', 'ThirdServicePlanController@mainWorkServiceMaster')->name('thired_service_plan.mainWorkServiceMaster');
        Route::get('other_service_master', 'ThirdServicePlanController@otherServiceMaster')->name('thired_service_plan.mainWorkServiceMaster');
        Route::get('service_plan3/pdf/{service_plan}', 'ThirdServicePlanController@pdf')->name('thired_service_plan.pdf');
    });

    // 実績情報画面
    Route::prefix('group_home/result_info')->namespace("GroupHome\ResultInfo")->middleware('auth')->group(function () {
        // 実績情報画面ページ
        Route::get('/', 'ResultInfoController@index')->name('group_home.result_info');

        // 施設利用者が事業所から提供を受けているサービス種別を返す。
        Route::get('service_type', 'ResultInfoController@getServiceType');
        Route::post('transmission_mode', 'ResultInfoController@getTransmissionMode');

        // 指定した利用者の中から国保連請求・保険外請求のいずれかが承認済みである利用者を取得する
        Route::post('getApprovedUsers', 'ResultInfoController@getApprovedUsers');

        // 外泊日登録
        Route::get("stay_out/user_info", 'StayOutController@userInfo');
        Route::get("stay_out/stay_out_detail", 'StayOutController@stayOutDetail');
        Route::post("stay_out/save", 'StayOutController@save');
        Route::delete("stay_out/delete", 'StayOutController@delete');

        // 保険外請求
        Route::get("uninsured/user_info", 'UninsuredController@userInfo');
        Route::get("uninsured/list", 'UninsuredController@list');
        Route::get("uninsured/item_list", 'UninsuredController@itemList');
        Route::post("uninsured/agreement", 'UninsuredController@agreement');
        Route::get("uninsured/check_agreement", 'UninsuredController@checkAgreement');
        Route::get("uninsured/save_cell", 'UninsuredController@saveCell');
        Route::get("uninsured/save_row", 'UninsuredController@saveRow');
        Route::get("uninsured/save_sort", 'UninsuredController@saveSort');
        Route::post("uninsured/save_item", 'UninsuredController@saveItem');
        Route::delete("uninsured/delete", 'UninsuredController@delete');
        Route::get("uninsured/get_user_public_info", 'UninsuredController@getUserPublicInfo');
    });

    // グループホーム(利用者情報画面)
    Route::prefix('group_home/user_info')->namespace("GroupHome\UserInfo")->middleware('auth')->group(function () {
        // 利用者情報画面ページ
        Route::get('/', 'UserInfoController@index')->name('group_home.user_info');
        Route::get('/get_facility_use_service', 'UserInfoController@getFacilityUseService');
        // サービス ttakenaka
        Route::post('/service/ajax', 'UserInfoController@service_ajax');
        Route::post('/popup_service/ajax', 'UserInfoController@popup_service_ajax');
        Route::post('/popup_updata_service/ajax', 'UserInfoController@popup_updata_service_ajax');
        Route::post('/popup_facility_service/ajax', 'UserInfoController@popup_facility_service_ajax');
        Route::post('/service/store', 'UserInfoController@service_store');
        Route::get('/service/start_date', 'UserInfoController@startDate');
        Route::get('/service/hisotry_service_info', 'UserInfoController@getHistoryServiceInfo');
        // 認定情報 ttakenaka
        Route::get('/approval/values_check_result', 'UserInfoController@getApprovalValuesCheckResult');
        Route::post('/approval/ajax', 'UserInfoController@approval_ajax');
        Route::post('/approval/store', 'UserInfoController@approval_store');
        Route::post('/popup_updata_approval/ajax', 'UserInfoController@popup_updata_approval_ajax');
        // 自立度 ttakenaka
        Route::post('/independence/ajax', 'UserInfoController@independence_ajax');
        Route::post('/independence/store', 'UserInfoController@independence_store');
        Route::post('/popup_updata_independence/ajax', 'UserInfoController@popup_updata_independence_ajax');
        // 公費情報 ttakenaka
        Route::get('/public_expenditure/get_public_spending', 'UserInfoController@getPublicSpending');
        Route::get('/public_expenditure/values_check_result', 'UserInfoController@getPublicExpenditureValuesCheckResult');
        Route::post('/public_expenditure/public_expenditure_history', 'UserInfoController@getPublicExpenditureHistory');
        Route::post('/public_expenditure/public_expending_cheked_data', 'UserInfoController@getPublicSpendingCheckedData');
        Route::post('/public_expenditure/save', 'UserInfoController@publicExpenditureSave');
        // 給付率 hyamada
        Route::get('/benefit/values_check_result', 'UserInfoController@getBenefitValuesCheckResult');
        Route::post('/benefit/benefit_history', 'UserInfoController@getBenefitHistory');
        Route::post('/benefit/benefit_data', 'UserInfoController@getBenefitData');
        Route::post('/benefit/save', 'UserInfoController@benefitSave');
        Route::get('/benefit/get_insured_no', 'UserInfoController@getInsuredNo');
        //請求先情報
        Route::get('uninsured_billing_address/get_facility_user', 'UninsuredBillingAddresseController@get_facility_user');
        Route::get('uninsured_billing_address/get_billing_address', 'UninsuredBillingAddresseController@get_billing_address');
        Route::post('uninsured_billing_address/save', 'UninsuredBillingAddresseController@save');
        // 傷病名
        Route::get('injury_and_illness/get_user_info', 'InjuriesSicknessController@getUserInformation');
        Route::get('injury_and_illness/get_histories', 'InjuriesSicknessController@getHistories');
        Route::get('injury_and_illness/get_history', 'InjuriesSicknessController@getHistory');
        Route::get('injury_and_illness/get_special', 'InjuriesSicknessController@getSpecialMedicalExpensesList');
        Route::post('injury_and_illness/save', 'InjuriesSicknessController@save');
        // 基本摘要
        Route::get('basic_abstract/get_user_info', 'BasicAbstractController@getUserInformation');
        Route::get('basic_abstract/get_code', 'BasicAbstractController@getUserCircumstanceCode');
        Route::get('basic_abstract/get_histories', 'BasicAbstractController@getHistories');
        Route::get('basic_abstract/mdc_group_names', 'BasicAbstractController@getMdcGroupNames');
        Route::post('basic_abstract/save', 'BasicAbstractController@save');
        // 負担限度額
        Route::get('burden_limit/get_histories', 'BurdenLimitController@getHistories');
        Route::get('burden_limit/get_user_info', 'BurdenLimitController@getUserInformation');
        Route::post('burden_limit/save', 'BurdenLimitController@save');
    });

    // 事業所情報
    Route::prefix('group_home/facility_info')->namespace("GroupHome\FacilityInfo")->middleware('auth')->group(function () {
        // 事業所情報ページ
        Route::get('/', 'FacilityInfoController@index')->name('group_home.facility_info');
        // 法人 eikeda
        Route::post('/corporation', 'FacilityInfoController@corporation');
        Route::post('/corporation/update', 'FacilityInfoController@corporation_update');
        // 施設 eikeda
        Route::post('/institution', 'FacilityInfoController@institution');
        Route::post('/institution/update', 'FacilityInfoController@institution_update');
        // 事業所 eikeda
        Route::post('/office', 'FacilityInfoController@office');
        Route::post('/office/update', 'FacilityInfoController@office_update');
        // サービス種別 ttakenaka
        Route::post('/service_type/ajax', 'FacilityInfoController@service_type_ajax');
        Route::post('/service_type/update_from', 'FacilityInfoController@service_type_update_from');
        Route::get('/service_type/list', 'FacilityInfoController@service_type_list');
        Route::post('/service_type/store', 'FacilityInfoController@service_type_store');
        // 加算状況
        Route::get('/addition_status/get/care_reward_history', 'FacilityAdditionController@getCareRewardHistory');
        Route::get('/addition_status/get/care_reward_histories', 'FacilityAdditionController@getCareRewardHistories');
        Route::get('/addition_status/get/latest_care_reward_history', 'FacilityAdditionController@geLatestCareRewardHistory');
        Route::post('/addition_status/insert/care_reward_history', 'FacilityAdditionController@insertCareRewardHistory');
        Route::post('/addition_status/update/care_reward_history', 'FacilityAdditionController@updateCareRewardHistory');
        Route::get('/special_medical_expenses/get/special_medical_information', 'SpecialMedicalExpensesController@getSpecialMedicalInformation');
        Route::get('/special_medical_expenses/get/histories', 'SpecialMedicalExpensesController@getHistories');
        Route::post('/special_medical_expenses/save/', 'SpecialMedicalExpensesController@save');
        // 保険外サービス
        Route::post('/uninsured_service/get_history', 'FacilityInfoController@getUninsuredServiceHistory');
        Route::post('/uninsured_service/get_uninsured_item_histories', 'FacilityInfoController@getUninsuredItemHistories');
        Route::post('/uninsured_service/save_uninsured_item', 'FacilityInfoController@saveUninsuredItem');
        Route::post('/uninsured_service/delete_service_item', 'FacilityInfoController@deleteServiceItem');
        Route::post('/uninsured_service/first_service_register', 'FacilityInfoController@firstServiceRegister');
        Route::post('/uninsured_service/new_month_service', 'FacilityInfoController@newMonthService');
        Route::post('/uninsured_service/save_sort', 'FacilityInfoController@saveSort');
    });

    // スタッフ情報
    Route::prefix('group_home/staff_info')->namespace("GroupHome\StaffInfo")->middleware('auth')->group(function () {
        // 基本情報ページ
        Route::get('/', 'StaffInfoController@index')->name('group_home.staff_info');
        Route::get('staff/get_staff_list', 'StaffInfoController@getStaffList');
        Route::get('staff/get_staff_history', 'StaffInfoController@getStaffHistory');
        Route::post('staff/save', 'StaffInfoController@save');
        //権限設定
        Route::post('auth_extent/get_history', 'AuthExtentController@getAuthExtent');
        Route::post('auth_extent/save', 'AuthExtentController@save');
    });

    // 伝送情報
    Route::prefix('group_home/transmit_info')->namespace("GroupHome\TransmitInfo")->middleware('auth')->group(function () {
        // 基本情報ページ
        Route::get('/', 'TransmitController@index')->name('group_home.transmit_info');
        Route::post('/transmit/get_invoice', 'TransmitController@getInvoice');
        Route::post('/transmit/get_document', 'TransmitController@getDocument');
        Route::post('/transmit/filter', 'TransmitController@setFilter');
        Route::post('/transmit/sent_invoice', 'TransmitController@sentInvoice');
        Route::get('/transmit/get_file', 'TransmitController@getFileFromS3');
        Route::get('/transmit/get_retrundocument', 'TransmitController@getReturnDocument');
        Route::post('/transmit/test', 'TransmitController@test');
        Route::post('/transmit/cancel_transmit', 'TransmitController@cancelTransmit');
        Route::post('/transmit/delete_invoice', 'TransmitController@deleteInvoice');

        Route::get('/transmit/get_retrundocumentlist', 'TransmitController@getReturnDocumentList');
        Route::get('/transmit/check_transmit_period', 'TransmitController@checkTransmitPeriod');
    });

    // yhamada pdf出力デモ
    Route::prefix('group_home/pdf_demo')->namespace("GroupHome\PdfDemo")->middleware('auth')->group(function () {
        Route::get('/', 'PdfDemoController@index')->name('group_home.pdf_demo');
        Route::get('/all', 'PdfDemoAllController@index')->name('group_home.pdf_demo_all');
        Route::get('/facility', 'PdfDemoFacilityController@index')->name('group_home.pdf_demo_facility');
    });
    // yhamada pdf出力デモ

    // グループホーム(サービス)
    Route::prefix('group_home/service')->namespace("GroupHome\Service")->middleware('auth')->group(function () {
        // 事業所情報
        Route::get('facility', 'FacilityController@getRelatedData');

        // 自動サービスコード/取得
        Route::get('auto_service_code/get', 'AutoServiceCodeController@get');

        // 公費取得
        Route::get('/public_expense/get', 'PublicExpenseController@get');

        // 公費次回分
        Route::get('public_expense_next/get', 'PublicExpenseNextController@get');

        Route::post('corporation', 'CorporationController@getRelatedData');
        Route::post('institution', 'InstitutionController@getRelatedData');
        // 保険者
        Route::get('insurer/get', 'InsurerController@get');
        Route::get('facility_user/header/get', 'FacilityUserController@getHeader')->name('facility_user.get_header');
        // 施設利用者について請求対象者の情報を取得する。
        Route::get('facility_user/billing_target/get', 'FacilityUserController@getBillingTarget')->name('facility_user.get_billing_target');
        // 施設利用者について外泊日の情報を取得する。
        Route::get('facility_user/stay_out_days/get', 'FacilityUserController@getStayOutDays')->name('facility_user.get_stay_out_days');
        // 施設利用者について入居日の情報を取得する。
        Route::get('facility_user/start_dates/get', 'FacilityUserController@getStartDates')->name('facility_user.get_start_dates');
        // 施設利用者について退去日の情報を取得する。
        Route::get('facility_user/end_dates/get', 'FacilityUserController@getEndDates')->name('facility_user.get_end_dates');
        Route::post('facility_user/get_data', 'FacilityUserController@getData')->name('facility_user.get_data');
        Route::post('facility_user/insert_form', 'FacilityUserController@insertForm')->name('facility_user.insert_form');
        Route::post('facility_user/update_form', 'FacilityUserController@updateForm')->name('facility_user.update_form');

        // 国保連請求の承認状態の更新。
        Route::post('national_health_billing/agreement/update', 'ServiceResultController@updateApproval');

        // サービス実績の様式の取得。
        Route::get('service_result/form/get', 'ServiceResultController@getForm');

        Route::post('service_result/get_benefit_billing', 'ServiceResultController@getBenefitBilling');

        // サービス実績の取得。
        Route::get('service_result/get', 'ServiceResultController@getFacilityUserTargetYm');

        // サービス実績の保存。
        Route::post('service_result/save', 'ServiceResultController@save')->name('service_result.save');

        Route::post('download_csv', 'NationalHealthBillingController@downloadCsv')->name('download_csv');
        Route::post('invoice/make_invoice/facility_users', 'InvoiceController@makeInvoice')->name('make_invoice');
        Route::get('national_health/download_csv/facility_users', 'NationalHealthBillingController@downloadCsvWithFacilityUserIds')->name('download_csv_facility_users');

        // サービスコード
        Route::get('service_code/get', 'ServiceCodeController@getServiceCodes');

        // サービスコード(特定入所者サービス)
        Route::get('service_code/incompetent_resident/list', 'ServiceCodeController@listIncompetentResidents');

        // 特別診療費
        Route::get('special_medical_code/get', 'SpecialMedicalCodeController@get');

        // 事業所情報画面
        Route::post('corporation_tree', 'CorporationTreeController@reference');
        //スタッフ画面
        Route::get('staff/header/get_header', 'StaffController@getHeader');
    });
});
