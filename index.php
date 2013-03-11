<?php

new orbiter();


class orbiter {

	public $docs = array();
	public $articles = array();

	static public $template = array();
	static public $config = array();
	static public $filters = array();


	function orbiter() {
		
		$this->load_config();
		$this->load_plugins();

		$this->render();

	}


	private function load_config() {

		// Load config file
		if ( file_exists( __DIR__ . '/config.ini' ) )
			$config = parse_ini_file( __DIR__ . '/config.ini', true );
		elseif ( file_exists( __DIR__ . '/config_sample.ini' ) )
			$config = parse_ini_file( __DIR__ . '/config_sample.ini', true );
		else
			$config = array();

		// Set config for the specific host / site
		if ( isset( $config[ $_SERVER['HTTP_HOST'] ] ) ) 
			self::$config = $config[ $_SERVER['HTTP_HOST'] ];
		elseif ( isset( $config['example.com'] ) ) // Load sample config for fresh install demo
			self::$config = $config['example.com'];
		else
			die('No config file found!');

	}


	private function render() {

		if ( ! isset( self::$config['home'] ) )
			self::$config['home'] = dirname( $_SERVER['SCRIPT_NAME'] );

		$docs = $this->glob_files( self::$config['docs_extension'], realpath( self::$config['docs'] ) );

		// Parse docs
		foreach ( $docs as $doc )
			$this->articles[] = $this->filter( 'parse_document', array( 
					'file' => $doc,
					'uri' => str_replace( realpath( self::$config['docs'] ), '', dirname( $doc ) ),
					'slug' => str_replace( 'index', '', array_shift( explode( '.', basename( $doc ) ) ) ),
					'template' => 'template.html',
					'content' => file_get_contents( $doc ),
					'filemtime' => filemtime( $doc ),
					'id' => md5( $doc ),
					'config' => self::$config
				) );

		// Render index pages
		$this->filter( 'render', $this->articles );

	}


	private function load_plugins() {

		if ( isset( self::$config['timezone'] ) )
			date_default_timezone_set( self::$config['timezone'] );

		// Load all plugins
		foreach ( explode( ',', self::$config['plugins'] ) as $plugin_name ) {
			$plugin_file = sprintf( '%1$s/plugins/%2$s/%2$s.plugin.php', __DIR__, trim( $plugin_name ) );

			if ( file_exists( $plugin_file ) )
				include( $plugin_file );
		}

		// Autoload plugins
		foreach ( get_declared_classes() as $class )
			if ( is_subclass_of( $class, get_class( $this ) ) )
				new $class();

	}


	public function glob_files( $pattern, $path ) {

		$files = glob( $path . '/' . $pattern );
		$dirs = glob( $path . '/*', GLOB_ONLYDIR );

		if ( ! empty( $dirs ) )
			foreach ( $dirs as $dir )
				$files = array_merge( $files, $this->glob_files( $pattern, $dir ) );
		
		return $files;

	}	


	static public function add_filter( $hook, $callback, $weight = 10 ) {

		// Register a filter
		self::$filters[ $hook ][ $weight ][] = $callback;

		// Sort filters by their weights
		ksort( self::$filters[ $hook ] );

	}


	static public function filter( $hook, $value, $args = array() ) {

		if ( ! isset( self::$filters[ $hook ] ) )
			return $value;

		foreach ( self::$filters[ $hook ] as $weight )
			foreach ( $weight as $callback )
				$value = call_user_func( $callback, $value, $args );

		return $value;

	}

}

