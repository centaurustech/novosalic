<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mecanismo
 *
 * @author 01610881125
 */
class Mecanismo extends GenericModel {
    protected $_banco   = 'SAC';
    protected $_name    = 'Mecanismo';
    protected $_schema  = 'dbo';
    
     public function buscarMecanismo() {
// criando objeto do tipo select
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('mec' => $this->_name), array('*'));
        return $this->fetchAll($slct);
     }
}
?>
