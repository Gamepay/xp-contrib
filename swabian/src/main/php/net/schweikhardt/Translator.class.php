<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Translator interface
   *
   * @purpose  Interface
   */
  interface Translator {
  
    /**
     * Translates the given sentence
     *
     * @param   string sentence
     * @return  string translation
     */  
    public static function translate($string);

  }
?>
