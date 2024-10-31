<?php
/*
Plugin Name: Post Event
Plugin URI: http://codex.oxfoz.com/cat/post-event/
Description: Add event information (date, location...) to a post and export ics files. Know who is interested in your invents by allow them to subscribe to your events ! This plugin DOES NOT WORK with PHP4. Please install PHP5 version on your server to run Post Event2
Version: 3.0.1
Author: oXfoZ
Author URI: http://www.oxfoz.com/
*/

require_once('class/myPostEventQuery.php');
require_once('class/myEvent.php');
require_once('post-event-functions.php');

session_start();

class PostEvent
{
	private $domain = 'PostEvent';
	private $localisation = 'wp-content/plugins/post-event2/';
	private $root = '/post-event2';
	
	/*
	** Plugin class constructor
	*/
	public function	__construct()
	{
		add_action('activate_'.$_GET['plugin'].'', array(&$this, 'postEventInit'));
		load_plugin_textdomain($this->domain, $this->localisation, $this->root);
		add_action('admin_menu', array(&$this, 'postEventCustomBox'));
		add_action('save_post', array(&$this, 'postEventSave'));
		add_action('admin_menu', array(&$this, 'postEventMenu'));
		add_action('manage_posts_custom_column', array(&$this, 'custom_columns'), 10, 2);
		add_action('delete_category', array(&$this, 'remove_ics_files'), 5);
		add_action('parse_query', array(&$this, 'insertMyRewriteParseQuery'));
		add_filter('the_content', array(&$this, 'PostEventContent'));
		add_filter('manage_posts_columns', array(&$this, 'columns'));
		if (isset($_SESSION['PostEventError']) && $_SESSION['PostEventError'] != '')
			add_action('admin_notices', array(&$this, 'errorDisplaying'));
		if (get_option('PostEventGoogleKey') == '' || !get_option('PostEventGoogleKey'))
			add_action('admin_notices', array(&$this, 'noGoogleKey'));
	}
	
	/*
	** Display a message if no google key is given
	*/
	public function	noGoogleKey()
	{
		echo '<div class="updated"><p><strong>PostEvent alert:</strong> No google key is given. To use google map with your event, you have to register your key <a href="admin.php?page=PostEvent/mapSetting">here</a></p></div>';
	}
	
	/*
	** Display error in $_SESSION['PostEventError']
	*/
	public function	errorDisplaying()
	{
		echo '<div class="error"><p><strong>'.$_SESSION['PostEventError'].'</strong></p></div>';
		$_SESSION['PostEventError'] = '';
	}
	
	/*
	** Plugin init function
	*/
	public function postEventInit()
	{
		global	$wpdb;
		
		add_option('PostEventGoogleKey', '', '', 'no');
		add_option('PostEventGoogleMapHeight', '300', '', 'no');
		add_option('PostEventGoogleMapWidth', '500', '', 'no');
		add_option('PostEventGoogleBubbleHeight', '50', '', 'no');
		add_option('PostEventGoogleBubblewidth', '280', '', 'no');
		add_option('PostEventOrderBy', '0', '', 'no');
		add_option('PostEventJoinOpt', '0', '', 'no');
		add_option('PostEventPast', '-3 day', '', 'no');
		add_option('PostEventFieldDel', ',', '', 'no');
		add_option('PostEventTextDel', '\"', '', 'no');
		add_option('PostEventSeeRegister', '0', '', 'no');
		$table_name = $wpdb->prefix . "PostEventRegister";
		$table = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
				 `ID` int(11) NOT NULL auto_increment,
				 `post_ID` int(11) NOT NULL,
				 `e_mail` varchar(50) NOT NULL,
				 `guests` varchar(2) NOT NULL,
				 `user_ID` int(11) NOT NULL,
				 `date` datetime NOT NULL,
				 PRIMARY KEY  (`ID`)
				 ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
			dbDelta($table);
		createIcsDirectory();
	}
	
	/*
	** Rewrite rule for ics files
	*/
	public function	insertMyRewriteParseQuery($query)
	{
		$wud = wp_upload_dir();
		if ($query->query_vars['pagename'])
			$redir = explode("/", $query->query_vars['pagename']);
		elseif ($query->query_vars['category_name'])
			$redir = explode("/", $query->query_vars['category_name']);
		if ($redir[count($redir) - 1] == "ical")
		{
			if (count($redir) == 1)
				header("Location: " . $wud['baseurl'] . "/ics/all_events.ics");
			else
			{
				$myquery = new myPostEventQuery();
				$file_name = $redir[count($redir) - 2];
				$id = $myquery->get_slug_id($file_name);
				$file_name = $file_name . "_" . $id . "_.ics";
				$the_id = array();
				$the_id[] = get_category($id);
				get_post_event_all_event_ical_cat($the_id);
				header("Location: " . $wud['baseurl'] . "/ics/" . $file_name);
				exit();
			}
			exit();
		}
	}
	
	/*
	** Return post content with the related event
	*/
	public function PostEventContent($content)
	{
		return $content . get_post_event_html();
	}
	
	/*
	** Add a column in post list
	*/
	public function	columns($default)
	{
		$default['begin_date'] = __('Start event date', 'PostEvent');
		return ($default);
	}
	
	/*
	** Add content in the column created above
	*/
	public function	custom_columns($column, $id)
	{
		if ($column == 'begin_date')
		{
			$date = get_post_event_start_date();
			if ($date != NULL)
				echo get_date_format(__($date, 'PostEvent'));
			else
				echo '<em>No event</em>';
		}
	}
	
	/*
	** Adding new menu
	*/
	public function	postEventMenu()
	{
		add_menu_page('PostEvent: Main setting', 'PostEvent', 10, 'PostEvent/mainSetting', array(&$this, 'postEventMainSetting'), get_bloginfo('url') . '/' . $this->localisation . 'Pictures/icon.png');
		add_submenu_page('PostEvent/mainSetting', 'PostEvent: ' . __('Map setting', 'PostEvent'), __('Map setting', 'PostEvent'), 10, 'PostEvent/mapSetting', array(&$this, 'postEventMapSetting'));
		add_submenu_page('PostEvent/mainSetting', 'PostEvent: ' . __('Loop setting', 'PostEvent'), __('Loop setting', 'PostEvent'), 10, 'PostEvent/loopSetting', array(&$this, 'postEventLoopSetting'));
		add_submenu_page('PostEvent/mainSetting', 'PostEvent: ' . __('Ics files', 'PostEvent'), __('Ics files', 'PostEvent'), 10, 'PostEvent/icsFiles', array(&$this, 'postEventIcsSetting'));
		add_submenu_page('PostEvent/mainSetting', 'PostEvent: ' . __('Subscription setting', 'PostEvent'), __('Subscription setting', 'PostEvent'), 10, 'PostEvent/subSetting', array(&$this, 'postEventSubSetting'));
	}
	
	
	/*
	** Main option page
	*/
	public function	postEventMainSetting()
	{
		?>
		<link rel="stylesheet" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/admin.css" type="text/css"/>
		<div class="wrap">
			<div id="postevent-plugin-page">
				<div id="icon-index" class="icon32"><br /></div><h2>PostEvent</h2>
				<?php
				echo '<br/>';
				_e('Do often do you ask yourself how you could create events through Wordpress, organize job meetings, parties or holidays with your friends and contact them easily ?', 'PostEvent');
				echo '<br/>';
				echo '<br/>';
				_e('Then \'Post Event\' is THE event plugin for wordpress you are looking for!', 'PostEvent');
				echo '<br/>';
				echo '<br/>';
				_e('Post Event allows you to define event information (date, location, schedule...) directly in the post administration page.', 'PostEvent');
				echo '<br/>';
				_e('All event information is automatically displayed in the post page (single.php), including Google Maps AND iCal link (integration in your agenda) without any code modification!', 'PostEvent');
				echo '<br/>';
				echo '<br/>';
				_e('It is very usefull (Google Maps and iCal integration), high-performance and can be used for any event.', 'PostEvent');
				echo '<br/>';
				echo '<br/>';
				_e('We have based the development on the wordpress structure, without any modification of the database (event details are loaded as meta of the posts).', 'PostEvent');
				echo '<br/>';
				echo '<br/>';
				?>
				<div id="map-img" class="icon32"><br /></div><h2><a href="admin.php?page=PostEvent/mapSetting"><?php _e('Map setting', 'PostEvent'); ?></a></h2>
				<div id="icon-options-general" class="icon32"><br /></div><h2><a href="admin.php?page=PostEvent/loopSetting"><?php _e('Loop setting', 'PostEvent'); ?></a></h2>
				<div id="clook-img" class="icon32"><br /></div><h2><a href="admin.php?page=PostEvent/icsFiles"><?php _e('Ics files', 'PostEvent'); ?></a></h2>
				<div id="icon-users" class="icon32"><br /></div><h2><a href="admin.php?page=PostEvent/subSetting"><?php _e('Subscription setting', 'PostEvent'); ?></a></h2>
			</div>
			<?php
			oxfoz_plugin_presentation('PostEvent is brought to you for free by oXfoZ Technologies.', $this->domain, 'post-event/');
			?>
		</div>
		<?php
	}
	
	/*
	** Map setting page
	*/
	public function	postEventMapSetting()
	{
		?>
		<link rel="stylesheet" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/admin.css" type="text/css"/>
		<div class="wrap">
			<div id="postevent-plugin-page">
				<div id="map-img" class="icon32"><br /></div>
				<h2>PostEvent: <?php _e('Map setting', 'PostEvent'); ?></h2>
				<?php
				if (isset($_GET['updated']))
				{
					if ($_GET['updated'] == 'true')
						echo '<div class="updated fade"><p><strong>' . __('Map settings saved.', 'PostEvent') . '</strong></p></div>';
				}
				?>
				<br/>
				<?php
				_e('To use google maps, you have to own a google key, you can get one <a href="http://code.google.com/apis/maps/signup.html">here</a>', 'PostEvent');
				?>
				<form method="post" action="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>update-options.php">
					<table class="PostEventTable">
						<tr valign="top">
							<th scope="row"><?php _e('Enter your google Key:', 'PostEvent') ?></th>
							<td><input type="text" id="PostEventGoogleKey" name="PostEventGoogleKey" size="90" value="<?php echo stripslashes((get_option('PostEventGoogleKey'))); ?>"/></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Choose map height:', 'PostEvent'); ?></th>
							<td><input type="text" id="PostEventGoogleMapHeight" name="PostEventGoogleMapHeight" size="4" value="<?php echo get_option('PostEventGoogleMapHeight'); ?>"/> px</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Choose map width:', 'PostEvent'); ?></th>
							<td><input type="text" id="PostEventGoogleMapWidth" name="PostEventGoogleMapWidth" size="4" value="<?php echo get_option('PostEventGoogleMapWidth'); ?>"/> px</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Choose bubble height:', 'PostEvent'); ?></th>
							<td><input type="text" id="PostEventGoogleBubbleHeight" name="PostEventGoogleBubbleHeight" size="4" value="<?php echo get_option('PostEventGoogleBubbleHeight'); ?>"/> px</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Choose bubble width:', 'PostEvent'); ?></th>
							<td><input type="text" id="PostEventGoogleBubbleWidth" name="PostEventGoogleBubbleWidth" size="4" value="<?php echo get_option('PostEventGoogleBubbleWidth'); ?>"/> px</td>
						</tr>		
					</table>
					<?php wp_nonce_field('PostEventOptionUpdate-form'); ?>
					<input type="hidden" name="action" value="map"/>
					<div class="submit"><input type="submit" name="update" value="<?php _e('Update options', 'PostEvent')?> &raquo;"/></div>
				</form>
			</div>
			<?php
			oxfoz_plugin_presentation('PostEvent is brought to you for free by oXfoZ Technologies.', $this->domain, 'post-event/');
			?>
		</div>
		<?php
	}
	
	/*
	** Loop setting page
	*/
	public function	postEventLoopSetting()
	{
		?>
		<link rel="stylesheet" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/admin.css" type="text/css"/>
		<div class="wrap">
			<div id="postevent-plugin-page">
				<div id="icon-options-general" class="icon32"><br /></div>
				<h2>PostEvent: <?php _e('Loop settings', 'PostEvent'); ?></h2>
				<?php
				if (isset($_GET['updated']))
				{
					if ($_GET['updated'] == 'true')
						echo '<div class="updated fade"><p><strong>' . __('Loop settings saved.', 'PostEvent') . '</strong></p></div>';
				}
				echo '<br/>';
				_e('PostEvent creates the function \'query_events\' which is used in the theme.', 'PostEvent');
				echo '<br/>';
				_e('It does the same as \'query_posts\' but sorts posts by the event\'s start date (Desc as default).', 'PostEvent');
				echo '<br/>';
				_e('To sort in asc order, the box must be checked.', 'PostEvent');
				?>
				<form method="post" action="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>update-options.php">
					<table class="PostEventTable">
						<tr valign="top">
							<th scope="row"><?php _e('Order event by asc:', 'PostEvent'); ?></th>
							<td><input type="checkbox" name="PostEventOrderBy" value="1" <?php if (get_option('PostEventOrderBy') == '1'){ ?>checked="checked" <?php } ?>/></td>
						</tr>
					</table>
					<br/>
					<?php _e('You can choose how many past events to display (Past 3 days as default).', 'PostEvent'); ?>
					<table class="PostEventTable">
						<tr valign="top">
							<th scope="row"><?php _e('Display past events since:', 'PostEvent'); ?></th>
							<td><select name="PostEventPast">
								<option value="-0 day" <?php if (get_option('PostEventPast') == "-0 day"){ ?>selected="selected" <?php } ?>><?php _e('Today', 'PostEvent'); ?></option>
								<option value="-3 day" <?php if (get_option('PostEventPast') == "-3 day"){ ?>selected="selected" <?php } ?>>3 <?php _e('days', 'PostEvent'); ?></option>
								<option value="-8 day" <?php if (get_option('PostEventPast') == "-8 day"){ ?>selected="selected" <?php } ?>>8 <?php _e('days', 'PostEvent'); ?></option>
								<option value="-30 day" <?php if (get_option('PostEventPast') == "-30 day"){ ?>selected="selected" <?php } ?>>30 <?php _e('days', 'PostEvent'); ?></option>
								<option value="19700101" <?php if (get_option('PostEventPast') == "19700101"){ ?>selected="selected" <?php } ?>><?php _e('all', 'PostEvent'); ?></option>
								</select></td>
						</tr>
					</table>
					<?php wp_nonce_field('PostEventOptionUpdate-form'); ?>
					<input type="hidden" name="action" value="loop"/>
					<div class="submit"><input type="submit" name="update" value="<?php _e('Update options', 'PostEvent')?> &raquo;"/></div>
				</form>
			</div>
			<?php
			oxfoz_plugin_presentation('PostEvent is brought to you for free by oXfoZ Technologies.', $this->domain, 'post-event/');
			?>
		</div>
		<?php
	}
	
	/*
	** Ics files page
	*/
	public function	postEventIcsSetting()
	{
		?>
		<link rel="stylesheet" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/admin.css" type="text/css"/>
		<div class="wrap">
			<div id="postevent-plugin-page">
				<div id="clook-img" class="icon32"><br /></div>
				<h2>PostEvent: <?php _e('Ics files', 'PostEvent'); ?></h2>
				<?php
				if (isset($_GET['updated']))
				{
					if ($_GET['updated'] == 'true')
						echo '<div class="updated fade"><p><strong>' . __('Directory created successfully.', 'PostEvent') . '</strong></p></div>';
					else
						echo '<div class="error fade"><p><strong>' . __('Directory creation failed', 'PostEvent') . ' : ' . htmlentities($_GET['msg']) . '</strong></p></div>';
				}
				?>
				<br/>
				<?php
				_e('When you save a post, ics files are generated, corresponding to each category.', 'PostEvent');
				echo '<br/>';
				_e('Here is the list of all your ics files.', 'PostEvent');
				?>
				<br/>
				<table class="PostEventTable">
				<?php
				if (is_dir(ABSPATH . "wp-content/blogs.dir"))
				{
					global $blog_id;
					$dir = 'wp-content/blogs.dir/' . $blog_id;
				}
				else
					$dir = 'wp-content/uploads';
				if (is_dir(ABSPATH . $dir . '/ics'))
				{
					$direct = opendir(ABSPATH . $dir. "/ics");
					$i = 0;
					while (($read = readdir($direct)))
					{
						$name = explode(".", $read);
						$first = $read[0];	
						if ($name[count($name) - 1] == "ics")
						{
							if (preg_match("/[^.]/", $first) == 1)
							{
								echo '<tr>';
									echo "<td><a href=\"" . get_bloginfo('url') . '/' . $dir . "/ics/" . $read ."\">" . $read . "</a><br /><br /></td>";
								echo '</tr>';
							}
							$i++;
						}
					}
					closedir($direct);
					if ($i == 0)
						echo '<em><h3>' . __('No ics files', 'PostEvent') . '</h3></em>';
				}
				else
				{
					_e('Ics directory did not exist.', 'PostEvent');
					echo ' ';
					_e('Click the button to create it.', 'PostEvent');
					?>
					<form method="post" action="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>update-options.php">
					<?php
					wp_nonce_field('PostEventOptionUpdate-form');
					?>
					: <input type="submit" name="create" value="<?php _e('Create directory', 'PostEvent'); ?>"/>
					<input type="hidden" name="action" value="ics-dir"/>
					</form>
					<?php
				}
				?>
				</table>
			</div>
			<?php
			oxfoz_plugin_presentation('PostEvent is brought to you for free by oXfoZ Technologies.', $this->domain, 'post-event/');
			?>
		</div>
		<?php
	}
	
	/*
	** Subscription setting page
	*/
	public function	postEventSubSetting()
	{
		?>
		<link rel="stylesheet" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/admin.css" type="text/css"/>
		<div class="wrap">
			<div id="postevent-plugin-page">
				<div id="icon-users" class="icon32"><br /></div>
				<h2>PostEvent: <?php _e('Subscription setting', 'PostEvent'); ?></h2>
				<?php
				if (isset($_GET['updated']))
				{
					if ($_GET['updated'] == 'true')
						echo '<div class="updated fade"><p><strong>' . __('Subscription settings saved.', 'PostEvent') . '</strong></p></div>';
				}
				echo '<br/>';
				_e('You can allow visitors to join events. You have to activate the subscription option in this page, then you can manage each post event subscription in the post edit page', 'PostEvent');
				?>
				<form method="post" action="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>update-options.php">
					<table class="PostEventTable">
						<tr valign="top">
							<th scope="row"><?php _e('Activate users registration', 'PostEvent'); ?>:</th><td><input type="checkbox" value="1" name="PostEventActivateRegistration" <?php if (get_option('PostEventActivateRegistration')) echo "checked='checked'"; ?>/></td>
						</tr>
					</table>
					<?php
					$activate = get_option('PostEventActivateRegistration');
					if ($activate == '1')
					{
						?>
						<table class="PostEventTable">
							<tr valign="top">
								<th scope="row"><?php _e('Only registered users can join events', 'PostEvent'); ?>:</th><td><input type="checkbox" value="1" name="PostEventJoinOpt" <?php if (get_option("PostEventJoinOpt") == '1') echo "checked='checked'"; ?>/> <em><?php _e('If you check this box, you have to allow visitors to register into you blog', 'PostEvent'); ?></em></td>
							</tr>
							<tr valign="top">
							  <th scope="row"><?php _e('Allow users to see who is registered', 'PostEvent'); ?>:</th><td><input type="checkbox" value="1" name="PostEventSeeRegister" <?php if (get_option("PostEventSeeRegister") == '1') echo "checked='checked'"; ?>/></td>
							</tr>
							<tr valign="top">
							  <th scope="row"><?php _e('Allow users to see the number of registered users to an event', 'PostEvent'); ?>:</th><td><input type="checkbox" value="1" name="PostEventSeeRegisterNb" <?php if (get_option("PostEventSeeRegisterNb") == '1') echo "checked='checked'"; ?>/></td>
							</tr>
						</table>
						<br/>
						<table class="PostEventTable">
							<?php _e('You can save the list of registered users as a csv file. Below you can choose the parameter for your csv files', 'PostEvent'); ?>
							<tr valign="top">
								<th scope="row"><?php _e('Fields separator', 'PostEvent'); ?>: </th><td><select name="PostEventFieldDel">
																										<option value="," <?php if (get_option('PostEventFieldDel') == ",") echo "selected='selected'"; ?>>,</option>
																										<option value=";" <?php if (get_option('PostEventFieldDel') == ";") echo "selected='selected'"; ?>>;</option>
																										<option value=":" <?php if (get_option('PostEventFieldDel') == ":") echo "selected='selected'"; ?>>:</option>
																										<option value="{tab}" <?php if (get_option('PostEventFieldDel') == "{tab}") echo "selected='selected'"; ?>>{tab}</option>
																										<option value="{space}" <?php if (get_option('PostEventFieldDel') == "{space}") echo "selected='selected'"; ?>>{space}</option>
																										</select></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Text separator', 'PostEvent'); ?>: </th><td><select name="PostEventTextDel">
																									  <option value='"' <?php if (get_option('PostEventTextDel') == '\"') echo "selected='selected'"; ?>>"</option>
																									  <option value="'" <?php if (get_option('PostEventTextDel') == "\'") echo "selected='selected'"; ?>>'</option>
																									  </select></td>
							</tr>
						</table>
						<input type="hidden" name="activate" value=""/>
						<?php
					}
					wp_nonce_field('PostEventOptionUpdate-form'); ?>
					<input type="hidden" name="action" value="sub"/>
					<div class="submit"><input type="submit" name="update" value="<?php _e('Update options', 'PostEvent')?> &raquo;"/></div>
				</form>
			</div>
			<?php
			oxfoz_plugin_presentation('PostEvent is brought to you for free by oXfoZ Technologies.', $this->domain, 'post-event/');
			?>
		</div>
		<?php
	}
	
	/*
	** Add meta box in post admin page
	*/
	public function postEventCustomBox()
	{
		add_meta_box('PostEventSectionId', __('Event', $this->domain), array(&$this, 'postEventInnerCustomBox'), 'post', 'side', 'core');
		if (get_option('PostEventActivateRegistration') == '1')
			add_meta_box('PostEventRegister', __('Event subscription', $this->domain), array(&$this, 'postEventRegisterBox'), 'post', 'normal', 'core');
	}
	
	/*
	** Display the event subscriber list
	*/
	public function	postEventRegisterBox($post)
	{
		$MyEventMeta = get_post_meta($post->ID, "_MyEvent");
		$sub = 0;
		if(is_array($MyEventMeta))
		{
			$MyEvent = unserialize($MyEventMeta[0]);
			if ($MyEvent && $MyEvent->getSubscribe() == "1")
				$sub = 1;
		}
		if ($sub == "1")
		{
			$register = get_register($post->ID, "LIMIT 0,20");
			$link =  get_bloginfo('url') . "/wp-content/plugins/post-event2/script.php?action=create_csv&amp;post_id=" . $post->ID;
			$link = wp_nonce_url($link, 'create_csv_file');
			?>
			<h4><?php _e('Only the twenty last users are displayed, to see all users download the csv file:', 'PostEvent'); ?> <a href="<?php echo $link; ?>"><?php _e('here', 'PostEvent'); ?></a></h4>
			<table style="width: 90%; text-align: left; border: 1px solid #000000; border-collapse: collapse; margin: auto; margin-top: 5px;">
				<tr style="height: 20px;">
					<th style="width: 30%; border: 1px solid grey"><?php _e('Mail', 'PostEvent'); ?></th>
					<th style="width: 20%; border: 1px solid grey"><?php _e('Users', 'PostEvent'); ?></th>
					<th style="width: 20%; border: 1px solid grey"><?php _e('Guests', 'PostEvent'); ?></th>
					<th style="border: 1px solid grey;">Date</th>
				</tr>
				<?php
				foreach ($register as $user)
				{
					echo "<tr style='border: 1px solid grey; padding: 3px;'><td style='border: 1px solid grey; padding: 3px;'>" . $user['mail'] . "</td><td style='border: 1px solid grey; padding: 3px;'>" . $user['user'] . "</td><td style='border: 1px solid grey; padding: 3px;'>" . $user['guests'] . "</td><td style='border: 1px solid grey; padding: 3px;'>" . get_date_format($user['date']) . "</td></tr>";
				}
				?>
			</table>
			<?php
		}
		else
			echo "<h4><em>" . __('Subscription are not allowed for this event.', 'PostEvent') . "</em></h4>";
	}

	/*
	** Displaying box on the right of post editing page
	*/
	public function	postEventInnerCustomBox($post)
	{
		$_SESSION['PostEventError'] = '';
		$myEventMeta = get_post_meta($post->ID, "_MyEvent");
		$flag = 0;
		if(!empty($myEventMeta))
		{
			$myEvent = unserialize($myEventMeta[0]);
			if ($myEvent)
				$flag = 1;
		}
		if ($flag == 0)
		{
			$myEvent = new MyEvent($post->ID, "", "");
			$myEvent->setDateEnd("");
			$myEvent->setTimeStart("");
			$myEvent->setTimeEnd("");
			$myEvent->setGuests("0");
			$myEvent->setParts("0");	
		}
		wp_enqueue_script('jquery');
		?>
		<script type="text/javascript" src="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>Js/dhtmlgoodies_calendar.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/dhtmlgoodies_calendar.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>css/admin.css"/>
		<script type="text/javascript">
		function change_date_value(id)
		{
			document.getElementById('EventDateEnd').value = id.value;
		}
		</script>
		<?php
	    $begin_date = explode('-', $myEvent->getDateStart());
		$end_date = explode('-', $myEvent->getDateEnd());
		$begin_date = $begin_date[0] . $begin_date[1] . $begin_date[2];
		$end_date = $end_date[0] . $end_date[1] . $end_date[2];
		if ($end_date !=0 && ($end_date < $begin_date))
			echo '<span style="color:red;"><em>'.__('Warning: End date more recent than start date.', 'PostEvent').'</em></span><br/><br/>';
		?>
		<table class="PostEventBoxTable">
			<tr>
				<td><?php _e('Start date', $this->domain); ?> :</td>
				<td><input type="text" name="EventDateStart" id="EventDateStart" onChange="change_date_value(this);" size="10" class="date-pick" readonly="readonly" value="<?php echo mysql2date(get_date_formatPostEvent(), $myEvent->getDateStart());?>"/></td>
				<td><img src="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>Pictures/icon.png" id="date_debut_img" name="date_debut_img" style="cursor: pointer;" onclick="displayCalendar(document.getElementsByName('EventDateStart')[0],'dd/mm/yyyy',this)" title="Selecteur de date"></td>
				<td><select name="EventTimeStart" id="EventTimeStart" onchange="document.getElementById('EventTimeEnd').value = this.value;">	
						<option value=""></option>
						<?php
							echo getTimeOption($myEvent->getTimeStart());
						?>
					</select></td>
			</tr>
			<tr>
				<td><?php _e('End date', $this->domain); ?> :</td>
				<td><input type="text" name="EventDateEnd" id="EventDateEnd" size="10" class="date-pick" readonly="readonly" value="<?php echo mysql2date(get_date_formatPostEvent(), $myEvent->getDateEnd()); ?>"/></td>
				<td><img src="<?php echo get_bloginfo('url') . '/' . $this->localisation; ?>Pictures/icon.png" id="date_debut_img" name="date_debut_img" style="cursor: pointer;" onclick="displayCalendar(document.getElementsByName('EventDateEnd')[0],'dd/mm/yyyy',this)" title="Selecteur de date"></td>
				<td><select name="EventTimeEnd" id="EventTimeEnd">	
						<option value=""></option>
						<?php
							echo getTimeOption($myEvent->getTimeEnd());
						?>
					</select></td>
			</tr>
			<tr>
				<td><?php _e("Location:", $this->domain);?></td>
				<td colspan="3"><input type="text" onBlur="reload_map()" name="EventPlace" id="EventPlace" class="location-pick" size="25" value="<?php echo str_replace("~39", "'", $myEvent->getPlace());?>"/></td>
			</tr>
		</table>
		<?php
		if (get_option('PostEventGoogleKey') == NULL)
			echo '<span style="color:red;"><em>'.__('Warning: No google key, no map would be displayed.').'</em></span><br/><br/>';
		else
		{
			?>
			<div id="entry" class="entry-localization" style="display: none;">
				<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_option('PostEventGoogleKey'); ?>" type="text/javascript"></script>
				<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo get_option('PostEventGoogleKey'); ?>"></script>
				<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . $this->root .'/Js/google_map.js'; ?>" ></script>
				<div id="map" style="overflow:hidden;width:270px;height:180px;"></div>
			</div>
			<?php
		}
		?>
		<?php
		$activate = get_option('PostEventActivateRegistration');
		if ($activate == '1')
		{
			?>
			<script type="text/javascript">
			function	check_checkbox(elem)
			{
				if (elem.checked == true)
				{
					document.getElementById('max_guests').style.display = 'table-row';
					document.getElementById('max_parts').style.display = 'table-row';
				}
				else
				{
					document.getElementById('max_guests').style.display = 'none';
					document.getElementById('max_parts').style.display = 'none';
				}
			}
			</script>
			<table class="PostEventBoxTable">
				<tr>
					<td><?php _e('Allow subscription', 'PostEvent'); ?> :</td>
					<td><input type="checkbox" name="subscription" onclick="check_checkbox(this);" value="subscribe" <?php if ($myEvent->getSubscribe() == '1') {?> checked="checked" <?php } ?>/></td>
				</tr>
				<tr id="max_parts" <?php if ($myEvent->getSubscribe() == '0' || !$myEvent->getSubscribe()) echo 'style="display: none;"'; ?>>
					<td><?php _e('Total number of participants', 'PostEvent'); ?> :</td>
					<td><input type="text" name="nb_parts" size="2" value="<?php echo $myEvent->getParts(); ?>" /></td>
				</tr>
				<tr id="max_guests" <?php if ($myEvent->getSubscribe() == '0' || !$myEvent->getSubscribe()) echo 'style="display: none;"'; ?>>
					<td><?php _e('Number of guests each participant is allowed to bring', 'PostEvent'); ?>:</td>
					<td><select name="nb_guests">
							<option <?php if ($myEvent->getGuests() == "0") echo "selected='selected'"; ?> value="0">0</option>
							<option <?php if ($myEvent->getGuests() == "1") echo "selected='selected'"; ?> value="1">1</option>
							<option <?php if ($myEvent->getGuests() == "2") echo "selected='selected'"; ?> value="2">2</option>
							<option <?php if ($myEvent->getGuests() == "3") echo "selected='selected'"; ?> value="3">3</option>
							<option <?php if ($myEvent->getGuests() == "4") echo "selected='selected'"; ?> value="4">4</option>
							<option <?php if ($myEvent->getGuests() == "5") echo "selected='selected'"; ?> value="5">5</option>
							<option <?php if ($myEvent->getGuests() == "6") echo "selected='selected'"; ?> value="6">6</option>
							<option <?php if ($myEvent->getGuests() == "7") echo "selected='selected'"; ?> value="7">7</option>
							<option <?php if ($myEvent->getGuests() == "8") echo "selected='selected'"; ?> value="8">8</option>
							<option <?php if ($myEvent->getGuests() == "9") echo "selected='selected'"; ?> value="9">9</option>
						</select></td>
				</tr>
			</table>
			<?php
		}
		if ($myEvent->getDateStart() != '')
		{
			?>
			<br/>
			<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . $this->root . '/Js/remove_event.js'; ?>" ></script>
			<input type="button" value="<?php _e('Delete event informations', 'PostEvent'); ?>" name="delete" onClick="remove_event('<?php echo $post->ID; ?>', '<?php _e('Really delete event ?', 'PostEvent'); ?>');"/>
			<?php
		}
	}
	
	/*
	** Save event when saving post
	*/
	public function	postEventSave($post_ID)
	{
		global	$wpdb;
		
		if ($_POST['action'] != 'autosave')
		{
			if (($realID = wp_is_post_revision($post_ID)))
				$post_ID = $realID;
			$_SESSION['PostEventError'] = '';
			if(!empty($_POST["EventDateStart"]) AND !empty($_POST["EventPlace"]))
			{
				$MyEvent = new MyEvent($post_ID, convertToIso($_POST["EventDateStart"]), $_POST["EventPlace"]);
				
				if(!empty($_POST["EventDateEnd"]))
					$MyEvent->setDateEnd(convertToIso($_POST["EventDateEnd"]));
				else
					$MyEvent->setDateEnd(convertToIso($_POST["EventDateStart"]));
				if(!empty($_POST["EventTimeStart"]))
					$MyEvent->setTimeStart($_POST["EventTimeStart"]);
				if(!empty($_POST["EventTimeEnd"]))
					$MyEvent->setTimeEnd($_POST["EventTimeEnd"]);
				else
					$MyEvent->setTimeEnd($_POST["EventTimeStart"]);
				if(!empty($_POST["subscription"]))
					$MyEvent->setSubscribe('1');
				else
					$MyEvent->setSubscribe('0');
				if(!empty($_POST["nb_guests"]))
					$MyEvent->setGuests($_POST["nb_guests"]);
				if(!empty($_POST["nb_parts"]))
					$MyEvent->setParts(intval($_POST["nb_parts"]));
				if(!update_post_meta($post_ID,"_MyEvent",serialize($MyEvent)))
					add_post_meta($post_ID, "_MyEvent", serialize($MyEvent), true);
				$group = array();
				$categories = get_the_category($post_ID);
				get_post_event_all_event_ical_cat($categories);
				get_post_event_all_event_ical();
			}
			else
			{
				if (!empty($_POST["EventDateEnd"]) || !empty($_POST["EventTimeStart"]) || !empty($_POST["EventTimeEnd"]) || !empty($_POST["EventPlace"]) || !empty($_POST["EventDateStart"]))
					$_SESSION['PostEventError'] = 'Couldn\'t save event. Start date and place have to be given.';
			}
		}
	}
	
	/*
	** Remove ics file when removing category
	*/
	public function	remove_ics_files($cat_id)
	{
		if (is_dir(ABSPATH . "wp-content/blogs.dir"))
		{
			global $blog_id;
			$dir = ABSPATH . 'wp-content/blogs.dir/' . $blog_id . '/ics/';
		}
		else
			$dir = ABSPATH . "wp-content/uploads/ics/";
		$dir = opendir($dir);
		while (($read = readdir($dir)))
		{
			if (strpos($read, "_" . $cat_id . "_") !== false)
				unlink("../wp-content/plugins/post-event2/ics_files/$read");
		}
		closedir($dir);
	}
}

//Plugin Launch
if(class_exists('PostEvent')) {
	$php_version = phpversion();
	$php_version = explode('.', $php_version);
	if ($php_version[0] >= 5)
		$PostEvent = new PostEvent();
	else
		add_action('admin_notices', 'error_php_version');
}

?>
