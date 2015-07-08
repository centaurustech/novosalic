<?php


/**
 * DAO RecursoXPlanilhaAprovacao
 * @author Equipe RUP - Politec
 * @since 27/07/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class RecursoXPlanilhaAprovacaoDAO extends Zend_Db_Table
{
	/* dados da tabela */
	protected $_schema  = "";
	protected $_name    = "SAC.dbo.tbRecursoXPlanilhaAprovacao";
	protected $_primary = "idRecurso";



	/**
	 * Método para cadastrar informações dos recursos na planilha de aprovação
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function cadastrar($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("SAC.dbo.tbRecursoXPlanilhaAprovacao", $dados);

		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		} 
	} // fecha método cadastrar()



	
	
	
	
	
	/**
	 * Método para alterar informações dos recursos na planilha de aprovação
	 * @access public
	 * @static
	 * @param array $dados
	 * @param integer $id
	 * @return bool
	 */
	public static function alterar($dados, $id)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$where   = "idRecurso = $id";
		$alterar = $db->update("SAC.dbo.tbRecursoXPlanilhaAprovacao", $dados, $where);

		if ($alterar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método alterar()

} // fecha class RecursoXPlanilhaAprovacaoDAO