<?php

Class ProponenteDAO extends Zend_Db_Table
{

    protected $_name = 'SAC.dbo.Projetos';

    public function execPaProponente($idPronac)
    {
        $sql = "exec SAC.dbo.paAgente $idPronac";
//        $sql = "exec SAC.dbo.paProponente $idPronac ";
//        $sql = "exec SAC.dbo.paProponente $pronac ";
//        $sql = "Select top 1000 * from SAC.dbo.Projetos";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
//        x($sql);
        $resultado = $db->fetchAll($sql);
//        xd($resultado);
        return $resultado;
    }

    public function verificarHabilitado($CgcCPf)
    {
        $sql = "SELECT i.Habilitado FROM SAC.dbo.Inabilitado i
                WHERE i.CgcCpf = '$CgcCPf' AND i.Habilitado='N'";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    }

    public function buscarDadosProponente($idpronac)
    {
        $sql = "SELECT
		itn.Nome,
		itn.Endereco,
		itn.CgcCpf,
		itn.Uf,
		itn.Cidade,
		itn.Esfera,
		itn.Responsavel,
		nat.Direito,
                itn.Cep,
                itn.Administracao,
                itn.Utilidade,
		case
		when ag.tipoPessoa = 0 then 'Pessoa Física'
		when ag.tipoPessoa = 1 then 'Pessoa Jurídica'
		end as tipoPessoa
		FROM  SAC.dbo.Projetos  pr
		JOIN SAC.dbo.Interessado  itn ON pr.CgcCpf = itn.CgcCpf
		left JOIN Agentes.dbo.Agentes ag on ag.CNPJCPF = pr.CgcCpf
		left JOIN AGENTES.dbo.Natureza AS nat ON nat.idAgente = ag.idAgente
		WHERE pr.IdPRONAC = $idpronac";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    }

    public function buscarEmail($idpronac)
    {
        $sql1 = "SELECT
                *,
                CASE
                WHEN It.TipoInternet = 28
                    THEN 'Email Particular'
                WHEN It.TipoInternet = 29
                    THEN 'Email Institucional'
                End as TipoInternet,
                It.Descricao as Email
            FROM AGENTES.dbo.Internet as It
            LEFT JOIN AGENTES.dbo.Agentes Ag on Ag.IdAgente = It.IdAgente
            LEFT JOIN SAC.dbo.Projetos Pr ON Ag.CNPJCPF = Pr.CgcCpf
            where Pr.IdPRONAC = " . $idpronac ;

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado1 = $db->fetchAll($sql1);

        return $resultado1;
    }

    public function buscarTelefone($idpronac)
    {
        $sql2 = "SELECT
                    CASE
                    WHEN Tl.TipoTelefone = 22 or Tl.TipoTelefone = 24
                    THEN 'Residencial'
                    WHEN Tl.TipoTelefone = 23 or Tl.TipoTelefone = 25
                    THEN 'Comercial'
                    WHEN Tl.TipoTelefone = 26
                    THEN 'Celular'
                    WHEN Tl.TipoTelefone = 27
                    THEN 'Fax'
                    END as TipoTelefone,
                    Uf.Descricao as UF,
                    Tl.DDD as DDDTelefone,
                    Tl.Numero as NumeroTelefone,
                    CASE
                    WHEN Tl.Divulgar = 1
                    THEN 'Sim'
                    WHEN Tl.Divulgar = 0
                    THEN 'Não'
                    end as Divulgar
                FROM AGENTES.dbo.Telefones Tl
                    INNER JOIN AGENTES.dbo.Uf as Uf on Uf.idUF = Tl.UF
                    INNER JOIN AGENTES.dbo.Agentes Ag on Ag.IdAgente = Tl.IdAgente
                    INNER JOIN SAC.dbo.Projetos Pr On Ag.CNPJCPF = Pr.CgcCpf
                    WHERE Pr.IdPRONAC = " . $idpronac ;

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado2 = $db->fetchAll($sql2);

        return $resultado2;
    }

    public function dadospronacs($idpronac)
    {
        $sql = "select
            pr.AnoProjeto+pr.Sequencial as pronac,
            pr.NomeProjeto as nomeprojeto,
            ar.Descricao as area,
            seg.Descricao as seg,
            ap.AprovadoReal,
            SAC.dbo.fnTotalCaptacao(pr.AnoProjeto, pr.Sequencial) as captado,
            SAC.dbo.fnTotalAprovadoProjeto(pr.AnoProjeto, pr.Sequencial) as aprovado
            from SAC.dbo.Projetos pr
            join SAC.dbo.Aprovacao ap on ap.IdPRONAC = pr.IdPRONAC
            join SAC.dbo.Area ar on ar.Codigo = pr.Area
            join SAC.dbo.Segmento seg on seg.Codigo = pr.Segmento
            where pr.IdPRONAC = " . $idpronac;
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado1 = $db->fetchAll($sql);

        return $resultado1;
    }

    public function buscarArquivados($idpronac)
    {
        $sql3 = "SELECT
							Pr.IdPRONAC,
                                                        Pr.AnoProjeto+Pr.Sequencial as pronac,
							Pr.NomeProjeto,
							Ar.descricao dsArea,
							Sg.descricao dsSegmento,
							Pr.SolicitadoReal,
							CASE WHEN Pr.Mecanismo IN ('2','6')
							THEN SAC.dbo.fnValorAprovadoConvenio(Pr.AnoProjeto,Pr.Sequencial) 
							ELSE SAC.dbo.fnValorAprovado(Pr.AnoProjeto,Pr.Sequencial)
							END AS ValorAprovado,
							SAC.dbo.fnCustoProjeto (Pr.AnoProjeto,Pr.Sequencial) as ValorCaptado
							FROM SAC.dbo.Projetos Pr 
							INNER JOIN SAC.dbo.Situacao St ON St.Codigo = Pr.Situacao
							INNER JOIN SAC.dbo.Area Ar ON  Ar.Codigo = Pr.Area
							INNER JOIN SAC.dbo.Segmento Sg ON Sg.Codigo = Pr.Segmento
							INNER JOIN SAC.dbo.Mecanismo Mc ON Mc.Codigo = Pr.Mecanismo
							INNER JOIN SAC.dbo.Enquadramento En ON En.idPRONAC =  Pr.idPRONAC
							LEFT JOIN AGENTES.dbo.Agentes A ON A.CNPJCPF = Pr.CgcCpf
							LEFT JOIN SAC.dbo.PreProjeto PP ON PP.idPreProjeto = Pr.idProjeto
							LEFT JOIN AGENTES.dbo.Nomes N ON N.idAgente = A.idAgente 
							LEFT JOIN SAC.dbo.tbArquivamento Ta ON Ta.idPronac = Pr.idPRONAC and Ta.stEstado = '1'
							LEFT JOIN SAC.dbo.Interessado I ON Pr.CgcCpf = I.CgcCpf
					  		WHERE Pr.idPRONAC = " . $idpronac . " and Ta.stEstado = '1'";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado1 = $db->fetchAll($sql3);

        return $resultado1;
    }

    public function buscarInativos($cpfCnpj)
    {
        $sql4 = "SELECT
                    Pr.IdPRONAC,
                    Pr.NomeProjeto,
                    Pr.AnoProjeto+Pr.Sequencial as pronac,
                    Ar.descricao dsArea,
                    Sg.descricao dsSegmento,
                    Pr.SolicitadoReal,
                    CASE WHEN Pr.Mecanismo IN ('2','6')
                    THEN SAC.dbo.fnValorAprovadoConvenio(Pr.AnoProjeto,Pr.Sequencial)
                    ELSE SAC.dbo.fnValorAprovado(Pr.AnoProjeto,Pr.Sequencial)
                    END AS ValorAprovado,
                    SAC.dbo.fnCustoProjeto (Pr.AnoProjeto,Pr.Sequencial) as ValorCaptado
                    FROM SAC.dbo.Projetos Pr
                    INNER JOIN SAC.dbo.Situacao St ON St.Codigo = Pr.Situacao
                    INNER JOIN SAC.dbo.Area Ar ON  Ar.Codigo = Pr.Area
                    INNER JOIN SAC.dbo.Segmento Sg ON Sg.Codigo = Pr.Segmento
                    WHERE Pr.CgcCpf='$cpfCnpj' and St.StatusProjeto = '0' order by 2 asc ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado1 = $db->fetchAll($sql4);

        return $resultado1;
    }

    public function buscarAtivos($cpfCnpj)
    {
        $sql5 = "SELECT
                    Pr.IdPRONAC,
                    Pr.AnoProjeto+Pr.Sequencial as pronac,
                    Pr.NomeProjeto,
                    Ar.descricao dsArea,
                    Sg.descricao dsSegmento,
                    Pr.SolicitadoReal,
                    CASE WHEN Pr.Mecanismo IN ('2','6')
                    THEN SAC.dbo.fnValorAprovadoConvenio(Pr.AnoProjeto,Pr.Sequencial)
                    ELSE SAC.dbo.fnValorAprovado(Pr.AnoProjeto,Pr.Sequencial)
                    END AS ValorAprovado,
                    SAC.dbo.fnCustoProjeto (Pr.AnoProjeto,Pr.Sequencial) as ValorCaptado
                    FROM SAC.dbo.Projetos Pr
                    INNER JOIN SAC.dbo.Situacao St ON St.Codigo = Pr.Situacao
                    INNER JOIN SAC.dbo.Area Ar ON  Ar.Codigo = Pr.Area
                    INNER JOIN SAC.dbo.Segmento Sg ON Sg.Codigo = Pr.Segmento
                    WHERE Pr.CgcCpf='$cpfCnpj' and St.StatusProjeto = '1' order by 2 asc";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado1 = $db->fetchAll($sql5);

        return $resultado1;
    }

    public function buscarDirigentes($idPronac)
    {
        $sql = "SELECT A.CNPJCPF, N.Descricao
				FROM SAC.dbo.Projetos P
					,SAC.dbo.Interessado I
					,AGENTES.dbo.Agentes A
					,AGENTES.dbo.Nomes N
				
				WHERE P.CgcCpf = I.CgcCpf
					AND A.idAgente = N.idAgente
					AND I.CgcCpf = A.CNPJCPF
					AND P.IdPRONAC = $idPronac";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);

        return $resultado;
    }

}
