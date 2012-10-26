<?php


class mustache extends orbiter {

	private $m;
	
	function mustache() {

		include( 'mustache/src/Mustache/Autoloader.php' );
		Mustache_Autoloader::register();
		
		$this->m = new Mustache_Engine( orbiter::filter( 'mustache_engine_init', array() ) );

		orbiter::add_filter( 'render_article_html', array( $this, 'render_article_html' ) );
		
	}
	
	function render_article_html( $vars ) {

		$this->m->setPartials( orbiter::$template );
		
		if ( isset( $vars['article']['template'] ) )
			return $this->m->render( orbiter::$template[ $vars['article']['template'] ], $vars );

		return $vars;
		
	}
}