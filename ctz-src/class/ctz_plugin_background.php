<?php

/*
Class:        Ctz_plugin_background
creation:     20/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin_background extends Ctz_plugin
{
   /* MEMBERS */
   public $tabrectup;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->tabrectup=array();
      $this->tab_action=array("plugin-background");
   }

   public function activate ($action, $object) {
      $response=ctz_var('RESPONSE');

      switch ($action) {
      case 'plugin-background':
         $this->build_image();
         //$this->build_rect_round();
         //$this->show_command();
         //$response->data='HELLO';
         break;
      default:
         break;
      }
   }

   public function build_rect_round () {
      $w0=100;
      $h0=100;
      // build the image
      $im = @imagecreatetruecolor($w0, $h0)
         or die();
      $background_color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
      imagefilledrectangle($im, 0, 0, $w0, $h0, $background_color);
      header("Content-Type: image/jpeg");
      imagejpeg($im);
      imagedestroy($im);
      exit();
   }

   public function show_command () {
      $memory_limit=return_bytes(ini_get('memory_limit'));

      $curdir=dirname(__FILE__);
      $pngfile=trim(basename($_REQUEST['ctzbg']));

      $description=array();
      $ctztext=$_REQUEST['ctztext'];
      if ($ctztext) {
         // parse the description file
         $description=explode("\n", $ctztext);
         $command=array();
         foreach($description as $l => $d) {
            $d=trim($d);
            list($sel, $x, $y, $w, $h, $style)=explode('|', $d);
            if (preg_match("/^p_/", $sel)) {
               $command[$sel]=array(
               "s" => $sel, 
               "x" => $x, 
               "y" => $y, 
               "w" => $w, 
               "h" => $h,
               "class" => $style,
               "line" => $d
               );
            }
         }
      }  
      print_r($command);
   }

   public function build_image () {

      $dx0=10;
      $dy0=10;

      $memory_limit=return_bytes(ini_get('memory_limit'));

      $curdir=dirname(__FILE__);
      $pngfile=trim(basename($_REQUEST['ctzbg']));

      // set black or white background color
      // default to white (255) if not set
      if ($_REQUEST['ctzbgcolor'] != "") {
         $ctzbgcolor=intval($_REQUEST['ctzbgcolor']);
      }
      else {
         $ctzbgcolor=255;
      }

      $description=array();
      $ctztext=$_REQUEST['ctztext'];
      if ($ctztext) {
         // parse the description file
         $description=explode("\n", $ctztext);
         $command=array();
         foreach($description as $l => $d) {
            $d=trim($d);
            list($sel, $x, $y, $w, $h, $style)=explode('|', $d);
            $command[$sel]=array(
               "s" => $sel, 
               "x" => $x, 
               "y" => $y, 
               "w" => $w, 
               "h" => $h,
               "class" => $style,
               "line" => $d
            );

            if ( preg_match("/^p_/", $sel) || preg_match("/^ul_/", $sel) || preg_match("/^ol_/", $sel) ) {
               // remember for up layer
               $this->tabrectup[]=array(
               'x' => $x, 
               'y' => $y, 
               'w' => $w, 
               'h' => $h,
               's' => $sel,
               );
            }
         }
      }

      if (!$stop) {

         // load the brush
         //$brushes=glob("$curdir/brush/*/*.png");
         $brushdir=ctz_var('plugin-background-brush-dir');
         $brushes=glob("$brushdir/*/*.png");
         if (is_array($brushes) && ! empty($brushes)) {
            @shuffle($brushes);
            $nbcommand=count($command);
            $brushes=array_slice($brushes, 0, $nbcommand);
            $nbbrush=count($brushes);
         }

         //$w0=min(max(1024, $command["document"]["w"]), 1920);
         $wd=$command["document"]["w"];
         $hd=$command["document"]["h"];

         $w0=min(max(1024, $command["document"]["w"]), 1280);
         $h0=min(max(540, $command["document"]["h"]), 3000);
         
         // build the image
         $im = @imagecreatetruecolor($w0, $h0)
            or die();

         // get the 1280 frame limit
         $x00=max(0, round(.5*($wd-$w0)));
         $x01=$w0;

         // fill the image with bg color
         //$background_color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
         //$background_color = imagecolorallocate($im, 255, 255, 255);
         $background_color = imagecolorallocate($im, $ctzbgcolor, $ctzbgcolor, $ctzbgcolor);
         imagefilledrectangle($im, 0, 0, $w0, $h0, $background_color);

         // build the background relative to the page
         //$x0=$command["page"]["x"];
         //$y0=$command["page"]["y"];
         // coordinate of new (0,0) in document coordinates
         $x0=$wd-$w0;
         $y0=$command["page"]["y"];
   
         $y1=10;
         $dx=15;
         $dy=15;
         $blur=2*min(intval(count($command)/10), 10);
         // randomize the order of drawing
         if (is_array($command)) {
            @shuffle($command);
         }
         $cindex=0;
         $greymin=0;
         $greymax=255;
         if (is_array($command)) {
            foreach($command as $s => $c) {
               $cindex++;
               // avoid document to hide too many things ?
               if ($s == "document") continue;

               $x=$c["x"];
               $y=$c["y"];
               $w=$c["w"];
               $h=$c["h"];
               $style=$c["class"];
               $d=$c["line"];

               $txt="$s => $x $y $w $h $style, $s";
               //$draw=max(0, rand(1,8-intval($w0*$h0/1000000)));
               $draw=rand(1, round(sqrt(3*$cindex)));
               if ($w > $h*rand(1,5)) $draw++;
               
               //break;

               switch ($draw) {
               case '1':
               case '2':
                  // colorful
                  $text_color = imagecolorallocatealpha($im, rand(0, 255), rand(0, 255), rand(0, 255), rand(40, 100));
                  // center
                  $x2=max($x00, round($x-$x00 +$w/2));
                  $y2=round($y-$y0 +$h/2);
                  $w2=max(100, min(round($w*rand(10,20)/10), 1280));
                  $h2=max(100, min(round($h*rand(10,30)/10), 1280));
                  // limits
                  $x2=max(round($w2/2), $x2);
                  // draw
                  imagefilledellipse(
                     $im, 
                     $x2, 
                     $y2, 
                     $w2, 
                     $h2, 
                     $text_color
                  );
                  break;
               case '2':
               case '3':
                  // colorful
                  $text_color = imagecolorallocatealpha($im, rand(0, 255), rand(0, 255), rand(0, 255), rand(40, 90));
                  // expand the bbox
                  /*
                  $w2=min(round($w*rand(120,200)/100.0), 1420);
                  $h2=round($h*rand(120,200)/100.0);
                  $x20=max($x00, min(round($x-$x00+($w-$w2)/2), 1280));
                  $y20=max(0, min(round($y+($h-$h2)/2), 1280));
                   */
                  $w2=$w;
                  $h2=$h;
                  // top left
                  $x20=round($x-$x00);
                  $y20=round($y-$y0);
                  // bottom right
                  $x21=$x20+$w2;
                  $y21=$y20+$h2;
                  // expand
                  $x20=round($x20*mt_rand(6,8)/10);
                  $y20=round($y20*mt_rand(6,8)/10);
                  $x20=round($x20*mt_rand(12,14)/10);
                  $y20=round($y20*mt_rand(12,14)/10);
                  // draw
                  imagefilledrectangle(
                     $im, 
                     $x20, 
                     $y20,
                     $x21, 
                     $y21, 
                     $text_color
                  );
                  break;
               default:
               // skip if no brush
               if ($nbbrush < 1) 
                  continue;

               $mybrush=$brushes[$cindex-1];
         
               $bsize=filesize($mybrush);

               // skip if too heavy
               if ( ($memory_limit - memory_get_usage(true)) < ($bsize*$brush_memory_average))
                  continue;

               // add some blur effect to give distance effect for background drawings
               if ($blur > rand(0, 100)) {
                 if ( ($memory_limit - memory_get_usage(true)) > max(15*$w0*$h0, $bsize*$brush_memory_average) ) {
                    imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
                 }
                 $blur--;
               }

               $memory_b0=memory_get_usage(true);
         
               // load the brush and draw on the current image
               $brush = imagecreatefrompng($mybrush);

               $bx=imagesx($brush);
               $by=imagesy($brush);

               imagesetbrush($im, $brush);
               $bdx=max(intval($bx/2), intval($x-$x00-$dx0+$w/2));
               $bdy=max(intval($by/2), intval($y-$y00-$dy0+$h/2));
               // add some random art
               $bdx=max(intval($bx/2), intval($bdx +$w*rand(8, 10)/15*cos(rand(0,9)*M_PI) ));
               $bdy=max(intval($by/2), intval($bdy +$y*rand(8, 10)/15*cos(rand(0,9)*M_PI) ));

               // limit to page
               $bdx=max($x00+round(.5*$bx), $bdx);
               $bdx=min($bdx, $x01-round(.5*$bx));

               $bdy=max($y00+round(.5*$by), $bdy);

               // draw the brush
               imageline($im, $bdx, $bdy, $bdx, $bdy, IMG_COLOR_BRUSHED);
      	       //imagecopy($im, $brush, intval($x-$dx0+$w/2), intval($y-$dy0+$h/2), 0, 0, imagesx($brush), imagesy($brush));
               imagedestroy($brush);

               $memory_b1=memory_get_usage(true);
               $brush_memory_average=($brush_memory_average + ($memory_b1 - $memory_b0)/$bsize)/2;
               break;
               }

            }

            /*
            $nbrectup=count($this->tabrectup);
            foreach($this->tabrectup as $i => $r) {
               if ($r['w'] && $r['h']) {

                  $choice=rand(1, 4);
                  $zw=0.1*rand(12,15);
                  $zh=0.1*rand(12,15);
                  $rw=round(max(0, min($zw*$r['w'], 1280)));
                  $rh=round(max(200, min($zh*$r['h'], 1280)));

                  $tr=rand(45, 55);
               
                  if ( preg_match("/^p_/", $r['s']) || preg_match("/^ul_/", $r['s']) || preg_match("/^ol_/", $r['s']) ) {
                     $tr=rand(45, 55);
                     //$choice=0;
                  }
                  $foreground_color = imagecolorallocatealpha(
                     $im, 
                     rand(180, 255), 
                     rand(180, 255), 
                     rand(180, 255), 
                     $tr
                  );

                  switch ($choice) {
                     case 0:
                     case 1:
                        $prx=round($r['x']+0.5*$r['w']); // center x
                        $pry=round($r['y']+0.5*$r['h']); // center y
                        $points=array();
                        $nbp=rand(3, 40);
                        for ($p=0; $p < $nbp; $p++) {
                           $rad=M_PI*2*$p/$nbp;
                           //$points[]=max(0, round($prx+0.5*min(sqrt(pow($rw*cos($rad), 2)+pow($rh*sin($rad), 2)), 1280) )); // x
                           //$points[]=max(0, round($pry+0.5*min(sqrt(pow($rw*sin($rad), 2)+pow($rh*cos($rad), 2)), 1280) )); // y
                           $fx=0.1*rand(9, 15);
                           $points[]=max(0, round($prx+0.5*min($rw*cos($rad)*$fx, 1280) )); // x
                           $points[]=max(0, round($pry+0.5*min($rh*sin($rad)*$fx, 1280) )); // y
                        }
                        $nbp=count($points);
                        if ($nbp > 5) {
                           imagefilledpolygon(
                              $im, 
                              $points, 
                              floor($nbp/2), 
                              $foreground_color
                           );
                        }
                        break;
                     case 2:
                        imagefilledrectangle(
                           $im, 
                           max($x00, round($r['x']-0.5*($rw-$r['w']))), 
                           round($r['y']-0.5*($rh-$r['h'])), 
                           $rw, 
                           $rh, 
                           $foreground_color
                        );
                     break;
                     case 3:
                        $ix=round($r['x']-0.5*($rw-$r['w']));
                        $iy=round($r['y']-0.5*($rh-$r['h']));
                        $br=rand(2, 10);
                        $br2=2*$br;
                     case 4:
                     default:
                        imagefilledellipse(
                           $im, 
                           max($x00+0.5*$r['w'], round($r['x']+0.5*$r['w'])), 
                           round($r['y']+0.5*$r['h']), 
                           $rw, 
                           $rh, 
                           $foreground_color
                        );
                     break;
                  }
               }
            }
             */

            // get an uniform color
            //$foreground_color = imagecolorallocatealpha($im, rand(200, 255), rand(200, 255), rand(200, 255), rand(85, 90));
            //imagefilledrectangle($im, 0, 0, $w0, $h0, $foreground_color);

      
         }
   
      }

      // bad results :-(
      //imagetruecolortopalette($im, true, 32768); // 256, 512, 1024, 2048, 4096, 8192, 16384, 32768
      //imagegammacorrect($im, 1.0, 1.5);
      //imageantialias($im, true);
      //imageinterlace($im, true);

      header("Content-Type: image/jpeg");
      imagejpeg($im, null, 60);
      imagedestroy($im);
   
   }
}

function imagefillroundedrect($im,$x,$y,$cx,$cy,$rad,$col)
{

// Draw the middle cross shape of the rectangle

    imagefilledrectangle($im,$x,$y+$rad,$cx,$cy-$rad,$col);
    imagefilledrectangle($im,$x+$rad,$y,$cx-$rad,$cy,$col);

    $dia = $rad*2;

// Now fill in the rounded corners

    imagefilledellipse($im, $x+$rad, $y+$rad, $rad*2, $dia, $col);
    imagefilledellipse($im, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
    imagefilledellipse($im, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
    imagefilledellipse($im, $cx-$rad, $y+$rad, $rad*2, $dia, $col);
}

function return_bytes ($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // Le modifieur 'G' est disponible depuis PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

?>
