<?php
/*
Plugin Name: Dynamic Subdomains
Plugin URI: http://plugins.inteleck.com/dynamic-subdomains
Description: Automatically maps subdomains to page and post permalinks.
Version: 1.0
Author: Aaron Affleck - Inteleck
Author URI: http://www.inteleck.com
License: GPL2
*/
/*  Copyright 2012  Inteleck  (email : support@inteleck.com)
    This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class DynamicSubdomains {
	
	var $url;
	var $uri;
	var $domain;
	var $domain_depth;
	var $site_url;
	var $subdomain;
	var $subpages;
	var $subpage_depth;
	function DynamicSubdomains(){
		$this->url = $_SERVER['HTTP_HOST'];
		$this->uri = $_SERVER['REQUEST_URI'];
		$this->domain = explode('.',$this->url);
		$this->domain_depth = count($this->domain);
		$this->site_url = str_replace('http://','',site_url());
		$this->subdomain = $this->domain[0];
		$this->subpages = explode('/',$this->uri);
		$this->subpage_depth = count($this->subpages);
	}

	function ds_get_subdomain() {
		if ($this->domain_depth > 2 AND $this->domain != $this->site_url && $this->subdomain != "www") {
			echo $this->domain_depth;
			$args = 'pagename='.$this->subdomain;
			
			if($this->uri != "/" && $this->subpage_depth > 2){
				$pagepath = $this->subdomain;
				//print_r($this->subpages);
				$i=$this->subpage_depth-$this->subpage_depth+1;
				while($i < $this->subpage_depth){
					$pagepath .= "/".$this->subpages[$i];
					$i++;
				}
				$page = get_page_by_path($pagepath);
				
				$args = 'page_id='.$page->ID;
			}
			//echo $args;
			$the_query = query_posts( $args );
			
			if ( have_posts() ) {} else { 
				$args = 'name='.$this->subdomain;
				$the_query = query_posts( $args );
				if ( have_posts() ) {} else { wp_reset_query(); } 
			}
		}
		//DEBUG
		
		/*	echo"<p>subdomain: ".$this->subdomain;	
		
			
			echo "<br>args: ".$args;	
		*/
		
	}
}
$DYNSUB = new DynamicSubdomains();
add_action('init',array($DYNSUB, 'ds_get_subdomain'));
?>