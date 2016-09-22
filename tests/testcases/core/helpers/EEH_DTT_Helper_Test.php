<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }

/**
 *
 * EEH_DTT_Helper_Test
 *
 * @package 			Event Espresso
 * @subpackage 	tests
 * @author				Brent Christensen
 *
 */
class EEH_DTT_Helper_Test extends EE_UnitTestCase {


	/**
	 * This will hold the _datetime_field object for all tests.
	 *
	 * @var EE_Datetime_Field
	 */
	protected $_datetime_field;


	/**
	 * 	setUp
	 */
	public function setUp() {
		parent::setUp();
	}



	/**
	 * Used to set the _datetime_field property for tests with the provided params and set with defaults
	 * if none provided.
	 *
	 * @see EE_Datetime_Field for docs on params
	 * @param string $table_column
	 * @param string $nice_name
	 * @param bool   $nullable
	 * @param string $default_value
	 * @param null   $timezone
	 * @param null   $date_format
	 * @param null   $time_format
	 * @param null   $pretty_date_format
	 * @param null   $pretty_time_format
	 */
	protected function _set_dtt_field_object( $table_column = 'DTT_EVT_start', $nice_name = 'Start Date', $nullable = false, $default_value = '', $timezone = NULL, $date_format = NULL, $time_format = NULL, $pretty_date_format = NULL, $pretty_time_format = NULL ) {
		$this->loadModelFieldMocks( array( 'EE_Datetime_Field' ));
		$this->_datetime_field = new EE_Datetime_Field_Mock( $table_column, $nice_name, $nullable, $default_value, $timezone, $date_format, $time_format, $pretty_date_format, $pretty_time_format );
	}



	/**
	 * 	test_get_valid_timezone_string
	 */
	function test_get_valid_timezone_string() {

		$original_timezone_string = get_option('timezone_string');

		// TEST 1: retrieval of WP timezone string
		$expected_timezone_string = 'UTC';
		update_option( 'timezone_string', $expected_timezone_string );
		$timezone_string = EEH_DTT_Helper::get_valid_timezone_string();
		$this->assertEquals( $timezone_string, $expected_timezone_string );

		// TEST 2: retrieval of specific timezone string
		$expected_timezone_string = 'America/Vancouver';
		update_option( 'timezone_string', $expected_timezone_string );
		$timezone_string = EEH_DTT_Helper::get_valid_timezone_string( $expected_timezone_string );
		$this->assertEquals( $timezone_string, $expected_timezone_string );

		// TEST 3: bogus timezone string
		try {
			$timezone_string = EEH_DTT_Helper::get_valid_timezone_string( 'me got funky pants and like to dance' );
			$this->fail( sprintf( __( 'The timezone string %1$s should have thrown an Exception, but did not!', 'event_espresso' ), $timezone_string ));
		} catch( EE_Error $e ) {
			$this->assertTrue( true );
		}
		// reset timezone_string
		update_option( 'timezone_string', $original_timezone_string );
	}



	/**
	 * 	test_get_timezone_string_from_gmt_offset
	 */
	function test_get_timezone_string_from_gmt_offset() {
		// TEST 4: gmt offsets
		$orig_timezone_string = get_option( 'timezone_string' );
		$orig_gmt_offset = get_option( 'gmt_offset' );
		// set timezone string to empty string
		update_option( 'timezone_string', '' );
		$gmt_offsets = array (
			-12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5, 0,
			0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14
		);
		foreach ( $gmt_offsets as $gmt_offset ) {
			update_option( 'gmt_offset', $gmt_offset );
			try {
				$timezone_string = EEH_DTT_Helper::get_valid_timezone_string();
				if ( empty( $timezone_string ) ) {
					$this->fail( sprintf( __( 'The WP GMT offset setting %1$s has resulted in an invalid timezone_string!', 'event_espresso' ), $gmt_offset ));
				}
			} catch( EE_Error $e ) {
				$gmt_offset = $gmt_offset >= 0 ? '+' . (string) $gmt_offset : (string) $gmt_offset;
				$gmt_offset = str_replace( array( '.25','.5','.75' ), array( ':15',':30',':45' ), $gmt_offset );
				$gmt_offset = 'UTC' . $gmt_offset;
				$this->fail( sprintf( __( 'The WP GMT offset setting %1$s has thrown an Exception, but should not have!', 'event_espresso' ), $gmt_offset ));
				unset( $gmt_offset );
			}
		}
		update_option( 'timezone_string', $orig_timezone_string );
		update_option( 'gmt_offset', $orig_gmt_offset );
	}



	/**
	 *  setup_DateTime_object
	 *
	 * @param string $timezone_string
	 * @param int    $time
	 * @return \DateTime
	 */
	function setup_DateTime_object( $timezone_string = 'Africa/Abidjan', $time = 0 ) {
		$timezone_string = empty( $timezone_string ) ? 'Africa/Abidjan' : $timezone_string;
		$DateTime = new DateTime( 'now', new DateTimeZone( $timezone_string ) );
		$time = absint( $time );
		if ( $time ) {
			$DateTime->setTimestamp( $time );
		}
		return $DateTime;
	}



	/**
	 * 	test_date_time_add
	 */
	function test_date_time_add() {
		$this->_date_time_modifier_tests();
	}



	/**
	 * 	test_date_time_subtract
	 */
	function test_date_time_subtract() {
		$this->_date_time_modifier_tests( false );
	}



	/**
	 * 	 _date_time_modifier_tests
	 *
	 * @param bool $increment_datetimes
	 */
	protected function _date_time_modifier_tests( $increment_datetimes = true ) {
		$orig_timezone_string = get_option( 'timezone_string' );
		$orig_gmt_offset = get_option( 'gmt_offset' );
		// setup data arrays for generating test conditions
		$timezones_and_offsets = array(
			'UTC' => '',
			'America/Vancouver' => '',
			null => -5 // EST with no DST
		);
		$periods = array(
			'years' 			=> 'P%Y',
			'months'		=> 'P%M',
			'weeks'		=> 'P%W',
			'days'			=> 'P%D',
			'hours'			=> 'PT%H',
			'minutes'		=> 'PT%M',
			'seconds' 	=> 'PT%S'
		);
		// I can not tell a Fib... the following sequence is for Sidney
		$intervals = array( 0, 1, 2, 3, 5, 8, 13, 21, 34 );
		// loop thru timezones and gmt_offsets and set up environment
		foreach ( $timezones_and_offsets as $timezone_string => $gmt_offset ) {
			$gmt_offset = $gmt_offset !== 'null' ? $gmt_offset : '';
			update_option( 'timezone_string', $timezone_string );
			update_option( 'gmt_offset', $gmt_offset );
			// loop thru remaining data arrays
			foreach ( $periods as $period => $designator ) {
				foreach ( $intervals as $interval ) {
					// don't bother adding more than 5 years
					if ( $period == 'years' && $interval > 5 ) {
						continue;
					}
					// TEST: add $interval $period ( ie: add 1 year...  add 3 months...  add 34 seconds )
					// setup some objects used for testing
					$expected_datetime = $this->setup_DateTime_object();
					$actual_datetime = EE_Datetime::new_instance( array( 'DTT_EVT_start' => time() ));
					$expected_datetime->setTimeZone( new DateTimeZone( $actual_datetime->get_timezone() ) );
					$period_interval = str_replace( '%', $interval, $designator );
					// apply conditions to both objects
					if ( $increment_datetimes ) {
						$expected_datetime->add( new DateInterval( $period_interval ) );
						$actual_datetime = EEH_DTT_Helper::date_time_add( $actual_datetime, 'DTT_EVT_start', $period, $interval );
					} else {
						$expected_datetime->sub( new DateInterval( $period_interval ) );
						$actual_datetime = EEH_DTT_Helper::date_time_subtract( $actual_datetime, 'DTT_EVT_start', $period, $interval );
					}
					$expected = $expected_datetime->format( 'Y-m-d H:i:s' );
					$actual = $actual_datetime->get_DateTime_object( 'DTT_EVT_start' )->format( 'Y-m-d H:i:s' );
					$this->assertDateWithinOneMinute(
						$expected,
						$actual,
						'Y-m-d H:i:s',
						sprintf(
							__(
								'The %1$s method failed to produce correct results for the the period interval %2$s for timezone "%6$s" and UTC offset "%7$s" .%3$sExpected value: %4$s%3$sActual value: %5$s%3$s',
								'event_espresso'
							),
							$increment_datetimes ? 'EEH_DTT_Helper::date_time_add()'
								: 'EEH_DTT_Helper::date_time_subtract()',
							$period_interval,
							"\n",
							$expected,
							$actual,
							$timezone_string,
							$gmt_offset
						)
					);
					unset( $expected_datetime );
					unset( $actual_datetime );
				}
			}
		}
		update_option( 'timezone_string', $orig_timezone_string );
		update_option( 'gmt_offset', $orig_gmt_offset );
	}




	/**
	 * @since 4.6.12+
	 */
	public function test_get_timestamp_with_offset() {
		//now in timezone currently set.
		$default_timezone = new DateTimeZone( EEH_DTT_Helper::get_timezone() );
		$now = new DateTime( "now",  $default_timezone );
		$expected_offset = (int)$now->format( 'U' ) + (int)timezone_offset_get( $default_timezone, $now );

		$this->assertEquals( $expected_offset, EEH_DTT_Helper::get_timestamp_with_offset( $now->format('U' ) ) );

		//this might fail because of execution time.
		$this->assertEquals( current_time( 'timestamp' ), EEH_DTT_Helper::get_timestamp_with_offset() );

		//now let's test with a different timezone for the incoming timestamp.
		$now->setTimeZone( new DateTimeZone( 'America/Toronto' ) );
		$expected_timestamp = (int)$now->format('U') + (int)timezone_offset_get( new DateTimeZone( 'America/Toronto' ), $now );
		$this->assertEquals( $expected_timestamp, EEH_DTT_Helper::get_timestamp_with_offset( $now->format('U'), 'America/Toronto' ) );
	}





	/**
	 *  @since 4.7.0
	 */
	public function test_dates_represent_one_24_hour_date() {
		$midnight_start = new DateTime( '2015-01-25 00:00:00' );
		$midnight_end = new DateTime( '2015-01-26 00:00:00' );
		$midday_start = new DateTime( '2015-01-25 12:00:00' );
		$midday_end = new DateTime( '2015-01-26 12:00:00' );
		$midnight_next_day = new DateTime( '2015-01-27 00:00:00' );

		//first test nulls
		$this->assertFalse( EEH_DTT_Helper::dates_represent_one_24_hour_date( null, $midnight_end ) );
		$this->assertFalse( EEH_DTT_Helper::dates_represent_one_24_hour_date( $midnight_start, null ) );

		//test non midnights
		$this->assertFalse( EEH_DTT_Helper::dates_represent_one_24_hour_date( $midnight_start, $midday_end ) );
		$this->assertFalse( EEH_DTT_Helper::dates_represent_one_24_hour_date( $midday_start, $midnight_end ) );

		//test midnights but not 24 hours difference
		$this->assertFalse( EEH_DTT_Helper::dates_represent_one_24_hour_date( $midnight_start, $midnight_next_day ) );

		//test correct range
		$this->assertTrue( EEH_DTT_Helper::dates_represent_one_24_hour_date( $midnight_start, $midnight_end ) );

	}


	/**
	 * @since 4.9.0.rc.025
	 */
	public function test_get_timezone_string_for_display() {
		$offsets_to_test = array(
			0 => 'UTC+0:00',
			1 => 'UTC+1:00',
			'-1.5' => 'UTC-1:30',
			'1.25' => 'UTC+1:15'
		);
		$original_timezone_string = get_option( 'timezone_string' );
		$original_offset = get_option( 'gmt_offset' );
		//first test when there is an actual timezone_string
		update_option( 'timezone_string', 'America/New_York' );
		$this->assertEquals( 'New York', EEH_DTT_Helper::get_timezone_string_for_display() );

		//clear out timezone string and do offset tests
		update_option( 'timezone_string', '' );
		foreach ( $offsets_to_test as $offset => $expected ) {
			update_option( 'gmt_offset', $offset );
			$this->assertEquals( $expected, EEH_DTT_Helper::get_timezone_string_for_display() );
		}

		//restore original timezone_string and offset
		update_option( 'gmt_offset', $original_offset );
		update_option( 'timezone_string', $original_timezone_string );
	}


}
// End of file EEH_DTT_Helper_Test.php
// Location: /EEH_DTT_Helper_Test.php
