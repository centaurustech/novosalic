<?php
include_once 'GenericController.php';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImprimirController
 *
 * @author tisomar
 */
class ImprimirController extends GenericControllerNew {




    public function init() {
        // verifica as permissões
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 93;  // Coordenador de Parecerista
        $PermissoesGrupo[] = 94;  // Parecerista
        $PermissoesGrupo[] = 121; // Técnico
        $PermissoesGrupo[] = 122; // Coordenador de Acompanhamento
        parent::perfil(1, $PermissoesGrupo);

        parent::init();
        // chama o init() do pai GenericControllerNew
    }


    public function indexAction()
    {

        
    }

    public function imprimirAction()
    {

        
         
    }


    
}
?>
