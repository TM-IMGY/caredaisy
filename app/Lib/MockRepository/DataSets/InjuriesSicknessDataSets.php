<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 傷病のデータセット。
 */
class InjuriesSicknessDataSets
{
    public const DATA = [
        [
            'facility_user_id' => 3,
            'id' => 1,
            'start_date' => '2022-08-01',
            'end_date' =>'2022-08-31',
            'details' => [
                [
                    'detail_id' => 1,
                    'group' => 1,
                    'name' => '脳梗塞後遺症 ２型糖尿病',
                    'relation' => [
                        [
                            'relation_id' => 1,
                            'selected_position' => 1,
                            'special_medical_code_id' => 146
                        ],
                        [
                            'relation_id' => 2,
                            'selected_position' => 2,
                            'special_medical_code_id' => 167
                        ]
                    ]
                ],
                [
                    'detail_id' => 2,
                    'group' => 2,
                    'name' => '脳梗塞後遺症　廃用症候群',
                    'relation' => [
                        [
                            'relation_id' => 3,
                            'selected_position' => 1,
                            'special_medical_code_id' => 156
                        ],
                        [
                            'relation_id' => 4,
                            'selected_position' => 2,
                            'special_medical_code_id' => 171
                        ],
                        [
                            'relation_id' => 5,
                            'selected_position' => 3,
                            'special_medical_code_id' => 160
                        ],
                        [
                            'relation_id' => 6,
                            'selected_position' => 4,
                            'special_medical_code_id' => 185
                        ]
                    ]
                ],
                [
                    'detail_id' => 3,
                    'group' => 3,
                    'name' => '脳梗塞後遺症　嚥下障害',
                    'relation' => [
                        [
                            'relation_id' => 7,
                            'selected_position' => 1,
                            'special_medical_code_id' => 163
                        ]
                    ]
                ],
                [
                    'detail_id' => 4,
                    'group' => 4,
                    'name' => '構音障害',
                    'relation' => [
                        [
                            'relation_id' => 8,
                            'selected_position' => 1,
                            'special_medical_code_id' => 170
                        ],
                        [
                            'relation_id' => 9,
                            'selected_position' => 2,
                            'special_medical_code_id' => 174
                        ]
                    ]
                ]
            ]
        ]
    ];
}
