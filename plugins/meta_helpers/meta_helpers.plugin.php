<?php

class meta_helpers extends orbiter_plugin {

	function meta_helpers() {

		orbiter::add_filter( 'index_item', array( $this, 'add_helper_meta' ), 30 );

	}

	function add_helper_meta( $article ) {

		if ( ! isset( $article['template'] ) )
			$article['template'] = 'template.html';

		if ( $article['slug'] == 'index' )
			$article['uri'] = rtrim( substr( $article['uri'], strlen( 'index' ) ), '/' );

		// Add default article title, if none set
		if ( ! isset( $article['title'] ) )
			if ( preg_match( '|<h[^>]+>(.*)</h[^>]+>|iU', $article['content'], $headings ) )
				$article['title'] = strip_tags( array_shift( $headings ) );

		// Add default article pubdate for generating RSS feeds
		if ( ! isset( $article['pubdate'] ) )
			if ( isset( $article['time'] ) )
				$article['pubdate'] = date( 'r', $article['time'] );
			elseif ( isset( $article['date'] ) )
				$article['pubdate'] = date( 'r', $article['date'] );
			else
				$article['pubdate'] = date( 'r', $article['filemtime'] );

		return $article;
		
	}

}