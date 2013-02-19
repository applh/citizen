<?php

/*
Class:        Ctz_plugin_cloud
creation:     24/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin_cloud extends Ctz_plugin
{
   /* MEMBERS */
   public $private_robots;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->tab_action=array("dns-info");

   }
   
   public function activate ($action, $object) {
      $system=ctz_var('SYSTEM');

      switch ($action) {
      case 'dns-info':
         // LH HACK 
         $dnsdir=$system->get_dir('dns', false);

         if (!is_dir($dnsdir)) {
            $tabdns=explode(".", $_SERVER['SERVER_NAME']);
            $tabdns=array_reverse($tabdns);
            $tld=$tabdns[0];
            // don't create folder if IP address
            if (!is_numeric($tld)) {
               $dnsdir=$system->get_dir('dns', true);
            }
            else {
               $serverip=gethostbyname($_SERVER['SERVER_NAME']);
               if ($serverip == $_SERVER['SERVER_NAME']) {
                  $dnsdir=$system->get_dir('dns', true);
               }
               else {
                  header("HTTP/1.0 404 Not Found", true, 404);
                  exit();
               }
            }
         }

         // shouldn't create folder automatically as spam risk exists
         $include="$dnsdir/index.php";
         if (!is_file($include)) {
            touch($include); 
         }

         if (is_file($include)) {
            $cnt=file_get_contents($include);
            if (!$cnt) {
               $ref=$_SERVER['HTTP_REFERER'];
               if ($ref) {
                  $ref=parse_url($ref);
                  $ref=$ref['host'];
                  $server=$_SERVER['SERVER_NAME'];

                  if ($ref != $server) {

                     $iref=pathinfo($ref);
                     $ihere=pathinfo($server);

                     $extref=$iref['extension'];
                     $exthere=$ihere['extension'];

                     $refdom=preg_replace("/$extref$/", '', $ref);
                     if (preg_match("/^$refdom/", $server)) {
                        $ipref=gethostbyname($ref);
                        $iphere=gethostbyname($server);
                        // avoid direct looping
                        if ($ipref != $iphere) {
                           $build=true;
                        }
                     }
                  }
               }

               if ($build) {
                  $cnt="<?php\n   ctz_set('proxy', '$ref');\n   ctz_set('proxy.private', 'true');\n?>";
                  file_put_contents($include, $cnt);

                  // add also robots.txt protection;
                  $this->private_robots="User-agent: * \nDisallow: / \n";
                  
                  $filerobots=ctz_var('albums_dir').'/robots.txt';
                  $system->write_file($filerobots, $this, "private_robots");
               }
            }
         }
         break;
      default:
         break;
      }
   
   }
}

?>
