<?php
 uses (
    'unittest.TestCase'
 );

 /**
  * Tests files:
  * - XPBugCircularReference, XPBugCircularReferenceExtended, XPBugCircularReferenceInterface
  *
  * This test shows an xp framework circular reference bug, which appears
  * when you try to load a class which has a uses to an other class
  * which extends the first one. e.g:
  *
  * MainInterface.class.php:
  * 	interface MainInterface
  * 	{
  * 		public function foo();
  * 	}
  *
  * Main.class.php:
  * 	use('foo.bar.MainExtended');
  * 	class Main implements MainInterface
  * 	{
  * 		public function foo(
  * 			return new MainExtended();
  * 		);
  * 	}
  *
  * MainExtended.class.php:
  * 	use('foo.bar.Main');
  * 	class MainExtended extends Main {
  *		}
  *
  * test:
  * 	use('foo.bar.Main');
  * 	PHP Fatal error:  Class 'Main' not found in ... /MainExtended.class.php
  *
  * Btw: This error only occurs when the first class implements one or more interfaces?!
  */

 /**
  * Tests for xp circular reference
  */
 class XPBugCircularReferenceTest extends TestCase
 {
 		#[@test]
		public function XpFrameworkCircularReferenceTest()
		{
			 // try to load class
			 uses ('xpbug.circularreference.XPBugCircularReference');

			 // we are still here so no fatal error
			 return $this->assertTrue(true);
		}
 }
?>
