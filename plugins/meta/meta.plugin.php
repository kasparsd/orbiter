<?php

class meta extends orbiter {

	function meta() {
		orbiter::add_filter( 'parse_document', array( $this, 'extract_metadata' ), 5 );
	}

	function extract_metadata( $article ) {

		// Meta must be seperated with two new lines
		$ini = explode( "\n\n", $article['content'], 2 );
		
		if ( count( $ini ) !== 2 )
			return $article;

		$meta = parse_ini_string( array_shift( $ini ) );

		// Remove meta data from the main content area
		if ( $meta && orbiter::filter( 'strip_meta_from_content', true, $article, $meta ) )
			$article['content'] = end( $ini );

		return array_merge( $article, orbiter::filter( 'meta_values', $meta, $article ) );

	}
	
}