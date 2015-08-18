<?php

// Run!
orbiter::instance()->run();


class orbiter {

	static $root;
	static $config = array();
	static $filters = array();


	protected function __construct() {

		self::$root = dirname( __FILE__ );

		$this->build_config();

		date_default_timezone_set( self::$config['timezone'] );

	}


	public function run() {

		$this->load_plugins();
		$this->render();

	}


	public static function instance() {

		static $instance = null;

		if ( null === $instance )
			$instance = new self;

		return $instance;

	}


	private function build_config() {

		$config = array();

		$config_file = sprintf( 
				'%s/config/%s.ini', 
				self::$root, 
				$_SERVER['HTTP_HOST']
			);

		$defaults = array(
				'home' => sprintf( 'http://%s%s', $_SERVER['SERVER_NAME'], dirname( $_SERVER['DOCUMENT_URI'] ) ),
				'docs' => 'sample_content',
				'docs_extension' => 'md',
				'template' => 'templates/default',
				'plugins' => array( 
					'render_default',
					'parser_helpers',
					'mustache',
					'markdown',
					'meta',
					'meta_helpers',
					'typography' 
				),
				'timezone' => 'Europe/Riga',
				'debug' => false
			);

		if ( file_exists( $config_file ) )
			$config = parse_ini_file( $config_file );

		self::$config = array_merge( $defaults, $config );

	}


	private function build_index() {

		$index = array();
		$articles = array();

		$docs = $this->glob_files( 
				sprintf( '*.%s', self::$config['docs_extension'] ), 
				realpath( self::$config['docs'] ) 
			);

		foreach ( $docs as $doc ) {

			$uri = str_replace( realpath( self::$config['docs'] ), '', $doc );
			$uri_info = pathinfo( $uri );

			$uri = trim( sprintf( 
					'%s/%s',
					trim( $uri_info['dirname'], '/\\' ),
					$uri_info['filename']
				), '/' );

			$article = $this->filter( 
					'index_item', 
					array( 
						'id' => md5( $doc ),
						'dirname' => trim( $uri_info['dirname'], '/' ),
						'slug' => $uri_info['filename'],
						'uri' => $uri,
						'file' => $doc,
						'filemtime' => filemtime( $doc )
					)
				);

			$articles[ $article['uri'] ] = $article;

		}

		$index = array( 
				'index' => $articles, 
				'timestamp' => time() 
			);

		// Store in cache
		file_put_contents(
			self::$root . '/cache/index.json', 
			json_encode( $index )
		);

		return $index;

	}


	function config() {

		return self::$config;

	}


	function index() {

		static $index = array();

		if ( ! empty( $index ) )
			return $index['index'];

		if ( file_exists( self::$root . '/cache/index.json' ) )
			$index = json_decode( file_get_contents( self::$root . '/cache/index.json' ), true );
		else
			$index = $this->build_index();

		return $index['index'];

	}


	private function render() {

		// Get the request relative to the document root
		$request = str_replace( 
				dirname( $_SERVER['DOCUMENT_URI'] ), 
				'', 
				$_SERVER['REQUEST_URI']
			);

		$parsed = parse_url( $request );

		$request = trim( $parsed['path'], '/' );

		$this->filter( 'render', $request );

	}


	private function load_plugins() {

		$plugins = array();

		// See which plugins are enabled in site config
		$plugins_enabled = self::$config['plugins'];

		// Include enabled plugin files
		foreach ( $plugins_enabled as $plugin_name ) {

			$plugin_file = sprintf( 
					'%1$s/plugins/%2$s/%2$s.plugin.php', 
					self::$root, 
					$plugin_name 
				);

			if ( file_exists( $plugin_file ) )
				include $plugin_file;

		}

		// Autoload plugins
		foreach ( get_declared_classes() as $class )
			if ( is_subclass_of( $class, 'orbiter_plugin' ) )
				$plugins[] = new $class;

		return $plugins;

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


class orbiter_plugin {

	function __construct() {}

}

