"use strict";
document.addEventListener("DOMContentLoaded", async () => {
    let home = document.getElementById("category_main_btn_home");
    let facility = document.getElementById("category_main_btn_facility");
    let staff = document.getElementById("category_main_btn_staff");
    let facilityUser = document.getElementById(
        "category_main_btn_facility_user"
    );
    let carePlan = document.getElementById("category_main_btn_care_plan");
    let result = document.getElementById("category_main_btn_result");
    let transmit = document.getElementById("category_main_btn_transmit");
    if (home) {
        home.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    if (facility) {
        facility.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    if (staff) {
        staff.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    if (facilityUser) {
        facilityUser.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    if (carePlan) {
        carePlan.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    if (result) {
        result.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    if (transmit) {
        transmit.addEventListener("click", function (e) {
            showDaialog(e);
        });
    }
    function showDaialog(e) {
        let changed_flg = document.getElementById("changed_flg").value;
        //TODO リリース対象から外すため暫定的にアラート表示しない
        // if (changed_flg === "true") {
        //     let clicked_tab = document.getElementById("clicked_tab_or_user");
        //     clicked_tab.value = e.currentTarget.id;
        //     let dialog = document.getElementById("confirm_dialog");
        //     let message = document.getElementById("confirm_dialog_message");
        //     dialog.classList.remove("confirm_dialog_hidden");
        //     message.innerHTML =
        //         "この画面から移動しますか？入力したデータは保存されません。";
        //     e.preventDefault();
        // }
    }

    let dialog_yes = document.getElementById("confirm_dialog_yes");
    let dialog_no = document.getElementById("confirm_dialog_no");
    dialog_yes.addEventListener("click", function (e) {
        document.getElementById("changed_flg").value = false;
        let clicked_tab = document.getElementById("clicked_tab_or_user").value;
        document.getElementById(clicked_tab).click();
        document
            .getElementById("confirm_dialog")
            .classList.add("confirm_dialog_hidden");
    });
    dialog_no.addEventListener("click", function (e) {
        document
            .getElementById("confirm_dialog")
            .classList.add("confirm_dialog_hidden");
    });
});
