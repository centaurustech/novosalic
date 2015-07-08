<?php
/**
 * DAO tbMovimentacaoBancariaItemxTipoInconsistencia 
 * @author emanuel.sampaio - Politec
 * @since 17/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbMovimentacaoBancariaItemxTipoInconsistencia extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "tbMovimentacaoBancariaItemxTipoInconsistencia";



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