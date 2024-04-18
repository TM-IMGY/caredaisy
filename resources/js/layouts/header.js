document.addEventListener('DOMContentLoaded',async()=>{
  // ログアウトメニューを取得する
  let logoutMenu = document.getElementById('application_header_logout_menu');
  let logoutCancelBtn = document.getElementById('application_header_logout_cancel_btn');
  let logoutMenuParent = logoutMenu.parentNode;
  logoutMenuParent.removeChild(logoutMenu);

  logoutMenuParent.addEventListener('mouseenter',()=>{
    logoutMenuParent.appendChild(logoutMenu);
  })
  logoutMenuParent.addEventListener('mouseleave',()=>{
    logoutMenuParent.removeChild(logoutMenu);
  })
  logoutCancelBtn.addEventListener('click',()=>{
    logoutMenuParent.removeChild(logoutMenu);
  });
});
