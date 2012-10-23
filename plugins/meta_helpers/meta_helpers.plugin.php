<?php

class meta_helpers extends orbiter {

	function meta_helpers() {
		orbiter::add_filter( 'parse_document', array( $this, 'add_helper_meta' ), 30 );
	}

	function add_helper_meta( $article ) {

		// Add default article title, if none set
		if ( ! isset( $article['title'] ) )
			if ( preg_match( '|<h[^>]+>(.*)</h[^>]+>|iU', $article['content'], $headings ) )
				$article['title'] = strip_tags( array_shift( $headings ) );

		// Add default article pubdate for generating RSS feeds
		if ( ! isset( $article['pubdate'] ) )
			if ( isset( $article['time'] ) )
				$article['pubdate'] = date( 'r', $article['time'] );
			else if ( isset( $article['date'] ) )
				$article['pubdate'] = date( 'r', $article['date'] );
			else
				$article['pubdate'] = date( 'r', $article['filemtime'] );

		return $article;
	}

}