
let loginInputElList = document.getElementsByClassName('login_input');
let loginBtn = document.getElementById('loging_btn');
loginInfoCnt = 0;
for (let i=0,len=loginInputElList.length; i<len; i++) {
  if(loginInputElList[i].value){loginInfoCnt++}

  loginInputElList[i].addEventListener('change',(event)=>{
    loginInfoCnt = event.target.value ? loginInfoCnt+1 : loginInfoCnt-1;
    loginBtn.style.backgroundColor = loginInfoCnt===2 ? 'var(--login-input-focus-color)' : null;
  });
}


