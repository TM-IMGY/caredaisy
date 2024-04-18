
import CSRF_TOKEN from '../../lib/csrf_token.js'
import CustomAjax from '../../lib/custom_ajax.js';

/**
 * 加算状況のテーブル操作に責任をもつクラス
 */
export default class AdditionStatusTable {
  /**
   * 介護報酬履歴を取得して返す
   * @returns {Promise}
   */
  async getCareRewardHistory(facilityId, id, serviceTypeCodeId){
    try {
      let res = await CustomAjax.get(
        '/group_home/facility_info/addition_status/get/care_reward_history?'
          + 'facility_id=' + facilityId + '&'
          + 'id=' + id + '&'
          + 'service_type_code_id=' + serviceTypeCodeId,
        {'X-CSRF-TOKEN':CSRF_TOKEN}
      );
      let data = await res.json();
      return data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * 介護報酬履歴を全て取得して返す
   * @returns {Promise}
   */
  async getCareRewardHistories(facilityId, serviceTypeCodeId){
    try {
      let res = await CustomAjax.get(
        '/group_home/facility_info/addition_status/get/care_reward_histories?'
          + 'facility_id=' + facilityId + '&'
          + 'service_type_code_id=' + serviceTypeCodeId,
        {'X-CSRF-TOKEN':CSRF_TOKEN}
      );
      let data = await res.json();
      return data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * 最新の介護報酬履歴を取得して返す
   * @returns {Promise}
   */
   async getLatestCareRewardHistory(facilityId, serviceTypeCodeId){
    try {
      let res = await CustomAjax.get(
        '/group_home/facility_info/addition_status/get/latest_care_reward_history?'
          + 'facility_id=' + facilityId + '&'
          + 'service_type_code_id=' + serviceTypeCodeId,
        {'X-CSRF-TOKEN':CSRF_TOKEN}
      );
      let data = await res.json();
      return data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * マスタを返す
   * @return {Object}
   */
  getMaster(){
    return {
      // キーはデータベース上のID
      1 : this.getMaster32(),
      2 : this.getMaster37(),
      3 : this.getMaster33(),
      4 : this.getMaster36(),
      5 : this.getMaster35(),
      6 : this.getMaster55()
    };
  }

  /**
   * 種別32のマスタを返す。実装当初は加算状況の独自実装が多いため設置したが長いので要改修。
   * @returns {Object}
   */
  getMaster32(){
    return [
      {
        service_code_name : '認知症共同生活介護・夜減',
        values : [
          {
            label : 'なし      ',
            name: 'night_shift',
            value: 1
          },
          {
            label : 'あり',
            name: 'night_shift',
            value: 6,
          }
        ]
      },
      {
        service_code_name : '認知症共同生活介護・超',
        values : [
          {
            label : 'なし      ',
            name: 'over_capacity',
            value: 1
          },
          {
            label : 'あり',
            name: 'over_capacity',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症共同生活介護・欠',
        values : [
          {
            label : 'なし      ',
            name: 'vacancy',
            value : 1,
          },
          {
            label : 'あり',
            name: 'vacancy',
            value : 2
          }
        ]
      },
      {
        service_code_name : '認知症対応型身体拘束廃止未実施減算',
        values : [
          {
            label : 'なし      ',
            name: 'physical_restraint',
            value: 1
          },
          {
            label : 'あり',
            name: 'physical_restraint',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型３ユニット夜勤職員２人以上の場合の減算',
        values : [
          {
            label : 'なし      ',
            name: 'night_care_over_capacity',
            value: 1
          },
          {
            label : 'あり',
            name: 'night_care_over_capacity',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型夜間支援体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'night_care',
            value: 1
          },
          {
            label : 'あり',
            name: 'night_care',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型若年性認知症受入加算',
        values : [
          {
            label : 'なし      ',
            name: 'juvenile_dementia',
            value: 1
          },
          {
            label : 'あり',
            name: 'juvenile_dementia',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型入院時費用',
        values : [
          {
            label : 'なし      ',
            name: 'hospitalization_cost',
            value: 1
          },
          {
            label : 'あり',
            name: 'hospitalization_cost',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型看取り介護加算',
        values : [
          {
            label : 'なし      ',
            name: 'nursing_care',
            value: 1
          },
          {
            label : 'あり',
            name: 'nursing_care',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型初期加算',
        values : [
          {
            label : 'なし      ',
            name: 'initial',
            value: 1
          },
          {
            label : 'あり',
            name: 'initial',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型医療連携体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'medical_cooperation',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'medical_cooperation',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'medical_cooperation',
            value: 3,
          },
          {
            label : '加算Ⅲ  ',
            name: 'medical_cooperation',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '認知症対応型退居時相談援助加算',
        values : [
          {
            label : 'なし      ',
            name: 'consultation',
            value: 1
          },
          {
            label : 'あり',
            name: 'consultation',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型認知症専門ケア加算',
        values : [
          {
            label : 'なし      ',
            name: 'dementia_specialty',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'dementia_specialty',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'dementia_specialty',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '認知症対応型生活機能向上連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_living_function',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_living_function',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_living_function',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '認知症対応型栄養管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'nutrition_management',
            value: 1
          },
          {
            label : 'あり',
            name: 'nutrition_management',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型口腔衛生管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_hygiene_management',
            value: 1
          },
          {
            label : 'あり',
            name: 'oral_hygiene_management',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型口腔栄養スクリーニング加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_screening',
            value: 1
          },
          {
            label : 'あり',
            name: 'oral_screening',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応型科学的介護推進体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'scientific_nursing',
            value: 1
          },
          {
            label : 'あり',
            name: 'scientific_nursing',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '認知症対応サービス提供体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'strengthen_service_system',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'strengthen_service_system',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'strengthen_service_system',
            value: 3,
          },
          {
            label : '加算Ⅲ  ',
            name: 'strengthen_service_system',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '認知症対応型処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'treatment_improvement',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'treatment_improvement',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'treatment_improvement',
            value: 3,
          },
          {
            label : '加算Ⅲ  ',
            name: 'treatment_improvement',
            value: 4,
          },
        ]
      },
      {
        service_code_name : '認知症対応型特定処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_specific_treatment',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'improvement_of_specific_treatment',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'improvement_of_specific_treatment',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '認知症対応型ベースアップ等支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'baseup',
            value: 1
          },
          {
            label : 'あり',
            name: 'baseup',
            value: 2,
          }
        ]
      },
    ];
  }

  /**
   * 種別33のマスタを返す。実装当初は加算状況の独自実装が多いため設置したが長いので要改修。
   * @returns {Object}
   */
  getMaster33(){
    return [
      {
        service_code_name : '特定施設生活介護・欠',
        values : [
          {
            label : 'なし      ',
            name: 'vacancy',
            value : 1,
          },
          {
            label : 'あり',
            name: 'vacancy',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設身体拘束廃止未実施減算',
        values : [
          {
            label : 'なし      ',
            name: 'physical_restraint',
            value : 1,
          },
          {
            label : 'あり',
            name: 'physical_restraint',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設入居継続支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'support_continued_occupancy',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'support_continued_occupancy',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'support_continued_occupancy',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '特定施設生活機能向上連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_living_function',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_living_function',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_living_function',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '特定施設個別機能訓練加算Ⅰ',
        values : [
          {
            label : 'なし      ',
            name: 'individual_function_training_1',
            value : 1,
          },
          {
            label : 'あり',
            name: 'individual_function_training_1',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設個別機能訓練加算Ⅱ',
        values : [
          {
            label : 'なし      ',
            name: 'individual_function_training_2',
            value : 1,
          },
          {
            label : 'あり',
            name: 'individual_function_training_2',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設ＡＤＬ維持等加算',
        values : [
          {
            label : 'なし      ',
            name: 'adl_maintenance_etc',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'adl_maintenance_etc',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'adl_maintenance_etc',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '特定施設夜間看護体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'night_nursing_system',
            value : 1,
          },
          {
            label : 'あり',
            name: 'night_nursing_system',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設若年性認知症受入加算',
        values : [
          {
            label : 'なし      ',
            name: 'juvenile_dementia',
            value : 1,
          },
          {
            label : 'あり',
            name: 'juvenile_dementia',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設医療機関連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'medical_institution_cooperation',
            value : 1,
          },
          {
            label : 'あり',
            name: 'medical_institution_cooperation',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設口腔衛生管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_hygiene_management',
            value : 1,
          },
          {
            label : 'あり',
            name: 'oral_hygiene_management',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設口腔栄養スクリーニング加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_screening',
            value : 1,
          },
          {
            label : 'あり',
            name: 'oral_screening',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設科学的介護推進体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'scientific_nursing',
            value : 1,
          },
          {
            label : 'あり',
            name: 'scientific_nursing',
            value : 2
          }
        ]
      },
      // リリース1.6暫定対応
      // {
      //   service_code_name : '特定施設障害者等支援加算',
      //   values : [
      //     {
      //       label : 'なし      ',
      //       name: 'support_persons_disabilities',
      //       value : 1,
      //     },
      //     {
      //       label : 'あり',
      //       name: 'support_persons_disabilities',
      //       value : 2
      //     }
      //   ]
      // },
      {
        service_code_name : '特定施設退院退所時連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'discharge_cooperation',
            value : 1,
          },
          {
            label : 'あり',
            name: 'discharge_cooperation',
            value : 2
          }
        ]
      },
      {
        service_code_name : '特定施設看取り介護加算',
        values : [
          {
            label : 'なし      ',
            name: 'nursing_care',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'nursing_care',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'nursing_care',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '特定施設認知症専門ケア加算',
        values : [
          {
            label : 'なし      ',
            name: 'dementia_specialty',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'dementia_specialty',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'dementia_specialty',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '特定施設サービス提供体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'strengthen_service_system',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'strengthen_service_system',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'strengthen_service_system',
            value: 3,
          },
          {
            label : '加算Ⅲ    ',
            name: 'strengthen_service_system',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '特定施設処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'treatment_improvement',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'treatment_improvement',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'treatment_improvement',
            value: 3,
          },
          {
            label : '加算Ⅲ    ',
            name: 'treatment_improvement',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '特定施設特定処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_specific_treatment',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_specific_treatment',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_specific_treatment',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '特定施設ベースアップ等支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'baseup',
            value: 1
          },
          {
            label : 'あり',
            name: 'baseup',
            value: 2,
          }
        ]
      }
    ];
  }

  /**
   * 種別35のマスタを返す。実装当初は加算状況の独自実装が多いため設置したが長いので要改修。
   * @returns {Object}
   */
  getMaster35(){
    return [
      {
        service_code_name : '予防特定施設生活介護・欠',
        values : [
          {
            label : 'なし      ',
            name: 'vacancy',
            value : 1,
          },
          {
            label : 'あり',
            name: 'vacancy',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設身体拘束廃止未実施減算',
        values : [
          {
            label : 'なし      ',
            name: 'physical_restraint',
            value : 1,
          },
          {
            label : 'あり',
            name: 'physical_restraint',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設生活機能向上連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_living_function',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_living_function',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_living_function',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '予防特定施設個別機能訓練加算Ⅰ',
        values : [
          {
            label : 'なし      ',
            name: 'individual_function_training_1',
            value : 1,
          },
          {
            label : 'あり',
            name: 'individual_function_training_1',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設個別機能訓練加算Ⅱ',
        values : [
          {
            label : 'なし      ',
            name: 'individual_function_training_2',
            value : 1,
          },
          {
            label : 'あり',
            name: 'individual_function_training_2',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設若年性認知症受入加算',
        values : [
          {
            label : 'なし      ',
            name: 'juvenile_dementia',
            value : 1,
          },
          {
            label : 'あり',
            name: 'juvenile_dementia',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設医療機関連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'medical_institution_cooperation',
            value : 1,
          },
          {
            label : 'あり',
            name: 'medical_institution_cooperation',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設口腔衛生管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_hygiene_management',
            value : 1,
          },
          {
            label : 'あり',
            name: 'oral_hygiene_management',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設口腔栄養スクリーニング加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_screening',
            value : 1,
          },
          {
            label : 'あり',
            name: 'oral_screening',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設科学的介護推進体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'scientific_nursing',
            value : 1,
          },
          {
            label : 'あり',
            name: 'scientific_nursing',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予防特定施設認知症専門ケア加算',
        values : [
          {
            label : 'なし      ',
            name: 'dementia_specialty',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'dementia_specialty',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'dementia_specialty',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '予防特定施設サービス提供体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'strengthen_service_system',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'strengthen_service_system',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'strengthen_service_system',
            value: 3,
          },
          {
            label : '加算Ⅲ    ',
            name: 'strengthen_service_system',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '予防特定施設処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'treatment_improvement',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'treatment_improvement',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'treatment_improvement',
            value: 3,
          },
          {
            label : '加算Ⅲ    ',
            name: 'treatment_improvement',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '予防特定施設特定処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_specific_treatment',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_specific_treatment',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_specific_treatment',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '予防特定施設ベースアップ等支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'baseup',
            value: 1
          },
          {
            label : 'あり',
            name: 'baseup',
            value: 2,
          }
        ]
      },
    ];
  }

  /**
   * 種別36のマスタを返す。実装当初は加算状況の独自実装が多いため設置したが長いので要改修。
   * @returns {Object}
   */
  getMaster36(){
    return [
      {
        service_code_name : '地域特定施設生活介護・欠',
        values : [
          {
            label : 'なし      ',
            name: 'vacancy',
            value : 1,
          },
          {
            label : 'あり',
            name: 'vacancy',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設身体拘束廃止未実施減算',
        values : [
          {
            label : 'なし      ',
            name: 'physical_restraint',
            value : 1,
          },
          {
            label : 'あり',
            name: 'physical_restraint',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設入居継続支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'support_continued_occupancy',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'support_continued_occupancy',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'support_continued_occupancy',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '地域特定施設生活機能向上連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_living_function',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_living_function',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_living_function',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '地域特定施設個別機能訓練加算Ⅰ',
        values : [
          {
            label : 'なし      ',
            name: 'individual_function_training_1',
            value : 1,
          },
          {
            label : 'あり',
            name: 'individual_function_training_1',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設個別機能訓練加算Ⅱ',
        values : [
          {
            label : 'なし      ',
            name: 'individual_function_training_2',
            value : 1,
          },
          {
            label : 'あり',
            name: 'individual_function_training_2',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設ＡＤＬ維持等加算',
        values : [
          {
            label : 'なし      ',
            name: 'adl_maintenance_etc',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'adl_maintenance_etc',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'adl_maintenance_etc',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '地域特定施設夜間看護体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'night_nursing_system',
            value : 1,
          },
          {
            label : 'あり',
            name: 'night_nursing_system',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設若年性認知症受入加算',
        values : [
          {
            label : 'なし      ',
            name: 'juvenile_dementia',
            value : 1,
          },
          {
            label : 'あり',
            name: 'juvenile_dementia',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設医療機関連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'medical_institution_cooperation',
            value : 1,
          },
          {
            label : 'あり',
            name: 'medical_institution_cooperation',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設口腔衛生管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_hygiene_management',
            value : 1,
          },
          {
            label : 'あり',
            name: 'oral_hygiene_management',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設口腔栄養スクリーニング加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_screening',
            value : 1,
          },
          {
            label : 'あり',
            name: 'oral_screening',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設退院退所時連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'discharge_cooperation',
            value : 1,
          },
          {
            label : 'あり',
            name: 'discharge_cooperation',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設看取り介護加算',
        values : [
          {
            label : 'なし      ',
            name: 'nursing_care',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'nursing_care',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'nursing_care',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '地域特定施設認知症専門ケア加算',
        values : [
          {
            label : 'なし      ',
            name: 'dementia_specialty',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'dementia_specialty',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'dementia_specialty',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '地域特定施設科学的介護推進体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'scientific_nursing',
            value : 1,
          },
          {
            label : 'あり',
            name: 'scientific_nursing',
            value : 2
          }
        ]
      },
      {
        service_code_name : '地域特定施設サービス提供体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'strengthen_service_system',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'strengthen_service_system',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'strengthen_service_system',
            value: 3,
          },
          {
            label : '加算Ⅲ    ',
            name: 'strengthen_service_system',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '地域特定施設処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'treatment_improvement',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'treatment_improvement',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'treatment_improvement',
            value: 3,
          },
          {
            label : '加算Ⅲ    ',
            name: 'treatment_improvement',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '地域特定施設特定処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_specific_treatment',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_specific_treatment',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_specific_treatment',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '地域特定施設ベースアップ等支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'baseup',
            value: 1
          },
          {
            label : 'あり',
            name: 'baseup',
            value: 2,
          }
        ]
      }
    ];
  }

  /**
   * 種別37のマスタを返す。実装当初は加算状況の独自実装が多いため設置したが長いので要改修。
   * @return {Object}
   */
  getMaster37(){
    return [
      {
        service_code_name : '予認知症共同生活介護・夜減',
        values : [
          {
            label : 'なし      ',
            name: 'night_shift',
            value: 1
          },
          {
            label : 'あり',
            name: 'night_shift',
            value: 6,
          }
        ]
      },
      {
        service_code_name : '予認知症共同生活介護・超',
        values : [
          {
            label : 'なし      ',
            name: 'over_capacity',
            value: 1
          },
          {
            label : 'あり',
            name: 'over_capacity',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症共同生活介護・欠',
        values : [
          {
            label : 'なし      ',
            name: 'vacancy',
            value : 1,
          },
          {
            label : 'あり',
            name: 'vacancy',
            value : 2
          }
        ]
      },
      {
        service_code_name : '予認知症対応型身体拘束廃止未実施減算',
        values : [
          {
            label : 'なし      ',
            name: 'physical_restraint',
            value: 1
          },
          {
            label : 'あり',
            name: 'physical_restraint',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型３ユニット夜勤職員２人以上の場合の減算',
        values : [
          {
            label : 'なし      ',
            name: 'night_care_over_capacity',
            value: 1
          },
          {
            label : 'あり',
            name: 'night_care_over_capacity',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型夜間支援体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'night_care',
            value: 1
          },
          {
            label : 'あり',
            name: 'night_care',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型若年性認知症受入加算',
        values : [
          {
            label : 'なし      ',
            name: 'juvenile_dementia',
            value: 1
          },
          {
            label : 'あり',
            name: 'juvenile_dementia',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型入院時費用',
        values : [
          {
            label : 'なし      ',
            name: 'hospitalization_cost',
            value: 1
          },
          {
            label : 'あり',
            name: 'hospitalization_cost',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型初期加算',
        values : [
          {
            label : 'なし      ',
            name: 'initial',
            value: 1
          },
          {
            label : 'あり',
            name: 'initial',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型退居時相談援助加算',
        values : [
          {
            label : 'なし      ',
            name: 'consultation',
            value: 1
          },
          {
            label : 'あり',
            name: 'consultation',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型認知症専門ケア加算',
        values : [
          {
            label : 'なし      ',
            name: 'dementia_specialty',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'dementia_specialty',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'dementia_specialty',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型生活機能向上連携加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_living_function',
            value: 1
          },
          {
            label : '加算Ⅰ    ',
            name: 'improvement_of_living_function',
            value: 2,
          },
          {
            label : '加算Ⅱ    ',
            name: 'improvement_of_living_function',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型栄養管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'nutrition_management',
            value: 1
          },
          {
            label : 'あり',
            name: 'nutrition_management',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型口腔衛生管理体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_hygiene_management',
            value: 1
          },
          {
            label : 'あり',
            name: 'oral_hygiene_management',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型口腔栄養スクリーニング加算',
        values : [
          {
            label : 'なし      ',
            name: 'oral_screening',
            value: 1
          },
          {
            label : 'あり',
            name: 'oral_screening',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型科学的介護推進体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'scientific_nursing',
            value: 1
          },
          {
            label : 'あり',
            name: 'scientific_nursing',
            value: 2,
          }
        ]
      },
      {
        service_code_name : '予認知症対応サービス提供体制加算',
        values : [
          {
            label : 'なし      ',
            name: 'strengthen_service_system',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'strengthen_service_system',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'strengthen_service_system',
            value: 3,
          },
          {
            label : '加算Ⅲ  ',
            name: 'strengthen_service_system',
            value: 4,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'treatment_improvement',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'treatment_improvement',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'treatment_improvement',
            value: 3,
          },
          {
            label : '加算Ⅲ  ',
            name: 'treatment_improvement',
            value: 4,
          },
        ]
      },
      {
        service_code_name : '予認知症対応型特定処遇改善加算',
        values : [
          {
            label : 'なし      ',
            name: 'improvement_of_specific_treatment',
            value: 1
          },
          {
            label : '加算Ⅰ  ',
            name: 'improvement_of_specific_treatment',
            value: 2,
          },
          {
            label : '加算Ⅱ  ',
            name: 'improvement_of_specific_treatment',
            value: 3,
          }
        ]
      },
      {
        service_code_name : '予認知症対応型ベースアップ等支援加算',
        values : [
          {
            label : 'なし      ',
            name: 'baseup',
            value: 1
          },
          {
            label : 'あり',
            name: 'baseup',
            value: 2,
          }
        ]
      },
    ];
  }

  /**
   * 種類55のマスタを返す。
   * @return {Object}
   */
  getMaster55(){
    return [
        {
            service_code_name : '定員超過',
            values : [
                {
                    label : 'なし      ',
                    name: 'over_capacity',
                    value : 1,
                },
                {
                    label : 'あり',
                    name: 'over_capacity',
                    value : 2
                }
            ]
        },
        {
            service_code_name : '夜間勤務条件基準',
            values : [
                {
                    label : '基準      ',
                    name: 'night_shift',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'night_shift',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'night_shift',
                    value: 3,
                },
                {
                    label : '加算Ⅲ  ',
                    name: 'night_shift',
                    value: 4,
                },
                {
                    label : '加算Ⅳ  ',
                    name: 'night_shift',
                    value: 5,
                },
                {
                    label : '減算  ',
                    name: 'night_shift',
                    value: 6,
                }
            ]
        },
        {
            service_code_name : '職員欠員の減算',
            values : [
                {
                    label : 'なし      ',
                    name: 'vacancy',
                    value : 1,
                },
                {
                    label : 'あり',
                    name: 'vacancy',
                    value : 2
                }
            ]
        },
        {
            service_code_name : '正看比率',
            values : [
                {
                    label : '21%以上      ',
                    name: 'registered_nurse_ratio',
                    value : 1,
                },
                {
                    label : '20%未満',
                    name: 'registered_nurse_ratio',
                    value : 2
                }
            ]
        },
        {
            service_code_name : 'ユニットケア体制未整備減算',
            values : [
                {
                    label : 'なし      ',
                    name: 'unit_care_undevelopment',
                    value : 1,
                },
                {
                    label : 'あり',
                    name: 'unit_care_undevelopment',
                    value : 2
                }
            ]
        },
        {
            service_code_name : '医療院身体拘束廃止未実施減算',
            values : [
                {
                    label : 'なし      ',
                    name: 'physical_restraint',
                    value : 1,
                },
                {
                    label : 'あり',
                    name: 'physical_restraint',
                    value : 2
                }
            ]
        },
        {
            service_code_name : '医療院安全管理体制未実施減算',
            values : [
                {
                label : 'なし      ',
                name: 'safety_subtraction',
                value : 1,
                },
                {
                label : 'あり',
                name: 'safety_subtraction',
                value : 2
                }
            ]
        },
        {
            service_code_name : '医療院栄養管理基準減算',
            values : [
                {
                    label : 'なし      ',
                    name: 'nutritional_subtraction',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'nutritional_subtraction',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '医療院療養環境減算',
            values : [
                {
                    label : 'なし      ',
                    name: 'recuperation_subtraction',
                    value: 1
                },
                {
                    label : '廊下  ',
                    name: 'recuperation_subtraction',
                    value: 2,
                },
                {
                    label : '療養室  ',
                    name: 'recuperation_subtraction',
                    value: 3,
                },
            ]
        },
        {
            service_code_name : '若年性認知症入所者受け入れ加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'juvenile_dementia',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'juvenile_dementia',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '外泊時費用',
            values : [
                {
                    label : 'なし      ',
                    name: 'overnight_expenses_cost',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'overnight_expenses_cost',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '試行的退所サービス費',
            values : [
                {
                    label : 'なし      ',
                    name: 'trial_exit_service_fee',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'trial_exit_service_fee',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '他科受診時費用',
            values : [
                {
                    label : 'なし      ',
                    name: 'other_consultation_cost',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'other_consultation_cost',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '初期加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'initial',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'initial',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '再入所時栄養連携加算',
            values : [
                {
                    label : 'なし      ',
                    name: 're_entry_nutrition_cooperation',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 're_entry_nutrition_cooperation',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '退所前訪問指導加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'before_leaving_visit_guidance',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'before_leaving_visit_guidance',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '退所後訪問指導加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'after_leaving_visit_guidance',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'after_leaving_visit_guidance',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '退所時指導加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'leaving_guidance',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'leaving_guidance',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '退所時情報提供加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'leaving_information_provision',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'leaving_information_provision',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '退所前連携加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'after_leaving_alignment',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'after_leaving_alignment',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '訪問看護指示加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'home_visit_nursing_Instructions',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'home_visit_nursing_Instructions',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '栄養マネジメント強化加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'nutrition_management_strength',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'nutrition_management_strength',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '経口移行加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'oral_transfer',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'oral_transfer',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '経口維持加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'oral_maintenance',
                    value: 1
                },
                {
                    label : '加算Ⅰ    ',
                    name: 'oral_maintenance',
                    value: 2,
                },
                {
                    label : '加算Ⅱ    ',
                    name: 'oral_maintenance',
                    value: 3,
                }
            ]
        },
        {
            service_code_name : '口腔衛生管理加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'oral_hygiene',
                    value: 1
                },
                {
                    label : '加算Ⅰ    ',
                    name: 'oral_hygiene',
                    value: 2,
                },
                {
                    label : '加算Ⅱ    ',
                    name: 'oral_hygiene',
                    value: 3,
                }
            ]
        },
        {
            service_code_name : '療養食加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'recuperation_food',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'recuperation_food',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '在宅復帰支援機能加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'home_return_support',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'home_return_support',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '緊急時治療管理',
            values : [
                {
                    label : 'なし      ',
                    name: 'emergency_treatment',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'emergency_treatment',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '認知症専門ケア加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'dementia_specialty',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'dementia_specialty',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'dementia_specialty',
                    value: 3,
                }
            ]
        },
        {
            service_code_name : '認知症緊急対応加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'emergency_response',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'emergency_response',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '重度認知症疾患療養体制加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'severe_dementia_treatment',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'severe_dementia_treatment',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'severe_dementia_treatment',
                    value: 3,
                }
            ]
        },
        {
            service_code_name : '排せつ支援加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'excretion_support',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'excretion_support',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'excretion_support',
                    value: 3,
                },
                {
                    label : '加算Ⅲ  ',
                    name: 'excretion_support',
                    value: 4,
                }
            ]
        },
        {
            service_code_name : '自立支援促進加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'promotion_independence_support',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'promotion_independence_support',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '科学的介護推進体制加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'scientific_nursing',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'scientific_nursing',
                    value: 3,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'scientific_nursing',
                    value: 4,
                }
            ]
        },
        {
            service_code_name : '医療院長期療養生活移行加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'long_term_medical_treatment',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'long_term_medical_treatment',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : '安全対策体制',
            values : [
                {
                    label : 'なし      ',
                    name: 'safety_measures_system',
                    value: 1
                },
                {
                    label : 'あり',
                    name: 'safety_measures_system',
                    value: 2,
                }
            ]
        },
        {
            service_code_name : 'サービス提供体制強化加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'strengthen_service_system',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'strengthen_service_system',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'strengthen_service_system',
                    value: 3,
                }
            ]
        },
        {
            service_code_name : '介護職員処遇改善加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'treatment_improvement',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'treatment_improvement',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'treatment_improvement',
                    value: 3,
                }
            ]
        },
        {
            service_code_name : '介護職員等特定処遇改善加算',
            values : [
                {
                    label : 'なし      ',
                    name: 'improvement_of_specific_treatment',
                    value: 1
                },
                {
                    label : '加算Ⅰ  ',
                    name: 'improvement_of_specific_treatment',
                    value: 2,
                },
                {
                    label : '加算Ⅱ  ',
                    name: 'improvement_of_specific_treatment',
                    value: 3,
                }
            ]
        },
        {
          service_code_name : '医療院ベースアップ等支援加算',
          values : [
            {
              label : 'なし      ',
              name: 'baseup',
              value: 1
            },
            {
              label : 'あり',
              name: 'baseup',
              value: 2,
            }
          ]
        },
        {
          service_code_name : '重症皮膚潰瘍管理指導',
          values : [
              {
                  label : 'なし      ',
                  name: 'severe_skin_ulcer',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'severe_skin_ulcer',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '薬剤管理指導',
          values : [
              {
                  label : 'なし      ',
                  name: 'drug_guidance',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'drug_guidance',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '集団コミュニケーション療法',
          values : [
              {
                  label : 'なし      ',
                  name: 'group_communication_therapy',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'group_communication_therapy',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '理学療法',
          values : [
              {
                  label : 'なし      ',
                  name: 'physical_therapy',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'physical_therapy',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '作業療法',
          values : [
              {
                  label : 'なし      ',
                  name: 'occupational_therapy',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'occupational_therapy',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '言語聴覚療法',
          values : [
              {
                  label : 'なし      ',
                  name: 'speech_hearing_therapy',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'speech_hearing_therapy',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '精神科作業療法',
          values : [
              {
                  label : 'なし      ',
                  name: 'psychiatric_occupational_therapy',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'psychiatric_occupational_therapy',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : 'その他リハビリ提供体制',
          values : [
              {
                  label : 'なし      ',
                  name: 'other_rehabilitation_provision',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'other_rehabilitation_provision',
                  value: 2,
              }
          ]
        },
        {
          service_code_name : '認知症短期集中リハビリ加算',
          values : [
              {
                  label : 'なし      ',
                  name: 'dementia_short_rehabilitation',
                  value: 1
              },
              {
                  label : 'あり',
                  name: 'dementia_short_rehabilitation',
                  value: 2,
              }
          ]
        },
    ];
  }

  /**
   * マスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic(){
    return {
      1 : this.getMasterBasic32(),
      2 : this.getMasterBasic37(),
      3 : this.getMasterBasic33(),
      4 : this.getMasterBasic36(),
      5 : this.getMasterBasic35(),
      6 : this.getMasterBasic55()
    };
  }

  /**
   * 種別32のマスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic32(){
    return [
      {
        service_code_name : '認知症共同生活介護',
        values : [
          {
            label : 'Ⅰ型      ',
            name: 'section',
            value : 1,
          },
          {
            label : 'Ⅱ型      ',
            name: 'section',
            value : 2
          }
        ]
      },
    ];
  }

  /**
   * 種別33のマスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic33(){
    return [
      {
        service_code_name : 'サービス形態',
        values : [
          {
            label : '一般型      ',
            name: 'service_form',
            value : 1,
          },
          {
            invalid : true,
            label : '外部サービス利用型      ',
            name: 'service_form',
            value : 2
          }
        ]
      }
    ];
  }

  /**
   * 種別33のマスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic35(){
    return [
      {
        service_code_name : 'サービス形態',
        values : [
          {
            label : '一般型      ',
            name: 'service_form',
            value : 1,
          },
          {
            invalid : true,
            label : '外部サービス利用型      ',
            name: 'service_form',
            value : 2
          }
        ]
      }
    ];
  }

  /**
   * 種別36のマスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic36(){
    return [
      {
        service_code_name : 'サービス形態',
        values : [
          {
            label : '一般型      ',
            name: 'service_form',
            value : 1,
          }
        ]
      }
    ];
  }

  /**
   * 種別37のマスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic37(){
    return [
      {
        service_code_name : '予認知症共同生活介護',
        values : [
          {
            label : 'Ⅰ型      ',
            name: 'section',
            value : 1,
          },
          {
            label : 'Ⅱ型      ',
            name: 'section',
            value : 2
          }
        ]
      },
    ];
  }

  /**
   * 種別55のマスタ(基本)を返す
   * @return {Object}
   */
  getMasterBasic55(){
    return [
        {
            service_code_name : 'サービス形態',
            values : [
                {
                    label : '従来型      ',
                    name: 'service_form',
                    value : 3,
                },
                {
                    label : 'ユニット型      ',
                    name: 'service_form',
                    value : 4,
                }
            ]
        },
    ];
  }

  getExclusiveChoice(){
    return {
      6 : this.getExclusiveChoice55()
    };
  }

  getExclusiveChoice55(){
    return [
        {
            service_code_name : '施設区分・人員配置区分',
            values : [
                {
                    label : 'Ⅰ型（Ⅰ）      ',
                    name: 'section',
                    value: 3,
                    content_name: 'conventional unit'
                },
                {
                    label : 'Ⅰ型（Ⅱ）  ',
                    name: 'section',
                    value: 4,
                    content_name: 'conventional unit'
                },
                {
                    label : 'Ⅰ型（Ⅲ）  ',
                    name: 'section',
                    value: 5,
                    content_name: 'conventional'
                },
                {
                    label : 'Ⅱ型  ',
                    name: 'section',
                    value: 2,
                    content_name: 'unit'
                },
                {
                    label : 'Ⅱ型（Ⅰ）      ',
                    name: 'section',
                    value: 6,
                    content_name: 'conventional'
                },
                {
                    label : 'Ⅱ型（Ⅱ）  ',
                    name: 'section',
                    value: 7,
                    content_name: 'conventional'
                },
                {
                    label : 'Ⅱ型（Ⅲ）  ',
                    name: 'section',
                    value: 8,
                    content_name: 'conventional'
                },
                {
                    label : '特別Ⅰ  ',
                    name: 'section',
                    value: 9,
                    content_name: 'conventional unit'
                },
                {
                    label : '特別Ⅱ  ',
                    name: 'section',
                    value: 10,
                    content_name: 'conventional unit'
                }
            ]
        },
        // {
        //     service_code_name : '施設区分・人員配置区分',
        //     tr_class_name: 'unit',
        //     display: 'none',
        //     values : [
        //         {
        //             label : 'Ⅰ型（Ⅰ）      ',
        //             name: 'section',
        //             value: 3,
        //         },
        //         {
        //             label : 'Ⅰ型（Ⅱ）  ',
        //             name: 'section',
        //             value: 4,
        //         },
        //         {
        //             label : 'Ⅱ型  ',
        //             name: 'section',
        //             value: 2,
        //         },
        //         {
        //             label : '特別Ⅰ  ',
        //             name: 'section',
        //             value: 9,
        //         },
        //         {
        //             label : '特別Ⅱ  ',
        //             name: 'section',
        //             value: 10,
        //         }
        //     ]
        // }
    ]
  }

  /**
   * 割引のサービスコードを返す
   * @returns {Object}
   */
  getServiceCodeDiscount(){
    return[
      {
        service_code_name : '割引',
        values : [
          {
            label : 'なし      ',
            name: 'discount',
            value : 1,
          },
          {
            label : 'あり',
            name: 'discount',
            value : 2
          }
        ]
      },
    ];
  }

  /**
   * 新規挿入処理
   * @param {Object} data
   * @throws {error}
   * @returns {Promise}
   */
  async insert(data){
    return await CustomAjax.post(
        '/group_home/facility_info/addition_status/insert/care_reward_history',
        {'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN},
        data
      );
  }

  /**
   * 更新処理
   * @param {Object} data
   * @throws {error}
   * @returns {Promise}
   */
  async update(data){
	  return await CustomAjax.post(
        '/group_home/facility_info/addition_status/update/care_reward_history',
        {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
        data
      );
  }
}
