@extends('layouts.application')
@section('title', 'サービスコード選択')

@section('script')

@endsection
@section('content')

<link rel='stylesheet' href="{{ mix('/css/extra.css') }}">


<div id="caredaisy_contents">
    <div id="caredaisy_contents_header">
        <div id="caredaisy_contents_header_caption">サービス提供表</div>
    </div>
    <div class="infoarea"><!--情報表示テーブル-->
        <div class="infobox">
            <table>
              <!--foreach予定-->
              <caption>基本情報</caption>
              <tr>
                <td style="width:120px;">被保険者氏名</td><!--被保険者氏名-->
                <td>DBデータ表示</td><!--被保険者氏名-->
              </tr>
              <tr>
                <td>DBデータ表示</td><!--被保険者氏名（カナ）-->
                <td>DBデータ表示</td><!--被保険者氏名（カナ）-->
              </tr>
              <tr>
                <td>生年月日</td><!--生年月日-->
                <td>DBデータ表示</td><!--生年月日-->
              </tr>
              <tr>
                <td>性別</td><!--性別-->
                <td>DBデータ表示</td><!--性別-->
              </tr>
              <tr>
                <td>保険者番号</td><!--保険者番号-->
                <td>DBデータ表示</td><!--保険者番号-->
              </tr>
              <tr>
                <td>保険者名</td><!--保険者名-->
                <td>DBデータ表示</td><!--保険者名-->
              </tr>
              <tr>
                <td>被保険者番号</td><!--被保険者番号-->
                <td>DBデータ表示</td><!--被保険者番号-->
              </tr>
            </table>
        </div>
        <div class="infobox">
            <table>
                <caption>現状</caption>
                <tr>
                    <td class="infoitem">要介護状態区分</td>
                    <td>DBデータ表示</td>
                </tr>
                <tr>
                    <td>変更後要介護状態区分</td>
                    <td>DBデータ表示</td>
                </tr>
                <tr>
                    <td>居宅介護支援事業者事業所名</td>
                    <td>DBデータ表示</td>
                </tr>
                <tr>
                    <td>担当者名</td>
                    <td>DBデータ表示</td>
                </tr>
            </table>
        </div>
        <div class="infobox">
            <table>
                <caption>詳細</caption>
                <tr>
                    <td class="infoitem">区分支給限度基準額</td>
                    <td>DBデータ表示</td>
                </tr>
                <tr>
                    <td>限度額適用期間</td>
                    <td>DBデータ表示</td>
                </tr>
                <tr>
                    <td>前月までの短期入所利用日数</td>
                    <td>DBデータ表示</td>
                </tr>
            </table>
        </div>
        <div class="infobox">
            <table>
                <caption>保険者確認</caption>
                <tr>
                    <td class="infoitem">保険者確認欄</td>
                    <td>DBデータ表示</td>
                </tr>
            </table>
        </div>
    </div>


    <div id="btnarea" class="flex"><!--提供表、別表切り替えと保存ボタン-->
        <div class="flex">
            <button type="button" class="changebtn selectbtn">サービス提供表</button>
            <button type="button" class="changebtn defaultbtn">サービス提供表別表</button>
        </div>
        <div class="item-right">
            <button type="button" class="yellowbtn float-right"
              {{-- href="{{ route('save') }}" --}}
              onclick="event.preventDefault();document.getElementById('save-form').submit();">
              保存する
            </button>
            <form id="save-form" action="{{ route('service_offer_slip.store') }}" method="POST">
                @csrf
                <input type="hidden" name="facility_user_id" value="1">
                <input type="hidden" name="year" value="2021">
                <input type="hidden" name="month" value="4">
            </form>
            <div class="clone_target">
                <input type="hidden" name="service_offer">
                <input type="hidden" name="service_offer">
                <input type="hidden" name="service_offer">
                <input type="hidden" name="service_offer">
            </div>
            <script>
                let cloneTarget = document.getElementsByClassName('clone_target')[0];
                for (let i=0; i<20; i++) {
                    let clone = cloneTarget.cloneNode(true);
                    clone.children[0].name = clone.children[0].name+`[${i}][facility_id]`;
                    clone.children[1].name = clone.children[1].name+`[${i}][date_daily_rate]`;
                    clone.children[2].name = clone.children[2].name+`[${i}][service_code]`;
                    clone.children[3].name = clone.children[3].name+`[${i}][service_count_date]`;

                    clone.children[0].value = Math.floor(Math.random()*10)+1;
                    clone.children[1].value = '1010101010101010101010101010101';
                    clone.children[2].value = ['321111','326502','326304','326108'][Math.floor(Math.random()*4)];
                    clone.children[3].value = '31';
                    document.getElementById('save-form').appendChild(clone);
                }
            </script>
        </div>
    </div>

    <div id="tablearea"><!--実績テーブル-->
        <table id="table">
            <thead id="table_thead">
                <tr>
                    <th colspan="2"></th>
                    <th colspan="34">月刊サービス計画及び実績の記録</th>
                </tr>
                <tr>
                    <th rowspan="2" class="servicecontents">サービス内容</th>
                    <th rowspan="2"  class="serviceoffice">サービス事業者<br />
                        事業所名</th>
                    <th class="plan_result">日付</th>
                    <th class="flg_cell">1</th>
                    <th class="flg_cell">2</th>
                    <th class="flg_cell">3</th>
                    <th class="flg_cell">4</th>
                    <th class="flg_cell">5</th>
                    <th class="flg_cell">6</th>
                    <th class="flg_cell">7</th>
                    <th class="flg_cell">8</th>
                    <th class="flg_cell">9</th>
                    <th class="flg_cell">10</th>
                    <th class="flg_cell">11</th>
                    <th class="flg_cell">12</th>
                    <th class="flg_cell">13</th>
                    <th class="flg_cell">14</th>
                    <th class="flg_cell">15</th>
                    <th class="flg_cell">16</th>
                    <th class="flg_cell">17</th>
                    <th class="flg_cell">18</th>
                    <th class="flg_cell">19</th>
                    <th class="flg_cell">20</th>
                    <th class="flg_cell">21</th>
                    <th class="flg_cell">22</th>
                    <th class="flg_cell">23</th>
                    <th class="flg_cell">24</th>
                    <th class="flg_cell">25</th>
                    <th class="flg_cell">26</th>
                    <th class="flg_cell">27</th>
                    <th class="flg_cell">28</th>
                    <th class="flg_cell">29</th>
                    <th class="flg_cell">30</th>
                    <th class="flg_cell">31</th>
                    <th rowspan="2" class="count">回数</th>
                    <th rowspan="2" class="total">合計</th>
                </tr>
                <tr>
                    <th>曜日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                </tr>
            </thead>
            <tbody id="table_tbody1">
                <tr id="tagert_1">
                    <td rowspan="2" class="servicecontents">
                        <dialog>
                            <!--ダイアログ中身-->
                        </dialog>
                        <button class="deletedialogopen">ゴミ箱</button>
                    </td>
                    <td class="serviceoffice"></td>
                    <th class="plan_result">予定</th>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="flg_cell"></td>
                    <td class="count"></td>
                    <td class="total"></td>
                </tr>
                <tr id="tagert_2">
                    <td>DBデータ表示</td>
                    <th>実績</th>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="servicecodebtnarea">
        <button class="servicecodebtn" id="js-show-popup"></button>
    </div>
</div>


<!--↓↓ ポップアップ ↓↓-->

<!--↓↓ ポップアップ表示内容 ↓↓-->
<div>
    <div class="popup" id="js-popup">
    <div class="popup-inner">
        <button class="close-btn" id="js-close-btn" ><span>x</span></button>

        <!--↓↓ 事業所 ↓↓-->

        <div class="office">
        <div class="service_title">サービスコード選択</div>
        <form id="servicecode_ui" method="" action="">
        　<div class="caption">事業所</div>
            <select>
            @foreach($service_codes as $service_code)
                <option value="{{$service_code->facility_name_kanji}}">{{$service_code->facility_name_kanji}}</option>
            @endforeach
            </select>
        </div>

        <!--↑↑ 事業所 ↑↑-->

        <!--↓↓ 種別 ↓↓-->

        <div class="service_type">
        　<div class="caption">種類</div>
            <select>
            @foreach($service_types as $service_type)
                <option value="{{$service_type->service_type_code}}">{{$service_type->service_type_code}}</option>
            @endforeach
            </select>
        </div>

        <!--↑↑ 種別 ↑↑-->

        <!--↓↓ サービスコード、サービス内容 ↓↓-->

        <table id="table">
            <thead id="table_thead">
            <tr>
                <th class="service_code">サービスコード</th>
                <th class="service_contents">サービス内容</th>
            </tr>
            </thead>
            <tbody id="table_tbody2">
            @foreach($service_codes as $service_code)
            <tr id="target" onclick="serect_row(this)">
                <th>
                    <option class="service_code" value="{{$service_code->facility_name_kanji}}">{{$service_code->facility_name_kanji}}</option>
                </th>
                <th>
                    <option class="service_contents" value="{{$service_code->location}}">{{$service_code->location}}</option>
                </th>
            </tr>
            @endforeach
            </tbody>
        </table>

        <!--↑↑ サービスコード、サービス内容 ↑↑-->

        <!--↓↓ 登録 閉じる ↓↓-->
        <div id="btnarea">
            <button class="button" id="savebtn" type="submit">登録</button>
            <button class="button" id="cancelbtn" type="button">キャンセル</button>
        </div>
        </form>
        <!--↑↑ 登録 閉じる ↑↑-->
        </div id="servicecodebtnarea">
        <div class="black-background" id="js-black-bg"></div>
    </div>
    <!--↑↑ ポップアップ表示内容 ↑↑-->
</div>

<!--↓↓ ポップアップ表示内容CSS ↓↓-->
<style>
    .service_title{
        font-size: 36px;
        font-weight: bold;
    }
    .popup {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: .6s;
    }
    .popup.is-show {
    opacity: 1;
    visibility: visible;
    }
    .popup-inner {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
    width: 80%;
    max-width: 600px;
    padding: 50px;
    background-color: #fff;
    z-index: 2;
    }
    .popup-inner img {
    width: 100%;
    }
    .close-btn {
    position: absolute;
    right: 0;
    top: 0;
    width: 40px;
    height: 40px;
    text-align: center;
    cursor: pointer;
    }
    .close-btn span {
    position: absolute;
    font-size: 400%;
    color: #333;
    -webkit-transform : translate(-50%,-50%,-50%,-50%);
    transform : translate(-50%,-55%);

    }
    .black-background {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,.8);
    z-index: 1;
    cursor: pointer;
    }
</style>
<!--↑↑ ポップアップ表示内容CSS ↑↑-->


<!--↓↓ ポップアップ表示内容JS ↓↓-->
<script>

    function popupImage() {
    var popup = document.getElementById('js-popup');
    if(!popup) return;

    var blackBg = document.getElementById('js-black-bg');
    var closeBtn = document.getElementById('js-close-btn');
    var showBtn = document.getElementById('js-show-popup');

    closePopUp(blackBg);
    closePopUp(closeBtn);
    closePopUp(showBtn);
    function closePopUp(elem) {
        if(!elem) return;
        elem.addEventListener('click', function() {
        popup.classList.toggle('is-show');
        });
    }
    }
    popupImage();

</script>

<!--↑↑ ポップアップ表示内容JS ↑↑-->





<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

<script>

    $(function () {
        tr_default("#table_tbody2 tr");
        $("#table_tbody2 tr").click(function () {
            tr_default("#table_tbody2 tr");
            tr_click($(this));
        });
    });

    function tr_default(vTR) {
        $(vTR).css("background-color", "#ffffff");
        $(vTR).mouseover(function () {
            $(this).css("background-color", "#CCFFCC").css("cursor", "pointer")
        });
        $(vTR).mouseout(function () {
            $(this).css("background-color", "#ffffff").css("cursor", "normal")
        });
    }

    function tr_click(trID) {
        trID.css("background-color", "#e49e61");
        trID.mouseover(function () {
            $(this).css("background-color", "#CCFFCC").css("cursor", "pointer")
        });
        trID.mouseout(function () {
            $(this).css("background-color", "#e49e61").css("cursor", "normal")
        });
    }

    </script>





