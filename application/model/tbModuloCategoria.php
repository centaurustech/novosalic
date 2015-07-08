<?php 
/*
 * Classe: tbEditalModulo
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class tbModuloCategoria extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbModuloCategoria';
    
    public function inserirCategoria($dados){
        $insert = $this->insert($dados);
        return $insert; 
    }

}
?>