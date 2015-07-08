<?php

class ConsultarDadosProjetoDAO extends Zend_Db_Table {

    public static function obterDadosProjeto($dados = array()) {
        /**
         * @todo: Implementar maneira correta utilizando models.
         */
        $retorno = false;
        if ($dados['idPronac']) {
            $sql = "SELECT p.IdPRONAC, p.idProjeto, p.AnoProjeto+p.Sequencial as NrProjeto,p.NomeProjeto,UfProjeto,a.Descricao as Area,
                s.Descricao as Segmento, m.Descricao as Mecanismo,p.Situacao + ' - ' + si.Descricao as Situacao,
                convert(varchar(10),DtSituacao,103) as DtSituacao,ProvidenciaTomada,
                isnull(sac.dbo.fnValorDaProposta(idProjeto),sac.dbo.fnValorSolicitado(p.AnoProjeto,p.Sequencial)) as ValorProposta,
                sac.dbo.fnValorSolicitado(p.AnoProjeto,p.Sequencial) as ValorSolicitado,
                sac.dbo.fnOutrasFontes(p.idPronac) as OutrasFontes,
                case
                when p.Mecanismo ='2' or p.Mecanismo ='6'
                then sac.dbo.fnValorAprovadoConvenio(p.AnoProjeto,p.Sequencial)
                else sac.dbo.fnValorAprovado(p.AnoProjeto,p.Sequencial)
                end as ValorAprovado,
                case
                when p.Mecanismo ='2' or p.Mecanismo ='6'
                then sac.dbo.fnValorAprovadoConvenio(p.AnoProjeto,p.Sequencial)
                else sac.dbo.fnValorAprovado(p.AnoProjeto,p.Sequencial) + sac.dbo.fnOutrasFontes(p.idPronac) end as ValorProjeto,
                sac.dbo.fnCustoProjeto (p.AnoProjeto,p.Sequencial) as ValorCaptado,
                p.CgcCPf,Nome as Proponente,sac.dbo.fnFormataProcesso(p.idPronac) as Processo,
                tabelas.dbo.fnEstruturaOrgao(p.Orgao,0) as Origem,
                h.Destino,h.DtTramitacaoEnvio,h.dtTramitacaoRecebida,h.Situacao as Estado,
                sac.dbo.fnNomeUsuario(idUsuarioEmissor)  as Emissor,
                sac.dbo.fnNomeUsuario(idUsuarioReceptor) as Receptor,
                h.meDespacho,p.ResumoProjeto,case
                when Enquadramento = '1'
                then 'Artigo 26'
                when Enquadramento = '2'
                then 'Artigo 18'
                else 'Não enquadrado'
                end as Enquadramento, p.Situacao as codSituacao
                FROM SAC.dbo.Projetos p
                LEFT JOIN SAC.dbo.Enquadramento e on (p.idPronac = e.idPronac)
                INNER JOIN SAC.dbo.Interessado i on (p.CgcCPf = i.CgcCPf)
                INNER JOIN SAC.dbo.Area a on (p.Area = a.Codigo)
                INNER JOIN SAC.dbo.Segmento s on (p.Segmento = s.Codigo)
                INNER JOIN SAC.dbo.Mecanismo m on (p.Mecanismo = m.Codigo)
                INNER JOIN SAC.dbo.Situacao si on (p.Situacao = si.Codigo)
                LEFT JOIN SAC.dbo.vwTramitarProjeto h on (p.idPronac = h.idPronac)
                WHERE p.IdPRONAC='{$dados['idPronac']}'";
                //WHERE p.AnoProjeto+p.Sequencial='{$dados['idPronac']}'";
            try {
                $db = Zend_Registry::get('db');
                $db->setFetchMode(Zend_DB::FETCH_OBJ);
            } catch (Zend_Exception_Db $e) {
                $this->view->message = "Falha ao buscar projeto: " . $e->getMessage();
            }
//            xd($sql);
            $retorno = $db->fetchAll($sql);
        }
        return $retorno;
    }
    
    public static function verificaComprovarExecucaoFinanceira($idPronac) 
    {
        $sql = "SELECT CAST(dtfimexecucao AS DATE),
       Dateadd(DAY, 30, CAST(dtfimexecucao AS DATE)),
       Situacao
        FROM   sac.dbo.projetos
        WHERE  CAST(Getdate() AS DATE) <= Dateadd(DAY, 30, CAST(dtfimexecucao AS DATE))
               AND dtfimexecucao IS NOT NULL
       AND ( situacao = 'E12'
             OR situacao = 'E13'
            OR situacao = 'E15'
            OR situacao = 'D40'
            OR situacao = 'E60'
            OR situacao = 'E61'
            OR situacao = 'E62'
            OR situacao = 'E59' 
            OR situacao = 'D34'
            OR situacao = 'D35'
            OR situacao = 'D28'
            OR situacao = 'D29'
            OR situacao = 'E60'
            OR situacao = 'E61'
            OR situacao = 'E62' )
       AND idpronac = $idPronac  ";
    }
}
