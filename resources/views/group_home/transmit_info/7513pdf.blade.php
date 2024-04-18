<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <!-- PDF出力せずにブラウザで確認するためスタイルを指定 PDF出力時はコメントアウトすること　-->
    <!-- <link rel='stylesheet' href="{{ mix('/css/group_home/transmit_info/print.css') }}"> -->
    <title>介護給付費等支払決定額通知書</title>
  </head>
  <body>
    <div class="portrait">
      <div class="address">
        <div class="facility">〒{{ $postal1 }}－{{ $postal2 }}</div>
        <div class="facility">{{ $location }}</div>
        <div class="facility">&nbsp;</div>
        <div class="facility">&nbsp;</div>
        <div class="facility">{{ $facilityName }}</div>
        <div class="facility">{{ $name }}</div>
        <div class="facility sama">様</div>
      </div>
      <hr />
      <div class="notice">
        <div class="info">
          <p class="title_7513">介護給付費等支払決定額通知書</p>
          <div class="">
            <p>{{ $examinationDate }} 審査分として下記金額を支払決定し</p>
            <p>右記銀行に送金しますので通知致します。</p>
          </div>
        </div>

        <div class="property">
          <br />
          <table>
            <tr>
              <td class="property_left_td">事業所番号</td>
              <td class="property_right_td text_align_center">{{ $facilityNumber }}</td>
            </tr>
            <tr>
              <td class="clear_record">&nbsp;</td>
              <td class="clear_record"></td>
            </tr>
            <tr>
              <td class="property_left_td">金&nbsp;&nbsp;&nbsp;&nbsp;額</td>
              <td class="property_right_td text_align_right">{{ $transferAmountOfMoney }}</td>
            </tr>
          </table>
          <div><br /></div>
          <span>{{ $bank }}</span>
          <div class="line">&nbsp;</div>
          <span>{{ $branch }}</span>
          <div class="line">&nbsp;</div>
          <p>{{ $dateOfCreation }}</p>
          <p>{{ $kokuhoren }}</p>
        </div>
      </div>
      <div class="amount">
        <div class="transfer_amount">振込金額内訳</div>
        <div>&nbsp;</div>
        <table class="summary">
          <tr>
            <td class="left_td bottom_line">介護給付費支払額</td>
            <td class="right_td bottom_line text_align_right">{{ $longTermCareBenefits }}</td>
          </tr>
        </table>
        <table class="summary">
          <tr>
            <td class="left_td bottom_line">主治医意見書作成料</td>
            <td class="right_td bottom_line text_align_right">{{ $doctorsOpinion }}</td>
          </tr>
          <tr>
            <td class="left_td bottom_line">消費税</td>
            <td class="right_td bottom_line text_align_right">{{ $doctorsOpinionConsumptionTax }}</td>
          </tr>
        </table>

        <table class="summary">
          <tr>
            <td class="left_td bottom_line">認定調査委託料</td>
            <td class="right_td bottom_line text_align_right">{{ $accreditedSurveyFee }}</td>
          </tr>
          <tr>
            <td class="left_td bottom_line">消費税</td>
            <td class="right_td bottom_line text_align_right">{{ $accreditedSurveyFeeConsumptionTax }}</td>
          </tr>
        </table>

        <table class="summary">
          <tr>
            <td class="left2_td bottom_line">
              介護予防・日常生活支援総合事業費支払額
            </td>
            <td class="right2_td bottom_line text_align_right">{{ $ComprehensiveBusinessFee }}</td>
          </tr>
          <tr>
            <td class="left2_td bottom_line">
              電子証明書発行手数料（消費税を含む）
            </td>
            <td class="right2_td bottom_line text_align_right">{{ $digitalCertificateFee }}</td>
          </tr>
        </table>

        <table class="summary">
          <tr>
            <td class="left_td bottom_line">介護給付費等合計</td>
            <td class="right_td bottom_line text_align_right">{{ $totalAmount }}</td>
          </tr>
        </table>
      </div>
      <div class="bottom">SIBL01（7513）</div>
    </div>
  </body>
</html>
