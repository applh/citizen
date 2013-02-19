var CTZ_login={};

CTZ_login.htm=
'<form id="form-login"><table><tr><td>EMAIL</td><td><input id="login-email" name="login-email" type="text"/></td></tr><tr><td>PASSWORD</td><td><input id="login-password" name="login-password" type="password"/></td></tr><tr><td></td><td><a id="act-login" href="#login">LOGIN</a></td></tr><tr><td></td><td><hr/><div><a id="act-lost" href="#lost">I lost my password</a></div><div></div></td></tr></table></form>';

CTZ_login.act_login=function () {
   CTZ_login.email=jQuery("#login-email").val();
   CTZ_login.password=MD5(CTZ_ip+MD5(jQuery("#login-password").val()));
   var query=""
      +Base64.encode(CTZ_login.email+"\n"+CTZ_login.password+"\n")
      ;
   goto='/@/plugins/manager/'+query+'/';
   window.location.replace(goto);
};
CTZ_login.act_lost=function () {
   CTZ_login.email=jQuery("#login-email").val();
   CTZ_login.password=MD5(CTZ_ip+MD5(jQuery("#login-password").val()));
   var query=""
      +Base64.encode(CTZ_login.email+"\n"+CTZ_login.password+"\n"+"lost")
      ;
   goto='/@/plugins/manager/'+query+'/';
   window.location.replace(goto);
};

jQuery(".play_0 .scene").append(CTZ_login.htm);
jQuery("#act-login").click(CTZ_login.act_login);
jQuery("#act-lost").click(CTZ_login.act_lost);

