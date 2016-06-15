<?php
class DMSTest extends \PHPUnit_Framework_TestCase {
	
	// make sure PL_THEMENAME == PageLines
    public function testThemeName() {
        $this->expectOutputString('PageLines');
        print PL_THEMENAME;
    }

	// Test PHP Safe Mode is not enabled
	public function testSafeMode() {
		$this->assertFalse( (bool) ini_get('open_basedir') );
	}
	
	// Test the PageLines Less Compiler
	public function testLess() {		
		$expected = "height: 24px;\n";				
		$this->expectOutputString($expected);
		$less = '@base:24px;height:@base';
		$pless = new PagelinesLess();
		print $pless->raw_less($less);
	}
	
	// Make sure there are no Less errors in the system
	public function testLessError() {		
		$this->expectOutputString('');
		print get_theme_mod( 'less_last_error' );
	}
}