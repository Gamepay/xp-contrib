<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.ValidationService',
    'validation.validator.NullValidator'
  );

  /**
   * Test validation.validator.NotNullValidator
   */
  class NullValidatorTest extends TestCase {

    /**
     * Test validate
     */
    #[@test]
    public function testValidate() {
      $validator= new NullValidator();

      $configuration= new ValidatorConfiguration('NullValidator');

      $service= new ValidationService();
      $context= new ValidationContext('name', $service);

      $validator->validate(null, $configuration, $context);
      $this->assertFalse($context->hasMessages());

      $validator->validate('notNull', $configuration, $context);
      $this->assertTrue($context->hasMessages());
    }
  }

?>