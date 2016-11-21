<?php

class Orbis_Projects_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_projects' );
		$this->set_db_version( '1.0.0' );
	}
}
