<?php
/**
 * Description of GerarPagamentoParecerista
 *
 * @author Tarcisio Angelo
 */
class GerarPagamentoParecerista extends GenericModel {
 
    protected $_name = 'tbGerarPagamentoParecerista';
    protected $_schema = 'dbo';
    protected $_banco = 'SAC';

    public function buscarDespachos($where = array()) {

        $select = $this->select()->distinct();
        $select->setIntegrityCheck(false);
        $select->from(array('gpp'=>$this->_name),
                        array('gpp.idGerarPagamentoParecerista',
                                'gpp.idConfigurarPagamento',
                                'CONVERT(VARCHAR(10), gpp.dtGeracaoPagamento ,103) as dtGeracaoPagamento',
                                'CONVERT(VARCHAR(10), gpp.dtEfetivacaoPagamento ,103) as dtEfetivacaoPagamento',
                                'CONVERT(VARCHAR(10), gpp.dtOrdemBancaria ,103) as dtOrdemBancaria',
                                'gpp.nrOrdemBancaria',
                                'gpp.nrDespacho',
                                'gpp.siPagamento',
                                'gpp.vlTotalPagamento',
                                'gpp.idUsuario')
        );
        
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        
//        xd($select->assemble());
        
        return $this->fetchAll($select);
    }

    public function ultimoDespachoDoAno()
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('gpp'=>$this->_name), array('max(convert(int,(nrDespacho))) as UltimoDespachoDoAno'));
        $select->where('YEAR([dtGeracaoPagamento]) = ?', new Zend_Db_Expr('YEAR(GETDATE())'));
        return $this->fetchRow($select);
    }

    public function buscarProjetosFinalizados($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pro' => 'Projetos'),
                        array('Pronac' => New Zend_Db_Expr('pro.AnoProjeto + pro.Sequencial'),
                              'sac.dbo.fnNome(pp.idParecerista) as Parecerista',
                              'pro.IdPRONAC as idPronac',
                              'pro.NomeProjeto')
        );
        
        
        $select->joinInner(array('pp'=> 'tbPagarParecerista'), "pro.IdPRONAC = pp.idPronac",
                            array('pp.vlPagamento')
        );
        
        $select->joinInner(array('a'=> 'Agentes'), "pp.idParecerista = a.idAgente",
                            array('a.CNPJCPF'),'AGENTES.dbo'
        );
        
        $select->joinInner(array('p'=> 'Produto'), "pp.idProduto = p.Codigo",
                            array('p.Descricao as Produto')
        );
        
        $select->joinInner(array('o'=> 'Orgaos'), "pp.idUnidadeAnalise = o.Codigo",
                            array('o.Sigla as Vinculada')
        );
        
        $select->joinLeft(array('gpp'=> 'tbGerarPagamentoParecerista'), "pp.idGerarPagamentoParecerista = gpp.idGerarPagamentoParecerista",
                            array('CONVERT(VARCHAR(10), gpp.dtGeracaoPagamento ,103) as dtGeracaoPagamento',
                                  'CONVERT(VARCHAR(10), gpp.dtEfetivacaoPagamento ,103) as dtEfetivacaoPagamento',
                                  'CONVERT(VARCHAR(10), gpp.dtOrdemBancaria ,103) as dtOrdemBancaria',
                                  'gpp.nrOrdemBancaria',
                                  'gpp.nrDespacho',
                                  'gpp.siPagamento as Estado')
        );

        $siArquivo = 1;
        if (isset($where['gpp.siPagamento = ?']) && !empty($where['gpp.siPagamento = ?'])) {
            if (5 == $where['gpp.siPagamento = ?']) {
                $siArquivo = 2;
            }
        }
        $select->joinLeft(
            array('pa'=>'tbPagamentoPareceristaXArquivo'),
                "pa.idGerarPagamentoParecerista = gpp.idGerarPagamentoParecerista and pa.siArquivo = {$siArquivo}",
            array(),
                'SAC.dbo'
        );
        $select->joinLeft(
            array('arq'=>'tbArquivo'),
            "arq.idArquivo = pa.idArquivo",
            array('arq.idArquivo', 'nomeArquivo' => 'arq.nmArquivo'),
            'BDCORPORATIVO.scCorp'
        );

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        
        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }
        
        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }
        
        
//        xd($select->assemble());
        
        return $this->fetchAll($select);
    }
    
    
}

?>
