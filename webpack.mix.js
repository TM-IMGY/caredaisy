
const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
// メンテナンスページ
    .styles(
        [
            'resources/css/maintenance.css',
        ],  'public/css/maintenance.css')

// その他系
    .styles(
        [
            'resources/css/claim_users.css',
            'resources/css/login.css',
            'resources/css/service_code.css',
            'resources/css/service_table.css'
        ],'public/css/extra.css')
    .styles('resources/css/external_capture.css','public/css/external_capture.css')
    .js('resources/js/external_capture.js', 'public/js/external_capture.js')
    .js('resources/js/login.js', 'public/js/login.js')
    .styles('resources/css/calendar.css','public/css/calendar.css')

// レイアウト系
    .styles(
        [
            'resources/css/layouts/application.css',
            'resources/css/group_home/application_header/header.css'
        ],  'public/css/layouts/layout.css')
    .js('resources/js/layouts/header.js', 'public/js/layouts/layout.js')

// 帳票
    .styles(
        [
            'resources/css/group_home/own_uninsurance_bill/style.css'
        ],'public/css/own_uninsurance_bill/own_uninsurance_bill.css')
    .styles('resources/css/group_home/own_uninsurance_bill/style_list.css', 'public/css/own_uninsurance_bill/own_uninsurance_bill_list.css')
// pdf_demo
    .styles(
        [
            'resources/css/group_home/pdf_demo/pdf_demo.css',
        ],'public/css/group_home/pdf_demo/pdf_demo.css')

// demo_facility
    .styles(
        [
            'resources/css/group_home/pdf_demo/pdf_demo_facility.css'
        ],'public/css/group_home/pdf_demo/pdf_demo_facility.css')

// demo_all
    .styles(
        [
            'resources/css/group_home/pdf_demo/pdf_demo_all.css',
        ],'public/css/group_home/pdf_demo/pdf_demo_all.css')

// pdf_all_yoshiki_6-x
    .styles(
        [
            'resources/css/group_home/pdf_demo/pdf_all_yoshiki_6-x.css'
        ], "public/css/group_home/pdf_demo/pdf_all_yoshiki_6-x.css")

// pdf_yoshiki_6-x
    .styles(
        [
            'resources/css/group_home/pdf_demo/pdf_yoshiki_6-x.css'
        ], "public/css/group_home/pdf_demo/pdf_yoshiki_6-x.css")

    // 帳票様式9の2
    .styles(
        ['resources/css/group_home/pdf_demo/format_9_2.css'],
        "public/css/group_home/pdf_demo/format_9_2.css"
    )

// スタッフ情報
    .styles(
        [
            'resources/css/group_home/staff_info/staff_info.css'
        ],  'public/css/group_home/staff_info/staff_info.css')
    .js([
            'resources/js/group_home/staff_info/auths.js',
            'resources/js/group_home/staff_info/facility_pulldown.js',
            'resources/js/group_home/staff_info/pulldown.js',
            'resources/js/group_home/staff_info/staff.js',
            'resources/js/group_home/staff_info/staff_info.js',
            'resources/js/group_home/staff_info/staff_table.js',
            'resources/js/group_home/staff_info/tab_manager.js'
        ] , 'public/js/group_home/staff_info/staff_info.js')

// 実績情報
    .styles(
        [
            'resources/css/group_home/result_info/national_health.css',
            'resources/css/group_home/result_info/result_info.css',
            'resources/css/group_home/result_info/result_info_facility_user_info_header.css',
            'resources/css/group_home/result_info/service_result_table.css',
            'resources/css/group_home/result_info/stayOut.css',
            'resources/css/group_home/result_info/tab_manager.css',
            'resources/css/group_home/result_info/uninsured.css',
            'resources/css/ja_calendar.css'
        ],'public/css/group_home/result_info/result_info.css')
    .js([
            'resources/js/group_home/result_info/result_info.js',
        ] , 'public/js/group_home/result_info/result_info.js')


// 利用者情報
    .styles(
        [
            'resources/css/group_home/user_info/approval.css',
            'resources/css/group_home/user_info/basic.css',
            'resources/css/group_home/user_info/benefit.css',
            'resources/css/group_home/user_info/billing_address.css',
            'resources/css/group_home/user_info/independence.css',
            'resources/css/group_home/user_info/public_expenditure.css',
            'resources/css/group_home/user_info/service.css',
            'resources/css/group_home/user_info/tab_manager.css',
            'resources/css/group_home/user_info/user_info.css',
            'resources/css/group_home/user_info/injury_and_illness.css',
            'resources/css/group_home/user_info/basic_abstract.css',
            'resources/css/group_home/user_info/burden_limit.css',
            'resources/css/ja_calendar.css'
        ],'public/css/group_home/user_info/user_info.css')
    .js([
            'resources/js/group_home/user_info/approval.js',
            'resources/js/group_home/user_info/basic_info.js',
            'resources/js/group_home/user_info/benefit.js',
            'resources/js/group_home/user_info/facility_pulldown.js',
            'resources/js/group_home/user_info/facility_user_table.js',
            'resources/js/group_home/user_info/independence.js',
            'resources/js/group_home/user_info/log_info.js',
            'resources/js/group_home/user_info/public_expenditure.js',
            'resources/js/group_home/user_info/service.js',
            'resources/js/group_home/user_info/user_info.js',
            'resources/js/group_home/user_info/tab_manager.js',
            'resources/js/group_home/change_monitoring.js',
            'resources/js/group_home/user_info/burden_limit.js',
        ] , 'public/js/group_home/user_info/user_info.js')

// 事業所情報
    .styles(
        [
            'resources/css/group_home/facility_info/addition_status.css',
            'resources/css/group_home/facility_info/corporation.css',
            'resources/css/group_home/facility_info/facility_info.css',
            'resources/css/group_home/facility_info/service_type.css',
            'resources/css/group_home/facility_info/tab_manager.css',
            'resources/css/group_home/facility_info/uninsured_service.css',
            'resources/css/group_home/facility_info/special_medical_expenses.css',
            'resources/css/ja_calendar.css'
        ],'public/css/group_home/facility_info/facility_info.css')
    .js([
            'resources/js/group_home/facility_info/addition_status.js',
            'resources/js/group_home/facility_info/addition_status_table.js',
            'resources/js/group_home/facility_info/corporation.js',
            'resources/js/group_home/facility_info/corporation_tree.js',
            'resources/js/group_home/facility_info/facility.js',
            'resources/js/group_home/facility_info/facility_info.js',
            'resources/js/group_home/facility_info/living_room.js',
            'resources/js/group_home/facility_info/office.js',
            'resources/js/group_home/facility_info/service_type.js',
            'resources/js/group_home/facility_info/staff_info.js',
            'resources/js/group_home/facility_info/tab_manager.js',
            'resources/js/group_home/facility_info/tree_list_update.js',
            'resources/js/group_home/facility_info/uninsured_service.js'
        ] , 'public/js/group_home/facility_info/facility_info.js')

// 介護計画書情報
    .styles(
        [
            'resources/css/group_home/care_plan_info/tab_manager.css',
            'resources/css/group_home/care_plan_info/care_plan_info.css',
            'resources/css/group_home/care_plan_info/service_plan1.css',
            'resources/css/group_home/care_plan_info/service_plan2.css',
            'resources/css/group_home/care_plan_info/service_plan3.css'
        ], 'public/css/group_home/care_plan_info/care_plan_info.css')
    .styles(
        [
            'resources/css/group_home/care_plan_info/service_plan1_pdf.css',
        ],  'public/css/group_home/care_plan_info/service_plan1_pdf.css')
    .styles(
        [
            'resources/css/group_home/care_plan_info/service_plan2_pdf.css'
        ],  'public/css/group_home/care_plan_info/service_plan2_pdf.css')
    .styles(
        [
            'resources/css/group_home/care_plan_info/service_plan3_pdf.css',
            //'resources/css/group_home/care_plan_info/service_plan3.css'
        ],  'public/css/group_home/care_plan_info/service_plan3_pdf.css')
    .js([
            'resources/js/group_home/care_plan_info/care_plan_info.js',
            'resources/js/group_home/care_plan_info/facility_pulldown.js',
            'resources/js/group_home/care_plan_info/facility_user_table.js',
            'resources/js/group_home/care_plan_info/service_plan1.js',
            'resources/js/group_home/care_plan_info/service_plan2.js',
            'resources/js/group_home/care_plan_info/service_plan2_user_info.js',
            'resources/js/group_home/care_plan_info/tab_manager.js'
        ] , 'public/js/group_home/care_plan_info/care_plan_info.js')
// 伝送
    .styles(
        [
            'resources/css/group_home/transmit_info/invoice.css',
            'resources/css/group_home/transmit_info/transmit_info.css',
            'resources/css/ja_calendar.css'
        ],  'public/css/group_home/transmit_info/transmit_info.css')
    .styles(
        [
            'resources/css/group_home/transmit_info/print.css'
        ],  'public/css/group_home/transmit_info/print.css')
    .js([
            'resources/js/group_home/transmit_info/document.js',
            'resources/js/group_home/transmit_info/document_list.js',
            'resources/js/group_home/transmit_info/search_scope.js',
            'resources/js/group_home/transmit_info/tab_manager.js',
            'resources/js/group_home/transmit_info/transmit.js',
            'resources/js/group_home/transmit_info/transmit_info.js',
            'resources/js/group_home/transmit_info/transmit_list.js'
        ] , 'public/js/group_home/transmit_info/transmit_info.js')
// 全画面
    .js([
        'resources/js/group_home/change_alert.js',
        'resources/js/group_home/change_monitoring.js',
        'resources/js/group_home/chnage_popup.js'
    ], 'public/js/group_home/change_alert.js')

// js/lib
    .js('resources/js/lib/csrf_token.js','public/js/lib/csrf_token.js')
    .js('resources/js/lib/custom_ajax.js','public/js/lib/custom_ajax.js')
    .js('resources/js/lib/facility_user_info_header.js','public/js/lib/facility_user_info_header.js')
    .js('resources/js/lib/staff_info_header.js','public/js/lib/staff_info_header.js')
    .js('resources/js/lib/japanese_calendar.js','public/js/lib/japanese_calendar.js')

    .version();
