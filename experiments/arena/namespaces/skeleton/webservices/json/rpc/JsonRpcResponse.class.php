<?php
/* This class is part of the XP framework
 *
 * $Id: JsonRpcResponse.class.php 10594 2007-06-11 10:04:54Z friebe $
 */

  namespace webservices::json::rpc;
 
  uses('scriptlet.rpc.AbstractRpcResponse');
  
  /**
   * Wraps JSON response
   *
   * @see scriptlet.HttpScriptletResponse  
   */
  class JsonRpcResponse extends scriptlet::rpc::AbstractRpcResponse {
    
    /**
     * Sets JSON message
     *
     * @param   webservices.json.rpc.JsonRpcMessage msg
     */
    public function setMessage($msg) {
      $this->message= $msg;
    }
    
    /**
     * Make sure a fault is passed as "500 Internal Server Error"
     *
     * @see     scriptlet.HttpScriptletResponse#process
     */
    public function process() {
      if (!$this->message) return;

      if (NULL !== $this->message->getFault()) {
        $this->setStatus(HTTP_INTERNAL_SERVER_ERROR);
      }
      
      $this->content= $this->message->serializeData();
      $this->cat && $this->cat->debug('>>> ', $this->content);
    }
  }
?>