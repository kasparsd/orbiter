<?php


class render_default extends orbiter {

	function render_default() {
		orbiter::add_filter( 'parse_document', array( $this, 'render_path_setup' ), 50 );
		orbiter::add_filter( 'render_article', array( $this, 'render_article' ), 50 );
	}

	function render_path_setup( $article ) {
		
		$slug_path = str_replace( realpath( orbiter::$config['docs'] ), '', dirname( $article['file'] ) );
		$slug = array_shift( explode( '.', basename( $article['file'] ) ) );

		// Add a default slug
		if ( ! isset( $article['slug'] ) )
			$article['slug'] = $slug;

		// Add a default location
		if ( ! isset( $article['uri'] ) )
			$article['uri'] = $slug_path;

		// We know what we're doing, so don't put it in a sub-folder
		if ( isset( $article['filename'] ) )
			$article['slug'] = dirname( $slug_path );

		// Specify a default template file
		if ( ! isset( $article['template'] ) )
			$article['template'] = 'template.html';

		// Specify a default destination filename
		if ( ! isset( $article['filename'] ) )
			$article['filename'] = 'index.html';
		else
			$article['filename'] = basename( $article['filename'] );

		// Create a permalink
		$article['permalink'] = ltrim( $article['uri'] . '/' . $article['slug'], '/' );

		return $article;
	}


	function render_article( $article, $articles ) {

		$destination = realpath( orbiter::$config['public'] ) . '/' . $article['permalink'] . '/' . $article['filename'];

		// Create path to that folder
		if ( ! is_dir( dirname( $destination ) ) )
			mkdir( dirname( $destination ), 0777, true );

		// Automatically symlink the assets folder
		if ( is_dir( dirname( $article['file'] ) . '/assets' ) && ! is_link( dirname( $destination ) . '/assets' ) )
			symlink( dirname( $article['file'] ) . '/assets', dirname( $destination ) . '/assets' );

		$template_vars = array( 
				'article' => $article, 
				'articles' => $articles, 
				'config' => orbiter::$config 
			);

		$template_vars = orbiter::filter( 'render_article_vars', $template_vars, $article, $articles );

		if ( isset( orbiter::$template[ $article['template'] ] ) )
			file_put_contents( 
					orbiter::filter( 'render_article_destination', $destination, $article ),
					orbiter::filter( 'render_article_html', $template_vars, orbiter::$template[ $article['template'] ] ) 
				);

		return $article;

	}

}