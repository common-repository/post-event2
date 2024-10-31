function	remove_event(post_id, mess)
	{
		if (jQuery('#EventDateStart').val())
		{
			if (confirm(mess))
			{
				jQuery(document).ready(function() {
				jQuery.post("../wp-content/plugins/post-event2/script.php", {post_id: post_id, action: 'remove_event'}, function success(data){ });
				});
				jQuery('#EventDateStart').val("");
				jQuery('#EventDateEnd').val("");
				jQuery('#EventTimeStart').val("");
				jQuery('#EventTimeEnd').val("");
				jQuery('#EventPlace').val("");
				jQuery('#map').css('display', 'none');
			}
		}
		return false;
	}