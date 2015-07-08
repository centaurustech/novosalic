<?php
/**
 * CidadeController
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class CidadeController extends Zend_Controller_Action
{
	/**
	 * Método para buscar as cidades de um estado
	 * @param void
	 * @return void
	 */
	public function cidadeAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

		// recebe o id via post
		$post = Zend_Registry::get('post');
		$id = (int) $post->id;

		// integração MODELO e VISÃO
		$this->view->cidades = Cidade::buscar($id);
	}



	/**
	 * Método para buscar as cidades de um estado
	 * Busca como XML para o AJAX
	 * @access public
	 * @param void
	 * @return void
	 */
	public function comboAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

		// recebe o id via post
		$post = Zend_Registry::get('post');
		$id = (int) $post->id;

		// integração MODELO e VISÃO
		$this->view->combocidades = Cidade::buscar($id);
	} // fecha comboAction()
}