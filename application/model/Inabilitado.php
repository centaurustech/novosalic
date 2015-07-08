<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vinculo
 *
 * @author tisomar
 */
class Inabilitado extends GenericModel {

    protected $_banco = "SAC";
    protected $_name = "Inabilitado";

    public function salvar($dados)
    {
        //INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $tabela = new Inabilitado();
        
	$insert = $tabela->insert($dados); // cadastra
        return $insert;
    }

    public function BuscarInabilitado($CgcCpf, $AnoProjeto, $Sequencial) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('In' => $this->_name)
        );
        //$slct->where('CgcCpf 	 = ?', $CgcCpf);
        $slct->where('AnoProjeto = ?', $AnoProjeto);
        $slct->where('Sequencial = ?', $Sequencial);

//xd($slct->assemble());
        return $this->fetchRow($slct);
    }
    
    public function Localizar($where) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
	                array('I' => $this->_name),
	                array(	'CgcCpf',
	                		'AnoProjeto',
	                		'Sequencial',
	                		'Orgao',
	                		'Logon',
	                		'Habilitado',
	                		'idProjeto',
	                		'idTipoInabilitado',
	                		'dtInabilitado',
	                	  	'DATEDIFF(DAY, dtInabilitado, GETDATE()) / 365 AS Anos')
        );
        
    	//adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
         //xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    public function updateTbl($dados){
    
        //INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $tmpTbl = new Inabilitado();

        $sql = "UPDATE ".$this->_name." set ";

        $sql = "UPDATE SAC.dbo.Inabilitado SET 
                         	Logon 			= '".$dados['Logon']."',
    			 			Habilitado 		= '".$dados['Habilitado']."',
    			 			Orgao 			= '".$dados['Orgao']."'
    			 			WHERE  
    			 			AnoProjeto 		= '".$dados['AnoProjeto']."'
                         	AND Sequencial 	= '".$dados['Sequencial']."'";
		
        
        //Retirado, não pode ter mais de um registro de um único projeto
        //AND CgcCpf 		= '".$dados['CgcCpf']."'
        
        //xd($sql);
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
        $db->query($sql);
    }

    
    public function listainabilitados($CNPJCPF){
    

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('I' => $this->_name)
        );
        
        $select->joinInner(
                array('Projetos'),
                "Projetos.AnoProjeto = I.AnoProjeto AND Projetos.Sequencial = I.Sequencial",
                array('*')
        );
        
        $select->joinInner(
                array('Orgaos'),
                "Projetos.Orgao = Orgaos.Codigo",
                array('*')
        );
        
        
        $select->where("I.CgcCpf = ?", $CNPJCPF);

        return $this->fetchAll($select);


    }



}
?>
