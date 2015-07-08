<?php

/**
 * Description of Projetos
 *
 * @author André Nogueira Pereira
 */
class HistoricoDevolucaoFiscalizacao extends GenericModel {

    protected $_name = 'tbHistoricoDevolucaoFiscalizacao';
    protected $_schema = 'dbo';
    protected $_banco = 'SAC';

    public function buscaHistoricoDevolucaoFiscalizacao($where) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('rf' => $this->_name), array(
            'rf.idHistoricoDevolucao',
            'rf.idRelatorioFiscalizacao',
            'CAST(rf.dsJustificativaDevolucao AS TEXT) as dsJustificativaDevolucao',
            'rf.dtEnvioDevolucao',
            'rf.stDevolucao'
                )
        );

        foreach ($where as $key => $value) {
            $select->where($key, $value);
        }
        $select->order('rf.dtEnvioDevolucao desc');
//        xd($select->assemble());
        return $this->fetchAll($select);
    }

    public function insereHistoricoDevolucaoFiscalizacao($dados) {
        return $this->insert($dados);
    }

    public function alteraHistoricoDevolucaoFiscalizacao($dados, $where) {
        try {
            return $this->update($dados, $where);
        } catch (Zend_Db_Table_Exception $e) {
            return 'HistoricoDevolucaoFiscalizacao -> alteraHistoricoDevolucaoFiscalizacao. Erro:' . $e->getMessage();
        }
    }

}

// fecha class