/**
 * 保険外サービス
 * @author
 */

import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

export default class UninsuredService{
    constructor(){
        this.elementID = 'tm_contents_uninsured_service';
        this.element = document.getElementById(this.elementID);

        this.selectedHRecord = null;
        this.selectedLRecord = null;
        this.notificationList = [];
        this.selectedHistoryEndMonth = null;

        this.historyTBody = document.getElementById('table_tbody_uninsured_histroy');
        this.uninsuredItemListTBody = document.getElementById('table_tbody_uninsured_items');

        // ルーティングリスト
        this.getUninsuredServiceHistory = 'facility_info/uninsured_service/get_history';
        this.getUninsuredItemHistories = 'facility_info/uninsured_service/get_uninsured_item_histories';
        this.saveUninsuredItem = 'facility_info/uninsured_service/save_uninsured_item';
        this.deleteServiceItem = 'facility_info/uninsured_service/delete_service_item';
        this.firstServiceRegister = 'facility_info/uninsured_service/first_service_register';
        this.newMonthService = 'facility_info/uninsured_service/new_month_service';
        this.saveSort = 'facility_info/uninsured_service/save_sort';

        this.active = false;
        this.serviceInformation = {};
        this.targetUninsuredItemHistoryId = null;
        this.targetUninsuredItemId = null;
        this.history = null;
        this.latestStartMonth = null;
        this.fixItem = ['朝食','昼食','夕食','おやつ'];
        this.resultData = null;
        this.uninsuredHistoryList = null;
        this.paramLastSync = {serviceId:null};
        this.serviceId = null;

        this.validationDisplayArea = document.getElementById("validateErrorsUninsuredCost");
        this.startMonthBox = document.getElementById('uninsured_start_month')
        this.endMonthBox = document.getElementById('uninsured_end_month')

        // ボタン類
        this.registerBtn = document.getElementById('uninsured_btn_register');
        this.updateBtn = document.getElementById('uninsured_btn_update');
        this.itemRegisterPopupCBtn = document.getElementById('add_uninsured_list_poppu_cancel');
        this.itemRegisterPopupOBtn = document.getElementById('add_uninsured_list_poppu_ok')
        this.plusBtn = document.getElementById('uninsured_table_plus')
        this.deleteBtn = document.getElementById('uninsured_btn_delete')
        this.cantDeleteBtn = document.getElementById('uninsured_btn_cant_delete')
        this.itemDeletePopupOBtn = document.getElementById('delete_uninsured_item_poppu_ok')
        this.itemDeletePopupCBtn = document.getElementById('delete_uninsured_item_poppu_cancel')
        this.itemCantDeletePopupCBtn = document.getElementById('cant_delete_uninsured_item_poppu_cancel')
        this.serviceAlertPopupCloseBtn = document.getElementById('alert_uninsured_item_poppu_cancel')

        // popup
        this.itemAddPopup = document.getElementById('overflow_add_uninsured_list')
        this.itemDeletePopup = document.getElementById('overflow_delete_uninsured_item')
        this.itemCantDeletePopup = document.getElementById('overflow_cant_delete_uninsured_item')
        this.serviceAlert = document.getElementById('overflow_alert_uninsured_item')
        if(this.registerBtn !== null){
            this.registerBtn.addEventListener('click',this.clickUninsuredRegister.bind(this));
        }

        if(this.updateBtn !== null){
            this.updateBtn.addEventListener('click',function(){
                this.clearValidateDisplay();
                this.itemAddPopup.style.display = 'block';
            }.bind(this));
        }

        this.itemRegisterPopupCBtn.addEventListener('click',function(){ 
            this.itemAddPopup.style.display = 'none';
            //変更フラグをリセット
            document.getElementById("changed_flg").value = false;
        }.bind(this));
        this.itemRegisterPopupOBtn.addEventListener('click',this.checkAddList.bind(this));

        this.plusBtn.addEventListener('click',this.plusBtnClick.bind(this));

        // 削除用ポップアップfunction
        this.deleteFunction = function(){
            this.clearValidateDisplay();
            this.itemDeletePopup.style.display = 'block';
        }
        // 非削除用ポップアップfunction
        this.cantDeleteFunction = function(){
            this.clearValidateDisplay();
            this.itemCantDeletePopup.style.display = 'block';
        }

        if(this.deleteBtn !== null){
            this.deleteBtn.addEventListener('click', this.deleteFunction.bind(this));
            this.cantDeleteBtn.addEventListener('click', this.cantDeleteFunction.bind(this));
        }

        this.itemDeletePopupOBtn.addEventListener('click',this.deleteItem.bind(this));
        this.itemDeletePopupCBtn.addEventListener('click',function(){ this.itemDeletePopup.style.display = 'none';}.bind(this));
        this.itemCantDeletePopupCBtn.addEventListener('click',function(){ this.itemCantDeletePopup.style.display = 'none';}.bind(this));

        // 同月サービス存在時ポップアップ内「閉じる」にイベント付与
        this.serviceAlertPopupCloseBtn.addEventListener('click',function(){ this.serviceAlert.style.display = 'none';}.bind(this))

        // リスト外をクリックしたら選択解除
        $(document).on('click',function(event) {
            if(!$(event.target).closest('.click-not-remove').length) {
                if(this.selectedLRecord){
                    this.selectedLRecord.classList.remove('uninsured_cost_select_record');
                }
                if(this.registerBtn !== null){
                    this.registerBtn.style.visibility = 'visible';
                }
                if(this.updateBtn !== null){
                    this.updateBtn.style.visibility = 'hidden';
                }
                if(this.deleteBtn !== null){
                    this.deleteBtn.style.display = 'none';
                }
                if(this.cantDeleteBtn !== null){
                    this.cantDeleteBtn.style.display = 'none';
                }
            }
        }.bind(this));

        // 品目並び替え設定
        $('#table_tbody_uninsured_items').sortable({
            axis: 'y',
            containment: '#table_tbody_uninsured_items',
            cursor: 'grabbing',
            items: '> tr',
            opacity: 0.7,
            tolerance: 'pointer',
            update: function() {
                // 並び替えた順番を保存する
                this.sortUpdate();
            }.bind(this)
        });
    }

    /**
     * @param {bool} status 表示のブーリアン値
     */
    async setActive(status){
        if(this.hasAllParam() && this.hasParamUpdateDifferenceToSetActive()){
            await this.activeUninsuredService();
        } else if(!this.hasAllParam()) {
        }
        this.cacheParamLastSync();
        this.active = status
    }

    /**
    * @param {Object} param
    */
    async setFacilityData(param){
        this.fId = 'facilityId' in param ? param.facilityId : this.fId;
        this.serviceId = 'serviceId' in param ? param.serviceId : this.serviceId;
        this.sTypeId = 'serviceTypeCodeId' in param ? param.serviceTypeCodeId : this.sTypeId;
        this.sTypeName = 'serviceTypeName' in param ? param.serviceTypeName : this.sTypeName;
        this.serviceInformation = param;

        if(this.active == true){
            if(this.hasAllParam() && this.hasParamUpdateDifference()){
                this.activeUninsuredService();
            } else if(!this.hasAllParam()){

            }
        }
    }

    async activeUninsuredService(){
        this.history = null;
        let json = JSON.stringify(this.serviceInformation);
        let getId = JSON.parse(json);

        // 履歴リストを初期化
        this.historyTBody.textContent = null;
        this.startMonthBox.value = null;
        this.startMonthBox.value = null;

        // アイテムリストの削除
        let table_tbody = document.getElementById('table_tbody_uninsured_items')
        while(table_tbody.firstChild){
            table_tbody.removeChild(table_tbody.firstChild)
        }

        let serviceId = {'service_id':getId.serviceId};

        let history = await CustomAjax.post(
            this.getUninsuredServiceHistory,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            serviceId
        );

        if(history.length == 0){
            return false;
        }
        this.history = history;
        this.uninsuredHistoryList = history['uninsured_list'];
        this.latestItemList = history['uninsured_item_list'];
        this.latestStartMonth = this.uninsuredHistoryList[0]['start_month'].slice(0,-3);
        this.targetUninsuredItemId  = this.uninsuredHistoryList[0]['id'];

        // 開始月・終了月履歴テーブル作成
        Object.keys(this.uninsuredHistoryList).forEach((key)=>{
            let uninsuredItemsId = this.uninsuredHistoryList[key]['id'];

            let record = document.createElement('tr');
            let startMonthCell = document.createElement('td');
            let endMonthCell = document.createElement('td');

            let startDate = new Date(this.uninsuredHistoryList[key]['start_month']);
            startDate = this.formatDate(startDate);
            startMonthCell.textContent = startDate;
            let endDate = this.uninsuredHistoryList[key]['end_month'];
            if(endDate){
                endDate = new Date(endDate);
                endDate = this.formatDate(endDate);
                endMonthCell.textContent = endDate;
            }
            record.appendChild(startMonthCell);
            record.appendChild(endMonthCell);

            // td要素にクラス付与
            Array.from(record.children).forEach((child) => {
                child.className = 'text-data-uninsured-histroy';
            });

            if(key == '0'){
                this.selectedHRecord = record;
                this.selectedHRecord.classList.add('uninsured_cost_select_record');
                this.startMonthBox.value = startDate;
                this.endMonthBox.value = endDate;
            }

            //レコードに選択イベントを付与
            record.addEventListener('click',(event)=>{
                if(this.selectedHRecord){
                    this.selectedHRecord.classList.remove('uninsured_cost_select_record')
                }
                this.selectedHRecord = record;
                this.selectedHRecord.classList.add('uninsured_cost_select_record');
                this.startMonthBox.value = startDate;
                this.endMonthBox.value = endDate;
                this.selectedHistoryEndMonth = endDate;
                if(endDate){
                    this.updateBtn.style.visibility = 'hidden';
                    this.deleteBtn.style.display = 'none';
                    this.cantDeleteBtn.style.display = 'none';
                }
            });

            // 履歴選択時
            record.addEventListener('click',async function(){
                // 権限チェックのために"service_id"を追加
                let postData = {'id':uninsuredItemsId,'service_id':this.serviceId}
                let itemList =  await CustomAjax.post(this.getUninsuredItemHistories,{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},postData);
                this.createItemTable(itemList);
            }.bind(this));

            this.historyTBody.appendChild(record);
        });
        /*------------------------------------------------*/

        // アイテムリスト
        this.createItemTable(this.history);
        if(this.registerBtn !== null){
            this.registerBtn.disabled = false;
            this.registerBtn.style.visibility = 'visible';
        }
        if(this.updateBtn !== null){
            this.updateBtn.style.visibility = 'hidden';
        }
        if(this.deleteBtn !== null){
            this.deleteBtn.style.display = 'none';
        }
        if(this.cantDeleteBtn !== null){
            this.cantDeleteBtn.style.display = 'none';
        }
    }

    /**
     * ポップアップ内のフォームを初期化
     */
    popupFormClear(){
        this.clearValidateDisplay();
        this.targetUninsuredItemHistoryId = null;
        document.getElementById('add_item_name').readOnly = false;
        document.getElementById('add_unit').disabled = false;
        document.querySelectorAll('.popup_checkbox').forEach((e) => { e.checked = false;});
        let popUpText = document.querySelectorAll('.popup-text');
        Array.prototype.slice.call(popUpText).forEach(data => {data.value = null});
        let popupSelect = document.querySelectorAll('.popup_select');
        Array.prototype.slice.call(popupSelect).forEach(data => {data.options[0].selected = true;});
        document.getElementById('delete_item_name').value = null;
        document.getElementById('add_unit_cost').disabled =false;
    }

    /**
     * 品目の削除
     */
    async deleteItem(){
        let id = this.targetUninsuredItemHistoryId;
        // 権限チェックのために"service_id"を追加
        let postData = {'id':id,'service_id':this.serviceId};
        let result = await CustomAjax.post(this.deleteServiceItem,{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},postData);

        if(result > 0){
            this.itemDeletePopup.style.display = 'none';
            this.setFacilityData(this.serviceInformation);
        }
    }

    /**
     * サービス内容登録・更新
     */
    async checkAddList(){
        this.clearValidateDisplay();
        let id;
        this.targetUninsuredItemHistoryId ? id=this.targetUninsuredItemHistoryId : id = null;

        let postData = {}
        postData['uninsured_item_id'] = this.targetUninsuredItemId;
        postData['item'] = document.getElementById('add_item_name').value;
        postData['unit_cost'] = document.getElementById('add_unit_cost').value;
        postData['unit'] = document.getElementById('add_unit').value;
        postData['set_one'] = document.getElementById('set_one_check').checked ? 1 : 0;
        postData['fixed_cost'] = document.getElementById('fixed_cost_check').checked ? 1 : 0;
        postData['variable_cost'] = document.getElementById('variable_cost_check').checked ? 1 : 0;
        postData['welfare_equipment'] = document.getElementById('welfare_equipment_check').checked ? 1 : 0;
        postData['meal'] = document.getElementById('meal_check').checked ? 1 : 0;
        postData['daily_necessary'] = document.getElementById('daily_necessary_check').checked ? 1 : 0;
        postData['hobby'] = document.getElementById('hobby_check').checked ? 1 : 0;
        postData['escort'] = document.getElementById('escort_check').checked ? 1 : 0;
        postData['billing_reflect_flg'] = document.getElementById('billing_reflect_flg').checked ? 1 : 0;

        if(id !== null){
            postData['id'] = id;
        }else{
            postData['sort'] = document.getElementById('table_tbody_uninsured_items').rows.length + 1;
        }

        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
        await CustomAjax.send(
            'POST',
            this.saveUninsuredItem,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            postData,
            'callRegister',
            this
        );

        if(this.resultData === null){
            return false;
        }else if(this.resultData > 0 || Object.keys(this.resultData).length){
            this.itemAddPopup.style.display = 'none';
        }

        var insertId = {'id':this.targetUninsuredItemId}
        var item =  await CustomAjax.post(this.getUninsuredItemHistories,{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},insertId);
        this.latestItemList = item['uninsured_item_list'];
        this.createItemTable(item);
        this.registerBtn.style.visibility = 'visible';
        this.updateBtn.style.visibility = 'hidden';
        this.deleteBtn.style.display = 'none';
        this.cantDeleteBtn.style.display = 'none';
    }

    /**
     *
     * 新規登録押下時処理
     * @returns
     */
    async clickUninsuredRegister(){
        this.registerBtn.disabled = true;
        let postData = {};

        let date = new Date();
        let thisMonth = this.formatDateToSave(date);

        // 履歴がひとつもない → 初回登録
        if(this.history == null){
            postData['service_id'] = this.serviceInformation.serviceId;
            postData['start_month'] = thisMonth

            this.saveService(postData,this.firstServiceRegister)
            return;
        }

        let closeUninsuredItemsId = this.uninsuredHistoryList[0]['id'];

        postData['close_uninsured_items_id'] = closeUninsuredItemsId;
        postData['service_id'] = this.serviceInformation.serviceId;
        postData['latest_item_list'] = this.latestItemList;
        postData['start_month'] = thisMonth;
        postData['end_month'] = thisMonth;

        this.saveService(postData,this.newMonthService);
    }

    async saveService(postData,url){
        await CustomAjax.send(
            'POST',
            url,
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            postData,
            'callNewService',
            this
        );
    }

    callNewService(json){
        if(json !== void 0){
            this.setFacilityData(this.serviceInformation);
        }
    }

    /**
     * アイテムリストテーブルを作成する
     * @param {object} item
     */
    async createItemTable(item){
        let itemLists = item['uninsured_item_list'];
        let uninsuredRequest = item['uninsured_request']
        let fixUnitIds = [];
        Object.keys(uninsuredRequest).forEach(key=>{
            fixUnitIds.push(uninsuredRequest[key]['uninsured_item_history_id']);
        })

        // アイテムリストの削除
        let table_tbody = document.getElementById('table_tbody_uninsured_items')
        while(table_tbody.firstChild){
            table_tbody.removeChild(table_tbody.firstChild)
        }

        Object.keys(itemLists).forEach((key)=>{
            let itemList = Object.assign({}, itemLists[key]);
            delete itemList.billing_reflect_flg;

            let record = document.createElement('tr');
            record.setAttribute('id', 'history_id_' + itemList['id']);
            delete itemList.sort;

            let idCell = document.createElement('input');
            idCell.type = 'hidden';
            idCell.setAttribute('id','uninsured_item_history_id');
            idCell.value = itemList['id'];

            delete itemList.id;

            let firstItemCell = document.createElement('td');
            firstItemCell.className = 'uninsured-first-item';
            if(itemList['item'].length >= 10){firstItemCell.classList.add('long_name_item') };
            firstItemCell.textContent = itemList['item'];
            delete itemList.item;

            let itemUnitCostCell = document.createElement('td');
            itemUnitCostCell.className = 'uninsured-item-unit_cost';
            itemUnitCostCell.textContent = Number(itemList['unit_cost']).toLocaleString();
            delete itemList.unit_cost;

            let itemUnitCell = document.createElement('td');
            itemUnitCell.className = 'uninsured-item-unit';
            itemUnitCell.textContent = this.unitCostCheck(itemList['unit']);
            delete itemList.unit;

            record.appendChild(idCell)
            record.appendChild(firstItemCell)
            record.appendChild(itemUnitCostCell)
            record.appendChild(itemUnitCell)

            Object.keys(itemList).forEach((data)=>{
                var td = document.createElement('td');
                if(itemList[data] === 1){
                    var data = td.textContent = "〇";
                }
                td.className = 'uninsured-item';
                record.appendChild(td);
            });

            let deleteBtnDisplay = "inline";
            let cantDeleteBtnDisplay = "none";

            record.addEventListener('click',(event)=>{
                this.popupFormClear();
                let target = event.currentTarget;
                this.targetUninsuredItemHistoryId = target.firstElementChild.value;

                //　ポップアップ内に値を入れる
                document.getElementById('add_item_name').value = itemLists[key]['item'];
                document.getElementById('add_unit_cost').value = itemLists[key]['unit_cost'];
                document.getElementById('add_unit').options[itemLists[key]['unit']].selected = true;
                itemLists[key]['set_one']==1?document.getElementById('set_one_check').checked = true:'';
                itemLists[key]['fixed_cost']==1?document.getElementById('fixed_cost_check').checked = true:'';
                itemLists[key]['variable_cost']==1?document.getElementById('variable_cost_check').checked = true:'';
                itemLists[key]['welfare_equipment']==1?document.getElementById('welfare_equipment_check').checked = true:'';
                itemLists[key]['meal']==1?document.getElementById('meal_check').checked = true:'';
                itemLists[key]['daily_necessary']==1?document.getElementById('daily_necessary_check').checked = true:'';
                itemLists[key]['hobby']==1?document.getElementById('hobby_check').checked = true:'';
                itemLists[key]['escort']==1?document.getElementById('escort_check').checked = true:'';
                itemLists[key]['billing_reflect_flg']==1?document.getElementById('billing_reflect_flg').checked = true:'';

                // 削除ポップアップにも品目を入れる
                document.getElementById('delete_item_name').textContent = itemLists[key]['item'];

                // システム側で設定する品目は、「品目」「単位」は変更できない
                if(this.fixItem.includes(itemLists[key]['item'])){
                    document.getElementById('add_item_name').readOnly = true;
                    document.getElementById('add_unit').disabled = true;
                }

                if(fixUnitIds.includes(itemLists[key]['id'])){
                    document.getElementById('add_unit').disabled = true;
                    deleteBtnDisplay = 'none';
                    cantDeleteBtnDisplay = 'inline';
                } else {
                    deleteBtnDisplay = 'inline';
                    cantDeleteBtnDisplay = 'none';
                }
            });

            //レコードに選択イベントを付与
            record.addEventListener('click',(event)=>{
                if(document.getElementById('uninsured_end_month').value == ''){
                    if(this.selectedLRecord){
                        this.selectedLRecord.classList.remove('uninsured_cost_select_record');
                    }
                    this.selectedLRecord = record;
                    this.selectedLRecord.classList.add('uninsured_cost_select_record');
                    this.registerBtn.style.visibility = 'hidden';
                    this.updateBtn.style.visibility = 'visible';

                    // Bind切り替えようとしたがうまくいかなかったので
                    // 2つの削除ボタンのhidden/visibleを切り替えることにした
                    this.deleteBtn.style.display = deleteBtnDisplay;
                    this.cantDeleteBtn.style.display = cantDeleteBtnDisplay;
                }
            });

            this.uninsuredItemListTBody.appendChild(record);
        })
    }

    unitCostCheck(data){
        let value;
        switch(data){
            case 1:
                value = '1回';
                break;

            case 2:
                value = '1日';
                break;

            case 3:
                value = '1セット';
                break;

            case 4:
                value = '1ヶ月';
                break;
        }
        return value;
    }

    async plusBtnClick(){
        if(this.selectedHistoryEndMonth){return;}
        this.popupFormClear();
        this.itemAddPopup.style.display = 'block';
    }

    formatDate(date){
        let format = 'yyyy/M';
        format = format.replace(/yyyy/g, date.getFullYear());
        format = format.replace(/M/g, (date.getMonth() + 1));
        return format;
    }

    formatDateToSave(date){
        let year = date.getFullYear();
        let month = (date.getMonth() + 1).toString().padStart(2, "0");
        let day = (date.getDate()).toString().padStart(2, "0");
        let formatDate = year + '-' + month + '-' + day;
        return formatDate
    }

    callRegister(json){
        this.resultData = null;
        if(json !== void 0){
            this.resultData = json
        }
    }

    validateDisplay(errorBody){
        let createRow = (function(key, value){
            let record = document.createElement('li');
            let validationDisplayArea = document.getElementById("validateErrorsUninsuredCost");
            record.textContent = value;
            validationDisplayArea.appendChild(record);
        });

        errorBody = JSON.parse(errorBody);
        if('start_month' in errorBody.errors){
            this.serviceAlert.style.display = 'block';
            this.registerBtn.disabled = false;
            return
        }
        let errorList = errorBody.errors;
        Object.keys(errorList).map(key =>
            createRow(key, errorList[key])
        );
    }

    clearValidateDisplay()
    {
        while(this.validationDisplayArea.lastChild){
            this.validationDisplayArea.removeChild(this.validationDisplayArea.lastChild);
        }
    }

    hasAllParam(){
        return ![this.serviceId,this.fId,this.sTypeId,this.sTypeName].includes(null);
    }

    hasParamUpdateDifference(){
        return this.paramLastSync.serviceId!==this.serviceId
        || this.paramLastSync.fId!==this.fId
        || this.paramLastSync.sTypeId!==this.sTypeId
        || this.paramLastSync.sTypeName!==this.sTypeName;
    }

    hasParamUpdateDifferenceToSetActive(){
        return this.paramLastSync.serviceId!==this.serviceId
    }

    cacheParamLastSync(){
        this.paramLastSync.serviceId = this.serviceId;
    }

    async sortUpdate()
    {
        let sortIndexes = $('#table_tbody_uninsured_items').sortable('toArray');
        let uninsuredItemHistoryIds = [];
        sortIndexes.forEach((sortIndex) => {
            uninsuredItemHistoryIds.push(document.getElementById(sortIndex).querySelector('input').value);
        });

        let data = {
            'uninsured_item_history_id_list': uninsuredItemHistoryIds,
            'service_id': this.serviceId
        };

        await CustomAjax.send(
            'POST',
            this.saveSort,
            {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
            data
        );
    }
}
