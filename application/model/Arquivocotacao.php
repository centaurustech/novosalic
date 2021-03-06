<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Arquivocotacao
 *
 * @author 01610881125
 */
class Arquivocotacao  extends GenericModel{

    protected $_banco   = 'BDCORPORATIVO';
    protected $_name    = 'tbArquivoCotacao';
    protected $_schema  = 'scSAC';

    public function buscarArquivos($idcotacao){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('ac'=>$this->_schema.'.'.$this->_name),
                        array(
                                'ac.idArquivo'
                              )
                      );

        $select->joinInner(
                            array('arq'=>'tbArquivo'),
                            'arq.idArquivo = ac.idArquivo',
                            array('arq.nmArquivo','arq.sgExtensao','arq.nrTamanho'),
                            'BDCORPORATIVO.scCorp'
                           );
        $select->where('ac.idCotacao = ?', $idcotacao);

        return $this->fetchAll($select);
    }
}
?>
