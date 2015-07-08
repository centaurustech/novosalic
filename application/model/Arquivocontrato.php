<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Arquivocontrato
 *
 * @author 01610881125
 */
class Arquivocontrato extends GenericModel{

    protected $_banco   = 'BDCORPORATIVO';
    protected $_name    = 'tbArquivoContrato';
    protected $_schema  = 'scSAC';

    public function buscarArquivos($idcontrato){
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
        $select->where('ac.idContrato = ?', $idcontrato);

        return $this->fetchAll($select);
    }
}
?>
