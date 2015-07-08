<?php
/**
 * MunicipiosController
 * @author Emanuel Sampaio <emanuelonline@gmail.com>
 * @since 18/04/2012
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */

class MunicipiosController extends Zend_Controller_Action {
    /**
     * Método para buscar as cidades de um estado
     * Busca como XML para o AJAX
     * @access public
     * @param void
     * @return void
     */
    public function comboAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

        // recebe o idUF via post
        $post = Zend_Registry::get('post');
        $id = (int) $post->id;

        // integração MODELO e VISÃO
        $Municipios = new Municipios();
        $this->view->combocidades = $Municipios->combo(array('idUFIBGE = ?' => $id));
    } // fecha comboAction()


    public function comboAjaxAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

        // recebe o idUF via post
        $post = Zend_Registry::get('post');
        $id = (int) $post->id;

        // integração MODELO e VISÃO
        $Municipios = new Municipios();
        $result = $Municipios->combo(array('idUFIBGE = ?' => $id));

        $a = 0;
        if(count($result) > 0){
            foreach ($result as $registro) {
                $arrayMunicipios[$a]['id'] = $registro['id'];
                $arrayMunicipios[$a]['descricao'] = utf8_encode($registro['descricao']);
                $a++;
            }
            $jsonEncode = json_encode($arrayMunicipios);
            echo json_encode(array('resposta'=>true,'conteudo'=>$arrayMunicipios));

        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();

        
    } // fecha comboAction()
}