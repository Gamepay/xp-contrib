<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.ValidationService',
    'validation.validator.NotNullValidator'
  );

  /**
   * Test validation.validator.NotNullValidator
   */
  class NotNullValidatorTest extends TestCase {

    /**
     * Test validate
     */
    #[@test]
    public function testValidate() {
      $validator= new NotNullValidator();

      $configuration= new ValidatorConfiguration('NotNullValidator');

      $service= new ValidationService();
      $context= new ValidationContext('name', $service);

      $validator->validate('notNull', $configuration, $context);
      $this->assertFalse($context->hasMessages());

      $validator->validate(null, $configuration, $context);
      $this->assertTrue($context->hasMessages());
    }
  }

?>