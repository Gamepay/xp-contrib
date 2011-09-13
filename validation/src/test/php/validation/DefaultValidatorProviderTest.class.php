<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.DefaultValidatorProvider'
  );

  /**
   * Test validation.ValidationContext
   */
  class DefaultValidatorProviderTest extends TestCase {

    /**
     * Test getValidator
     */
    #[@test]
    public function testGetValidator() {
      $provider= new DefaultValidatorProvider();
      $validator= $provider->getValidator('NotNullValidator');
      $this->assertInstanceOf('validation.ValidatorInterface', $validator);

      $validator= $provider->getValidator('FantasticValidator');
      $this->assertNull($validator);
    }

    /**
     * Test addPackage
     */
    #[@test]
    public function testAddPackage() {
      $provider= new DefaultValidatorProvider();
      $provider->addPackage('validation');

      try {
        $provider->addPackage('validation.FantasticPackage');
        $this->assertFalse(TRUE);
      } catch (ElementNotFoundException $exc) {
        $this->assertEquals('No classloaders provide validation.FantasticPackage', $exc->getMessage());
      }
    }
  }

?>