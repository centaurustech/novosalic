<?php
/**
 * Description of ArquivoPagamentoParecerista
 *
 * @author Tarcisio Angelo
 */
class ArquivoPagamentoParecerista extends GenericModel {
 
    protected $_name = 'tbPagamentoPareceristaXArquivo';
    protected $_schema = 'dbo';
    protected $_banco = 'SAC';
    protected $_primary = 'idArquivo';

    public function inserirArquivodePagamento($idGerarPagamentoParecerista, $idArquivo, $siArquivo){
        
        $sql = "INSERT INTO ".$this->_banco.".".$this->_schema.".".$this->_name." (idGerarPagamentoParecerista,idArquivo,siArquivo)
                values ($idGerarPagamentoParecerista, $idArquivo,'$siArquivo')";
        
        $db  = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->query($sql);
        
    }


    public function buscarArquivo($where) {

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('arqpa'=>$this->_name),
                        array('arqpa.idGerarPagamentoParecerista','arqpa.siArquivo')
        );
        
        $select->joinInner(array('arq'=>'tbArquivo'), "arq.idArquivo = arqpa.idArquivo",
                            array('arq.idArquivo',
                                  'arq.nmArquivo',
                                  'arq.sgExtensao',
                                  'arq.dtEnvio',
                                  'nrTamanho'),'BDCORPORATIVO.scCorp'
        );
        
        $select->joinInner(array('aim'=>'tbArquivoImagem'), "arq.idArquivo = aim.idArquivo",
                            array('aim.biArquivo'),'BDCORPORATIVO.scCorp'
        );

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        
        $select->order('arq.dtEnvio');
//        xd($select->assemble());
        
        return $this->fetchAll($select);
    }
}

?>
