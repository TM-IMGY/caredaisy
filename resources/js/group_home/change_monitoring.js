export default class ChangeMonitoring {
    constructor(){                
        //inputタグにchangeイベントを追加して変更を監視
        let input = document.getElementsByTagName('input');
        let select = document.getElementsByTagName('select');
        let textarea = document.getElementsByTagName('textarea');
        let addEventListerFunc = function(tag){
            for(var i = 0; i < tag.length; ++i){
                //特定の項目は除外する
                let ret = excludeIds.find(id => id === tag[i].id )
                if (!ret) {
                    tag[i].addEventListener(
                        "change",
                        function(){
                            document.getElementById("changed_flg").value = true;
                        }
                    );
                }

            }
        }
        let excludeIds = [
            'year_month_pulldown',
            'facility_pulldown' ,
            'add_unit_cost' ,
            'add_item_name' ,
            'add_unit' ,
            'set_one_check' ,
            'fixed_cost_check' ,
            'daily_necessary_check' ,
            'variable_cost_check' ,
            'hobby_check' ,
            'welfare_equipment_check' ,
            'escort_check' ,
            'meal_check' ,
            'billing_reflect_flg' ,
            'un_table_s_item_pulldown' ,
            'item_sub_name' ,
            'item_sub_unit' ,
            'item_sub_date'
        ];

        addEventListerFunc(input);
        addEventListerFunc(select);
        addEventListerFunc(textarea);
    }
}