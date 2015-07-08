<?php
/**
 * Modelo Telefone
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Telefone extends Zend_Db_Table
{
	/**
	 * @var nome da tabela
	 */
	protected $_name = 'AGENTES.dbo.Telefones'; // nome da tabela



	/**
	 * Método para buscar todos os telefones de um conselheiro
	 * @access public
	 * @param integer $idAgente
	 * @return object $db->fetchAll($sql)
	 */
	public static function buscar($idAgente)
	{
		$sql = "SELECT * FROM AGENTES.dbo.Telefones WHERE idAgente =".$idAgente;
		$db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		return $db->fetchAll($sql);
	} // fecha método buscar()



	/**
	 * Método para cadastrar todos os telefones de um conselheiro
	 * @access public
	 * @param array $dados
	 * @return boolean
	 */
	public static function cadastrar($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		try
		{
			$inserir = $db->insert('AGENTES.dbo.Telefones', $dados);
			$db->closeConnection();
			return true;
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao cadastrar Telefones do Proponente: " . $e->getMessage();
			return false;
		}
	} // fim cadastrar()



	/**
	 * Método para excluir telefone de um conselheiro
	 * @access public
	 * @param integer $id
	 * @return object $db->fetchAll($sql)
	 */
	public static function excluir($id)
	{
		try
		{
			$sql = "DELETE FROM AGENTES.dbo.Telefones WHERE idTelefone = '$id'";

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao excluir Telefone do Proponente: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha excluir()



	/**
	 * Método para excluir todos os telefones de um conselheiro
	 * @access public
	 * @param integer $id
	 * @return object $db->fetchAll($sql)
	 */
	public static function excluirTodos($idAgente)
	{
		try
		{
			$sql = "DELETE FROM AGENTES.dbo.Telefones WHERE idAgente = ".$idAgente;

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
			$i = $db->query($sql);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao excluir Telefone: " . $e->getMessage();
		}
	} // fecha método excluirTodos()

} // fecha class