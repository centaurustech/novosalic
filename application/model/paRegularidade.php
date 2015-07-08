<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paRegularidade
 */

class paRegularidade extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paRegularidade';

    public function exec($CNPJCPF){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." '$CNPJCPF' ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
