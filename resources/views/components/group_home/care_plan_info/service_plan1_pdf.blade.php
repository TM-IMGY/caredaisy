<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel='stylesheet' href="{{ asset('/css/group_home/care_plan_info/service_plan1_pdf.css') }}">
    @foreach ($facilityUserInformations as $item => $val)
    <title>介護計画書_{{ $val['user_info']['last_name'].$val['user_info']['first_name'] }}</title>
    @endforeach
</head>
<body>
    @foreach ($facilityUserInformations as $item => $val)

    <div class="container sp1_pdf_first_block">

        <div class="sp1_pdf_row_1">
            <div class="page">
                <span class="page_num">第1表</span>
            </div>
            <div class="create_date">
                <span class="create_date_ymd">作成年月日</span>
                <span class="create_date_year"><span class="create_date_year_val">{{ $val['service_plan_info']['plan_start_period']['year']}}</span>年</span>
                <span class="create_date_month"><span class="create_date_month_val">{{ $val['service_plan_info']['plan_start_period']['month']}}</span>月</span>
                <span class="create_date_day"><span class="create_date_day_val">{{ $val['service_plan_info']['plan_start_period']['day']}}</span>日</span>
            </div>
        </div>

        <div class="sp1_pdf_row_2">
            <span class="title">{{ $val['title'] }}</span>
        </div>

        <div class="sp1_pdf_row_3">
            <div class="division">
                <span
                @if ($val['service_plan_info']['plan_division'] == \App\Models\FirstServicePlan::PLAN_DIVISION_FIRST_TIME)
                    class="division_circul div_status_1"
                @elseif ($val['service_plan_info']['plan_division'] == \App\Models\FirstServicePlan::PLAN_DIVISION_INTRODUCTION)
                    class="division_circul div_status_2"
                @elseif ($val['service_plan_info']['plan_division'] == \App\Models\FirstServicePlan::PLAN_DIVISION_CONTINUATION)
                    class="division_circul div_status_3"
                @elseif ($val['service_plan_info']['plan_division'] == \App\Models\FirstServicePlan::PLAN_DIVISION_INTRODUCTION_AND_CONTINUATION)
                    class="div_status_5"
                @endif>
                </span>

                <span class="first_time">初回</span>
                <span>・</span>
                <span class="introduce">紹介</span>
                <span>・</span>
                <span class="continu">継続</span>
            </div>
            <div class="status">
                <span
                @if ($val['service_plan_info']['certification_status'] == \App\Models\UserCareInformation::CERTIFICATION_STATUS_APPLYING)
                    class="certification_status_circul certification_status_1"
                @elseif ($val['service_plan_info']['certification_status'] == \App\Models\UserCareInformation::CERTIFICATION_STATUS_CERTIFIED)
                    class="certification_status_circul certification_status_2"
                @endif>
                </span>
                <span class="ok">認定済</span>
                <span>・</span>
                <span class="ng">申請中</span>
            </div>
        </div>

        <div class="sp1_pdf_row_4">
            <div class="facility_user_name">
                <div>
                    <span>利用者名</span>
                </div>
                <div class="name">
                    <span>{{ $val['user_info']['last_name']}} {{ $val['user_info']['first_name'] }}</span>
                </div>
                <div class="sama">
                    <span>様</span>
                </div>
            </div>
            <div class="birthday">
                <span class="birthday_ymd">生年月日</span>
                <span class="birthday_year"><span class="birthday_year_val">{{ $val['user_info']['birth']['year']}} 年</span></span>
                <span class="birthday_month"><span class="birthday_month_val">{{ $val['user_info']['birth']['month']}} 月</span></span>
                <span class="birthday_day"><span class="birthday_day_val">{{ $val['user_info']['birth']['day']}} 日</span></span>
            </div>
            <div class="address">
                <span>住所</span><span>　{{$val['user_info']['location1']}}　{{$val['user_info']['location2']}}</span>
            </div>
        </div>

        <div class="sp1_pdf_row_5">
            <div class="service_create">
                <div class="service_creater">
                    <span>施設サービス計画作成者氏名</span>
                </div>
                <div class="service_creater_name">
                    <span>{{ $val['service_plan_info']['plan_end_period']}}</span>
                </div>
            </div>
        </div>

        <div class ="sp1_pdf_row_6">
            <div class="facility">
                <span>施設介護支援事業者・事業所名及び住所</span>
                <span>　　{{ $val['facility_info']['facility_name_kanji']}}　　{{ $val['facility_info']['location']}}</span>
            </div>
        </div>

        <div class="sp1_pdf_row_7">
            <div class="service_plan">
                <span class="service_plan_ymd">施設サービス計画作成(変更)日</span>
                <span class="service_plan_year"><span class="service_plan_year_val">{{ $val['service_plan_info']['plan_start_period']['year']}}</span>年</span>
                <span class="service_plan_month"><span class="service_plan_month_val">{{ $val['service_plan_info']['plan_start_period']['month']}}</span>月</span>
                <span class="service_plan_day"><span class="service_plan_day_val">{{ $val['service_plan_info']['plan_start_period']['day']}}</span>日</span>
            </div>
            <div class="first_service_plan">
                <span class="first_service_plan_ymd">初回施設サービス計画作成日</span>
                <span class="first_service_plan_year">
                    <span
                        @if ($val['service_plan_info']['first_plan_start_period']['year'] == null)
                            class="first_service_plan_year_val first_plan_date_space"
                        @else
                            class="first_service_plan_year_val"
                        @endif
                    >{{ $val['service_plan_info']['first_plan_start_period']['year']}}</span>年
                </span>

                <span class="first_service_plan_month">
                    <span
                        @if ($val['service_plan_info']['first_plan_start_period']['month'] == null)
                            class="first_service_plan_month_val first_plan_date_space"
                        @else
                            class="first_service_plan_month_val"
                        @endif
                    >{{ $val['service_plan_info']['first_plan_start_period']['month']}}</span>月
                </span>

                <span class="first_service_plan_day">
                    <span
                        @if ($val['service_plan_info']['first_plan_start_period']['day'] == null)
                            class="first_service_plan_day_val first_plan_date_space"
                        @else
                            class="first_service_plan_day_val"
                        @endif
                    >{{ $val['service_plan_info']['first_plan_start_period']['day']}}</span>日
                </span>
            </div>
        </div>

        <div class="sp1_pdf_row_8">
            <div class="recognition_date">
                <span class="recognition_date_ymd">認定日</span>
                <span class="recognition_date_year">
                    <span
                        @if ($val['service_plan_info']['recognition']['year'] == null)
                            class="recognition_date_year_val recognition_date_space"
                        @else
                            class="recognition_date_year_val"
                        @endif
                    >{{ $val['service_plan_info']['recognition']['year']}}</span>年
                </span>

                <span class="recognition_date_month">
                    <span
                        @if ($val['service_plan_info']['recognition']['month'] == null)
                            class="recognition_datee_month_val recognition_date_space"
                        @else
                            class="recognition_datee_month_val"
                        @endif
                    >{{ $val['service_plan_info']['recognition']['month']}}</span>月
                </span>

                <span class="recognition_date_day">
                    <span
                        @if ($val['service_plan_info']['recognition']['day'] == null)
                            class="recognition_date_day_val recognition_date_space"
                        @else
                            class="recognition_date_day_val"
                        @endif
                    >{{ $val['service_plan_info']['recognition']['day']}}</span>日
                </span>
            </div>

            <div class="recognition_period">
                <span class="period">認定の有効期間</span>
                <span class="period_start_year care_period_start">
                    <span
                        @if ($val['service_plan_info']['care_period_start']['year'] == null)
                            class="period_start_year_val care_period_start_val period_start_space"
                        @else
                            class="period_start_year_val care_period_start_val"
                        @endif
                    >{{ $val['service_plan_info']['care_period_start']['year']}}</span>年
                </span>

                <span class="period_start_month care_period_start">
                    <span
                        @if ($val['service_plan_info']['care_period_start']['month'] == null)
                            class="period_start_month_val care_period_start_val period_start_space"
                        @else
                            class="period_start_month_val care_period_start_val"
                        @endif
                    >{{ $val['service_plan_info']['care_period_start']['month']}}</span>月
                </span>

                <span class="period_start_day">
                    <span
                        @if ($val['service_plan_info']['care_period_start']['day'] == null)
                            class="period_start_day_val care_period_start_val period_start_space"
                        @else
                            class="period_start_day_val care_period_start_val"
                        @endif
                    >{{ $val['service_plan_info']['care_period_start']['day']}}</span>日
                </span>
                <span class="wave">～</span>
                <span class="period_end_year care_period_end">
                    <span
                        @if ($val['service_plan_info']['care_period_end']['year'] == null)
                            class="period_end_year_val care_period_end_val period_end_space"
                        @else
                            class="period_end_year_val care_period_end_val"
                        @endif
                    >{{ $val['service_plan_info']['care_period_end']['year']}}</span>年
                </span>

                <span class="period_end_month care_period_end">
                    <span
                        @if ($val['service_plan_info']['care_period_end']['month'] == null)
                            class="period_end_month_val care_period_end_val period_end_space"
                        @else
                            class="period_end_month_val care_period_end_val"
                        @endif
                    >{{ $val['service_plan_info']['care_period_end']['month']}}</span>月
                </span>

                <span class="period_end_day">
                    <span
                        @if ($val['service_plan_info']['care_period_end']['day'] == null)
                            class="period_end_day_val care_period_end_val period_end_space"
                        @else
                            class="period_end_day_val care_period_end_val"
                        @endif
                    >{{ $val['service_plan_info']['care_period_end']['day']}}</span>日
                </span>
            </div>
        </div>

        <div class="sp1_pdf_row_9">
            <div class="care_level">
                <div class="level_division">
                    <span>要介護状態区分</span>
                </div>
                <div class="level">
                    <span
                    @if ($val['service_plan_info']['care_level_name'] == '要介護１' && in_array($val['facilityUserServiceTypeCode'], [
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_MEDICAL_CLINIC
                    ]))
                        class="care_level_21"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要介護２' && in_array($val['facilityUserServiceTypeCode'], [
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_MEDICAL_CLINIC
                    ]))
                        class="care_level_22"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要介護３' && in_array($val['facilityUserServiceTypeCode'], [
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_MEDICAL_CLINIC
                    ]))
                        class="care_level_23"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要介護４' && in_array($val['facilityUserServiceTypeCode'], [
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_MEDICAL_CLINIC
                    ]))
                        class="care_level_24"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要介護５' && in_array($val['facilityUserServiceTypeCode'], [
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE,
                        \App\Models\ServiceType::SERVICE_TYPE_CODE_MEDICAL_CLINIC
                    ]))
                        class="care_level_25"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要支援２' && $val['facilityUserServiceTypeCode'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTIVE_DEMENTIA_RESPONSE_TYPE_COMMUNAL_LIFE_CARE)
                        class="care_level_13"
                    @elseif ($val['service_plan_info']['care_level_name'] == '非該当' && $val['facilityUserServiceTypeCode'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTION_SPECIFIED_FACILITY_RESIDENT_CARE)
                        class="care_level_1"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要支援１' && $val['facilityUserServiceTypeCode'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTION_SPECIFIED_FACILITY_RESIDENT_CARE)
                        class="care_level_12"
                    @elseif ($val['service_plan_info']['care_level_name'] == '要支援２' && $val['facilityUserServiceTypeCode'] == \App\Models\ServiceType::SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTION_SPECIFIED_FACILITY_RESIDENT_CARE)
                        class="care_level_13 service_type_code_35"
                    @else
                        class="care_level_other"
                    @endif
                    {{-- 介護認定度表示判断フラグ --}}
                    @if ($val['service_plan_info']['care_level_dispflg'] == \App\Lib\Common\Consts::VALID)
                        id="care_level_circle"
                    @endif
                    >
                    </span>
                    @foreach ($val['care_level_division'] as $item)
                        <span>{{ $item }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="container second_block">
        <div class="sp1_pdf_row_10">
            <div class="title_1" style="height: {{$val['first_service_plan_info']['content_count']['content1'] * 19}}px">
                <span>{{ $val['first_service_plan_info']['title1']}}</span>
            </div>
            <div class="content_1">
                <span>{!! nl2br(e($val['first_service_plan_info']['content1'])) !!}</span>
            </div>
        </div>
        <div class="sp1_pdf_row_11">
            <div class="title_2" style="height: {{$val['first_service_plan_info']['content_count']['content2'] * 19}}px">
                <span>{{ $val['first_service_plan_info']['title2']}}</span>
            </div>
            <div class="content_2">
                <span>{!! nl2br(e($val['first_service_plan_info']['content2'])) !!}</span>
            </div>
        </div>
        <div class="sp1_pdf_row_12">
            <div class="title_3" style="height: {{$val['first_service_plan_info']['content_count']['content3'] * 19}}px">
                <span>{{ $val['first_service_plan_info']['title3']}}</span>
            </div>
            <div class="content_3">
                <span>{!! nl2br(e($val['first_service_plan_info']['content3'])) !!}</span>
            </div>
        </div>
        <div class="sp1_pdf_row_13" style="height: {{$val['first_service_plan_info']['content_count']['content4'] * 19}}px" hidden>
            <div class="title_4">
                <span>{{ $val['first_service_plan_info']['title4']}}</span>
            </div>
            <div class="content_4">
                <span>{!! nl2br(e($val['first_service_plan_info']['content4'])) !!}</span>
            </div>
        </div>
    </div>

    <div class="container third_block"
    @if ($flg == \App\Lib\Common\Consts::VALID)
        style="page-break-after:always;"
    @endif
    >
{{--
        <div class="sp1_pdf_row_14">
            <div class="calculation_reason">
                <div class="reason">
                    <span>生活援助中心型の算定理由</span>
                </div>
                <div class="reason_name">
                    @if ($val['service_plan_info']['living_alone'] == 1)
                        <span class="reason_circul living_alone"></span>
                    @endif
                        <span>1. 一人暮らし</span>
                    @if ($val['service_plan_info']['handicapped'] == 1)
                        <span class="reason_circul handicapped"></span>
                    @endif
                        <span>2. 家族等が病気・疾病等</span>
                    @if ($val['service_plan_info']['other'] == 1)
                        <span class="reason_circul other"></span>
                    @endif
                        <span style="width: 35px">3. その他({{$val['first_service_plan_info']['other_reason']}}<span class="other_reason_form">)</span></span>
                </div>
            </div>
        </div>
--}}
        <div class="sp1_pdf_row_15">
            <div class="agree">
                <div>施設サービス計画について説明を受け、内容に同意し交付を受けました。</div>
                <div>説明・同意日</div>
                <div class="agree_date">
                    <span class="agree_day"><span class="agree_day_val"></span>年</span>
                    <span class="agree_day"><span class="agree_day_val"></span>月</span>
                    <span><span></span>日</span>
                </div>
                <div>利用者同意欄</div>
                <div class="agree_name">&nbsp;</div>
            </div>
        </div>

    </div>

    @endforeach

    @if ($flg == \App\Lib\Common\Consts::VALID)
        @include('components.group_home.care_plan_info.service_plan2_pdf',
        [
            'secondServiceDatas'=>$secondServiceDatas,
            "editor" => $editor,
            "createdAt" =>$createdAt,
            "facilityUser"=>$facilityUser,
            "facilityUserServiceTypeCode" => $val["facilityUserServiceTypeCode"]
        ])
    @endif

</body>

</html>
