<?php  

class NomesDAO extends Zend_Db_Table
{  

	protected $_name = 'AGENTES.dbo.Nomes';  
	
	
	public static function buscarNome($idAgente)
	{
		
		$sql = "Select idNome, idAgente, TipoNome, Descricao, Status, Usuario From AGENTES.dbo.Nomes Where idAgente =".$idAgente;
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$dados =  $db->fetchAll($sql);	
		
		return $dados;
		
	}
	
	
	public static function gravarNome($idAgente, $TipoNome, $Descricao, $Status, $Usuario)
	{
		
		$sql = "Insert Into AGENTES.dbo.Nomes(idAgente, TipoNome, Descricao, Status, Usuario)values(".$idAgente.", ".$TipoNome.", '".$Descricao."', ".$Status.", ".$Usuario.")";
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$dados =  $db->query($sql);	
		
	}


	public static function atualizaNome($idAgente, $TipoNome, $Descricao, $Status, $Usuario)
	{
		
		$sql = "Update AGENTES.dbo.Nomes set TipoNome = ".$TipoNome.", Descricao = '".$Descricao."', Status = ".$Status.", Usuario = ".$Usuario."	Where idAgente = ".$idAgente;
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$dados =  $db->query($sql);	
		
	}
    
    public static function atualizaNomeReadequacao($idAgente, $Descricao)
	{
		$sql = "Update AGENTES.dbo.Nomes set Descricao = '".$Descricao."' Where idAgente = ".$idAgente;
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		return $db->query($sql);	
		
	}
	
	

}  