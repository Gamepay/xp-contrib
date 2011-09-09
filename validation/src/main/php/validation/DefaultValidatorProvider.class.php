<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'lang.reflect.Package',
    'validation.ValidatorProviderInterface'
  );

  /**
   *
   */
  class DefaultValidatorProvider implements ValidatorProviderInterface {

    protected
      $packages= array();

    public function __construct() {
      $this->addPackage('validation.validator');
    }

    public function getValidator($type) {
      foreach ($this->packages as $package) {
        if ($package->providesClass($type)) {
          $class= $package->loadClass($type);
          try {
            return $class->newInstance();
          } catch (Exception $exc) { }
        }
      }
    }

    public function addPackage($package) {
      if (!is_string($package) || empty($package)) {
        throw new IllegalArgumentException('$package must be a non empty string!');
      }
      $this->packages[]= Package::forName($package);
    }
  }
?>