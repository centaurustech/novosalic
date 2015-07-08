<?php
/**
 * DAO tbHistoricoExclusaoConta
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbHistoricoExclusaoConta extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbHistoricoExclusaoConta";

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
	 * Método para buscar o relatório consolidado
	 * @access public
	 * @param array $dados
	 * @return array dos dados cadastrados
	 */
        
} // fecha class