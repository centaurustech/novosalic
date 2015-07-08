<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Arquivodispensalicitacao
 *
 * @author 01610881125
 */
class Arquivodispensalicitacao  extends GenericModel{

    protected $_banco   = 'BDCORPORATIVO';
    protected $_name    = 'tbArquivoDispensaLicitacao';
    protected $_schema  = 'scSAC';

    public function buscarArquivos($iddispensalicitacao){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('adl'=>$this->_schema.'.'.$this->_name),
                        array(
                                'adl.idArquivo'
                              )
                      );

        $select->joinInner(
                            array('arq'=>'tbArquivo'),
                            'arq.idArquivo = adl.idArquivo',
                            array('arq.nmArquivo','arq.sgExtensao','arq.nrTamanho'),
                            'BDCORPORATIVO.scCorp'
                           );
        $select->where('adl.idDispensaLicitacao = ?', $iddispensalicitacao);

        return $this->fetchAll($select);
    }
}
?>
