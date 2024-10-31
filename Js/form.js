function	joined_event(id, url)
{	
	document.getElementById(id).style.visibility = "visible";
	if (document.getElementById('mail-' + id))
	{
		var email = document.getElementById('user-' + id).value;
		var verif = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]{2,}[.][a-zA-Z]{2,3}$/
		if (verif.exec(email) == null)
		{
			document.getElementById(id).style.visibility = "hidden";
			jQuery(document).ready(function() {
			jQuery("#mess-" + id).html("Votre email est incorrecte");
			jQuery("#mess-" + id).slideDown("slow");
			setTimeout(function(){ jQuery("#mess-" + id).slideUp("slow"); }, 4000);
			});
			return false;
		}
	}
	jQuery(document).ready(function() {
	jQuery.post(url + "/wp-content/plugins/post-event2/script.php", {post_id: id, user: jQuery("#user-" + id).val(), guests: jQuery("#guests_number-" + id).val(), action: "join_user", _wpnonce: jQuery("#_wpnonce").val()}, function success(data){ 
		jQuery("#" + id).css("visibility", "hidden"); 
		if (data == "1")
			jQuery("#unsub-" + id).slideDown("slow");
		else
		{
			jQuery("#mess-" + id).html(data);
			jQuery("#mess-" + id).slideDown("slow");
			setTimeout(function(){ jQuery("#mess-" + id).slideUp("slow"); }, 4000);
		}
		});
	});
	return false;
}

function	unsub_user(id, action, url)
{
	jQuery(document).ready(function() {
		if (action == 'no')
			jQuery("#unsub-" + id).slideUp("slow");
		if (action == 'yes')
		{
			document.getElementById(id).style.visibility = "visible";
			jQuery.post(url + "/wp-content/plugins/post-event2/script.php", {post_id: id, user: jQuery("#user-" + id).val(), action: "unsub_user", _wpnonce: jQuery("#_wpnonce").val()}, function success(data){ document.getElementById(id).style.visibility = "hidden";
				jQuery("#unsub-" + id).slideUp("slow");
				jQuery("#mess-" + id).html(data);
				setTimeout(function(){ jQuery("#mess-" + id).slideDown("slow"); }, 800);
				setTimeout(function(){ jQuery("#mess-" + id).slideUp("slow"); }, 4000); });
		}
	});
}