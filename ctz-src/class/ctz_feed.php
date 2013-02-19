<?php

/*
Class:        Ctz_feed
creation:     10/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_feed extends Ctz_object
{
   /* MEMBERS */
   public $xml_model;
   public $xml_model_item;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      ctz_set("feed_title", ctz_var("site.title"));
      ctz_set("feed_generator", "Citizen Web");
      ctz_set("feed_date", date("r"));
      ctz_set("feed_lang", ctz_var('lang'));

      $site_description=trim(ctz_var('site.description').' '.ctz_var('extra_description'));
      if ($site_description) {
         ctz_set('feed_description', $site_description);
      }

      $this->xml_model=<<<XMLMODEL
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
<channel>
	<title><!--FEED-TITLE--></title>
	<atom:link href="<!--FEED-URL-->" rel="self" type="application/rss+xml" />
	<link><!--FEED-SITE-URL--></link>
	<description><!--FEED-DESCRIPTION--></description>
	<lastBuildDate><!--FEED-DATE--></lastBuildDate>
	<generator><!--FEED-GENERATOR--></generator>
	<language><!--FEED-LANG--></language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
         <!--FEED-ITEMS-->
	</channel>
</rss>
XMLMODEL;

      $this->xml_model_item=<<<XMLMODELITEM
			<item>
		<title><!--FEED-ITEM-TITLE--></title>

		<link><!--FEED-ITEM-LINK--></link>
		<comments><!--FEED-ITEM-LINK-COMMENT--></comments>
		<pubDate><!--FEED-ITEM-DATE--></pubDate>
                <dc:creator><!--FEED-ITEM-AUTHOR--></dc:creator>
                <!--FEED-ITEM-CATEGORIES-->
		<guid isPermaLink="false"><!--FEED-ITEM-LINK--></guid>
		<description><![CDATA[<!--FEED-ITEM-DESCRIPTION-->]]></description>
			<content:encoded><![CDATA[<!--FEED-ITEM-CONTENT-->]]></content:encoded>
			<wfw:commentRss><!--FEED-ITEM-LINK-RSS-COMMENT--></wfw:commentRss>
		<slash:comments><!--FEED-ITEM-COMMENT-NB--></slash:comments>
                </item>
XMLMODELITEM;

      $navigation=new Ctz_navigation();
      $xml_items=$navigation->build_feed();

      ctz_set('feed_items', $xml_items);

   }

   public function get_xml ($object, $attr) {
      $res=$this->xml_model;
      if (property_exists($object, $attr)) {
         $object->$attr=$res;
      }
   }

}

?>
