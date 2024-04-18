export default class JapaneseCalendar{
  // 和暦変換
  static toJacal(dateText)
  {
    if(dateText){
      let date = new Date(dateText);
      let reiwa = new Date("2019/05/01");
      let heisei = new Date("1989/01/08");
      let syouwa = new Date("1926/12/25");
      let taishou = new Date("1912/07/30");
      let meiji = new Date("1868/01/25");
      let year = date.getFullYear();
      let tmp;

      if (reiwa <= date) {          //令和
        tmp = year - 2018;
        tmp = '令和' + (tmp == 1 ? '元' : tmp);
        return tmp;
      }else if (heisei <= date) {   //平成
        tmp = year - 1988;
        tmp = '平成' + (tmp == 1 ? '元' : tmp);
        return tmp;
      }else if (syouwa <= date) {   //昭和
        tmp = year - 1925;
        tmp = '昭和' + (tmp == 1 ? '元' : tmp);
        return tmp;
      }else if (taishou <= date) {  //大正
        tmp = year - 1911;
        tmp = '大正' + (tmp == 1 ? '元' : tmp);
        return tmp;
      }else if (meiji <= date) {    //明治
        tmp = year - 1867;
        tmp = '明治' + (tmp == 1 ? '元' : tmp);
        return tmp;
      }else{                        //該当なし
        return "";
      }
    }else{
      return "";
    }
  }

  // 手入力時のフォーマット変換、和暦セット
  static inputChangeJaCal(event){
    let num =  event.target.value.replace(/[^0-9]/g, '');

    if(num.length == 8){
      var y = num.substr(0,4);
      var m = num.substr(4,2);
      var d = num.substr(6,2);
      event.target.value = y + "/" + m + "/" + d;

      let JaCal = JapaneseCalendar.toJacal(event.target.value);
      $("#" + event.target.id).prev().children('[id^="jaCal"]').text(JaCal);
    }
  }

  // 手入力時のフォーマット変換、和暦セット（年月）
  static inputChangeJaCalYearMonth(event){
    let num =  event.target.value.replace(/[^0-9]/g, '');
    if(num.length == 6){
      let y = num.slice(0, 4);
      let m = num.slice(4, 6);
      event.target.value = y + "/" + m;
      let JaCal = JapaneseCalendar.toJacal(event.target.value);
      $("#" + event.target.id).prev().children('[id^="jaCal"]').text(JaCal);
    }
  }
}
