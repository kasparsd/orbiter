<?php

class parser_helpers extends orbiter_plugin {

	function parser_helpers() {

		orbiter::add_filter( 'index_item', array( $this, 'img_to_relative' ), 60 );

	}

	function img_to_relative( $article ) {

		if ( isset( orbiter::$config['home'] ) ) {

			$uri = str_ireplace( orbiter::$root, '', dirname( $article['file'] ) );

			$article['content'] = preg_replace( 
					'/src="([^http:|\/][^\"]+)"/i', 
					sprintf( 'src="%s/$1"', orbiter::$config['home'] . $uri ), 
					$article['content'] 
				);

		}

		return $article;
		
	}
	
}