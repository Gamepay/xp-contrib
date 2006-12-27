<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * RequestData
   *
   * @see      xp://peer.http.HttpRequest#setParameters
   * @purpose  Pass request data directly to
   */
  class RequestData extends Object {
    public
      $data = '';

    /**
     * Constructor
     *
     * @param   string buf
     */
    public function __construct($buf) {
      $this->data= $buf;
      
    }
    
    /**
     * Retrieve data
     *
     * @return  string
     */
    public function getData() {
      return $this->data;
    }
  }
?>
