<?php

function fave_post( $post_id = NULL, $user_id = NULL ) {
	return FaveIt::fave_post( $post_id, $user_id );	
}

function unfave_post( $post_id = 0, $user_id = 0 ) {
	return FaveIt::unfave_post( $post_id, $user_id );	
}

function has_fave( $post_id = 0, $user_id = 0 ) {
	return FaveIt::has_fave( $post_id, $user_id );	
}
