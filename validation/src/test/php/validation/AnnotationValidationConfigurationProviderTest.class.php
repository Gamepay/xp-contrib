<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.AnnotationValidationConfigurationProvider',
    'validation.ValidationConfiguration'
  );

  /**
   * Test validation.ValidationContext
   */
  class AnnotationValidationConfigurationProviderTest extends TestCase {

    /**
     * Test extendConfiguration
     */
    #[@test]
    public function testExtendConfiguration() {
      $provider= new AnnotationValidationConfigurationProvider();

      $configuration= new ValidationConfiguration('validation.resources.AnnotationTestBase');
      $provider->extendConfiguration($configuration);



      $classConfigurations= $configuration->getClassConfigurations();
      $this->assertEquals(1, count($classConfigurations));

      $classConfiguration = array_shift($classConfigurations);
      $this->assertEquals('NotNullValidator', $classConfiguration->getType());
      $this->assertNull($classConfiguration->getGroups());
      $this->assertEquals(array(), $classConfiguration->getParameter());



      $expected= array('noAnnotationField', 'privateField', 'protectedField', 'publicField');
      $this->assertEquals($expected, $configuration->getFieldNames());



      $fieldConfigurations= $configuration->getFieldConfigurations('noAnnotationField');
      $this->assertEquals(1, count($fieldConfigurations));

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NotNullValidator', $fieldConfiguration->getType());
      $this->assertEquals(array('noAnnotation'), $fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());



      $fieldConfigurations= $configuration->getFieldConfigurations('privateField');
      $this->assertEquals(2, count($fieldConfigurations));

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NotNullValidator', $fieldConfiguration->getType());
      $this->assertEquals(array('protected', 'private'), $fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NullValidator', $fieldConfiguration->getType());
      $this->assertEquals(array('protected', 'private'), $fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());



      $fieldConfigurations= $configuration->getFieldConfigurations('protectedField');
      $this->assertEquals(2, count($fieldConfigurations));

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NotNullValidator', $fieldConfiguration->getType());
      $this->assertEquals(array('protected'), $fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NullValidator', $fieldConfiguration->getType());
      $this->assertEquals(array('protected'), $fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());



      $fieldConfigurations= $configuration->getFieldConfigurations('publicField');
      $this->assertEquals(2, count($fieldConfigurations));

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NotNullValidator', $fieldConfiguration->getType());
      $this->assertNull($fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());

      $fieldConfiguration = array_shift($fieldConfigurations);
      $this->assertEquals('NullValidator', $fieldConfiguration->getType());
      $this->assertNull($fieldConfiguration->getGroups());
      $this->assertEquals(array(), $fieldConfiguration->getParameter());
    }
  }
?>