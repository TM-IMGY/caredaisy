<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title')</title>
  <link href="{{ mix('/css/layouts/layout.css') }}" rel='stylesheet'>
  @yield('style')
</head>
<body>
  <header id="header">
    <div class="header_wrap">
      <div class="header_left">
        <div class="header_left_logo">
          <a href="{{ route('top') }}">
            <img alt="Care Daisy" class="header_logo" src="{{ asset('/img/group_home/application_header/logo1.svg') }}">
          </a>
        </div>
        <nav>
          <ul>
            {{-- ホーム --}}
            <li>
              <a href="{{ route('top')}}" id="category_main_btn_home"
              @if(\Route::currentRouteName()=='top')style="color: rgb(255,131,10)"@endif>ホーム</a>
            </li>

            {{-- 事業所情報 --}}
            @can('readFacility')
              <li>
                <a href="{{ route('group_home.facility_info')}}" id="category_main_btn_facility"
                @if(\Route::currentRouteName()=='group_home.facility_info')style="color: rgb(255,131,10)"@endif>事業所情報</a>
              </li>
            @endcan

            {{-- スタッフ情報 --}}
            @can('readStaff')
              <li>
                <a href="{{ route('group_home.staff_info')}}" id="category_main_btn_staff"
                @if(Route::currentRouteName()=='group_home.staff_info')style="color: rgb(255,131,10)"@endif>スタッフ情報</a>
              </li>
            @endcan

            {{-- 利用者情報 --}}
            <li>
              <a href="{{ route('group_home.user_info')}}" id="category_main_btn_facility_user"
              @if(Route::currentRouteName()=='group_home.user_info')style="color: rgb(255,131,10)"@endif>利用者情報</a>
            </li>

            {{-- ケアプラン --}}
            <li>
              <a href="{{ route('group_home.care_plan_info')}}" id="category_main_btn_care_plan"
              @if(Route::currentRouteName()=='group_home.care_plan_info')style="color: rgb(255,131,10)"@endif>ケアプラン</a>
            </li>

            {{-- 実績情報 --}}
            @can('readRequest')
            <li>
              <a href="{{ route('group_home.result_info')}}" id="category_main_btn_result"
              @if(Route::currentRouteName()=='group_home.result_info')style="color: rgb(255,131,10)"@endif>実績情報</a>
            </li>
            @endcan

            {{-- 伝送情報 --}}
            @can('transmitRequest')
              @if($hasTransmission)
              <li>
                <a href="{{ route('group_home.transmit_info')}}" id="category_main_btn_transmit"
                @if(Route::currentRouteName()=='group_home.transmit_info')style="color: rgb(255,131,10)"@endif>伝送情報</a>
              </li>
              @endif
            @endcan
          </ul>
        </nav>
      </div>
      <div class="header_right">
        <ul class="header_right_ul">
          <li class="header_manual" dusk="header-manual">マニュアル
						<div class="manual_tooltip">
							<div class="manual_tooltip_title">
								マニュアルダウンロード
							</div>
							<div class="manual_tooltip_item">
								<a class="manual_download" href="{{ route('operation_manual_download') }}" dusk="operation-manual-download">操作マニュアル</a>
								<a class="manual_download" href="{{ route('transmission_manual_download') }}" dusk="transmission-manual-download">伝送マニュアル</a>
							</div>
						</div>
					</li>
          <li class="header_inquiry">
            お問い合わせ
            <div class="inquiry_tooltip">
              <div class="inquiry_tooltip_title">
                ケアデイジー顧客問い合わせ窓口
              </div>
              <div class="inquiry_tooltip_item">
                <p class="inquiry_tel">03-6812-1231</p>
                <p class="inquiry_time">平日9:00～12:00 / 13:00～18:00</p>
                <a class="inquiry_mail" href="mailto:daisy_support@kusurinomadoguchi.co.jp">メールで問い合わせる</a>
              </div>
            </div>
          </li>
          <li class="header_logout">
            ログアウト
            <div id="application_header_logout_menu" class="logout_tooltip">
              <p class="logout_name">{{ Auth::user()->account_name }} 様</p>
              <p class="logout_question">ログアウトしますか？</p>
              <div class="logout_btn_wrap">
                <a class="logout_btn" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">はい</a>
                <a id="application_header_logout_cancel_btn" class="logout_btn cancel">いいえ</a>
              </div>
            </div>
          </li>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </ul>
      </div>
    </div>
    <div class="header_sub">
      <p class="login_name">{{ Auth::user()->account_name }} 様</p>
    </div>
  </header>

  {{-- メインコンテンツ --}}
  <div id="caredaisy_contents">
    <input type="hidden" id="updating_flg" value="false" />
    <div id="update_dialog" class="update_dialog_hidden update_dialog" dusk="update-dialog">
      <div id="update_dialog_window" class="update_dialog_window">
        <div class="loading_icon" ></div>
      </div>
    </div>
    <input type="hidden" id="changed_flg" value="false" />
    <input type="hidden" id="clicked_tab_or_user" value="" />
    <div id="confirm_dialog" class="confirm_dialog_hidden confirm_dialog">
      <div id="confirm_dialog_window" class="confirm_dialog_window">
        <p id="confirm_dialog_message"></p>
        <div class="confirm_dialog_button_group">
          <button class="caredaisy_submit_btn" id="confirm_dialog_yes">はい</button>
          <button class="caredaisy_submit_btn" id="confirm_dialog_no">いいえ</button>
        </div>
      </div>
    </div>
    @yield('contents')
  </div>

  {{-- 確認ダイアログ --}}
  <div class="caredaisy_confirmation_dialog caredaisy_confirmation_dialog_hidden">
    <div class="caredaisy_confirmation_dialog_window">
      <p class="caredaisy_confirmation_dialog_message"></p>
      <div>
        <button class="caredaisy_confirmation_dialog_btn caredaisy_confirmation_dialog_yes">はい</button>
        <button class="caredaisy_confirmation_dialog_btn caredaisy_confirmation_dialog_no">いいえ</button>
      </div>
    </div>
  </div>

  <script src="{{ mix('/js/layouts/layout.js') }}"></script>
  @yield('script')
  <script type="module" src="{{ mix('/js/group_home/change_alert.js') }}" defer></script>
</body>
</html>
