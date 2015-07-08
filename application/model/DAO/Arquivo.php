<?php
/**
 * Modelo Arquivo
 * @author Equipe RUP - Politec
 * @since 30/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.models
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Arquivo
{
	/**
	 * Método para buscar os tipos de rquivo
	 * @access public
	 * @param void
	 * @return object
	 */
	public static function buscar()
	{
		$sql = "SELECT idArquivo";
		$sql.= "FROM BDCORPORATIVO.scSAC.tbComprovanteExecucao ";
		

		try
		{
			$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao buscar os Tipos de Documentos: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha método buscar()

} // fecha class
?>