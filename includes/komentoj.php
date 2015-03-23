<?php

$konversacio_id = get_post_meta( get_the_ID(), 'wpgs_conversation_id', true );

$nodo_url = parse_url(get_option( '_wpgs_apiurl'));
$nodo_url = $nodo_url['host'];

$rss_fluo_url = 'http://' . $nodo_url . '/api/statusnet/conversation/' .  $konversacio_id . '.atom';

$konversacio_url = 'http://' . $nodo_url . '/conversation/' .  $konversacio_id;

$respondo_url = 'http://' . $nodo_url . '/index.php?action=newnotice&inreplyto=' . $konversacio_id;

$fluo = simplexml_load_file($rss_fluo_url);

$x = $fluo->getElementsByTagName('entry');

?>

<!-- You can start editing here. -->

<?php if ( in_category('miniposts') ) {?>
<p class="infopost">
<?php } else { ?><p class="infomaxipost"><? } ?>

<?
if (( get_post_status ( $ID ) == 'private' ) OR (!empty($post->post_password))) :
$words = explode(' ', the_title('', '',  false));
$count = count($words);
$count = $count-1;
$words[$count] = '<span>'.$words[$count].'</span>';
$title = implode(' ', $words);
else: $title= get_the_title();
endif;?>

«<a name="comments"><?php echo $title;?></a>» recibió <a class="genericon-comentario" href="<?php comments_link(); ?>"> <?php echo $x->length; ?></a> desde que se publicó el <?php echo get_the_time(get_option('date_format'))?>. 

<?php
$posttags = get_the_tags();
if (($posttags) AND ($enlaces==0)):
$taxonomy = "post_tag";
$term_slug = $tag->slug;
$term = get_term_by('slug', $term_slug, $taxonomy);?>
 dentro de la serie «<?php echo get_the_tag_list('','','');?>»
<?php endif;?>
Si te ha gustado este post quizá te gusten otros <strong>posts escritos por <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php the_author(); ?></a></strong>.</p>


<div id="respond">

<ul class="comments-navigation">
<li class="previous-comments"></li>
<li class="next-comments"></li>
</ul>

<ol class="comments-list">

<?php

foreach($fluo->entry as $ero) { ?>


					<li class="comment byuser comment-author-<?php echo $ero->author->name; ?> even thread-even depth-1" id="comment-93345">
					
					<!-- 
<article id="comment-37" class="comment">

<header class="comment-meta comment-author vcard">

-->

<cite class="comment-author"><img alt='' src="<?php echo $ero->author->link[1]->attributes()->href; ?>" class='avatar avatar-48 photo' height='48' width='48' /><a href="<?php echo $ero->author->uri; ?>"  rel="external " class='url'><?php echo $ero->author->name; ?></a></cite>

<p class="comment-metadata"><a title="Enlace permanente a este comentario" href="<?php echo $ero->link->attributes()->href; ?>"><?php echo $ero->published; ?></a></p>
<!-- </header> -->

<!-- <section class="comment-content comment"> -->
<div class="comment-body">

<p><?php echo $ero->content; ?></p>

<!-- </section> -->

<!-- <div class="reply"> -->
<p><span class="reply-comment"><a href="<?php echo $respondo_url . '&status_textarea=@' . $ero->author->name . '&nbsp'; ?>">Responder</a>

<!-- </div> -->
<p class="edit-comment"></p>

<!--

<ul class="children">

<li class="comment byuser comment-author-admin bypostauthor odd alt depth-2" id="comment-93400">
<cite class="comment-author"><img alt='' src='http://1.gravatar.com/avatar/79346a3b48b1b32b472ea4aa57716eb6?s=48&amp;d=http%3A%2F%2Flasindias.com%2Favatar.png%3Fs%3D48&amp;r=X' class='avatar avatar-48 photo' height='48' width='48' /><a href='http://lasindias.com/indianopedia/david-de-ugarte'  rel="external " class='url'>David de Ugarte</a></cite>
<p class="comment-metadata"><a title="Enlace permanente a este comentario" href="http://lasindias.com/como-casar-gnusocial-con-wordpress#comment-93400">19.mar.2015 - 14:50</a></p>
												
<div class="comment-body">
<p><img src="http://lasindias.com/wp-includes/images/smilies/icon_biggrin.gif" alt=":-D" class="wp-smiley" /></p>
<p><span class="reply-comment"><a href="http://lasindias.com/como-casar-gnusocial-con-wordpress?replytocom=93400#respond">Responder</a> | <a href="http://lasindias.com/como-casar-gnusocial-con-wordpress#respond">Comentar post</a></span></p>	
</div>
						
<p class="edit-comment"></p>
-->

</li>

<!-- #comment-## -->
<!--
</ul><!-- .children 
</article> -->
<?php } ?>

</ol>
						
<!-- </div> -->
</div>
