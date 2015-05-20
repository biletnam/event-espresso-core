<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 *
 * Events_Admin_Page_Decaf_Mock
 * Used for testing Events Admin Page tests.
 *
 * @package			Event Espresso
 * @subpackage		mocks
 * @author			Darren
 * @since  4.6
 *
 */
require_once EE_ADMIN . 'EE_Admin_Page.core.php';
require_once EE_ADMIN . 'EE_Admin_Page_CPT.core.php';
require_once EE_ADMIN_PAGES . 'events/Events_Admin_Page.core.php';
class Events_Admin_Page_Decaf_Mock extends Events_Admin_Page {


	public function __construct( $routing = TRUE ) {
		//define any constants that might not be defined yet when using this mock.
		if ( ! defined( 'EVENTS_PG_SLUG' ) ) {
			define( 'EVENTS_PG_SLUG', 'espresso_events' );
			define( 'EVENTS_LABEL', __('Events', 'event_espresso'));
			define( 'EVENTS_ADMIN', EE_ADMIN_PAGES . 'events' . DS );
			define( 'EVENTS_ADMIN_URL', admin_url( 'admin.php?page=' . EVENTS_PG_SLUG ));
			define( 'EVENTS_TEMPLATE_PATH', EVENTS_ADMIN . 'templates' . DS );
			define( 'EVENTS_ASSETS_URL', EE_PLUGIN_DIR_URL . 'admin_pages/events/assets/' );
		}

		parent::__construct( false );
	}


	public function default_tickets_update( $evtobj, $data ) {
		return $this->_default_tickets_update( $evtobj, $data );
	}

} //end class Events_Admin_Page_Decaf_Mock
