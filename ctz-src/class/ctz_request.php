<?php

/*
Class:        Ctz_request
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_request extends Ctz_object
{
   /* MEMBERS */
   public $uri;
   public $uri_album;
   public $content_type;
   public $taburl;
   public $protocol;
   public $basename;
   public $path_info;

   public $mime_types;

   public $ajax;
   public $backend;

   public $uri_feed;

   public $uri_feed_regex;
   public $uri_special_regex;

   /* METHODS */

   public function __construct () {
      parent::__construct();

      $this->mime_types = array(
            // FORBIDDEN
            'ini' => 'FORBIDDEN',

            // text
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',

            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

      $this->uri_special_regex=",^/@/,";
      $this->uri_feed_regex=",/feed/$,";

      $this->uri_feed="/feed/";

      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {

      $https=trim($_SERVER['HTTPS']);
      if ($https) {
         $this->protocol='https';
      }
      else {
         $this->protocol='http';
      }

      if ($_POST['@form']) {
         $this->ajax=true;
         ctz_set('request_ajax', true);
         ctz_set('process_form', true);
      }

      $qs=$_SERVER['QUERY_STRING'];
      $this->uri=$_SERVER['REQUEST_URI'];
      // remove query_string from request_uri
      $this->uri=str_replace("?$qs", '', $this->uri);

      $this->taburl=parse_url($this->protocol."://".$_SERVER['SERVER_NAME'].$this->uri);
      $this->path_info=pathinfo($this->uri);

      $this->extension=strtolower(trim($this->path_info['extension']));
      $this->basename=strtolower(trim($this->path_info['basename']));
      if ($this->extension) {
         $this->uri_album=$this->extension['dirname'].'/';
      }
      else {
         if ($this->uri[count($this->uri)-1] != "/") {
            // uri like http://domain.tld/uri
            $this->uri_album=$this->uri.'/';
         }
         else {
            // uri like http://domain.tld/uri/
            $this->uri_album=$this->uri;
         }
      }

      // check special uri
      $this->check_special_uri();

      // publish some var
      // must be in lower case as capitals are reserved to global objects
      ctz_set('ALBUM', $this->uri_album);
      ctz_set('URL_HOME', $this->protocol."://".$_SERVER['SERVER_NAME']);
      ctz_set('URL_ALBUM', ctz_var('URL_HOME').ctz_var('ALBUM'));

      ctz_set('request_uri', $this->uri);
      ctz_set('uri_album', $this->uri_album);
      ctz_set('url_home', $this->protocol."://".$_SERVER['SERVER_NAME']);
      ctz_set('url_feed', ctz_var('url_home').$this->uri_feed);
      ctz_set('url_album', ctz_var('url_home').ctz_var('uri_album'));

      // some default initialisation
      $site_title=ctz_var('site.title');
      if (!$site_title) {
         ctz_set('site.title', $_SERVER['SERVER_NAME']);
      }

      // publish object
      ctz_set('REQUEST', $this);
   }
   
   public function check_special_uri() {

      $system=ctz_var('SYSTEM');

      // check proxy mode
      $proxyurl=$system->get_dns_info('proxy');
   
         //if ($this->uri_album == $this->uri_feed) {
      if (ctz_var('proxy')) {
         // as php can be used for every type of content :-P
         // trust the browser request ?
         $accept=$_SERVER["HTTP_ACCEPT"];
         $referer=$_SERVER["HTTP_REFERER"];
         list($ct, $info)=explode(",", $accept);
         if ( $referer && $ct) {
            // don't use HTTP_ACCEPT if no REFERER
            if ($ct == "text/css") {
               $this->content_type=strtolower($ct);
            }
         }
         // LH HACK
         // check the suffix to confirm
         $qs=$_SERVER['QUERY_STRING'];
         $qsend=substr($qs, -4, 4);
         if (".jpg" == $qsend) {
            $this->content_type="image/jpeg";
         }
         elseif (".png" == $qsend) {
            $this->content_type="image/png";
         }
      }
      elseif (0 < preg_match($this->uri_feed_regex, $this->uri_album)) {
         // remove special /feed/
         $this->uri_album=dirname($this->uri_album).'/';
         $this->uri=$this->uri_album;

         $this->content_type='text/xml';
      }
      elseif (0 < preg_match($this->uri_special_regex, $this->uri_album)) {
         //echo $this->uri_album;

         $sl="/";
         $at=strtok($this->uri_album, $sl);
         $env=strtok($sl);
         //echo $env;
         if ($env == "plugins") {
            ctz_set('process_plugin', true);
            $pluginame=strtok($sl);
            //echo $pluginame;
            ctz_set('process_plugin_name', $pluginame);
         }
         elseif ($env == "manager") {
            ctz_set('site.manager', true);
         }
         //exit();
      }
      elseif ($this->extension == "php") {
         // as php can be used for every type of content :-P
         // trust the browser request ?
         $accept=$_SERVER["HTTP_ACCEPT"];
         list($ct, $info)=explode(",", $accept);
         if ($ct) {
            $this->content_type=strtolower($ct);
         }
         // otherwise should also test the qs end extension ?
      }
   }

   public function get_content_type () {
      if ($this->content_type) {
         return $this->content_type;
      }

      $res=$this->mime_types[strtolower($this->extension)];
      if (!$res) 
         $res="text/html";

      if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
         $res="application/x-www-form-urlencoded; charset=UTF-8";
      }

      $this->content_type=$res;
      return $res;
   }

   public function get_uri () {
      $res=$this->uri;
      return $res;
   }

   public function has_extension () {
      return trim($this->extension);
   }
}

?>
