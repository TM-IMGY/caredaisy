<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title></title>
    <link rel='stylesheet' href="{{ mix('/css/external_capture.css') }}">
    <link rel='stylesheet' href="{{ mix('/css/layouts/layout.css') }}">
</head>
<body>

{{-- ログアウトメニュー --}}
<a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" style="float:right;">
{{-- {{ __('Logout') }} --}}
ログアウト
</a>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
@csrf
</form>


<div>
    <div id='title'>外部利用者情報取込</div>
    <form action="{{ route('csv_regist') }}" method="post" id='contents'>
        @csrf
        <div class='item'>
            <div class='item_name'>取込ファイル形式種別<span class="mandatory-color">*必須</span></div>
            <select name="file_type" id='file_type' required>
                <option  disabled selected></option>
                <option value="1">ケア記録アプリ</option>
            </select>
        </div>

        <div class='item flex'>
            <div>
                <div class='item_name'>介護事業所番号<span class="mandatory-color">*必須</span></div>
                <input type='text' id='facility_number' required>
            </div>
            <div class='right_item'>
                <div class='item_name'>事業所名称</div>
                <p id="facility_name_display" style="margin:0px;">&nbsp;</p>
                <input type='hidden' id='facility_name'>
            </div>
        </div>

        <div class='item'>
            <div class='item_name'>サービス種別<span class="mandatory-color">*必須</span></div>
            <select name="service_type" id='service_type_select' required>
            </select>
        </div>

        <div class='item'>
            <div class='item_name'>利用者情報ファイル選択<span class="mandatory-color">*必須（出力の場合入力不要）</span></div>
            <div class='flex'>
                <input type='text' id='file_name'>
                <label>
                    <input type="file" id="select_file">参照
                </label>
            </div>
        </div>

        <div class='item'>
            <button type="button" id="register">登録</button>
            <button Type="button" id="output">出力</button>
        </div>
    </form>

    <div id='false_num_popup_area' class='popup_area'>
        <div id='false_num_popup' class='popup'>
            <div class='popup_message'>正しい介護事業所番号を入力してください。</div>
            <button Type="button" id="false_num_popup_close" class="close">閉じる</button>
        </div>
    </div>

    <div id='already_regist_popup_area' class='popup_area'>
        <div id='already_regist_popup' class='popup'>
            <div class='popup_message'>すでに登録済みです</div>
            <div class='popup_message' id='already_regist_row'></div>
            <button Type="button" id="already_regist_popup_close" class="close">閉じる</button>
        </div>
    </div>

    <div id='not_data_popup_area' class='popup_area'>
        <div id='not_data_popup' class='popup'>
            <div class='popup_message'>登録対象データがファイルに未設定です。</div>
            <button Type="button" id="not_data_popup_close" class="close">閉じる</button>
        </div>
    </div>

    <div id='regist_popup_area' class='popup_area'>
        <div id='regist_popup' class='popup'>
            <div class='popup_message'>登録完了しました。</div>
            <div class='popup_message' id='row_num'></div>
            <div class='popup_message' id='capture_count'></div>
            <div class='popup_message' id='captured_count'></div>
            <div class='popup_message' id='new_record'></div>
            <button Type="button" id="regist_popup_close" class="close">閉じる</button>
        </div>
    </div>

    <div id="external_dialog" class="external_dialog_hidden">
        <div id="external_dialog_window">
            <p>対象の「ファイル形式・介護事業所番号」の利用者紐づけ情報が登録されておりません。</p>
            <div>
                <button class="external_dialog_close" id="external_closebtn">閉じる</button>
            </div>
        </div>
    </div>

    <script type="module" src="{{ mix('/js/external_capture.js') }}"></script>
</div>

    <input type="hidden" id="updating_flg" value="false" />
    <div id="update_dialog" class="update_dialog_hidden update_dialog">
      <div id="update_dialog_window" class="update_dialog_window">
        <div class="loading_icon" ></div>
      </div>
    </div>


</body>
</html>
