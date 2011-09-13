<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.ValidationConfigurationInterface'
  );

  /**
   *
   */
  interface ValidationConfigurationProviderInterface {

    public function extendConfiguration(
      ValidationConfigurationInterface $configuration
    );

  }
?>