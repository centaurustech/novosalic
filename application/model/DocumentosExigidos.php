<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DocumentosExigidos
 *
 * @author 01610881125
 */
class DocumentosExigidos extends GenericModel {
    protected $_name   = 'DocumentosExigidos';
    protected $_schema = 'dbo';
    protected $_banco  = 'SAC';

    function listarDocumentosExigido($idCodigoDocumentosExigidos = ''){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array($this->_name),
                        array(
                                'Codigo',
                                'Descricao',
                                'Area',
                                'Opcao',
                                'stEstado'
                              )
                      );
        $select->where('Opcao in ?', new Zend_Db_Expr('(1,2)'));
        if($idCodigoDocumentosExigidos)
            $select->where('Codigo = ?',$idCodigoDocumentosExigidos);
        return $this->fetchAll($select);
    }
    
}
?>
