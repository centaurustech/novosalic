<?php
/**
 * DAO tbMovimentacaoBancariaItem 
 * @author emanuel.sampaio - Politec
 * @since 17/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbMovimentacaoBancariaItem extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "tbMovimentacaoBancariaItem";



	/**
	 * Método para buscar
	 * @access public
	 * @param void
	 * @return object/array
	 */
	public function buscarDados()
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this);
		$select->order('dtAberturaConta');
		$select->order('dtMovimento');
		return $this->fetchAll($select);
	} // fecha método buscarDados()



	/**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o último id cadastrado)
	 */
	public function cadastrarDados($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarDados()



	/**
	 * Método para alterar
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $where)
	{
		$where = "idMovimentacaoBancariaItem = " . $where;
		return $this->update($dados, $where);
	} // fecha método alterarDados()



	/**
	 * Método para excluir
	 * @access public
	 * @param integer $where
	 * @return integer (quantidade de registros excluídos)
	 */
	public function excluirDados($where)
	{
		$where = "idMovimentacaoBancariaItem = " . $where;
		return $this->delete($where);
	} // fecha método excluirDados()

} // fecha class