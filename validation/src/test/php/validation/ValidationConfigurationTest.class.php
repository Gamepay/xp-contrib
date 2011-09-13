<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.ValidationConfiguration'
  );

  /**
   * Test validation.ValidationContext
   */
  class ValidationConfigurationTest extends TestCase {

    /**
     * Test the Constructor
     */
    #[@test]
    public function testCreate() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      try {
        $configuration= new ValidationConfiguration(null);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$name must be a non empty string!', $exc->getMessage());
      }
    }

    /**
     * Test getName
     */
    #[@test]
    public function testGetName() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      $this->assertEquals('validation.ValidationConfiguration', $configuration->getName());
    }

    /**
     * Test addClassConfiguration
     */
    #[@test]
    public function testAddClassConfiguration() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      $classConfiguration= new ValidatorConfiguration('NotNull');
      $configuration->addClassConfiguration($classConfiguration);
    }

    /**
     * Test getClassConfigurations
     */
    #[@test]
    public function testGetClassConfiguration() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      $this->assertEquals(array(), $configuration->getClassConfigurations());

      $classConfiguration= new ValidatorConfiguration('NotNull');
      $configuration->addClassConfiguration($classConfiguration);
      $this->assertEquals(array($classConfiguration), $configuration->getClassConfigurations());
    }

    /**
     * Test getFieldNames
     */
    #[@test]
    public function testGetFieldNames() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      $this->assertEquals(array(), $configuration->getFieldNames());

      $fieldConfiguration= new FieldValidatorConfiguration('field', 'NotNull');
      $configuration->addFieldConfiguration($fieldConfiguration);
      $this->assertEquals(array('field'), $configuration->getFieldNames());
    }

    /**
     * Test addFieldConfiguration
     */
    #[@test]
    public function testAddFieldConfiguration() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      $fieldConfiguration= new FieldValidatorConfiguration('field', 'NotNull');
      $configuration->addFieldConfiguration($fieldConfiguration);
      $this->assertEquals(array('field'), $configuration->getFieldNames());
      $this->assertEquals(array($fieldConfiguration), $configuration->getFieldConfigurations('field'));
    }

    /**
     * Test getFieldConfigurations
     */
    #[@test]
    public function testGetFieldConfigurations() {
      $configuration= new ValidationConfiguration('validation.ValidationConfiguration');
      $this->assertEquals(array(), $configuration->getFieldConfigurations('field'));

      $fieldConfiguration= new FieldValidatorConfiguration('field', 'NotNull');
      $configuration->addFieldConfiguration($fieldConfiguration);
      $this->assertEquals(array($fieldConfiguration), $configuration->getFieldConfigurations('field'));

      try {
        $configuration->getFieldConfigurations(null);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$field must be a non empty string!', $exc->getMessage());
      }
    }
  }

?>