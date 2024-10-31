<?php

class	myPostEventQuery
{
	function		load_user($post_id, $guests)
	{
		global			$wpdb;
		global			$current_user;

		$user = $wpdb->get_results("SELECT COUNT(*) AS nb_user FROM " . $wpdb->prefix . "PostEventRegister WHERE user_ID='" . $current_user->ID . "' AND post_ID='" . $post_id . "'");
		if ($user[0]->nb_user == 0)
		{
			$protect = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "PostEventRegister VALUES('', %d, '', %s, %d, NOW())", $post_id, $guests, $current_user->ID);
			$wpdb->query($protect);
			$mess = __("You have join the event", "PostEvent") . " \"" . get_the_title($post_id) . "\".";
		}
		else
			$mess = "1";
		return ($mess);
	}
	
	function		load_not_user($post_id, $user_id, $guests)
	{
		global			$wpdb;
		
		$user = $wpdb->get_results("SELECT COUNT(*) AS nb_user FROM " . $wpdb->prefix . "PostEventRegister WHERE e_mail='" . $user_id . "' AND post_ID='" . $post_id . "'");
		if ($user[0]->nb_user == 0)
		{
			$protect = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "PostEventRegister VALUES('', %d, '%s', '%s', '0', NOW())", $post_id, $user_id, $guests);
			$wpdb->query($protect);
			$mess = __("You have join the event", "PostEvent") . " \"" . get_the_title($post_id) . "\".";
		}
		else
			$mess = "1";
		return ($mess);
	}
	
	function		unsub_user($post_id)
	{
		global			$wpdb;
		global			$current_user;

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "PostEventRegister WHERE post_ID=" . $post_id . " AND user_ID=" . $current_user->ID);
		$mess = __("You have left the event", "PostEvent") . " \"" . get_the_title($post_id) . "\".";
		return ($mess);
	}
	
	function		unsub_not_user($post_id, $user_id)
	{
		global			$wpdb;
		
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "PostEventRegister WHERE post_ID=" . $post_id . " AND e_mail='" . $user_id . "'");
		$mess = __("You have left the event", "PostEvent") . " \"" . get_the_title($post_id) . "\".";
		return ($mess);
	}
	
	function		get_register($post, $opt)
	{
		global			$wpdb;
		
		return ($wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "PostEventRegister WHERE post_ID='" . $post . "' ORDER BY date DESC $opt"));
	}
	
	function		get_nbpart($post, $opt)
	{
		global		$wpdb;
		
		return ($wpdb->get_results("SELECT SUM(guests) AS parts, COUNT(*) AS nb_guests FROM " . $wpdb->prefix . "PostEventRegister WHERE post_ID='" . $post . "' ORDER BY date DESC $opt"));
	}
	

	function		get_slug_id($slug)
	{
		global			$wpdb;
		
		$id = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "terms WHERE slug='" . $slug . "'");
		return ($id[0]->term_id);
	}
}

?>
