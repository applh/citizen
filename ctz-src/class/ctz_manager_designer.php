<?php

/*
Class:        Ctz_manager_designer
creation:     03/03/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_manager_designer extends Ctz_object
{
   /* MEMBERS */
   public $html;

   public $tmp;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {

      $system=ctz_var('SYSTEM');
      $ip=$system->getip();
      $system->get_media_content("/manager/base.htm", $this, 'html');

      $translate=array();

      $user=ctz_var('USER');
      $usid=$user->sid();
      $sid=$user->get_session('id');

      $jslib.=$system->get_media_content("/manager/md5.js");
      $jslib.=$system->get_media_content("/manager/base64.js");

      $login.="CTZ_ip='$ip';";
      
      if (!$sid) {
         //$login.="alert('NEED LOGIN [$ip]');";
         $login.=$system->get_media_content("/manager/login.js");
      }
      else {
         $login.=$system->get_media_content("/manager/logout.js");
      }

      $translate["<!--TITLE-->"].="Manager - ".$_SERVER['SERVER_NAME'];
      $translate["<!--SESSION-ID-->"].="$usid|$sid";

      $translate["/*-JS-INLINE-*/"].=$jslib;
      $translate["/*-JS-INLINE-*/"].=$login;


      $system->string_replace($translate, $this, 'html');

      ctz_set('response.container', $this);
      ctz_set('response.attribute', 'html');
   }
}

?>
