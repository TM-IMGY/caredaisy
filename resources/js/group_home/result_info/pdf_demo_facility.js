export default class PdfDemoFacility{
  constructor(){
    this.element = document.getElementById('pdf_demo_facility_form');
  }

  printPreview(facilityUserIds,facilityId,year,month){
    // 現時点で得られているデータのパラメータを作成する
    // 作成したパラメータをurlの末尾につなぎリクエストパラメータを作る
    const url = this.element.href + "?" + 'facility_user_ids[]' + '=' + facilityUserIds + '&' + 'facility_id' + '=' + facilityId + '&' + 'year' + '=' + year + '&' + 'month' + '=' + month;
    
    // aTagでプレビュー画面を表示する
    let aTag = document.createElement('a');
    aTag.href = url;
    aTag.rel = 'noopener noreferrer';
    aTag.target = '_blank';
    aTag.click();
  }
}
