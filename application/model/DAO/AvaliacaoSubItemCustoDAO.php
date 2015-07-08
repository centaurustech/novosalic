<?php
/**
 * DAO AvaliacaoSubItemCusto
 * @author Equipe RUP - Politec
 * @since 28/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class AvaliacaoSubItemCustoDAO extends Zend_Db_Table
{
	/* dados da tabela */
	protected $_schema  = "";
	protected $_name    = "BDCORPORATIVO.scSAC.tbAvaliacaoSubItemCusto";
	protected $_primary = "idAvaliacaoSubItemCusto";



	/**
	 * Método para cadastrar
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function cadastrar($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("BDCORPORATIVO.scSAC.tbAvaliacaoSubItemCusto", $dados);

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
	 * Método para verificar se o registro existe
	 * @access public
	 * @static
	 * @param integer $idPlanilhaAprovacao
	 * @return object || bool
	 */
	public static function verificar($idPlanilhaAprovacao)
	{
		$sql = "SELECT * FROM BDCORPORATIVO.scSAC.tbAvaliacaoSubItemCusto WHERE idPlanilhaAprovacao = $idPlanilhaAprovacao";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método verificar()

} // fecha class AvaliacaoSubItemCustoDAO