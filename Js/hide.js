
function hide(aff, hide, hide2, hide3)
{
	var to_aff = aff;
	jQuery('#'+hide).slideUp("slow");
	jQuery('#'+hide2).slideUp("slow");
	jQuery('#'+hide3).slideUp("slow");
	setTimeout(function(){ jQuery('#'+to_aff).slideDown("slow"); }, 800);
}

