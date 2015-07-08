<?php
Class Analiseprojeto extends Zend_Db_Table{

       	protected $_name    = 'SAC.dbo.Projetos';

       	public static function buscar($pronac)
       	{
       		$sql = "select idPRONAC,
NomeProjeto
from SAC.dbo.Projetos where IdPRONAC = " . $pronac . " ";
   
			$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
			$resultado = $db->fetchAll($sql);

			return $resultado;
       	}
       	
}