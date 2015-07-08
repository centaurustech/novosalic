<?php

/**
 * Description of spSelecionarParecerista
 *
 */
class spSelecionarParecerista extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'spSelecionarParecerista';

    public function exec($idOrgao, $idArea, $idSegmento, $vlProduto){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idOrgao, '$idArea', '$idSegmento', $vlProduto";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
