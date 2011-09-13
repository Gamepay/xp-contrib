<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.ValidationContext',
    'validation.ValidationService'
  );

  /**
   * Test validation.ValidationContext
   */
  class ValidationContextTest extends TestCase {

    /**
     * Test the Constructor
     */
    #[@test]
    public function testCreate() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      try {
        $context= new ValidationContext(null, $service);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$name must be a non empty string!', $exc->getMessage());
      }
    }

    /**
     * Test getName()
     */
    #[@test]
    public function testGetName() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $this->assertEquals('name', $context->getName());
    }

    /**
     * Test getValidationService()
     */
    #[@test]
    public function testGetValidationService() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $this->assertEquals($service, $context->getValidationService());
    }

    /**
     * Test getParentContext()
     */
    #[@test]
    public function testGetParentContext() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $childContext= $context->getChildContext('child', TRUE);
      $this->assertEquals($context, $childContext->getParentContext());
    }

    /**
     * Test getChildContextNames()
     */
    #[@test]
    public function testGetChildContextNames() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $childContext1= $context->getChildContext('child1', TRUE);
      $childContext2= $context->getChildContext('child2', TRUE);
      $this->assertEquals(array('child1', 'child2'), $context->getChildContextNames());
    }

    /**
     * Test getChildContext()
     */
    #[@test]
    public function testGetChildContext() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $childContext1= $context->getChildContext('child1', TRUE);
      $this->assertEquals('child1', $childContext1->getName());
      $this->assertEquals($service, $childContext1->getValidationService());
      $this->assertEquals($context, $childContext1->getParentContext());
      $this->assertEquals($childContext1, $context->getChildContext('child1', TRUE));
      $this->assertEquals($childContext1, $context->getChildContext('child1'));
      $childContext2= $context->getChildContext('child2', TRUE);
      $this->assertNotEquals($childContext1, $childContext2);

      try {
        $childContext3= $context->getChildContext('child3');
        $this->assertFalse(TRUE);
      } catch (IllegalStateException $exc) {
        $this->assertEquals('no child context with name "child3" found!', $exc->getMessage());
      }
      try {
        $childContext3= $context->getChildContext(null);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$name must be a non empty string!', $exc->getMessage());
      }
      try {
        $childContext3= $context->getChildContext('child3', 'TRUE');
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$create must be a boolean!', $exc->getMessage());
      }
    }

    /**
     * Test logMessage()
     */
    #[@test]
    public function testLogMessage() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $context->logMessage('context message');
      $this->assertEquals(array('name' => array('context message')), $context->getMessages());

      try {
        $context= $context->logMessage(null);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$message must be a non empty string!', $exc->getMessage());
      }
    }

    /**
     * Test hasMessages()
     */
    #[@test]
    public function testHasMessages() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $childContext= $context->getChildContext('child', TRUE);

      $this->assertFalse($context->hasMessages());;

      $childContext->logMessage('child context message');
      $this->assertTrue($context->hasMessages());
      $this->assertFalse($context->hasMessages(FALSE));

      $context->logMessage('context message');
      $this->assertTrue($context->hasMessages());
      $this->assertTrue($context->hasMessages(FALSE));
    }

    /**
     * Test getMessages()
     */
    #[@test]
    public function testGetMessages() {
      $service= new ValidationService();
      $context= new ValidationContext('name', $service);
      $childContext= $context->getChildContext('child', TRUE);

      $this->assertEquals(array(), $context->getMessages());;

      $childContext->logMessage('child context message');
      $expected= array(
        'name.child' => array('child context message')
      );
      $this->assertEquals(array('name.child' => array('child context message')), $context->getMessages());
      $this->assertEquals(array(), $context->getMessages(FALSE));;

      $context->logMessage('context message');
      $expected= array(
        'name' => array('context message'),
        'name.child' => array('child context message')
      );
      $this->assertEquals($expected, $context->getMessages());
      $expected= array(
        'name' => array('context message')
      );
      $this->assertEquals($expected, $context->getMessages(FALSE));
    }
  }

?>