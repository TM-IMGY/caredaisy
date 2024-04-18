export default class UsageFeeAll{
    constructor(){
        this.element = document.getElementById('usage_fee_all_form');
    }

    createParameter(name,value){
        let param = document.createElement('input');
        param.type = 'hidden';
        param.name = name;
        param.value = value;
        this.element.appendChild(param);
        return param;
    }

    /**
     * @param {Number} month
     */
    submit(facilityId, year, month, facilityUserIds, eventType, issueDate){
        let m = (('0'+month).slice(-2));
        let targetMonth = year+'-'+m+'-01';
        let lastDay = new Date(year,month,0).getDate();
        let endOfMonth = year+'-'+m+'-'+lastDay;
        this.createParameter('facility_id',facilityId);
        this.createParameter('target_month',targetMonth);
        this.createParameter('facility_user_ids',facilityUserIds);
        this.createParameter('event_type',eventType);
        this.createParameter('end_of_month',endOfMonth);
        this.createParameter('issue_date', issueDate);
        this.element.submit();
    }
}
