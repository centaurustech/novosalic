<?php
/**
 * DAO RealizarPrestacaoDeContas
 * @author Equipe RUP - Politec
 * @since 20/09/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class RealizarPrestacaoDeContasDAO extends Zend_Db_Table
{
	/* dados da tabela */
	protected $_schema  = "";
	protected $_name    = "";
	protected $_primary = "";



	/**
	 * Método para ...
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function cadastrar($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("", $dados);

		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		} 
	} // fecha método cadastrar()

} // fecha class RealizarPrestacaoDeContasDAO