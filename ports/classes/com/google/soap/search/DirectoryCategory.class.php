<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Type wrapper
   *
   * @purpose  Specialized SOAP type
   */
  class DirectoryCategory extends Object {
    public
      $fullViewableName,
      $specialEncoding;

    /**
     * Retrieves fullViewableName
     *
     * @return  string 
     */
    public function getFullViewableName() {
      return $this->fullViewableName;
    }

    /**
     * Sets fullViewableName
     *
     * @param   string fullViewableName
     */
    public function setFullViewableName($fullViewableName) {
      $this->fullViewableName= $fullViewableName;
    }

    /**
     * Retrieves specialEncoding
     *
     * @return  string 
     */
    public function getSpecialEncoding() {
      return $this->specialEncoding;
    }

    /**
     * Sets specialEncoding
     *
     * @param   string specialEncoding
     */
    public function setSpecialEncoding($specialEncoding) {
      $this->specialEncoding= $specialEncoding;
    }
  }
?>
