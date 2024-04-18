import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'
import ServicePlan1 from "./service_plan1.js";

const selHeight = 37;
const startMinutes = 4 * 60; // 4時
const totalSelNum = 24 * 2; // 30分刻みのため総数は24の倍
const sceletonHeight = selHeight * totalSelNum;
const otherServiceId = 'otherService';
const mainServiceDay = 8;
const otherServiceDay = 9;
const borderWidth = 1;

export default class ServicePlan3{
    constructor(){
        this.REQUEST_HEADER = {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN, 'X-Requested-With':'XMLHttpRequest'};
        this.elementID = 'tm_contents_service_plan3';
        this.pageElement = document.getElementById(this.elementID);
        // this.isActive = false;
        // 問い合わせ用変数
        this.params = {
            weekly: [],
            mainAction: [],
            otherService: {}
        };
        this.services = [];// サービスプラン一覧
        this.servicePlanId = null;
        this.weeklyServiceMaster = [];
        this.weeklyServiceMasterList = [];
        this.mainWorkServiceMaster = [];
        this.otherServiceMaster = [];
        this.facilityUser = { facilityUserID:'', userName:''};
        this.facilityId = 0;
        this.plan1Obj = null;

        this.calendarInit();
    }

    setServicePlan1(obj){
        this.plan1Obj = obj;
    }

    // _/_/_/_/_/_/_/_/_/_/_/_/要素の書き換えや選択肢の用意_/_/_/_/_/_/_/_/_/_/_/_/
    /**
     * サービス履歴を選択肢にセット
     * @param services
     */
    setServiceHistory(services) {
        const target = this.pageElement.querySelector('.plan-select tbody');
        target.querySelectorAll('tr').forEach( e => target.removeChild(e)); // 選択肢の初期化

        this.calendarInit();

        this.services = services;

        this.services.forEach( async service => {
            const row = document.createElement('tr');
            const radioColumn = document.createElement('td');
            const radio = document.createElement('input');
            radio.value = service.id;
            radio.type = 'radio';
            radio.name = 'plan';
            radio.checked = false;
            radioColumn.appendChild(radio);
            const label = document.createElement('label');
            label.classList.add('radio-disp');
            label.addEventListener('click', this.planSelectedEvent);
            radioColumn.appendChild(label);
            row.appendChild(radioColumn);

             // 交付日
             const delivery = document.createElement('td');
             delivery.textContent = '';
             if(service.delivery_date) {
                 delivery.textContent = this.dateformat(service.delivery_date);
             }
             row.appendChild(delivery);

            // ケアプラン期間
            const duration = document.createElement('td');

            const start_date = this.dateformat(service.start_date);
            const end_date = this.dateformat(service.end_date);

            duration.textContent = start_date +'~'+ end_date;
            row.appendChild(duration);

            // 介護度
            const care_level = document.createElement('td');
            care_level.textContent = service.care_level_name;
            row.appendChild(care_level);

            // 作成日
            const createdAt = document.createElement('td');
            createdAt.textContent = this.dateformat(service.plan_start_period);
            row.appendChild(createdAt);

            // 状態
            const status = document.createElement('td');
            status.textContent = this.getStatusDispName(service.status);
            row.appendChild(status);

            target.appendChild(row);
        });
    }

    /**
     * 週間サービス作成・編集のサービス選択肢のセット
     */
    deployWeeklyServiceMaster() {
        const target = this.pageElement.querySelector('select[name="weekly_service_id"]');

        target.querySelectorAll('optgroup').forEach( el => target.removeChild(el)); // 初期化
        target.querySelectorAll('option').forEach( el => target.removeChild(el))

        this.weeklyServiceMaster.forEach( category => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = category.description;
            category.weekly_services.forEach( service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.innerHTML = service.description;
                optgroup.appendChild(option);
            });
            target.appendChild(optgroup)
        })
    }

    /**
     * 主な日常生活上の活動の選択肢のセット
     */
    deployMainWorkServiceMaster() {
        const tab = this.pageElement.querySelector('.mainwork .tab');
        const innerWrapper = this.pageElement.querySelector('.mainwork .tab_wrapper');
        this.mainWorkServiceMaster.forEach(category => {
            const li = document.createElement('li');
            li.innerHTML = category.description;
            li.dataset.id = category.id;
            li.addEventListener('click', this.mainWorkTabActiveAction);
            tab.appendChild(li);

            const tabBody = document.createElement('div');
            tabBody.classList.add('tab_inner');
            tabBody.dataset.categoryid = category.id;

            const buttonWrapper = document.createElement('div');
            buttonWrapper.classList.add('tab_inner_btn_wrap');
            category.weekly_services.forEach(service => {
                const button = document.createElement('button');
                button.innerHTML = service.description;
                button.addEventListener('click', this.appendsMainWorkServiceAction);
                buttonWrapper.appendChild(button);
            });
            tabBody.appendChild(buttonWrapper);
            innerWrapper.appendChild(tabBody);
        });

        // 良い書き方があれば書き換えるべき
        const event = {target:{dataset:{id:this.mainWorkServiceMaster[0].id}}};
        this.mainWorkTabActiveAction(event);
    }

    deployOtherServiceMaster() {
        const targetElement = this.pageElement.querySelector('.otherservice .tab_inner_btn_wrap');
        this.otherServiceMaster.forEach(master => {
            const button = document.createElement('button');
            button.innerHTML = master.description;
            button.addEventListener('click', this.appendsOtherServiceAction);
            targetElement.appendChild(button);
        });
    }

    dateformat(date) {
        let context = new Date(date).toLocaleDateString('ja-JP-u-ca-japanese', {
            era : 'long',
            year : '2-digit',
            month: '2-digit',
            day: '2-digit'
        })
        context = context.split('/');
        context = context[0]+'年'+context[1]+'月'+context[2]+'日';

        return context;
    }

    // _/_/_/_/_/_/_/_/_/_/_/_/カレンダー関連_/_/_/_/_/_/_/_/_/_/_/_/
    /**
     * カレンダーの初期化
     */
    calendarInit() {
        this.removeEvents();
        this.removeSchedule();
        this.removeMainWork();

        // sceletonの高さを揃える
        this.pageElement.querySelectorAll('.calendar-sceleton .day-of-week').forEach( e => e.style.height = totalSelNum * selHeight + 'px');
    }

    addEvents() {
        // 週間の新規作成・編集ウィンドウオープン用のイベントを付与
        this.pageElement.querySelectorAll('.calendar-sceleton .day-of-week').forEach(el => el.addEventListener('click', this.weeklyCreateWindowOpen));

        // 週間の削除イベントを付与
        this.pageElement.querySelector('.modal-wrapper.weekly button.delete').addEventListener('click', this.deleteSchedule);

        // 全キャンセルボタンにイベントを付与
        this.pageElement.querySelectorAll('button.cancel').forEach( el => el.addEventListener('click', this.allModalHideAction));

        // 主な日常生活上の活動にイベントを付与
        this.pageElement.querySelectorAll('#main-work tbody td').forEach( el => el.addEventListener('click', this.mainWorkEditWindowOpen));
        this.pageElement.querySelector('.mainwork .save').addEventListener('click', this.editMainWork);

        // 週単位以外
        this.pageElement.querySelector('.non-weekly_services_contents').addEventListener('click', this.otherServiceEditWindowOpen);
        this.pageElement.querySelector('.otherservice .save').addEventListener('click', this.editOtherService);

        // 保存
        this.pageElement.querySelector('#grand_save').addEventListener('click', this.updateAll);

        // 表２プレビュー
        this.pageElement.querySelector('#preview2').addEventListener('click', this.clickOutput);

        // 表３プレビュー
        this.pageElement.querySelector('#preview3').addEventListener('click', this.clickOutput3);

        // サービス内容選択時
        this.pageElement.querySelector('#radio_service_contents').addEventListener('click', this.clickServiceContents);

        // その他選択時
        this.pageElement.querySelector('#radio_service_other').addEventListener('click', this.clickServiceOther);
    }

    removeEvents() {
        // 週間の新規作成・編集ウィンドウオープン用のイベントを削除
        this.pageElement.querySelectorAll('.calendar-sceleton .day-of-week').forEach(el => el.removeEventListener('click', this.weeklyCreateWindowOpen));

        // 週間の削除イベントを削除
        this.pageElement.querySelector('.modal-wrapper.weekly button.delete').removeEventListener('click', this.deleteSchedule);

        // 全キャンセルボタンにイベントを削除
        this.pageElement.querySelectorAll('button.cancel').forEach( el => el.removeEventListener('click', this.allModalHideAction));

        // 主な日常生活上の活動にイベントを削除
        this.pageElement.querySelectorAll('#main-work tbody td').forEach( el => el.removeEventListener('click', this.mainWorkEditWindowOpen));
        this.pageElement.querySelector('.mainwork .save').removeEventListener('click', this.editMainWork);

        // 週単位以外
        this.pageElement.querySelector('.non-weekly_services_contents').removeEventListener('click', this.otherServiceEditWindowOpen);
        this.pageElement.querySelector('.otherservice .save').removeEventListener('click', this.editOtherService);

        // 保存
        this.pageElement.querySelector('#grand_save').removeEventListener('click', this.updateAll);

        // 表２プレビュー
        this.pageElement.querySelector('#preview2').removeEventListener('click', this.clickOutput);

        // 表３プレビュー
        this.pageElement.querySelector('#preview3').removeEventListener('click', this.clickOutput3);

    }

    // スケジュールを空欄にする
    removeSchedule() {
        document.querySelectorAll('.calendar-sceleton .day-of-week').forEach(dailySceleton => {
            dailySceleton.style.height = sceletonHeight + 'px';
            dailySceleton.querySelectorAll('div').forEach( el => dailySceleton.removeChild(el));
        });
    }

    // 主な日常生活上の活動を空欄にする
    removeMainWork() {
        document.querySelectorAll('#main-work tbody td').forEach(el => {
            el.innerHTML = '';
        });
    }

    /**
     * スケジュールを配置する
     */
    deploySchedules() {
        this.removeSchedule();
        this.params.weekly.forEach(schedule => {
            const targetScheleton = document.querySelector('[data-sceletonweek="'+ schedule.service_day +'"]');
            this.deploySchedule(targetScheleton, schedule)
        });
    }

    deploySchedule(element, event) {
        let eventElement = document.createElement('div');
        const position = this.calculateEventPosition(event);
        eventElement.textContent = this.getEventTitle(event);
        eventElement.style.top = position.top + "px";
        eventElement.style.bottom = position.bottom + "px";
        eventElement.dataset.id = event.id;
        eventElement.addEventListener('click', this.weeklyEditWindowOpen);

        element.appendChild(eventElement);
    }

    // 主な日常生活上の活動を配置する
    deployMainWorks() {
        this.removeMainWork();
        this.params.mainAction.forEach(action => {
            const targetElement = this.pageElement.querySelector('#main-work td[data-startminutes="'+ action.start_minutes +'"]');
            targetElement.innerHTML = action.content;
            targetElement.dataset.id = action.id;
        });
    }

    deployOtherService() {
        const targetElement = this.pageElement.querySelector('.non-weekly_services_contents');
        targetElement.textContent = this.params.otherService.content || '';
    }


    // _/_/_/_/_/_/_/_/_/_/_/_/モーダル系統_/_/_/_/_/_/_/_/_/_/_/_/
    /**
     * 週間計画作成時
     *
     */
    weeklyCreateWindowOpen = e => {
        const timezone = this.calculateTimeZone(e);

        const targetModal = this.pageElement.querySelector('.modal-wrapper.weekly');
        targetModal.querySelector('select[name="service_day"] option[value="'+ e.target.dataset.sceletonweek +'"]').selected = true;
        targetModal.querySelector('select[name="start_minutes"] option[value="'+ timezone.start +'"]').selected = true;
        targetModal.querySelector('select[name="end_minutes"] option[value="'+ timezone.end +'"]').selected = true;
        targetModal.querySelector('select[name="weekly_service_id"]').selectedIndex = -1;
        targetModal.querySelector('input[name="content"]').value = '';

        this.pageElement.querySelector('select[name="weekly_service_id"]').disabled = false;
        this.pageElement.querySelector('#service_other').disabled = true;

        const serviceRadios = targetModal.querySelectorAll('input[name="service_active"]');
        serviceRadios[0].checked = true;

        targetModal.style.display = 'block';
        targetModal.querySelector('button.delete').disabled = true;
        targetModal.querySelector('button.save').dataset.id = '';
        targetModal.querySelector('button.delete').dataset.id = '';

        targetModal.querySelector('.save').removeEventListener('click', this.editSchedule);
        targetModal.querySelector('.save').addEventListener('click', this.addSchedule)

        this.removeValidationErrors()
    }

    /**
     * 週間計画編集時
     * @param {*} param
     * @returns
     */
    weeklyEditWindowOpen = e => {
        const id = e.target.dataset.id;
        const event = this.findEvent(id);
        const targetModal = this.pageElement.querySelector('.modal-wrapper.weekly');
        targetModal.querySelector('select[name="service_day"] option[value="'+  event.service_day +'"]').selected = true;
        targetModal.querySelector('select[name="start_minutes"] option[value="'+  event.start_minutes +'"]').selected = true;
        targetModal.querySelector('select[name="end_minutes"] option[value="'+  event.end_minutes +'"]').selected = true;
        const serviceTarget = targetModal.querySelector('select[name="weekly_service_id"] option[value="'+  event.weekly_service_id +'"]');
        if (serviceTarget) { // マスターの変更によっては存在しないこともあるので、念の為
            serviceTarget.selected = true;
        }
        targetModal.querySelector('input[name="content"]').value = event.content;

        const serviceRadios = targetModal.querySelectorAll('input[name="service_active"]');
        for (let service of serviceRadios) {
            service.checked = false;
        }
        if (!!event.content) {
            serviceRadios[1].checked = true;
            this.pageElement.querySelector('select[name="weekly_service_id"]').disabled = true;
            this.pageElement.querySelector('#service_other').disabled = false;
        } else {
            serviceRadios[0].checked = true;
            this.pageElement.querySelector('select[name="weekly_service_id"]').disabled = false;
            this.pageElement.querySelector('#service_other').disabled = true;
        }

        targetModal.style.display = 'block';
        targetModal.querySelector('button.delete').disabled = false;
        targetModal.querySelector('button.save').dataset.id = id;
        targetModal.querySelector('button.delete').dataset.id = id;

        targetModal.querySelector('.save').removeEventListener('click', this.addSchedule);
        targetModal.querySelector('.save').addEventListener('click', this.editSchedule);

        this.removeValidationErrors();

        e.stopPropagation();
    }

    /**
     * 主な日常生活上の活動編集時
     * @param {*} e
     */
    mainWorkEditWindowOpen = e => {
        const targetModal = this.pageElement.querySelector('.modal-wrapper.mainwork');
        const event = this.findMainWork(e.target.dataset.id);

        const content = event?event.content:'';
        targetModal.querySelector('#main_activities_daily_contents').value = content;
        targetModal.querySelector('.save').dataset.id = e.target.dataset.id;
        targetModal.querySelector('.save').dataset.startminutes = e.target.dataset.startminutes;
        let start_hour_tmp = Math.floor(e.target.dataset.startminutes/60);
        if (start_hour_tmp >= 24) start_hour_tmp -= 24;
        const start_hour = start_hour_tmp;
        let end_hour_tmp = start_hour + 1;
        if (end_hour_tmp >= 24) end_hour_tmp -= 24;
        const end_hour = end_hour_tmp;
        const start_time = start_hour + ':00'
        const end_time = end_hour + ':00'
        targetModal.style.display = 'block';
        targetModal.querySelector('.mainwork .timezone span').textContent = start_time + ' ~ ' + end_time;
    }

    otherServiceEditWindowOpen = _e => {
        const targetModal = this.pageElement.querySelector('.modal-wrapper.otherservice');
        targetModal.querySelector('#other_than_weekly').value = this.params.otherService.content || '';
        targetModal.style.display = 'block';
    }

    removeValidationErrors() {
        const targetModal = this.pageElement.querySelector('.modal-wrapper.weekly');
        const className = targetModal.className;

        const regexp = new RegExp(/error_\S*/, 'g');
        const matchedClassList = className.match(regexp) || [];
        matchedClassList.forEach(classname => targetModal.classList.remove(classname));

        targetModal.querySelectorAll('.error').forEach(el => el.textContent = '');
    }


    // _/_/_/_/_/_/_/_/_/_/_/_/イベント_/_/_/_/_/_/_/_/_/_/_/_/

    /**
     * プランが選択された時のイベント
     */
     planSelectedEvent = async event => {
        // 選択肢の背景色を変更する
        this.pageElement.querySelectorAll('.plan-select tr').forEach(el => el.classList.remove('selected'));
        event.target.parentElement.parentElement.classList.add('selected');
        event.target.parentElement.querySelector('input').checked = true;
        this.servicePlanId = parseInt(event.target.parentElement.querySelector('input').value);

        // カレンダー初期化
        this.calendarInit();
        // スケジュールの取得、配置
        await this.retrieveScheduleByServicePlanId();
        this.deploySchedules();
        this.deployMainWorks();
        this.deployOtherService();

        this.addEvents();
    };

    /**
     * ページ中全モーダルウィンドウを消す
     * @param {*} _event
     */
    allModalHideAction = _event => {
        this.pageElement.querySelectorAll('.modal-wrapper').forEach( modal => modal.style.display = 'none' )
    }

    /**
     * スケジュールの新規作成
     * @param {*} e
     * @returns
     */
    addSchedule = e => {
        const targetModal = this.pageElement.querySelector('.modal-wrapper.weekly');
        const id = this.params.weekly.length + 1;
        const usingExisting =  targetModal.querySelector('input[name="service_active"]:checked').value === 'existing';

        const event = {
            id: 'cteated:' + id,
            service_day: targetModal.querySelector('select[name="service_day"]').value,
            start_minutes: targetModal.querySelector('select[name="start_minutes"]').value,
            end_minutes: targetModal.querySelector('select[name="end_minutes"]').value,
            weekly_service_id: targetModal.querySelector('select[name="weekly_service_id"]').value,
            content: usingExisting?'':targetModal.querySelector('input[name="content"]').value,
        };

        if(!this.validation()) {
            return false;
        }

        const targetScheleton = document.querySelector('[data-sceletonweek="'+ event.service_day +'"]');
        this.deploySchedule(targetScheleton, event);

        this.params.weekly.push(event); // 問い合わせ用の変数に格納する

        this.allModalHideAction(e);
    }

    editSchedule = e => {
        const id = e.target.dataset.id;
        const event = this.findEvent(id);

        if(!this.validation(id)) {
            return false;
        }

        const targetModal = this.pageElement.querySelector('.modal-wrapper.weekly');

        const usingExisting =  targetModal.querySelector('input[name="service_active"]:checked').value === 'existing';

        event.service_day = targetModal.querySelector('select[name="service_day"]').value;
        event.start_minutes = targetModal.querySelector('select[name="start_minutes"]').value;
        event.end_minutes = targetModal.querySelector('select[name="end_minutes"]').value;
        event.weekly_service_id =  targetModal.querySelector('select[name="weekly_service_id"]').value;
        event.content = usingExisting?'':targetModal.querySelector('input[name="content"]').value;

        // 全てのイベントを再描画する
        this.deploySchedules();

        this.allModalHideAction(e);
    }

    deleteSchedule = e => {
        const id = e.target.dataset.id;
        this.params.weekly = this.params.weekly.filter(event => event.id != id);

        // 全てのイベントを再描画する
        this.deploySchedules();

        this.allModalHideAction(e);
    }

    editMainWork = e => {
        const start_minutes = parseInt(e.target.dataset.startminutes);
        const end_minutes = start_minutes + 60;

        let event = this.findMainWork(e.target.dataset.id);
        if (!event) {
            event = {id: 'newMainWork-'+this.params.mainAction.length ,start_minutes:'',end_minutes:'',content:''}
            this.params.mainAction.push(event);
        }

        const value = this.pageElement.querySelector('#main_activities_daily_contents').value;

        event.start_minutes = start_minutes;
        event.end_minutes = end_minutes;
        event.content = value;

        this.deployMainWorks();
        this.allModalHideAction(e);
    }

    editOtherService = e => {
        const value = this.pageElement.querySelector('#other_than_weekly').value;
        this.params.otherService = {content:value};

        this.deployOtherService();
        this.allModalHideAction(e);
    }

    mainWorkTabActiveAction = e => {
        const id = e.target.dataset.id;

        this.allTabDisableAction(e);

        const target = this.pageElement.querySelector('.tab_inner[data-categoryid="'+ id +'"]');
        target.style.display = 'block';

        this.pageElement.querySelector('.mainwork .tab li[data-id="' + id + '"]').classList.add('active');

    }

    allTabDisableAction = _e => {
        this.pageElement.querySelectorAll('.mainwork .tab_inner').forEach(el => el.style.display = 'none');
        this.pageElement.querySelectorAll('.mainwork .tab li').forEach(el => el.classList.remove('active'));
    }

    appendsMainWorkServiceAction = e => {
        const target = this.pageElement.querySelector('#main_activities_daily_contents');
        target.value += e.target.innerHTML;
    }

    appendsOtherServiceAction = e => {
        const target = this.pageElement.querySelector('#other_than_weekly');
        target.value += e.target.innerHTML;
    }

    updateAll = async _e => {
        await this.update();

        // その他エリア初期化
        const target = this.pageElement.querySelector('.service-content');
        target.value = '';

        // カレンダー初期化
        this.calendarInit();

        // スケジュールの取得、配置
        // その他のサービスでマスターが追加されている可能性があるため、マスタも再取得
        await Promise.all([
            this.retrieveScheduleByServicePlanId(),
            this.retrieveWeeklyServiceByFacilityId({facility_id:this.facilityId})
        ]);

        this.deployWeeklyServiceMaster();
        this.deploySchedules();
        this.deployMainWorks();
        this.deployOtherService();

        this.addEvents();
    }

    clickOutput = async _e =>
    {
        let serviceCount = await this.checkEffectiveService();
        if(serviceCount == 0) {
            const SERVICE_EMPTY_MSG = 'ケアプラン有効期間内で有効なサービスが<br>利用者へ登録されておりません。'

            this.plan1Obj.showPopup(SERVICE_EMPTY_MSG)
            return;
        }

        // 本来は対象プランが存在しているかとかのチェック
        if(this.servicePlanId == null){ return; }
        let url = "/group_home/care_plan_info/service_plan2/pdf?plan_id=" + this.servicePlanId;
        window.open(url, '_blank')
        return;
    }

    clickOutput3 = async _e =>
    {
        const url = "/group_home/care_plan_info/service_plan3/pdf/" + this.servicePlanId;
        window.open(url, '_blank')
        return;
    }

    clickServiceContents = e => {
        this.pageElement.querySelector('select[name="weekly_service_id"]').disabled = false;
        this.pageElement.querySelector('#service_other').disabled = true;
    }

    clickServiceOther = e => {
        this.pageElement.querySelector('select[name="weekly_service_id"]').disabled = true;
        this.pageElement.querySelector('#service_other').disabled = false;
    }

    // _/_/_/_/_/_/_/_/_/_/_/_/計算系_/_/_/_/_/_/_/_/_/_/_/_/

    /**
     * イベントの表示位置を計算して返す
     * @param {start_minutes:number, end_minute:number, content:string} event
     * @returns {top:float, bottom:float}
     */
    calculateEventPosition(event) {
        const top = (event.start_minutes - startMinutes) / 60 * 2 * selHeight - borderWidth;
        const bottom = totalSelNum * selHeight - (event.end_minutes - startMinutes) / 60 * 2 * selHeight + borderWidth*2;

        return {top, bottom};
    }

    /**
     * クリック位置から時間帯を計算して返す
     * @param {*} e
     * @returns
     */
    calculateTimeZone(e) {
        const clickY = e.pageY;
        const rect = e.target.getBoundingClientRect();
        const positionY = rect.top + window.pageYOffset;
        const start = startMinutes + Math.floor(Math.floor(clickY - positionY) / selHeight) * 30; // カレンダーは30分刻み
        const end = start + 30; // 30分後をデフォルトにする

        return {start, end};
    }

    // _/_/_/_/_/_/_/_/_/_/_/_/バリデーション/_/_/_/_/_/_/_/_/_/_/_/
    validation(id = null) {
        const targetModal = this.pageElement.querySelector('.modal-wrapper.weekly');
        const service_day = targetModal.querySelector('select[name="service_day"]').value;
        const start_minutes = parseInt(targetModal.querySelector('select[name="start_minutes"]').value);
        const end_minutes = parseInt(targetModal.querySelector('select[name="end_minutes"]').value);
        const weekly_service_id =  targetModal.querySelector('select[name="weekly_service_id"]').value;
        const content = targetModal.querySelector('input[name="content"]').value;

        this.removeValidationErrors();

        let conflictService = this.params.weekly.find(event => {
            if(id && event.id == id){
                return false; // 自身は無視
            }

            // 時間帯の重複
            if(event.service_day != service_day) {
                return false;
            }

            if (event.start_minutes < start_minutes) {
                if (event.end_minutes <= start_minutes) {
                    return false;
                }
            } else {
                if (end_minutes <= event.start_minutes) {
                    return false;
                }
            }

            return true;
        });

        if(!!conflictService) {
            targetModal.classList.add('error_time');
            targetModal.querySelector('.error.time').textContent = '※同じ時間に他のサービスが登録されているため保存できません'
            return false;
        }

        if (end_minutes <= start_minutes) {
            targetModal.classList.add('error_time');
            targetModal.querySelector('.error.time').textContent = '※開始時間と終了時間の関係性に誤りがあるので確認してください'
            return false;
        }

        if (weekly_service_id === otherServiceId){
            if (!content) {
                targetModal.classList.add('error_content');
                targetModal.querySelector('.error.content').textContent = 'サービス内容を新規作成する場合、必須項目です'
                return false;
            }
        }

        return true;
    }


    // _/_/_/_/_/_/_/_/_/_/_/_/アクセサ/_/_/_/_/_/_/_/_/_/_/_/
    /**
     * 現在選択されているサービスプランを返答
     * @returns
     */
    getCurrentPlan() {
        return this.services.find( service => service.id === this.servicePlanId );
    }

    /**
     * idからイベントデータを引っ張り出す(主に編集時)
     * 新規作成したイベントの編集時IDはstringになるため注意
     */
    findEvent(id) {
        return this.params.weekly.find(event => event.id == id);
    }

    /**
     * idから日常生活の活動を引っ張り出す
     */
    findMainWork(id) {
        return this.params.mainAction.find(event => event.id == id);
    }

    getStatusDispName(statusId) {
        if (statusId === 1) {
            return '保存';
        }
        if (statusId === 2) {
            return '提出';
        }
        if (statusId === 3) {
            return '確定';
        }
        if (statusId === 4) {
            return '交付済';
        }
    }

    getEventTitle(event) {
        if(!!event.content) {
            return event.content;
        }
        const service = this.weeklyServiceMasterList.find(master => master.id == event.weekly_service_id);
        if (!service) {
            return '不明なサービス';
        }

        return service.description;
    }

    // _/_/_/_/_/_/_/_/_/_/_/_/APIリクエスト_/_/_/_/_/_/_/_/_/_/_/_/
    /**
     * 対応する計画を取得する
     */
     async retrieveScheduleByServicePlanId() {
        let data = await CustomAjax.get(
            'care_plan_info/schedule/' + this.servicePlanId,
            this.REQUEST_HEADER
        );

        data = await data.json();

        // パラメータにセットする
        this.params = {
            weekly:[],
            mainAction:[],
            otherService:{}
        };
        data.schedules.forEach(event => {
            // 他ファイルでAddEventListenerの実装が間違っているため同時に複数回呼ばれる可能性が排除できない
            // 初期化が意味をなさないので同一IDのものは排除する
            if (this.params.weekly.find(item => item.id === event.id)) {
                return;
            }
            if (this.params.mainAction.find(item => item.id === event.id)) {
                return;
            }

            if (event.service_day < mainServiceDay) { // 週間計画
                this.params.weekly.push(event);
            }
            if (event.service_day == mainServiceDay) { // 主な日常生活上の活動
                this.params.mainAction.push(event);
            }
            if (event.service_day == otherServiceDay) { // 週単位以外のサービス
                this.params.otherService = event;
            }
        });
    }

    /**
     * 週間計画のマスターを取得する
     */
    async retrieveWeeklyServiceByFacilityId(params) {
        const query_params = new URLSearchParams(params);
        let data = await CustomAjax.get(
            'care_plan_info/weekly_service_master?' + query_params,
            this.REQUEST_HEADER,
        );

        data = await data.json();
        this.weeklyServiceMaster = data.services;

        for(let category of data.services) {
            this.weeklyServiceMasterList.push(...category.weekly_services);
        }
    }

    /**
     * 主な日常生活のマスターを取得する
     */
    async retrieveMainWorkServiceByFacilityId(params) {
        const query_params = new URLSearchParams(params);
        let data = await CustomAjax.get(
            'care_plan_info/main_work_service_master?' + query_params,
            this.REQUEST_HEADER,
        );

        data = await data.json();
        this.mainWorkServiceMaster = data.services;
    }

    /**
     * 週単位以外のサービスのマスターを取得する
     */
    async retrieveOtherServiceByFacilityId(params) {
        const query_params = new URLSearchParams(params);
        let data = await CustomAjax.get(
            'care_plan_info/other_service_master?' + query_params,
            this.REQUEST_HEADER,
        );

        data = await data.json();
        this.otherServiceMaster = data.services;
    }


    /**
     * 保存
     */
     async update() {

        let data = await CustomAjax.post(
            'care_plan_info/schedule/' + this.servicePlanId,
            this.REQUEST_HEADER,
            {...this.params, facility_id:this.facilityId}
        );

        if(data.result === false) {
            // エラー処理
        }
    }

    /**
     * ケアプラン期間内に有効なサービスが存在するかチェックする
     * @returns
     */
    async checkEffectiveService()
    {
        let serviceCount = await CustomAjax.get('/group_home/care_plan_info/check_service?'
            + 'facility_user_id=' + this.facilityUser.facilityUserID
            + '&service_plan_id=' + this.servicePlanId,
            this.REQUEST_HEADER,
        );

        return await serviceCount.json()
    }

    // _/_/_/_/_/_/_/_/_/_/_/_/他コンポーネントからのデータの引き継ぎ_/_/_/_/_/_/_/_/_/_/_/_/
    /**
     * 計画書１から介護計画書IDをセットする
     * @param {object} param
     */
    async setServiceID(param) {
        if (param == null || param.id == null) {
            return;
        }

        this.servicePlanId = parseInt(param.id);

        const event = {}; // 辻褄合わせのため。良い書き方があれば変更するべき
        event.target = this.pageElement.querySelector('.plan-select input[type="radio"][value="'+ this.servicePlanId +'"]');
        event.target.checked = true;
        await this.planSelectedEvent(event);
    }

    /**
     * facility user tableより選択されたユーザを取得する
     * @param { facilityUserID:'', userName:''} user
     */
    setFacilityUser(user) {
        this.calendarInit();
        const target = this.pageElement.querySelector('.plan-select tbody');
        target.querySelectorAll('tr').forEach( e => target.removeChild(e)); // 選択肢の初期化
        this.pageElement.querySelector('.non-weekly_services_contents').textContent = '';

        this.facilityUser = user;
        this.pageElement.querySelector('.user_name_line').textContent = user.userName;

        this.allModalHideAction();
    }

    /**
     * 事務所IDのセット
     * 併せて週間マスター等を取得する
     * @param {} id
     */
    async setFacilityId(id) {
        this.facilityId = id;
        await Promise.all([
            this.retrieveWeeklyServiceByFacilityId({facility_id:id}),
            this.retrieveMainWorkServiceByFacilityId({facility_id:id}),
            this.retrieveOtherServiceByFacilityId({facility_id:id})
        ]);
        this.deployWeeklyServiceMaster();
        this.deployMainWorkServiceMaster();
        this.deployOtherServiceMaster();

        this.allModalHideAction();
    }
}
