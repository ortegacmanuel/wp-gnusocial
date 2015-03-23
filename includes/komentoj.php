<?php

$konversacio_id = get_post_meta( get_the_ID(), 'wpgs_conversation_id', true );

$nodo_url = parse_url(get_option( '_wpgs_apiurl'));
$nodo_url = $nodo_url['host'];

$rss_fluo_url = 'http://' . $nodo_url . '/api/statusnet/conversation/' .  $konversacio_id . '.atom';

$konversacio_url = 'http://' . $nodo_url . '/conversation/' .  $konversacio_id;

$respondo_url = 'http://' . $nodo_url . '/index.php?action=newnotice&inreplyto=' . $konversacio_id;

?>

<div id="respond">

<ul class="comments-navigation">
<li class="previous-comments"></li>
<li class="next-comments"></li>
</ul>

<ol class="comment-list">

<?php

$fluo = simplexml_load_file($rss_fluo_url);

foreach($fluo->entry as $ero) { ?>


					<li class="comment byuser comment-author-<?php echo $ero->author->name; ?> even thread-even depth-1" id="comment-93345">
						<cite class="comment-author"><img alt='' src='<?php echo $ero->author->link[1]->attributes()->href; ?>' class='avatar avatar-48 photo' height='48' width='48' /><a href='http://lasindias.com/indianopedia/manuel-ortega'  rel="external " class='url'>Manuel Ortega</a></cite>
						<p class="comment-metadata"><a title="Enlace permanente a este comentario" href="http://lasindias.com/como-casar-gnusocial-con-wordpress#comment-93345">19.mar.2015 - 10:28</a></p>
												
						<div class="comment-body">
							<p>Estuve documentando y tengo muy buenas noticias <img src="http://lasindias.com/wp-includes/images/smilies/icon_biggrin.gif" alt=":-D" class="wp-smiley" /></p>
<p>GNU social ya nos genera el <a href="https://lamatriz.org/api/statusnet/conversation/7757.atom" rel="nofollow">feed de comentarios que necesitamos incrustar en WordPress</a>. Aquí dejo <a href="https://lamatriz.org/api/statusnet/conversation/7757.atom" rel="nofollow">un ejemplo de una conversación</a> en la Matriz.org.</p>
<p>Vamos a tener un gran finde <img src="http://lasindias.com/wp-includes/images/smilies/icon_smile.gif" alt=":-)" class="wp-smiley" /></p>
<p><span class="reply-comment"><a href="http://lasindias.com/como-casar-gnusocial-con-wordpress?replytocom=93345#respond">Responder</a> | <a href="http://lasindias.com/como-casar-gnusocial-con-wordpress#respond">Comentar post</a></span></p>	
						</div>
						
						<p class="edit-comment">  <a class="comment-edit-link" href="http://lasindias.com/wp-admin/comment.php?action=editcomment&amp;c=93345">Editar</a></p>
						
</li><!-- #comment-## -->

<?php } ?>

</ol>
						
<!-- </div> -->
</div>
