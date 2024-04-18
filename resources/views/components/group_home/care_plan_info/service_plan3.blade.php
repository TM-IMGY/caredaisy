<div class="tm_contents_hidden" id="tm_contents_service_plan3">

  {{-- ヘッダー情報 --}}
  <h2 dusk="care-plan-3-form-label">週間サービス計画表</h2>
  <div class="user_name_line"></div>
  <div class="plan-select-wrapper">
    <table class="plan-select">
      <thead>
        <tr>
            <th>選択</th>
            <th class="care-level-history">交付日</th>
            <th class="plan1-care-period-start-history">ケアプラン期間</th>
            <th class="plan1-care-period-end-history">介護度</th>
            <th class="care-createdat">作成日</th>
            <th class="certification-done-history">状態</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="button-group">
    <button id='grand_save' class="yellow_btn" type="button">保存</button>
    <button id='preview2' class="gray_btn" type="button">２表プレビュー</button>
    <button id='preview3' class="gray_btn" type="button">３表プレビュー</button>
  </div>
  <div class="plan-calendars">
    <div id="timezone">
      <div></div>
      <div>深夜</div>
      <div>早朝</div>
      <div>午前</div>
      <div>午後</div>
      <div>夜間</div>
      <div>深夜</div>
    </div>

    <table id="calendar">
      <thead>
        <tr><td>
          <table><thead>
            <th class="time-cell"></th>
            <th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th><th>日</th>
          </thead></table>
        </td></tr>
      </thead>
      <tbody>
        <tr><td id="body-wrapper">
          <div class="timegrid">
            <div class="calendar-bg">
              <table>
                <tbody>
                  <tr>
                    <td class="time-cell"></td>
                    <td class="mon"></td>
                    <td class="tue"></td>
                    <td class="wed"></td>
                    <td class="thu"></td>
                    <td class="fri"></td>
                    <td class="sat"></td>
                    <td class="sun"></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="calendar-slats">
              <table>
                <tbody>
                  <?php
                  $times = [
                    '4:00','', '5:00','','6:00','','7:00','','8:00','','9:00','','10:00','','11:00','','12:00','','13:00','',
                    '14:00','','15:00','','16:00','','17:00','','18:00','','19:00','','20:00','','21:00','','22:00','','23:00','','0:00','',
                    '1:00','', '2:00','', '3:00',''
                  ];
                  ?>
                  @foreach ($times as $time)
                  <tr>
                    <td class="time-cell">{{$time}}</td>
                    <td></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="calendar-sceleton">
              <table>
                <tbody>
                  <tr>
                    <td class="time-cell"></td>
                    @for($i=1; $i < 8; $i++)
                    <td class="day-of-week" data-sceletonweek="{{$i}}"></td>
                    @endfor
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </td></tr>
      </tbody>
    </table>
    <table id="main-work">
      <thead>
      <tr><td>
          <table><thead>
            <th>主な日常生活上の活動</th></thead>
          </table></td></tr>
      </thead>
      <tbody>
        @for($i=4; $i < 28; $i++)
        <tr>
          <td data-startminutes="{{$i * 60}}"></td>
        </tr>
        @endfor
      </tbody>
    </table>
  </div>

  <div class="non-weekly_services_wrap">
    <div class="non-weekly_services_title">
      週単位以外のサービス
    </div>
    <div class="non-weekly_services_contents modal_open other_than_weekly"></div>
  </div>

  <div class="modal-wrapper weekly">
    <div class="modal">
      <header>
        <h3>サービス内容</h3>
      </header>
      <div class="select-day-of-week input-wrapper">
        <label>週間サービス</label>
        <select name="service_day">
          <option value="1">月曜日</option>
          <option value="2">火曜日</option>
          <option value="3">水曜日</option>
          <option value="4">木曜日</option>
          <option value="5">金曜日</option>
          <option value="6">土曜日</option>
          <option value="7">日曜日</option>
        </select>
      </div>
      <div class="select-timezone">
        <div class="select-start-time input-wrapper">
          <label>開始時間</label>
          <select name="start_minutes">
          @for($i=4*60; $i < 28*60; $i+=30)
            <option value="{{ $i }}">
              @if (floor($i / 60) > 23)
                {{ floor($i / 60) - 24 }}:{{ str_pad($i % 60, 2, 0) }}
              @else
                {{ floor($i / 60) }}:{{ str_pad($i % 60, 2, 0) }}
              @endif
            </option>
          @endfor
          </select>
        </div>
        <div class="select-end-time input-wrapper">
        <label>終了時間</label>
          <select name="end_minutes">
          @for($i=4*60+30; $i <= 28*60; $i+=30)
            <option value="{{ $i }}">
              @if (floor($i / 60) > 23)
                {{ floor($i / 60) - 24 }}:{{ str_pad($i % 60, 2, 0) }}
              @else
                {{ floor($i / 60) }}:{{ str_pad($i % 60, 2, 0) }}
              @endif
            </option>
          @endfor
          </select>
        </div>
      </div>
      <div class="error time"></div>
      <div class="service-weekly-id input-wrapper">
        <label>サービス内容</label>
        <input type="radio" checked id="radio_service_contents" name="service_active" value="existing">
        <label class="radio-disp radio01" for="radio_service_contents">
          <select name="weekly_service_id">
          </select>
          </label>
      </div>
      <div class="service-content input-wrapper">
      <input type="radio" id="radio_service_other" name="service_active" value="newone">
      <label class="radio-disp radio01" for="radio_service_other">
          その他<input name="content" id="service_other" type="text">
          </label>
      </div>
      <div class="error content"></div>
      <div class="button-area">
        <button class="gray_btn cancel">キャンセル</button>
        <button class="gray_btn delete">削除</button>
        <button class="yellow_btn save">追加</button>
      </div>
    </div>
  </div>

  <div class="modal-wrapper mainwork">
    <div class="modal">
      <header>
        <h3>主な日常生活上の活動</h3>
      </header>
      <div class="input-wrapper timezone">
        <label>時間帯</label>
        <span></span>
      </div>
      <div class="input-wrapper">
        <label>活動内容</label>
        <textarea id="main_activities_daily_contents" name="main_activities_daily_contents"></textarea>
        <div class="tab_wrapper">
          <ul class="tab"></ul>
        </div>
      </div>
      <div class="button-area">
        <button class="gray_btn cancel">キャンセル</button>
        <!-- <button class="gray_btn">削除</button> -->
        <button class="yellow_btn save">追加</button>
      </div>
    </div>
  </div>

  <div class="modal-wrapper otherservice">
    <div class="modal">
      <header>
        <h3>週単位以外のサービス内容</h3>
      </header>
      <div class="input-wrapper">
        <label>サービス内容</label>
        <textarea id="other_than_weekly" name="other_than_weekly"></textarea>
        <div class="other_than_weekly_wrap">
          <div class="tab_inner_btn_wrap"></div>
        </div>
      </div>
      <div class="button-area">
        <button class="gray_btn cancel" type="button">キャンセル</button>
        <button class="yellow_btn save" type="button">追加</button>
      </div>
    </div>
  </div>

</div>
