<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paUsuariosDoPerfil
 *
 * @author 01129075125
 */
class paUsuariosDoPerfil extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paUsuariosDoPerfil';

    public function buscarUsuarios($codPerfil, $codOrgao){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $codPerfil, $codOrgao ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
