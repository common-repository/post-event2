=== Plugin Name ===
Contributors: oXfoZ
Donate link: http://codex.oxfoz.com/cat/post-event/
Tags: event, post, iCal, google map, event management, events
Requires at least: 2.7.0
Tested up to: 2.9.2
Stable tag: 3.0.1

== Description ==

You often ask how could you create event using wordpress, organize jobs meeting, party or holidays with your friends and contact them easily ?
Then "Post Event" is THE events plugin for wordpress you are looking for!

Post Event allows you to define event information (date, location, schedule...) directly in the post administration page. All event information is automatically displayed in the post page (single.php), including Google Maps AND iCal link (integration in your agenda) without any code modification!

It is very usefull (Google Maps and iCal integration), high-performance and can be use for any event.
We have based the development on wordpress structure, without any modification of the database (events details are loaded as meta of the posts).
The plugin is provided in english and french but can be translated in any languages (.po file included).

== Installation ==

This plugin will be located in the WordPress plugin folder and activated from the admin panel.

    * Download the plugin here: http://wordpress.org/extend/plugins/post-event2,
    * Upload the plugin to the wp-content/plugins folder in your WordPress directory (wp-content/plugins),
    * Activate in your admin panel (under the "plugins" section).
    * Don't forget to define a google key in the admin/settings menu ! This key will allow you to generate google maps for your events

WARNING: You must have at least php5 ! Otherwise you will have a parse error !

== Frequently Asked Questions ==

= What are the modification in the admin panel after plugin activation? =

* A new meta box named 'PostEventSectionId' is created in the right sidebar when you edit or create a post. This is where you will be able to edit the event
* A new section "Post Event" is available in the option settings

= I want to display in my home page the 5 next events of cat "evenementSportif". Can we do that?! =

Of course :-) You just need to call "query_events" function, with the same arguments as the standard query_posts:

<?php query_events('category_name=evenementSportif&showposts=5'); ?>
<ul>
	<?php if (have_posts()) : while (have_posts()) : the_post();?>
	<li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a><small><?php echo mysql2date(get_option('date_format'), get_post_event_end_date(), true) ?></small></li>
	<?php endwhile; endif; ?>
</ul>

= What are the other functions I can call on the front side? =

Functions you can call on front-side :
 query_events: load event from databases and sort them by start date, by desc as default, Sort order could be change in admin panel.
 get_post_event_ical: return event iCal document parametre: void.
 get_post_event_start_date: return event start date parametre: void.
 get_post_event_end_date: return event end date parametre: void.
 get_post_event_start_time: return event start time parametre: void.
 get_post_event_end_time: return event end date parametre: void.
 get_post_event_place: return event place parametre: void.
 get_post_event_as_object: return event as object: void.
 the_post_event_html: display event details parametre: void.
 the_post_event_map: display event map parametre: void.

= How can I translate Post Event in my own language? =

To create a language file you have to create a document like: PostEvent-lang_LANG.mo wich is compiled version of lang_LANG.po.
To create a language file you need gettext and the command line to enter is : msgfmt lang_LANG.po -o PostEvent-lang_LANG.mo

== Screenshots ==

1. First screen shot corresponds to the general options (Google API Key...)
2. In the second screen shot, we can see all the event options (meta box): date, location...
3. Third screen shot corresponds to the event in the front side...


== Changelog ==

= 1.0 =
* First public version

= 1.1 =
* Bug corrected in query_events function (test if empty)

= 1.5 =
* Bug corrected in query_events function (showposts, several events with the same date.).
* When you edit a post, ics files are generated for each category wich the post is included, and a global ics files is generated too.
* Admin page has new design.
* When you have write the location, a google map is displayed under the input.
* Clean code, no more direct database call.

= 2.0 =
* You can know allow your visitors to subscribe to events.
* You can choose if visitors can register to events or not.
* You can get an ics file with all registers users for an event.
* If you want have an ical "feed" from a special category, you select the category and you add "ical" after the "/" in the url navebar.
* Ics files are more complete. A link to the posts is added and the category name.
* You can had a link in your pages using functions :the_ical_calendar() and get_ical_calendar() arguments are "nothing" or category name.
* Query are sql inject protected and are in a class file. There are no more direct database call except in the query class file.
* A button delete event has been added under the input in admin posts.
* A column 'Start Event Date' has been added in the admin posts list.

= 2.1 =
* You can now add guests to your event
* Max guests number is defined in admin when you edit the post

= 2.1.1 =
* Fixing some bugs in query_events function

= 2.2 =
* You can now choose to show registers number to visitors
* Fixing bugs.

= 2.3 =
* Fixing translation mistakes
* Warning if end event date older than start date
* With WPMU, ics directory are now in blogs directory and with wordpress ics directory is now in upload directory
* Fixing bug with ics generating
* You can now have many maps on the front page

= 2.3.1 =
* Fixing important class bug

= 2.3.2 =
* Updating query_events
* Fixing multi-map bugs

= 3.0 =
* All the code has been review bugs fixed
* Admin page has been remake for more use comfort
* New calendar in post admin page

= 3.0.1 =
* Fixing bug in query_events

= 3.0.2 =
* Fixing XSS problem