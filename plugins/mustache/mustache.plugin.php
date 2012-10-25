<?php


class mustache extends orbiter {

	private $m;
	
	function mustache() {

		orbiter::add_filter( 'render_article_html', array( $this, 'render_article_html' ) );

		include( 'mustache/src/Mustache/Autoloader.php' );
		Mustache_Autoloader::register();

		$this->m = new Mustache_Engine(
				orbiter::filter( 'mustache_engine', array() )
			);
		
	}
	
	function render_article_html( $article, $template ) {

		return $this->m->render( $template, $article );
		
	}
}