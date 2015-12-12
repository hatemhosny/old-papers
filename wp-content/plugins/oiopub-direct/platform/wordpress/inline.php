<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* WORDPRESS INLINE ADS CLASS */

class oiopub_inline {

	var $data;
	
	var $post_num = 0;
	var $empty_slot = 0;
	var $links_per_post = 1;

	//init
	function oiopub_inline() {
		global $oiopub_set;
		if(oiopub_inline && $oiopub_set->enabled == 1) {
			add_filter('the_content', array(&$this, 'output'));
			add_filter('widget_text', array(&$this, 'shortcodes'));

		}
	}

	//process data
	function data() {
		global $oiopub_set;
		$data = array( "links"=>array(), "ads"=>array() );
		if(($oiopub_set->inline_ads['enabled'] == 1 || $oiopub_set->inline_links['enabled'] == 1)) {
			$post_ids = oiopub_post_ids();
			if(oiopub_count($post_ids) > 0) {
				//output class exists?
				if(!class_exists('oiopub_output', false)) {
					include_once($oiopub_set->folder_dir . "/include/output.php");
				}
				//get ad data
				$output = new oiopub_output;
				$ads = $output->purchased_ads('inline');
				$output->zn = 'inline_ads';
				$output->zn_def = 'inline_defaults';
				$output->zone = $oiopub_set->inline_ads['selection'];
				$ads = array_merge($ads, $output->default_ads('banner'));
				//loops through ads
				if(oiopub_count($ads) > 0) {
					$count = 0;
					$inline_ads = array();
					foreach($ads as $a) {
						if($a->item_type == 4 && $oiopub_set->inline_links['enabled'] == 1) {
							//get post IDs
							$pids = $a->post_id > 0 ? array($a->post_id) : $post_ids;
							//loop through post IDs
							foreach($pids as $pid) {
								if(!isset($data['links'][$pid])) {
									$data['links'][$pid] = array();
								}
								$count = oiopub_count($data['links'][$pid]) + 1;
								$data['links'][$pid][$count]['url'] = stripslashes($a->item_url);
								$data['links'][$pid][$count]['tooltip'] = stripslashes($a->item_tooltip);
								$data['links'][$pid][$count]['phrase'] = stripslashes($a->post_phrase);
								$data['links'][$pid][$count]['nofollow'] = intval($a->item_nofollow);
								$data['links'][$pid][$count]['id'] = intval($a->item_id);
								$data['links'][$pid][$count]['direct'] = intval($a->direct_link);
							}
						} elseif($oiopub_set->inline_ads['enabled'] == 1) {
							$inline_ads[] = $a;
						}
					}
					//unset data
					unset($ads, $post_id);
					//shuffle data
					shuffle($inline_ads);
					$inline_ads_num = oiopub_count($inline_ads);
					//loop through posts
					if($inline_ads_num > 0) {
						$count = $rand = 0;
						foreach($post_ids as $p) {
							if(!is_single() && $count >= $oiopub_set->inline_ads['showposts']) {
								break;
							}
							if(isset($inline_ads[$rand]) && get_post_meta($p, "oio_inline", true) == 0) {
								$data['ads'][$p]['image'] = stripslashes($inline_ads[$rand]->item_url);
								$data['ads'][$p]['site'] = stripslashes($inline_ads[$rand]->item_page);
								$data['ads'][$p]['tooltip'] = stripslashes($inline_ads[$rand]->item_tooltip);
								$data['ads'][$p]['nofollow'] = intval($inline_ads[$rand]->item_nofollow);
								$data['ads'][$p]['id'] = intval($inline_ads[$rand]->item_id);
								$data['ads'][$p]['direct'] = intval($inline_ads[$rand]->direct_link);
								$data['ads'][$p]['notes'] = stripslashes($inline_ads[$rand]->item_notes);
								if($oiopub_set->inline_ads['reuse'] == 0) {
									$rand++;
								} else {
									$rand = rand(0, $inline_ads_num-1);
								}
								$count++;
							}	
						}
					}
					//unset data
					unset($inline_ads, $inline_ads_num, $rand, $count);
				}
			}
		}
		return $data;
	}
	
	//final output
	function output($content) {
		global $id, $oiopub_set;
		//format shortcodes
		$content = $this->shortcodes($content);
		//stop here?
		if($id <= 0 || (is_page() && !is_home())) {
			return $content;
		}
		//get default data?
		if(empty($this->data)) {
			$this->data = $this->data();
		}
		//format inline links?
		if($oiopub_set->inline_links['enabled'] == 1) {
			$data = isset($this->data['links'][$id]) ? $this->data['links'][$id] : array();
			$content = $this->links_output($content, $id, $data);
		}
		//format inline ads?
		if($oiopub_set->inline_ads['enabled'] == 1) {
			$data = isset($this->data['ads'][$id]) ? $this->data['ads'][$id] : array();
			$content = $this->ads_output($content, $id, $data);
		}
		//format post links
		$content = $this->postlinks($content, $id);
		//all done
		return $content;
	}
	
	//output links
	function links_output($content, $id, $data) {
		global $oiopub_set, $oiopub_module;
		if($oiopub_set->inline_links['enabled'] != 1) {
			return $content;
		}
		//begin processing
		$link_count = oiopub_count($data);
		if($link_count > 0) {
			for($z=1; $z <= $link_count; $z++) {
				//set defaults
				$nofollow = '';
				$new_window = "";
				$url = $data[$z]['url'];
				$phrase = $data[$z]['phrase'];
				//add to array
				if(is_array($oiopub_set->pids) && $data[$z]['id'] > 0) {
					$oiopub_set->pids[] = $data[$z]['id'];
				}
				//tracker?
				if($oiopub_module->tracker == 1 && $oiopub_set->tracker['enabled'] == 1 && $data[$z]['id'] > 0 && $data[$z]['direct'] == 0) {
					$url = $oiopub_set->tracker_url . '/go.php?id=' . $data[$z]['id'];
				}
				//nofollow?
				if($oiopub_set->inline_links['nofollow'] == 1 || ($oiopub_set->inline_links['nofollow'] == 2 && $data[$z]['nofollow'] == 1)) {
					$nofollow = ' rel="nofollow"';
				}
				//new window?
				if($oiopub_set->general_set['new_window'] == 1) {
					$new_window = ' target="_blank"';
				}
				//create link(s)
				$this->tmp_links = array(
					'lc' => '<a' . $nofollow . ' href="' . $url . '" title="' . $data[$z]['tooltip'] . '"' . $new_window . '>' . $phrase . '</a>',
					'uc' => '<a' . $nofollow . ' href="' . $url . '" title="' . $data[$z]['tooltip'] . '"' . $new_window . '>' . ucfirst($phrase) . '</a>',
				);
				//update content
				$content = preg_replace_callback("/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b" . preg_quote($phrase, '/') . "\b/imsU", array( $this, 'links_output_callback' ), $content, $this->links_per_post);
			}
		}
		return $content;
	}

	//link replacement callback
	function links_output_callback($matches) {
		if($matches[0] === ucfirst($matches[0])) {
			return $this->tmp_links['uc'];
		} else {
			return $this->tmp_links['lc'];
		}
	}

	//output ads
	function ads_output($content, $id, $data) {
		global $oiopub_set, $oiopub_module;
		//check vars
		if($oiopub_set->inline_ads['enabled'] != 1) {
			return $content;
		}
		if(is_feed() && $oiopub_set->inline_ads['selection'] != 2) {
			return $content;
		}
		//new window?
		if($oiopub_set->general_set['new_window'] == 1) {
			$new_window = " target='_blank'";
		} else {
			$new_window = "";
		}
		//position?
		if($oiopub_set->inline_ads['position'] == "left") {
			$position = "oio-inline-left";
		} else {
			$position = "oio-inline-right";
		}
		//div height
		$div_height = $oiopub_set->inline_ads['height'] + 20;
		//count ads
		$ads_count = oiopub_count($data);
		//begin processing
		if($ads_count > 0) {
			//set defaults
			$number = 0;
			$output = "";
			$nofollow = '';
			$url = $data['site'];
			//add to array
			if(is_array($oiopub_set->pids) && $data['id'] > 0) {
				$oiopub_set->pids[] = $data['id'];
			}
			//tracker?
			if($oiopub_module->tracker == 1 && $oiopub_set->tracker['enabled'] == 1 && $data['id'] > 0 && $data['direct'] == 0) {
				$url = $oiopub_set->tracker_url . "/go.php?id=" . $data['id'];
			}
			//nofollow?
			if($oiopub_set->inline_ads['nofollow'] == 1 || ($oiopub_set->inline_nofollow == 2 && $data['nofollow'] == 1)) {
				$nofollow = ' rel="nofollow"';
			}
			if($oiopub_set->inline_ads['selection'] == 1) {
				//video ad
				$exp = explode("|", $data['image']);
				if(empty($data['notes'])) {
					$output .= "<div class='" . $position . " oio-center'>\n";
					$output .= oiopub_image_display("http://www.youtube.com/v/".$exp[1], "", $oiopub_set->inline_ads['width'], $oiopub_set->inline_ads['height']);
					$output .= "</div>\n";
				} else {
					$output .= "<div style='width:" . $oiopub_set->inline_ads['width'] . "px; height:" . $oiopub_set->inline_ads['height'] . "px;'>" . stripslashes($data['notes']) . "</div>";
				}
			} elseif($oiopub_set->inline_ads['selection'] == 2) {
				//banner ad
				if(empty($data['notes'])) {
					if(is_feed()) {
						$feed_style = "float:left; margin-right:10px;";
					} else {
						$feed_style = "";
					}
					$display = oiopub_image_display($data['image'], $url, $oiopub_set->inline_ads['width'], $oiopub_set->inline_ads['height'], 0, $data['tooltip'], '', '', $feed_style);
					if(strpos($display, "<object") === false) {
						$image = "<a" . $nofollow . " href='" . $url . "'" . $new_window . ">" . $display . "</a>";
					} else {
						$image = $display;
					}
				} else {
					$image = "<div style='width:" . $oiopub_set->inline_ads['width'] . "px; height:" . $oiopub_set->inline_ads['height'] . "px;'>" . stripslashes($data['notes']) . "</div>";
				}
				$content = "<div class='" . $position . " oio-center'>" . $image . "</div>" . $content;
			} elseif($oiopub_set->inline_ads['selection'] == 3) {
				//rss feed ad
				$rssbox = '';
				if(empty($data['notes'])) {
					$template = stripslashes($oiopub_set->inline_ads['template']);
					$output .= "<div class='" . $position . " oio-center'>\n";
					$output .= "<script type='text/javascript'>\n";
					$output .= "<!--\n";
					$output .= "var rssoutputscript = '" . $oiopub_set->plugin_url . "/libs/rssbox/main.php';\n";
					$output .= "var rssfeed = new rssdisplaybox('" . $data['image'] . "', 'myrss_feed', '');\n";
					$output .= "rssfeed.set_pid(" . $data['id'] . ");\n";
					$output .= "rssfeed.set_items_shown(3);\n";
					$output .= "rssfeed.set_template('" . $template . "');\n";
					$output .= "rssfeed.start();\n";
					$output .= "//-->\n";
					$output .= "</script>\n";
					$output .= "</div>\n";
				} else {
					$output .= "<div style='width:" . $oiopub_set->inline_ads['width'] . "px; height:" . $oiopub_set->inline_ads['height'] . "px;'>" . stripslashes($data['notes']) . "</div>";
				}
			}
			//set content
			$content = "<div style='min-height:" . $div_height . "px; _height:" . $div_height . "px;'>" . $output . $content . "</div>";
		} else {
			//default ad slot
			if((is_single() || $oiopub_set->inline_ads['showposts'] > 0) && !is_feed() && get_post_meta($id, "oio_inline", true) == 0) {
				if($this->empty_slot == 0 && $this->post_num < $oiopub_set->inline_ads['showposts']) {
					//link to show
					if(!empty($oiopub_set->general_set['buypage'])) {
						if(strpos($oiopub_set->general_set['buypage'], "http://") === false) {
							$purchase_url = $oiopub_set->site_url . "/" . $oiopub_set->general_set['buypage'] . "#inline";
						} else {
							$purchase_url = $oiopub_set->general_set['buypage'] . "#inline";
						}
					} else {
						$purchase_url = $oiopub_set->plugin_url . "/purchase.php?do=inline&amp;type=" . $oiopub_set->inline_ads['selection'] . "";
					}
					//get output
					$output  = "<div style='width:" . $oiopub_set->inline_ads['width'] . "px; height:" . $oiopub_set->inline_ads['height'] . "px; line-height:" . $oiopub_set->inline_ads['height'] . "px;'>\n";
					$output .= "<div class='oio-inline-border'>\n";
					if($oiopub_set->inline_ads['price'][0] > 0) {
						$output .= "<a rel='nofollow' href='" . $purchase_url . "'><b>" . __oio("Advertise Here") . "</b></a>\n";
					} else {
						$output .= "<a rel='nofollow' href='http://www.oiopublisher.com'><b>" . __oio("OIOpublisher") . "</b></a>\n";
					}
					$output .= "</div>\n";
					$output .= "</div>\n";
					$content = "<div class='" . $position . " oio-center oio-empty'>" . $output . "</div>" . $content;
					$content = "<div class='oio-inline-empty' style='min-height:" . $div_height . "px; _height:" . $div_height . "px;'>" . $content . "</div>";
				}
				$this->empty_slot++;
			}
		}
		$this->post_num++;
		return $content;
	}
	
	//post specific links
	function postlinks($content, $id) {
		global $oiopub_set;
		if($oiopub_set->general_set['postlinks'] != 1) {
			return $content;
		}
		$extra = '';
		if(($oiopub_set->inline_links['enabled'] == 1 && $oiopub_set->inline_links['price'][0] > 0) || ($oiopub_set->inline_ads['enabled'] == 1 && $oiopub_set->inline_ads['price'][0] > 0)) {	
			$extra .= "<div class='oio-postlinks'><b>&raquo; " . __oio("Inline Ad Purchase") . ":</b>&nbsp;&nbsp;";
			if($oiopub_set->inline_links['enabled'] == 1 && $oiopub_set->inline_links['price'][0] > 0) {
				$extra .= "<a rel='nofollow' href='" . $oiopub_set->plugin_url . "/purchase.php?do=inline&amp;type=4&amp;p=" . $id . "'>" . __oio("Intext Link") . "</a>";
			}
			if($oiopub_set->inline_links['enabled'] == 1 && $oiopub_set->inline_links['price'][0] > 0 && $oiopub_set->inline_ads['enabled'] == 1 && $oiopub_set->inline_ads['price'][0] > 0) {
				$extra .= " | ";
			}
			if($oiopub_set->inline_ads['enabled'] == 1 && $oiopub_set->inline_ads['price'][0] > 0) {
				if($oiopub_set->inline_ads['selection'] == 1) $ad_type = __oio("Video Ad");
				if($oiopub_set->inline_ads['selection'] == 2) $ad_type = __oio("Banner Ad");
				if($oiopub_set->inline_ads['selection'] == 3) $ad_type = __oio("RSS Feed Ad");
				$extra .= "<a rel='nofollow' href='" . $oiopub_set->plugin_url . "/purchase.php?do=inline&amp;type=" . $oiopub_set->inline_ads['selection'] . "'>" . $ad_type . "</a>";
			}
			$extra .= "</div>\n";
		}
		$content = $content . $extra;
		return $content;
	}

	//ad zone shortcode
	function shortcodes($content) {
		//search for matches
		if(!preg_match_all('/\[\%\s?oiopub\-(.*)\s?\%\]/iU', $content, $matches)) {
			return $content;
		}
		//loop through matches
		foreach($matches[0] as $placeholder) {
			//set vars
			$output = '';
			$findme = str_replace(array('[%', '%]'), '', $placeholder);
			//explode
			$exp = explode('-', trim($findme));
			//get parts
			$type = $exp[1];
			$zone_id = (int) $exp[2];
			$position = empty($exp[3]) ? "left" : $exp[3];
			//function name
			$function = "oiopub_" . $type . "_zone";
			//add to output?
			if($zone_id > 0) {
				//get output
				if(is_feed()) {
					$output = '';
				} elseif(function_exists($function)) {
					$output = $function($zone_id, array(
						'fluid' => false,
						'echo' => false,
						'align' => $position,
					));
				}
				//set output
				if($output) {
					$margin = $position == "right" ? "margin-left" : "margin-right";
					$output = '<div style="float:' . $position . '; margin:0; padding:0; ' . $margin . ':10px;">' . $output . '</div>';
				}
				//replace content
				$content = str_replace($placeholder, $output, $content);
			}
		}
		//return
		return $content;
	}
	
}

?>