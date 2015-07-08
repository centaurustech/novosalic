<?php
/**
 * DAO sInformacaoReceitaFederalV3 
 * @author emanuel.sampaio - Politec
 * @since 29/07/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class sInformacaoReceitaFederalV3 extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "sInformacaoReceitaFederalV3";



	/**
	 * Método para executar a SP de geração de DBF para a Receita Federal
	 * @access public
	 * @param integer $ano
	 * @return bool
	 */
	public function gerarDBF ($ano)
	{
		try
		{
			// executa a sp
			$executar = "EXEC " . $this->_banco . "." . $this->_schema . "." . $this->_name . " " . $ano;
			return $this->getAdapter()->query($executar);
		}
		catch (Zend_Exception $e)
		{
			return $e->getMessage();
		}
	} // fecha método gerarDBF()

} // fecha class