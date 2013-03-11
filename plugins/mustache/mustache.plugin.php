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

		$template = sprintf( '%s/%s', realpath( orbiter::$config['template'] ), $vars['article']['template'] );
		
		if ( file_exists( $template ) )
			return $this->m->render( file_get_contents( $template ), $vars );

		die('Mustache failed to render the page.');
		
	}

}