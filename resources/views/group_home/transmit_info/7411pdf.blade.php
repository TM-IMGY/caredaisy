<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること　-->
    {{-- <link rel='stylesheet' href="{{ mix('/css/group_home/transmit_info/print.css') }}"> --}}
    <title>請求明細書・給付管理票返戻（保留）一覧表</title>
  </head>
  <body>
    @for ($s = 0; $s < count($rec); $s++)
    <div class="landscape pdf_7411">
      <div class="header_container">
        <div class="left_contents pdf_7411">
          <div class="facility_number pdf_7411">
            <table class="facility_number_table">
              <tr class="facility_number_tr pdf_7411">
                <td>事業所（保険者）番号</td>
                <td>{{ $facilityNumber }}</td>
              </tr>
            </table>
          </div>
          <div class="facility_name pdf_7411">
            <table class="facility_name_table">
              <tr class="facility_name_tr pdf_7411">
                <td>事業所（保険者）名</td>
                <td>{{ $facilityName }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="center_contents pdf_7411">
          <div class="title_main">請求明細書・給付管理票返戻（保留）一覧表</div>
          <div class="">{{ $examinationDate }} 審査分</div>
        </div>

        <div class="right_contents pdf_7411">
          <p>{{ $dateOfCreation }}</p>
          <p>{{ $s + 1 }} / {{ count($rec) }} 頁</p>
          <p>{{ $kokuhoren }}</p>
        </div>
      </div>

      <div class="contents">
        <table class="table_contets">
          <tr class="table_header">
            <td class="insure_td">
              <p>保険者（事業所）番号</p>
              <p>保険者（事業所）名</p>
            </td>
            <td class="insured_td">
              <p>被保険者番号</p>
              <p>被保険者氏名</p>
            </td>
            <td class="type_td">種別</td>
            <td class="service_date_td">
							<p>サービス</p>
							<p>提供年月</p>
            </td>
            <td class="service_type_td">
							<p>サービス</p>
							<p>種類</p>
            </td>
            <td class="service_item_td">
							<p>サービス</p>
							<p>項目等</p>
            </td>
            <td class="unit_td">
							<p>単位数</p>
							<p>特定入所者介護費等</p>
            </td>
            <td class="reason_td">事由</td>
            <td class="content_td">内容</td>
            <td class="remark_td">備考</td>
          </tr>

          @foreach ($rec[$s] as $index=> $r)
          <tr class="top_record pdf_7411">
            <td>{{ $r['insurerNo'] }}</td>
            <td>{{ $r['insuredNo'] }}</td>
            <td rowspan="2">{{ $r['type'] }}</td>
            <td rowspan="2">{{ $r['dateOfProvision'] }}</td>
            <td rowspan="2">{{ $r['kindCode'] }}</td>
            <td rowspan="2">{{ $r['serviceItemCode'] }}</td>
            <td rowspan="2">{{ $r['unit'] }}</td>
            <td rowspan="2">{{ $r['reason'] }}</td>
            <td rowspan="2" class="content pdf_7411">{{ $r['content'] }}</td>
            <td rowspan="2">{{ $r['remark'] }}</td>
          </tr>
          <tr class="top_record pdf_7411">
            <td class="text_align_left">{{ $r['insurerName'] }}</td>
            <td class="text_align_left">{{ $r['insuredNameKana'] }}</td>
          </tr>
          @endforeach

          {{-- 以下、空レコード行を作成する --}}
          @for ($i = 0; $i < 12 - count($rec[$s]); $i++)
          <tr class="top_record pdf_7411">
            <td></td>
            <td></td>
            <td rowspan="2"></td>
            <td rowspan="2"></td>
            <td rowspan="2"></td>
            <td rowspan="2"></td>
            <td rowspan="2"></td>
            <td rowspan="2"></td>
            <td rowspan="2" class="content pdf_7411"></td>
            <td rowspan="2"></td>
          </tr>
          <tr class="top_record pdf_7411">
            <td></td>
            <td></td>
            </tr>
          @endfor
        </table>
      </div>

      <div class="footer">
        <p>&nbsp;</p>
        <p>※　種別　　：サ…サービス計画費請求明細書、請…請求明細書、給…給付管理票</p>
        <p>※　サービス項目等：審査エラーによる返戻のうち、明細情報と特定入所者情報のエラーにはサービス項目コード、特定情報のエラーに識別」番号が出力されます</p>
        <p class="justify_content">※　備考の保留は、当月審査分において居宅介護支援事業者から給付管理票の提出がないため、保留扱いしたものである。<span class="style_num pdf_7411">SHDL01(7411)</span></p>
      </div>
    </div>
    @endfor
  </body>
</html>
