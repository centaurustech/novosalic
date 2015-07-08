<?php

/**
 * Description of paChecarLimitesOrcamentario
 *
 */
class paChecarLimitesOrcamentario extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paChecarLimitesOrcamentario';

    public function exec($idPronac, $fase){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idPronac, $fase ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
