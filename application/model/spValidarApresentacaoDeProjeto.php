<?php

class spValidarApresentacaoDeProjeto extends GenericModel {

    protected $_banco = 'SAC';
    protected $_name = 'dbo.spValidarApresentacaoDeProjeto';

    public function validarEnvioProposta($idPreProjeto) {
        $select = new Zend_Db_Expr(" exec SAC.dbo.spValidarApresentacaoDeProjeto $idPreProjeto ");
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = $e->getMessage();
        }
        return $db->fetchAll($select);
    }

    public function paChecklistDeEnvioDeProposta($idPreProjeto) {
        $select = new Zend_Db_Expr(" exec SAC.dbo.paChecklistDeEnvioDeProposta $idPreProjeto ");
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = $e->getMessage();
        }
        return $db->fetchAll($select);
    }
    
}

?>
