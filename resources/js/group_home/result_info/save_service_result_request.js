/**
 * サービス実績の保存のリクエストクラス。
 */
export default class SaveServiceResultRequest{
  /**
   * @param {Number} facilityId 
   * @param {Number} facilityUserId 
   * @param {Number} year 
   * @param {Number} month 
   */
  constructor(facilityId, facilityUserId, year, month){
    this.data = {
      facility_id : facilityId,
      facility_user_id : facilityUserId,
      service_results : [],
      year : year,
      month : month
    };
  }

  /**
   * @returns {void}
   */
  addServiceResult(
    burdenLimit,
    dateDailyRate,
    dateDailyRateOneMonthAgo,
    dateDailyRateTwoMonthAgo,
    serviceCountDate,
    serviceItemCodeId,
    specialMedicalCodeId
  ) {
    this.data.service_results.push({
      burden_limit: burdenLimit,
      date_daily_rate: dateDailyRate,
      date_daily_rate_one_month_ago: dateDailyRateOneMonthAgo,
      date_daily_rate_two_month_ago: dateDailyRateTwoMonthAgo,
      service_count_date: serviceCountDate,
      service_item_code_id: serviceItemCodeId,
      special_medical_code_id: specialMedicalCodeId
    });
  }
}
