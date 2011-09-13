<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.resources.AnnotationTestClass',
    'validation.ValidationService'
  );

  /**
   * Test validation.ValidationContext
   */
  class ValidationServiceTest extends TestCase {

    /**
     * Test the Constructor
     */
    #[@test]
    public function testCreate() {
      $service= new ValidationService();
    }

    /**
     * Test validate()
     */
    #[@test]
    public function testValidate() {
      $service= new ValidationService();
      $object= new AnnotationTestClass();

      $context= $service->validate($object);
      $expected= array(
        'AnnotationTestClass.publicField' => array('Value must be null!')
      );
      $this->assertEquals($expected, $context->getMessages());

      $context= $service->validate($object, array('protected'));
      $expected= array(
        'AnnotationTestClass.privateField' => array('Value must not be null!'),
        'AnnotationTestClass.protectedField' => array('Value must not be null!'),
        'AnnotationTestClass.publicField' => array('Value must be null!')
      );
      $this->assertEquals($expected, $context->getMessages());
    }
  }
?>