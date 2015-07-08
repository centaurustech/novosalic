<?php
class GerarImprimirpdfDAO
{

    public static function ConsultaDadosProjeto($id_projeto)
    {
        $sql = "SELECT
                    p.*,
                    n.Descricao as NomeProponente,
                    a.CNPJCPF as CpfCnpjProponente
                FROM
                    SAC.dbo.PreProjeto p
                LEFT JOIN
                    AGENTES.dbo.Agentes a ON (p.idAgente=a.idAgente)
                LEFT JOIN
                    AGENTES.dbo.Nomes n ON (p.idAgente=n.idAgente)

                WHERE p.idPreProjeto=".$id_projeto ;

        try
        {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_ASSOC);
            return $db->fetchRow($sql);
        }
        catch (Zend_Exception_Db $e)
        {
            $this->view->message = $e->getMessage();
        }
    }

    
    
    public static function ConsultaDadosDivulgacao($id_projeto)
    {
        $sql = "SELECT
                idPlanoDivulgacao,
                idProjeto,
               
                Usuario,
                (select descricao from SAC.dbo.Verificacao where idverificacao = P.idPeca) as Peca,
                (select descricao from SAC.dbo.Verificacao where idverificacao = P.idVeiculo) as Veiculo,
                Usuario
            FROM
                SAC.dbo.PlanoDeDivulgacao as P
            WHERE idProjeto=".$id_projeto;

        try
        {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Exception_Db $e)
        {
            $this->view->message = $e->getMessage();
        }

        return $db->fetchAll($sql);
    }


    public static function DistribuicaodeProduto($id_projeto)
    {
        $sql = "SELECT 
                idPlanoDistribuicao,
                idProjeto,
                idProduto,
                idPosicaoDaLogo,
                QtdeProduzida,
                QtdePatrocinador,
                QtdeProponente,
                QtdeOutros,
                QtdeVendaNormal,
                QtdeVendaPromocional,
                PrecoUnitarioNormal,
                PrecoUnitarioPromocional,
                (QtdeVendaNormal*PrecoUnitarioNormal) as ReceitaNormal,
                (QtdeVendaPromocional*PrecoUnitarioPromocional) as ReceitaPro,
                (QtdeVendaNormal*PrecoUnitarioNormal) + (QtdeVendaPromocional*PrecoUnitarioPromocional) as ReceitaPrevista,
                Usuario,
                Area,
                Segmento,
                (select descricao from SAC.dbo.Produto where codigo = P.idproduto) as Produto,
                (select descricao from SAC.dbo.area where codigo = P.Area) as AreaFim,
                (select descricao from SAC.dbo.Segmento where codigo = P.Segmento) as SeguimentoFim,
                (select Descricao from SAC.dbo.verificacao where idVerificacao = P.idPosicaoDaLogo) as PosicaoDaLogo
            FROM
                SAC.dbo.PlanoDistribuicaoProduto as P
            WHERE idProjeto=".$id_projeto . " AND P.stPlanoDistribuicaoProduto = 1";



        try
        {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Exception_Db $e)
        {
            $this->view->message = $e->getMessage();
        }

        return $db->fetchAll($sql);
    }


        public static function AbrangenciaGeografica($id_projeto)
    {
        $sql = "SELECT CASE a.idPais
                WHEN 0 THEN 'Não é possível informar o local de realização do projeto'
                ELSE p.Descricao
                END as Pais,u.Descricao as UF,m.Descricao as Cidade,x.DtInicioDeExecucao,x.DtFinalDeExecucao
                FROM  SAC.dbo.Abrangencia a
                INNER JOIN SAC.dbo.PreProjeto x on (a.idProjeto = x.idPreProjeto)
                LEFT JOIN Agentes.dbo.Pais p on (a.idPais=p.idPais)
                LEFT JOIN Agentes.dbo.Uf u on (a.idUF=u.idUF)
                LEFT JOIN Agentes.dbo.Municipios m on (a.idMunicipioIBGE=m.idMunicipioIBGE)
                WHERE idProjeto=".$id_projeto." AND a.stAbrangencia = 1 
                ORDER BY p.Descricao,u.Descricao,m.Descricao";



        try
        {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Exception_Db $e)
        {
            $this->view->message = $e->getMessage();
        }

        return $db->fetchAll($sql);
    }



            public static function Orcamento($id_projeto)
    {
        $sql = "SELECT
                    idPlanilhaProposta,
                    idProjeto,
                    idProduto,
                    idEtapa,
                    idPlanilhaItem,
                    Descricao,
                    Unidade,
                    Quantidade,
                    Ocorrencia,
                    ValorUnitario,
                    QtdeDias,
                    TipoDespesa,
                    TipoPessoa,
                    Contrapartida,
                    FonteRecurso,
                    UfDespesa,
                    MunicipioDespesa,
                    idUsuario,
                    CAST(dsJustificativa AS TEXT) AS dsJustificativa,
                    (SELECT Descricao FROM SAC.dbo.tbPlanilhaEtapa WHERE idPlanilhaEtapa = P.idEtapa) as Etapa,
                    (select Descricao from SAC.dbo.tbPlanilhaItens where idPlanilhaItens = P.idPlanilhaItem) as Item,
                    (select descricao from SAC.dbo.tbPlanilhaUnidade where idUnidade = P.Unidade) as UnidadeF,
                    (select Descricao from SAC.dbo.Verificacao where idVerificacao=P.FonteRecurso)as FonteRecursoF,
                    (select descricao from Agentes.dbo.uf where iduf = P.UfDespesa) as UfDespesaF,
                    (select descricao from agentes.dbo.Municipios where idMunicipioIBGE=P.MunicipioDespesa) as MunicipioDespesaF,
                    (SELECT Descricao from SAC.dbo.Produto where Codigo = P.idProduto) as ProdutoF
                FROM
                    SAC.dbo.tbPlanilhaProposta as P
                WHERE
                    idProjeto = ".$id_projeto."
                ORDER BY
                    idEtapa,idProduto";

        try
        {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Exception_Db $e)
        {
            $this->view->message = $e->getMessage();
        }

        return $db->fetchAll($sql);
    }


}

?>
