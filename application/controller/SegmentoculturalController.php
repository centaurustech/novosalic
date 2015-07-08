<?php
/**
 * SegmentoculturalController
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class SegmentoculturalController extends Zend_Controller_Action
{
	/**
	 * Método para buscar os segmentos culturais de uma área
	 * @param void
	 * @return void
	 */
	public function segmentoculturalAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

		// recebe o id via post
		$post = Zend_Registry::get('post');
		$id = (int) $post->id;

		// integração MODELO e VISÃO
		$this->view->segmentosculturais = Segmentocultural::buscar($id);
	}



	/**
	 * Método para buscar os segmentos culturais de uma área
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
		$this->view->combosegmentosculturais = Segmentocultural::buscarSegmento($id);
	} // fecha comboAction()
	/**
	 * Método para buscar os segmentos culturais de uma área
	 * Busca como XML para o AJAX
	 * @access public
	 * @param void
	 * @return void
	 */
	public function comboZAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

		// recebe o id via post
		$post = Zend_Registry::get('post');
		$id = (int) $post->id;

                // integração MODELO e VISÃO
		$Segmento = new Segmento();
		$this->view->combosegmentosculturais = $Segmento->combo(array('a.Codigo = ?' => $id), array('s.Descricao ASC'));
	} // fecha comboAction()
}