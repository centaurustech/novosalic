<?php
/**
 * DAO MantertabelaitensDAO
 * @author Equipe RUP - Politec
 * @since 13/12/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class MantertabelaitensDAO extends Zend_Db_Table {

	/*********************************************************************************************************/
	
    public static function exibirprodutoetapaitem($item=null,$nomeItem=null,$idEtapa=null,$idProduto=null) {
        $sql = "SELECT distinct pr.Codigo as idProduto, pr.Descricao as Produto
 				FROM SAC.dbo.tbItensPlanilhaProduto p
				INNER JOIN SAC.dbo.Produto pr on (p.idProduto = pr.Codigo)
				INNER JOIN SAC.dbo.TbPlanilhaItens i on (p.idPlanilhaItens = i.idPlanilhaItens)
				INNER JOIN SAC.dbo.TbPlanilhaEtapa e on (p.idPlanilhaEtapa = e.idPlanilhaEtapa)	";
        
        
        if(!empty($nomeItem)){
            $sql .=" AND i.Descricao ".$nomeItem;
        }
        if(!empty($item)){
            $sql .=" WHERE i.idPlanilhaItens = ".$item;
        }
        if(!empty($idEtapa)){
            $sql .=" AND e.idPlanilhaEtapa = ".$idEtapa;
        }
        if(!empty($idProduto)){
            $sql .=" AND pr.Codigo = ".$idProduto;
        }
        $sql .=" ORDER BY pr.Codigo ASC";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    } // fecha método buscaprodutoetapaitem()

    
    public static function exibirEtapa($idProduto) {
        
    	$sql = "SELECT distinct  e.idPlanilhaEtapa as idEtapa, e.Descricao as Etapa
					FROM SAC.dbo.tbItensPlanilhaProduto p
					INNER JOIN SAC.dbo.Produto pr on (p.idProduto = pr.Codigo)
					INNER JOIN SAC.dbo.TbPlanilhaItens i on (p.idPlanilhaItens = i.idPlanilhaItens)
					INNER JOIN SAC.dbo.TbPlanilhaEtapa e on (p.idPlanilhaEtapa = e.idPlanilhaEtapa)
						Where  idProduto = ".$idProduto." 
						 ORDER BY  e.Descricao  ASC ";
        
    	//die('<pre>'.$sql);
    	 $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    } 

    public static function exibirItem($idProduto, $idEtapa) {
        $sql = "SELECT i.idPlanilhaItens as idItem,	i.Descricao as NomeDoItem
				FROM SAC.dbo.tbItensPlanilhaProduto p
				INNER JOIN SAC.dbo.Produto pr on (p.idProduto = pr.Codigo)
				INNER JOIN SAC.dbo.TbPlanilhaItens i on (p.idPlanilhaItens = i.idPlanilhaItens)
				INNER JOIN SAC.dbo.TbPlanilhaEtapa e on (p.idPlanilhaEtapa = e.idPlanilhaEtapa)
					Where  p.idProduto = ".$idProduto." and e.idPlanilhaEtapa = ".$idEtapa." 
					ORDER BY  i.Descricao  ASC";
        
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    } 

    /*********************************************************************************************************/	


    public static function buscaprodutoetapaitem($item=null,$nomeItem=null) {
        $sql = "SELECT pr.Codigo as idProduto, 
                       p.idPlanilhaItens,
                       e.idPlanilhaEtapa as idEtapa,
                       pr.Descricao as Produto,
                       e.Descricao as Etapa,
                       i.Descricao as NomeDoItem
                FROM SAC.dbo.tbItensPlanilhaProduto p
		INNER JOIN SAC.dbo.Produto pr on (p.idProduto = pr.Codigo)
		INNER JOIN SAC.dbo.TbPlanilhaItens i on (p.idPlanilhaItens = i.idPlanilhaItens)
		INNER JOIN SAC.dbo.TbPlanilhaEtapa e on (p.idPlanilhaEtapa = e.idPlanilhaEtapa)";
        if(!empty($item)){
            $sql .=" WHERE i.idPlanilhaItens = ".$item;
        }
        if(!empty($nomeItem)){
            $sql .=" AND i.Descricao ".$nomeItem;
        }
        //XD($sql);
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchRow($sql);
    } // fecha método buscaprodutoetapaitem()


    public static function buscaproduto($where=null) {
        $sql = "SELECT Codigo as codproduto, Descricao as Produto
				FROM SAC.dbo.Produto WHERE stEstado = 0 ORDER BY Produto "; //WHERE stEstado = 0
        if(!empty($where)){
            $sql .=" AND i.Descricao ".$nomeItem;
        }
        //xd($sql);
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    } // fecha método buscaprodutoetapaitem()



    public static function buscaetapa() {
        $sql = "SELECT idPlanilhaEtapa as codetapa, Descricao as Etapa
				FROM SAC.dbo.TbPlanilhaEtapa";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    } // fecha método buscaprodutoetapaitem()




    public static function buscaitem() {
        $sql = "Select idPlanilhaItens as coditens, Descricao as Item, idUsuario from SAC.dbo.tbPlanilhaItens
		 order by Descricao";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    } // fecha método buscaprodutoetapaitem()



    public static function solicitacoes($idAgente) {
        $sql = "SELECT prod.Codigo as idProduto, prod.Descricao as Produto,
			       et.idPlanilhaEtapa, et.Descricao as Etapa,
			       sol.idSolicitarItem,
			       CASE
			            WHEN  sol.IdPlanilhaItens > 0 THEN it.Descricao
			            ELSE sol.NomeDoItem
			       END as ItemSolicitado,
			       sol.Descricao as Justificativa,
			       CASE sol.stEstado
			            WHEN 0 THEN 'Solicitado'
			            WHEN 1 THEN 'Atendido'
			            ELSE 'Negado'
			       END as Estado,Resposta
			 FROM SAC.dbo.tbSolicitarItem sol
			      INNER JOIN SAC.dbo.Produto prod ON sol.idProduto = prod.Codigo
			      INNER JOIN SAC.dbo.tbPlanilhaEtapa et ON sol.idEtapa = et.idPlanilhaEtapa
			      LEFT JOIN SAC.dbo.TbPlanilhaItens it ON sol.idPlanilhaItens = it.idPlanilhaItens
			 WHERE sol.idAgente = '".$idAgente."'
			 ORDER BY sol.idSolicitarItem";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    } // fecha método buscaprodutoetapaitem()





    public static function cadastraritem($dadosassociar) {

        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);


        //Codigo anterior
        /*$sql = "insert into SAC.dbo.tbPlanilhaItens (Descricao, idUsuario)
                            values ('".$Descricao."', ".$idAgente.")";*/

        $cadastrar = $db->insert("SAC.dbo.tbSolicitarItem", $dadosassociar);

        if ($cadastrar) {
            return true;
        }
        else {
            return false;
        }

       /* $resultado = $db->query($sql);
        return $resultado;*/
    } // fecha método cadastraritem()



    public static function buscarItem($idAgente) {
        $sql = "SELECT TOP 1 idPlanilhaItens FROM SAC.dbo.tbPlanilhaItens where idUsuario = ".$idAgente." order by idPlanilhaItens desc";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_ASSOC);
        return $db->fetchRow($sql);
    } // fecha método buscarItem()



    public static function associaritem($dadosassociar) {


        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $cadastrar = $db->insert("SAC.dbo.tbSolicitarItem", $dadosassociar);
		
        if ($cadastrar) {
            return true;
        }
        else {
            return false;
        }


    }

    public static function buscarSolicitacoes($where=array(),$nomeItem=null) {


         $sql = "SELECT prod.Codigo as idProduto,
                        prod.Descricao as Produto,
			et.idPlanilhaEtapa,
                        et.Descricao as Etapa,
			sol.idSolicitarItem,
			       CASE
			            WHEN  sol.IdPlanilhaItens > 0 THEN it.Descricao
			            ELSE sol.NomeDoItem
			       END as ItemSolicitado,
			       sol.Descricao as Justificativa,
			       CASE sol.stEstado
			            WHEN 0 THEN 'Solicitado'
			            WHEN 1 THEN 'Atendido'
			            ELSE 'Negado'
			       END as Estado,Resposta
			 FROM SAC.dbo.tbSolicitarItem sol
			      INNER JOIN SAC.dbo.Produto prod ON sol.idProduto = prod.Codigo
			      INNER JOIN SAC.dbo.tbPlanilhaEtapa et ON sol.idEtapa = et.idPlanilhaEtapa
			      LEFT JOIN SAC.dbo.TbPlanilhaItens it ON sol.idPlanilhaItens = it.idPlanilhaItens";

        $ct=1;
        foreach ($where as $coluna=>$valor)
        {
            if($ct==1)
                $sql .= " WHERE ".$coluna." = '".$valor."'";
            else
                $sql .= " AND ".$coluna." = '".$valor."'";
            $ct++;
        }
        if(!empty($nomeItem)){
            $sql .="AND (sol.NomeDoItem = '{$nomeItem}' OR it.Descricao = '{$nomeItem}')";
        }
        $sql .= " ORDER BY sol.idSolicitarItem";
        
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);


    }




}