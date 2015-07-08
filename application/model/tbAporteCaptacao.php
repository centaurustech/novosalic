<?php
/**
 * DAO tbAporteCaptacao
 * @since 15/04/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */
class tbAporteCaptacao extends GenericModel
{
	/**
	 * Numero do lote usado para representar aportes de depositos equivocados
	 */
	const DEPOSITO_EQUIVOCADO_NRLOTE = -1;

	/**
	 * 
	 */
    protected $_banco   = "SAC";
    protected $_schema  = "dbo";
    protected $_name    = "tbAporteCaptacao";

    /**
     * 
     */
    public function pesquisarDepositoEquivocado(array $where)
    {
    	$select = $this->select()->setIntegrityCheck(false);
    	$select->from($this->_name, array('*'));
    	$select->joinInner(array('i' => 'Interessado'), 'tbAporteCaptacao.CNPJCPF = i.CgcCPf', array('*'), 'SAC.dbo');
    	$select->joinInner(array('a' => 'agentes'), 'a.CNPJCPf = i.CgcCPf', array('*'), 'Agentes.dbo');
    	$select->where('nrLote = ?', self::DEPOSITO_EQUIVOCADO_NRLOTE);
    	foreach ($where as $key => $value) {
    		$select->where($key, $value);
    	}
    	return $this->fetchAll($select);
    }
    
    /**
     * 
     */
    public function pesquisarDevolucoesIncentivador(array $where, $dbg = false)
    {
    	$select = $this->select()->setIntegrityCheck(false);
    	$select->from($this->_name, array('*'));
    	$select->joinInner(array('i' => 'Interessado'), 'tbAporteCaptacao.CNPJCPF = i.CgcCPf', array('*'), 'SAC.dbo');
    	$select->joinInner(array('a' => 'agentes'), 'a.CNPJCPf = i.CgcCPf', array('*'), 'Agentes.dbo');
    	$select->where('idVerificacao = ?', Verificacao::DEVOLUCAO_FUNDO_NACIONAL_CULTURA);
    	foreach ($where as $key => $value) {
    		$select->where($key, $value);
    	}
        
        if($dbg){
            xd($select->assemble());
        }
        
    	return $this->fetchAll($select);
    }

    /**
     * 
     */
    public function cadastrarAporteCaptacaoPronac($idPronac, $idCaptacao, $idUsuario)
    {
        $tbTmpCaptacaoModel = new tbTmpCaptacao();
        $tbTmpInconsistenciaCaptacaoModel = new tbTmpInconsistenciaCaptacao();
    	#
    	$captacoes = $tbTmpCaptacaoModel->find($idCaptacao);
    	if (!$captacoes->count() || 1 < $captacoes->count()) {
    		throw new Exception('Captação inválida.');
    	}
    	$captacao = $captacoes->current();
    	if (!($captacao instanceof Zend_Db_Table_Row)) {
    		throw new Exception('Captação inválida.');
    	}
    	#
        $contaBancariaModel = new ContaBancaria();
        $contasBancarias = $contaBancariaModel->buscar(array(
        	'c.AnoProjeto = ?' => $captacao->nrAnoProjeto,
        	'c.Sequencial = ?' => $captacao->nrSequencial,
        ));
    	if (!$contasBancarias->count() || 1 < $contasBancarias->count()) {
    		throw new Exception('Conta bancária inválida.');
    	}
    	$contaBancaria = $contasBancarias->current();
    	if (!($contaBancaria instanceof Zend_Db_Table_Row)) {
    		throw new Exception('Conta bancária inválida.');
    	}
		$this->getAdapter()->beginTransaction();
    	$this->inserir(array(
	    	'idPRONAC' => $idPronac,
	    	'idVerificacao' => Verificacao::DEVOLUCAO_FUNDO_NACIONAL_CULTURA,
	    	'CNPJCPF' => $captacao->nrCpfCnpjIncentivador,
    		'idContaBancaria' => $contaBancaria->IdContaBancaria,
    		'idUsuarioInterno' => $idUsuario,
    		'dtCredito' => ConverteData($captacao->dtCredito, 13),
    		'vlDeposito' => $captacao->vlValorCredito,
    		'nrLote' => self::DEPOSITO_EQUIVOCADO_NRLOTE,
    		'dtLote' => ConverteData(date('Y-m-d', time()), 13),
    	));
    	$tbTmpInconsistenciaCaptacaoModel->delete(array('idTmpCaptacao = ?' => $idCaptacao));
    	$tbTmpCaptacaoModel->delete(array('idTmpCaptacao = ?' => $idCaptacao));
    	#
    	$this->getAdapter()->commit();
    }
}