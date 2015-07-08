<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Arquivolicitacao
 *
 * @author 01610881125
 */
class Arquivolicitacao extends GenericModel{

    protected $_banco   = 'BDCORPORATIVO';
    protected $_name    = 'tbArquivoLicitacao';
    protected $_schema  = 'scSAC';

    public function buscarArquivos($idlicitacao){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('al'=>$this->_schema.'.'.$this->_name),
                        array(
                                'al.idArquivo'
                              )
                      );

        $select->joinInner(
                            array('arq'=>'tbArquivo'),
                            'arq.idArquivo = al.idArquivo',
                            array('arq.nmArquivo','arq.sgExtensao','arq.nrTamanho'),
                            'BDCORPORATIVO.scCorp'
                           );
        $select->where('al.idlicitacao = ?', $idlicitacao);

        return $this->fetchAll($select);
    }

}
?>
