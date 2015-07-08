<?php
/**
 * DAO tbRetirarDePauta 
 * @author emanuel.sampaio - XTI
 * @since 16/01/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbRetirarDePauta extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "BDCORPORATIVO";
	protected $_schema  = "scSAC";
	protected $_name    = "tbRetirarDePauta";



	/**
	 * Método para buscar
	 * @access public
	 * @param integer $idPronac
	 * @return object
	 */
	public function buscarDados($where=array(), $order=array())
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this
			,array('idRetirarDePauta'
					,'MotivoRetirada'
					,'CAST(dsJustificativa AS TEXT) AS dsJustificativa'
					,'idPronac'
					,'idAgenteEnvio'
					,'CONVERT(CHAR(10), dtEnvio, 103) AS dtEnvio'
					,'idAgenteAnalise'
					,'CONVERT(CHAR(10), dtAnalise, 103) AS dtAnalise'
					,'tpAcao'
					,'stAtivo'));

		// adiciona quantos filtros foram enviados
		foreach ($where as $coluna => $valor)
		{
			$select->where($coluna, $valor);
		}

		// adicionando linha order ao select
		$select->order($order);

		return $this->fetchAll($select);
	} // fecha método buscarDados()

} // fecha class