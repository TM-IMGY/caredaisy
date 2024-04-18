
import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js';

/**
 * 保険者のテーブル操作に責任をもつクラス
 */
export default class InsurerTable {
  /**
   * 保険者情報を取得して返す
   * @param insurerNo 保険者番号
   * @param year 年
   * @param month 月
   * @returns {Promise}
   */
  async get(insurerNo, year, month){
    // パラメーターを作成する
    let params = new URLSearchParams({
      insurer_no : insurerNo,
      year : year,
      month : month
    });

    try {
      let res = await CustomAjax.get(
        '/group_home/service/insurer/get?' + params.toString(),
        {'X-CSRF-TOKEN':CSRF_TOKEN}
      );
      let data = await res.json();
      return data;
    } catch (error) {
      throw error;
    }
  }
}
