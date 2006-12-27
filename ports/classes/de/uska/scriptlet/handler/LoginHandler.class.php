<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.workflow.Handler',
    'de.uska.scriptlet.wrapper.LoginWrapper',
    'de.uska.db.Player'
  );
  
  /**
   * Handler for login
   *
   * @purpose  Login
   */
  class LoginHandler extends Handler {
    public
      $cat=     NULL;

    /**
     * Constructor.
     *
     */
    public function __construct() {
      parent::__construct();
      $this->setWrapper(new LoginWrapper());
      
      $log= Logger::getInstance();
      $this->cat= $log->getCategory();
    }
    
    /**
     * Handle submitted data
     *
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.Context context
     */
    public function handleSubmittedData($request, $context) {
      $cm= ConnectionManager::getInstance();
      $wrapper= $this->getWrapper();
      
      try {
        $player= Player::getByUsername($wrapper->getUsername());
      } catch (SQLException $e) {
        throw($e);
      }
      
      if (!is('de.uska.db.Player', $player)) {
        $this->addError('mismatch');
        return FALSE;
      }
      
      if (20000 != $player->getBz_id()) {
        $this->addError('mismatch');
        return FALSE;
      }
      
      if (md5($wrapper->getPassword()) != $player->getPassword()) {
        $this->addError('mismatch');
        return FALSE;
      }
      
      $context->setUser($player);
      
      try {
        $db= $cm->getByHost('uska', 0);
        $perms= $db->select('
            p.name
          from
            plain_right_matrix as prm,
            permission as p
          where p.permission_id= prm.permission_id
            and prm.player_id= %d',
          $player->getPlayer_id()
        );
      } catch (SQLException $e) {
        throw($e);
      }
      
      $cperms= array();
      foreach ($perms as $p) { $cperms[$p['name']]= TRUE; }
      $context->setPermissions($cperms);
      
      return TRUE;
    }
    
    /**
     * Finalize this handler
     *
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @param   &scriptlet.xml.Context context
     */
    public function finalize($request, $response, $context) {

      // Set a cookie
      $response->setCookie(new Cookie('uska-user', $context->user->getUsername()));

      $return= $request->session->getValue('authreturn');

      if ($return) {
        $this->cat->debug('Redirect to', $return);
        // $request->session->removeValue('authreturn');
        $response->sendRedirect(sprintf('%s://%s%s%s',
          $return['scheme'],
          $return['host'],
          $return['path'],
          $return['query'] ? '?'.$return['query']: ''
        ));
      }
    }
  }
?>
