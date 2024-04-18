<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <!-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること　-->
    <!-- <link rel='stylesheet' href="{{ mix('/css/group_home/transmit_info/print.css') }}"> -->
    <title>介護予防・日常生活支援総合事業費支払決定額内訳書&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</title>
  </head>
  <body>
    @for ($s = 0; $s < count($rec); $s++)
    <div class="landscape">
      <div class="header">
        <div class="title">
          <div class="title_left">国保連合会→事業所</div>
          <div class="title_main">
            介護予防・日常生活支援総合事業費支払決定額内訳書
          </div>
        </div>
        <div class="header_content">
          <div class="header_left">
            <table class="header_table">
              <tr>
                <td>事 業 所 番 号</td>
                <td>事 業 所 名</td>
              </tr>
              <tr>
                <td>{{ $facilityNumber }}</td>
                <td>{{ $facilityName }}</td>
              </tr>
            </table>
          </div>
          <div class="header_middle">{{ $examinationDate }} 審査分</div>
          <div class="">
            <p class="header_right">{{ $dateOfCreation }}</p>
            <p class="header_right">{{ $s + 1 }} / {{ count($rec) }} 頁</p>
            <p class="header_right">{{ $kokuhoren }}</p>
          </div>
        </div>
      </div>
      <div class="contents">
        <table class="table_contets">
          <tr>
            <td rowspan="2" class="width1_td">
              <p>保険者番号</p>
              <p>（公費負担者 番号）</p>
            </td>
            <td rowspan="2" class="width1_td">
              <p>サービス</p>
              <p>提供年月</p>
            </td>
            <td rowspan="2" class="width1_td">
              <p>サービス</p>
              <p>種類名</p>
            </td>
            <td colspan="4">審 査 決 定</td>
            <td rowspan="2" class="width2_td">
              <p>保険者（公費負担者）</p>
              <p>負担金額</p>
            </td>
            <td rowspan="2" class="width2_td">備 考</td>
          </tr>
          <tr>
            <td class="width3_td">件 数</td>
            <td class="width3_td">
              <p>日数</p>
              <p>（回数）</p>
              <p>日</p>
              <p>（回）</p>
            </td>
            <td class="width4_td">
              <p>単 位 数</p>
              <p>単 位</p>
            </td>
            <td class="width4_td">
              <p>金 額</p>
              <p>円</p>
            </td>
          </tr>
          @foreach($rec[$s] as $index=> $r)
          <tr class="record">
            <td>{{ $r['insurerNo'] }}</td>
            <td>{{ $r['dateOfProvision'] }}</td>
            <td class="kind_name">{{ $r['kindName'] }}</td>
            <td class="money">{{ $r['numberOfService'] }}</td>
            <td class="money">{{ $r['daysOfService'] }}</td>
            <td class="money">{{ $r['numberOfUnits'] }}</td>
            <td class="money">{{ $r['amount'] }}</td>
            <td class="money">{{ $r['longTermCareBenefits'] }}</td>
            <td></td>
          </tr>
          @endforeach
          @for ($i = 0; $i < 10 - count($rec[$s]); $i++)
          <tr class="record">
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          @endfor

          @php
            $endFlag = count($rec) == ($s + 1) ? true : false;
          @endphp
          <tr>
            <td>審査決定</td>
            <td>総合事業費</td>
            <td><img class="diagonal" src="{{ $diagonal_base64 }}"></td>
            <td class="money">{{ $endFlag ? $t1NumberOfService : "" }}</td>
            <td class="money">{{ $endFlag ? $t1DaysOfService : "" }}</td>
            <td class="money">{{ $endFlag ? $t1NumberOfUnits : "" }}</td>
            <td class="money">{{ $endFlag ? $t1Amount : "" }}</td>
            <td class="money">{{ $endFlag ? $t1LongTermCareBenefits : "" }}</td>
            <td></td>
          </tr>
          <tr class="clear_record">
            <td class="clear_record">&nbsp;</td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
            <td class="clear_record"></td>
          </tr>
          <tr>
            <td>過誤調整</td>
            <td>総合事業費</td>
            <td><img class="diagonal" src="{{ $diagonal_base64 }}"></td>
            <td class="money">{{ $endFlag ? $t2NumberOfService : "" }}</td>
            <td class="money">{{ $endFlag ? $t2DaysOfService : "" }}</td>
            <td class="money">{{ $endFlag ? $t2NumberOfUnits : "" }}</td>
            <td class="money">{{ $endFlag ? $t2Amount : "" }}</td>
            <td class="money">{{ $endFlag ? $t2LongTermCareBenefits : "" }}</td>
            <td></td>
          </tr>
          <tr>
            <td>支払決定</td>
            <td>総合事業費</td>
            <td><img class="diagonal" src="{{ $diagonal_base64 }}"></td>
            <td class="money">{{ $endFlag ? $t3NumberOfService : "" }}</td>
            <td class="money">{{ $endFlag ? $t3DaysOfService : "" }}</td>
            <td class="money">{{ $endFlag ? $t3NumberOfUnits : "" }}</td>
            <td class="money">{{ $endFlag ? $t3Amount : "" }}</td>
            <td class="money">{{ $endFlag ? $t3LongTermCareBenefits : "" }}</td>
            <td></td>
          </tr>
        </table>
      </div>
      <div class="footer">
        <p>&nbsp;</p>
        <p>
          ※過誤調整の内訳については、介護予防・日常生活支援総合事業費過誤決定通知書、介護予防・日常生活支援総合事業費再審査決定通知書に記載しています。
          SICL21(7551)
        </p>
      </div>
    </div>
    @endfor
  </body>
</html>
