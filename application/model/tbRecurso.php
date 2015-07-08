<?php
/**
 * DAO Recurso
 * @author emanuel.sampaio - Politec
 * @since 18/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbRecurso extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbRecurso";



	/**
	 * Método para buscar o(s) recursos(s)
	 * @access public
	 * @param $idPronac integer
	 * @param $idPlanilhaAprovacao integer
	 * @return object
	 */
	public function buscarDados($idPronac = null, $idPlanilhaAprovacao = null)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(
			array("r" => $this->_name)
			,array("r.idRecurso"
				,"CONVERT(CHAR(10), r.dtSolicitacaoRecurso,103) + ' ' + CONVERT(CHAR(8), r.dtSolicitacaoRecurso,108) AS dtSolicitacaoRecurso"
				,"CAST(r.dsSolicitacaoRecurso AS TEXT) AS dsSolicitacaoRecurso"
				,"proponente.CNPJCPF AS CNPJCPFProponente"
				,"nmProponente.Descricao AS Proponente"
				,"CONVERT(CHAR(10), r.dtAvaliacao,103) + ' ' + CONVERT(CHAR(8), r.dtAvaliacao,108) AS dtAvaliacao"
				,"CAST(r.dsAvaliacao AS TEXT) AS dsAvaliacao"
				,"r.stAtendimento"
				,"r.tpSolicitacao"
				,"ministro.CNPJCPF AS CNPJCPFMinistro"
				,"nmMinistro.Descricao AS Ministro"
				,"rx.idPlanilhaAprovacao"
				,"rx.stRecursoAprovacao"
				,"CAST(rx.dsJustificativa AS TEXT) AS dsJustificativaProponente"
				,"CAST(papm.dsJustificativa AS TEXT) AS dsJustificativaMinistro"
				,"pi.Descricao AS Item"
				)
		);

		$select->joinLeft(
			array("rx" => "tbRecursoXPlanilhaAprovacao")
			,"r.idRecurso = rx.idRecurso"
			,array()
		);
		$select->joinLeft(
			array("pa" => "tbPlanilhaAprovacao")
			,"rx.idPlanilhaAprovacao = pa.idPlanilhaAprovacao"
			,array()
		);
		$select->joinLeft(
			array("pi" => "tbPlanilhaItens")
			,"pi.idPlanilhaItens = pa.idPlanilhaItem"
			,array()
		);
		$select->joinLeft(
			array("proponente" => "Agentes")
			,"proponente.idAgente = r.idAgenteSolicitante"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinLeft(
			array("nmProponente" => "Nomes")
			,"nmProponente.idAgente = proponente.idAgente"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinLeft(
			array("ministro" => "Agentes")
			,"ministro.idAgente = r.idAgenteAvaliador"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinLeft(
			array("nmMinistro" => "Nomes")
			,"nmMinistro.idAgente = ministro.idAgente"
			,array()
			,"AGENTES.dbo"
		);

		// avaliação do ministro
		$select->joinLeft(
			array("papm" => "tbPlanilhaAprovacao")
			,"rx.idPlanilhaAprovacao = papm.idPlanilhaAprovacaoPai AND papm.tpPlanilha = 'MI'"
			,array()
		);

		// filtra pelo idPronac
		if (!empty($idPronac))
		{
			$select->where("r.IdPRONAC = ?", $idPronac);
		}

		// filtra pelo idPlanilhaAprovacao
		if (!empty($idPlanilhaAprovacao))
		{
			$select->where("rx.idPlanilhaAprovacao = ?", $idPlanilhaAprovacao);
		}

		$select->order("r.dtSolicitacaoRecurso DESC");
		$select->order("r.dtAvaliacao DESC");
		$select->order("pi.Descricao");

		return $this->fetchAll($select);
	} // fecha método buscarDados()



	/**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o último id cadastrado)
	 */
	public function cadastrarDados($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarDados()



	/**
	 * Método para alterar
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $where)
	{
		$where = "idRecurso = " . $where;
		return $this->update($dados, $where);
	} // fecha método alterarDados()



	/**
	 * Método para excluir
	 * @access public
	 * @param integer $idPronac (excluir todos os recursos de um projeto)
	 * @param integer $idRecurso (excluir um determinado recurso)
	 * @return integer (quantidade de registros excluídos)
	 */
	public function excluirDados($idPronac = null, $idRecurso = null)
	{
		// exclui todos os recursos de um projeto
		if (!empty($idPronac))
		{
			$where = "IdPRONAC = " . $idPronac;
		}

		// exclui um determinado recurso
		else if (!empty($idRecurso))
		{
			$where = "idRecurso = " . $idRecurso;
		}

		return $this->delete($where);
	} // fecha método excluirDados()



	/**
	 * Método para buscar os projetos com solicitação de recurso
	 * @access public
	 * @param $idPronac integer
	 * @param $tpSolicitacao string
	 * @return object
	 */
	public function buscarSolicitacaoRecurso($idPronac = null, $tpSolicitacao = null)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->distinct();
		$select->from(
			array("rec" => $this->_name)
			,array("Pr.IdPRONAC"
				,"(Pr.AnoProjeto+Pr.Sequencial) AS pronac"
				,"Rec.idRecurso"
				,"Rec.dsSolicitacaoRecurso"
				,"CONVERT(CHAR(10),Rec.dtSolicitacaoRecurso,103) + ' ' + CONVERT(CHAR(10),Rec.dtSolicitacaoRecurso,108) AS dtSolicitacaoRecurso"
				,"Pr.IdPRONAC"
				,"Pr.CgcCpf"
				,"Pr.NomeProjeto"
				,"Ar.Descricao AS Area"
				,"Seg.Descricao AS Segmento"
				,"CONVERT(CHAR(10),Pr.DtInicioExecucao,103) AS DataInicio"
				,"CONVERT(CHAR(10),Pr.DtFimExecucao,103) AS DataFim"
                ,"NomeProponente" => new Zend_Db_Expr("
					CASE 
						WHEN N.Descricao IS NULL
							THEN I.Nome
						ELSE N.Descricao
					END")
				,"(mun.Descricao +' - '+ uf.Sigla) AS Cidade"
				,"Rec.stAtendimento"
				,"Rec.tpSolicitacao"
				,"Enq.IdEnquadramento AS idEnquadramento"
				,"Enq.Enquadramento")
		);

		$select->joinInner(
			array("Pr" => "Projetos")
			,"Rec.IdPRONAC = Pr.IdPRONAC"
			,array()
		);
		$select->joinInner(
			array("Seg" => "Segmento")
			,"Seg.Codigo = Pr.Segmento"
			,array()
		);
		$select->joinInner(
			array("Ar" => "Area")
			,"Ar.Codigo = Pr.Area"
			,array()
		);
		$select->joinInner(
			array("I" => "Interessado")
			,"I.CgcCpf = Pr.CgcCpf"
			,array()
		);
		$select->joinLeft(
			array("Ag" => "Agentes")
			,"Ag.CNPJCPF = Pr.CgcCpf"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinLeft(
			array("N" => "Nomes")
			,"N.idAgente = Ag.idAgente"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinLeft(
			array("EN" => "EnderecoNacional")
			,"EN.idAgente = Ag.idAgente"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinInner(
			array("mun" => "Municipios")
			,"mun.idMunicipioIBGE = I.Cidade OR mun.idMunicipioIBGE = EN.Cidade"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinInner(
			array("uf" => "UF")
			,"uf.idUF = mun.idUFIBGE"
			,array()
			,"AGENTES.dbo"
		);
		$select->joinLeft(
			array("Enq" => "Enquadramento")
			,"Enq.IdPRONAC = Pr.IdPRONAC"
			,array()
		);

		$select->where("Rec.stAtendimento = 'N'");

		// filtra pelo tipo de solicitacao
		if (!empty($tpSolicitacao))
		{
			if ($tpSolicitacao == 'PP' || $tpSolicitacao == 'PE')
			{
				$select->where("Rec.tpSolicitacao = 'PP' OR Rec.tpSolicitacao = 'PE'");
			}
			else
			{
				$select->where("Rec.tpSolicitacao = ?", $tpSolicitacao);
			}
		} // fecha if

		// filtra pelo idPronac
		if (!empty($idPronac))
		{
			$select->where("Pr.IdPRONAC = ?", $idPronac);
		}

		$select->order("Pr.NomeProjeto");

		return $this->fetchAll($select);
	} // fecha método buscarSolicitacaoRecurso()
    
    
	/**
	 * Método para buscar os projetos com solicitação de recurso
	 * @access public
	 * @param $idPronac integer
	 * @param $tpSolicitacao string
	 * @return object
	 */
	public function buscarRecursosEnviadosPlenaria($idReuniao)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(
			array("tp" => $this->_name)
			,array(
                new Zend_Db_Expr("
                    CASE
                        WHEN tp.tpSolicitacao = 'EN' THEN 'Enquadramento' 
                        WHEN tp.tpSolicitacao = 'EO' THEN 'Enquadramento e Orçamento' 
                        WHEN tp.tpSolicitacao = 'OR' THEN 'Orçamento' 
                        WHEN tp.tpSolicitacao = 'PI' THEN 'Projeto indeferido'
                    END AS tpSolicitacao,
                    tp.stAnalise,
                    (pr.AnoProjeto+pr.Sequencial) AS pronac, 
                    pr.NomeProjeto, 
                    pr.IdPRONAC, 
                    ar.Descricao AS area, 
                    seg.Descricao AS segmento,
                    par.ParecerFavoravel, 
                    nm.Descricao AS nomeComponente,
                    tp.idRecurso,
                    tp.tpSolicitacao as tipoSolicitacao")
            ), 'SAC.dbo'
		);
		$select->joinInner(
			array("pr" => "Projetos")
			,"pr.IdPRONAC = tp.IdPRONAC"
			,array(), 'SAC.dbo'
		);
		$select->joinInner(
			array("ar" => "Area")
			,"pr.Area = ar.Codigo"
			,array(), 'SAC.dbo'
		);
		$select->joinInner(
			array("seg" => "Segmento")
			,"pr.Segmento = seg.Codigo"
			,array(), 'SAC.dbo'
		);
		$select->joinLeft(
			array("par" => "Parecer")
			,"par.IdPRONAC = tp.IdPRONAC AND par.DtParecer = (SELECT TOP 1 max(DtParecer) from SAC..Parecer where IdPRONAC = pr.IdPRONAC)"
			,array(), 'SAC.dbo'
		);
		$select->joinLeft(
			array("nm" => "Nomes")
			,"nm.idAgente = tp.idAgenteAvaliador"
			,array(), 'AGENTES.dbo'
		);

		$select->where("tp.stEstado = ?", 0);
		$select->where("tp.idNrReuniao = ?", $idReuniao);
		$select->where("tp.siRecurso = ?", 8);
		$select->where("par.stAtivo = ?", 1);
		$select->where("par.TipoParecer = ?", 7);
		$select->where("NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao AS cv WHERE tp.idNrReuniao = cv.idNrReuniao and tp.IdPRONAC = cv.IdPRONAC)", '');
		$select->order(array(8,2));
        
        //xd($select->assemble());
		return $this->fetchAll($select);
	} // fecha método buscarSolicitacaoRecurso()



	/**
	 * Método que busca a planilha de de orçamento de custos
	 * @access public
	 * @param $idPronac integer
	 * @param $tpPlanilha string
	 * @return object
	 */
	public function buscarPlanilhaDeCustos($idPronac = null, $tpPlanilha = null)
	{
		$sql = "SELECT DISTINCT PAP.idProduto
					,RPA.dsJustificativa AS justificativa
					,Rec.idRecurso
					,PD.Descricao
					,CASE
						WHEN PAP.idProduto = 0
							THEN 'Administração do Projeto'
							ELSE PD.Descricao
						END AS Produto
					,PAP.qtItem AS quantidade_con
					,PRO.idpronac
					,PRO.AnoProjeto+Sequencial AS pronac
					,PAP.idPlanilhaAprovacao
					,PAP.qtDias AS dias_con
					,PAP.nrFonteRecurso
					,PAP.nrOcorrencia AS ocorrencia_con
					,PAP.vlUnitario AS valorUnitario_con
					,PAP.idPlanilhaAprovacao
					,PAP.idPlanilhaProjeto
					,PAP.idPlanilhaProposta
					,PAP.IdPRONAC
					,PAP.idProduto
					,PAP.idUnidade
					,PAP.idPlanilhaItem
					,UNI.Descricao AS Unidade
					,PRO.NomeProjeto
					,PAP.idEtapa
					,E.Descricao AS Etapa
					,I.Descricao AS Item
					,(PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) AS VlSolicitado
					,((PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) - (PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario)) AS VlReduzidoParecerista
					,(PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario) AS VlSugeridoParecerista
					,PPJ.Justificativa AS dsJustificativaParecerista
					,((PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) - (PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario)) AS VlReduzidoConselheiro
					,(PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) AS VlSugeridoConselheiro
					,PAP.dsJustificativa AS dsJustificativaConselheiro
					,PAPM.dsJustificativa AS dsMin
					,CASE
						WHEN (PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) - (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) = 0
							THEN 'Item Retirado'
							ELSE 'Item Reduzido'
						END AS Situacao
					,UF.idUF
					,UF.Sigla AS UF
					,CID.idMunicipioIBGE AS idCidade
					,CID.Descricao AS Cidade
					,LTRIM(RTRIM(TI.Descricao)) AS FonteRecurso
				FROM SAC.dbo.Projetos PRO
					LEFT JOIN SAC.dbo.tbPlanilhaProjeto PPJ ON PPJ.idPRONAC = PRO.IdPRONAC
					LEFT JOIN SAC.dbo.tbPlanilhaProposta PP ON (PPJ.idPlanilhaProposta = PP.idPlanilhaProposta)
					LEFT JOIN SAC.dbo.tbPlanilhaItens I ON (PPJ.idPlanilhaItem = I.idPlanilhaItens)
					LEFT JOIN SAC.dbo.tbPlanilhaAprovacao PAP ON (PAP.idPlanilhaProposta = PP.idPlanilhaProposta)
					LEFT JOIN SAC.dbo.tbPlanilhaAprovacao PAPM ON (PAPM.idPlanilhaProposta = PP.idPlanilhaProposta AND PAPM.tpPlanilha = 'MI')
					LEFT JOIN SAC.dbo.tbPlanilhaItens PIT ON (PAP.idPlanilhaItem = PIT.idPlanilhaItens)
					LEFT JOIN AGENTES.dbo.UF UF ON (PAP.idUFDespesa = UF.idUF)
					LEFT JOIN AGENTES.dbo.Municipios CID ON (PAP.idMunicipioDespesa = CID.idMunicipioIBGE)
					LEFT JOIN SAC.dbo.tbPlanilhaEtapa E ON (PAP.idEtapa = E.idPlanilhaEtapa)
					LEFT JOIN SAC.dbo.tbPlanilhaUnidade UNI ON (PAP.idUnidade = UNI.idUnidade)
					LEFT JOIN SAC.dbo.Produto PD ON (PAP.idProduto = PD.Codigo)
					INNER JOIN SAC.dbo.Verificacao TI ON TI.idverificacao = PAP.nrFonteRecurso AND TI.idTipo = 5
					INNER JOIN SAC.dbo.tbAnaliseAprovacao ap ON ap.idpronac = pro.idpronac AND tpAnalise = '$tpPlanilha' 
					LEFT JOIN SAC.dbo.tbRecursoXPlanilhaAprovacao RPA ON RPA.idPlanilhaAprovacao = PAP.idPlanilhaAprovacao
					LEFT JOIN SAC.dbo.tbRecurso Rec ON Rec.idRecurso = RPA.idRecurso

				WHERE ap.stAvaliacao = 1
					AND PAP.tpPlanilha = '$tpPlanilha'
					AND PAP.idPlanilhaItem NOT IN (206) 
					AND (PP.Quantidade * PP.Ocorrencia * PP.ValorUnitario) <> (PPJ.Quantidade * PPJ.Ocorrencia * PPJ.ValorUnitario)"; // vl solicitado != vl sugerido parecerista

		if (!empty($idPronac))
		{
			$sql.= "AND PRO.idpronac = $idPronac ";
		}

		$sql.= "ORDER BY PAP.nrFonteRecurso, PD.Descricao, PAP.idEtapa, E.Descricao, UF.Sigla, CID.Descricao, I.Descricao";
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		$resultado = $db->fetchAll($sql);
		return $resultado;
	} // fecha método buscarPlanilhaDeCustos()


    public function buscarRecursoProjeto($idPronac) {
        $sql = "SELECT  idRecurso, IdPRONAC, dtSolicitacaoRecurso, CAST(dsSolicitacaoRecurso AS TEXT) AS dsSolicitacaoRecurso,
                        idAgenteSolicitante, dtAvaliacao, dsAvaliacao, stAtendimento,
                        tpSolicitacao, idAgenteAvaliador
                FROM SAC.dbo.tbRecurso WHERE IdPRONAC = $idPronac";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método buscarPlanilhaDeCustos()
    
    public function painelRecursos($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idRecurso, b.AnoProjeto+b.Sequencial as PRONAC, b.NomeProjeto, a.dtSolicitacaoRecurso"),
                new Zend_Db_Expr("CASE
                                    WHEN tpSolicitacao = 'EN' THEN 'Enquadramento' 
                                    WHEN tpSolicitacao = 'OR' THEN 'Orçamento' 
                                    WHEN tpSolicitacao = 'PI' THEN 'Projeto indeferido'
                                    WHEN tpSolicitacao = 'EO' THEN 'Enquadramento e Orçamento'
                                 END AS tpSolicitacao,
                                 CASE
                                    WHEN tpRecurso = 1 THEN 'Pedido de Reconsideração'
                                    WHEN tpRecurso = 2 THEN 'Recurso'
                                 END AS tpRecurso, a.siRecurso
                "),
            )
        );

        $select->joinInner(
            array('b' => 'Projetos'), 'a.idPronac = b.idPronac',
            array(''), 'SAC.dbo'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

//        xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    public function buscarDadosRecursos($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idRecurso, 
                a.IdPRONAC, 
                a.dtSolicitacaoRecurso, 
                CAST(a.dsSolicitacaoRecurso AS TEXT) AS dsSolicitacaoRecurso, 
                a.idAgenteSolicitante, 
                a.dtAvaliacao,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.tpRecurso,
                CASE
                    WHEN tpRecurso = 1 THEN 'Pedido de Reconsideração'
                    WHEN tpRecurso = 2 THEN 'Recurso'
                END AS tpRecursoDesc,
                a.tpSolicitacao,
                CASE
                    WHEN tpSolicitacao = 'EN' THEN 'Enquadramento' 
                    WHEN tpSolicitacao = 'OR' THEN 'Orçamento' 
                    WHEN tpSolicitacao = 'PI' THEN 'Projeto indeferido'
                    WHEN tpSolicitacao = 'EO' THEN 'Enquadramento e Orçamento'
                END AS tpSolicitacaoDesc,
                a.idAgenteAvaliador,
                a.stAtendimento,
                a.siRecurso,
                a.stEstado,
                a.siFaseProjeto
            ")
        );
        
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'), 'a.siRecurso = b.idTipoEncaminhamento',
            array('dsEncaminhamento AS siRecursoDesc'), 'SAC.dbo'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        //xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    public function recursosNaoSubmetidos($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idRecurso, b.AnoProjeto+b.Sequencial AS PRONAC, b.NomeProjeto, a.dtSolicitacaoRecurso, a.tpSolicitacao"),
                new Zend_Db_Expr("CASE
                                    WHEN tpSolicitacao = 'EN' THEN 'Enquadramento' 
                                    WHEN tpSolicitacao = 'OR' THEN 'Orçamento' 
                                    WHEN tpSolicitacao = 'PI' THEN 'Projeto indeferido'
                                    WHEN tpSolicitacao = 'EO' THEN 'Enquadramento e Orçamento'
                                 END AS descTpSolicitacao,
                                 CASE
                                    WHEN tpRecurso = 1 THEN 'Pedido de Reconsideração'
                                    WHEN tpRecurso = 2 THEN 'Recurso'
                                 END AS tpRecurso
                "),
                new Zend_Db_Expr("c.Descricao AS Componente, d.Descricao AS dsArea, e.Descricao AS dsSegmento"),
            )
        );

        $select->joinInner(
            array('b' => 'Projetos'), 'a.idPronac = b.idPronac',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Nomes'), 'a.idAgenteAvaliador = c.idAgente',
            array(''), 'AGENTES.dbo'
        );
        $select->joinInner(
            array('d' => 'Area'), 'b.Area = d.Codigo',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('e' => 'Segmento'), 'b.Segmento = e.Codigo',
            array(''), 'SAC.dbo'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

//        xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    public function atualizarRecursosProximaPlenaria($idNrReuniao) {
        $sql = "UPDATE SAC.dbo.tbRecurso
                     SET idNrReuniao = idNrReuniao + 1
                FROM  SAC.dbo.tbRecurso  a
                INNER JOIN SAC.dbo.Projetos c on (a.IdPRONAC = c.idPronac)
                WHERE siRecurso = 8
                      AND NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao b WHERE a.IdPRONAC = b.IdPRONAC AND a.idNrReuniao = $idNrReuniao )";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método buscarPlanilhaDeCustos()
    
    public function atualizarStatusRecursosNaoSubmetidos($idNrReuniao) {
        $sql = "UPDATE SAC.dbo.tbRecurso
                    SET stEstado = 1
               FROM  SAC.dbo.tbRecurso a
               INNER JOIN SAC.dbo.Projetos c on (a.IdPRONAC = c.idPronac)
               WHERE a.stEstado = 0 and
                    (a.siRecurso = 9 and a.idNrReuniao = $idNrReuniao ) or
                    (a.siRecurso = 8 and a.stEstado = 0
                    AND EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao b WHERE a.IdPRONAC = b.IdPRONAC AND a.idNrReuniao = $idNrReuniao ))";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método buscarPlanilhaDeCustos()
    
} // fecha class