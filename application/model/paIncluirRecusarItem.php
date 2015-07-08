<?php

/**
 * Description of paIncluirRecusarItem
 * @author Jefferson Alessandro
 */
class paIncluirRecusarItem extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paIncluirRecusarItem';

    public function incluirRecusarItem($idSolicitarItem, $usuariologado, $stEstado){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idSolicitarItem, $usuariologado, $stEstado ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
