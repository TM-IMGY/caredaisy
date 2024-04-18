// 特別診療費のサービス内容を持っておくだけのファイル
export default class specialMedicalExpensesTable {

    /**
     * 特別診療費項目を返す
     * addition_name 加算状況側で該当カラムが「あり」になっていたら自動フラグ対象
     * @returns
     */
    getSpecialMedicalExpenseItem()
    {
        return [
            {
                service_code_name : '01　感染対策指導管理',
                addition_name : "",
                name: 'infection_control_guidance',
                value: '0001'
            },
            {
                service_code_name : '02　特定施設管理',
                addition_name : "",
                name: 'specific_facility_management',
                value: '0002'
            },
            {
                service_code_name : '03　特定施設管理個室加算',
                addition_name : "",
                name: 'facility_management_private_room',
                value: '0003'
            },
            {
                service_code_name : '04　特定施設管理2人部屋加算',
                addition_name : "",
                name: 'facility_management_double_room',
                value: '0004'
            },
            {
                service_code_name : '05　初期入所診療管理',
                addition_name : "",
                name: 'medical_care_management',
                value: '0005'
            },
            {
                service_code_name : '06　重症皮膚潰瘍管理指導',
                addition_name : "severe_skin_ulcer",
                name: 'severe_skin_ulcer',
                value: '0006'
            },
            {
                service_code_name : '09　薬剤管理指導',
                addition_name : "drug_guidance",
                name: 'drug_guidance',
                value: '0009'
            },
            {
                service_code_name : '57　薬剤管理指導情報活用加算',
                addition_name : "drug_guidance",
                name: 'drug_guidance_information_utilization',
                value: '0057'
            },
            {
                service_code_name : '10　特別薬剤管理指導加算',
                addition_name : "drug_guidance",
                name: 'special_drug_guidance',
                value: '0010'
            },
            {
                service_code_name : '11　医学情報提供（Ⅰ）',
                addition_name : "",
                name: 'medical_information_provision_1',
                value: '0011'
            },
            {
                service_code_name : '12　医学情報提供（Ⅱ）',
                addition_name : "",
                name: 'medical_information_provision_2',
                value: '0012'
            },
            {
                service_code_name : '18　理学療法（Ⅰ）',
                addition_name : "physical_therapy",
                name: 'physical_therapy_1',
                value: '0018'
            },
            {
                service_code_name : '19　理学療法（Ⅱ）',
                addition_name : "other_rehabilitation_provision",
                name: 'physical_therapy_2',
                value: '0019'
            },
            {
                service_code_name : '20　理学療法リハビリ計画加算',
                addition_name : "physical_therapy",
                name: 'physical_therapy_rehabilitation_plan',
                value: '0020'
            },
            {
                service_code_name : '22　理学療法日常動作訓練指導加算',
                addition_name : "",
                name: 'physical_therapy_daily_movement',
                value: '0022'
            },
            {
                service_code_name : '48　理学療法リハビリ体制強化加算',
                addition_name : "physical_therapy",
                name: 'physical_therapy_rehabilitation_reinforcement',
                value: '0048'
            },
            {
                service_code_name : '58　理学療法（Ⅰ）情報活用加算',
                addition_name : "physical_therapy",
                name: 'physical_therapy_1_information_exercise',
                value: '0058'
            },
            {
                service_code_name : '59　理学療法（Ⅱ）情報活用加算',
                addition_name : "other_rehabilitation_provision",
                name: 'physical_therapy_2_information_exercise',
                value: '0059'
            },
            {
                service_code_name : '25　作業療法',
                addition_name : "occupational_therapy",
                name: 'occupational_therapy',
                value: '0025'
            },
            {
                service_code_name : '27　作業療法リハビリ計画加算',
                addition_name : "occupational_therapy",
                name: 'occupational_therapy_rehabilitation_plan',
                value: '0027'
            },
            {
                service_code_name : '29　作業療法日常動作訓練指導加算',
                addition_name : "",
                name: 'occupational_therapy_daily_movement',
                value: '0029'
            },
            {
                service_code_name : '49　作業療法リハビリ体制強化加算',
                addition_name : "occupational_therapy",
                name: 'occupational_therapy_rehabilitation_reinforcement',
                value: '0049'
            },
            {
                service_code_name : '60　作業療法情報活用加算',
                addition_name : "occupational_therapy",
                name: 'occupational_therapy_information_exercise',
                value: '0060'
            },
            {
                service_code_name : '31　摂食機能療法',
                addition_name : "other_rehabilitation_provision",
                name: 'eating_function_therapy',
                value: '0031'
            },
            {
                service_code_name : '32　精神科作業療法',
                addition_name : "psychiatric_occupational_therapy",
                name: 'psychiatric_occupational_therapy',
                value: '0032'
            },
            {
                service_code_name : '33　認知症入所精神療法',
                addition_name : "other_rehabilitation_provision",
                name: 'dementia_admission_psychotherapy',
                value: '0033'
            },
            {
                service_code_name : '34　褥瘡対策指導管理（Ⅰ）',
                addition_name : "",
                name: 'pressure_ulcer_control_guidance_1',
                value: '0034'
            },
            {
                service_code_name : '56　褥瘡対策指導管理（Ⅱ）',
                addition_name : "",
                name: 'pressure_ulcer_control_guidance_2',
                value: '0056'
            },
            {
                service_code_name : '35　重度療法管理',
                addition_name : "",
                name: 'severe_therapy_management',
                value: '0035'
            },
            {
                service_code_name : '39　言語聴覚療法',
                addition_name : "speech_hearing_therapy",
                name: 'speech_hearing_therapy',
                value: '0039'
            },
            {
                service_code_name : '50　言語聴覚療法リハビリ体制強化加算',
                addition_name : "speech_hearing_therapy",
                name: 'speech_hearing_therapy_rehabilitation',
                value: '0050'
            },
            {
                service_code_name : '61　言語聴覚療法情報活用加算',
                addition_name : "speech_hearing_therapy",
                name: 'speech_hearing_therapy_information_exercise',
                value: '0061'
            },
            {
                service_code_name : '42　理学療法（Ⅰ）（減算）',
                addition_name : "physical_therapy",
                name: 'physical_therapy_1_subtraction',
                value: '0042'
            },
            {
                service_code_name : '43　理学療法（Ⅱ）（減算）',
                addition_name : "other_rehabilitation_provision",
                name: 'physical_therapy_2_subtraction',
                value: '0043'
            },
            {
                service_code_name : '45　作業療法（減算）',
                addition_name : "occupational_therapy",
                name: 'occupational_therapy_subtraction',
                value: '0045'
            },
            {
                service_code_name : '47　言語聴覚療法（減算）',
                addition_name : "speech_hearing_therapy",
                name: 'speech_hearing_therapy_subtraction',
                value: '0047'
            },
            {
                service_code_name : '52　短期集中リハビリテーション',
                addition_name : "",
                name: 'short_concentration_rehabilitation',
                value: '0052'
            },
            {
                service_code_name : '54　集団コミュニケーション療法',
                addition_name : "group_communication_therapy",
                name: 'group_communication_therapy',
                value: '0054'
            },
            {
                service_code_name : '55　認知症短期集中リハビリテーション',
                addition_name : "dementia_short_rehabilitation",
                name: 'dementia_short_rehabilitation',
                value: '0055'
            },
        ]
    }
}