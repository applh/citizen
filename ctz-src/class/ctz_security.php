<?php

/*
Class:        Ctz_security
creation:     29/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_security extends Ctz_object
{
   /* MEMBERS */
   public $user;

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
      //$dnsdir=$system->get_dir('dns', true);

      $this->check_subdomain();

      $this->user=new Ctz_user();

      $this->check_user($this->user);

   }

   public function check_user ($user) {

   }

   public function check_subdomain () {
      $dns=trim($_SERVER['SERVER_NAME']);
      $tabdns=explode('.', $dns);
      $nbdns=count($tabdns);
      $maxdns=ctz_var('dns_max');
      if ($nbdns > $maxdns) {
         ctz_set('security.stop', true);

         $ip=trim($_SERVER['REMOTE_ADDR']);
         error_log("IP[$ip]SECURITY[$dns]");
      }

   }

}

?>
