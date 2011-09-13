<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.FieldValidatorConfiguration',
    'validation.ValidatorConfigurationTest'
  );

  /**
   * Test validation.ValidationContext
   */
  class FieldValidatorConfigurationTest extends ValidatorConfigurationTest {

    public function setUp() {
      $this->instance= new FieldValidatorConfiguration('fieldName', 'NotNull');
    }

    /**
     * Test getFieldName()
     */
    #[@test]
    public function testGetFieldName() {
      $configuration= $this->instance;
      $this->assertEquals('fieldName', $configuration->getFieldName());
    }

    /**
     * Test setFieldName()
     */
    #[@test]
    public function testSetFieldName() {
      $configuration= $this->instance;
      $configuration->setFieldName('theFieldName');
      $this->assertEquals('theFieldName', $configuration->getFieldName());

      try {
        $configuration->setFieldName(null);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$fieldName must be a non empty string!', $exc->getMessage());
      }
    }

  }

?>