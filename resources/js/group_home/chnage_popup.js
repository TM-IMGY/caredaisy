export default class ChangePopup {
    constructor(){}
    static popup(clicked){
        let changed_flg = document.getElementById("changed_flg").value;
        //TODO リリース対象から外すため暫定的にアラート表示しない
        // if (changed_flg === 'true'){
        //     let clicked_tab = document.getElementById("clicked_tab_or_user");
        //     clicked_tab.value = clicked.id;
        //     let dialog = document.getElementById("confirm_dialog");
        //     let message = document.getElementById("confirm_dialog_message");
        //     dialog.classList.remove('confirm_dialog_hidden');
        //     message.innerHTML = "この画面から移動しますか？入力したデータは保存されません。";

        //     return false;
        // }
        return true;
    }
}