<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paVoltarProjetoFinalizadoComponente
 *
 * @author 01129075125
 */
class paPropostaParaProjeto extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'paPropostaParaProjeto';

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
