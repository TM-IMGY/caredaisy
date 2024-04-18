/**
 * 事業所
 * @author eikeda
 */

export default class Office{
  constructor(){
    this.elementID = 'tm_contents_office';

    // 保存ボタン
    document.getElementById('office_update').addEventListener('click',this.openOfficeSubmitDialog.bind(this));
    // はいボタン
    document.getElementById('updatabtn_office').addEventListener('click',this.submitOffice.bind(this));
    // いいえボタン
    document.getElementById('cancelbtn_office').addEventListener('click',this.closeOfficeSubmitDialog.bind(this));
  }

  setFacilityData(user){
    //corporationIdが取得できた場合データ表示
    if(user.facilityId){
      //事業所情報のfacilityidセット
      $("#getIdOffice").val(user.facilityId);

      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      $.ajax({
        url:'facility_info/office',
        type:'POST',
        data:{'facility_id':user.facilityId},
      })
      .done(function(data){
        $("#facility_number").val(data.facility.facility_number);
        $("#facility_name_kanji").val(data.facility.facility_name_kanji);
        $("#facility_name_kana").val(data.facility.facility_name_kana);
        $("#facility_name_abbr").val(data.facility.abbreviation);
        $("#facility_manager").val(data.facility.facility_manager);
        $("#insurer_no").val(data.facility.insurer_no);
        $("#postal_code").val(data.facility.postal_code);
        $("#location").val(data.facility.location);
        $("#phone_number").val(data.facility.phone_number);
        $("#fax_number").val(data.facility.fax_number);
        $("#remarks").val(data.facility.remarks);
        $("#job_title").val(data.facility.job_title);
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
      });
    }else{
      //クリア
      $("#facility_number").val("");
      $("#facility_name_kanji").val("");
      $("#facility_name_kana").val("");
      $("#facility_name_abbr").val("");
      $("#facility_manager").val("");
      $("#insurer_no").val("");
      $("#postal_code").val("");
      $("#location").val("");
      $("#phone_number").val("");
      $("#fax_number").val("");
      $("#remarks").val("");
    }
  }

  openOfficeSubmitDialog(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // ダイアログ表示
    $("#overflow_office").show();
  }

  closeOfficeSubmitDialog(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // ダイアログ非表示
    $("#overflow_office").hide();
  }

  submitOffice(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    let id = $("#getIdOffice").val();
    let num = $("#facility_number").val();
    let kanji = $("#facility_name_kanji").val();
    let kana = $("#facility_name_kana").val();
    let manager = $("#facility_manager").val();
    let abbr = $("#facility_name_abbr").val();
    let insurer = $("#insurer_no").val();
    let postal = $("#postal_code").val();
    let location = $("#location").val();
    let phone = $("#phone_number").val();
    let fax = $("#fax_number").val();
    let remarks = $("#remarks").val();
    let jobTitle = $("#job_title").val();

    // 保存
    if(num && kanji && kana && insurer && postal && location && phone){
      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      // 権限チェックのために"office_id"だけ'facility_id'に改修
      $.ajax({
        url:'facility_info/office/update',
        type:'POST',
        data:{
          'facility_id' : id,
          'office_number' : num,
          'office_name_kanji' : kanji,
          'office_name_kana' : kana,
          'office_name_abbr' : abbr,
          'office_manager' : manager,
          'office_insurer_no' : insurer,
          'office_postal_code' : postal,
          'office_location' : location,
          'office_phone_number' : phone,
          'office_fax_number' : fax,
          'office_remarks' : remarks,
          'office_job_title' : jobTitle
        },
      })
      .done(function(){
        $("#overflow_office").hide();
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
        alert("失敗");
      });
    }
  }
}
