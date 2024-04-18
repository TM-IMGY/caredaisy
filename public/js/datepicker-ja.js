/* Japanese initialisation for the jQuery UI date picker plugin. */
/* Written by Kentaro SATO (kentaro@ranvis.com). */
( function( factory ) {
	"use strict";

	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
} )( function( datepicker ) {
"use strict";

datepicker.regional.ja = {
	closeText: "閉じる",
	prevText: "前へ",
	nextText: "次へ",
	currentText: "今日",
	monthNames: [ "1月", "2月", "3月", "4月", "5月", "6月",
	"7月", "8月", "9月", "10月", "11月", "12月" ],
	monthNamesShort: [ "1月", "2月", "3月", "4月", "5月", "6月",
	"7月", "8月", "9月", "10月", "11月", "12月" ],
	dayNames: [ "日曜日", "月曜日", "火曜日", "水曜日", "木曜日", "金曜日", "土曜日" ],
	dayNamesShort: [ "日", "月", "火", "水", "木", "金", "土" ],
	dayNamesMin: [ "日", "月", "火", "水", "木", "金", "土" ],
	weekHeader: "週",
	dateFormat: "yy/mm/dd",
	firstDay: 0,
	isRTL: false,
	showMonthAfterYear: true,
	showOnlyMonths: false
};
datepicker.setDefaults( datepicker.regional.ja );

return datepicker.regional.ja;

} );

function convert_wareki(year){
	var tmp;
	if (year > 2019) {	        // 令和 
		tmp = year - 2018;
		tmp = '令和' + tmp + '年 (' + year + ')';
		return tmp;
	}else if(year == 2019) {	// 平成 令和
		tmp = year - 1988;
		tmp = '平成' + tmp + '年/令和元年 (' + year + ')';
		return tmp;
	}else if (year > 1989) {	// 平成
		tmp = year - 1988;
		tmp = '平成' + tmp + '年 (' + year + ')';
		return tmp;
	}else if (year == 1989) {	// 昭和 平成
		tmp = year - 1925;
		tmp = '昭和' + tmp + '年/平成元年 (' + year + ')';
		return tmp;
	}else if (year > 1926) {	// 昭和
		tmp = year - 1925;
		tmp = '昭和' + tmp + '年 (' + year + ')';
		return tmp;
	}else if (year == 1926) {	// 大正 昭和
		tmp = year - 1911;
		tmp = '大正' + tmp + '年/昭和元年 (' + year + ')';
		return tmp;
	}else if (year > 1912) {	// 大正
		tmp = year - 1911;
		tmp = '大正' + tmp + '年 (' + year + ')';
		return tmp;
	}else if (year == 1912) {	// 明治 大正
		tmp = year - 1867;
		tmp =  '明治' + tmp + '年/大正元年 (' + year + ')';
		return tmp;
	}else if (year > 1867) {	// 明治
		tmp = year - 1867;
		tmp = '明治' + tmp + '年 (' + year + ')';
		return tmp;
	}else{               		// 該当なし
		return '';
	}
}
