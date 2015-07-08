<?php
/**
 * Modelo Tipoemail
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.models
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Tipoemail extends Zend_Db_Table
{
	protected $_name = 'AGENTES.dbo.Verificacao'; // nome da tabela



	/**
	 * Método para buscar todos os tipos de e-mails
	 * @access public
	 * @param void
	 * @return object $db->fetchAll($sql)
	 */
	public static function buscar()
	{
		$sql = "SELECT idVerificacao AS id, Descricao AS descricao ";
		$sql.= "FROM AGENTES.dbo.Verificacao ";
		$sql.= "WHERE idTipo = 4 ";
		$sql.= "	AND (idVerificacao = 28 OR idVerificacao = 29) ";
		$sql.= "ORDER BY Descricao;";

		try
		{
			$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao buscar Tipos de E-mails: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha buscar()
} // fecha class