<?php
/**
 * @author Caio Lucena <caioflucena@gmail.com>
 */
class ComprovantePagamento extends GenericModel
{
    protected $comprovantePagamento;
    protected $fornecedor;
    protected $item;
    protected $tipo;
    protected $numero;
    protected $serie;
    protected $dataEmissao;
    protected $arquivo;

    protected $comprovanteTipo;
    protected $comprovanteData;
    protected $comprovanteValor;
    protected $comprovanteNumero;
    protected $comprovanteJustificativa;

    /**
     * Zend Table
     */
    protected $_banco = 'bdcorporativo';
    protected $_schema = 'scSAC';
    protected $_name = 'tbComprovantePagamento';

    /**
     * 
     */
    public function __construct(
        $comprovantePagamento = null,
        $fornecedor = null,
        $item = null,
        $tipo = null,
        $numero = null,
        $serie = null,
        $dataEmissao = null,
        $arquivo = null,
        $comprovanteTipo = null,
        $comprovanteData = null,
        $comprovanteValor = null,
        $comprovanteNumero = null,
        $comprovanteJustificativa = null
    )
    {
        parent::__construct();
        $this->comprovantePagamento = $comprovantePagamento;
        $this->fornecedor = $fornecedor;
        $this->item = $item;
        $this->tipo = $tipo;
        $this->numero = $numero;
        $this->serie = $serie;
        $this->dataEmissao = $dataEmissao;
        $this->arquivo = $arquivo;
        $this->comprovanteTipo = $comprovanteTipo;
        $this->comprovanteData = $comprovanteData;
        $this->comprovanteValor = $comprovanteValor;
        $this->comprovanteNumero = $comprovanteNumero;
        $this->comprovanteJustificativa = $comprovanteJustificativa;
    }

    public function getComprovantePagamento()
    {
        return $this->comprovantePagamento;
    }

    public function getFornecedor()
    {
        return $this->fornecedor;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getTipoDocumento()
    {
        return $this->tipo;
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function getSerie()
    {
        return $this->serie;
    }

    public function getDataEmissao()
    {
        return $this->dataEmissao;
    }

    public function getArquivo()
    {
        return $this->arquivo;
    }

    public function getComprovanteTipo()
    {
        return $this->comprovanteTipo;
    }

    public function getComprovanteData()
    {
        return $this->comprovanteData;
    }

    public function getComprovanteValor()
    {
        return $this->comprovanteValor;
    }

    public function getComprovanteNumero()
    {
        return $this->comprovanteNumero;
    }

    public function getComprovanteJustificativa()
    {
        return $this->comprovanteJustificativa;
    }

    public function setComprovantePagamento($comprovantePagamento)
    {
        $this->comprovantePagamento = $comprovantePagamento;
    }

    public function setFornecedor($fornecedor)
    {
        $this->fornecedor = $fornecedor;
    }

    public function setItem($item)
    {
        $this->item = $item;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    public function setSerie($serie)
    {
        $this->serie = $serie;
    }

    public function setDataEmissao($dataEmissao)
    {
        $this->dataEmissao = $dataEmissao;
    }

    public function setArquivo($arquivo)
    {
        $this->arquivo = $arquivo;
    }

    public function setComprovanteTipo($comprovanteTipo)
    {
        $this->comprovanteTipo = $comprovanteTipo;
    }

    public function setComprovanteData($comprovanteData)
    {
        $this->comprovanteData = $comprovanteData;
    }

    public function setComprovanteValor($comprovanteValor)
    {
        $this->comprovanteValor = $comprovanteValor;
    }

    public function setComprovanteNumero($comprovanteNumero)
    {
        $this->comprovanteNumero = $comprovanteNumero;
    }

    public function setComprovanteJustificativa($comprovanteJustificativa)
    {
        $this->comprovanteJustificativa = $comprovanteJustificativa;
    }

    /**
     * 
     */
    public function inserirComprovantePagamento($data){
        $insert = $this->insert($data);
        return $insert;
    }

    /**
     *
     */
    public function alterarComprovantePagamento($data, $where){
        $update = $this->update($data, $where);
        return $update;
    }

    /**
     *
     */
    public function deletarComprovantePagamento($where){
        $delete = $this->delete($where);
        return $delete;
    }

    /**
     * 
     */
    private function validarCadastrar()
    {
        if (!$this->fornecedor) {
            throw new Exception('Fornecedor inválido.');
        }
        if (!$this->item) {
            throw new Exception('Item inválido.');
        }
        if (!$this->tipo) {
            throw new Exception('Comprovante inválido.');
        }
        if (!$this->numero) {
            throw new Exception('Número inválido.');
        }

        # validar periodo
        $itemModel = new PlanilhaAprovacao();
        $projetoModel = new Projetos();
        $projeto = $projetoModel->find($itemModel->find($this->item)->current()->IdPRONAC)->current();
        $dtInicioExecucao = new DateTime($projeto->DtInicioExecucao);
        $dtFimExecucao = new DateTime($projeto->DtFimExecucao);
        if (!$this->dataEmissao || ($this->dataEmissao < $dtInicioExecucao) || ($this->dataEmissao > $dtFimExecucao)) {
            throw new Exception('A data do documento deve estar dentro do período de execução do projeto.');
        }

        if (!$this->comprovanteTipo) {
            throw new Exception('Forma de pagamento inválida.');
        }
        if (!$this->comprovanteNumero) {
            throw new Exception('Número do comprovante inválido.');
        }
        if (!$this->comprovanteValor) {
            throw new Exception('Valor do item inválido.');
        }
    }

    /**
     * @todo usar objeto de agente quando disponivel (pesquisa)
     * @todo usar objeto de arquivo quando disponivel (cadastro)
     * @todo usar objeto de planilha comprovante quando disponivel (cadastro)
     */
    public function cadastrar()
    {
        $this->validarCadastrar();
        $this->comprovantePagamento = $this->insert(
            array(
                'idFornecedor' => $this->fornecedor,
                'tpDocumento' => $this->tipo,
                'nrComprovante' => $this->numero,
                'nrSerie' => $this->serie,
                'dtEmissao' => $this->dataEmissao->format('Y-m-d h:i:s'),
                'idArquivo' => $this->arquivo,
                'vlComprovacao' => $this->comprovanteValor,
                'dtPagamento' => $this->comprovanteData->format('Y-m-d h:i:s'),
                'dsJustificativa' => $this->comprovanteJustificativa,
                'tpFormaDePagamento' => $this->comprovanteTipo,
                'nrDocumentoDePagamento' => $this->comprovanteNumero,
            )
        );
        $this->comprovarPlanilhaCadastrar();
    }

    /**
     * 
     */
    public function atualizar()
    {
    	$this->validarCadastrar();

        $arquivoModel = new ArquivoModel();
        $arquivoModel->deletar($this->arquivo);
        $arquivoModel->cadastrar('arquivo');

        $this->update(
        	array(
        		'idFornecedor' => $this->fornecedor,
        		'tpDocumento' => $this->tipo,
        		'nrComprovante' => $this->numero,
        		'nrSerie' => $this->serie,
        		'dtEmissao' => $this->dataEmissao->format('Y-m-d h:i:s'),
        		'idArquivo' => $arquivoModel->getId(),
        		'vlComprovacao' => $this->comprovanteValor,
        		'dtPagamento' => $this->comprovanteData->format('Y-m-d h:i:s'),
        		'dsJustificativa' => $this->comprovanteJustificativa,
        		'tpFormaDePagamento' => $this->comprovanteTipo,
        		'nrDocumentoDePagamento' => $this->comprovanteNumero,
        	),
        	array('idComprovantePagamento = ?' => $this->comprovantePagamento)
        );
        $this->comprovarPlanilhaAtualizarStatus(4, $this->comprovantePagamento);
    }

    /**
     * 
     */
    public function deletar()
    {
        if (!$this->comprovantePagamento) {
            throw new Exception('Comprovante nao informado.');
        }
        $tbComprovantePagamentoxPlanilhaAprovacao = new ComprovantePagamentoxPlanilhaAprovacao();
        $tbComprovantePagamentoxPlanilhaAprovacao->delete(array('idComprovantePagamento = ?' => $this->comprovantePagamento));
        $vwAnexarComprovantes = new vwAnexarComprovantes();
        $vwAnexarComprovantes->excluirArquivo($this->comprovantePagamento);
        
        $tbComprovantePagamento = new ComprovantePagamento();
        $comprovantePagamentoRow = $tbComprovantePagamento->fetchRow(array('idComprovantePagamento = ?' => $this->comprovantePagamento));

        if ($comprovantePagamentoRow && $comprovantePagamentoRow->idFornecedorExterior) {
            $idfornecedorInvoice = $comprovantePagamentoRow->idFornecedorExterior;
        }
        $comprovantePagamentoRow->delete();
        if (isset($idfornecedorInvoice)) {
            $fornecedorInvoiceTable = new FornecedorInvoice();
            $fornecedorInvoiceTable->getAdapter()->getProfiler()->setEnabled(true);
            $fornecedorInvoiceTable->delete(array('idFornecedorExterior = ?' => $idfornecedorInvoice));
        }
    }

    /**
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function pesquisarComprovante($idComprovante)
    {
        $select = "SELECT
                    comp.*,
                    arq.nmArquivo,
                    convert(char(10), comp.dtEmissao, 103) as dtEmissao,
                    (
                        CASE pa.idProduto
                            WHEN 0 THEN ('Administração do projeto')
                            ELSE prod.Descricao
                        END
                    ) as produtonome,
                    pEtapa.Descricao as etapanome,
                    pit.Descricao as itemnome,
                    (
                        CASE tpFormaDePagamento 
                            WHEN 1 THEN ('Cheque')
                            WHEN 2 THEN ('Transferência Bancária')
                            WHEN 3 THEN ('Saque/Dinheiro')
                        END
                    ) as tipoFormaPagamentoNome,
                    (
                        CASE tpDocumento
                            WHEN 1 THEN ('Boleto Banc&aacute;rio')
                            WHEN 2 THEN ('Cupom Fiscal')
                            WHEN 3 THEN ('Nota Fiscal/Fatura')
                            WHEN 4 THEN ('Recibo de Pagamento')
                            WHEN 5 THEN ('Aut&ocirc;nomo')
                        END
                    ) as tipoDocumentoNome,
                    ROUND((pa.QtItem * pa.nrOcorrencia * pa.VlUnitario),2) AS valorAprovado,
                    (
                        SELECT sum(b1.vlComprovacao) AS vlPagamento
                        FROM BDCORPORATIVO.scSAC.tbComprovantePagamentoxPlanilhaAprovacao AS a1
                        INNER JOIN BDCORPORATIVO.scSAC.tbComprovantePagamento AS b1 ON (a1.idComprovantePagamento = b1.idComprovantePagamento)
                        INNER JOIN SAC.dbo.tbPlanilhaAprovacao AS c1 ON (a1.idPlanilhaAprovacao = c1.idPlanilhaAprovacao)
                        WHERE c1.stAtivo = 'S' AND c1.idPlanilhaAprovacao = pa.idPlanilhaAprovacao
                        GROUP BY a1.idPlanilhaAprovacao
                    ) AS valorComprovado, cpxpa.dsJustificativa as JustificativaTecnico
                FROM bdcorporativo.scSAC.tbComprovantePagamento AS comp
                    INNER JOIN bdcorporativo.scSAC.tbComprovantePagamentoxPlanilhaAprovacao AS cpxpa ON cpxpa.idComprovantePagamento = comp.idComprovantePagamento
                    INNER JOIN SAC.dbo.tbPlanilhaAprovacao AS pa ON pa.idPlanilhaAprovacao = cpxpa.idPlanilhaAprovacao
                    INNER JOIN SAC.dbo.tbPlanilhaItens AS pit ON pit.idPlanilhaItens = pa.idPlanilhaItem
                    INNER JOIN SAC.dbo.tbPlanilhaEtapa AS pEtapa ON pa.idEtapa = pEtapa.idPlanilhaEtapa 
                    INNER JOIN BDCORPORATIVO.scCorp.tbArquivo as arq ON arq.idArquivo = comp.idArquivo
                    LEFT JOIN SAC.dbo.Produto AS prod ON pa.idProduto = prod.Codigo
                WHERE 
                    cpxpa.idComprovantePagamento = ?";
        $statement = $this->getAdapter()->query($select, array($idComprovante));
        return $statement->fetchAll();
    }

    /**
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function pesquisarComprovantePorItem($item)
    {
        $select = "SELECT
                    comp.*,
                    arq.nmArquivo,
                    convert(char(10), comp.dtEmissao, 103) as dtEmissao,
                    (
                        CASE pa.idProduto
                            WHEN 0 THEN ('Administração do projeto')
                            ELSE prod.Descricao
                        END
                    ) as produtonome,
                    pEtapa.Descricao as etapanome,
                    pit.Descricao as itemnome,
                    (
                        CASE tpFormaDePagamento 
                            WHEN 1 THEN ('Cheque')
                            WHEN 2 THEN ('Transferência Bancária')
                            WHEN 3 THEN ('Saque/Dinheiro')
                        END
                    ) as tipoFormaPagamentoNome,
                    (
                        CASE tpDocumento
                            WHEN 1 THEN ('Boleto Banc&aacute;rio')
                            WHEN 2 THEN ('Cupom Fiscal')
                            WHEN 3 THEN ('Guia de Recolhimento')
                            WHEN 4 THEN ('Nota Fiscal/Fatura')
                            WHEN 5 THEN ('Recibo de Pagamento')
                            WHEN 6 THEN ('RPA')
                        END
                    ) as tipoDocumentoNome,
                    ROUND((pa.QtItem * pa.nrOcorrencia * pa.VlUnitario),2) AS valorAprovado,
                    (
                        SELECT sum(b1.vlComprovacao) AS vlPagamento
                        FROM BDCORPORATIVO.scSAC.tbComprovantePagamentoxPlanilhaAprovacao AS a1
                        INNER JOIN BDCORPORATIVO.scSAC.tbComprovantePagamento AS b1 ON (a1.idComprovantePagamento = b1.idComprovantePagamento)
                        INNER JOIN SAC.dbo.tbPlanilhaAprovacao AS c1 ON (a1.idPlanilhaAprovacao = c1.idPlanilhaAprovacao)
                        WHERE c1.stAtivo = 'S' AND c1.idPlanilhaAprovacao = pa.idPlanilhaAprovacao
                        GROUP BY a1.idPlanilhaAprovacao
                    ) AS valorComprovado
                FROM bdcorporativo.scSAC.tbComprovantePagamento AS comp
                    INNER JOIN bdcorporativo.scSAC.tbComprovantePagamentoxPlanilhaAprovacao AS cpxpa ON cpxpa.idComprovantePagamento = comp.idComprovantePagamento
                    INNER JOIN SAC.dbo.tbPlanilhaAprovacao AS pa ON pa.idPlanilhaAprovacao = cpxpa.idPlanilhaAprovacao
                    INNER JOIN SAC.dbo.tbPlanilhaItens AS pit ON pit.idPlanilhaItens = pa.idPlanilhaItem
                    INNER JOIN SAC.dbo.tbPlanilhaEtapa AS pEtapa ON pa.idEtapa = pEtapa.idPlanilhaEtapa 
                    INNER JOIN BDCORPORATIVO.scCorp.tbArquivo as arq ON arq.idArquivo = comp.idArquivo
                    LEFT JOIN SAC.dbo.Produto AS prod ON pa.idProduto = prod.Codigo
                WHERE 
                    pa.idPlanilhaAprovacao = ?
                    AND pa.stAtivo = 'S'
                ORDER BY prod.Descricao ASC";
        $statement = $this->getAdapter()->query($select, array($item));
        return $statement->fetchAll();
    }

    /**
     * 
     */
    public function pesquisarComprovanteRecusado($idPronac)
    {
        $select = "SELECT top 50 a.*,
                    (
                        CASE c.idProduto
                            WHEN 0 THEN ('Administração do projeto')
                            ELSE f.Descricao
                        END
                    ) as produto, b.*, d.Descricao as etapa, e.Descricao as item
            FROM bdcorporativo.scSAC.tbComprovantePagamentoxPlanilhaAprovacao AS a
            INNER JOIN bdcorporativo.scSAC.tbComprovantePagamento AS b ON b.idComprovantePagamento = a.idComprovantePagamento
            INNER JOIN SAC.dbo.tbPlanilhaAprovacao AS c ON c.idPlanilhaAprovacao = a.idPlanilhaAprovacao
            INNER JOIN SAC.dbo.tbPlanilhaEtapa AS d ON d.idPlanilhaEtapa = c.idEtapa 
            INNER JOIN SAC.dbo.tbPlanilhaItens AS e ON e.idPlanilhaItens = c.idPlanilhaItem
            LEFT JOIN SAC.dbo.Produto AS f ON f.Codigo = c.idProduto
            WHERE 
                stItemAvaliado = 3
                AND IdPRONAC = ?
            ORDER BY 1 DESC";
        $statement = $this->getAdapter()->query($select, array($idPronac));
        return $statement->fetchAll();
    }

    /**
     * 
     */
    public function toStdclass()
    {
        return (object) array(
            'comprovantePagamento' => $this->comprovantePagamento,
        );
    }

    /**
     * @todo remover esse metodo apos implementacao ideal planilha comprovacao
     */
    protected function comprovarPlanilhaCadastrar()
    {
        $comprovantePlanilha = new ComprovantePagamentoxPlanilhaAprovacao();
        $comprovantePlanilha->insert(
            array(
                'idComprovantePagamento' => $this->comprovantePagamento,
                'idPlanilhaAprovacao' => $this->item,
                'vlComprovado' => $this->comprovanteValor,
            )
        );
    }

    /**
     * @todo remover esse metodo apos implementacao ideal planilha comprovacao
     */
    private function comprovarPlanilhaAtualizarStatus($status, $idComprovantePagamento)
    {
        $comprovantePlanilha = new ComprovantePagamentoxPlanilhaAprovacao();
        $comprovantePlanilha->update(
            array('stItemAvaliado' => $status), //aguardando analise
            array('idComprovantePagamento = ?' => $idComprovantePagamento,)
        );
    }
}
