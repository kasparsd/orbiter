<?php

class markdown extends orbiter {

	function markdown() {

		include( 'php-markdown/markdown.php' );
		orbiter::add_filter( 'parse_document', array( $this, 'convert_markdown' ), 20 );
	
	}

	function convert_markdown( $article ) {
		
		// Parse Markdown
		$article['content'] = Markdown( $article['content'] );

		return $article;
		
	}
	
}