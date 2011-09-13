<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'validation.ValidatorConfiguration'
  );

  /**
   * Test validation.ValidationContext
   */
  class ValidatorConfigurationTest extends TestCase {

    protected
      $instance;

    public function setUp() {
      $this->instance= new ValidatorConfiguration('NotNull');
    }

    /**
     * Test getType()
     */
    #[@test]
    public function testGetType() {
      $configuration= $this->instance;
      $this->assertEquals('NotNull', $configuration->getType());
    }

    /**
     * Test setType()
     */
    #[@test]
    public function testSetType() {
      $configuration= $this->instance;
      $configuration->setType('Null');
      $this->assertEquals('Null', $configuration->getType());

      try {
        $configuration->setType(null);
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$type must be a non empty string!', $exc->getMessage());
      }
    }

    /**
     * Test getGroups()
     */
    #[@test]
    public function testGetGroups() {
      $configuration= $this->instance;
      $this->assertNull($configuration->getGroups());
    }

    /**
     * Test setGroups()
     */
    #[@test]
    public function testSetGroups() {
      $configuration= $this->instance;

      $configuration->setGroups(array('state1', 'state2'));
      $this->assertEquals(array('state1', 'state2'), $configuration->getGroups());

      $configuration->setGroups(NULL);
      $this->assertNull($configuration->getGroups());

      try {
        $configuration->setGroups(array(1, 2));
        $this->assertFalse(TRUE);
      } catch (IllegalArgumentException $exc) {
        $this->assertEquals('$groups must be null or an array of non empty strings!', $exc->getMessage());
      }
    }

    /**
     * Test getParameter()
     */
    #[@test]
    public function testGetParameter() {
      $configuration= $this->instance;
      $this->assertEquals(array(), $configuration->getParameter());
    }

    /**
     * Test setParameter()
     */
    #[@test]
    public function testSetParameter() {
      $configuration= $this->instance;
      $configuration->setParameter(array('name1' => 'parameter1'));
      $this->assertEquals(array('name1' => 'parameter1'), $configuration->getParameter());
    }

    /**
     * Test addParameter()
     */
    #[@test]
    public function testAddParameter() {
      $configuration= $this->instance;
      $configuration->addParameter(array('name1' => 'parameter1'));
      $this->assertEquals(array('name1' => 'parameter1'), $configuration->getParameter());
      $configuration->addParameter(array('name1' => 'parameter2'));
      $this->assertEquals(array('name1' => 'parameter2'), $configuration->getParameter());
    }
  }

?>