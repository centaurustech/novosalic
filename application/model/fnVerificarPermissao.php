<?php

class fnVerificarPermissao extends GenericModel {

    protected $_banco = 'SAC';
    protected $_name = 'dbo.fnVerificarPermissao';

    public function verificarPermissaoProjeto($idPronac, $idUsuarioLogado) {
        $select = new Zend_Db_Expr("SELECT SAC.dbo.fnVerificarPermissao(2,'',$idUsuarioLogado,$idPronac) as Permissao");
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = $e->getMessage();
        }
        return $db->fetchRow($select);
    }

    public function verificarPermissaoProposta($idPreProjeto, $idUsuarioLogado) {
        $select = new Zend_Db_Expr("SELECT SAC.dbo.fnVerificarPermissao(1,'',$idUsuarioLogado,$idPreProjeto) as Permissao");
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = $e->getMessage();
        }
        return $db->fetchRow($select);
    }

    public function verificarPermissaoAdministrativo($idUsuarioLogado) {
        $select = new Zend_Db_Expr("SELECT SAC.dbo.fnVerificarPermissao(0,'',$idUsuarioLogado,'') as Permissao");
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = $e->getMessage();
        }
        return $db->fetchRow($select);
    }
    
}

?>
