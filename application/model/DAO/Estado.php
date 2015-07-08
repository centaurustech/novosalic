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

class Estado extends Zend_Db_Table
{
	protected $_name = 'AGENTES.dbo.UF'; // nome da tabela



	/**
	 * Método para buscar os estados
	 * @access public
	 * @param void
	 * @return object $db->fetchAll($sql)
	 */
	public static function buscar()
	{
		$sql = "SELECT idUF AS id, Sigla AS descricao ";
		$sql.= "FROM AGENTES.dbo.UF ";
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

		return $db->fetchAll($sql);
	} // fecha buscar()



	/**
	 * Método para buscar os estados de acordo com a região
	 * @access public
	 * @param void
	 * @return object $db->fetchAll($sql)
	 */
	public static function buscarRegiao($regiao)
	{
		$sql = "SELECT idUF AS id, Descricao AS descricao 
			FROM AGENTES.dbo.UF 
			WHERE Regiao = '$regiao'
			ORDER BY Sigla";

		try
		{
			$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao buscar Estados: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha buscar()

} // fecha class