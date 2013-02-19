<?php

/*
Class:        Ctz_user
creation:     02/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_user extends Ctz_object
{
   /* MEMBERS */
   public $active;
   public $key;

   public $maxage; // seconds
   
   public $sessionkey;
   public $sessiondir;
   public $sessionfile;
   public $tabsession;

   public $ip;
   public $ipkey;
   public $login;
   public $mail;
   public $microtimestamp;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->maxage=1000; // 16min 40s of inactivity allowed

      $this->active=false;

      $system=ctz_var('SYSTEM');

      $this->microtimestamp=microtime(true);
      // get ip
      $this->ip=$system->getip();
      $this->ipkey=$system->get_ip_key();
      // session key is md5 ( ipkey + user_agent )
      $this->sessionkey=md5($this->ipkey.$_SERVER['HTTP_USER_AGENT']);
      // get the session dir
      $this->sessiondir=$system->mkdir4('session', $this->sessionkey, false);
      $this->sessionfile=$this->sessiondir."/user.txt";
      // check session expiration
      if (!$this->check_timeout()) {
         if (is_file($this->sessionfile)) {
            $this->tabsession=unserialize(@file_get_contents($this->sessionfile));
         }
      }

      // publish user
      ctz_set('USER', $this);
   }

   public function check_timeout () {
      if (is_file($this->sessionfile)) {
         $mtime=filemtime($this->sessionfile);
         $now=time();
         if ($now > $mtime + $this->maxage) {
            // clean session by removing file
            unlink($this->sessionfile);
         }
         else {
            // valid session
            return false;
         }
      }
      // timeout
      return true;
   }

   public function get_key () {
      if (!$this->key) {
         $this->key=new Ctz_key();
      }
      return $key;
   }

   public function sid () {
      return $this->sessionkey;
   }

   public function get_session ($var) {
      if (is_array($this->tabsession)) {
         $res=$this->tabsession[$var];
      }

      return $res;
   }

   public function create_session () {      
      $system=ctz_var('SYSTEM');
      $this->sessiondir=$system->mkdir4('session', $this->sessionkey, true);
      $this->sessionfile=$this->sessiondir."/user.txt";
      $this->tabsession=array(
         "id" => $this->sessionkey,
         "ip" => $this->ip,
         "ipkey" => $this->ipkey,
         "http-user-agent" => $_SERVER['HTTP_USER_AGENT'],
         "time" => $this->microtimestamp,
         );
      $system->write_file($this->sessionfile, $this, 'tabsession');

      return $this->sessionkey;
   }

   public function login ($email, $password, $context) {
      $system=ctz_var('SYSTEM');
      $tabuser=$system->get_user_info($email);

      // check passwords
      // should include ip address in md5 input
      $md5pwd=md5($password);
      $ip=$system->getip();
      $md5pwdref=md5(md5($ip.$tabuser['password']));
      if ($md5pwd == $md5pwdref) {
         $sout=$this->create_session();
      }
      return  ($sout == $this->sessionkey);
      //return "$ip,$password,".$tabuser['password'].",$md5pwd,$md5pwdref";
   }

   public function logout () {
      if (is_file($this->sessionfile)) {
         // kill the session file
         unlink($this->sessionfile);
      }
   }

   public function build_random_password () {
      $passwordnew=substr(md5(microtime(true)), 0, 8);
      ctz_set('password.new', $passwordnew);
      return $passwordnew;
   }

   public function set_random_password ($email, $password, $context) {
      $system=ctz_var('SYSTEM');
      $tabuser=$system->get_user_info($email);
      if ($email == $tabuser['email']) {
         // change the information
         $passwordnew=$this->build_random_password();
         $tabuser['password']=md5($passwordnew);
         // update the user profile
         $system->update_user_info($tabuser);
      }
      else {
         $s=serialize(array("email" => $email));
         $this->custom("user-not-found", $s);
      }
   }

   public function add_user ($tabinfo) {
      $system=ctz_var('SYSTEM');
      if (!is_array($tabinfo)) {
         $tabuser=$system->get_user_info($email);
      }
      else {
         $tabuser=$tabinfo+$system->get_user_info($email);
      }
      // change the information
      $passwordnew=$this->build_random_password();
      $tabuser['password']=md5($passwordnew);
      // update the user profile
      $system->update_user_info($tabuser);
   }
}

?>
