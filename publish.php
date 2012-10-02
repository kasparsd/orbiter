<?php

/**
 * Load and verify config
 */

if ( file_exists( 'config.ini' ) )
	$config = parse_ini_file( 'config.ini' );
else
	die( 'config.ini could not be found.' );

if ( empty( $config ) )
	die( 'config.ini is empty.' );

$config_req = array( 'docs', 'template', 'public' );

if ( array_intersect( $config_req, array_keys( $config ) ) !== $config_req )
	die( 'All required configuration settings are not specified.' );


/**
 * Includes
 */

// Mustache
include( 'lib/mustache/src/Mustache/Autoloader.php' );
Mustache_Autoloader::register();
$config['mustache_engine'] = new Mustache_Engine;

// Markdown
include( 'lib/php-markdown-1.0.1o/markdown.php' );

// Typography
include( 'lib/php-typography/php-typography.php' );
$config['typography'] = new phpTypography();


/**
 * Collect all articles and parse the content
 */

$files = glob_docs( '*.md', $config['docs'] );

/**
 * Generate articles
 */

if ( ! empty( $files ) )
	$articles = parse_docs( $files );
else
	report_error( 'No source files found.' );


/**
 * Generate HTML files
 */

if ( isset( $articles ) && ! empty( $articles ) )
	generate_html( $articles );
else
	report_error( 'No HTML files were generated because no articles were found.' );

/**
 * Return errors, if any
 */

show_errors();

/**
 * Functions
 */

function glob_docs( $pattern, $path ) {
	$files = glob( $path . $pattern );
	$dirs = glob( $path . '*', GLOB_ONLYDIR | GLOB_NOSORT );

	if ( ! empty( $dirs ) )
		foreach ( $dirs as $dir )
			$files = array_merge( $files, glob( $dir . DIRECTORY_SEPARATOR . $pattern ) );
	
	return $files;
}

function parse_docs( $files ) {
	global $config;

	$articles = array();

	foreach ( $files as $file ) {

		// Don't publish drafts
		if ( strstr( $file, '.draft') )
			continue;

		// Create the slug
		$p = pathinfo( $file );
		$base = basename( $p['dirname'] );
		$slug = $p['filename'];

		// Setup the article
		$article = array(
				'file' => $file,
				'slug' => $slug,
				'content' => file_get_contents( $file ),
				'filemtime' => filemtime( $file ),
				'title' => ''
			);

		// Extract meta data
		if ( $meta = get_meta( $article['content'] ) ) {
			$article = array_merge( $article, $meta['meta'] );
			$article['content'] = $meta['content'];
		}

		// Apply Markdown parser, generate HTML
		$article['content'] = Markdown( $article['content'] );
		
		// Apply typography
		$article['content'] = $config['typography']->process( $article['content'] );

		// Extract a default title if none set
		if ( empty( $article['title'] ) )
			if ( preg_match('|<h[^>]+>(.*)</h[^>]+>|iU', $article['content'], $heading ) )
				$article['title'] = $heading[1];

		// Append article to the list
		$articles[] = $article;
	}

	return $articles;
}

function generate_html( $articles = array() ) {
	global $config;

	if ( empty( $articles ) )
		return false;

	$template = file_get_contents( $config['template'] );

	// Generated individual articles
	foreach ( $articles as $article ) {
		$path = $config['public'] . $article['slug'] . '/';

		if ( ! is_dir( $path ) )
			mkdir( $path );

		// Add images folder as a symlink
		if ( is_dir( dirname( $article['file'] ) . '/images' ) && ! file_exists( $path . 'images' ) )
			symlink( dirname( $article['file'] ) . '/images', $path . 'images' );

		file_put_contents( 
			$path . 'index.html', 
			$config['mustache_engine']->render( 
				$template, 
				array(
					'article' => $article 
				)
			)
		);
	}

	// Generate the index page
	file_put_contents( 
		$config['public'] . 'index.html', 
		$config['mustache_engine']->render( 
			$template, 
			array(
				'index' => array_values( $articles )
			)
		) 
	);
}

function get_meta( $text ) {

	// Meta must be seperated with two new lines
	$split_meta = explode( "\n\n", $text, 2 );
	
	if ( count( $split_meta ) !== 2 )
		return false;

	// Each meta entry is separated by a single line break
	$meta_rows = explode( "\n", current( $split_meta ) . "\n", -1 );
	
	if ( empty( $meta_rows ) )
		return false;

	$meta = array();

	foreach ( $meta_rows as $row )
		if ( $maybe_meta_row = explode( ':', trim( $row ), 2 ) )
			if ( count( $maybe_meta_row ) == 2 )
				$meta['meta'][ trim( $maybe_meta_row[0] ) ] = trim( $maybe_meta_row[1] );

	if ( isset( $meta['meta'] ) )
		$meta['content'] = end( $split_meta );

	return $meta;
}

function report_error( $error ) {
	global $errors;

	$errors[] = $error;
}

function show_errors() {
	global $errors;

	if ( ! isset( $errors ) || empty( $errors ) )
		return;

	echo json_encode( $errors );
}

