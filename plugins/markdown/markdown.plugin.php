<?php

class markdown extends orbiter_plugin {

	function markdown() {

		include( dirname( __FILE__ ) . '/php-markdown/markdown.php' );

		orbiter::add_filter( 'index_item', array( $this, 'convert_markdown' ), 20 );
	
	}

	function convert_markdown( $article ) {
		
		// Parse Markdown
		if ( isset( $article['content'] ) )
			$article['content'] = Markdown( $article['content'] );

		return $article;
		
	}
	
}
