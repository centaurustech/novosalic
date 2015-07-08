<?php
/**
 * Description of GerarPagamentoParecerista
 *
 * @author Tarcisio Angelo
 */ 
class ConfigurarPagamentoParecerista extends GenericModel {

    protected $_name = 'tbConfigurarPagamento';
    protected $_schema = 'dbo';
    protected $_banco = 'SAC';

    public function buscarConfiguracoes($where = array()) {

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('c'=> $this->_name),
                        array('c.idConfigurarPagamento',
                                'c.nrDespachoInicial',
                                'c.nrDespachoFinal',
                                'CONVERT(VARCHAR(10), c.dtConfiguracaoPagamento ,103) as dtConfiguracaoPagamento',
                                'c.stEstado',
                                'c.idUsuario')
        );
        
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        
//        xd($select->assemble());
        
        return $this->fetchAll($select);
    }
    
}

?>
