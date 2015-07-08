<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paDocumentos
 */
class paDocumentos extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paDocumentos';

    public function marcasAnexadas($idPronac){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idPronac ";
//        $sql = " select top 100 * from sac.dbo.projetos ";
//        xd($sql);
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
