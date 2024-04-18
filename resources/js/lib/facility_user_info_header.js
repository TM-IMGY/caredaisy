import CSRF_TOKEN from './csrf_token.js'
import CustomAjax from './custom_ajax.js'

export default class facilityUserInfoHeader{
    constructor(){
        this.REQUEST_HEADER = {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN};
        this.year = null;
        this.month = null;
        this.facilityUserID = null;
        this.viewStayOutPeriod = '';
        this.stayOutPeriod = [];
        this.head = null;
        this.stayOutStartDate = null;
        this.stayOutEndDate = null;
    }

    clearUser(){
        document.querySelectorAll('.facility_user_info_text').forEach((e) => { e.textContent = ""});
        document.querySelectorAll('.stay_out_period_leader').forEach((e) => { e.style.visibility = 'hidden'});
        document.querySelectorAll('.stay_out_period').forEach((e) => { e.style.display = 'flex'});
        document.querySelectorAll('.stay_out_period_detail').forEach((e) => { e.style.display = 'none'});
    }

    /**
     * 3点リーダーを押下で詳細を3行目以降に表示する
     * @param {Object} header
     */
    createStayOutDetail(header)
    {
        header.querySelector('.stay_out_period').style.display = 'none';
        header.querySelector('.stay_out_period_detail').style.display = 'flex';
        this.viewStayOutPeriod = '';
        this.stayOutPeriod.forEach( period =>{
            this.createStayoutPeriod(period)
        })
        header.querySelector('.facility_user_info_header_stay_out_period_detail').textContent = this.viewStayOutPeriod.slice(0,-1)
    }

    /**
     * 外泊期間に表示するtextを作成
     * @param {Object} period
     */
    createStayoutPeriod(period)
    {
        this.stayOutStartEndFormat(period)
        this.viewStayOutPeriod += this.stayOutStartDate + " - " + this.stayOutEndDate + "、";
    }

    /**
     * 外泊期間を整形する
     * @param {Object} period
     */
    stayOutStartEndFormat(period)
    {
        let startDate = period.start_date;
        let endDate = period.end_date;
        let yearDiffrence = this.checkYearDiffrence(startDate,endDate)
        this.stayOutStartDate = this.stayOutPeriodDateFormat(startDate);
        this.stayOutEndDate = this.stayOutPeriodDateFormat(endDate);
        // 年跨ぎの場合
        if(yearDiffrence >= 1) {
            this.stayOutStartDate = this.getRuleBasedDateFormat(startDate);
            this.stayOutEndDate = this.getRuleBasedDateFormat(endDate);
        }
        // 外泊終了日未設定
        if(endDate == null) {
            this.stayOutEndDate = '未定';
        }
    }

    /**
     * 年を除いた外泊日を作成
     * @param {date} endDate
     * @returns
     */
    stayOutPeriodDateFormat(endDate)
    {
        let date = new Date(endDate);
        let month = (date.getMonth() + 1).toString();
        let day = (date.getDate()).toString();
        return month + '/' + day;
    }

    /**
     * 年を跨いでいるか計算
     * @param {date} startDate
     * @param {date} endDate
     * @returns
     */
    checkYearDiffrence(startDate,endDate)
    {
        let start = new Date(startDate);
        let end = new Date(endDate);
        let startYear = start.getFullYear();
        let endYear = end.getFullYear();
        return endYear - startYear;
    }

    /**
     * ルールで決められた日付の書式を返す
     * @param {String} dateStr
     * @returns {String}
     */
    getRuleBasedDateFormat(dateStr){
        let date = new Date(dateStr);
        // 利用者情報画面の場合は0埋めをする。
        if(location.href.match(/user_info/g)){
            let year = date.getFullYear();
            let month = (date.getMonth() + 1).toString().padStart(2, "0");
            let day = (date.getDate()).toString().padStart(2, "0");
            return year + '/' + month + '/' + day;
        }
        return date.toLocaleDateString(); // yyyy/mm/dd
    }

    /**
     * @return {Promise}
     */
    async requestData(){
        await CustomAjax.send(
            'GET',
            '/group_home/service/facility_user/header/get' + this.getRequestGetInfoTableParams(),
            {},
            [],
            "setInfoTable",
            this
        );
    }

    /**
     * リクエストurlの作成
     * @returns
     */
    getRequestGetInfoTableParams(){
        return '?facility_user_id='
            + this.facilityUserID
            + '&year='
            + this.year
            + '&month='
            + this.month;
    }

    /**
     * @param {Object} user {facilityUserID: string, userName: string}
     */
    async setFacilityUser(user){
        this.clearUser();
        this.facilityUserID = user.facilityUserID;
        let date = new Date();
        this.month = date.getMonth()+1;
        this.year = date.getFullYear();

        if(user.facilityUserID === null){ return; }
        await this.requestData();
    }

    /**
     *
     * @param {Object} user {facilityUserID: string, userName: string, year: date, month: date}
     * @returns
     */
    async setFacilityUserForResultInfo(user){
        this.clearUser();
        this.facilityUserID = user.facilityUserID;
        this.month = user.month;
        this.year = user.year;

        if(user.facilityUserID === null){ return; }
        this.requestData();
    }

    /**
     * リロードする。
     * @return {void}
     */
    async reload(){
        // プルダウンで選択中の対象年月をセットする
        let yearMonth = document.getElementById('year_month_pulldown').value;
        let arrayYearMonth = yearMonth.match(/([0-9]{4})\/([0-9]{1,2})/);
        this.year = Number (arrayYearMonth[1]);
        this.month = Number (arrayYearMonth[2]);

        if(this.facilityUserID && this.year && this.month){
            await this.requestData();
        }
    }

    async setInfoTable(userInformation)
    {
        if(userInformation===null){ return; }
        this.stayOutPeriod = userInformation.stay_out_periods;
        let heads = document.querySelectorAll('.facility_user_info_header');
        heads.forEach(head => {
            this.head = head;
            head.querySelectorAll('.facility_user_info_header_user_name').forEach(data =>{
                data.textContent = userInformation.last_name + userInformation.first_name;
            })

            // 実績情報画面 特有カラム
            head.querySelector('.facility_user_info_header_aggrement').textContent = userInformation.approval === 1 ? '承認' : '未承認';
            head.querySelector('.facility_user_info_header_gender').textContent = userInformation.gender === 1 ? '男性' : '女性';
            head.querySelector('.facility_user_info_header_stay_out').textContent = userInformation.stay_out + "泊";
            this.viewStayOutPeriod = '';
            userInformation.stay_out_periods.forEach( date => {
                this.createStayoutPeriod(date)
            })
            head.querySelector('.facility_user_info_header_stay_out_period').textContent = this.viewStayOutPeriod.slice(0,-1)
            // 月の外泊回数が5回以上なら省略して3点リーダー作成
            if(userInformation.stay_out_periods.length >= 5) {
                head.querySelector('.stay_out_period_leader').style.visibility = 'visible';
                // 3点リーダーを押下で全期間表示
                head.querySelector('.stay_out_period_leader').addEventListener('click',this.createStayOutDetail.bind(this,head))
            }

            if(userInformation.care_info){
                head.querySelectorAll('.facility_user_info_header_care_level').forEach(data => {
                    data.textContent = userInformation.care_info.care_level.care_level_name;
                })
                head.querySelector('.facility_user_info_header_recognition_date').textContent = this.getRuleBasedDateFormat(userInformation.care_info.recognition_date);
                if(userInformation.care_info.care_period_start && userInformation.care_info.care_period_end){
                    head.querySelectorAll('.facility_user_info_header_care_period').forEach(data => {
                        data.textContent =
                            this.getRuleBasedDateFormat(userInformation.care_info.care_period_start) + ' - ' + this.getRuleBasedDateFormat(userInformation.care_info.care_period_end);
                    })
                    head.querySelector('.facility_user_info_header_care_period').setAttribute('care_period_start',this.getRuleBasedDateFormat(userInformation.care_info.care_period_start));
                    head.querySelector('.facility_user_info_header_care_period').setAttribute('care_period_end',this.getRuleBasedDateFormat(userInformation.care_info.care_period_end));
                }
                head.querySelector('.facility_user_info_header_certification_status').textContent = userInformation.care_info.certification_status === 2 ? '認定済' : '申請中';
                head.querySelector('.facility_user_info_header_certification_status').setAttribute('value',userInformation.care_info.certification_status)
            }

            if(userInformation.independence_information){
                head.querySelector('.facility_user_info_header_independence_level').textContent =
                    this.getIndependenceLevelName(userInformation.independence_information.independence_level);
                    head.querySelector('.facility_user_info_header_independence_level').setAttribute('value',userInformation.independence_information.independence_level)

                head.querySelector('.facility_user_info_header_dementia_independence').textContent =
                    this.getDementiaIndependenceName(userInformation.independence_information.dementia_level);
                    head.querySelector('.facility_user_info_header_dementia_independence').setAttribute('value',userInformation.independence_information.dementia_level)
            }
        });
    }

    /**
     *
     * @param {number} param
     * @returns
     */
    getIndependenceLevelName(param)
    {
        let independenceLevelName = {
            1:'自立',
            2:'J1：交通機関利用可',
            3:'J2：近隣の外出可',
            4:'A1：介助で外出可',
            5:'A2：外出頻度少',
            6:'B1：車いす利用',
            7:'B2：移乗介助で車いす利用',
            8:'C1：自力で寝返り可',
            9:'C2：自力で寝返り不可',
        }
        return independenceLevelName[param];
    }

    /**
     *
     * @param {number} param
     * @returns
     */
    getDementiaIndependenceName(param)
    {
        let dementiaIndependenceName = {
            1:'自立',
            2:'Ⅰ：認知症有自立',
            3:'Ⅱ：多少意思疎通難自立',
            4:'Ⅱa：Ⅱの家庭外',
            5:'Ⅱb：Ⅱの家庭内',
            6:'Ⅲ：日常生活支障有',
            7:'Ⅲa：Ⅲの日中中心',
            8:'Ⅲb：Ⅲの夜間中心',
            9:'Ⅳ：日常生活支障頻繁',
            10:'M：専門医療必要',
        }
        return dementiaIndependenceName[param];
    }
}

