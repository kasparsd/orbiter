<?php

class meta extends orbiter_plugin {

	function meta() {

		orbiter::add_filter( 'index_item', array( $this, 'extract_metadata' ), 5 );

		orbiter::add_filter( 'index_meta_values', array( $this, 'set_meta_uri' ) );

	}

	function extract_metadata( $article ) {

		if ( ! isset( $article['content'] ) || empty( $article['content'] ) )
			$article['content'] = file_get_contents( $article['file'] );

		// Meta must be seperated with two new lines
		$ini = explode( PHP_EOL.PHP_EOL, $article['content'], 2 );
		
		if ( count( $ini ) !== 2 )
			return $article;

		$meta = parse_ini_string( $ini[0] );

		// Remove meta data from the main content area
		if ( $meta && orbiter::filter( 'index_strip_meta_from_content', true, $article, $meta ) )
			$article['content'] = $ini[1];

		$meta = orbiter::filter( 'index_meta_values', $meta, $article );

		return array_merge( $article, $meta );

	}

	function set_meta_uri( $meta, $article ) {

		if ( isset( $meta['slug'] ) ) {

			$meta['uri'] = trim( sprintf( 
					'%s/%s', 
					$article['dirname'], 
					$meta['slug'] 
				), '/\\' );
			
		}

		return $meta;

	}
	
}