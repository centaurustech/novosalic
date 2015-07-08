<?php
/**
 * DAO Dbf 
 * @author emanuel.sampaio - Politec
 * @since 29/07/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Dbf extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "Dbf";



	/**
	 * Método para ignorar a ausência da chave primária
	 */
	public function _setupPrimaryKey()
	{
		$this->_primary = "";
	}



	/**
	 * Método para buscar as informações do arquivo DBF
	 * @access public
	 * @param void
	 * @return object
	 */
	public function buscarInformacoes()
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(array($this->_name), array("Informacao"));
		$select->order("Informacao ASC");
		return $this->fetchAll($select);
	} // fecha método buscarInformacoes()

} // fecha class