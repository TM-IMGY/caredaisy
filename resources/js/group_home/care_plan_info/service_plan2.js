import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

export default class ServicePlan2{
    constructor(){
        this.REQUEST_HEADER = {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN};

        this.elementID = 'tm_contents_service_plan2';
        this.element = document.getElementById(this.elementID);
        this.elementTBody = document.getElementById('sp2_tbody');
        this.isActive = false;
        this.servicePlanID = null;
        this.servicePlanStatus = null;
        this.secondServicePlan = null;
        this.plan1Obj = null;
        this.sp2PopupMessageSuccess = document.getElementById('sp2_popup_message_success');
        this.sp2PopupMessageFalse = document.getElementById('sp2_popup_message_false');
        this.overflowSp2 = document.getElementById('overflow_sp2');
        this.overflowChangeDelivery = document.getElementById('overflow_sp2_change_delivery')

        if (document.getElementById('sp2_issued_btn') !== null){
            document.getElementById('sp2_issued_btn').addEventListener('click',this.clickSaveBtn.bind(this));
        }
        if (document.getElementById('sp2_decision_btn') !== null){
            document.getElementById('sp2_decision_btn').addEventListener('click',this.clickSaveBtn.bind(this));
        }
        if (document.getElementById('sp2_create_btn') !== null){
            document.getElementById('sp2_create_btn').addEventListener('click',this.clickSaveBtn.bind(this));
        }
        // 交付済編集時アラート適用時に"this.checkDelivery"に変更する
        if (document.getElementById('sp2_save_btn') !== null){
            document.getElementById('sp2_save_btn').addEventListener('click',this.clickSaveBtn.bind(this));
        }

        document.getElementById('sp2_output_btn').addEventListener('click',this.clickOutput.bind(this));

        document.getElementById('close_sp2_popup').addEventListener('click',function(){
            document.getElementById('overflow_sp2').style.display = 'none';
        })

        //todo 計画書2側でステータス変更する場合は要修正
        document.getElementById('change_delivery_updatabtn_sp2').addEventListener('click',this.clickSaveBtn.bind(this));
        document.getElementById('change_delivery_cancelbtn_sp2').addEventListener('click',function(){
            this.overflowChangeDelivery.style.display = 'none';
        }.bind(this));

        this.element.querySelectorAll('.header_lbl_independence').forEach(data =>{
            data.style.display = "flex";
        });

        document.getElementById('delete_row_confirm_yes').addEventListener('click',this.clickDeleteRowConfirmYesBtn.bind(this));
        document.getElementById('delete_row_confirm_no').addEventListener('click',this.clickDeleteRowConfirmNoBtn.bind(this));

        this.removeRowData = null; // 削除希望の行データ(モーダル表示の間だけ格納される)
    }

    /**
     * テーブルの長期を追加
     * @param element targetRow 長期を追加する行
     * @return Number 追加した援助内容の合計数
     */
    addTableLong(lPlanList,targetRow,needSortID){
        // 作成した援助内容の合計
        let supportTotal = 0;
        for (let lPlanIndex=0,lPlanLen=lPlanList.length; lPlanIndex<lPlanLen; lPlanIndex++) {
            let lPlan = lPlanList[lPlanIndex];

            // 長期セルをニーズ行に追加
            // 1行目と2行目以降でターゲットとなる行が異なる
            let row = null;
            if(lPlanIndex===0){
                row = targetRow;
            } else {
                row = document.createElement('tr');
                this.elementTBody.appendChild(row);
            }

            // 長期セルをニーズ行に追加
            let lPlanCellConfig = {class_list:['sp2_table_long'],clm_category:'long',clm_name:'goal',
                value:lPlan['goal'],sort:{need:needSortID,long:lPlan['sort']},id:lPlan['service_long_plan_id']};
            let lPlanPeriodCellConfig = {class_list:['sp2_table_long_period'],clm_category:'long',
                clm_name:{date_start:'task_start',date_end:'task_end'},type:'date',
                value:{date_start:lPlan['task_start'],date_end:lPlan['task_end']},sort:{need:needSortID,long:lPlan['sort']},id:lPlan['service_long_plan_id']};

            let lPlanCell = this.createCell(lPlanCellConfig);
            let lPlanPeriodCell = this.createCell(lPlanPeriodCellConfig);

            row.appendChild(lPlanCell);
            row.appendChild(lPlanPeriodCell);

            // 短期
            let supportCnt = this.addTableShort(lPlan['short_plan_list'],row,needSortID,lPlan['sort']);

            // 援助内容/サービス内容の数によってrowSpanが決まる
            lPlanCell.rowSpan = supportCnt;
            lPlanPeriodCell.rowSpan = supportCnt;

            supportTotal += supportCnt;

            // テキストエリアの高さを調整
            this.setTextareaHeight(lPlanCell, supportCnt);
        }
        return supportTotal;
    }

    /**
     * テーブルのニーズを追加
     * @return void
     */
    addTableNeed(needList){
        for (let needIndex=0,needLen=needList.length; needIndex<needLen; needIndex++) {
            let need = needList[needIndex];

            // 行を追加
            let needsRow = document.createElement('tr');
            this.elementTBody.appendChild(needsRow);
            // ニーズセルを追加
            let needsCellConfig = {class_list:['sp2_table_needs'],clm_category:'need',clm_name:'needs',
                value:need['needs'],sort:{need:need['sort']},id:need['service_plan_need_id']};
            let needsCell = this.createCell(needsCellConfig);
            needsRow.appendChild(needsCell);

            // 長期
            let supportCnt = this.addTableLong(need['long_plan_list'],needsRow,need['sort']);

            // 追加した援助内容/サービス内容の数によってrowSpanが決まる
            needsCell.rowSpan = supportCnt;

            // テキストエリアの高さを調整
            this.setTextareaHeight(needsCell, supportCnt);
        }
    }

    /**
     * テーブルの短期を追加
     * @param element targetRow 短期を追加する行
     * @return Number 追加した援助内容の合計数
     */
    addTableShort(sPlanList,targetRow,needSortID,longSortID){
        let supportTotal = 0;
        for (let sPlanIndex=0,sPlanLen=sPlanList.length; sPlanIndex<sPlanLen; sPlanIndex++) {
            let sPlan = sPlanList[sPlanIndex];

            // 短期セルをニーズ行に追加
            // 1行目と2行目以降でターゲットとなる行が異なる
            let row = null;
            if(sPlanIndex===0){
                row = targetRow;
            } else {
                row = document.createElement('tr');
                this.elementTBody.appendChild(row);
            }

            let sortID = sPlan['sort'];
            let sPlanCellConfig = {class_list:['sp2_table_short'],clm_category:'short',clm_name:'goal',
                value:sPlan['goal'],sort:{need:needSortID,long:longSortID,short:sortID},id:sPlan['service_short_plan_id']};
            let sPlanPeriodCellConfig = {class_list:['sp2_table_short_period'],clm_category:'short',
                clm_name:{date_start:'task_start',date_end:'task_end'},type:'date',
                value:{date_start:sPlan['task_start'],date_end:sPlan['task_end']},sort:{need:needSortID,long:longSortID,short:sortID},id:sPlan['service_short_plan_id']};

            let sPlanCell = this.createCell(sPlanCellConfig);
            let sPlanPeriodCell = this.createCell(sPlanPeriodCellConfig);

            row.appendChild(sPlanCell);
            row.appendChild(sPlanPeriodCell);

            // 援助内容
            let supportCnt = this.addTableSupport(sPlan['support_list'],row,needSortID,longSortID,sPlan['sort']);

            // 援助内容/サービス内容の数によってrowSpanが決まる
            sPlanCell.rowSpan = supportCnt;
            sPlanPeriodCell.rowSpan = supportCnt;

            supportTotal += supportCnt;

            // テキストエリアの高さを調整
            this.setTextareaHeight(sPlanCell, supportCnt);
        }
        return supportTotal;
    }

    /**
     * テーブルの援助内容を追加
     * @param element targetRow 援助内容を追加する行
     * @return Number 追加した援助内容の合計数
     */
    addTableSupport(supportList,targetRow,needSortID,longSortID,shortSortID){
        for (let supportIndex=0,supportLen=supportList.length; supportIndex<supportLen; supportIndex++) {
            let support = supportList[supportIndex];

            // 援助内容セルをニーズ行に追加(rowspanは固定)
            // 1行目と2行目以降でターゲットとなる行が異なる
            let row = null;
            if(supportIndex===0){
                row = targetRow;
            } else {
                row = document.createElement('tr');
                this.elementTBody.appendChild(row);
            }

            let sortID = support['sort'];
            let contentsCellConfig = {class_list:['sp2_table_contents'],clm_category:'support',clm_name:'service',
                value:support['service'],sort:{need:needSortID,long:longSortID,short:shortSortID,support:sortID},id:support['service_plan_support_id']};
            let staffCellConfig = {class_list:['sp2_table_staff'],clm_category:'support',clm_name:'staff',
                value:support['staff'],sort:{need:needSortID,long:longSortID,short:shortSortID,support:sortID},id:support['service_plan_support_id']};
            let frequencyCellConfig = {class_list:['sp2_table_frequency'],clm_category:'support',clm_name:'frequency',
                value:support['frequency'],sort:{need:needSortID,long:longSortID,short:shortSortID,support:sortID},id:support['service_plan_support_id']};
            let contentsPeriodCellConfig = {class_list:['sp2_table_contents_period'],clm_category:'support',
                clm_name:{date_start:'task_start',date_end:'task_end'},type:'date',
                value:{date_start:support['task_start'],date_end:support['task_end']},sort:{need:needSortID,long:longSortID,short:shortSortID,support:sortID},id:support['service_plan_support_id']};

            let contentsCell = this.createCell(contentsCellConfig);
            let staffCell = this.createCell(staffCellConfig);
            let frequencyCell = this.createCell(frequencyCellConfig);
            let contentsPeriodCell = this.createCell(contentsPeriodCellConfig);

            row.appendChild(contentsCell);
            row.appendChild(staffCell);
            row.appendChild(frequencyCell);
            row.appendChild(contentsPeriodCell);
        }
        return supportList.length;
    }

    /**
     * 期間の自動補完を行う
     * @param {Number} needIndex
     * @param {Number} longIndex
     * @param {Number} shortIndex
     * @param {String} clmName
     * @param {String} value
     * @returns {void}
     */
    autoInputPeriod(needIndex, longIndex, shortIndex, clmName, value){
        // 長期の自動入力
        if(longIndex > -1){
            this.getLong(needIndex, longIndex)['short_plan_list'].forEach(element => {
                element[clmName] = value;
            });
        }

        // 短期の自動入力
        if(shortIndex > -1) {
            this.getShort(needIndex, longIndex, shortIndex)['support_list'].forEach(element => {
                element[clmName] = value;
            });
        }

        this.deleteTableRow();
        this.createTableRow();
    }

    /**
     * テキストエリアの高さを設定する
     * @param {Element} cell 対象のテキストエリアを含むセル
     * @param {Number} rowCount テキストエリアを表示する行数
     */
    setTextareaHeight(cell, rowCount) {
        if (rowCount >= 2) {
            let textarea = cell.getElementsByTagName('textarea')[0];

            // 各スタイルの値を取得
            let textareaStyle = window.getComputedStyle(textarea);
            let height = textareaStyle.getPropertyValue('height').replace(/px/, '');
            let marginBottom = textareaStyle.getPropertyValue('margin-bottom').replace(/px/, '');
            let tableStyle = window.getComputedStyle(textarea.parentNode.parentNode);
            let padding = tableStyle.getPropertyValue('padding').replace(/px/, '');

            // 行数分の高さ+行数分の下マージン(最後行除く)+行間のパディング
            let allHeight = (Math.round(Number(height)) * rowCount)
                          + (Number(marginBottom) * (rowCount - 1))
                          + ((Number(padding) * 2) * (rowCount - 1));

            textarea.style.height = 'auto';
            textarea.style.height = `${allHeight}px`;
        }
    }

    /**
     * セルテキストエリアチェンジイベント
     * @param {event} event
     */
    async changeCellTextArea(event){
        let target = event.target;
        let value = target.value;
        let clmCategory = target.getAttribute('clm-category');
        let clmName = target.getAttribute('clm-name');
        let needIndex = target.getAttribute('need-sort')-1;
        let longIndex = target.getAttribute('long-sort')-1;
        let shortIndex = target.getAttribute('short-sort')-1;
        let supportIndex = target.getAttribute('support-sort')-1;

        if(clmCategory==='need'){
            this.getNeed(needIndex)[clmName] = value;
        } else if(clmCategory==='long'){
            this.getLong(needIndex,longIndex)[clmName] = value;
        } else if(clmCategory==='short'){
            this.getShort(needIndex,longIndex,shortIndex)[clmName] = value;
        } else if (clmCategory==='support'){
            this.getSupport(needIndex,longIndex,shortIndex,supportIndex)[clmName] = value;
        }

        //変更フラグをセット
        document.getElementById("changed_flg").value = true;
    }

    /**
     * セル日付入力欄チェンジイベント
     * @param {event} event
     */
    async changeDate(event){
        // 介護計画書2が持つデータを更新する
        this.changeCellTextArea(event);
        // this.confirmAutoInputPeriod();
    }

    /**
   *
   * @returns
   */
    async checkDelivery()
    {
        if (this.servicePlanStatus != 4) {
        this.clickSaveBtn()
        return;
        }
        this.overflowChangeDelivery.style.display = 'block';
    }

    setServicePlan1(obj)
    {
        this.plan1Obj = obj;
    }

    /**
     * プレビューボタン押されたので帳票出力へ
     * */
    async clickOutput()
    {
        let serviceCount = await this.plan1Obj.checkEffectiveService();
        if(serviceCount == 0) {
            const SERVICE_EMPTY_MSG = 'ケアプラン有効期間内で有効なサービスが<br>利用者へ登録されておりません。'
            // 計画書1のメソッドを使いまわしてるので場合によっては切り分けが必要
            this.plan1Obj.showPopup(SERVICE_EMPTY_MSG)
            return;
        }

        // 本来は対象プランが存在しているかとかのチェック
        console.log(this.servicePlanID);
        if(this.servicePlanID == null){ return;}
        let url = "/group_home/care_plan_info/service_plan2/pdf?plan_id=" + this.servicePlanID;
        window.open(url, '_blank')
        return;
    }

    async clickCellMinus(event) {
        let removeRow = event;
        this.removeRowData = removeRow;
        $('#overflow_sp2_delete_row').show();
        return;
    }
    async clickDeleteRowConfirmNoBtn(){
        this.removeRowData = null;
        $('#overflow_sp2_delete_row').hide();
        return;
    }

    async clickDeleteRowConfirmYesBtn() {
        $('#overflow_sp2_delete_row').hide();

        let removeRow = this.removeRowData;

        // 見た目の削除はこれでよい
        let targetCell = removeRow.target;

        // ニーズ
        if(targetCell.getAttribute('clm-category') === 'need')
        {
            let cnt = document.querySelectorAll('.sp2_table_cell_plus[clm-category^="need"]').length;
            await this.removeNeedList(
                this.getSecondServicePlanId(),
                Number(targetCell.getAttribute('need-sort'))-1 // sortは1から始まるがjsのインデックスは0から
            );
            if(cnt == 1){
                this.insertNeedList(
                    this.getSecondServicePlanId(),
                    Number(targetCell.getAttribute('need-sort'))-1 // sortは1から始まるがjsのインデックスは0から
                );
            }
        }
        // 長期
        else if(targetCell.getAttribute('clm-category') === 'long')
        {
            let cnt = document.querySelectorAll('.sp2_table_cell_plus[clm-category^="long"][need-sort^="'+ targetCell.getAttribute('need-sort') +'"]').length;
            console.log(cnt);
            this.removeLongList(
                Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0から
                Number(targetCell.getAttribute('long-sort'))-1
            );
            if(cnt == 2){
                this.insertLongList(
                    Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0から
                    Number(targetCell.getAttribute('long-sort'))-1
                );
            }

        }
        // 短期
        else if(targetCell.getAttribute('clm-category') === 'short'){
            let cnt = document.querySelectorAll('.sp2_table_cell_plus[clm-category^="short"][need-sort^="'+ targetCell.getAttribute('need-sort') +'"][long-sort^="'+ targetCell.getAttribute('long-sort') +'"]').length;
            console.log(cnt);
            this.removeShortList(
                Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0から
                Number(targetCell.getAttribute('long-sort'))-1,
                Number(targetCell.getAttribute('short-sort')-1)
            );
            if(cnt == 2){
                this.insertShortList(
                    Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0から
                    Number(targetCell.getAttribute('long-sort'))-1,
                    Number(targetCell.getAttribute('short-sort')-1)
                );
            }

        }
        else if(targetCell.getAttribute('clm-category') === 'support')
        {
            let cnt = document.querySelectorAll('[clm-name^="task_start"][clm-category^="support"][need-sort^="'+ targetCell.getAttribute('need-sort') +'"][long-sort^="'+ targetCell.getAttribute('long-sort') +'"][short-sort="'+ targetCell.getAttribute("short-sort") +'"]').length;
            if(cnt == 1){
                this.insertSupportList(
                    Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0からのため-1
                    Number(targetCell.getAttribute('long-sort'))-1,
                    Number(targetCell.getAttribute('short-sort')-1),
                    Number(targetCell.getAttribute('support-sort'))-1,
                );
            }
            this.removeSupportList(
                Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0からのため-1
                Number(targetCell.getAttribute('long-sort'))-1,
                Number(targetCell.getAttribute('short-sort')-1),
                Number(targetCell.getAttribute('support-sort'))-1,
            );
       }

        this.deleteTableRow();
        this.createTableRow();

        //変更フラグをセット
        document.getElementById("changed_flg").value = true;
        return;
    }

    /**
     * セルプラスボタンクリックイベント
     * @param {event} event
     */
    async clickCellPlus(event){
        let targetCell = event.target;
        // ニーズ
        if(targetCell.getAttribute('clm-category') === 'need')
        {
            this.insertNeedList(
                this.getSecondServicePlanId(),
                Number(targetCell.getAttribute('need-sort'))-1 // sortは1から始まるがjsのインデックスは0から
            );
        }
        // 長期
        else if(targetCell.getAttribute('clm-category') === 'long')
        {
            this.insertLongList(
                Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0から
                Number(targetCell.getAttribute('long-sort'))-1
            );
        }
        // 短期
        else if(targetCell.getAttribute('clm-category') === 'short'){
            this.insertShortList(
                Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0から
                Number(targetCell.getAttribute('long-sort'))-1,
                Number(targetCell.getAttribute('short-sort')-1)
            );
        }
        else if(targetCell.getAttribute('clm-category') === 'support')
        {
            this.insertSupportList(
                Number(targetCell.getAttribute('need-sort'))-1, // sortは1から始まるがjsのインデックスは0からのため-1
                Number(targetCell.getAttribute('long-sort'))-1,
                Number(targetCell.getAttribute('short-sort')-1),
                Number(targetCell.getAttribute('support-sort'))-1,
            );
        }

        this.deleteTableRow();
        this.createTableRow();
    }

    /**
     * 保存ボタンイベント
     */
    async clickSaveBtn(){
        // 介護計画書IDがない、または介護計画書2がない場合、エスケープ
        if(!(this.hasServicePlanID() && this.hasServicePlan2())){ return; }

        let updateResult = await this.requestUpdate();

        this.overflowChangeDelivery.style.display = 'none';

        if(updateResult == null || updateResult.errors){
            this.sp2PopupMessageSuccess.style.display = 'none';
            this.sp2PopupMessageFalse.style.display = 'block';
            this.overflowSp2.style.display = 'block';
            this.sp2PopupMessageFalse.textContent = "保存に失敗しました";

            if(updateResult.errors){
                for(const key in updateResult.errors) {
                    this.sp2PopupMessageFalse.textContent = updateResult.errors[key][0];
                    break;
                }
            }
            return;
        }else{
            this.sp2PopupMessageSuccess.style.display = 'block';
            this.sp2PopupMessageFalse.style.display = 'none';
            this.overflowSp2.style.display = 'block';
        }

        let getDataResult = await this.requestGetSecondServicePlanData();
        if(getDataResult===null){ return; }

        this.secondServicePlan = getDataResult;

        this.deleteTableRow();
        this.createTableRow();

        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
    }

    /**
     * 長期と短期の期間の自動入力をする
     * @returns {void}
     */
    confirmAutoInputPeriod(){
        // 仮実装 期間の自動入力機能
        // 長期の場合は短期、短期の場合はサービス内容の期間も合わせて修正するかの確認ポップアップを出現させる
        let target = event.target;
        let value = target.value;
        let clmCategory = target.getAttribute('clm-category');

        // ポップアップで承認された場合、長期の場合は短期、短期の場合はサービス内容の期間も合わせて修正する
        let clmName = target.getAttribute('clm-name');
        let needIndex = target.getAttribute('need-sort')-1;
        let longIndex = target.getAttribute('long-sort')-1;
        let shortIndex = target.getAttribute('short-sort')-1;
        this.autoInputPeriod(needIndex, longIndex, shortIndex, clmName, value);
    }

    /**
     * セルを作成する
     * @param {object} data
     *   class_list array
     *   clm_category 列カテゴリ名
     *   clm_name 列名
     *   type デフォルトはtextarea。dateとの選択
     *   sort key need,long,short,support
     *   value dateの時はdate_startとdate_endを渡す
     * @return {element}
     */
    createCell(data){
        let classList = data['class_list'];
        let clmCategory = data['clm_category'];
        let clmName = data['clm_name'];
        let sort = data['sort'];
        let type = data['type'] ? data['type'] : 'textarea';
        let value = data['value'];
        let clmId = data["id"] ? data["id"] : '';

        let cell = document.createElement('td');
        cell.className = 'sp2_table_td';

        let cellRow = document.createElement('div');
        cellRow.className = 'sp2_table_cell';
        if(type==='textarea'){
            let cellTextarea = document.createElement('textarea');
            cellTextarea.value = value;
            cellTextarea.className = `sp2_table_textarea ${classList.join(' ')}`;
            cellTextarea.setAttribute('clm-category',clmCategory);
            cellTextarea.setAttribute('clm-name',clmName);
            for (let key in sort) {
                cellTextarea.setAttribute(key+'-sort',sort[key]);
            }
            cellTextarea.addEventListener('change',this.changeCellTextArea.bind(this));
            cellRow.appendChild(cellTextarea);
        } else if(type==='date'){
            let date = document.createElement('div');
            let dateStart = document.createElement('input');
            dateStart.type = 'date';
            dateStart.className = `sp2_table_input_date ${classList.join(' ')}`;
            dateStart.value = value['date_start'];
            dateStart.addEventListener('change',this.changeDate.bind(this));
            let dateEnd = document.createElement('input');
            dateEnd.type = 'date';
            dateEnd.className = `sp2_table_input_date ${classList.join(' ')}`;
            dateEnd.value = value['date_end'];
            dateEnd.addEventListener('change',this.changeDate.bind(this));
            dateStart.setAttribute('clm-category',clmCategory);
            dateStart.setAttribute('clm-name',clmName['date_start']);
            dateStart.setAttribute('min','1900-01-01');
            dateStart.setAttribute('max','2100-01-01');
            dateEnd.setAttribute('clm-category',clmCategory);
            dateEnd.setAttribute('clm-name',clmName['date_end']);
            dateEnd.setAttribute('min','1900-01-01');
            dateEnd.setAttribute('max','2100-01-01');
            for (let key in sort) {
                dateStart.setAttribute(key+'-sort',sort[key]);
                dateEnd.setAttribute(key+'-sort',sort[key]);
            }

            date.appendChild(dateStart);
            date.appendChild(dateEnd);
            cellRow.appendChild(date);
        }

        let cellArrowUp = document.createElement('div');
        // cellArrowUp.textContent = '↑';
        cellArrowUp.className = 'sp2_table_arrow';
        cellRow.appendChild(cellArrowUp);

        let cellRow2 = document.createElement('div');
        cellRow2.className = 'sp2_table_cell_row2';

        let cellPlus = document.createElement('div');
        cellPlus.textContent = '+';
        cellPlus.className = 'sp2_table_cell_plus';
        cellPlus.setAttribute('clm-category', clmCategory);
        for (let key in sort) {
            cellPlus.setAttribute(key + '-sort', sort[key]);
        }
        cellPlus.addEventListener('click', this.clickCellPlus.bind(this));
        cellRow2.appendChild(cellPlus);

        let cellMinus = document.createElement('div');
        cellMinus.textContent = '‐';
        cellMinus.className = 'sp2_table_cell_minus';
        cellMinus.setAttribute('clm-category', clmCategory);
        cellMinus.setAttribute('data-clm_id', clmId);

        for (let key in sort) {
            cellMinus.setAttribute(key + '-sort', sort[key]);
        }
        cellMinus.addEventListener('click', this.clickCellMinus.bind(this));
        cellRow2.appendChild(cellMinus);

        // 追加削除ボタンをニーズ、長期、短期、サービス内容列以外は非表示にする
        // ボタン押下時の挙動の関係で要素自体は必要なので、あえて追加後に非表示にする
        if (clmName != 'needs' && clmName != 'goal' && clmName != 'service') {
            cellPlus.style.display = 'none';
            cellMinus.style.display = 'none';
        }

        let cellArrowDown = document.createElement('div');
        cellArrowDown.className = 'sp2_table_arrow';
        // cellArrowDown.textContent = '↓';
        cellRow2.appendChild(cellArrowDown);

        cell.appendChild(cellRow);
        cell.appendChild(cellRow2);

        //変更フラグをセット
        document.getElementById("changed_flg").value = true;
        return cell;
    }

    /**
     * テーブルの行を作成する
     */
    createTableRow(){
        this.addTableNeed(this.secondServicePlan['need_list']);
    }

    /**
     * テーブルの行を削除する
     */
    deleteTableRow(){
        this.elementTBody.textContent = null;
    }

    /**
     * @return {Object}
     */
    getLong(needIndex,longIndex){
        return this.getLongList(needIndex)[longIndex];
    }

    /**
     * 長期のリストを返す
     * @return {array}
     */
    getLongList(needIndex){
        return this.getNeed(needIndex)['long_plan_list'];
    }

    /**
     * @return {Object}
     */
    getNeed(needIndex){
        return this.getNeedList()[needIndex];
    }

    /**
     * ニーズのリストを返す
     * @return {array}
     */
    getNeedList(){
        return this.secondServicePlan['need_list'];
    }

    /**
     * 長期の新規データを返す
     * @return {array}
     */
    getNewLongData(){
        return {goal:null,sort:1,task_start:null,task_end:null,short_plan_list:[this.getNewShortData()]};
    }

    /**
     * ニーズの新規データを返す
     * @return {array}
     */
    getNewNeedData(parentID){
        return {needs:null,second_service_plan_id:parentID,sort:1,task_start:null,task_end:null,long_plan_list:[this.getNewLongData()]};
    }

    /**
     * 短期の新規データを返す
     * @return {array}
     */
    getNewShortData(){
        return {goal:null,sort:1,task_start:null,task_end:null,support_list:[this.getNewSupportData()]};
    }

    /**
     * 援助内容の新規データを返す
     * @return {array}
     */
    getNewSupportData(){
        return {frequency:null,service:null,sort: 1,staff:null,task_start:null,task_end:null};
    }

    /**
     * @return {Object}
     */
    getShort(needIndex,longIndex,shortIndex){
        return this.getShortList(needIndex,longIndex)[shortIndex];
    }

    /**
     * @return {array}
     */
    getShortList(needIndex,longIndex){
        return this.getLong(needIndex,longIndex)['short_plan_list'];
    }

    /**
     * @return {Object}
     */
    getSupport(needIndex,longIndex,shortIndex,supportIndex){
        return this.getSupportList(needIndex,longIndex,shortIndex)[supportIndex];
    }

    /**
     * @return {array}
     */
    getSupportList(needIndex,longIndex,shortIndex){
        return this.getShort(needIndex,longIndex,shortIndex)['support_list'];
    }

    /**
     * @return {Number}
     */
    getSecondServicePlanId(){
        return this.secondServicePlan['second_service_plan_id'];
    }

    /**
     * @return {boolean}
     */
    hasServicePlanID(){
        return this.servicePlanID !== null;
    }

    /**
     * @return {boolean}
     */
    hasServicePlan2(){
        return this.secondServicePlan !== null;
    }

    /**
     * 長期に新規挿入する
     * @param {Number} needIndex 0から
     * @param {Number} longIndex 0から
     * @return {void}
     */
    insertLongList(needIndex,longIndex)
    {
        this.getLongList(needIndex).splice(longIndex+1,0,this.getNewLongData());
        this.resetSortLong(this.getLongList(needIndex));
    }

    /**
     * ニーズに新規挿入する
     * @param {String} parentID
     * @param {Number} needIndex 0から
     * @return {void}
     */
    insertNeedList(parentID,needIndex)
    {
        this.getNeedList().splice(needIndex+1,0,this.getNewNeedData(parentID));
        this.resetSortNeed();
    }

    /**
     * 短期に新規挿入する
     * @param {Number} needIndex 0から
     * @param {Number} longIndex 0から
     * @param {Number} shortIndex 0から
     * @return {void}
     */
    insertShortList(needIndex,longIndex,shortIndex)
    {
        this.getShortList(needIndex,longIndex).splice(shortIndex+1,0,this.getNewShortData());
        this.resetSortShort(this.getShortList(needIndex,longIndex));
    }

    /**
     * 援助内容に新規挿入する
     * @param {Number} needIndex 0から
     * @param {Number} longIndex 0から
     * @param {Number} shortIndex 0から
     * @param {Number} supportIndex 0から
     * @return {void}
     */
    insertSupportList(needIndex,longIndex,shortIndex,supportIndex){
        this.getSupportList(needIndex,longIndex,shortIndex).splice(supportIndex+1,0,this.getNewSupportData());
        this.resetSortSupport(this.getSupportList(needIndex,longIndex,shortIndex));
    }

    /**
     * ニーズの要素を削除する
     * @param {String} parentID
     * @param {Number} needIndex 0から
     * @return {void}
     */
    removeNeedList(parentID,needIndex)
    {
        this.getNeedList().splice(needIndex,1);
        this.resetSortNeed();
    }

    /**
     * 長期の要素をする
     * @param {Number} needIndex 0から
     * @param {Number} longIndex 0から
     * @return {void}
     */
    removeLongList(needIndex,longIndex)
    {
        this.getLongList(needIndex).splice(longIndex,1);
        this.resetSortLong(this.getLongList(needIndex));
    }


    /**
     * 短期の要素を削除する
     * @param {Number} needIndex 0から
     * @param {Number} longIndex 0から
     * @param {Number} shortIndex 0から
     * @return {void}
     */
    removeShortList(needIndex,longIndex,shortIndex)
    {
        this.getShortList(needIndex,longIndex).splice(shortIndex,1);
        this.resetSortShort(this.getShortList(needIndex,longIndex));
    }

    /**
     * 援助内容の要素を削除する
     * @param {Number} needIndex 0から
     * @param {Number} longIndex 0から
     * @param {Number} shortIndex 0から
     * @param {Number} supportIndex 0から
     * @return {void}
     */
    removeSupportList(needIndex,longIndex,shortIndex,supportIndex){
        this.getSupportList(needIndex,longIndex,shortIndex).splice(supportIndex,1);
        this.resetSortSupport(this.getSupportList(needIndex,longIndex,shortIndex));
    }


    /**
     * 介護計画書2のデータをリクエストする
     * @return {Promise}
     */
    async requestGetSecondServicePlanData(){
        return await CustomAjax.post('/group_home/care_plan_info/service_plan2/get',this.REQUEST_HEADER,{service_plan_id:this.servicePlanID});
    }

    /**
     * 介護計画書のデータをリクエストする
     * @return {Promise}
     */
    async requestGetServicePlanData() {
        return await CustomAjax.post('/group_home/care_plan_info/service_plan/get',this.REQUEST_HEADER,{
            clm: ['plan_start_period','plan_end_period','start_date','end_date'],
            service_plan_id: this.servicePlanID
        });
    }

    /**
     * 介護計画書2の新規挿入をリクエストする
     * @return {Promise}
     */
    async requestInsert(){
        return await CustomAjax.post('/group_home/care_plan_info/service_plan2/insert',this.REQUEST_HEADER,{service_plan_id:this.servicePlanID});
    }

    /**
     * 介護計画書2の更新をリクエストする
     * @return {Promise}
     */
    async requestUpdate(){
        return await CustomAjax.post('/group_home/care_plan_info/service_plan2/update',this.REQUEST_HEADER,{
            service_plan_id:this.servicePlanID,
            care_plan_period_start: $('#sp2_care_plan_period_start').val(),
            care_plan_period_end: $('#sp2_care_plan_period_end').val(),
            service_plan2:this.secondServicePlan
        });
    }

    /**
     * 長期のソートをリセットする
     * @param {Array} longList
     * @return {void}
     */
    resetSortLong(longList){
        for (let longIndex=0,longLen=longList.length; longIndex<longLen; longIndex++) {
            longList[longIndex]['sort'] = longIndex+1;
            this.resetSortShort(longList[longIndex]['short_plan_list']);
        }
    }

    /**
     * ニーズのソートをリセットする
     * @return {void}
     */
    resetSortNeed(){
        let needList = this.getNeedList();
        for (let needIndex=0,needLen=needList.length; needIndex<needLen; needIndex++) {
            needList[needIndex]['sort'] = needIndex+1; // sortは1から始まる
            this.resetSortLong(needList[needIndex]['long_plan_list']);
        }
    }

    /**
     * 短期のソートをリセットする
     * @param {Array} shortList
     * @return {void}
     */
    resetSortShort(shortList){
        for (let shortIndex=0,shortLen=shortList.length; shortIndex<shortLen; shortIndex++) {
            shortList[shortIndex]['sort'] = shortIndex+1;
            this.resetSortSupport(shortList[shortIndex]['support_list']);
        }
    }

    /**
     * 援助内容のソートをリセットする
     * @param {Array} supportList
     * @return {void}
     */
    resetSortSupport(supportList){
        for (let supportIndex=0,supportLen=supportList.length; supportIndex<supportLen; supportIndex++) {
            supportList[supportIndex]['sort'] = supportIndex+1;
        }
    }

    /**
     * 表示状態をセットする
     * @param {Boolean} status 表示のブーリアン値
     * @returns {Promise}
     */
    async setActive(status) {
        this.isActive = status;

        if(!this.isActive){ return; }

        // 介護計画書IDがない場合、介護計画書2を操作することはできない
        if(!this.hasServicePlanID()){ return; }
        // 介護計画書IDがある場合、介護計画書の作成日と作成者を取得する
        let servicePlanData = await this.requestGetServicePlanData();
        if(!(servicePlanData!==null && servicePlanData.length===1)){ return; }
        this.setServicePlan(servicePlanData[0]);
        // 介護計画書IDがある場合、介護計画書2のデータを取得する
        let responseData = await this.requestGetSecondServicePlanData();

        if(responseData.errors){
            // 介護計画書2のデータを持っていない場合、新規作成する
            await this.requestInsert();
            this.secondServicePlan = await this.requestGetSecondServicePlanData();
        }else{
            this.secondServicePlan = responseData;
        }

        this.deleteTableRow();
        this.createTableRow();
        //変更フラグをリセット
        document.getElementById("changed_flg").value = false;
    }

    /**
     * @param {Object} user {facilityUserID: string, userName: string}
     */
    setFacilityUser(user){
        // 他の利用者選択時、前の利用者の計画書2のデータが
        // 残ったままなのでthis.servicePlanID = nullで削除する
        this.servicePlanID = null;
        this.setServiceID(null);
        this.setServicePlan(null);
        this.setSecondServicePlan(null);
        this.deleteTableRow();
    }

    /**
     * 計画書1で選択された履歴の利用者情報ヘッダの情報を表示する
     * @param {object}
     */
    setFacilityUserInformationHeader(param)
    {
        if(param['recognition_date']){
            let recognition_date = new Date(param['recognition_date']);
            this.element.querySelector('.facility_user_info_header_recognition_date').textContent = recognition_date.toLocaleDateString();
        }

        if(param['care_period_start'] && param['care_period_end']){
            let care_period_start = new Date(param['care_period_start'])
            let care_period_end = new Date(param['care_period_end'])
            this.element.querySelector('.facility_user_info_header_care_period').textContent = care_period_start.toLocaleDateString() + ' - ' + care_period_end.toLocaleDateString()
        }

        this.element.querySelector('.facility_user_info_header_care_level').textContent = param['care_level_name'];

        if(param['certification_status'] == 2){
            this.element.querySelector('.facility_user_info_header_certification_status').textContent = '認定済'
        }else{
            this.element.querySelector('.facility_user_info_header_certification_status').textContent = '申請中'
        }

        this.element.querySelector('.facility_user_info_header_independence_level').textContent = param['independenceLevelName'];
        this.element.querySelector('.facility_user_info_header_dementia_independence').textContent = param['dementiaIndependenceName'];
    }

    /**
     * 介護計画書2をセットする
     * @param {Object} data
     * @returns {void}
     */
    setSecondServicePlan(data){
        this.secondServicePlan = data;
    }

    /**
     * 介護計画書IDをセットする
     * @param {object} param
     */
    async setServiceID(param){
        if (param == null) {
            return;
        } else if (param.id == null) {
            this.servicePlanID = null;
            this.setServicePlan(null);
            this.setSecondServicePlan(null);
            this.deleteTableRow();
        }
        this.servicePlanID = param.id;
        this.servicePlanStatus = param.status;
    }

    /**
     * 介護計画書のメタ情報をセットする
     * @param {Object} key: plan_start_period, plan_end_period, start_date, end_date
     * @returns {Promise}
     */
    setServicePlan(data) {
        let elementCraeteDate = document.getElementById('sp2_create_date');
        let elementAuthor = document.getElementById('sp2_author');
        let elementCarePlanPeriodStart = document.getElementById('sp2_care_plan_period_start');
        let elementCarePlanPeriodEnd = document.getElementById('sp2_care_plan_period_end');

        elementCraeteDate.value = data===null ? data : data.plan_start_period;
        elementAuthor.value = data===null ? data : data.plan_end_period;
        elementCarePlanPeriodStart.value = data===null ? data : data.start_date;
        elementCarePlanPeriodEnd.value = data===null ? data : data.end_date;
    }
}
