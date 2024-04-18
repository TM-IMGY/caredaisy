export default class PdfDemoAll{
    constructor(){
        this.element = document.getElementById('pdf_demo_all_form');
    }
    // パラメータの作成
    createParameter(facilityUserIds,facilityId,year,month){
        let parameter = '?';
        // 配列の対応
        for (let i = 0 ; i < facilityUserIds.length ; i++){
            parameter += 'facility_user_ids[]=' + facilityUserIds[i] + '&';
        }
        return parameter + 'facility_id' + '=' + facilityId + '&' + 'year' + '=' + year + '&' + 'month' + '=' + month;
    }

    // 印刷プレビューをaTageで開く
    printPreview(facilityUserIds,facilityId,year,month){
        // URLの作成
        const url = this.element.href + this.createParameter(facilityUserIds,facilityId,year,month);

        // aTagでプレビュー画面を表示する
        let aTag = document.createElement('a');
        aTag.href = url;
        aTag.rel = 'noopener noreferrer';
        aTag.target = '_blank';
        aTag.click();
    }

}
