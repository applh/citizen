<?php

/*
Class:        Ctz_navigation
creation:     04/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_navigation extends Ctz_object
{
   /* MEMBERS */
   public $html;
   public $here;

   public $samelevel;
   public $childlevel;
   public $parentlevel;
   public $otherlevel;

   public $nbsamelevel;
   public $nbchildlevel;
   public $nbotherlevel;

   public $maxsamelevel;
   public $maxchildlevel;
   public $maxotherlevel;

   public $parentsep;
   public $samesep;

   public $tabpost;
   public $posts_html;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->parentsep=' &gt; ';
      $this->samesep=', ';
      
      $this->maxsamelevel=1000;
      $this->maxchildlevel=1000;
      $this->maxotherlevel=1000;

   }

   public function build_posts ($qs='') {
      if (is_array($this->tabpost))
         return;

      // extract values
      $tabp=array();
      parse_str($qs, $tabp);

      $this->tabpost=array();

      $request=ctz_var('REQUEST');
      $uri=$request->get_uri();
      $curlevel=substr_count($uri, '/');
      $parenturi=dirname($uri);
      if (!$parenturi) 
         $parenturi="/";

      $system=ctz_var('SYSTEM');
      $rootdir=$system->get_dir('album');
      $tabdir=$system->get_albums();

      $N="\n";
      $home=ctz_var('url_home');
      $alinks.='';
      $tabitems=array();
      $count=0;
      $tabnav=array();

      foreach($tabdir as $i => $a) {
         $araw=$a;
         $a=preg_replace(
            array(',/@[^/]*,'), 
            array(''), 
            $a
         );

         // skip if already one or root
         if (!$tabnav[$a] && ($a != "/")) {
            $ah=preg_replace(array(',^/,', ',/$,', ',/,'), array('', '', ' '), $a);

            $file="$rootdir$araw";
            $atitle='';
            if (file_exists($file)) {
               $fmtime=@filemtime($file);

               // load album.ini info
               $atitle=$system->get_meta($araw, 'title');
               if (!$atitle) {
                  $atitle=$ah;
               }

               $tabitems["$fmtime"]=""
                  .$N."<div>"
                  .$N."<span>".'<a href="'.$home.$a.'" title="'.$ah.'">'.$atitle."</a></span>"
                  .$N."</div>"
                  ;
               //remember this url
               $tabnav[$a]=$araw;

               $this->tabpost["$fmtime"]=$araw;
               $count++;
            }
         }
      }
      krsort($tabitems, SORT_NUMERIC);
      krsort($this->tabpost, SORT_NUMERIC);
      $nbpost=ctz_var("album.posts.number");
      $tabitems=array_values($tabitems);
      $iter=0;
      foreach ($tabitems as $p => $phtm) {
         $this->posts_html.=$phtm;
         $iter++;
         if ($nbpost <= $iter)
            break;
      }
      $this->posts_html.="<!--total-$count-->";
   }

   public function build_feed () {
      $request=ctz_var('REQUEST');
      $uri=$request->get_uri();
      $curlevel=substr_count($uri, '/');
      $parenturi=dirname($uri);
      if (!$parenturi) 
         $parenturi="/";

      $system=ctz_var('SYSTEM');
      $rootdir=$system->get_dir('album');
      $tabdir=$system->get_albums();

      $N="\n";
      $home=ctz_var('url_home');
      $alinks.='';
      $tabitems=array();
      $tabnav=array();
      $count=0;
      foreach($tabdir as $i => $a) {
         $araw=$a;
         $a=preg_replace(
            array(',/@[^/]*,'), 
            array(''), 
            $a
         );

         if (!$tabnav[$a] && ($a != "/")) {
            $ah=preg_replace(array(',^/,', ',/$,', ',/,'), array('', '', ' '), $a);

            $atitle='';
            $file="$rootdir$araw";
            // load album.ini
            $atitle=$system->get_meta($araw, 'title');
            if (!$atitle) {
               $atitle=$ah;
            }

            $fmtime=filemtime("$rootdir$araw");
            $tabitems["$fmtime"]=""
               .$N."<item>"
               .$N." <title>$atitle</title>"
               .$N." <link>$home$a</link>"
               .$N." <pubDate>".date("r", $fmtime)."</pubDate>"
               .$N." <guid isPermaLink='false'>$home$a</guid>"
               .$N." <description><![CDATA[$ah]]></description>"
               .$N." <content:encoded><![CDATA[$ah]]></content:encoded>"
               .$N."</item>"
               ;
            $tabnav[$a]=$ah;
            $count++;
         }
      }
      krsort($tabitems, SORT_NUMERIC);
      $alinks.=implode("", $tabitems)
         ."<!--$count-->";
      return $alinks;
   }

   public function build_html () {
      $request=ctz_var('REQUEST');
      $uri=$request->get_uri();
      // sanitize $uri ?
      $uri=preg_replace(',[^a-z0-9-/],', '', $uri);
      $curlevel=substr_count($uri, '/');
      $parenturi=dirname($uri);
      if (!$parenturi) 
         $parenturi="/";

      $system=ctz_var('SYSTEM');
      $rootdir=$system->get_dir('album');
      // get the list of albums
      $tabdir=$system->get_albums();
      $alinks.='<div class="list-albums">';
      $tabnav=array();

      // init
      $this->nbchildlevel=0;

      foreach($tabdir as $i => $araw) {
         $a=preg_replace(
            array(',/@[^/]*,'), 
            array(''), 
            $araw
         );
         if (!$tabnav[$a] && ($a != "/")) {
            $ah=preg_replace(array(',^/,', ',/$,', ',/,'), array('', '', ' '), $a);
            $atitle='';
            $file="$rootdir$araw";
            // load album.ini
            $atitle=$system->get_meta($araw, 'title');
            if (!$atitle) {
               $atitle=$ah;
            }

            $tabnav[$a]=$ah;

            $alevel=substr_count($a, '/');
            // display nicer text :-P
            $ah=preg_replace(array(',^/,', ',/$,', ',/,'), array('', '', ' '), $a);
            if ($alevel == $curlevel) {
               if ($a != $uri) {
                  if (preg_match(",^$parenturi,", $a) > 0) {
                     $this->samelevel.='<a href="'.$a.'" class="l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a>'.$this->samesep;
                  }
                  else {
                     $this->otherlevel.='<a href="'.$a.'" class="l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a> ';
                  }
               }
               else {
                  $this->here.='<a href="'.$a.'" class="here l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a> ';
               }
            }
            elseif ($alevel > $curlevel) {
               if (preg_match(",^$uri,", $a) > 0) {
                  $this->nbchildlevel++;
                  if ($this->nbchildlevel < $this->maxchildlevel) {
                     $this->childlevel.='<a href="'.$a.'" class="l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a> ';
                  }
               }
               else {
                  $this->otherlevel.='<a href="'.$a.'" class="l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a> ';
               }
            }
            elseif ($alevel < $curlevel) {
               if (preg_match(",^$a,", $uri) > 0) {
                  $this->parentlevel.='<a href="'.$a.'" class="l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a>'.$this->parentsep;
               }
               else {
                  $this->otherlevel.='<a href="'.$a.'" class="l'.$alevel.'" title="'.$ah.'">'.$atitle.'</a> ';
               }
            }
         }
      }
      $alinks.='<!--parent--><strong><span class="parent">'.$this->parentlevel."</span>";
      $alinks.='<!--here--><span class="here">'.$this->here."</span></strong>";
      $alinks.="<!--child--><br/>".'<span class="child">'.$this->childlevel.'</span>';
      //$alinks.="<!--same--><br/>".'<span class="child">'.$this->samelevel.'</span>';
      //$alinks.="<!--other-->".'<span class="other">'.$this->otherlevel.'</span>';
      $alinks.='</div>';
      $this->html.=$alinks;
   }

}

?>
