<?php
/**
 * DAO tbDepositoIdentificadoCaptacao 
 * @author emanuel.sampaio - Politec
 * @since 30/06/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbDepositoIdentificadoCaptacao extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "tbDepositoIdentificadoCaptacao";



	/**
	 * Método para ignorar a ausência da chave primária
	 */
	/*public function _setupPrimaryKey()
	{
		$this->_primary = "";
	}*/



	/**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o último id cadastrado)
	 */
	public function cadastrarDados($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarDados()
        
        /**
         * Executa a Procedure spDepositoIdentificadoCaptacao
         * @access public
         * @return null
         */
        public function DepositoIdentificadoCaptacao(){
            $sql ="exec SAC.dbo.spDepositoIdentificadoCaptacao";
            
            $db = Zend_Registry :: get('db');
            $db->setFetchMode(Zend_DB :: FETCH_OBJ);

            return $db->query($sql);
        }
        

} // fecha class