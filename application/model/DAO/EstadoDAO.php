<?php
/**
 * Modelo Estado
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.models
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class EstadoDAO extends Zend_Db_Table
{
	protected $_name = 'AGENTES.dbo.UF'; // nome da tabela



	/**
	 * Método para buscar os estados
	 * @access public
	 * @param void
	 * @return object $db->fetchAll($sql)
	 */
	public static function buscar($id = null)
	{
		$sql = "SELECT idUF AS id, Sigla AS descricao ";
		$sql.= "FROM AGENTES.dbo.UF ";

		if (!empty($id))
		{
			$sql.= "WHERE idUF = {$id} ";
		}

		$sql.= "ORDER BY Sigla";

		try
		{
			$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao buscar Estados: " . $e->getMessage();
		}
		//xd($sql);
		return $db->fetchAll($sql);
	} // fecha buscar()
} // fecha class