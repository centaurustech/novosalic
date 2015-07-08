<?php
/**
 * DAO tbTipoInconsistencia 
 * @author emanuel.sampaio - Politec
 * @since 17/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbTipoInconsistencia extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "tbTipoInconsistencia";



	/**
	 * Método para buscar
	 * @access public
	 * @param void
	 * @return object/array
	 */
	public function buscarDados()
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this);
		$select->order('idTipoInconsistencia');
		return $this->fetchAll($select);
	} // fecha método buscarDados()

} // fecha class