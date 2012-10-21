<?php


class render_default extends orbiter {

	function render_default() {
		orbiter::add_filter( 'parse_document', array( $this, 'render_path_setup' ), 50 );
		orbiter::add_filter( 'render_article', array( $this, 'render_article' ), 50 );
	}

	function render_path_setup( $article ) {

		$slug_path = str_replace( realpath( orbiter::$config['docs'] ), '', dirname( $article['file'] ) );
		$slug = array_shift( explode( '.', basename( $article['file'] ) ) );

		if ( ! isset( $article['slug'] ) )
			$article['slug'] = $slug;

		if ( ! isset( $article['uri'] ) )
			$article['uri'] = dirname( $slug_path );

		if ( ! isset( $article['template'] ) )
			$article['template'] = 'template.html';

		if ( ! isset( $article['filename'] ) )
			$article['filename'] = $article['slug'] . '/index.html';
		else
			$article['filename'] = basename( $article['filename'] );

		$article['permalink'] = ltrim( $article['uri'], '/' ) . '/' . $article['slug'];

		return $article;
	}


	function render_article( $article, $articles ) {

		$destination = realpath( orbiter::$config['public'] ) . $article['uri'] . '/' . $article['filename'];

		// Create path to that folder
		if ( ! is_dir( dirname( $destination ) ) )
			mkdir( dirname( $destination ), 0777, true );

		if ( isset( orbiter::$template[ $article['template'] ] ) )
			file_put_contents( 
					orbiter::filter( 'render_article_destination', $destination, $article ),
					orbiter::filter( 'render_article_html', array( 'article' => $article, 'articles' => $articles ), orbiter::$template[ $article['template'] ] ) 
				);

		return $article;

	}

}