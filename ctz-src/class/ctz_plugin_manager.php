<?php

/*
Class:        Ctz_plugin_manager
creation:     03/03/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin_manager extends Ctz_plugin
{
   /* MEMBERS */
   public $response;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->tab_action=array("plugin-manager", "user-not-found");
   }

   public function activate ($action, $object) {

      switch ($action) {
      case 'plugin-manager':
         $this->process_login();
         ctz_set('response.redirect', $_SERVER['HTTP_REFERER']);
         //ctz_set('response.container', $this);
         //ctz_set('response.attribute', 'response');
         break;
      case 'user-not-found':
         $arg=unserialize($object->serial);
         $email=$arg['email'];
         $emailroot=ctz_var('ctz_email');
         if ($email && ($email == $emailroot)) {
            $user=ctz_var('USER');
            $user->add_user($arg);
         }
         break;
      default:
         break;
      }

   }

   public function process_login () {
      $system=ctz_var('SYSTEM');
      $request=ctz_var('REQUEST');

      $uri=$request->get_uri();
      $base='/@/plugins/manager/';
      $data=str_replace($base, '', $uri);
      if ($data) {
         // remove end slash
         if ("/" == substr($data, -1)) {
            $data=substr($data, 0, -1);
         }
      
         $data=trim(base64_decode($data));
         $tabdata=explode("\n", $data);
         $email=trim($tabdata[0]);
         $password=trim($tabdata[1]);
         $context=trim($tabdata[2]);
         $user=ctz_var('USER');
         switch($context) {
         case 'lost':
            $user->set_random_password($email, $password, $context);
            break;
         case 'logout':
            $user->logout();
            break;
         default:
            $loginok=$user->login($email, $password, $context);
            break;
         }

         // read the session id
         $sid=$user->get_session('id');
         $this->response="$loginok|$sid|$email|$password|$context|$data";
      }
   }

}

?>
