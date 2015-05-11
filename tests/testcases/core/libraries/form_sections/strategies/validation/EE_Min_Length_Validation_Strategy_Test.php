<?php
if ( !defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 *
 * EE_Min_Length_Validation_Strategy_Test
 *
 * @package			Event Espresso
 * @subpackage
 * @author				Mike Nelson
 * @group forms
 * @group core/libraries/form_sections/validation
 * @group EE_Min_Length_Validation_Strategy_Test
 *
 */
class EE_Min_Length_Validation_Strategy_Test extends EE_UnitTestCase{

	protected $_validator = null;
	public function setUp(){
		parent::setUp();
		$this->_validator = new EE_Min_Length_Validation_Strategy( 'oups', 5 );
		$input = new EE_Text_Input();
		//finalize its construction, but we don't actually need the input anyways
		$this->_validator->_construct_finalize( $input );
	}
	/**
	 * tests that it can be left blank and pass validation (because the required validation
	 * strategy is what should fail here, if it's added as a validation strategy)
	 */
	public function test_validate__blank_but_not_required() {
		try{
			$this->_validator->validate( '' );
			$this->assertTrue(true);
		}catch(EE_Validation_Error $e ){
			$this->assertFalse( true, 'Even though the empty string is shorter than the min length, that indicates no response was given. And that should be checked by the REQUIRED validation' );
		}
	}

	/**
	 * tests that validation passes with MORE than th eminimum characters
	 */
	public function test_validate__pass() {
		try{
			$this->_validator->validate( '123456' );
			$this->assertTrue(true);
		}catch(EE_Validation_Error $e ){
			$this->assertFalse( true, '123456 has more than 5 characters and so should be ok' );
		}
	}

	/**
	 * tests validation passes with the minimum number of characters
	 */
	public function test_validate__pass_but_barely() {
		try{
			$this->_validator->validate( '12345' );
			$this->assertTrue(true);
		}catch(EE_Validation_Error $e ){
			$this->assertFalse( true, '12345 has 5 characters and so should be ok' );
		}
	}

	/**
	 * tests that validation fails when there are FEWER than the minimum number of characters
	 */
	public function test_validate__fail() {
		try{
			$this->_validator->validate( '123' );
			$this->assertFalse( true, '123 has too FEW character' );
		}catch(EE_Validation_Error $e ){
			$this->assertTrue( true );
		}
	}



}

// End of file EE_Min_Length_Validation_Strategy_Test.php