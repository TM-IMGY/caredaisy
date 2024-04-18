
import CustomAjax from '../../lib/custom_ajax.js'

export default class Billing{
    getRequestParameter(facilityUserIds, year, month){
        let parameter = '?';
        for (let i=0,len=facilityUserIds.length; i<len; i++) {
            parameter += 'facility_user_ids[]=' + facilityUserIds[i] + '&';
        }
        return parameter
          + 'year='
          + year
          + '&month='
          + month;
    }

    async submit(facilityUserIds, year, month){
        let response = await CustomAjax.get(
            '/group_home/service/national_health/download_csv/facility_users' + this.getRequestParameter(facilityUserIds,year,month)
        );

        let blob = await response.blob();
        let blobUrl = window.URL.createObjectURL(blob, {
            type: response.type
        });
        let fileName = response.headers.get('content-disposition').match(/filename="(.*)"/)[1];

        // ダウンロード用のankerタグを追加する
        let a = document.createElement('a');
        a.download = fileName;
        a.href = blobUrl;
        a.style.display = 'none';
        document.body.appendChild(a);

        a.click();

        window.URL.revokeObjectURL(blobUrl);
    }
}
