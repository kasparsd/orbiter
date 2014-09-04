<?php

include dirname( __FILE__ ) . '/orbiter.php';

if ( isset( $_REQUEST['build'] ) )
	orbiter::instance()->build();
else
	orbiter::instance()->run();
