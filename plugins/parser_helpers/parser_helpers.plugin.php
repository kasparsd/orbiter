<?php

class parser_helpers extends orbiter {

	function parser_helpers() {

		orbiter::add_filter( 'parse_document', array( $this, 'img_to_relative' ), 60 );
	}

	function img_to_relative( $article ) {

		if ( isset( orbiter::$config['home'] ) ) {
			$base = orbiter::$config['home'] . '/' . trim( orbiter::$config['docs'], '/\\' ) . str_replace( realpath( orbiter::$config['docs'] ), '', dirname( $article['file'] ) );
			$article['content'] = preg_replace( '/src="([^http:|\/][^\"]+)"/i', sprintf( 'src="%s/$1"', $base ), $article['content'] );
		}

		return $article;
		
	}
	
}