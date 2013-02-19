<?php

/*
Class:        Ctz_designer
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_designer extends Ctz_object
{
   /* MEMBERS */
   public $album;
   public $proxy;

   public $datatype;
   public $content;
   public $rawdata;

   public $dir;
   public $model;
   public $text_addon;
   
   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {

      // initialize the custom addon
      $this->text_addon=array();
      ctz_set('text_addon', $this->text_addon);

      $system=ctz_var('SYSTEM');
      $this->dir=$system->get_dir('design', true);
      $tabmodel=glob($this->dir."/model-*.ini");
      if (count($tabmodel) < 1) {
         $this->install_model_default();
         $tabmodel=glob($this->dir."/model-*.ini");
      }
      if (count($tabmodel) == 1) {
         $this->model=new Ctz_model();
         $this->model->load($tabmodel[0]);
      }
      else {
         error_log("ERROR:nomodel:".serialize($tabmodel));
      }

      $request=ctz_var('REQUEST');
      $this->datatype=$request->get_content_type();

   }

   public function init_response ($album) {
      $this->album=$album;
      $this->content=new Ctz_content();
   }

   public function init_proxy ($proxy) {
      $this->proxy=$proxy;
      $this->content=new Ctz_content();
   }

   public function get_content_proxy () {
      $request=ctz_var('REQUEST');
      $system=ctz_var('SYSTEM');
      $uri=$request->get_uri();
      // check local ovverride
      if ($request->has_extension() && $system->is_file_album($uri)) {
         $this->rawdata=$system->get_file_contents($uri);
      }
      else {
         $this->rawdata=$this->proxy->process();
         if ($this->proxy->curl_error) {
             ctz_set('response.proxy.error', $this->proxy->curl_error);
         }
      }

      switch ($this->datatype) {
      case 'text/html':
      case 'text/css':
      case 'text/javascript':
         $this->dress_html();
         break;
      default:
         break;
      }
      return $this->rawdata;
   }

   public function get_content () {
      $type=$this->datatype;

      // check if uri is existing file in album
      if (($type != 'FORBIDDEN') && $this->album) {
         if ($this->album->is_file()) {
            $type='readfile';
         }
      }

      switch ($type) {
      case 'FORBIDDEN':
         // 
         break;
      case 'text/html':
         $res=$this->get_content_html();
         break;
      case 'text/css':
      case 'text/javascript':
         $res=$this->get_content_text();
         break;
      case 'text/xml':
         $res=$this->get_content_xml();
         break;
      case 'image/jpeg':
         $res=$this->get_content_image();
         break;
      case 'image/gif':
      case 'image/png':
            // should improve transparency management
      case 'readfile':
      default:
         $res=$this->get_content_file();
         break;
      }
      return $res;
   }

   public function get_content_text () {
      $request=ctz_var('REQUEST');
      $system=ctz_var('SYSTEM');
      $uri=$request->get_uri();
      $this->rawdata=$system->get_file_contents($uri);
      return $this->rawdata;
   }

   public function get_content_image () {
      $request=ctz_var('REQUEST');
      $system=ctz_var('SYSTEM');
      $uri=$request->get_uri();
      
      $image=new Ctz_image($uri);
      $this->rawdata=$image->get_data();
      return $this->rawdata;
   }

   public function get_content_file () {

      $request=ctz_var('REQUEST');
      $system=ctz_var('SYSTEM');
      $uri=$request->get_uri();
      ctz_set('response.readfile', $uri);
      //$this->rawdata=$system->get_file_contents($uri);
      return $this->rawdata;
   }

   public function get_content_xml () {
      $request=ctz_var('REQUEST');
      $system=ctz_var('SYSTEM');
      $uri=$request->get_uri();
      $feed=new Ctz_feed();

      $feed->get_xml($this, 'rawdata');

      $update=array(
         "<!--FEED-TITLE-->" => ctz_var('feed_title'),
         "<!--FEED-URL-->" => ctz_var('url_feed'),
         "<!--FEED-SITE-URL-->" => ctz_var('url_home'),
         "<!--FEED-DESCRIPTION-->" => ctz_var('feed_description'),
         "<!--FEED-DATE-->" => ctz_var('feed_date'),
         "<!--FEED-GENERATOR-->" => ctz_var('feed_generator'),
         "<!--FEED-LANG-->" => ctz_var('feed_lang'),
         "<!--FEED-ITEMS-->" => ctz_var('feed_items'),
         );
      
      $this->customize_content($update);

      return $this->rawdata;
   }

   public function customize_content ($update) {
      $res=$this->rawdata;


      // add model replacement
      if ($this->model) {
         if (is_array($this->model->text_addon)) {
            // warning : model addon is prevalent
            $update= $this->model->text_addon + $update;
         }
      }

      // add custom replacement
      $textaddon=ctz_var('text_addon');
      if (is_array($textaddon)) {
         // warning : custom addon is prevalent
         $update= $textaddon + $update;
      }

      $search=array_keys($update);
      $replace=array_values($update);
      $loop=true;
      $maxloop=10;
      $nbloop=$maxloop;
      while (0 < $nbloop--) {
         $oldres=$res;
         // replace the tag in the html code with content
         $res=str_replace($search, $replace, $res);

         if ($oldres == $res) {
            // stop the looping if no effect
            $nbloop=-1;
         }
      }
      $this->rawdata=$res;
   }

   public function get_content_html () {

      $system=ctz_var('SYSTEM');

      $update=array();
      
      $now=date("d/m/Y");
      ctz_set('page_title', $_SERVER['REQUEST_URI']." - ".$_SERVER['SERVER_NAME']." - $now");

      //$page_footer.="$now";
      //$page_footer.=md5($_SERVER['SERVER_NAME']);
      $navigation=new Ctz_navigation();

      $poststag=ctz_var('album.posts.tag');
      if($poststag) {
         $navigation->build_posts();
         $update["$poststag"]=$navigation->posts_html;
      }

      $navigation->build_html();
      $page_footer.=$navigation->html;
      $page_footer.='<div class="w3c-valid"><a href="http://validator.w3.org/check/referer">xhtml w3c valid</a></div>'.ctz_var('site.footer');

      // init the lib to transform warkdown content into HTML
      $markdown= new Ctz_markdown();
      // build the HTML content
      $page_content.=$this->model->get_content($this->album);

      $res.=$this->content->get_content();

      $page_title='<title>'.ctz_var('page_title').'</title>';

      $site_keywords=trim(ctz_var('album.keywords').' '.ctz_var('site.keywords').' '.ctz_var('extra_keywords'));
      if ($site_keywords) {
         $page_keywords='<meta name="keywords" content="'.$site_keywords.'" />';
      }

      $site_description=trim(ctz_var('album.description').' '.ctz_var('site.description').' '.ctz_var('extra_description'));
      if ($site_description) {
         $page_description='<meta name="description" content="'.$site_description.'" />';
      }


      $page_header_rss='<link rel="alternate" type="application/rss+xml" title="RSS wave" href="/feed/" />';

      $body_class="root ";
      $uri=ctz_var('uri_album');
      $tok=strtok($uri, '/');
      while ($tok !== FALSE) {
         $curclass= $curclass ? "$curclass-$tok" : $tok;
         $body_class.="root-$curclass ";
         $tok=strtok('/');
      }
      
      // add extra replace
      $update=array(
         "<!--CTZ-PAGE-TITLE-->" => $page_title,
         "<!--CTZ-PAGE-KEYWORDS-->" => $page_keywords,
         "<!--CTZ-PAGE-DESCRIPTION-->" => $page_description,
         "<!--CTZ-LINK-RSS-->" => $page_header_rss,
         "<!--CTZ-PAGE-CONTENT-->" => $page_content."\n",
         "<!--CTZ-PAGE-FOOTER-->" => $page_footer."\n",
         "container_width" => "container_".ctz_var('html_container_width'),
         "grid_width" => "grid_".ctz_var('html_container_width'),
         "CTZ-BODY-CLASS-WEB" => trim($body_class),
      )
      +$update;

      $this->rawdata=$res;
      $this->customize_content($update);
/*
      // add model replacement
      if ($this->model) {
         if (is_array($this->model->text_addon)) {
            // warning : model addon is prevalent
            $update= $this->model->text_addon + $update;
         }
      }

      // add custom replacement
      $textaddon=ctz_var('text_addon');
      if (is_array($textaddon)) {
         // warning : custom addon is prevalent
         $update= $textaddon + $update;
      }

      $search=array_keys($update);
      $replace=array_values($update);
      $loop=true;
      $maxloop=10;
      $nbloop=$maxloop;
      while (0 < $nbloop--) {
         $oldres=$res;
         // replace the tag in the html code with content
         $res=str_replace($search, $replace, $res);

         if ($oldres == $res) {
            // stop the looping if no effect
            $nbloop=-1;
         }
      }
      
      $this->rawdata=$res;
 */

      return $this->rawdata;
   }


   public function dress_html () {
      // replace all proxy domain name by requested local server name
      $source_url=$this->proxy->source_url;
      $update=array(
         $source_url => $_SERVER['SERVER_NAME'],
      );

      $private=ctz_var('proxy.private');
      if ($private) {
	$update["<meta name='robots' content='index,follow' />"]="<meta name='robots' content='noindex,nofollow' />";
      }
      else {
	$update["<meta name='robots' content='noindex,nofollow' />"]="<meta name='robots' content='index,follow' />";
      }

      // add model replacement
      if ($this->model) {
         if (is_array($this->model->text_addon)) {
            // warning : model addon is prevalent
            $update= $this->model->text_addon + $update;
         }
      }

      // add custom replacement
      $textaddon=ctz_var('text_addon');
      if (is_array($textaddon)) {
         // warning : custom addon is prevalent
         $update= $textaddon + $update;
      }

      $search=array_keys($update);
      $replace=array_values($update);
      $loop=true;
      $maxloop=10;
      $nbloop=$maxloop;
      $res=$this->rawdata;
      while (0 < $nbloop--) {
         $oldres=$res;
         // replace the tag in the html code with content
         $res=str_replace($search, $replace, $res);

         if ($oldres == $res) {
            // stop the looping if no effect
            $nbloop=-1;
         }
      }
      
      $this->rawdata=$res;

      return $this->rawdata;
   }

   function install_model_default () {
      $system=ctz_var('SYSTEM');
      $system->install_design_model();
   }
}

?>
