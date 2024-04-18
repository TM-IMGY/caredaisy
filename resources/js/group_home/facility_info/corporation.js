/**
 * 法人
 * @author eikeda
 */

export default class Corporation{
  constructor(){
    this.elementID = 'tm_contents_corporation';

    // 保存ボタン
    document.getElementById('corporation_update').addEventListener('click',this.openCorporationSubmitDialog.bind(this));
    // はいボタン
    document.getElementById('updatabtn_corporation').addEventListener('click',this.submitCorporation.bind(this));
    // いいえボタン
    document.getElementById('cancelbtn_corporation').addEventListener('click',this.closeCorporationSubmitDialog.bind(this));
  }

  setFacilityData(user){
    //corporationIdが取得できた場合データ表示
    if(user.corporationId){
      //法人情報のidセット
      $("#getIdCorporation").val(user.corporationId);

      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      // postData を改修
      $.ajax({
        url:'facility_info/corporation',
        type:'POST',
        data:{'corporation_id':user.corporationId},
      })
      .done(function(data){
        $("#corporation_name").val(data.corporations.name);
        $("#corporation_name_abbr").val(data.corporations.abbreviation);
        $("#corporation_rep").val(data.corporations.representative);
        $("#corporation_phone").val(data.corporations.phone_number);
        $("#corporation_fax").val(data.corporations.fax_number);
        $("#corporation_postalcode").val(data.corporations.postal_code);
        $("#corporation_address").val(data.corporations.location);
        $("#corporation_remarks").val(data.corporations.remarks);
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
      });
    }else{
      //クリア
      $("#corporation_name").val("");
      $("#corporation_name_abbr").val("");
      $("#corporation_rep").val("");
      $("#corporation_phone").val("");
      $("#corporation_fax").val("");
      $("#corporation_postalcode").val("");
      $("#corporation_address").val("");
      $("#corporation_remarks").val("");
    }
  }

  openCorporationSubmitDialog(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // ダイアログ表示
    $("#overflow_corporation").show();
  }

  closeCorporationSubmitDialog(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // ダイアログ非表示
    $("#overflow_corporation").hide();
  }

  submitCorporation(){
    // 変更フラグをリセット
    document.getElementById("changed_flg").value = false;

    // 保存
    if($("#corporation_name").val() != ""){
      $.ajaxSetup({
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}
      });
      $.ajax({
        url:'facility_info/corporation/update',
        type:'POST',
        data:{
          'corporation_id' : $("#getIdCorporation").val(),
          'corporation_name' : $("#corporation_name").val(),
          'corporation_name_abbr' : $("#corporation_name_abbr").val(),
          'corporation_rep' : $("#corporation_rep").val(),
          'corporation_phone' : $("#corporation_phone").val(),
          'corporation_fax' : $("#corporation_fax").val(),
          'corporation_postalcode' : $("#corporation_postalcode").val(),
          'corporation_address' : $("#corporation_address").val(),
          'corporation_remarks' : $("#corporation_remarks").val()
        },
      })
      .done(function(){
        $("#overflow_corporation").hide();
      })
      .fail(function(xhr){
        if(xhr.status == 419){
          location.href = location.href;
        }
      });
    }
  }
}
