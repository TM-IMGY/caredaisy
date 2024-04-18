import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js'

export default class MakeInvoice{
    async submit(facilityUserIds, year, month, facilityId){
        // 請求登録処理を実行する
        let res = await CustomAjax.post(
            '/group_home/service/invoice/make_invoice/facility_users',
            {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            {
                facility_user_ids: facilityUserIds,
                facility_id: facilityId,
                year: year,
                month: month
            }
        );

        // 請求登録処理の実行した結果を表示する
        alert(res.message);

        return res;
    }
}
