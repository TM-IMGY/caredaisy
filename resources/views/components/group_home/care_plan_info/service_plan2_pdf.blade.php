<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>介護計画書2_{{ $facilityUser['last_name'].$facilityUser['first_name'] }}</title>
</head>
<body>

    <div class="container first_block">

        <div class="row_1">
            <div class="page">
                <span class="page_num">第2表</span>
            </div>
            <div class="create_date">
                <span class="create_date_ymd">作成年月日</span>
                <span class="create_date_year"><span class="create_date_year_val">{{ date("Y", strtotime($createdAt)) }}</span>年</span>
                <span class="create_date_month"><span class="create_date_month_val">{{ date("m", strtotime($createdAt)) }}</span>月</span>
                <span class="create_date_day"><span class="create_date_day_val">{{ date("d", strtotime($createdAt)) }}</span>日</span>
            </div>
        </div>

        <div class="row_2">
            <span class="title">{{ $title }}</span>
        </div>

        <div class="row_5">
            <div class="sp2_pdf_facility_user_name">
                <div>
                    <span>利用者名</span>
                </div>
                <div class="name">
                    <span>{{ $facilityUser['last_name']}} {{ $facilityUser['first_name'] }}</span>
                </div>
                <div class="sama">
                    <span>様</span>
                </div>
            </div>
            <div class="service_creat">
                <div class="service_creater">
                    <span>施設サービス計画作成者氏名</span>
                </div>
                <div class="name">
                    <span>{{ $editor }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container second_block">
        <div class="row_4">
            <table border="1" class="plan2_main_table">
                <tr>
                    <th rowspan=2 class="th_1">生活全般の解決すべき課題(ニーズ)</th>
                    <th colspan=4>目標</th>
                    <th colspan=4>援助内容</th>
                </tr>
                <tr>
                    <th class="th_2">長期目標</th>
                    <th class="th_3">(期間)</th>
                    <th class="th_4">短期目標</th>
                    <th class="th_5">(期間)</th>
                    <th class="th_6">サービス内容</th>
                    <th class="th_7">担当者</th>
                    <th class="th_8">頻度</th>
                    <th class="th_9">期間</th>
                </tr>
                <tbody>
            @foreach($secondServiceDatas ?? [] as $needs)
                @foreach($needs as $need)
                    <tr class="print_pages">
                    @php
                        $lpIndex = 0;
                        $spIndex = 0;
                        $svIndex = 0;
                        $lpRowCount = 0;
                        $spRowCount = 0;
                        $svRowCount = 0;
                    @endphp
                    @for($i=0; $i<$need["childRowCount"]; $i++)
                            @if ($i == 0)
                                <td rowspan="{{ $need["childRowCount"]}}">
                                    {!! nl2br(e($need["needs"])) !!}
                                </td>
                            @endif

                            @if ($lpRowCount == 0)
                                <td rowspan="{{ $need["serviceLongPlans"][$lpIndex]["childRowCount"] }}">
                                    @if ($need["serviceLongPlans"][$lpIndex]["goal"] )
                                    {!! nl2br(e($need["serviceLongPlans"][$lpIndex]["goal"])) !!}
                                    @endif
                                </td>
                                <td rowspan="{{ $need["serviceLongPlans"][$lpIndex]["childRowCount"] }}">
                                    @if ( $need["serviceLongPlans"][$lpIndex]["task_start"] )
                                    {{ date('Y/m/d', strtotime($need["serviceLongPlans"][$lpIndex]["task_start"])) }} ~
                                    @endif
                                    @if ( $need["serviceLongPlans"][$lpIndex]["task_end"] )
                                    {{ date('Y/m/d', strtotime($need["serviceLongPlans"][$lpIndex]["task_end"])) }}
                                    @endif
                                </td>
                            @endif

                            @if ($spRowCount == 0)
                                <td rowspan="{{ $need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["childRowCount"] }}">
                                    @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["goal"])
                                    {!! nl2br(e($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["goal"])) !!}
                                    @endif
                                </td>
                                <td rowspan="{{ $need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["childRowCount"] }}">
                                    @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["task_start"] )
                                    {{ date('Y/m/d', strtotime($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["task_start"])) }} ~
                                    @endif
                                    @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["task_end"] )
                                    {{ date('Y/m/d', strtotime($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["task_end"])) }}
                                    @endif
                                </td>
                            @endif
                            <td>
                                @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["service"] )
                                {!! nl2br(e($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["service"])) !!}
                                @endif
                            </td>
                            <td>
                                @if ( $need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["staff"] )
                                {!! nl2br(e($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["staff"])) !!}
                                @endif
                            </td>
                            <td>
                                @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["frequency"] )
                                {!! nl2br(e($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["frequency"])) !!}
                                @endif
                            </td>
                            <td>
                                @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["task_start"] )
                                {{ date('Y/m/d', strtotime($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["task_start"])) }} ~
                                @endif
                                @if ($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["task_end"] )
                                {{ date('Y/m/d', strtotime($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"][$svIndex]["task_end"])) }}
                                @endif
                            </td>
                        @php
                            $lpRowCount++;
                            $spRowCount++;
                            $svRowCount++;
                            $svIndex++;

                            if($svIndex == count($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"][$spIndex]["servicePlanSupports"]) ){
                                $spIndex++;
                                $spRowCount=0;
                                $svIndex=0;
                            }
                            if($spIndex == count($need["serviceLongPlans"][$lpIndex]["serviceShortPlans"])){
                                $lpIndex++;
                                $lpRowCount=0;
                                $spIndex=0;
                                $svIndex=0;
                            }
                            if($lpIndex == count($need["serviceLongPlans"])){
                                $lpIndex=0;
                                $spIndex=0;
                                $svIndex=0;
                            }
                        @endphp
                        </tr>
                    @endfor
                @endforeach
            @endforeach
                </tbody>
            </table>
        <div>

    </div>
</body>
</html>
