<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paChecklistDeEnvioDeCumprimentoDeObjeto
 */
class paChecklistDeEnvioDeCumprimentoDeObjeto extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paChecklistDeEnvioDeCumprimentoDeObjeto';

    public function verificarRelatorio($idPronac){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idPronac ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
