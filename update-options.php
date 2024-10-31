<?php
require_once('../../../wp-config.php');
check_admin_referer('PostEventOptionUpdate-form');
if (isset($_POST['action']))
{
	if ($_POST['action'] == 'map')
	{
		$mapHeight = $_POST['PostEventGoogleMapHeight'];
		$mapWidth = $_POST['PostEventGoogleMapWidth'];
		$bubbleHeight = $_POST['PostEventGoogleBubbleHeight'];
		$bubbleWidth = $_POST['PostEventGoogleBubbleWidth'];
		if (!$mapHeight || $mapHeight == '')
			$mapHeight = '300';
		if (!$mapWidth || $mapWidth == '')
			$mapWidth = '500';
		if (!$bubbleHeight || $bubbleHeight == '')
			$bubbleHeight = '50';
		if (!$bubbleWidth || $bubbleWidth == '')
			$bubbleWidth = '280';
		update_option('PostEventGoogleKey', $_POST['PostEventGoogleKey']);
		update_option('PostEventGoogleMapHeight', $mapHeight);
		update_option('PostEventGoogleMapWidth', $mapWidth);
		update_option('PostEventGoogleBubbleHeight', $bubbleHeight);
		update_option('PostEventGoogleBubblewidth', $bubbleWidth);
		$link = get_bloginfo('url') . '/wp-admin/admin.php?page=PostEvent/mapSetting&updated=true';
	}
	if ($_POST['action'] == 'loop')
	{
		$orderBy = $_POST['PostEventOrderBy'];
		if (!$orderBy || $orderBy == '')
			$orderBy = '0';
		update_option('PostEventOrderBy', $orderBy);
		update_option('PostEventPast', $_POST['PostEventPast']);
		$link = get_bloginfo('url') . '/wp-admin/admin.php?page=PostEvent/loopSetting&updated=true';
	}
	if ($_POST['action'] == 'sub')
	{
		$activateSub = $_POST['PostEventActivateRegistration'];
		if (!$activateSub || $activateSub == '')
			$activateSub = '0';
		update_option('PostEventActivateRegistration', $activateSub);
		if ($activateSub == '1' && isset($_POST['activate']))
		{
			$joinOpt = $_POST['PostEventJoinOpt'];
			$seeRegister = $_POST['PostEventSeeRegister'];
			$registerNb = $_POST['PostEventSeeRegisterNb'];
			$fieldDel = $_POST['PostEventFieldDel'];
			$textDel = $_POST['PostEventTextDel'];
			if (!$joinOpt || $joinOpt == '')
				$joinOpt = '0';
			if (!$seeRegister || $seeRegister == '')
				$seeRegister = '0';
			if (!$registerNb || $registerNb == '')
				$registerNb = '0';
			if (!$fieldDel || $fieldDel == '')
				$fieldDel = ',';
			if (!$textDel || $textDel == '')
				$textDel = '\"';
			update_option('PostEventJoinOpt', $joinOpt);
			update_option('PostEventSeeRegister', $seeRegister);
			update_option('PostEventSeeRegisterNb', $registerNb);
			update_option('PostEventFieldDel', $fieldDel);
			update_option('PostEventTextDel', $textDel);
		}
		$link = get_bloginfo('url') . '/wp-admin/admin.php?page=PostEvent/subSetting&updated=true';
	}
	if ($_POST['action'] == 'ics-dir')
	{
		$link = get_bloginfo('url') . '/wp-admin/admin.php?page=PostEvent/icsFiles&updated=true';
		if (is_dir(ABSPATH . "wp-content/blogs.dir"))
		{
			global $blog_id;
			$dir ='wp-content/blogs.dir/' . $blog_id;
			$theContainer = 'blogs.dir';
		}
		else
		{
			$dir = 'wp-content/uploads';
			$theContainer = 'wp-content';
		}
		$path = ABSPATH . $dir;
		$stat = stat(ABSPATH . 'wp-content');
		$dir_perms = $stat['mode'] & 0000777;
		if (!file_exists($path))
		{
			if (!@mkdir($path))
			{
				$link = get_bloginfo('url') . '/wp-admin/admin.php?page=PostEvent/icsFiles&updated=false&msg='.ABSPATH . $theContainer . ' is not writable. Chmod must be 777';
				header('Location: '.$link.'');
			}
			@chmod($path, $dir_perms);
		}
		$pathics = "$path/ics";
		if (!file_exists($pathics))
		{
			if (!@mkdir($pathics))
			{
				$link = get_bloginfo('url') . '/wp-admin/admin.php?page=PostEvent/icsFiles&updated=false&msg='. $path . ' is not writable. Chmod must be 777';
				header('Location: '.$link.'');
			}
			@chmod($pathics, $dir_perms);
		}
	}
	header('Location: '.$link.'');
}
?>