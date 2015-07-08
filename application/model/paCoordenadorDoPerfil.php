<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paCoordenadorDoPerfil
 *
 */
class paCoordenadorDoPerfil extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paCoordenadorDoPerfil';

    public function buscarUsuarios($codPerfil, $codOrgao){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $codPerfil, $codOrgao ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
