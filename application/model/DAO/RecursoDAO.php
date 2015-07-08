<?php
/**
 * DAO Recurso
 * @author Equipe RUP - Politec
 * @since 27/07/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class RecursoDAO extends Zend_Db_Table
{
	/* dados da tabela */
	protected $_schema  = "";
	protected $_name    = "SAC.dbo.tbRecurso";
	protected $_primary = "idRecurso";



	/**
	 * Método para cadastrar informações dos recursos
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function cadastrar($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("SAC.dbo.tbRecurso", $dados);


		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		} 
	} // fecha método cadastrar()



	/**
	 * Método para alterar informações dos recursos
	 * @access public
	 * @static
	 * @param array $dados
	 * @param integer $id
	 * @return bool
	 */
	public static function alterar($dados, $idPronac)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$where   = "IdPRONAC = $idPronac";
		$alterar = $db->update("SAC.dbo.Projetos", $dados, $where);

		if ($alterar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método alterar()



	/**
	 * Método para buscar o id do último recurso cadastrado
	 * @access public
	 * @static
	 * @param void
	 * @return object || integer
	 */
	public static function buscarIdRecurso()
	{
		$sql = "SELECT MAX(idRecurso) AS idRecurso from SAC.dbo.tbRecurso";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarIdRecurso()







	/**
	 * Método para buscar o recurso cadastrado para Reenquadramento
	 * @access public
	 * @static
	 * @param void
	 * @return object || integer
	 */
	public static function buscarRecursoReenquadramento($idPronac = null)
	{
		$sql = "SELECT DISTINCT Pr.IdPRONAC,
                        Pr.AnoProjeto+Pr.Sequencial as pronac,
                  		Pr.AnoProjeto,
						Pr.Sequencial,
                        Rec.idRecurso,
                        Rec.dsSolicitacaoRecurso,
                        Rec.dtSolicitacaoRecurso,
                        Pr.IdPRONAC,
                        Pr.CgcCpf,
                        Pr.NomeProjeto,
                        Ar.Descricao as Area,
                        Seg.Descricao as Segmento,
                        Enq.IdEnquadramento,
                        Enq.Enquadramento,
                        CONVERT(CHAR(10),Pr.DtInicioExecucao,103) as DataInicio,
                        CONVERT(CHAR(10),Pr.DtFimExecucao,103) as DataFim,
                        CASE
                                WHEN N.Descricao IS NULL
                                        THEN I.Nome
                                ELSE N.Descricao
                                END AS NomeProponente,
                                mun.Descricao +' - '+ uf.Sigla AS Cidade,
                                Rec.stAtendimento,
                                Rec.tpSolicitacao
                        FROM 	SAC.dbo.tbRecurso Rec INNER JOIN
                              SAC.dbo.Projetos Pr ON Rec.IdPRONAC = Pr.IdPRONAC
                              INNER JOIN SAC.dbo.Segmento Seg on Seg.Codigo = Pr.Segmento
                              INNER JOIN SAC.dbo.Area Ar on Ar.Codigo = Pr.Area
                              INNER JOIN SAC.dbo.Enquadramento Enq on Enq.IdPRONAC = Pr.IdPRONAC 
                              LEFT JOIN AGENTES.dbo.Agentes Ag on Ag.CNPJCPF = Pr.CgcCpf
                              LEFT JOIN AGENTES.dbo.Nomes N on N.idAgente = Ag.idAgente
                              INNER JOIN SAC.dbo.Interessado I on I.CgcCpf = Pr.CgcCpf
                              LEFT JOIN AGENTES.dbo.EnderecoNacional EN on EN.idAgente = Ag.idAgente
                                  INNER JOIN Agentes..Municipios mun on mun.idMunicipioIBGE = I.Cidade or mun.idMunicipioIBGE = EN.Cidade
                                  INNER JOIN Agentes..UF uf on uf.idUF = mun.idUFIBGE
                            WHERE 
                            --Rec.stAtendimento = 'N' AND
                            Rec.tpSolicitacao = 'EN'";

		// caso o id do pronac seja informado
		if (!empty($idPronac))
		{
			$sql.= "AND Pr.IdPRONAC = '". $idPronac ."' ";
		}

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarRecursoReenquadramento()
	
	

		/**
	 * Método para buscar o recurso cadastrado para Orçamento
	 * @access public
	 * @static
	 * @param void
	 * @return object || integer
	 */
	public static function buscarRecursoOrcamento($idPronac = null, $idRecurso = null)
	{
		$sql = "SELECT DISTINCT Pr.IdPRONAC,
                        Pr.AnoProjeto+Pr.Sequencial as pronac,
                        Rec.dsSolicitacaoRecurso,
                        Rec.dtSolicitacaoRecurso,
                        Pr.IdPRONAC,
                        Pr.CgcCpf,
                        Pr.NomeProjeto,
                        Ar.Descricao as Area,
                        Seg.Descricao as Segmento,
                        Rec.idRecurso,
                        CONVERT(CHAR(10),Pr.DtInicioExecucao,103) as DataInicio,
                        CONVERT(CHAR(10),Pr.DtFimExecucao,103) as DataFim,
                        CASE
                                WHEN N.Descricao IS NULL
                                        THEN I.Nome
                                ELSE N.Descricao
                                END AS NomeProponente,
                                mun.Descricao +' - '+ uf.Sigla AS Cidade,
                                Rec.stAtendimento,
                                Rec.tpSolicitacao
                        FROM 	SAC.dbo.tbRecurso Rec INNER JOIN
                              SAC.dbo.Projetos Pr ON Rec.IdPRONAC = Pr.IdPRONAC
                              INNER JOIN SAC.dbo.Segmento Seg on Seg.Codigo = Pr.Segmento
                              INNER JOIN SAC.dbo.Area Ar on Ar.Codigo = Pr.Area
                              LEFT JOIN AGENTES.dbo.Agentes Ag on Ag.CNPJCPF = Pr.CgcCpf
                              LEFT JOIN AGENTES.dbo.Nomes N on N.idAgente = Ag.idAgente
                              INNER JOIN SAC.dbo.Interessado I on I.CgcCpf = Pr.CgcCpf
                              LEFT JOIN AGENTES.dbo.EnderecoNacional EN on EN.idAgente = Ag.idAgente
                                  INNER JOIN Agentes..Municipios mun on mun.idMunicipioIBGE = I.Cidade or mun.idMunicipioIBGE = EN.Cidade
                                  INNER JOIN Agentes..UF uf on uf.idUF = mun.idUFIBGE
                              WHERE Rec.stAtendimento = 'N'
                              AND Rec.tpSolicitacao = 'OR'";

		if (!empty($idPronac) && !empty($idRecurso))
		{
			$sql.= " AND Pr.IdPRONAC = $idPronac AND Rec.idRecurso = $idRecurso";
		}

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarRecursoOrcamento()
	

		/**
	 * Método para buscar o recurso cadastrado para Projetos Indeferidos
	 * @access public
	 * @static
	 * @param void
	 * @return object || integer
	 */
	
	public static function buscarRecursoProjetosIndeferidos($idPronac = null)
	{
		$sql = "SELECT DISTINCT Pr.IdPRONAC,
                        Pr.AnoProjeto+Pr.Sequencial as pronac,
                        Rec.idRecurso,
                        Rec.dsSolicitacaoRecurso,
                        Rec.dtSolicitacaoRecurso,
                        Pr.IdPRONAC,
                        Pr.CgcCpf,
                        Pr.NomeProjeto,
                        Ar.Descricao as Area,
                        Seg.Descricao as Segmento,
                        CONVERT(CHAR(10),Pr.DtInicioExecucao,103) as DataInicio,
                        CONVERT(CHAR(10),Pr.DtFimExecucao,103) as DataFim,
                        CASE
                                WHEN N.Descricao IS NULL
                                        THEN I.Nome
                                ELSE N.Descricao
                                END AS NomeProponente,
                                mun.Descricao +' - '+ uf.Sigla AS Cidade,
                                Rec.stAtendimento,
                                Rec.tpSolicitacao
                        FROM 	SAC.dbo.tbRecurso Rec INNER JOIN
                              SAC.dbo.Projetos Pr ON Rec.IdPRONAC = Pr.IdPRONAC
                              INNER JOIN SAC.dbo.Segmento Seg on Seg.Codigo = Pr.Segmento
                              INNER JOIN SAC.dbo.Area Ar on Ar.Codigo = Pr.Area
                              LEFT JOIN AGENTES.dbo.Agentes Ag on Ag.CNPJCPF = Pr.CgcCpf
                              LEFT JOIN AGENTES.dbo.Nomes N on N.idAgente = Ag.idAgente
                              INNER JOIN SAC.dbo.Interessado I on I.CgcCpf = Pr.CgcCpf
                              LEFT JOIN AGENTES.dbo.EnderecoNacional EN on EN.idAgente = Ag.idAgente
                                  INNER JOIN Agentes..Municipios mun on mun.idMunicipioIBGE = I.Cidade or mun.idMunicipioIBGE = EN.Cidade
                                  INNER JOIN Agentes..UF uf on uf.idUF = mun.idUFIBGE
		                 WHERE Rec.stAtendimento = 'N'
		                  AND Rec.tpSolicitacao = 'PI'";

		
		
		
		
		// caso o id do pronac seja informado
		if (!empty($idPronac))
		{
			$sql.= "AND Pr.IdPRONAC = '". $idPronac ."' ";
		}

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarRecursoProjetosIndeferidos()
	
		

	
	public static function buscarParecer($idAgente, $idPronac)
	{
		/*$sql = "select Pr.IdPRONAC, Pa.ParecerFavoravel,Pa.Conselheiro, Pa.Parecerista, Pa.ResumoParecer, Pa.TipoParecer, Tpa.dsItem, Tpa.dsJustificativa, Tpa.dtPlanilha 
from SAC.dbo.Parecer Pa
INNER JOIN SAC.dbo.Projetos Pr on Pr.IdPRONAC = Pa.idPRONAC
LEFT JOIN SAC.dbo.tbRecurso Re on Re.IdPRONAC = Pr.IdPRONAC
INNER JOIN SAC.dbo.Aprovacao Ap on Ap.IdPRONAC = Pa.idPRONAC
INNER JOIN SAC.dbo.Enquadramento En on En.IdEnquadramento = Pa.idEnquadramento
LEFT JOIN SAC.dbo.tbPlanilhaAprovacao Tpa on Tpa.IdPRONAC = Pr.IdPRONAC";

		// caso o id do pronac seja informado
		if (!empty($idPronac))
		{
			$sql.= "AND Pr.IdPRONAC = '". $idPronac ."' ";
		}*/
		$sql = "SELECT * FROM SAC.dbo.tbPlanilhaAprovacao WHERE idAgente = $idAgente AND IdPRONAC = $idPronac";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	}
	
	
	public static function buscarRecursoProjetosDeferidos()
		{
		$sql = "SELECT DISTINCT Pr.IdPRONAC,
                            Pr.AnoProjeto+Pr.Sequencial as pronac,
                            Rec.dsSolicitacaoRecurso,
                            Rec.dtSolicitacaoRecurso,
                            Pr.IdPRONAC,
                            Pr.CgcCpf,
                            Pr.NomeProjeto,
                            Ar.Descricao as Area,
                            Seg.Descricao as Segmento,
                            CONVERT(CHAR(10),Pr.DtInicioExecucao,103) as DataInicio,
                            CONVERT(CHAR(10),Pr.DtFimExecucao,103) as DataFim,
                            CASE
                                    WHEN N.Descricao IS NULL
                                            THEN I.Nome
                                    ELSE N.Descricao
                                    END AS NomeProponente,
                                    mun.Descricao +' - '+ uf.Sigla AS Cidade,
                                    Rec.stAtendimento,
                                    Rec.tpSolicitacao
                            FROM 	SAC.dbo.tbRecurso Rec INNER JOIN
                                  SAC.dbo.Projetos Pr ON Rec.IdPRONAC = Pr.IdPRONAC
                                  INNER JOIN SAC.dbo.Segmento Seg on Seg.Codigo = Pr.Segmento
                                  INNER JOIN SAC.dbo.Area Ar on Ar.Codigo = Pr.Area
                                  LEFT JOIN AGENTES.dbo.Agentes Ag on Ag.CNPJCPF = Pr.CgcCpf
                                  LEFT JOIN AGENTES.dbo.Nomes N on N.idAgente = Ag.idAgente
                                  INNER JOIN SAC.dbo.Interessado I on I.CgcCpf = Pr.CgcCpf
                                  LEFT JOIN AGENTES.dbo.EnderecoNacional EN on EN.idAgente = Ag.idAgente
                                      INNER JOIN Agentes..Municipios mun on mun.idMunicipioIBGE = I.Cidade or mun.idMunicipioIBGE = EN.Cidade
                                      INNER JOIN Agentes..UF uf on uf.idUF = mun.idUFIBGE
		                 WHERE Rec.stAtendimento = 'D'
		                  AND Rec.tpSolicitacao = 'NA'";

		
		
		
		
		// caso o id do pronac seja informado
		if (!empty($idPronac))
		{
			$sql.= "AND Pr.IdPRONAC = '". $idPronac ."' ";
		}

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarRecursoProjetosIndeferidos()
	
	
	
	
		public static function alterarSituacao($idPronac)
	{
		$sql = "UPDATE SAC.dbo.Projetos SET Situacao = 'D20' WHERE IdPRONAC = $idPronac";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
	} 




		public static function alterarEnquadramento($idPronac, $enquadramento)
	{
		$sql = "UPDATE SAC.dbo.Enquadramento SET Enquadramento = '$enquadramento' WHERE IdPRONAC = $idPronac";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
	} 

	
	
	
		public static function avaliarRecursoDef($idRecurso, $dtAvaliacao, $justificativa, $idAgenteAvaliador, $idPronac, $stAtendimento)
	
	{

				$sql = "UPDATE SAC.dbo.tbRecurso SET dtAvaliacao = '$dtAvaliacao', dsAvaliacao = '$justificativa', stAtendimento = 'D', idAgenteAvaliador = '469' WHERE idRecurso = $idRecurso";
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
		

		
	} // fecha método avaliarRecurso()
	

	
		
		public static function avaliarEnquadramentoDef($dtAvaliacao, $idPronac)
	
	{

				$sql = "UPDATE SAC.dbo.tbRecurso SET dtAvaliacao = '$dtAvaliacao', stAtendimento = 'D', idAgenteAvaliador = '469' WHERE idRecurso = $idPronac";
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
		

		
	} // fecha método avaliarRecurso()
	
	
	
			public static function avaliarEnquadramentoIndef($dtAvaliacao, $idPronac)
	
	{

				$sql = "UPDATE SAC.dbo.tbRecurso SET dtAvaliacao = '$dtAvaliacao', stAtendimento = 'I', idAgenteAvaliador = '469' WHERE idRecurso = $idPronac";
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
		

		
	} // fecha método avaliarRecurso()
	
	
	
		public static function avaliarRecursoInd($idRecurso, $dtAvaliacao, $justificativa, $idAgenteAvaliador, $idPronac, $stAtendimento)
	
	{

				$sql = "UPDATE SAC.dbo.tbRecurso SET dtAvaliacao = '$dtAvaliacao', dsAvaliacao = '$justificativa', stAtendimento = 'I', idAgenteAvaliador = '469' WHERE idRecurso = $idRecurso";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
		

		
	} // fecha método avaliarRecurso()
	
	
	public static function cadastrarRecurso($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("SAC.dbo.tbRecurso", $dados);


		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		} 
	} // fecha método cadastrar()
	
	
	
	
	
	
	

	
		public static function recursoReenquadramento($dados, $id)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$where = "IdEnquadramento = $id";
		$cadastrar = $db->update("SAC.dbo.Enquadramento", $dados, $where);

		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		} 
	} // fecha método cadastrar()

	
	
	
	public static function alterarEnquadramento18($idPronac)
	{

	$sql = "UPDATE SAC.dbo.Enquadramento SET Enquadramento = '1' WHERE idRecurso = $idRecurso";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
		

		
	} // fecha método alterarEnquadramento18()
	
	
	
	public static function alterarEnquadramento26($idPronac)
	{

	$sql = "UPDATE SAC.dbo.Enquadramento SET Enquadramento = '2' WHERE idRecurso = $idRecurso";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_ASSOC);
		return $db->fetchRow($sql);
		

		
	} // fecha método  alterarEnquadramento26()
	
	

	
	
	
	
    /**
     * Método para recuperar os projetos em análise. (CONSELHEIRO)
     * Só efetua a busca se as fontes de recursos estiverem de acordo com o Código 109 – Incentivo Fiscal Federal,
     * conforme Lei 8.313 de 1991.
     * @access public
     * @static
     * @param integer $idPronac
     * @return object
     */
    public static function analiseDeConta($idPronac = null, $idProduto = null, $idEtapa = null, $idUF = null, $idCidade = null, $buscarPorProduto = null)
    {
        $sql = "SELECT DISTINCT I.idPlanilhaItens AS idPlanilhaItens, 
					RPA.dsJustificativa as justificativa
					,Rec.idRecurso
                    ,PRO.AnoProjeto+Sequencial as pronac
					,PAP.vlUnitario AS valorUnitario_con
					,PAP.idPlanilhaAprovacao
					,PAP.idPlanilhaAprovacaoPai
					,PAP.IdPRONAC
					,PAP.idProduto
					,PRO.NomeProjeto
					,PD.Descricao AS Produto
					,I.Descricao AS Item
					,(PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) AS VlSolicitado
					,((PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) - (PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario)) AS VlReduzidoParecerista
					,(PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario) AS VlSugeridoParecerista
					,PPJ.Justificativa dsJustificativaParecerista
					,((PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) - (PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario)) AS VlReduzidoConselheiro
					,(PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) AS VlSugeridoConselheiro
					,PAP.dsJustificativa dsJustificativaMinistro, RPA.dsJustificativa justproponente
					,PAP.tpPlanilha

				FROM SAC.dbo.Projetos PRO
					,SAC.dbo.tbPlanilhaProjeto PPJ
					 INNER JOIN SAC.dbo.tbPlanilhaProposta PP on (PPJ.idPlanilhaProposta = PP.idPlanilhaProposta)
					 INNER JOIN SAC.dbo.tbPlanilhaItens I on (PPJ.idPlanilhaItem = I.idPlanilhaItens)
					 INNER JOIN SAC.dbo.tbPlanilhaAprovacao PAP on (PAP.idPlanilhaProposta = PP.idPlanilhaProposta)
					 INNER JOIN SAC.dbo.tbPlanilhaItens PIT on (PAP.idPlanilhaItem = PIT.idPlanilhaItens)
					 left join SAC.dbo.Produto PD on (PAP.idProduto = PD.Codigo)
					 LEFT JOIN SAC.dbo.tbRecursoXPlanilhaAprovacao RPA on RPA.idPlanilhaAprovacao = PAP.idPlanilhaAprovacao
					 LEFT JOIN SAC.dbo.tbRecurso Rec on Rec.idRecurso = RPA.idRecurso

				WHERE PAP.IdPRONAC = PRO.IdPRONAC 
					AND (PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario) <> (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario)
					AND (PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) <> (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario)
					AND PP.FonteRecurso = 109 
					AND PAP.stAtivo = 'S'";

        // busca de acordo com o pronac
        if (!empty($idPronac))
        {
            $sql.= " AND PAP.IdPRONAC = $idPronac";
        }
        
       if (!empty($idRecurso))
        {
            $sql.= " AND Rec.idRecurso = $idRecurso";
        }
        // busca de acordo com o produto
        if (!empty($idProduto) || $buscarPorProduto == true)
        {
            $sql.= " AND PAP.idProduto = $idProduto";
        }
        // busca de acordo com a etapa
        if (!empty($idEtapa))
        {
            $sql.= " AND PAP.idEtapa = $idEtapa";
        }
        // busca de acordo com a uf
        if (!empty($idUF))
        {
            $sql.= " AND PAP.idUFDespesa = $idUF";
        }
        // busca de acordo com a cidade
        if (!empty($idCidade))
        {
            $sql.= " AND PAP.idMunicipioDespesa = $idCidade";
        }

        $sql.= " ORDER BY I.Descricao ASC";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método analiseDeConta()
	
	
	
	
	
	 public static function analiseDeCustosBuscarProduto($idPronac)
    {
		$sql = "SELECT DISTINCT PD.Descricao, 
					CASE
						WHEN PAP.idProduto = 0
							THEN 'Administração do Projeto'
							ELSE PD.Descricao
						END AS Produto
					,PAP.IdPRONAC
					,PAP.idProduto

				FROM SAC.dbo.Projetos PRO
					,SAC.dbo.tbPlanilhaProjeto PPJ
					 INNER JOIN SAC.dbo.tbPlanilhaProposta PP on (PPJ.idPlanilhaProposta = PP.idPlanilhaProposta)
					 INNER JOIN SAC.dbo.tbPlanilhaItens I on (PPJ.idPlanilhaItem = I.idPlanilhaItens)
					 INNER JOIN SAC.dbo.tbPlanilhaAprovacao PAP on (PAP.idPlanilhaProposta = PP.idPlanilhaProposta)
					 INNER JOIN SAC.dbo.tbPlanilhaItens PIT on (PAP.idPlanilhaItem = PIT.idPlanilhaItens)
					 left join SAC.dbo.Produto PD on (PAP.idProduto = PD.Codigo)

				WHERE PAP.IdPRONAC = PRO.IdPRONAC
					AND (PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario) <> (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario)
					AND (PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) <> (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario)			
					AND PP.FonteRecurso = 109 
					AND PAP.tpPlanilha = 'CO' ";

        // busca de acordo com o pronac
        if (!empty($idPronac))
        {
            $sql.= " AND PAP.IdPRONAC = $idPronac";
        }

        $sql.= " ORDER BY PD.Descricao";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método analiseDeCustosBuscarProduto()



    /**
     * Método que busca as etapas dos projetos da análise de custos
     * @access public
     * @static
     * @param integer $idPronac
     * @return object
     */
    public static function analiseDeCustosBuscarEtapa($idPronac, $idProduto = null, $buscarPorProduto = null)
    {
		$sql = "SELECT DISTINCT PP.idEtapa AS idEtapa
					,PAP.IdPRONAC
					,PAP.idProduto

				FROM SAC.dbo.Projetos PRO
					,SAC.dbo.tbPlanilhaProjeto PPJ
					 INNER JOIN SAC.dbo.tbPlanilhaProposta PP on (PPJ.idPlanilhaProposta = PP.idPlanilhaProposta)
					 INNER JOIN SAC.dbo.tbPlanilhaItens I on (PPJ.idPlanilhaItem = I.idPlanilhaItens)
					 INNER JOIN SAC.dbo.tbPlanilhaAprovacao PAP on (PAP.idPlanilhaProposta = PP.idPlanilhaProposta)
					 INNER JOIN SAC.dbo.tbPlanilhaItens PIT on (PAP.idPlanilhaItem = PIT.idPlanilhaItens)
					 left join SAC.dbo.Produto PD on (PAP.idProduto = PD.Codigo)

				WHERE PAP.IdPRONAC = PRO.IdPRONAC
					AND (PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario) <> (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario)
					AND (PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) <> (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario)
					AND PP.FonteRecurso = 109 
					AND PAP.tpPlanilha = 'CO' ";

        // busca de acordo com o pronac
        if (!empty($idPronac))
        {
            $sql.= " AND PAP.IdPRONAC = $idPronac";
        }
        if (!empty($idProduto) || $buscarPorProduto == true)
        {
            $sql.= " AND PAP.idProduto = $idProduto";
        }

        $sql.= " ORDER BY PP.idEtapa";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método analiseDeCustosBuscarEtapa()

	
	
	
		public static function Parecer($dados)
	{
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("SAC.dbo.Parecer", $dados);


		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		} 
	} // fecha método cadastrar()

	} 
 

	
	/**
	 * Método para avaliar o recurso
	 */
	public static function avaliarRecurso($dados, $id)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$where   = "idRecurso = $id";
		$alterar = $db->update("SAC.dbo.tbRecurso", $dados, $where);

		if ($alterar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método avaliarRecurso()

	
	

	/**
	 * Cadastra na planilha de aprovação
	 */
	public static function cadastrarPlanilhaAprovacao($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("SAC.dbo.tbPlanilhaAprovacao", $dados);

		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método cadastrarPlanilhaAprovacao()

	

	/**
	 * desativa a planilha de aprovação
	 */
	public static function desativarPlanilhaAprovacao($dados, $id)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$where = "idPlanilhaAprovacao = $id";
		$alterar = $db->update("SAC.dbo.tbPlanilhaAprovacao", $dados, $where);

		if ($alterar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método cadastrarPlanilhaAprovacao()

	

	/**
	 * seleciona todos os dados da planilha de aprovação
	 */
	public static function buscarPlanilhaAprovacao($id)
	{
		$sql = "SELECT * 

				FROM SAC.dbo.tbPlanilhaAprovacao  

				WHERE idPlanilhaAprovacao = $id ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
	} // fecha método buscarPlanilhaAprovacao()
    


	/**
	 * busca a justificativa do proponente
	 */
	public static function buscarJustificativaProponente($idRecurso, $idPlanilha)
	{
		$sql = "SELECT dsJustificativa FROM SAC.dbo.tbRecursoXPlanilhaAprovacao WHERE idRecurso = " . (int)$idRecurso . " AND idPlanilhaAprovacao = " . (int)$idPlanilha;

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
	} // fecha método buscarJustificativaProponente()
}