<?php

/*
Class:        Ctz_proxy
creation:     01/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_proxy extends Ctz_object
{
   /* MEMBERS */
   public $curl_headers;
   public $ccall;
   public $ccall_info;
   public $source_url;
   public $uri;
   public $curl_protocol;
   public $curl_ssl_check;
   public $response_data;
   public $curl_error;

   public $curl_timeout;
   public $curl_connectimeout;

   /* METHODS */

   public function __construct ($uri) {
      parent::__construct();
 
      $this->uri=$uri;

      $this->init();
   }

   public function __destruct () {
       parent::__destruct();
  }

   public function init () {
      $this->curl_timeout=240;
      $this->curl_connecttimeout=5;

      $this->curl_headers=array();
      $system=ctz_var('SYSTEM');
      $this->source_url=$system->get_dns_info('proxy');

      $this->curl_error="";
   }

   public function process ($url='') {

      if ($url) {
         $this->source_url=$url;
      }

      // START CURL
      if ($this->source_url) {
         if (!preg_match(",^http://,", $this->source_url)) {
            $ishttps=ctz_var('proxy_https');
            if ($ishttps) {
               $this->curl_protocol="https://";
            }
            else {
               $this->curl_protocol="http://";
            }
         }

         //$this->ccall=curl_init($this->source_url.$this->uri);
         $curltarget=$this->curl_protocol.$this->source_url.$_SERVER['REQUEST_URI'];
         $this->ccall=curl_init($curltarget);
         //error_log("PROXY:$this->source_url$this->uri");
         //error_log("PROXY:$curltarget");
      }
      else {
         return;
      }
   
      // forward IP
      $ipforward=$_SERVER['HTTP_X_FORWARDED_FOR'];
      if (!$ipforward) {
         $this->curl_headers[]="X-Forwarded-For: ".$_SERVER['REMOTE_ADDR'];
      }
      else {
         $this->curl_headers[]="X-Forwarded-For: $ipforward, ".$_SERVER['REMOTE_ADDR'];
      }
      
      // hack
      if (preg_match(",application/json,", $_SERVER['HTTP_ACCEPT'])) {
   	$this->curl_headers[]="Accept: application/json, text/javascript, */*";
      }
      if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
         $this->curl_headers[]="X-Requested-With: XMLHttpRequest";
      }
      if (!empty($this->curl_headers)) {
         curl_setopt($this->ccall, CURLOPT_HTTPHEADER, $this->curl_headers); // follow redirections
      }
      curl_setopt($this->ccall, CURLOPT_RETURNTRANSFER, true); // return result in string
      curl_setopt($this->ccall, CURLOPT_BINARYTRANSFER, true); // return raw result 
      curl_setopt($this->ccall, CURLOPT_ENCODING, "gzip"); // get gzip compression 
      curl_setopt($this->ccall, CURLOPT_FOLLOWLOCATION, true); // follow redirections
      curl_setopt($this->ccall, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]); // forward user agent
      curl_setopt($this->ccall, CURLOPT_REFERER, $_SERVER["HTTP_REFERER"]); // forward referer
      curl_setopt($this->ccall, CURLOPT_CONNECTTIMEOUT, $this->curl_connecttimeout); // limit connection timeout to 10 seconds
      curl_setopt($this->ccall, CURLOPT_TIMEOUT, $this->curl_timeout); // limit connection total timeout to 300 seconds (5 minutes)

      if (empty($_POST)) {
         //curl_setopt($this->ccall, CURLOPT_POSTFIELDS, $_REQUEST); // forward POST parameters
         curl_setopt($this->ccall, CURLOPT_HTTPGET, true); // let it be GET
         //error_log("REQUEST:".serialize($_REQUEST));
      }
      else {
         curl_setopt($this->ccall, CURLOPT_POSTFIELDS, $_POST); // forward POST parameters
      }

      if (!$this->curl_ssl_check) {
         curl_setopt($this->ccall, CURLOPT_SSL_VERIFYPEER, FALSE); // SSL 
         curl_setopt($this->ccall, CURLOPT_SSL_VERIFYHOST, FALSE); // SSL 
      }

      // Launch the CURL request
      $this->response_data=curl_exec($this->ccall);

      if ($this->response_data === FALSE) {
         //$this->response_data=curl_error($this->ccall);
         $errormsg=curl_error($this->ccall);
         if (!$errormsg) {
            $errormsg='ERROR';
         }
         $this->curl_error=$errormsg;
      }
      // get the CURL response header
      $this->ccall_info=curl_getinfo($this->ccall);
      //$CT['response_content_type']=$ccall_info['content_type'];
      //$CT['response_url']=$ccall_info['url'];
      //$CT['response_redirect_count']=$ccall_info['redirect_count'];
      curl_close($this->ccall);
      // END CURL 
      
      return $this->response_data;
   }

}

?>
