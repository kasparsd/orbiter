<?php


class mustache extends orbiter_plugin {

	private $m;
	
	function mustache() {
		
		include( 'mustache/src/Mustache/Autoloader.php' );
		Mustache_Autoloader::register();
		
		$this->m = new Mustache_Engine( 
				orbiter::filter( 'mustache_engine_init', array() ) 
			);

		orbiter::add_filter( 'render_article_html', array( $this, 'render_article_html' ) );
		
	}
	
	function render_article_html( $article ) {

		$template_path = sprintf( 
				'%s/%s', 
				orbiter::$config['template'], 
				$article['template'] 
			);

		$args = array( 
				'orbiter' => orbiter::instance(), 
				'article' => $article,
				'articles' => $this->articles()
			);

		$args = orbiter::filter( 'render_article_html_args', $args, $article );

		if ( file_exists( $template_path ) )
			return $this->m->render( 
					file_get_contents( $template_path ), 
					$args
				);
		else
			return new Exception( 'Mustache failed to render the page.', 1 );
		
	}


	function articles() {

		$articles = array();

		foreach ( orbiter::instance()->index() as $uri => $doc )
			if ( ! isset( $doc['type'] ) )
				$articles[] = $doc;

		return $articles;

	}



}