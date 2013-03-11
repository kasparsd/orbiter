<?php


class render_default extends orbiter {


	function render_default() {
		
		// Generate a permalink
		orbiter::add_filter( 'parse_document', array( $this, 'article_setup' ), 50 );

		// Parse the request and resolve which file to use
		orbiter::add_filter( 'render', array( $this, 'render_view' ), 50 );

		// Add default headers for things like RSS feeds
		orbiter::add_filter( 'render_article_html', array( $this, 'http_headers' ), 5 );

	}


	function article_setup( $article ) {

		// Add permalink
		$article['permalink'] = trim( $article['uri'] . '/' . $article['slug'], '/' );

		return $article;

	}


	function render_view( $articles ) {

		// Get the request relative to this folder
		$request_uri = str_replace( dirname( $_SERVER['PHP_SELF'] ), '/', $_SERVER['REQUEST_URI'] );

		// Remove query args and opening/trailing slashes
		$request_uri = trim( array_shift( explode( '?', $request_uri ) ) , '/' );

		// Remove index.php
		$request_uri = str_replace( basename( $_SERVER['SCRIPT_NAME'] ), '', $request_uri );

		foreach ( $articles as $article )
			if ( $article['permalink'] == $request_uri )
				die( orbiter::filter( 'render_article_html', array( 'article' => $article, 'articles' => $articles, 'config' => orbiter::$config ), $request_uri ) );

		// Render Not Found
		header('HTTP/1.0 404 Not Found');

		foreach ( $articles as $article )
			if ( $article['permalink'] == '404' )
				die( orbiter::filter( 'render_article_html', array( 'article' => $article, 'articles' => $articles, 'config' => orbiter::$config ), $request_uri ) );

		die( orbiter::filter( 'render_article_html', array( 'article' => array( 'content' => 'Page Not Found' ), 'articles' => $articles, 'config' => orbiter::$config ), $request_uri ) );
	
	}


	function http_headers( $content ) {

		if ( ! isset( $content['article']['template'] ) )
			return $content;

		$path = pathinfo( $content['article']['template'] );

		if ( $path['extension'] == 'xml' )
			header( 'Content-type: text/xml; charset=UTF-8' );

		header( sprintf( 'Last-Modified: %s GMT', gmdate( 'D, d M Y H:i:s',  $content['article']['filemtime'] ) ) );

		return $content;

	}


}