<?php

/*
Class:        Ctz_response
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_response extends Ctz_object
{
   /* MEMBERS */
   public $mode;

   public $media;
   public $header;
   public $designer;
   public $data;
   public $datatype;

   public $proxy;
   public $cache;

   public $process_plugin;
   
   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {

      $redirect=ctz_var('response.redirect');
      if ($redirect) {
         header("location: $redirect");
      }
      else {
      
         //DEV:should move all this code outside of destructor
         $this->header->get_content();

         $readfile=ctz_var('response.readfile');
         $responsecontainer=ctz_var('response.container');
         $responseattribute=ctz_var('response.attribute');
         
         // LH Hack
         if ("/robots.txt" == $_SERVER['REQUEST_URI']) {
            $private=ctz_var('proxy.private');
            if ($private) {
               echo "User-agent: * \nDisallow:  /\n";
            }
            else {
               // LH Hack: empty robots.txt
               //echo "User-agent: * \nDisallow:  \n";
            }
         }      
         elseif ($readfile) {
            //LH: to improve for text files substitution?
            //warning: google html files need to be unchanged
            $system=ctz_var('SYSTEM');
            //error_log("READ FILE:$readfile");
            $system->readfile($readfile);
         }
         elseif (($this->datatype == "text/html") || ($this->datatype == "text/xml")) {
            // add extra information
            //$extra.="<!--".ctz_mstimer()."-ms-->";
            //$extra.="<!--".memory_get_peak_usage(true)."-bytes-->";
            //$extra.="<!--".Ctz_object::$total."-objects-->";
            //$extra.="<!--".Ctz_object::$train."-->";

            if ($responsecontainer && $responseattribute) {
               echo $responsecontainer->$responseattribute;
            }
            else {
               echo $this->data.$extra;
            }
         }
         elseif ($this->datatype == "text/plain") {
            // LH Hack
            if ("/robots.txt" == $_SERVER['REQUEST_URI']) {
               $private=ctz_var('proxy.private');
               if ($private) {
                  echo "User-agent: * \nDisallow:  /\n";
               }
            }
         }
         else {
            if ($responsecontainer && $responseattribute) {
               echo $responsecontainer->$responseattribute;
            }
            else {
               echo $this->data;
            }
         }
      
      }
      parent::__destruct();
   }

   public function init () {

      $request=ctz_var('REQUEST');
      
      $this->header=new Ctz_header();

      $this->cache=new Ctz_cache();
      //DEV: to improve
      $this->datatype=$request->get_content_type();

      // publish the response object so the plugins can use it
      ctz_set('RESPONSE', $this);
      
      $uri=ctz_var('request_uri');

      if ($this->cache->has_cache($uri)) {
         // retrieve cache response
         $this->data=$this->cache->get_cache($uri);
         //error_log("CACHE:$uri");
      }
      else {
         error_log("NOCACHE:".$_SERVER["SERVER_NAME"]."$uri,".$this->cache->get_key($uri));
         $system=ctz_var('SYSTEM');

         // check proxy mode
         $proxyurl=$system->get_dns_info('proxy');
         if ($proxyurl) {
            $this->mode='proxy';
         }
         else {
            // check ajax mode
            $ajaxmode=ctz_var('request_ajax');
            if ($ajaxmode) {
               $this->mode='ajax';
            }
            
            $pluginmode=ctz_var('process_plugin');
            if ($pluginmode) {
               $this->mode='plugin';
               $this->process_plugin=true;
            }
            
            $managermode=ctz_var('site.manager');
            if ($managermode) {
               $this->mode='manager';
            }
         }

         // build the response
         $this->process($uri);

         $error=ctz_var('response.error');
         // build the cache
         if (!$error && $this->data) {
            $this->cache->set_cache($uri, $this->data);
         }
         // process the cache data available
         $this->cache->process_queue();
      }

   }

   public function process ($uri) {

      switch ($this->mode) {
         case 'ajax':
            $this->process_ajax($uri);
            break;
         case 'proxy':
            $this->process_proxy($uri);
            break;
         case 'plugin':
            $this->process_plugin($uri);
            break;
         case 'manager':
            $this->process_manager($uri);
            break;
         default:
            $this->process_album($uri);
            break;
         }
   }

   public function process_manager ($uri) {
      $this->designer=new Ctz_manager_designer();
   }
 
   public function process_plugin ($uri) {
      $name=ctz_var("process_plugin_name");
      if ($name) {
         $this->custom("plugin-$name");
      }
   }
 
   public function process_album ($uri) {
      $this->media=new Ctz_album($uri);

      $redirect=$this->media->check_redirect();
      if ($redirect) {
         // change the header for redirect
         $redirurl=parse_url($redirect);
         $path=$redirurl['path'];
         $targeturl=$redirurl['scheme']."://".$redirurl['host'];
         if (!$path) {
            $srcuri=ctz_var('album.redirect.src.uri');
            $tgturi=ctz_var('album.redirect.target.uri');
            // use raw $_SERVER['REQUEST_URI'] 
            $targeturl.=str_replace($srcuri, $tgturi, $_SERVER['REQUEST_URI']);
         }
         $targeturl.=$redirurl['fragment'];
         // publish the redirection
         ctz_set('response.redirect', $targeturl);
      }
      else {
         // build the content
         $this->designer=new Ctz_designer();
         $this->designer->init_response($this->media);

         $this->data=$this->designer->get_content();
      }
   }

   public function process_ajax ($uri) {
      // ajax response was build by processing
      // just get it
      $this->data=ctz_var('response_ajax');
   }
 
   public function process_proxy ($uri) {
      $this->proxy=new Ctz_proxy($uri);
      $this->designer=new Ctz_designer();
      $this->designer->init_proxy($this->proxy);

      $this->data=$this->designer->get_content_proxy();
      $proxyerror=ctz_var('response.proxy.error');
      if ($proxyerror) {
         // propagte the error
         ctz_set('response.error', $proxyerror);
      }
   }

}

?>
