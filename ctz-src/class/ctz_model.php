<?php

/*
Class:        Ctz_model
creation:     24/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_model extends Ctz_object
{
   /* MEMBERS */
   public $file;
   public $tabvar;

   public $album;
   public $html;

   public $grid_w;

   public $text_addon;

   public $maxparent;
   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->maxparent=10;
         
      // for later HTML text replacement
      $this->text_addon=array();

      $this->text_addon["<!--CTZ-INCLUDE-JAVASCRIPT-->"]=<<<JSINC
JSINC;
      $this->text_addon["<!--CTZ-JS-FOOTER-->"]=<<<JSBG
<script type="text/javascript">
/* <![CDATA[ */

var CT={};
CT.ajax={};
CT.design={};
CT.design.desc="";
CT.design.dimension="";
CT.toparse=["h1", "h2", ".post"];
CT.curparse="";

CT.ajax.designer="";
CT.ajax.imager="";
CT.maxget=2000;
if (!jQuery.support.tbody) {
   CT.maxget=4000;
}

CT.parse_dimension=function(o, oid) {
   var txt;
   var jo=jQuery(o);
   var joo=jo.offset();
   txt=oid+"|"+joo.left+"|"+joo.top+"|"+jo.width()+"|"+jo.height()+"|"+jo.attr("class")+"\\n";
   return txt;
};

CT.parse_bloc=function(i) {
   var gh;
   gh="";
   gh+=CT.parse_dimension(this, CT.curparse+"_"+i);
   var desc;
   desc=jQuery(this).html();
   CT.design.desc+=desc;
   if (CT.design.dimension.length < CT.maxget) {
       CT.design.dimension+=gh;
   }
};


CT.bgbuilder_cb=function (responseText, textStatus, XMLHttpRequest) {
   CT.page_anim();
};

CT.page_anim=function(){};


CT.init=function() {
   var gh;
   gh="";

   gh+="window|"+"0"+"|"+"0"+"|"+jQuery(window).width()+"|"+jQuery(window).height()+"|"+""+"\\n";
   gh+="document|"+"0"+"|"+"0"+"|"+jQuery(document).width()+"|"+jQuery(document).height()+"|"+""+"\\n";
   
   var po=jQuery("#page");
   var poo=po.offset();
   gh+="page|"+poo.left+"|"+poo.top+"|"+po.width()+"|"+po.height()+"|"+po.attr("class")+"\\n";

   CT.design.dimension+=gh;
   for (x in CT.toparse) {
      CT.curparse=CT.toparse[x];
      jQuery(CT.curparse).each(CT.parse_bloc);
   };
   var getquery=jQuery.param({ctztext:CT.design.dimension});
   //CT.design.md5=getquery;
   CT.bodybgcolor=jQuery("body").css("background-color");
   if (CT.ajax.getimager) {
      jQuery("body").css("background", CT.bodybgcolor+" url("+CT.ajax.getimager+'?l='+CT.design.dimension.length+'&'+getquery+"&out.jpg) no-repeat scroll center top");
   }

   // robot callback to website
   jQuery.ajax({url: '/@/plugins/robot/'});
};


CT.ajax.getimager="/@/plugins/background/";

CT.toparse=[".page", ".page_bloc", ".page_bloc a", "p", "h1, h2, h3, h4, h5, h6", ".post", ".pagemenu", "strong", "input", ".footer", "ul", "ol"];

jQuery(function() {
   // load images
   jQuery(".imagelist a").each(function(i) {
      url=jQuery(this).attr("href");
      var h='<img src="' + url + '" alt="' + url + '" title="' + url + '" />';
      jQuery(this).empty().append(h);
   });
});

// load at the end
jQuery(CT.init);

/* ]]> */
</script>

JSBG;

      $this->text_addon["<!--CTZ-INCLUDE-CSS-->"]=<<<MYCSS
MYCSS;

      $system=ctz_var('SYSTEM');
      $N="\n";
      // add root style.css
      if ($system->is_file_album('/site.css')) {
         //$this->text_addon["<!--CTZ-INCLUDE-CSS-->"].="\n".'<link rel="stylesheet" href="'.ctz_var('URL_HOME').'/site.css" />';
         $this->text_addon["<!--CTZ-INCLUDE-CSS-->"].=$N
            .'<style type="text/css" media="all">'
            .$system->get_file_contents('/site.css')
            .'</style>';
      }
      // if not root add local album style.css
      if ($system->is_file_album(ctz_var('ALBUM').'style.css')) {
         $this->text_addon["<!--CTZ-INCLUDE-CSS-->"].="\n".'<link rel="stylesheet" href="'.ctz_var('URL_ALBUM').'style.css" />';
         // problem of relative resources to css file
         /*
          $this->text_addon["<!--CTZ-INCLUDE-CSS-->"].=$N
            .'<style type="text/css" media="all">'
            .$system->get_file_contents(ctz_var('ALBUM').'/style.css')
            .'</style>';
          */
      }
 
      $this->grid_w=12;
      //$this->grid_w=16;
      ctz_set('html_container_width', $this->grid_w);
   }

   public function cleanurl ($url) {
      $res=$url;
      return $res;
   }

   public function load ($file) {
      $tab0=array(
         "scene_min" => 3,
         );
      if (is_file($file)) {
         $this->file=$file;
         $this->tabvar=parse_ini_file($file)+$tab0;
      }
   }

   public function get_content ($album) {

      $this->album=$album;

      $nbscene=$album->get_nbscene();
      $minscene=$this->tabvar['scene_min'];
      $nbscene=max($minscene, $nbscene);

      $this->html.="<!--scenenumber-$nbscene-->";

      for ($s=0; $s<$nbscene; $s++) {
         $tabcurscene=$this->album->get_scene($s);
         if (count($tabcurscene) > 0) {
            $this->build_scene($s);
         }
         elseif (!$album->is_root()) {
            $nbsearch=$this->maxparent;
            $parent=$this->album->get_parent();
            while (0 < $nbsearch--) {
               $tabcurscene=$parent->get_scene($s);
               if (count($tabcurscene) > 0) {
                  $nbsearch=-1;
                  $this->build_scene($s, $parent);
               }
               else {
                  // try one level up
                  $parent=$parent->get_parent();
               }
            }
         }
         else {
            $this->html.="[404] CONTENT NOT FOUND";
         }
      }

      // include images
      $this->include_images();

      // include contact form
      $cform=$this->album->get_contact_form();
      if ($cform) {
         $this->text_addon["<!--CONTACT-FORM-->"]=$cform;
      }

      return $this->html;
   }

   public function include_images () {
      $aurl=ctz_var('URL_ALBUM');
      $this->html.="\n".'<!--IMAGES-->';
      $this->html.="\n".'<div class="imagelist">';
      if (is_array($this->album->tabjpg)) {
         foreach ($this->album->tabjpg as $i => $fimg) {
            $bimg=basename($fimg);
            $himg="\n".'<a href="'."$aurl$bimg".'">'.$bimg.'</a>';
            $this->html.=$himg;
         }
      }
      if (is_array($this->album->tabpng)) {
         foreach ($this->album->tabpng as $i => $fimg) {
            $bimg=basename($fimg);
            $himg="\n".'<a href="'."$aurl$bimg".'">'.$bimg.'</a>';
            $this->html.=$himg;
         }
      }
      if (is_array($this->album->tabgif)) {
         foreach ($this->album->tabgif as $i => $fimg) {
            $bimg=basename($fimg);
            $himg="\n".'<a href="'."$aurl$bimg".'">'.$bimg.'</a>';
            $this->html.=$himg;
         }
      }
      $this->html.="\n".'</div>';
   }

   public function build_scene ($s, $a=null) {
      if ($a) {
         $album=$a;
      }
      else {
         $album=$this->album;
      }

      $tabcurscene=$album->get_scene($s);

      $cgrid="scene$s";

      $this->html.="\n".'<div class="play play_'."$s grid_".$this->grid_w.' alpha omega">';
         $this->html.="\n<!--play-$s-->";
         
         $line=array("left", "prolog", "scene", "center", "epilog", "right");   
         $lineclass=array();
         $totalchar=0;
         $alpha=0;
         $omega=0;
         $iter=0;
         $linechar=array();
         foreach($line as $txt) {
            $linechar[$txt]=mb_strlen($tabcurscene[$txt]);
            $totalchar+=$linechar[$txt];
            if ($linechar[$txt] > 0) $omega=$iter;
            $iter++;
            if ($totalchar == 0) $alpha=$iter;

         }

         $nbcol=count($line);
         $linegw=array(
            "left" => intval($this->grid_w/$nbcol), 
            "prolog" => intval($this->grid_w/$nbcol), 
            "scene" => intval($this->grid_w/$nbcol), 
            "center" => intval($this->grid_w/$nbcol), 
            "epilog" => intval($this->grid_w/$nbcol), 
            "right" => intval($this->grid_w/$nbcol),
         );
         if ($totalchar > 0) {
            $freecol=$this->grid_w;
            foreach($line as $txt) {
               //$linegw[$txt]=round( ($this->grid_w * $linechar[$txt]) / $totalchar );
               $itercol=round( ($this->grid_w * $linechar[$txt]) / $totalchar );
               if ($freecol - $itercol < 0) {
                  $itercol=$freecol;
               }
               $freecol-=$itercol;
               $linegw[$txt]=$itercol;
            }
         }

         $iter=0;
         $max=$nbscene-1;
         foreach($line as $txt) {
            if ($iter == $alpha) $lineclass[$txt].="alpha ";
            if ($iter == $omega) $lineclass[$txt].="omega ";
            $iter++;

            $lineclass[$txt].="grid_".$linegw[$txt];
         }

         $this->html.=$this->build_div($tabcurscene["header"], "header", "grid_".$this->grid_w." alpha omega");
         foreach($line as $linetxt) {
            $this->html.=$this->build_div($tabcurscene[$linetxt], $linetxt, $lineclass[$linetxt]);
         }
         $this->html.=$this->build_div($tabcurscene["footer"], "footer", "grid_".$this->grid_w." alpha omega");

         $this->html.="\n<!--playend-$s-->\n</div>";

   }

   function build_div ($div, $divclass, $extraclass="") {
      $res="\n";

      if ($div) {
         $aclass=trim($divclass);
         $extraclass=trim($extraclass);
         if ($extraclass) $aclass.=" ".$extraclass;
         $res.=""
            .'<div class="'."$aclass".'">'
            ."\n<!--$divclass-->"
            ."\n".Markdown($div)
            ."\n</div>"
            ;
      }
      return $res;
   }
}

?>
