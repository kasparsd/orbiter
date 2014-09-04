<?php


class render_default extends orbiter_plugin {


	function render_default() {

		// Parse the request and resolve which file to use
		orbiter::add_filter( 'render', array( $this, 'render_item' ), 50 );

		// Add default headers for things like RSS feeds
		orbiter::add_filter( 'render_article_html', array( $this, 'http_headers' ), 5 );

	}


	function render_item( $request ) {

		$orbiter = orbiter::instance();
		$index = $orbiter->index();

		if ( isset( $index[ $request ] ) )
			$doc = $index[ $request ];
		elseif ( isset( $index[ $request . '/index' ] ) )
			$doc = $index[ $request . '/index' ];
		elseif ( isset( $index[ $request . 'index'] ) )
			$doc = $index[ $request . 'index'];
		elseif ( isset( $index['404'] ) )
			$doc = $index['404'];
		else
			throw new Exception( 'Could not find the requested document.', 1 );

		$article = orbiter::filter( 'parse_document', $doc, $request );

		$html = orbiter::filter( 
				'render_article_html', 
				$article
			);

		if ( is_string( $html ) )
			die( $html );
	
	}


	function http_headers( $article ) {

		// Render Not Found
		//header('HTTP/1.0 404 Not Found');

		if ( ! isset( $article['template'] ) )
			return $article;

		$path = pathinfo( $article['template'] );

		if ( $path['extension'] == 'xml' )
			header( 'Content-type: text/xml; charset=UTF-8' );

		header( sprintf( 'Last-Modified: %s GMT', gmdate( 'D, d M Y H:i:s',  $article['filemtime'] ) ) );

		return $article;

	}


}

