import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

export default class ServicePlan2UserInfo{
    constructor(){
        this.REQUEST_HEADER = {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN};
    }

    clearUser(){
        document.getElementById('sp2_facility_user_name').textContent = null;
        document.getElementById('sp2_care_level').textContent = null;
        document.getElementById('sp2_recognition_date').textContent = null;
        document.getElementById('sp2_care_period').textContent = null;
        document.getElementById('sp2_certification_status').textContent = null;
    }

    /**
     * ルールで決められた日付の書式を返す
     * @param {String} dateStr
     * @returns {String}
     */
    getRuleBasedDateFormat(dateStr){
        let date = new Date(dateStr);
        return date.toLocaleDateString(); // yyyy/mm/dd
    }

    /**
     * @return {Promise}
     */
    async requestData(facilityUserId){
        let date = new Date();
        return await CustomAjax.post('/group_home/care_plan_info/facility_user/get_data',this.REQUEST_HEADER,{
            clm: ['facility_user_id','first_name','last_name'],
            care_info: {
                clm_list: ['care_level_id','care_period_end','care_period_start','certification_status','facility_user_id','recognition_date'],
                month: date.getMonth()+1,
                year: date.getFullYear(),
                with:{
                    care_level:['care_level_id','care_level_name']
                }
            },
            facility_user_id_list: [facilityUserId]
        });
    }

    /**
     * @param {Object} user {facilityUserID: string, userName: string}
     */
    async setFacilityUser(user){
        this.clearUser();
        if(user.facilityUserID === null){ return; }

        let data  = await this.requestData(user.facilityUserID);
        if(data===null || data.length!==1){ return; }
        let facilityUser = data[0];
        document.getElementById('sp2_facility_user_name').textContent = facilityUser.last_name + facilityUser.first_name;
        
        let careInfo = facilityUser.care_info;
        if(careInfo===null){ return; }
        document.getElementById('sp2_care_level').textContent = careInfo.care_level.care_level_name;
        document.getElementById('sp2_recognition_date').textContent = this.getRuleBasedDateFormat(careInfo.recognition_date);
        document.getElementById('sp2_care_period').textContent =
            this.getRuleBasedDateFormat(careInfo.care_period_start) + ' - ' + this.getRuleBasedDateFormat(careInfo.care_period_end);
        document.getElementById('sp2_certification_status').textContent = careInfo.certification_status === 2 ? '認定済' : '申請中';
    }
}
