/**
 * 施設
 * @author eikeda
 */

export default class Facility{
  constructor(){
    this.elementID = 'tm_contents_facility';

    // 保存ボタン
    document.getElementById('institution_update').addEventListener('click',this.openInstitutionSubmitDialog.bind(this));
    // はいボタン
    document.getElementById('updatabtn_institution').addEventListener('click',this.submitInstitution.bind(this));
    // いいえボタン
    document.getElementById('cancelbtn_institution').addEventListener('click',this.closeInstitutionSubmitDialog.bind(this));
  }

  setFacilityData(user){
    //corporationIdが取得できた場合データ表示
    if(user.institutionId){
      //施設情報のidセット
      $("#getIdInstitution").val(user.institutionId);

      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      // postData を改修
      $.ajax({
        url:'facility_info/institution',
        type:'POST',
        data:{'institution_id':user.institutionId},
      })
      .done(function(data){
        $("#institution_name").val(data.institution.name);
        $("#institution_abbr").val(data.institution.abbreviation);
        $("#institution_rep").val(data.institution.representative);
        $("#institution_phone").val(data.institution.phone_number);
        $("#institution_fax").val(data.institution.fax_number);
        $("#institution_postalcode").val(data.institution.postal_code);
        $("#institution_address").val(data.institution.location);
        $("#institution_remarks").val(data.institution.remarks);
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
        console.log("サービスAjax通信に失敗");
      });
    }else{
      //クリア
      $("#institution_name").val("");
      $("#institution_abbr").val("");
      $("#institution_rep").val("");
      $("#institution_phone").val("");
      $("#institution_fax").val("");
      $("#institution_postalcode").val("");
      $("#institution_address").val("");
      $("#institution_remarks").val("");
    }
  }

  openInstitutionSubmitDialog(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // ダイアログ表示
    $("#overflow_institution").show();
  }

  closeInstitutionSubmitDialog(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // ダイアログ非表示
    $("#overflow_institution").hide();
  }

  submitInstitution(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // 保存
    if($("#institution_name").val() != ""){
      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      $.ajax({
        url:'facility_info/institution/update',
        type:'POST',
        data:{
          'institution_id' : $("#getIdInstitution").val(),
          'institution_name' : $("#institution_name").val(),
          'institution_abbr' : $("#institution_abbr").val(),
          'institution_rep' : $("#institution_rep").val(),
          'institution_phone' : $("#institution_phone").val(),
          'institution_fax' : $("#institution_fax").val(),
          'institution_postalcode' : $("#institution_postalcode").val(),
          'institution_address' : $("#institution_address").val(),
          'institution_remarks' : $("#institution_remarks").val()
        },
      })
      .done(function(data){
        console.log(data.institution);
        $("#overflow_institution").hide();
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
        console.log("サービスAjax通信に失敗");
      });
    }
  }
}
