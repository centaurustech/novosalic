<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * paChecklistSolicitacaoProrrogacaoPrazo
 * @author Jefferson Alessandro
 */
class paChecklistSolicitacaoProrrogacaoPrazo extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paChecklistSolicitacaoProrrogacaoPrazo';

    public function checkSolicitacao($idPronac, $dtInicio, $dtFinal, $acao){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idPronac, '$dtInicio', '$dtFinal', '$acao' ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
