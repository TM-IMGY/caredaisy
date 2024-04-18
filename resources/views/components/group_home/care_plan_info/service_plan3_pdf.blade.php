<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/group_home/care_plan_info/service_plan3_pdf.css') }}">
    <title>{{ $title }}</title>
</head>
<body>
    <div class="container">
      <header>
        <span class="page">第3表</span>

        <div class="facility_user_name">
            <span>利用者名： {{ $servicePlan->facilityUser->last_name}} {{ $servicePlan->facilityUser->first_name }}　様</span>
        </div>
        <span class="title">週間サービス計画表</span>
        <div class="create_date">
            作成年月日&nbsp;
            {{ date("Y", strtotime($servicePlan->plan_start_period)) }} 年
            {{ date("m", strtotime($servicePlan->plan_start_period)) }} 月
            {{ date("d", strtotime($servicePlan->plan_start_period)) }} 日
        </div>
      </header>

      <?php
      $currentTime = 4;
      $times = [
        ['label' => '深夜', 'length' => 2],
        ['label' => '早朝', 'length' => 2],
        ['label' => '午前', 'length' => 4],
        ['label' => '午後', 'length' => 6],
        ['label' => '夜間', 'length' => 5],
        ['label' => '深夜', 'length' => 5]
      ];
      ?>
      <div id="calendarWrapper">
        <table id="calendar">
          <thead>
            <th class="timezone-cell"></th>
            <th class="time-cell"></th>
            <th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th><th>日</th><th class="mainwork">主な日常生活上の活動</th>
          </thead>
          <tbody>
            @foreach($times as $timezone)
              @for($i=0; $i<$timezone['length']*2; $i++)
              <tr>
                @if ($i === 0)
                <td class="timezone" rowspan="{{ $timezone['length'] * 2 }}">{{ $timezone['label'] }}</td>
                @endif

                @if($i % 2 === 0)
                <td rowspan="2" class="time">
                    @if ($currentTime == 24)
                        0:00
                    @else
                        {{ $currentTime > 24 ? $currentTime - 24 : $currentTime}}:00
                    @endif
                </td>
                @endif

                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>

                @if($i % 2 === 0)
                <td  rowspan="2" class="main">
      <?php
        $target = $schedules->first(function ($schedule) use ($currentTime) {
            if ($schedule->service_day != 8) {
                return false;
            }
            return $schedule->start_minutes == $currentTime * 60;
        });
      ?>
                  @if($target)
                  {{ mb_strlen($target->content) < 32 ? $target->content : mb_substr($target->content,0,32).'...' }}
                  @endif
                </td>
                <?php $currentTime++; ?>
                @endif
              </tr>

              @endfor
            @endforeach
          </tbody>
        </table>
      <div class="weeklyArea">
        <div class="sceleton" data-sceletonweek="1"></div>
        <div class="sceleton" data-sceletonweek="2"></div>
        <div class="sceleton" data-sceletonweek="3"></div>
        <div class="sceleton" data-sceletonweek="4"></div>
        <div class="sceleton" data-sceletonweek="5"></div>
        <div class="sceleton" data-sceletonweek="6"></div>
        <div class="sceleton" data-sceletonweek="7"></div>
      </div>
    </div>

    <?php
        $target = $schedules->first(function ($schedule) {
            return $schedule->service_day == 9;
        });
      ?>
    <div class="otherArea">
      <div>週単位以外のサービス</div>
      <span>
      @if($target)
      {{ mb_strlen($target->content) < 200 ? $target->content : mb_substr($target->content,0,200).'...' }}
      @endif
      </span>
    </div>

    <script type="text/javascript">
      const weeklyArea = document.querySelector('.weeklyArea');
      const sceletons = document.querySelectorAll('.sceleton');
      const dummy = document.querySelector('.dummy');
      const schedules = @json($schedules);

      var weekly = [];
      var main = [];
      var other = [];

      // データを分類する
      const divideData = function(datas) {
        for(var i=0; i < datas.length; i++) {
          if(datas[i].service_day < 8) {
            weekly.push(datas[i]);
          }
          else if(datas[i].service_day == 8) {
            main.push(datas[i]);
          }
          else if(datas[i].service_day == 9) {
            other.push(datas[i]);
          }
        }
      }

      const deploySchedules = function(datas) {
          for(var i=0; i < datas.length; i++ ) {
              const schedule = datas[i];
              const targetScheleton = document.querySelector('[data-sceletonweek="'+ schedule.service_day +'"]');
              deploySchedule(targetScheleton, schedule)
          }
      }

      const deploySchedule = function(element, event) {
          var eventElement = document.createElement('div');
          const position = calculateEventPosition(event);
          eventElement.textContent = event.weekly_service ? event.weekly_service.description : '不明なサービス';
          eventElement.style.top = position.top + "px";
          eventElement.style.bottom = position.bottom + "px";
          eventElement.dataset.id = event.id;

          element.appendChild(eventElement);
      }

      /**
       * イベントの表示位置を計算して返す
       * @param {start_minutes:number, end_minute:number, content:string} event
       * @returns {top:float, bottom:float}
       */
      const calculateEventPosition = function(event) {
          const startMinutes = 4 * 60; // 4時
          const selHeight = 16;
          const borderWidth = 1;
          const totalSelNum = 24 * 2; // 30分刻みのため総数は24の倍
          const top = (event.start_minutes - startMinutes) / 60 * 2 * selHeight - borderWidth;
          const bottom = totalSelNum * selHeight - (event.end_minutes - startMinutes) / 60 * 2 * selHeight;

          return {top:top, bottom:bottom};
      }

      divideData(schedules);
      deploySchedules(weekly);
    </script>
</body>
</html>
