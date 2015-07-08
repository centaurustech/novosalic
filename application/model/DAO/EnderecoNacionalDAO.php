<?php  

class EnderecoNacionalDAO extends Zend_Db_Table
{  

	protected $_name = 'AGENTES.dbo.EnderecoNacional';  
	
	
	public static function buscarEnderecoNacional($idAgente)
	{
		
		$sql = "Select  idEndereco, 
						idAgente, 
						TipoEndereco, 
						TipoLogradouro, 
						Logradouro, 
						Numero, 
						Bairro, 
						Complemento, 
						Cidade, 
						UF, 
						Cep, 
						Municipio, 
						UfDescricao , 
						Status, 
						Divulgar , 
						Usuario 
						From AGENTES.dbo.EnderecoNacional 
							Where idAgente = ".$idAgente;
		
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$dados =  $db->fetchAll($sql);	
		
		return $dados;
		
	}
	
	
	public static function gravarEnderecoNacional($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$i =  $db->insert('AGENTES.dbo.EnderecoNacional', $dados);	
	}


	public static function atualizaEnderecoNacional($idAgente, $dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$where = "idAgente=".$idAgente;
		$i =  $db->update('AGENTES.dbo.EnderecoNacional', $dados, $where);
	}
	
	public static function deletarEnderecoNacional($idEndereco)
	{
		try
		{
			$sql = "DELETE FROM AGENTES.dbo.EnderecoNacional WHERE idEndereco = ".$idEndereco;

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao excluir Telefone do Proponente: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} // fecha excluir()
	
	public static function mudaCorrespondencia($idAgente)
	{
		try
		{
			$sql = "UPDATE AGENTES.dbo.EnderecoNacional set Status = 0 WHERE idAgente = ".$idAgente;

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao alterar o Status dos endereços: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} 
	
	public static function novaCorrespondencia($idAgente)
	{
		try
		{
			$sql = "UPDATE AGENTES.dbo.EnderecoNacional set Status = 1 
					WHERE idAgente = ".$idAgente." 
					AND idEndereco = (select MIN(idEndereco) as valor from AGENTES.dbo.EnderecoNacional  where idAgente = ".$idAgente.")";

			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao alterar o Status dos endereços: " . $e->getMessage();
		}

		return $db->fetchAll($sql);
	} 

}  