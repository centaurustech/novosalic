<?php

class fnLiberarLinks extends GenericModel {

    protected $_banco = 'SAC';
    protected $_name = 'dbo.fnLiberarLinks';

    public function liberarLinks($tipo, $cpfProponente, $idUsuarioLogado, $idPronac) {
        $select = new Zend_Db_Expr("SELECT SAC.dbo.fnLiberarLinks($tipo,'$cpfProponente',$idUsuarioLogado,$idPronac) as links");
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
