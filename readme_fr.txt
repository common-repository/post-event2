=== Plugin Name ===
Collaborateur: oXfoZ
Lien: http://codex.oxfoz.com/cat/post-event/
Tags: évènements, post, iCal, google map, gestion des évènements
Requiere au minimum vla version: 2.7.0
Testé sur la version: 2.89.2
Tag stable: 3.0.1

== Introduction ==

Vous vous etes toujours demandé comment gérer des évènements sur wordpress, organiser des rendez-vous professionels, des soirées, ou des vacances entre amis et en informer facilement ces derniers ?
PostEvent est LE plugin de gestion d'évènements sur wordpress.

PostEvent vous permez de relier un evenement a un article.
Vous pouvez definir le nom de l'evenement, les dates de debut et de fin, le lieu etc...
Vous pouvez aussi afficher toutes ces informations et plus encore.
PostEvent vous permet aussi d'affiche une carte google ainsi que de generer un document iCal pour votre agenda.
Il est donc tres pratique, tres performant, il peut etre utiliser pour n'importe quelle type d'evenements et le document iCal vous permettra de ne jamais oublier de rendez vous etc...

Nous nous basons sur la structure de wordpress sans modification de la base de donnee et les fonctions sont ecrite sur le meme model que celle de wordpress, ce qui rend leur utilisation bien plus aisee.
Les evenement sont enregistrer en meta donne de l'article.
Le plugin est internationnalise et est disponible en francais et en anglais, mais il est tres facile de le porter dans d'autre langue, le fichier .po etant distribuer

== Installation ==

Ce plugin doit etre place dans le dossier de plugin de wordpress et doir etre activer par la panneau d'administration.

    * Telecharger le plugin ici: http://wordpress.org/extend/plugins/post-event2,
    * Uploader le dans le dossier wp-content/plugins de wordpress(wp-content/plugins),
    * Activer le dans le panneau d'administration(dans la section "plugins").
    * N'oubliez pas d'enregistrer uune cle google afin de profiter des carte google pour vos evenements.

ATTENTION: Vous devez avoir au moin php5, sinon vous aurez une erreur: "Parse error" !

== Foire au questions ==

- Quelles sont les modifications dans le panneau d'administration apre l'activatoion du plugin ?
************************************************************

Modifications apres activation

    * Une nouvelle boite meta apellee 'PostEventSectionId' est cree dans la barre de droite quand vous editez un article ou creez un post. C'est ici que vous pourrez editer des evenements.
    * Lors de l'affichage de l'article les informations de l'evenement seront affichees a la suite du contenu.

== Pour les developpeurs ==
Fonctions que vous pouvez apeller en front-side.
 
 get_post_event_ical: retourne le document le document iCal de l'evenement parametre: void.
 get_post_event_start_date: retourne la date de debut de l'evenement parametre: void.
 get_post_event_end_date: retourne la date de fin de l'evenement parametre: void.
 get_post_event_start_time: retourne l'heure de debut de l'evenement parametre: void.
 get_post_event_end_time: retourne l'heure de fin de l'evenement parametre: void.
 get_post_event_place: retourne le lieu de l'evenement parametre: void.
 get_post_event_as_object: retourne un evenement sous forme d'objet parametre: void.
 the_post_event_html: affiche les details de l'evenement parametre: void.
 the_post_event_map: affiche la carte de l'evenement parametre: void.

 query_events: recupere les evenement de la base de donne et les trie par ordre de debut d'evenement, par ordre decroissant par defaut, l'ordre de trie peut etre changer dans le panneau d'administration. Il prend les memes argument que la fonction query_posts de wordpress, les requetes sont donc les memes que celle utilise habituellement avec query_posts (the_title, the_content etc...).
Exemple : je veux afficher les 5 derniers elements de la categorie 'evenementSportif' : 

<?php query_events('category_name=evenementSportif'); ?>
<ul>
	<?php if (have_posts()) : while (have_posts()) : the_post();?>
	<li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a><small><?php echo mysql2date(get_option('date_format'), get_post_event_end_date(), true) ?></small></li>
	<?php endwhile; endif; ?>
</ul>

 get_ : retourne des donnees qui peuvent etre modifie comme on le desire ou encor affichee...
 the_ : affiche des donnees.
 
 Pour creer un fichier de langue il faut creer un fichier de la forme suivant: PostEvent-lang_LANG.mo qui est une version compile du fichier lang_LANG.po.
Pour creer un fichier de langue vous aurez besoin de gettext et la ligne de commande a taper est : msgfmt lang_LANG.po -o PostEvent-lang_LANG.mo

== Changelog ==

= 1.0 =
* Premiere version publique

= 1.1 =
* Correction de bug dans query_events (test si vide)

= 1.5 =
* Correction de bug dans query_events (showposts, plusieurs évènement avec la meme date.).
* Lorsque vous sauvez un article, des fichiers ics sont générés pour chacune des catégorie dans lesquels l'article est inclu, un fichier ics global est aussi généré.
* La page d'admin a un nouveau design.
* Lorsque vous avez entré le lieu, une google map s'affiche en dessous.
* Nettoyage du code, plus d'apelle direct a la base de donnée.

= 2.0 =
* Les visiteurs peuvent si vous le désirez, s'inscrire a vos évenements.
* Vous pouvez choisir si les visiteurs peuvent s'inscrire a un évenement ou pas.
* Vous pouvez récupérer un fichier csv, dans lequel se trouve tous les inscrits pour un évenement donné.
* Pour récupérer le "flux" ical d'un catégorie en particulier, allez dans cette categorie et ajouter "ical" aprés le "/" dans la barre d'adresse
* Les fichiers ics sont plus complets. La description contient le lien vers le post et la categorie du post a été ajouté.
* Vous pouvez ajouté un lien vers les fichiers ical avec les fonctions :the_ical_calendar() and get_ical_calendar() On peut donner un argument(le nom de la categorie) ou pas d'argument. Sans argument, le fichier ics contenant tous les évenement est utilisé.
* Les requetes sql sont toutes protégé contre les injections sql et se trouve toutes dans une class. Il n'y a plus d'apelle direct en base de donné mis a part dans la class de requetes.
* Un bouton "Supprimer l'évenement" a été ajouté sous les champs dans l'administration des articles.
* Un colonne 'Debut de l'évenement' a été ajouté en admin dans la liste des articles.

= 2.1 =
* Vous pouvez choisir un nombre d'invitez lorsque vous vous inscrivez a un evenement.
* Le nombre d'invité maximum par inscrit est défini en admin dans l'édition d'un article.

= 2.1.1 =
* Debbugage dans la fonction query_events

= 2.2 =
* Vous pouvez maintenant choisir de montrer le nombre d'inscrit a vos visiteurs
* Debbugage

 = 2.3 =
* Correction d'erreur de traduction
* Un message apparait si la date de fin d'evenement est plus ancienne que la date de debut
* Dans WPMU, les dossiers ics sont dans les dossier du blog, et avec wordpress ils sont dans le dossier upload
* Correction d'un bug lors de la generation d'un fichier ics
* Vous pouvez maintenant avoir plusieurs carte sur une meme page.

= 2.3.1 =
* Correction d'un bug important de class

= 2.3.2 =
* Amelioration du query_events
* Correction de bug pour l'affichage de plusieurs carte sur une page

= 3.0 =
* Refonte complete du code
* Les pages d'admin ont ete refaire pour plus de comfort de navigation
* Nouveau calendrier pour l'enregistrement des evenements

= 3.0.1 =
* Correction d'un bug dans le query_events

= 3.0.2 =
* Correction faille XSS