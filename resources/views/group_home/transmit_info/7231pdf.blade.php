<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること　-->
    {{-- <link rel='stylesheet' href="{{ mix('/css/group_home/transmit_info/print.css') }}"> --}}
    <title>介護予防・日常生活支援総合事業審査決定増減表</title>
  </head>
  <body>
    @for ($s = 0; $s < count($rec); $s++)
    <div class="landscape">
      <div class="header_container">
        <div class="left_contents">
          <div class="facility_number">
            <table class="facility_number_table">
              <tr class="facility_number_tr">
                <td>事業所番号</td>
                <td>{{ $facilityNumber }}</td>
              </tr>
            </table>
          </div>
          <div class="facility_name">
            <table class="facility_name_table">
              <tr class="facility_name_tr">
                <td>事業所名</td>
                <td>{{ $facilityName }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="center_contents">
          <div class="main_title_7231">介護予防・日常生活支援総合事業審査決定増減表</div>
          <div class="">{{ $examinationDate }} 審査分</div>
        </div>

        <div class="right_contents">
          <p>{{ $dateOfCreation }}</p>
          <p>{{ $s + 1 }} / {{ count($rec) }} 頁</p>
          <p>{{ $kokuhoren }}</p>
        </div>

      </div>
      <div class="contents">
        <table class="table_contets pdf_7231">
          <tr class="table_header">
            <td rowspan="2" class="td_width1_7231">
              <p>保険者番号</p>
            </td>
            <td rowspan="2" class="td_width2_7231">
              <p>サービス</p>
              <p>提供年月</p>
            </td>
            <td colspan="2">請　求　差</td>
            <td colspan="2">返　戻</td>
            <td colspan="2">査　定　増　減</td>
            <td colspan="2">保　留　分</td>
            <td colspan="2">保　留　復　活　分</td>
            <td rowspan="2" class="td_width2_7231">備 考</td>
          </tr>
          <tr class="table_header">
            <td class="td_width2_7231">件 数</td>
            <td class="td_width3_7231"><p>金　額</p></td>
            <td class="td_width2_7231">件 数</td>
            <td class="td_width3_7231"><p>単位数</p></td>
            <td class="td_width2_7231">件 数</td>
            <td class="td_width3_7231"><p>単位数</p></td>
            <td class="td_width2_7231">件 数</td>
            <td class="td_width3_7231"><p>単位数</p></td>
            <td class="td_width2_7231">件 数</td>
            <td class="td_width3_7231"><p>単位数</p></td>
          </tr>

          @foreach($rec[$s] as $index=> $r)
          <tr class="top_record">
            <td>{{ $r['insurerNo'] }}</td>
            <td>{{ $r['dateOfProvision'] }}</td>
            <td class="rlg_01"><div class="diagonal_line1_7231"></div></td>
            <td class="rlg_01"><div class="diagonal_line2_7231"></div></td>
            <td><p>{{ $r['returnCount'] }}</p></td>
            <td><p>{{ $r['returnUnits'] }}</p></td>
            <td><p>{{ $r['assessmentCount'] }}</p></td>
            <td><p>{{ $r['assessmentUnits'] }}</p></td>
            <td><p>{{ $r['pendingCount'] }}</p></td>
            <td><p>{{ $r['pendingUnits'] }}</p></td>
            <td><p>{{ $r['putOnHoldAndRevivalCount'] }}</p></td>
            <td><p>{{ $r['putOnHoldAndRevivalUnits'] }}</p></td>
            <td></td>
          </tr>
          @endforeach
          {{-- 以下、空レコード行を作成する --}}
          @for ($i = 0; $i < 14 - count($rec[$s]); $i++)
          <tr class="top_record">
            <td></td>
            <td></td>
            <td class="rlg_01"><div class="diagonal_line1_7231"></div></td>
            <td class="rlg_01"><div class="diagonal_line2_7231"></div></td>
            <td></td>
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

          @if (count($rec) == ($s + 1))
          <tr class="top_record">
            <td colspan="2">合計</td>
            <td><p>{{ $t1BillDifferenceCount }}</p></td>
            <td><p>{{ $t1BillDifferenceAmounts }}</p></td>
            <td><p>{{ $t1ReturnCount }}</p></td>
            <td><p>{{ $t1ReturnUnits }}</p></td>
            <td><p>{{ $t1AssessmentCount }}</p></td>
            <td><p>{{ $t1AssessmentUnits }}</p></td>
            <td><p>{{ $t1PendingCount }}</p></td>
            <td><p>{{ $t1PendingUnits }}</p></td>
            <td><p>{{ $t1PutOnHoldAndRevivalCount }}</p></td>
            <td><p>{{ $t1PutOnHoldAndRevivalUnits }}</p></td>
            <td></td>
          </tr>
          @else
          <tr class="top_record">
            <td colspan="2">合計</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          @endif
        </table>
      </div>
      <div class="footer">
        <p>&nbsp;</p>
        <p>
          ※１　この表は請求のあった介護予防・日常生活支援総合事業費のうち、審査決定に際し、<br>
          &emsp;&emsp;&emsp;請求書と請求明細書の積上げとの差、返戻、査定増減、保留のあったものについて通知するものです。
        </p>
        <p>※２　保留復活分については、前月まで保留されていたものが、復活したものです。</p>
        <p class="end_content_7231"><span>SHBL05（7231）</span></p>
      </div>
    </div>
    @endfor
  </body>
</html>
