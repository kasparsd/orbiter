<?php

class markdown extends orbiter_plugin {

	function markdown() {

		include( 'php-markdown/markdown.php' );
		orbiter::add_filter( 'index_item', array( $this, 'convert_markdown' ), 20 );
	
	}

	function convert_markdown( $article ) {
		
		// Parse Markdown
		$article['content'] = Markdown( $article['content'] );

		return $article;
		
	}
	
}
