<?php

/*
Class:        Ctz_system
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_system extends Ctz_object
{
   /* MEMBERS */
   public $AT;

   public $datadir;
   public $tabenv;
   
   public $sitemd5;
   public $sitedir;
   public $albumsdir;

  // public $tabalbumini;

   public $basedir;

   public $tabdirall;

   public $ipuser;
   
   public $mailer;

   public $tabuserinfo;

   /* METHODS */

   public function __construct () {
      parent::__construct();

      $this->check_security();

      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function check_security () {
      $this->check_subdomain();
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

   public function init () {

      $stop=ctz_var('security.stop');
      if ($stop) {
          return;
      }
      $this->basedir=ctz_var('BASE_DIR');

      $this->AT='@';

      // some definitions
      if (!defined('CTZ_ALBUMS')) {
         define('CTZ_ALBUMS', 'albums');
      }

      $this->tabenv=array();
      if (defined('CTZ_DATA')) {
         $this->datadir=realpath(CTZ_DATA);
      }
      else {
         $error=new Ctz_Error('CTZ_DATA NOT DEFINED');
         ctz_set('system_error', true);
      }

      if (is_dir($this->datadir)) {
         ctz_set('DATA_DIR', $this->datadir);

         $listdir=glob($this->datadir."/*", GLOB_ONLYDIR);
         $nbdir=count($listdir);
      }

      if ($nbdir < 1) {
         $error=new Ctz_Error('EMPTY DATA DIR');
         ctz_set('system_error', true);
      }
      else {
         $sitemd5=$this->get_sitemd5();
	 $siteindex=$sitemd5[0];
         $testsitedir=$this->datadir."/websites/$siteindex/$sitemd5";
         if (is_dir($testsitedir)) {
            $this->sitedir=$testsitedir;
         }
         else {
            $error=new Ctz_Error('MISSING SITE DIR');
            ctz_set('system_error', true);
         }
      }
      
      $albumsdir=$this->sitedir."/".CTZ_ALBUMS;
      if (!is_dir($albumsdir)) {
            $error=new Ctz_Error('MISSING ALBUMS DIR');
            ctz_set('system_error', true);
      }
      else {
         $this->albumsdir=$albumsdir;
         ctz_set('albums_dir', $this->albumsdir);
      }

      // cache the album.ini keys/values
      $this->tabalbumini=array();

      // register the global object
      ctz_set('SYSTEM', $this);
   }

   public function get_meta ($uri, $key) {
      // remove album path
      $uri=str_replace($this->albumsdir, '', $uri);
      $aini=$this->albumsdir.$this->get_uri_dir($uri)."album.ini";
      $ainimd5=md5($aini);
      $tabini=$this->tabalbumini[$ainimd5];
      if (is_array($tabini)) {
         // read the cache
         $res=$tabini[$key];
      }
      else {
         // load the cache
         if (is_file($aini)) {
            $tabini=parse_ini_file($aini);
            $res=$tabini[$key];
            $this->tabalbumini[$ainimd5]=$tabini;
         }
      }
      return $res;
   }

   public function get_mailer () {
      if (!$this->mailer) {
         $this->mailer=new Ctz_mailer();
      }
 
      return $this->mailer;
   }

   public function get_sitemd5 () {
      if ($this->sitemd5) {
         return $this->sitemd5;
      }

      $res=md5($_SERVER['SERVER_NAME']);

      $uri=$_SERVER['REQUEST_URI'];
      if ( defined('CTZ_MULTISITE') && (CTZ_MULTISITE == 'DIR') ) {
         $domaindir=strtok($uri, '/');
         $res=md5("$domaindir.".$_SERVER['SERVER_NAME']);

         $uri=str_replace($domaindir, '', $uri);
         $uri=str_replace('//', '/', $uri);
      }
      $this->sitemd5=$res;

      return $res;
   }

   public function get_dir ($var, $create=false) {
      
      $A=$this->AT;

      switch ($var) {
      case 'plugins':
         $res=$this->datadir."/$A/plugins";
         break;
      case 'dns':
         $dnstok=explode('.', $_SERVER['SERVER_NAME']);
         $dnspath='';
         foreach($dnstok as $d) {
            if ($d) {
               $dnspath="$d/$dnspath";
            }
         }
         $res=$this->datadir."/$A/dns/$dnspath/".$this->get_sitemd5();
         break;
      case 'dnsroot':
         $res=$this->datadir."/$A/dns";
         break;
      case 'design':
         $res=$this->sitedir."/$A/design";
         break;
      case 'cache':
         $sitemd5=basename($this->sitedir);
         //$siteindex=$sitemd5[0];
         $siteindex=substr($sitemd5, 0, 3);
         $res=$this->datadir."/cache/$siteindex/$sitemd5";
         //$res=$this->sitedir."/$A/cache";
         break;
      case 'log':
         $sitemd5=basename($this->sitedir);
         $siteindex=$sitemd5[0];
         $res=$this->datadir."/log/$siteindex/$sitemd5";
         //$res=$this->sitedir."/$A/log";
         break;
      case 'message':
         $res=$this->sitedir."/$A/message";
         break;
      case 'tmp':
         $res=$this->sitedir."/$A/tmp";
         break;
      case 'memory':
         $res=$this->sitedir."/$A/memory";
         break;
      case 'session':
         $res=$this->sitedir."/$A/session";
         break;
      case 'user':
         $res=$this->sitedir."/$A/user";
         break;
      case 'album':
         if (!$this->albumsdir) {
            $this->albumsdir=$this->sitedir."/".CTZ_ALBUMS;
         }
         $res=$this->albumsdir;
         break;
      default:
         // makes the dir relative to sitedir ?
         $res=str_replace($this->sitedir, '', $var);
         $res=strtolower($res);
         $res=$this->sitedir.preg_replace(',[^a-z0-9-.@/],', '-', $res);
         break;
      }

      if ($create && $res && !is_dir($res)) {
         // some protection against too long recursive folders
         if (mb_strlen($res) < 1000) {
            if (!is_dir($res) && !file_exists($res)) {
               @mkdir($res, ctz_var('CHMOD_DIR'), true); 
            }
         }
      }

      return $res;
   }

   public function get_ip_key() {
      // should provide an unique key relative to route to replace IP unicity
      return md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_X_FORWARDED_FOR'].$_SERVER['HTTP_CLIENT_IP']);
   }
   
   public function get_user_ip()
   {
      if ($_SERVER['HTTP_CLIENT_IP']) {   
         $ip=$_SERVER['HTTP_CLIENT_IP'];
      }
      elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
         $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];  
      }
      else {
         $ip=$_SERVER['REMOTE_ADDR'];
      }
      // clean
      $ip=trim(preg_replace('/[^0-9.]/', '', $ip));

      // publish the ip
      ctz_set('ip', $ip);        

      return $ip;
   }

   public function getip ($ip='') {
      if (!$this->ipuser) {
         if (!$ip)       
            $ip=$this->get_user_ip();
         else
            $ip=trim(preg_replace('/[^0-9.]/', '', $ip));

         // store the user ip
         $this->ipuser=$ip;
      }
      return $this->ipuser;
   }

   public function mkdir4($base, $tag, $create) {
      $md5=md5($tag);
      $tagdir=$md5[0]."/".$md5[1]."/".$md5[2]."/".$md5[3]."/".$md5;

      // create the basedir anyway
      $basedir=$this->get_dir($base, true);
      // create the tagdir only if requested
      $res=$this->get_dir("$basedir/$tagdir", $create);

      return $res;
   }

   public function logip ($ip='') {
      $ip=$this->getip($ip);
      $ipnum=trim(preg_replace('/[^0-9]/', '', $ip));

      $logdir=$this->get_dir('log', true);
      //$ipdir=$logdir.$ipnum[0].$ipnum[1]."/".$ipnum[2].$ipnum[3]."/".$ip;
      $ipdir=$logdir."/".$ipnum[0].$ipnum[1].$ipnum[2]."/".$ipnum[3].$ipnum[4].$ipnum[5];
      if (!is_dir($ipdir)) {
         mkdir($ipdir, ctz_var('CHMOD_DIR'), true); 
      }

      $now=microtime(true);
      $s=ceil($now);
      //$today=date('y-m-d', $s);
      //$log=date('y/m/d.H:i:s.', $s).str_pad(ceil(1000*$now)%1000, 3, '0', STR_PAD_LEFT)." ".$_SERVER['REQUEST_URI'].' |'.$_SERVER['HTTP_REFERER'];
      // format y/m/d-H:i:s.time.ms /uri |referer
      // keep monthly files
      $month=date('y-m', $s);
      $log=date('y/m/d.H:i:s.', $s)."$now,$ip,".$_SERVER['REQUEST_URI'].' |'.$_SERVER['HTTP_REFERER'];
      //file_put_contents("$ipdir/$ipnum-$month-access.log", "$log\n", FILE_APPEND);      
   }


   public function is_file_album ($file) {
      //DEV: add some protection ???
      //
      $search=$this->albumsdir.$file;
      return is_file($search);
   }

   public function get_album_meta ($uri) {
      $adir=$this->get_dir_album($uri, true);
      $res=array();

      // get the local rules
      $metafile="$adir/here.ini";
      if (is_file($metafile)) {
         $res=$res+parse_ini_file($metafile);
      }

      $minlen=strlen($this->albumsdir);
      // load current album meta file album.ini and also parent ones
      while ($minlen <= strlen($adir)) {
         $metafile="$adir/album.ini";
         if (is_file($metafile)) {
            $res=$res+parse_ini_file($metafile);
         }
         $adir=dirname($adir);
      }
      return $res;
   }

   public function get_album_files ($uri) {
      $adir=$this->get_dir_album($uri, true);
      $tabfile=glob("$adir/*");

      // clean the file names
      foreach($tabfile as $f => $file) {
         if (is_file($file)) {
            $fbase=basename($file);
            $lowbase=strtolower($fbase);
            $lowbase=preg_replace('/[^a-z0-9.]/', '-', $lowbase);
            if ($lowbase != $fbase) {
               // clean files to have only low characters
               @rename("$adir/$fbase", "$adir/$lowbase");
               $tabfile[$f]="$adir/$lowbase";
            }
         }
      }
      return $tabfile;
   }

   public function touch ($file) {
      return touch($file);
   }

   public function get_albums ($amax=1000) {
      if (empty($this->tabdirall)) {

         $root=$this->get_dir('album', true);
         $this->tabdirall=array("/");
         $tabcount=1;
         $i=0;
         $max=1;
         while ($i < $max) {
            $curalbum=$this->tabdirall[$i];
            $curtab=glob("$root$curalbum*", GLOB_ONLYDIR|GLOB_MARK); // add a trailing slash
            // add each album to list to explore
            foreach($curtab as $add) {
               // check album.ini
               //$protection=$this->get_meta(str_replace($root, '', $add), 'protection');
               $protection=$this->get_meta($add, 'protection');

               if (!$protection || ($protection == "public")) {
                  $this->tabdirall[]=str_replace($root, "", $add);
                  $tabcount++;
               }
               // stop exploration if max reached
               //$max=min(count($this->tabdirall), $amax);
               $max=min($tabcount, $amax);
               if ($max == $amax) {
                  break;
               }
            }
            $i++;
            //$max=min(count($this->tabdirall), $amax);
            $max=min($tabcount, $amax);
         }
      }

      return $this->tabdirall;
   }

   public function get_uri_dir ($uri) {
      $pparts=pathinfo($uri);
      if ($pparts['extension']) {
         return $pparts['dirname'];
      }
      else {
         return $uri;
      }
   }
   public function get_dir_album ($uri, $create=false) {

      // uri must start with a /
      if ($uri[0] != "/") {
         $uri="/".$uri;
      }
      // check if uri has extension => looking for file folder
      $pparts=pathinfo($uri);
      if ($pparts['extension']) {
         $basename=$pparts['basename'];
      }
      $uri=$this->get_uri_dir($uri);
      $search=$this->albumsdir.$uri;
      if ($basename) {
         if (!is_file("$search/$basename")) {
            $loop=10;
         }
      }
      elseif (!is_dir($search)) {
         $loop=10;
      }
      
      if ($loop > 0) {
         $interval0="/".$this->AT."*";
         $interval=$interval0;
         while (0 < $loop--) {
            $cursearch=$this->albumsdir.$interval."$uri";
            if ($check=glob($cursearch, GLOB_ONLYDIR)) {
               if ($basename) {
                  foreach($check as $d => $trydir) { 
                     if (is_file("$trydir/$basename")) {
                        $first=$trydir;
                        $loop=0;
                        // just keep the first one
                        break;
                     }
                  }
               }
               else {
                  $first=$check[0];
                  $loop=0;
               }
            }

            if (!$first) {
               // try one more level
               $interval.=$interval0;
            }
         }

         if (is_dir($first)) {
            $search=$first;
         }
         elseif ($create) {
            // create if not found
            $this->custom("album.dir.notfound", $search);
            //$this->get_dir($search, true);
         }
      }

      return $search;
   }

   public function install_design_model ($model='default') {
      // some security
      $model=trim(basename($model));

      $srcdir=$this->basedir."/media/design/model";
      $srcmodel="$srcdir/$model.ini";

      $todir=$this->get_dir('design', true);
      $tomodel="$todir/model-$model.ini";

      if (is_file($srcmodel)) {
         copy($srcmodel, $tomodel);
      }
   }


   public function get_media_content ($uri, $obj=null, $attr='') {
      $res=$this->basedir."/media$uri";
      if (is_file($res)) {
         if (($obj != null) && $attr) {
            $obj->$attr=file_get_contents($res);
         }
         else {
            return file_get_contents($res);
         }
      }

   }

   public function string_replace($translate, $obj, $attr) {
      if (!empty($obj->$attr) && is_string($obj->$attr)) {
         $from=array_keys($translate);
         $to=array_values($translate);

         $obj->$attr=str_replace($from, $to, $obj->$attr);
      }
   }

   public function readfile ($uri) {
      $search=$this->get_dir_album($uri)."/".basename($uri);
      if (is_file($search)) {
         @readfile($search);
      }
   }

   public function get_file_contents ($uri) {
      //DEV: add some protection ???
      //
      $search=$this->get_dir_album($uri)."/".basename($uri);
      if (is_file($search)) {
         $res=file_get_contents($search);
      }
      return $res;
   }

   public function load_var ($file, $object, $attr) {
      //DEV: add some protection ???
      //
      if (is_file($file)) {
         $object->$attr=file_get_contents($file);
      }
   }

   public function get_dns_info ($info) {

      $this->custom('dns-info');

      $dnsrootdir=$this->get_dir('dnsroot', true);
      $includeroot="$dnsrootdir/index.php";

      // read the root dir first
      if (is_file($includeroot)) {
         include_once($includeroot);
      }
      $blocksubdomain=ctz_var('proxy_subdomain_block');
      if (!$blocksubdomain) {
         // don't create the folder automatically as spam risk exists
         $dnsdir=$this->get_dir('dns');
         $include="$dnsdir/index.php";

         $tabinc=array();
         $incdir=$dnsdir;
         while (strlen($incdir) > strlen($dnsrootdir)) {
            $include="$incdir/index.php";
            $blocksubdomain=ctz_var('proxy_subdomain_block');
            if ( !$blocksubdomain && is_file($include)) {
               $tabinc[]=$include;
            }
            $incdir=dirname($incdir);
         }
         $tabinc=array_reverse($tabinc);
         foreach ($tabinc as $include) {
            include_once($include);
         }
      }

      $res=ctz_var($info);

      switch ($info) {
      case 'proxy':
         break;
      default:
         break;
      }


      return $res;
   }

   public function store_message () {
      $res=true;

      $msgdir=$this->get_dir("message", true);
      $tabmsg=$_REQUEST;

      $msgip=$this->getip();
      $msgsite=$_SERVER['HTTP_REFERER'];
      $msgdate=date("ymd-His");
      $msgtimestamp=microtime(true);
      $msgid="d-".$msgdate."-i-".$msgip."-t-".$msgtimestamp.".txt";
      $msgfile="$msgdir/$msgid";
      $msghost=gethostbyaddr($msgip);

      $tabmsg["message-date"]=$msgdate;
      $tabmsg["message-site"]=$msgsite;
      $tabmsg["message-ip"]=$msgip;
      $tabmsg["message-host"]=$msghost;
      $tabmsg["message-timestamp"]=$msgtimestamp;
      $tabmsg["message-id"]=$msgid;
      
      $serialmsg=serialize($tabmsg);
      
      $write=file_put_contents($msgfile, $serialmsg);
      if ($write === FALSE) $res=FALSE;

      // send by mail ?
      $this->custom('store-message', $serialmsg);

      return $res;
   }

   public function load_plugins () {
      $res=array();

      $plugdir=$this->get_dir('plugins', true);
      $tabplugdir=glob("$plugdir/*", GLOB_ONLYDIR);
      foreach ($tabplugdir as $p => $pdir) {
         if (is_file("$pdir/index.php")) {
            @include_once("$pdir/index.php");
         }
      }

      // plugin mailer, etc...
      $res[]=new Ctz_plugin_manager();
      $res[]=new Ctz_plugin_album();
      $res[]=new Ctz_plugin_robot();
      $res[]=new Ctz_plugin_cloud();
      $res[]=new Ctz_plugin_mailer();
      $res[]=new Ctz_plugin_background();

      return $res;
   }

   public function send_mail ($obj) {
      $mailer=$this->get_mailer();
      $mailer->send_mail($obj);

   }

   public function write_file ($file, $object, $attr) {
      // some protection
      // makes file path relative to site dir
      $file=$this->sitedir.str_replace($this->sitedir, '', $file);
      if (is_string($object->$attr)) {
         file_put_contents($file, $object->$attr);
      }
      elseif (is_numeric($object->$attr)) {
         file_put_contents($file, $object->$attr);
      }
      else {
         file_put_contents($file, serialize($object->$attr));
      }
   }

   public function get_user_info ($email) {
      $userdir=$this->mkdir4("user", $email, false);
      $userfile="$userdir/user.txt";
      if (is_file($userfile)) {
         $this->tabuserinfo=unserialize(file_get_contents($userfile));
      }
      else {
         $this->tabuserinfo=array();
      }
      return $this->tabuserinfo;
   }

   public function update_user_info ($tabinfo) {
      if (is_array($tabinfo)) {
         $email=$tabinfo['email'];
         if ($email) {
            $userdir=$this->mkdir4("user", $email, true);
            $current=$this->get_user_info($email);
            $this->tabuserinfo=$tabinfo+$current;
            $userfile="$userdir/user.txt";
            // write
            $this->write_file($userfile, $this, 'tabuserinfo');

            // to improve
            $this->tabuserinfo['password']=ctz_var('password.new');
            $this->send_mail($this->tabuserinfo);
         }
      }

   }

}

?>
