import CSRF_TOKEN from './lib/csrf_token.js'
import CustomAjax from './lib/custom_ajax.js'

export default class ExternalCapture{
    constructor(){
        this.facilityNumber = document.getElementById('facility_number')
        this.facilityNameDisplay = document.getElementById('facility_name_display')
        this.facilityName = document.getElementById('facility_name')
        this.serviceTypeSelect = document.getElementById('service_type_select')
        this.selectFile = document.getElementById('select_file')
        this.fileName = document.getElementById('file_name')
        this.outPut = document.getElementById('output')
        this.register = document.getElementById('register')
        this.falseNumPopupArea = document.getElementById('false_num_popup_area')
        this.alreadyRegistPopupArea = document.getElementById('already_regist_popup_area')
        this.registPopupArea = document.getElementById('regist_popup_area')
        this.notDataPopupArea = document.getElementById('not_data_popup_area')
        this.falseNumPopupClose = document.getElementById('false_num_popup_close')
        this.alreadyRegistPopupClose = document.getElementById('already_regist_popup_close')
        this.registpopupClose = document.getElementById('regist_popup_close')
        this.notDataPopupClose = document.getElementById('not_data_popup_close')
        this.selectFileType = document.getElementById('file_type')
        this.alreadyRegistRow = document.getElementById('already_regist_row')
        this.externalDialog = document.getElementById('external_dialog');
        this.externalClosebtn = document.getElementById('external_closebtn');

        this.serviceTypeList = {};
        this.facilityList = {};

        CustomAjax.send(
            'get',
            'admin/get_facility_list',
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            '',
            'storageFacilityList',
            this);

        this.outPut.addEventListener('click',this.exportCsv.bind(this));
        this.facilityNumber.addEventListener('change',this.insertFacilityName.bind(this));
        this.selectFile.addEventListener('change',this.insertFileName.bind(this));
        this.register.addEventListener('click',this.registUserInfo.bind(this));
        this.falseNumPopupClose.addEventListener('click',function(){ this.falseNumPopupArea.style.display = 'none';}.bind(this))
        this.alreadyRegistPopupClose.addEventListener('click',function(){ this.alreadyRegistPopupArea.style.display = 'none';}.bind(this))
        this.registpopupClose.addEventListener('click',function(){ this.registPopupArea.style.display = 'none';}.bind(this))
        this.notDataPopupClose.addEventListener('click',function(){ this.notDataPopupArea.style.display = 'none';}.bind(this))
        this.externalClosebtn.addEventListener('click',this.DialogHide.bind(this));
    }

    storageFacilityList(json){
        this.resultData = null;
        if(json !== void 0){
            this.facilityList = json
        }
    }

    //事業所名称表示
    async insertFacilityName(){
        let val = this.facilityNumber.value;
        let facility_name = null;
        let facility_id = null;

        while(this.serviceTypeSelect.lastChild) {
            this.serviceTypeSelect.removeChild(this.serviceTypeSelect.lastChild);
        }

        Object.keys(this.facilityList).forEach((key) =>{
            if(this.facilityList[key].facility_number == val){
                facility_name = this.facilityList[key].facility_name_kanji;
                facility_id = this.facilityList[key].facility_id;
            }
        })
        this.facilityName.value = facility_id;
        if (facility_name){
            this.facilityNameDisplay.innerHTML = facility_name;

            this.serviceTypeList = await CustomAjax.post(
                'admin/service_type_list',
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                {facility_id:facility_id});

            this.createServiceTypeSelect();
        }else{
            this.facilityNameDisplay.innerHTML = "&nbsp;";
            while(this.serviceTypeSelect.lastChild) {
                this.serviceTypeSelect.removeChild(this.serviceTypeSelect.lastChild);
            }
        }
    }

    createServiceTypeSelect(){
        for(let i = 0; i < this.serviceTypeList.length; i++){
            let option1 = document.createElement('option');
            option1.value = this.serviceTypeList[i]['service_type_code'];
            option1.textContent = this.serviceTypeList[i]['service_type_name'];
            this.serviceTypeSelect.appendChild(option1)
        }
    }

    insertFileName(){
        this.fileName.value = this.selectFile.files[0]['name'];
    }

    async exportCsv(){
        let facilityNum = this.facilityNumber.value;
        let selectFileType = this.selectFileType.value;

        await CustomAjax.send(
            'GET',
            '/admin/external_datas?facilityNum=' + facilityNum + '&selectFileType=' + selectFileType,
            {'X-CSRF-TOKEN':CSRF_TOKEN},
            [],
            'exportCsvDatas',
            this
        );
    }

    exportCsvDatas(response){
        if(response.length){
            location.href ='/admin/csv/external_user_id_associations?facilityNum=' + this.facilityNumber.value;
        }else{
            this.showDialogWindow();
        }  
    }

    showDialogWindow(){
        this.externalDialog.classList.remove('external_dialog_hidden');
    }

    DialogHide(){
        this.externalDialog.classList.add('external_dialog_hidden');
    }

    registUserInfo(){
        this.fileName.required = true;
        if(this.facilityNameDisplay.innerHTML == "&nbsp;" && this.facilityNumber.value){
            this.falseNumPopupArea.style.display ="block";
            return;
        }
        if(!document.getElementById('contents').reportValidity()){
            return; 
        }
        
        let serviceTypeCode = this.serviceTypeSelect.value;
        let facilityNum = this.facilityNumber.value;
        let facilityNameVal = this.facilityName.value;
        let selectFileTypeVal = this.selectFileType.value;
        let registPopupArea = this.registPopupArea;
        let alreadyRegistPopupArea = this.alreadyRegistPopupArea;
        let alreadyRegistRow = this.alreadyRegistRow
        let notDataPopupArea = this.notDataPopupArea;

        let file = this.selectFile.files[0];
        let reader = new FileReader();
        reader.readAsText(file, 'Shift_JIS');

        reader.onload = async function(event) {
            let facilityId = facilityNameVal;
            let result = event.target.result;

            let tmp = result.split("\n");
            if(tmp.length <= 1){
                notDataPopupArea.style.display ="block";
                return;
            }

            let csvArr = [];
            for (let i = 1; i < tmp.length; ++i) {
                let cells = tmp[i].split(",");
                if(cells.length != 1){
                    csvArr[i-1] = {
                        external_user_id : cells[3],
                        last_name : cells[4],
                        first_name : cells[5],
                        last_name_kana : cells[6],
                        first_name_kana : cells[7],
                        gender : cells[8],
                        birthday : cells[9],
                        start_date : cells[13],
                        end_date : cells[14],
                        row_num: i+1
                    };
                }
            }

            let res = await CustomAjax.post(
                'admin/csv_regist',
                {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                {
                    csvArr: csvArr,
                    facilityId: facilityId,
                    selectFileTypeVal: selectFileTypeVal,
                    facilityNum: facilityNum,
                    serviceTypeCode: serviceTypeCode
                }
            );

            this.rowNum = document.getElementById('row_num');
            this.captureCount = document.getElementById('capture_count');
            this.capturedCount = document.getElementById('captured_count');
            this.newRecord = document.getElementById('new_record');

            this.rowNum.innerText = "";
            this.captureCount.innerText = "";
            this.capturedCount.innerText = "";
            this.newRecord.innerText = "";

            if(res.row_num.length != 0){
                this.rowNum.innerText = res.row_num.join(",") + '行目は登録済です';
            }
            if(res.capture_count != 0){
                this.captureCount.innerText = res.capture_count + "件を紐付けました";
            }
            if(res.captured_count != 0){
                this.capturedCount.innerText = res.captured_count + "件が紐付け済でした";
            }
            if(res.new_record != 0){
                this.newRecord.innerText = res.new_record + "件の新規登録データがありました";
            }
            registPopupArea.style.display ="block";
        };
    }

    exportCsvDatas(response){
        if(response.length){
            location.href ='/admin/csv/external_user_id_associations?facilityNum=' + this.facilityNumber.value;
        }else{
            this.showDialogWindow();
        }  
    }

    showDialogWindow(){
        this.externalDialog.classList.remove('external_dialog_hidden');
    }

    DialogHide(){
        this.externalDialog.classList.add('external_dialog_hidden');
    }
}
document.addEventListener('DOMContentLoaded',async()=>{
    new ExternalCapture();
});
