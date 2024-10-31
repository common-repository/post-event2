<?php
/**
 * get_post_event_all_event_ical
 * the_post_event_ical_link
 * get_post_event_ical_link
 * get_post_event_ical
 * get_post_event_start_date
 * get_post_event_end_date
 * get_post_event_end_time
 * get_post_event_start_time
 * get_post_event_place
 * get_post_event_as_object
 * the_post_event_html
 * get_post_event_html
 * the_post_event_map
 * get_post_event_map
 * get_events
 */


require_once('class/myPostEventQuery.php');
require_once("class/myEvent.php");

/*
** Return hour select option (hh:ss)
*/
function	getTimeOption($time = '')
{
	$i = 0;
	$minTab = array('00', '15', '30', '45');
	$final = '';
	while ($i != 24)
	{
		$y = 0;
		while ($y != 4)
		{
			if ($i < 10)
				$current = '0'.$i.':'.$minTab[$y];
			else
				$current = $i.':'.$minTab[$y];
			if ($current == $time)
				$final .= '<option selected="selected" value="'.$current.'">'.$current.'</option>';
			else
				$final .= '<option value="'.$current.'">'.$current.'</option>';
			$y++;
		}
		$i++;
	}
	return $final;
}

/*
** Display the oxfoz plugin presentation
*/
function	oxfoz_plugin_presentation($the_text, $domain, $url)
{
?>
	<div class="oxfoz_plugin">
		<div class="oxfoz_plugin_about">
			<h2><?php _e('About this plugin', 'PostEvent'); ?></h2>
			<p><?php _e($the_text, $domain); ?></p>
			<a href="http://codex.oxfoz.com/cat/plugins/<?php echo $url; ?>" title="Home Page">Plugin Home Page</a>
		</div>
		<div class="oxfoz_plugin_about">
			<h2><?php _e('About oXfoZ', 'PostEvent'); ?></h2>
			<br/>
			<img src="http://www.oxfoz.com/wp-content/themes/oxfoz/images/logo.jpg" alt="logo" /><br/><br/>
			<p><?php _e('oXfoZ Technologies is an experienced web design agency, with a qualified team to assist and guide you every step of your development process.', 'PostEvent'); ?></p>
			<a href="http://www.oxfoz.com" title="Home Page">oXfoZ Home Page</a>
		</div>
	</div>
<?php
}

/*
** Create a new iCal event
*/
function	create_event($the_event, $post, $is_save)
{
	$date_start = explode('-', $the_event->getDateStart());
	$date_start = $date_start[0] . $date_start[1] . $date_start[2];
	if ($the_event->getTimeStart())
	{
		$time_start = str_replace("h", "", str_replace(":", "", $the_event->getTimeStart())) . "00";
		if (strlen($time_start) == 5)
			$time_start = "0" . $time_start;
	}
	else
		$time_start = "000000";
	$date_end = explode('-', $the_event->getDateEnd());
	$date_end = $date_end[0] . $date_end[1] . $date_end[2];
	if ($date_end[0] == "0000")
		$date_end = "00000000";
	if ($the_event->getTimeEnd())
	{
		$time_end = str_replace("h", "", str_replace(":", "", $the_event->getTimeEnd())) . "00";
		if (strlen($time_end) == 5)
			$time_end = "0" . $time_end;
	}
	else
		$time_end = "000000";
	$fuseau = date('O');
	if ($fuseau[0] == '+')
	{
		$fuseau = substr($fuseau, 1, strlen($fuseau));
		$time_tmp = $time_start[0] . $time_start[1];
		$dif = $fuseau[0] . $fuseau[1];
		$hour = $time_tmp - $dif;
		if (strlen($hour) == 1)
			$hour = '0' . $hour;
		$time_start = $hour . substr($time_start, 2, strlen($time_start));
	}
	else
	{
		$fuseau = substr($fuseau, 1, strlen($fuseau));
		$time_tmp = $time_start[0] . $time_start[1];
		$dif = $fuseau[0] . $fuseau[1];
		$hour = $time_tmp + $dif;
		if (strlen($hour) == 1)
			$hour = '0' . $hour;
		$time_start = $hour . substr($time_start, 2, strlen($time_start));
	}
	$fuseau = date('O');
	if ($fuseau[0] == '+')
	{
		$fuseau = substr($fuseau, 1, strlen($fuseau));
		$time_tmp = $time_end[0] . $time_end[1];
		$dif = $fuseau[0] . $fuseau[1];
		$hour = $time_tmp - $dif;
		if (strlen($hour) == 1)
			$hour = '0' . $hour;
		$time_end = $hour . substr($time_end, 2, strlen($time_end));
	}
	else
	{
		$fuseau = substr($fuseau, 1, strlen($fuseau));
		$time_tmp = $time_end[0] . $time_end[1];
		$dif = $fuseau[0] . $fuseau[1];
		$hour = $time_tmp + $dif;
		if (strlen($hour) == 1)
			$hour = '0' . $hour;
		$time_end = $hour . substr($time_end, 2, strlen($time_end));
	}
	$cat = get_the_category($post->ID);
	$cat = $cat[0]->cat_name;
	$all_event = "BEGIN:VEVENT\n";
	$all_event .= "SUMMARY:" . $post->post_title . "\n";
	$all_event .= "LOCATION:" . str_replace("\"", "", str_replace("~39", "'", $the_event->getPlace())) . "\n";
	$all_event .= "DTSTART:" . $date_start . "T" . $time_start . "Z\n";
	$all_event .= "DTEND:" . $date_end . "T" . $time_end . "Z\n";
	$all_event .= "DESCRIPTION:" . get_permalink($post->ID) . "\n";
	$all_event .= "CATEGORIES:" . $cat . "\n";
	$all_event .= "END:VEVENT\n";
	return ($all_event);
}

/*
** Create an ics file with all events
*/
function	get_post_event_all_event_ical()
{
	$posts = query_events();
	$all_event = "BEGIN:VCALENDAR\n";
	$all_event .= "VERSION:2.0\n";
	$all_event .= "PRODID:" . get_bloginfo('url') . "\n";
	$is_save = array();
	if ($posts != null)
	{
		foreach ($posts as $post)
		{
			$events = get_post_meta($post->ID, '_MyEvent');
			$the_event = unserialize($events[0]);
			if ($the_event instanceof MyEvent)
			{
				if (!in_array($post->post_title, $is_save))
						$all_event .= create_event($the_event, $post, $is_save);
					$is_save[] = $post->post_title;
			}
		}
	}
	$all_event .= "END:VCALENDAR\n";
	if (is_dir(ABSPATH . "wp-content/blogs.dir"))
	{
		global $blog_id;
		$dir ='wp-content/blogs.dir/' . $blog_id . '/ics/';
		$file = fopen(ABSPATH . $dir . get_option('PostEventPrefix') . "all_events.ics", "w+");
		chmod(ABSPATH . $dir . get_option('PostEventPrefix') . "all_events.ics", 0777);
	}
	else
	{
		$file = fopen(ABSPATH . "wp-content/uploads/ics/" . get_option('PostEventPrefix') . "all_events.ics", "w+");
		chmod(ABSPATH . "wp-content/uploads/ics/" . get_option('PostEventPrefix') . "all_events.ics", 0777);
	}
	fwrite($file, $all_event);
}

/*
** Create an ics file with all events form a special category
*/
function	get_post_event_all_event_ical_cat($cat_tab)
{
	foreach($cat_tab as $cat)
	{	
		if ($cat->category_parent)
		{
			$parent_cat = array();
			$parent_cat[] = get_category($cat->category_parent);
			get_post_event_all_event_ical_cat($parent_cat);
		}
		$all_event = "BEGIN:VCALENDAR\n";
		$all_event .= "VERSION:2.0\n";
		$all_event .= "PRODID:" . get_bloginfo('url') . "\n";
		$is_save = array();
		$posts = query_events("cat=" . $cat->cat_ID);
		if ($posts != null)
		{
			foreach($posts as $post)
			{
				$events = get_post_meta($post->ID, '_MyEvent');
				$the_event = unserialize($events[0]);
				if (is_a($the_event,MyEvent))
				{
					if (!in_array($post->post_title, $is_save))
						$all_event .= create_event($the_event, $post, $is_save);
					$is_save[] = $post->post_title;
				}
			}
		}
		$all_event .= "END:VCALENDAR\n";
		if (is_dir(ABSPATH . "wp-content/blogs.dir"))
		{
			global $blog_id;
			$dir ='wp-content/blogs.dir/' . $blog_id . '/ics/';
			$file = fopen(ABSPATH . $dir . $cat->category_nicename . "_" . $cat->cat_ID . "_.ics", "w+");
			chmod(ABSPATH . $dir . $cat->category_nicename . "_" . $cat->cat_ID . "_.ics", 0777);
		}
		else
		{
			$file = fopen(ABSPATH . "wp-content/uploads/ics/" . $cat->category_nicename . "_" . $cat->cat_ID . "_.ics", "w+");
			chmod(ABSPATH . "wp-content/uploads/ics/" . $cat->category_nicename . "_" . $cat->cat_ID . "_.ics", 0777);
		}
		fwrite($file, $all_event);
	}
}

/*
** Display a link for an ics file corresponding to the posts
*/
function the_post_event_ical_link($link_text = "Get The iCal! (download .ics file)")
{
	echo get_post_event_ical_link($link_text);
}

/*
** Create a link for an ics file corresponding to the posts
*/
function get_post_event_ical_link($link_text = "Get The iCal! (download .ics file)")
{
	$id = get_the_id();	
	return ("<a href='" . get_bloginfo('url') . "/wp-content/plugins/post-event2/script.php?action=create_ics&amp;post_id=$id'>$link_text</a>");
}

/*
** Get the current event date start
*/
function get_post_event_start_date()
{
	$domain = "PostEvent";
	$id = get_the_id();
	if(isset($id))
	{
		$post_ID = $id;
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind,MyEvent))
			{
				return $EventFind->getDateStart();
			}
		}
	}
}

/* 
** Get the current event date end
*/
function get_post_event_end_date()
{
	$domain = "PostEvent";
	$id = get_the_id();
	if(isset($id))
	{
		$post_ID = $id;
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind,MyEvent))
			{
				return $EventFind->getDateEnd();
			}
		}
	}
}

/* 
** Get the current event time end
*/
function get_post_event_end_time()
{
	$domain = "PostEvent";
	$id = get_the_id();
	if(isset($id))
	{
		$post_ID = $id;
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind,MyEvent))
			{
				return $EventFind->getTimeEnd();
			}
		}
	}
}

/* 
** Get the current event time start
*/
function get_post_event_start_time()
{
	$domain = "PostEvent";
	$id = get_the_id();
	if(isset($id))
	{
		$post_ID = $id;
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind,MyEvent))
			{
				return $EventFind->getTimeStart();
			}
		}
	}
}

/* 
** Get the current event place
*/
function get_post_event_place()
{
	$domain = "PostEvent";
	$id = get_the_id();
	if(isset($id))
	{
		$post_ID = $id;
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind,MyEvent))
			{
				return $EventFind->getPlace();
			}
		}
	}
}

/* 
** Get the current event object
*/
function get_post_event_as_object()
{
$domain = "PostEvent";
	$id = get_the_id();

	if(isset($id))
	{
		$post_ID = $id;
		
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind,MyEvent))
			{
				return $EventFind;
			}
		}
	}
}

/*
** Display the current event detail
*/
function the_post_event_html()
{
	echo get_post_event_html();
}

/*
** Create the current event detail to display
*/
function get_post_event_html()
{
	$domain = "PostEvent";
	$id = get_the_id();
	if(isset($id))
	{
		$post_ID = $id;
		$EventFind = get_post_meta($post_ID, "_MyEvent");
		if(is_array($EventFind))
		{
			$EventFind =  unserialize($EventFind[0]);
			if(is_a($EventFind, MyEvent))
			{
				$disp .= "<div id=\"event\"><h3>"
				.  __("Event details", $domain) . 
				"</h3><ul><li>"
				. __("Begin:", $domain) .
				" "
				. get_date_format(__($EventFind->getDateStart(), $domain));
				if(!is_null($EventFind->getTimeStart()))
				{
					$disp .= " "
					. __("at", $domain) .
					" "
					. $EventFind->getTimeStart();
				}
				if(!is_null($EventFind->getDateEnd()))
				{
					$disp .=" </li><li>"
					. __("End:", $domain) .
					" "
					. get_date_format(__($EventFind->getDateEnd(), $domain));
					if(!is_null($EventFind->getTimeEnd()))
					{
						$disp .= " "
						. __("at", $domain) .
						" "
						. $EventFind->getTimeEnd();
					}
				}
				$disp .= "</li><li>"
				. __("Add to your calendar:", $domain) .
				" "
				. get_post_event_ical_link(__("Download ics file", $domain)) .
				"</li><li>"
				.  __("Place:", $domain) .
				" "
				. str_replace("~39", "'", $EventFind->getPlace()) .
				"</li></ul>";
				$disp .= get_post_event_map(get_option('PostEventGoogleMapWidth'), get_option('PostEventGoogleMapHeight'), get_option('PostEventGoogleBubbleWidth'), get_option('PostEventGoogleBubbleHeight'));
				global	$current_user;
				if ($current_user->ID == null && get_option("PostEventJoinOpt") == "1")
				{
					$disp .= "<p style='text-align: center'>" . __("You must be register to join events.", "PostEvent") . " <a href='" . get_bloginfo('url') . "/wp-login.php?action=register'>" . __("Register", "PostEvent") . "</a></p>";
					$disp .= "</div>";
					return $disp;
				}
				$activate = get_option('PostEventActivateRegistration');
				if ($activate == '1')
				{
					if ($EventFind->getSubscribe() == '1')
					{
						$disp .= "<script type='text/javascript' src='" . get_bloginfo('url') . "/wp-includes/js/jquery/jquery.js'></script>";
						$disp .= "<script type='text/javascript' src='" . get_bloginfo('url') . "/wp-content/plugins/post-event2/Js/form.js'></script>";
						$disp .= "<h3>" . __("Subscription", "PostEvent") . "</h3>";
						$disp .= "<form method='post' action='' onSubmit='joined_event(\"" . $post_ID . "\", \"" . get_bloginfo('url') . "\");return false'><ul><li>";
						$disp .= wp_nonce_field('PostEvent-joined-form');
						if ($current_user->ID == null )
							$disp .= __("Enter your email to register for this event : ", "PostEvent") ."<input type='text' id='user-" . $post_ID . "' name='user'/></li><li>";
						else
							$disp .= __("Your user name is : ", "PostEvent") . $current_user->user_login . "</li>";
						$opt = "";
						$i = 0;
						$query = new myPostEventQuery();
						$nb = $query->get_nbpart($post_ID, "LIMIT 0,20");
						$reste = ($EventFind->getParts() - ($nb[0]->parts + $nb[0]->nb_guests));
						if ($reste < $EventFind->getGuests())
							$ref = $reste;
						else
							$ref = $EventFind->getGuests();
						while ($i <= $ref)
						{
							$opt .= "<option value='" . $i . "'>" . $i . "</option>";
							$i++;
						}
						if ($EventFind->getGuests() != 0)
							$disp .= __("Guests number:", "PostEvent") . " <select style='width: 60px' id='guests_number-" . $post_ID . "'>" . $opt . "</select><br/>";
						if ($reste == 0)
							$disp .= __("Rest", "PostEvent") . ' ' . $reste .  __(" seat(s) for this event.", "PostEvent") . '<br/>';
						$disp .= " <input type='submit' name='join' value='" . __("Join this event", "PostEvent") . "'/> <img style='visibility: hidden;' id='" . $post_ID . "' src='" . get_bloginfo('url') . "/wp-content/plugins/post-event2/Pictures/load.gif' alt='load'/>";
						$disp .= "<input type='hidden' id='action' name='action' value='join_user'/>";
						$disp .= "<input type='hidden' id='post_id' name='post_id' value='" . $post_ID . "'/>";
						$disp .= "<div id='unsub-" . $post_ID . "' style='display: none;'><p>" . __("You already join this event, would you like to leave it ?", "PostEvent") . "</p><input type='button' value='" . __("Yes", "PostEvent") . "' onClick='unsub_user(\"" . $post_ID . "\", \"yes\", \"" . get_bloginfo('url') . "\");'/> <input type='button' value='" . __("No", "PostEvent") . "' onClick='unsub_user(\"" . $post_ID . "\", \"no\");'/></div>";
						$disp .= "<div id='mess-" . $post_ID ."' style='display: none;'></div>";
						$disp .= "</form>";
						$disp .= "<h5><em>" . __("When you join the event, to left it, click again on the 'Join this event' button", "PostEvent") . "</em></h5></li>";
						if (get_option('PostEventSeeRegisterNb') == '1')
						{
							$register = get_register($post_ID, "");
							$disp .= "<li>" . __("Registers number:", "PostEvent") . " " . count($register) . "</li>";
						}
						if (get_option('PostEventSeeRegister') == "1")
						{
							$link = get_bloginfo('url') . "/wp-content/plugins/post-event2/script.php?action=create_csv&amp;post_id=" . $post_ID;
							$link = wp_nonce_url($link, 'create_csv_file');
							$disp .= "<li>" . __("See who are register", "PostEvent") . ": <a href='$link'>" . __("Download csv file", "PostEvent") . "</a></li></ul>";
						}
					}
				}
				$disp .= "</div>";
			}
			return $disp;
	    }
	}
}

/* 
** Format a date
*/
function get_date_format($date)
{
	$format_date = get_option('date_format');
	$date = mysql2date($format_date, $date, true);
	return $date;
}

/*
** Display the map for the current event
*/
function the_post_event_map($mapWidth, $mapHeight, $bubbleWidth, $bubbleHeight)
{
	echo get_post_event_map($mapWidth, $mapHeight, $bubbleWidth, $bubbleHeight);
}

/*
** Create the map for the current event
*/
function get_post_event_map($mapWidth, $mapHeight, $bubbleWidth, $bubbleHeight)
{
	$post_ID = get_the_id();
	$post_id_7 = get_post($post_ID); 
	$title = $post_id_7->post_title;	
	$EventFind = get_post_meta($post_ID, "_MyEvent");
	if(is_array($EventFind))
	{
		$EventFind =  unserialize($EventFind[0]);
		if(is_a($EventFind,MyEvent))
		{
			$GoogleKey = get_option("PostEventGoogleKey");
			if(isset($GoogleKey) AND !is_null($GoogleKey) AND !empty($GoogleKey))
			{
				$j = $post_ID;		
				$content .= "<div class=\"entry-localization\">
			                 <script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=" . $GoogleKey . "\" type=\"text/javascript\"></script>
			                 <script type=\"text/javascript\" src=\"http://www.google.com/jsapi?key=" . $GoogleKey . "\"></script>
			                 <script type=\"text/javascript\">
							 google.load(\"maps\", \"2\",{\"other_params\":\"sensor=true\"});
							 var map". $j ." = null;
							 var geocoder". $j ." = null;
			
							 function initialize()
							 {
								map". $j ." = new google.maps.Map2(document.getElementById(\"map$j\"));
			                    map". $j .".setCenter(new google.maps.LatLng(48.857775, 2.211407), 13);
			                    geocoder". $j ." = new GClientGeocoder();
			                    map". $j .".addControl(new GSmallMapControl());
			                    showAddress". $j ."(\"". $EventFind->getPlace() ."\");
							 }
			
							 function showAddress". $j ."(address)
							 {
								if (geocoder". $j .")
								{
									geocoder". $j .".getLatLng(
									address,
									function(point)
									{
										if (!point)
											alert(address + \" not found\");
										else
										{
											map".$j.".setCenter(point, 13);
											var marker = new GMarker(point);
											map".$j.".addOverlay(marker);
											marker.openInfoWindowHtml('<div style=\"width:" . $bubbleWidth . "px;height:" . $bubbleHeight . "px;\"><strong>" . $title . "</strong><br /><a href=\"http://maps.google.fr/maps?f=q&hl=fr&q='+address+'\" target=\"_blank\" style=\"color:blue;\">" . $EventFind->getPlace() . "</div>');
										}
									});
								}
							 }
							 google.setOnLoadCallback(initialize); 
							 </script>
							 <div id=\"map".$j."\" style=\"width:" . $mapWidth . "px;height:" . $mapHeight. "px;\"></div>
							 </div>";
				return $content;
			}
		}
	}
}

/*
** Get the event using the query_posts parameters
*/
function query_events($query = '')
{
	preg_match('#(&?)showposts=([0-9])#isU', $query, $match_begin);
	if (!empty($match_begin))
	{
		if (!empty($match_begin[2]))
			$nb = $match_begin[2];
		else
			$nb = $match_begin[1];
		$query = str_replace($match_begin[0], '', $query);
	}
	else
		$nb = 0;
	if (is_null($query) || $query == '')
		$query = 'showposts=-1';
	else
		$query = 'showposts=-1&' . $query;
	query_posts($query);
	$myevents = $GLOBALS['wp_query']->posts;
	$today = date('d/m/Y');
	$today = explode("/", $today);
	$limit = get_option('PostEventPast');
	if ($limit != '19700101')
    {
		$today = mktime(0, 0, 0, $today[1], $today[0], $today[2]);
		$limit = strtotime($limit, $today);
		$limit = date('Ymd', $limit);
    }
	foreach($myevents as $event)
	{
		$event_info = get_post_meta($event->ID, "_MyEvent");
		if(is_array($event_info))
		{
			$event_info =  unserialize($event_info[0]);
			if(is_a($event_info,MyEvent))
			{
				$event_start_date = explode("-", $event_info->getDateStart());
				$event_start_date = $event_start_date[0].$event_start_date[1].$event_start_date[2];
				$event_end_date = explode("-", $event_info->getDateEnd());
				$event_end_date = $event_end_date[0].$event_end_date[1].$event_end_date[2];
				if ($event_end_date == '00000000')
					$event_end_date = $event_start_date;
				if ($event_end_date >= $limit)
					$event_array[$event_start_date][] = $event;
			}
		}
    }
	if (count($event_array))
	{
		$order = get_option('PostEventOrderBy');
		if ($order == '0')
			krsort($event_array);
		else
			ksort($event_array);
		$all_event = array();
		foreach ($event_array as $content)
		{
			foreach($content as $contents)
				$all_event[] = $contents;
		}
		if($nb > 0)
		{
			$all_event = array_slice($all_event, 0, $nb);
			$GLOBALS['wp_query']->post_count = $nb;
		}
		else
			$GLOBALS['wp_query']->post_count = count($event_array);
		$GLOBALS['wp_query']->posts = $all_event;
		$GLOBALS['wp_query']->post = $all_event[0];
	}
	else
	{
		$GLOBALS['wp_query']->posts = NULL;
		$GLOBALS['wp_query']->post = NULL;
	}
	return ($all_event);
}

/*
** Create the ics directory
*/
function createIcsDirectory()
{
	if (is_dir(ABSPATH . "wp-content/blogs.dir"))
	{
		global $blog_id;
		$dir ='wp-content/blogs.dir/' . $blog_id;
	}
	else
		$dir = 'wp-content/uploads';
	$path = ABSPATH . $dir;
	$stat = stat(ABSPATH . 'wp-content');
	$dir_perms = $stat['mode'] & 0000777;
	if (!file_exists($path))
	{
		if (!@mkdir($path))
		{
			add_action('admin_notices', 'errorCreating');
			return ;
		}
		@chmod($path, $dir_perms);
	}
	$pathics = "$path/ics";
	if (!file_exists($pathics))
	{
		if (!@mkdir($pathics))
		{
			add_action('admin_notices', 'errorCreating');
			return ;
		}
		@chmod($pathics, $dir_perms);
	}
}

/*
** Display a message if ics directory creation failed
*/
function	errorCreating()
{
	echo "<div class='updated fade'><p><strong>Ics directory could not be created. Check wp-content and uploads directories's chmod and create directories in admin page.</strong></p></div>";
}

/*
** Get the event registers in an array
*/
function	get_register($post, $opt)
{
	$query = new myPostEventQuery();
	$to_return = array();
	$registers = $query->get_register($post, $opt);
	$i = 0;
	foreach($registers as $register)
	{
		if ($register->user_ID != 0)
		{
			$infos = get_userdata($register->user_ID);
			$to_return[$i] = array();
			$to_return[$i]['user'] = $infos->user_login;
			$to_return[$i]['mail'] = $infos->user_email;
			$to_return[$i]['date'] = $register->date;
			$to_return[$i]['guests'] = $register->guests;
		}
		else
		{
			$to_return[$i] = array();
			$to_return[$i]['user'] = "Anonyme";
			$to_return[$i]['mail'] = $register->e_mail;
			$to_return[$i]['date'] = $register->date;
			$to_return[$i]['guests'] = $register->guests;
		}
		$i++;
	}
	return ($to_return);
}

/*
** Convert french date formet to iso format
*/
function	convertToIso($date)
{
	preg_match('#^[0-9]{2}/[0-9]{2}/[0-9]{4}$#', $date, $match);
	if (!empty($match))
	{
		$date = explode('/', $date);
		$date = $date[2] . '-' . $date[1] . '-' . $date[0];
	}
	return ($date);
}

/*
** Get the date format to display in event box
*/
function get_date_formatPostEvent()
{
	$date_format = get_option('date_format');
	if ($date_format == 'Y/m/d' || $date_format == 'm/d/Y' || $date_format == 'F j, Y')
		return ('Y-m-dd');
	elseif ($date_format == 'd/m/Y' || $date_format == 'j F Y')
		return ('d/m/Y');
	else
		return ('Y-m-dd');
}

/*
** Display a message if php version < 5
*/
function	error_php_version()
{
	echo "<div class='error fade'><p><strong>PostEvent could not work with your php version. Minimum version requirement: php5.</strong></p></div>";
}

function	get_ical_calendar($cat = "")
{
	if ($cat != "")
	{
		$id = get_cat_id($cat);
		$tab_id = array();
		$tab_id[] = get_category($id);
		$file = $tab_id[0]->category_nicename . "_" . $id . "_.ics";
		if (!is_file(ABSPATH . "wp-content/uploads/ics/" . $tab_id[0]->category_nicename . "_" . $id . "_.ics"))
			get_post_event_all_event_ical_cat($tab_id);
		return (get_bloginfo('url') . "/wp-content/uploads/ics/" . $tab_id[0]->category_nicename . "_" . $id . "_.ics");
	}
	else
	{
		if (!is_file(ABSPATH . "wp-content/uploads/ics/all_events.ics"))
			get_post_event_all_event_ical();
		return (get_bloginfo('url') . "/wp-content/uploads/ics/all_events.ics");
	}
}

function	the_ical_calendar($cat = "")
{
	echo "<a href=" . get_ical_calendar($cat) . "><img src='" . get_bloginfo('url') . "/wp-content/plugins/post-event2/Pictures/ical.gif' alt='ical'/></a>";
}
?>
