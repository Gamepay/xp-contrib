<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.collections.DJBX33AHashImplementation');

  /**
   * Provides hashing functionality for maps.
   * 
   * Basic usage:
   * <code>
   *   $hashCode= HashProvider::hashOf($string);
   * </code>
   *
   * Uses DJBX33A as default hashing implementation. To change the hashing
   * implementation to be used, use the following:
   * <code>
   *   $provider= &HashProvider::getInstance();
   *   $provider->setImplementation(new MyHashImplementation());
   * </code>
   * 
   * @see      xp://util.collections.DJBX33AHashImplementation
   * @see      xp://util.collections.Map
   * @purpose  Hashing
   */
  class HashProvider extends Object {
    public
      $impl= NULL;

    /**
     * Static initializer. Sets DJBX33A as default hashing implementation.
     * 
     */
    public static function __static() {
      $self= HashProvider::getInstance();
      $self->setImplementation(new DJBX33AHashImplementation());
    }
    
    /**
     * Retrieve sole instance of this object
     *
     * @return  &util.collections.HashProvider
     */
    public static function getInstance() {
      static $instance= NULL;

      if (!isset($instance)) {
        $instance= new HashProvider();

        // No need to set the impl member, this block is only ever 
        // executed from the static initializer, which sets this 
        // member to the default hashing implementation.
      }
      return $instance; 
    }
    
    /**
     * Returns hash for a given string
     *
     * @param   string str
     * @return  int
     */
    public static function hashOf($str) {
      $self= HashProvider::getInstance();
      return $self->impl->hashOf($str);
    }

    /**
     * Set hashing implementation
     * 
     * @param   &util.collections.HashImplementation impl
     * @throws  lang.IllegalArgumentException when impl is not a HashImplementation
     */
    public function setImplementation($impl) {
      if (!is('util.collections.HashImplementation', $impl)) {
        throw(new IllegalArgumentException(
          'Implementation is not a HashImplementation, '.xp::typeOf($impl).' given'
        ));
      }
      $this->impl= $impl;
    }

    /**
     * Get hashing implementation
     * 
     * @return  &util.collections.HashImplementation
     */
    public function getImplementation() {
      return $this->impl;
    }
  }
?>
