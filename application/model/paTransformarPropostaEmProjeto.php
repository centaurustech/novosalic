<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paTransformarPropostaEmProjeto
 */
class paTransformarPropostaEmProjeto extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paTransformarPropostaEmProjeto';

    public function execSP($idProposta, $CNPJCPF, $idOrgao, $idUsuario){
        try{
        $rodar = "exec " . $this->_banco .".dbo.". $this->_name . ' ' . $idProposta .',"'. $CNPJCPF.'",'. $idOrgao.','. $idUsuario;
        return  $this->getAdapter()->query($rodar);
        }
        catch(Zend_Exception $e){
            return $e->getMessage();
        }
    }
}
?>
