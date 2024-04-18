<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること　-->
    {{-- <link rel='stylesheet' href="{{ mix('/css/group_home/transmit_info/print.css') }}"> --}}
    <title>介護保険審査決定増減表</title>
  </head>
  <body>
    @for ($s = 0; $s < count($rec); $s++)
    <div class="landscape pdf_7211">
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
          <div class="facility_name pdf_7211">
            <table class="facility_name_table">
              <tr class="facility_name_tr">
                <td>事業所名</td>
                <td>{{ $facilityName }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="center_contents">
          <div class="title_main">介護保険審査決定増減表</div>
          <div class="">{{ $examinationDate }} 審査分</div>
        </div>

        <div class="right_contents">
          <p>{{ $dateOfCreation }}</p>
          <p>{{ $s + 1 }} / {{ count($rec) }} 頁</p>
          <p>{{ $kokuhoren }}</p>
        </div>

      </div>
      <div class="contents">
        <table class="table_contets">
          <tr class="table_header">
            <td rowspan="2" class="width5_td">
              <p>保険者番号</p>
            </td>
            <td rowspan="2" class="width1_td pdf_7211">
              <p>サービス</p>
              <p>提供年月</p>
            </td>
            <td colspan="2">請　求　差</td>
            <td colspan="2">返　戻</td>
            <td colspan="2">査　定　増　減</td>
            <td colspan="2">保　留　分</td>
            <td colspan="2">保　留　復　活　分</td>
            <td rowspan="2" class="width2_td pdf_7211">備 考</td>
          </tr>
          <tr class="table_header">
            <td class="width3_td pdf_7211">件 数</td>
            <td class="width1_td pdf_7211">
                <p>金　額</p>
                <p class="text_small">特定入所者介護費等</p>
            </td>
            <td class="width3_td pdf_7211">件 数</td>
            <td class="width1_td pdf_7211">
                <p>単位数</p>
                <p class="text_small">特定入所者介護費等</p>
            </td>
            <td class="width3_td pdf_7211">件 数</td>
            <td class="width1_td pdf_7211 assessment">
                <p>単位数</p>
                <p class="text_small">特定入所者介護費等</p>
            </td>
            <td class="width3_td pdf_7211">件 数</td>
            <td class="width1_td pdf_7211">
                <p>単位数</p>
                <p class="text_small">特定入所者介護費等</p>
            </td>
            <td class="width3_td pdf_7211">件 数</td>
            <td class="width1_td pdf_7211">
                <p>単位数</p>
                <p class="text_small">特定入所者介護費等</p>
            </td>
          </tr>

          @foreach($rec[$s] as $index=> $r)
          <tr class="top_record">
            <td>{{ $r['insurerNo'] }}</td>
            <td>{{ $r['dateOfProvision'] }}</td>
            <td class="rlg_01">
              <div class="diagonal_line1"></div>
            </td>
            <td class="rlg_01">
              <div class="diagonal_line2"></div>
            </td>
            <td>
              <p>{{ $r['nursingCareReturns'] }}</p>
              <p>{{ $r['numberOfReturnsOfSpecifiedResidents'] }}</p>
            </td>
            <td>
              <p>{{ $r['nursingCareReturnUnits'] }}</p>
              <p>{{ $r['returnSpecificResidentServiceFee'] }}</p>
            </td>
            <td>
              <p>{{ $r['numberOfExaminationsForNursingCare'] }}</p>
              <p>{{ $r['numberOfScreeningsForSpecifiedResidents'] }}</p>
            </td>
            <td>
              <p>{{ $r['nursingCareExaminationCredits'] }}</p>
              <p>{{ $r['examinationSpecificResidentServiceFee'] }}</p>
            </td>
            <td>
              <p>{{ $r['numberOfPendingNursingCare'] }}</p>
              <p>{{ $r['numberOfPendingCasesForSpecificResidents'] }}</p>
            </td>
            <td>
              <p>{{ $r['nursingCarePendingUnits'] }}</p>
              <p>{{ $r['retainedSpecificResidentServiceFee'] }}</p>
            </td>
            <td>
              <p>{{ $r['nursingCarePutOnHoldAndReinstated'] }}</p>
              <p>{{ $r['numberOfPendingReinstatementCasesForSpecificResidents'] }}</p>
            </td>
            <td>
              <p>{{ $r['nursingCareSuspensionReinstatementUnits'] }}</p>
              <p>{{ $r['specificResidentServiceFeeForPendingReinstatement'] }}</p>
            </td>
            <td></td>
          </tr>
          @endforeach
          {{-- 以下、空レコード行を作成する --}}
          @for ($i = 0; $i < 14 - count($rec[$s]); $i++)
          <tr class="top_record">
            <td></td>
            <td></td>
            <td class="rlg_01">
              <div class="diagonal_line1"></div>
            </td>
            <td class="rlg_01">
              <div class="diagonal_line2"></div>
            </td>
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
            <td>
              <p>{{ $t1NursingCareBillDifferenceNumber }}</p>
              <p>{{ $t1NumberOfBillingDiscrepanciesForSpecifiedResidents }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCareBillDifferentialUnits }}</p>
              <p>{{ $t1BillingDifferenceSpecificResidentServiceFee }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCareReturns }}</p>
              <p>{{ $t1NumberOfReturnsOfSpecifiedResidents }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCareReturnUnits }}</p>
              <p>{{ $t1ReturnSpecificResidentServiceFee }}</p>
            </td>
            <td>
              <p>{{ $t1NumberOfExaminationsForNursingCare }}</p>
              <p>{{ $t1NumberOfScreeningsForSpecifiedResidents }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCareExaminationCredits }}</p>
              <p>{{ $t1ExaminationSpecificResidentServiceFee }}</p>
            </td>
            <td>
              <p>{{ $t1NumberOfPendingNursingCare }}</p>
              <p>{{ $t1NumberOfPendingCasesForSpecificResidents }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCarePendingUnits }}</p>
              <p>{{ $t1RetainedSpecificResidentServiceFee }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCarePutOnHoldAndReinstated }}</p>
              <p>{{ $t1NumberOfPendingReinstatementCasesForSpecificResidents }}</p>
            </td>
            <td>
              <p>{{ $t1NursingCareSuspensionReinstatementUnits }}</p>
              <p>{{ $t1SpecificResidentServiceFeeForPendingReinstatement }}</p>
            </td>
            <td></td>
          </tr>
          @endif
        </table>
      </div>
      <div class="footer">
        <p>&nbsp;</p>
        <p>※１　この表は請求のあった介護給付費のうち、審査決定に際し、請求書と請求明細書の積上げとの差、返戻、査定増減、保留のあったものについて通知するものです。</p>
        <p>※２　保留復活分については、前月まで保留されていたものが、復活したものです。</p>
        <p class="justify_content">※３　下段は特定入所者介護サービス費等です。<span class="style_num">SHBL01（7211）</span></p>
      </div>
    </div>
    @endfor
  </body>
</html>
