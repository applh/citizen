var CTZ_login={};

CTZ_login.htm=
'<table><tr><td></td><td><a id="act-logout" href="#logout">LOGOUT</a></td></tr></table>';

CTZ_login.act_logout=function () {
   CTZ_login.email=jQuery("#login-email").val();
   CTZ_login.password=jQuery("#login-password").val();
   var query=""
      +Base64.encode(CTZ_login.email+"\n"+CTZ_login.password+"\n"+"logout")
      ;
   goto='/@/plugins/manager/'+query+'/';
   window.location.replace(goto);
};
CTZ_login.act_lost=function () {
   CTZ_login.email=jQuery("#login-email").val();
   CTZ_login.password=jQuery("#login-password").val();
   var query=""
      +Base64.encode(CTZ_login.email+"\n"+CTZ_login.password+"\n"+"lost")
      ;
   goto='/@/plugins/manager/'+query+'/';
   window.location.replace(goto);
};

jQuery(".play_0 .scene").append(CTZ_login.htm);
jQuery("#act-logout").click(CTZ_login.act_logout);
jQuery("#act-lost").click(CTZ_login.act_lost);


