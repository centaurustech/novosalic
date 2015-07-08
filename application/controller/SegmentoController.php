<?php
/**
 * SegmentoController
 * @author Emanuel Sampaio <emanuelonline@gmail.com>
 * @since 24/04/2012
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */

class SegmentoController extends Zend_Controller_Action
{
	/**
	 * Método para buscar os segmentos de uma área
	 * Busca como XML para o AJAX
	 * @access public
	 * @param void
	 * @return void
	 */
	public function comboAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

		// recebe o idUF via post
		$post = Zend_Registry::get('post');
		$idArea = (int) $post->id;
		$idSegmento = isset($post->segmento) ? $post->segmento : 0;

		// integração MODELO e VISÃO
		$Segmento = new Segmento();
		$resultado = $Segmento->combo(array("a.Codigo = '?'" => $idArea), array('s.Segmento ASC'));
                if(count($resultado)>0){
                    $html = '<option value=""> - Selecione - </option>';
                    foreach ($resultado as $value) {
                        if($idSegmento > 0 && $idSegmento == $value->id){
                            $html = $html.'<option value="'.$value->id.'" selected="selected">'.utf8_encode($value->descricao).'</option>';
                        } else {
                            $html = $html.'<option value="'.$value->id.'">'.utf8_encode($value->descricao).'</option>';
                        }
                    }
                    echo $html;
                } else {
                    echo '<option value=""> - Selecione - </option>';
                }
                die;
	} // fecha comboAction()
}