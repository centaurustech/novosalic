<?php

class fnDtInicioRelatorioTrimestral extends GenericModel {

    protected $_banco = 'SAC';
    protected $_name = 'dbo.fnDtInicioRelatorioTrimestral';

    public function dtInicioRelatorioTrimestral($idPronac) {
        $select = new Zend_Db_Expr("SELECT SAC.dbo.fnDtInicioRelatorioTrimestral($idPronac) as dtLiberacao");
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
