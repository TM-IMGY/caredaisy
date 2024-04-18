export default class CareBenefitStatement{
    /**
     * @param {NationalHealth} nationalHealth
     * @returns {element}
     */
    constructor(nationalHealth){
        this.element = document.getElementById('dep_pdf_demo_form');
        this.nationalHealth = nationalHealth;
    }

    printPreview(facilityId,facilityUserId,year,month){
        // 国保連請求画面が全てのパラメーターを持たない場合、遷移しない
        if(!(this.nationalHealth.hasAllParam())){ return; }

        // 現時点で得られているデータのパラメータを作成する
        // 作成したパラメータをurlの末尾につなぎリクエストパラメータを作る
        const url = this.element.href + "?" + '&' + 'facility_id' + '=' + facilityId + '& '+ 'facility_user_id' + '=' + facilityUserId + '&' + 'year' + '=' + year + '&' + 'month' + '=' + month;

        // aTagでプレビュー画面を表示する
        let aTag = document.createElement('a');
        aTag.href = url;
        aTag.rel = 'noopener noreferrer';
        aTag.target = '_blank';
        aTag.click();
    }
}
