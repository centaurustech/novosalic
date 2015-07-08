<?php
/**
 * CepController
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class CepController extends Zend_Controller_Action
{
	/**
	 * Método para buscar o endereço de acordo com o cep informado
	 * @access public
	 * @param void
	 * @return void
	 */
	public function cepAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

		// recebe o cep sem máscara vindo via ajax
		$get = Zend_Registry::get('get');
		$cep = Mascara::delMaskCEP(Seguranca::tratarVarAjaxUFT8($get->cep));

		/*
		$resultado = Cep::buscar($cep); // busca o cep no web service

		switch($resultado['resultado'])
		{
			// cidades com cep único
			case '2':
				$_end         = "";
				$_complemento = "";
				$_bairro      = "";
				$_cidade      = $resultado['cidade'];
				$_uf          = $resultado['uf'];
			break;

			// demais cidades
			case '1': 
				$_end         = $resultado['logradouro'];
				$_complemento = $resultado['tipo_logradouro'];
				$_bairro      = $resultado['bairro'];
				$_cidade      = $resultado['cidade'];
				$_uf          = $resultado['uf'];
			break;

			default:
				$_end         = "";
				$_complemento = "";
				$_bairro      = "";
				$_cidade      = "";
				$_uf          = "";
			break;
		} // fecha switch

		if ($_cidade == "" && $_uf == "")
		{
			$buscarCEP = "";
		}
		else
		{
			$buscarCEP = $_end . ":" . $_complemento . ":" . $_bairro . ":" . $_cidade . ":" . $_uf . ";";
		} */

		$resultado    = Cep::buscarCepDB($cep); // busca o cep no banco de dados

		if ($resultado) // caso encontre o cep
		{
			$_end         = $resultado['logradouro'];
			$_complemento = $resultado['tipo_logradouro'];
			$_bairro      = $resultado['bairro'];
			$_uf          = $resultado['uf'];

			// atribuição da cidade
			if (empty($resultado['idCidadeMunicipios']) || empty($resultado['dsCidadeMunicipios']))
			{
				// caso a cidade não exista na tabela de municipios (tabela associada aos agentes)
				// pega a primeira cidade do estado
				$_cod_cidade = $resultado['idCidadeUF'];
				$_cidade     = $resultado['dsCidadeUF'];
			}
			else
			{
				// caso a cidade exista na tabela de municipios (tabela associada aos agentes)
				// pega a cidade da tabela de municipios
				$_cod_cidade = $resultado['idCidadeMunicipios'];
				$_cidade     = $resultado['dsCidadeMunicipios'];
			}

			$buscarCEP = $_end . ":" . $_complemento . ":" . $_bairro . ":" . $_cod_cidade . ":" . $_cidade . ":" . $_uf . ";";
		} // fecha if
		else // caso não ache o cep
		{
			$buscarCEP = "";
		}

		$this->view->cep = $buscarCEP;
	} // fecha cepAction()

} // fecha class