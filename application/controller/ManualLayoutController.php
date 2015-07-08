<?php
/**
 * Controller Manual Layout
 * (Exemplo de Utilização do Layout)
 * @author emanuel.sampaio - Politec
 * @since 27/05/2011
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://salic.cultura.gov.br
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 */

require_once "GenericControllerNew.php";

class ManualLayoutController extends GenericControllerNew
{
	/**
	 * Reescreve o método init()
	 * @access public
	 * @param void
	 * @return void
	 */
	public function init()
	{
		// configurações do layout padrão
		Zend_Layout::startMvc(array('layout' => 'layout_login'));

		parent::init(); // chama o init() do pai GenericControllerNew
	} // fecha método init()



	/**
	 * Página inicial
	 * @access public
	 * @param void
	 * @return void
	 */
	public function indexAction()
	{
	} // fecha método indexAction()



	/**
	 * Página Simples
	 * @access public
	 * @param void
	 * @return void
	 */
	public function paginaSimplesAction()
	{
	} // fecha método paginaSimplesAction()



	/**
	 * Página com Menu Lateral
	 * @access public
	 * @param void
	 * @return void
	 */
	public function paginaMenuLateralAction()
	{
	} // fecha método paginaMenuLateralAction()



	/**
	 * Menu Lateral
	 * @access public
	 * @param void
	 * @return void
	 */
	public function menuLateralAction()
	{
	} // fecha método menuLateralAction()



	/**
	 * Mensagens
	 * @access public
	 * @param void
	 * @return void
	 */
	public function mensagensAction()
	{
	} // fecha método mensagensAction()



	/**
	 * Menu de Abas
	 * @access public
	 * @param void
	 * @return void
	 */
	public function menuAbas1Action()
	{
	} // fecha método menuAbas1Action()

	public function menuAbas2Action()
	{
	} // fecha método menuAbas2Action()

	public function menuAbas3Action()
	{
	} // fecha método menuAbas3Action()

	public function menuAbas4Action()
	{
	} // fecha método menuAbas4Action()



	/**
	 * Formulários
	 * @access public
	 * @param void
	 * @return void
	 */
	public function formulariosAction()
	{
	} // fecha método formulariosAction()



	/**
	 * Máscaras em JavaScript
	 * @access public
	 * @param void
	 * @return void
	 */
	public function mascarasJsAction()
	{
	} // fecha método mascarasJsAction()



	/**
	 * Máscaras em PHP
	 * @access public
	 * @param void
	 * @return void
	 */
	public function mascarasPhpAction()
	{
	} // fecha método mascarasPhpAction()



	/**
	 * Tabelas
	 * @access public
	 * @param void
	 * @return void
	 */
	public function tabelasAction()
	{
	} // fecha método tabelasAction()



	/**
	 * Grids
	 * @access public
	 * @param void
	 * @return void
	 */
	public function gridsAction()
	{
	} // fecha método gridsAction()



	/**
	 * Planilha
	 * @access public
	 * @param void
	 * @return void
	 */
	public function planilhaAction()
	{
	} // fecha método planilhaAction()



	/**
	 * Modal
	 * @access public
	 * @param void
	 * @return void
	 */
	public function modalAction()
	{
	} // fecha método modalAction()

} // fecha class