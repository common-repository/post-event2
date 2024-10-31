<?php
require_once('../../../wp-config.php');
if (isset($_POST['action']) || isset($_GET['action']))
{
	global	$current_user;
	
	if ($_POST['action'] == 'remove_event')
		delete_post_meta($_POST['post_id'], "_MyEvent");
	if ($_POST['action'] == 'join_user')
	{
		check_admin_referer('PostEvent-joined-form');
		$query = new myPostEventQuery();
		if ($_POST['guests'] == 'undefined')
			$_POST['guests'] = 0;
		if ($current_user->ID)
			$mess = $query->load_user($_POST['post_id'], $_POST['guests']);
		else
			$mess = $query->load_not_user($_POST['post_id'], $_POST['user'], $_POST['guests']);
		echo $mess;
	}
	if ($_POST['action'] == 'unsub_user')
	{
		check_admin_referer('PostEvent-joined-form');
		$query = new myPostEventQuery();
		if ($current_user->ID)
			$mess = $query->unsub_user($_POST['post_id']);
		else
			$mess = $query->unsub_not_user($_POST['post_id'], $_POST['user']);
		echo $mess;
	}
	if ($_GET['action'] == 'create_csv')
	{
		check_admin_referer('create_csv_file');
		if (!empty($_GET['post_id']))
		{
			$field = get_option('PostEventFieldDel');
			if ($field == "{tab}")
				$field = "	";
			if ($field == "{space}")
				$field = " ";
			$text = get_option('PostEventTextDel');
			$csv = $text . "Mail" . $text . $field . $text . "Users" . $text . $field . $text . "Date" . $text . "\n";
			$register = get_register($_GET['post_id'], "");
			foreach ($register as $user)
			{
				$csv .= $text . $user['mail'] . $text . $field . $text . $user['user'] . $text . $field . $text . $user['date'] . $text . "\n";
			}
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: attachement; filename=" . str_replace(" ", "_", get_the_title($_GET['post_id'])) . ".csv");
			echo stripslashes($csv);
		}
	}
	if ($_GET['action'] == 'create_ics')
	{
		if (isset($_GET['post_id']))
		{
			$post = $_GET['post_id'];
			$post = get_post($post);
			$all_event = "BEGIN:VCALENDAR\n";
			$all_event .= "VERSION:2.0\n";
			
			$all_event .= "PRODID:" . get_bloginfo('url') . "\n";
			$events = get_post_meta($post->ID, '_MyEvent');
			$the_event = unserialize($events[0]);
			$is_save = array();
			if (is_a($the_event,MyEvent))
			{
				if (!in_array($post->post_title, $is_save))
						$all_event .= create_event($the_event, $post, $is_save);
					$is_save[] = $post->post_title;
			}
			$all_event .= "END:VCALENDAR\n";
			header("Content-Type: text/x-vCalendar");
			header("Content-Disposition: inline; filename=myIcal.ics");
			echo $all_event;
		}
	}
}
?>
