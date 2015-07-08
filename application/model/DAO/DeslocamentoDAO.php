<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbrangenciaDAO
 *
 * @author 01129075125
 */
class DeslocamentoDAO extends Zend_Db_Table{

    public static function buscarPais(){
        $sql = "SELECT * FROM AGENTES.dbo.Pais";

        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$resultado = $db->fetchAll($sql);
	
		return $resultado;
    }

    public static function buscarDeslocamentos($idProjeto, $idDeslocamento = null){

        $sql = "SELECT
			    de.idDeslocamento,
			    de.idProjeto,
			    de.idPaisOrigem,
			    paO.Descricao PO,
			    de.idUFOrigem,
			    ufO.Descricao UFO,
			    de.idMunicipioOrigem,
			    muO.Descricao MUO,
			    de.idPaisDestino,
			    paD.Descricao PD,
			    de.idUFDestino,
			    ufD.Descricao UFD,
			    de.idMunicipioDestino,
			    muD.Descricao MUD,
			    de.Qtde,
			    de.idUsuario

			FROM
			    SAC.dbo.tbDeslocamento de
			   	LEFT JOIN AGENTES.dbo.Pais paO		 ON de.idPaisOrigem = paO.idPais
			    LEFT JOIN AGENTES.dbo.UF ufO		 ON de.idUFOrigem = ufO.idUF
			    LEFT JOIN AGENTES.dbo.Municipios muO ON de.idMunicipioOrigem = muO.idMunicipioIBGE
			    LEFT JOIN AGENTES.dbo.Pais paD		 ON de.idPaisDestino = paD.idPais
			    LEFT JOIN AGENTES.dbo.UF ufD		 ON de.idUFDestino = ufD.idUF
			    LEFT JOIN AGENTES.dbo.Municipios muD ON de.idMunicipioDestino = muD.idMunicipioIBGE
			WHERE
			    idProjeto= ".$idProjeto;


			if($idDeslocamento != null)
			{
				$sql .=" AND de.idDeslocamento = ".$idDeslocamento;
			}


        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$resultado = $db->fetchAll($sql);

		return $resultado;
    }

    public static function buscarDeslocamentosGeral($where = array(), $order = array()){
        $meuWhere = "";
        // adicionando clausulas where
        foreach ($where as $coluna=>$valor)
        {
            if($meuWhere != ""){ $meuWhere .= " AND "; }
            $meuWhere .= $coluna.$valor;
        }

        $meuOrder = "";
        // adicionando clausulas order
        foreach ($order as $valor)
        {
            if($meuOrder != ""){ $meuOrder .= " , "; }else{ $meuOrder = " ORDER BY "; }
            $meuOrder .= $valor;
        }

        $sql = "SELECT
			    de.idDeslocamento,
			    de.idProjeto,
			    de.idPaisOrigem,
			    paO.Descricao PO,
			    de.idUFOrigem,
			    ufO.Descricao UFO,
			    de.idMunicipioOrigem,
			    muO.Descricao MUO,
			    de.idPaisDestino,
			    paD.Descricao PD,
			    de.idUFDestino,
			    ufD.Descricao UFD,
			    de.idMunicipioDestino,
			    muD.Descricao MUD,
			    de.Qtde,
			    de.idUsuario

			FROM
			    SAC.dbo.tbDeslocamento de
			   	LEFT JOIN AGENTES.dbo.Pais paO		 ON de.idPaisOrigem = paO.idPais
			    LEFT JOIN AGENTES.dbo.UF ufO		 ON de.idUFOrigem = ufO.idUF
			    LEFT JOIN AGENTES.dbo.Municipios muO ON de.idMunicipioOrigem = muO.idMunicipioIBGE
			    LEFT JOIN AGENTES.dbo.Pais paD		 ON de.idPaisDestino = paD.idPais
			    LEFT JOIN AGENTES.dbo.UF ufD		 ON de.idUFDestino = ufD.idUF
			    LEFT JOIN AGENTES.dbo.Municipios muD ON de.idMunicipioDestino = muD.idMunicipioIBGE
			WHERE
			    {$meuWhere}
                            $meuOrder";

        
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$resultado = $db->fetchAll($sql);

		return $resultado;
    }


    public static function salvaDeslocamento($dados)
    {
        
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$db->insert('SAC.dbo.tbDeslocamento', $dados);
		
    }
   
    public static function atualizaDeslocamento($paisOrigem,$uf,$cidade,$paisDestino,$ufD,$cidadeD,$quantidade,$idDeslocamento)
    {
    
    	$sql = "UPDATE SAC.dbo.tbDeslocamento SET idPaisOrigem=".$paisOrigem.", " .
    			"idUFOrigem = ".$uf.", " .
    			"idMunicipioOrigem = ".$cidade.", " .
    			"idPaisDestino = ".$paisDestino.", " .
    			"idUFDestino = ".$ufD.", " .
    			"idMunicipioDestino = ".$cidadeD.", " .
    			"Qtde = ".$quantidade." " .
    			"WHERE idDeslocamento = ".$idDeslocamento;
    			
    			    
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$db->query($sql);
		
    }

    public static function excluiDeslocamento($idDeslocamento){
        
        $sql ="DELETE FROM SAC.dbo.tbDeslocamento WHERE idDeslocamento = ".$idDeslocamento;
        
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$db->query($sql);
		
    }


}
?>
