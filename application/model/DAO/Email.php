<?php
/**
 * Modelo Email
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Email extends Zend_Db_Table
{
	/**
	 * @var nome da tabela
	 */
	protected $_name = 'AGENTES.dbo.Internet'; // nome da tabela



	/**
	 * Método para buscar todos os e-mails de um conselheiro
	 * @access public
	 * @param integer $idAgente
	 * @return object $db->fetchAll($sql)
	 */
	public static function buscar($idAgente)
	{
		$sql = "SELECT * ";
		$sql.= "FROM AGENTES.dbo.Internet ";
		$sql.= "WHERE idAgente = '" . $idAgente . "'";

		try
		{
			$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao buscar E-mails do Proponente: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha buscar()



	/**
	 * Método para cadastrar todos os e-mails de um conselheiro
	 * @access public
	 * @param array $dados
	 * @return boolean
	 */
	public static function cadastrar($dados)
	{
		$db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		try
		{
			$inserir = $db->insert('AGENTES.dbo.Internet', $dados);
			$db->closeConnection();
			return true;
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao cadastrar E-mails do Proponente: " . $e->getMessage();
			return false;
		}
	} // fecha cadastrar()



	/**
	 * Método para excluir e-mail de um conselheiro
	 * @access public
	 * @param integer $id
	 * @return object $db->fetchAll($sql)
	 */
	public static function excluir($id)
	{
		try
		{
			$sql = "DELETE FROM AGENTES.dbo.Internet WHERE idInternet = '$id'";

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao excluir E-mail do Proponente: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha método excluir()



	/**
	 * Método para excluir todos os emails de um conselheiro
	 * @access public
	 * @param integer $id
	 * @return object $db->fetchAll($sql)
	 */
	public static function excluirTodos($idAgente)
	{
		try
		{
			$sql = "DELETE FROM AGENTES.dbo.Internet WHERE idAgente =".$idAgente;

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
			$i = $db->query($sql);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao excluir E-mail do Proponente: " . $e->getMessage();
		}
	} // fecha método excluirTodos()

} // fecha class