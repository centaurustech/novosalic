<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tbVerificaProjeto
 *
 * @author tiago
 */
class tbVerificaProjeto extends GenericModel{
    
    protected $_banco = 'SAC';
    protected $_name = 'tbVerificaProjeto';
    protected $_schema  = 'dbo';
    
    public function salvar($dados)
    {
        
        //INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $tmpTblVerificaProjeto = new tbVerificaProjeto();



        if(isset($dados['idVerificaProjeto'])){
            $tmpRsVerificaProjeto = $tmpTblVerificaProjeto->find($dados['idVerificaProjeto'])->current();
        }else{
            $tmpRsVerificaProjeto = $tmpTblVerificaProjeto->createRow();
        }

        //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS
        if(isset($dados['IdPRONAC'])){ $tmpRsVerificaProjeto->IdPRONAC = $dados['IdPRONAC']; }


        echo "<pre>";
        

        //SALVANDO O OBJETO CRIADO
        $id = $tmpRsVerificaProjeto->save();

        if($id){
            return $id;
        }else{
            return false;
        }
    }
    
}

?>
