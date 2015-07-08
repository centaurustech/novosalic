<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parecer
 *
 * @author augusto
 */
class Parecer extends GenericModel {

    protected $_banco = 'SAC';
    protected $_schema = 'dbo';
    protected $_name = 'Parecer';
    
    
    public function salvar($dados)
    {
        //x($dados);
        //INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $tmpTblParecer = new Parecer();


        if(isset($dados['idParecer'])){
            $tmpRsParecer= $tmpTblParecer->find($dados['idParecer'])->current();
        }else{
            $tmpRsParecer = $tmpTblParecer->createRow();
        }

        //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS
        if(isset($dados['IdPRONAC'])){ $tmpRsParecer->IdPRONAC = $dados['IdPRONAC']; }
        if(isset($dados['idEnquadramento'])){ $tmpRsParecer->idEnquadramento = $dados['idEnquadramento']; }
        if(isset($dados['AnoProjeto'])){ $tmpRsParecer->AnoProjeto = $dados['AnoProjeto']; }
        if(isset($dados['Sequencial'])){ $tmpRsParecer->Sequencial = $dados['Sequencial']; }
        if(isset($dados['TipoParecer'])){ $tmpRsParecer->TipoParecer = $dados['TipoParecer']; }
        if(isset($dados['ParecerFavoravel'])){ $tmpRsParecer->ParecerFavoravel = $dados['ParecerFavoravel']; }
        if(isset($dados['DtParecer'])){ $tmpRsParecer->DtParecer = $dados['DtParecer']; }
        if(isset($dados['Parecerista'])){ $tmpRsParecer->Parecerista = $dados['Parecerista']; }
        if(isset($dados['Conselheiro'])){ $tmpRsParecer->Conselheiro = $dados['Conselheiro']; }
        if(isset($dados['NumeroReuniao'])){ $tmpRsParecer->NumeroReuniao = $dados['NumeroReuniao']; }
        if(isset($dados['ResumoParecer'])){ $tmpRsParecer->ResumoParecer = $dados['ResumoParecer']; }
        if(isset($dados['SugeridoUfir'])){ $tmpRsParecer->SugeridoUfir = $dados['SugeridoUfir']; }
        if(isset($dados['SugeridoReal'])){ $tmpRsParecer->SugeridoReal = $dados['SugeridoReal']; }
        if(isset($dados['SugeridoCusteioReal'])){ $tmpRsParecer->SugeridoCusteioReal = $dados['SugeridoCusteioReal']; }
        if(isset($dados['SugeridoCapitalReal'])){ $tmpRsParecer->SugeridoCapitalReal = $dados['SugeridoCapitalReal']; }
        if(isset($dados['Atendimento'])){ $tmpRsParecer->Atendimento = $dados['Atendimento']; }
        if(isset($dados['Logon'])){ $tmpRsParecer->Logon = $dados['Logon']; }
        if(isset($dados['stAtivo'])){ $tmpRsParecer->stAtivo = $dados['stAtivo']; }
        if(isset($dados['idTipoAgente'])){ $tmpRsParecer->idTipoAgente = $dados['idTipoAgente']; }
//xd($tmpRsParecer);
        echo "<pre>";
        

        //SALVANDO O OBJETO CRIADO
        $id = $tmpRsParecer->save();

        if($id){
            return $id;
        }else{
            return false;
        }
    }
    

    public function inserirParecer($dados) {
        try {
            x($dados);
            xd($this->dbg($dados));
            $inserir = $this->insert($dados);
            return $inserir;
        } catch (Zend_Db_Table_Exception $e) {
            return "'inserirParecer = 'ERRO:" . $e->__toString() . "Linha:" . $e->getLine();
        }
    }


    public function updateSalvarParecer($dados, $idparecer) {
        try {
            $where = ("idParecer =  $idparecer");
            $salvar = $this->update($dados, $where);
            return true;
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
    }

    /**
     * @todo: N?o apague esse comentário, caso contrário aparecerá uma tela azul no seu computador! ;D
     *

        -- Lista de Análises realizadas
         > somente se selecionar um parecerista
        -- Se solicitar valor por produto linkar com planilha projeto

        --Select distinct AnoProjeto + Sequencial from SAC.dbo.Projetos pro
        select
                        pro.AnoProjeto + pro.Sequencial as PRONAC,
                        pro.NomeProjeto,
                        prod.Descricao as Produto,
                        ag.idAgente,
                        DATEDIFF(DAY, dp.DtDistribuicao, dp.DtDevolucao) as QtdDias,
                nomes.Descricao as Parecerista--,
                        --pp.ValorUnitario as ValorPagamento
           from SAC.dbo.tbDistribuirParecer dp
          inner join SAC.dbo.Produto prod on dp.idProduto = prod.Codigo
          inner join SAC.dbo.Projetos pro on pro.IdPRONAC = dp.idPRONAC
          inner join AGENTES.dbo.Agentes ag on ag.idAgente = dp.idAgenteParecerista
            inner join AGENTES.dbo.Nomes nomes on nomes.idAgente = dp.idAgenteParecerista
          --inner join TABELAS.dbo.Usuarios usu on ag.Usuario = usu.usu_codigo
          --inner join SAC.dbo.tbPlanilhaProjeto pp on pp.idPRONAC = dp.idPRONAC
          where dp.stEstado = 0
            and dp.FecharAnalise = 1
            and pro.AnoProjeto + pro.Sequencial = '103920'
            --and ag.idAgente = 30013
             */

    public function buscarAnaliseProduto($filtros) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('dp' => 'tbDistribuirParecer'),
                array(
                    "case when dp.TipoAnalise = 0 then 'Análise de conteúdo' else '' end +
                     case when dp.TipoAnalise = 1 then 'Análise de custo de produto' else '' end +
                     case when dp.TipoAnalise = 2 then 'Análise de custo adminstrativo do projeto' else '' end as TipoAnalise",
                    'dp.Observacao'
                )
        );
        $select->joinInner(
                array('prod' => 'Produto'),
                'dp.idProduto = prod.Codigo',
                array('prod.Descricao as produto')
        );
        $select->joinInner(
                array('pro' => 'Projetos'),
                'pro.IdPRONAC = dp.idPRONAC',
                array()
        );
        $select->joinInner(
                array('org' => 'Orgaos'),
                'org.Codigo = dp.idOrgao',
                array('org.Sigla as Orgao')
        );
        $select->joinInner(
                array('ag' => 'Agentes'),
                'inner join AGENTES.dbo.Agentes ag on ',
                array(),
                'AGENTES.dbo'
        );
        $select->joinInner(
                array('org' => 'Usuarios'),
                'ag.Usuario = usu.usu_codigo ',
                array('usu.usu_nome'),
                'TABELAS.dbo'
        );
        $select->joinInner(
                array('are' => 'Area'),
                'are.Codigo = pro.Area',
                array('are.Descricao')
        );
        $select->joinInner(
                array('seg' => 'Segmento'),
                'seg.Codigo = pro.Segmento',
                array('seg.Descricao')
        );

        $select->where('aa.tpAnalise = ?', $tpanalise);
        $select->where('aa.idPronac = ?',  $idpronac);
        return $this->fetchAll($select);
    }

    public function buscarDadosParecerista($filtros) {
    }

    /*===========================================================================*/
    /*====================== ABAIXO - METODOS DA CNIC ===========================*/
    /*===========================================================================*/
    
    public function buscarParecer($tipoAgente = null, $idpronac, $where=array()) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('pa' => $this->_name),
                array(
                    'pa.DtParecer',
                    'pa.ResumoParecer as ResumoParecer',
                    'pa.ParecerFavoravel',
                    'pa.TipoParecer'
                )
        );
        if(!empty($tipoAgente))
        {
        	$select->where('pa.idTipoAgente in(?)', $tipoAgente);
        }
        
        $select->where('pa.idPRONAC = ?', $idpronac);
        
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        $result = $this->fetchAll($select);
        return $result;
    }
    
    public function buscarParecerTipo($tipoAgente = null, $idpronac = null, $tipoParecer = null, $usu_codigo = null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('pa' => $this->_name),
                array(
                    "CONVERT(CHAR(10), pa.DtParecer, 103) AS DtParecer",
                    'pa.ResumoParecer as ResumoParecer',
                    'pa.ParecerFavoravel',
                    'pa.TipoParecer'
                )
        );
        if(!empty($tipoAgente))
        {
        	$select->where('pa.idTipoAgente in(?)', $tipoAgente);
        }
        if(!empty($tipoParecer))
        {
        	$select->where('pa.TipoParecer in(?)', $tipoParecer);
        }
        if(!empty($usu_codigo))
        {
        	$select->where('pa.Logon in(?)', $usu_codigo);
        }
        
        $select->where('pa.idPRONAC = ?', $idpronac);
        
        $result = $this->fetchAll($select);
        return $result;
    }
    
      public function buscaPareceresEmAprovacao($mecanismo,$QntdPorPagina=null, $PaginaAtual=null) {

            $TotalReg = $PaginaAtual*$QntdPorPagina;
            $select =  new Zend_Db_Expr("select * from (select top ". $QntdPorPagina ." * from (SELECT TOP ". $TotalReg ."
                pa.*, (pr.AnoProjeto+pr.Sequencial) AS pronac, pr.NomeProjeto FROM SAC.dbo.Parecer AS pa
            INNER JOIN SAC.dbo.Projetos AS pr ON pr.IdPRONAC = pa.idPRONAC WHERE pr.Mecanismo = '".$mecanismo."' 
            order by pa.idParecer) as tabela order by idParecer desc) as tabela order by idParecer");

            try {
                $db = Zend_Registry::get('db');
                $db->setFetchMode(Zend_DB::FETCH_OBJ);
            } catch (Zend_Exception_Db $e) {
                $this->view->message = $e->getMessage();
            }
            //xd($select);
            return $db->fetchAll($select);
        }
        
        public function buscaPareceresTotal($mecanismo) {

            $select =  new Zend_Db_Expr("SELECT *, (pr.AnoProjeto+pr.Sequencial) AS pronac, pr.NomeProjeto FROM SAC.dbo.Parecer AS pa
            INNER JOIN SAC.dbo.Projetos AS pr ON pr.IdPRONAC = pa.idPRONAC WHERE pr.Mecanismo = $mecanismo
            order by pa.idParecer");
            //xd($select);
            try {
                $db = Zend_Registry::get('db');
                $db->setFetchMode(Zend_DB::FETCH_OBJ);
            } catch (Zend_Exception_Db $e) {
                $this->view->message = $e->getMessage();
            }
            //xd($select);
            return $db->fetchAll($select);
        }

        public function verificaProjSituacaoCNIC($pronac) {

            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => 'Parecer'),
                    array('a.IdPRONAC', 'a.NumeroReuniao')
            );
            $select->joinInner(
                    array('b' => 'tbReuniao'),
                    'a.NumeroReuniao = b.NrReuniao',
                    array('')
            );

            $select->where('b.stEstado = ?', 0);
            $select->where('a.AnoProjeto+a.Sequencial = ?',  $pronac);
            return $this->fetchAll($select);
        }
        
        public function statusDeAvaliacao($idPronac) {

            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => 'Parecer'),
                    array(
                        new Zend_Db_Expr("CASE
                            WHEN ParecerFavoravel = '1' AND idTipoAgente = 1
                                THEN 'PROJETO INDEFERIDO'
                           WHEN ParecerFavoravel = '2' AND idTipoAgente = 1
                                THEN 'PROJETO APROVADO'
                           WHEN ParecerFavoravel = '1' AND idTipoAgente = 6
                                THEN 'PROJETO INDEFERIDO'
                           WHEN ParecerFavoravel = '2' AND idTipoAgente = 6
                                THEN 'PROJETO APROVADO'
                           END as ParecerFavoravel
                        ")
                    )
            );
            $select->where('a.IdPRONAC = ?', $idPronac);
            $select->where('a.TipoParecer = ?', 1);
            return $this->fetchAll($select);
        }

        public function identificacaoParecerConsolidado($idPronac) {

            $select =  new Zend_Db_Expr("
                SELECT p.idPronac AS Codigo, p.AnoProjeto+p.Sequencial AS Pronac, pr.NomeProjeto,
                   CASE
                     WHEN TipoParecer = '1'
                          THEN 'Aprovação'
                     WHEN TipoParecer = '2'
                          THEN 'Complementação'
                     WHEN TipoParecer = '3'
                          THEN 'Prorrogação'
                     WHEN TipoParecer = '4'
                          THEN 'Redução'
                   END AS TipoParecer,
                   CASE
                     WHEN ParecerFavoravel = '1'
                          THEN 'Não'
                     WHEN ParecerFavoravel = '2'
                          THEN 'Sim'
                          ELSE 'Sim com restrições'
                   END AS ParecerFavoravel,
                   CASE
                     WHEN Enquadramento = '1'
                          THEN 'Artigo 26'
                     WHEN Enquadramento = '2'
                          THEN 'Artigo 18'
                   END AS Enquadramento,
                   DtParecer,ResumoParecer,SugeridoReal
            FROM Parecer p
            LEFT JOIN Enquadramento e ON (p.idPronac = e.idPronac)
            LEFT JOIN Projetos pr on (p.idPronac = pr.idPronac)
            WHERE p.idTipoAgente = 1 AND p.idPronac = $idPronac ");
            try {
                $db = Zend_Registry::get('db');
                $db->setFetchMode(Zend_DB::FETCH_OBJ);
            } catch (Zend_Exception_Db $e) {
                $this->view->message = $e->getMessage();
            }
            return $db->fetchAll($select);
        }
        
        public function cidadoPareceConsolidado($idPronac) {

            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                array('p' => $this->_name),
                array(
                    New Zend_Db_Expr("
                         p.idPronac,
                         p.AnoProjeto+p.Sequencial AS Pronac,
                         pr.NomeProjeto,
                         CASE
                            WHEN TipoParecer = '1'
                                 THEN 'Aprovação'
                            WHEN TipoParecer = '2'
                                 THEN 'Complementação'
                            WHEN TipoParecer = '3'
                                 THEN 'Prorrogação'
                            WHEN TipoParecer = '4'
                                 THEN 'Redução'
                          END AS TipoParecer,
                          CASE
                            WHEN ParecerFavoravel = '1'
                                 THEN 'Não'
                            WHEN ParecerFavoravel = '2'
                                 THEN 'Sim'
                                 ELSE 'Sim com restrições'
                          END AS ParecerFavoravel,
                          CASE
                            WHEN Enquadramento = '1'
                                 THEN 'Artigo 26'
                            WHEN Enquadramento = '2'
                                 THEN 'Artigo 18'
                          END AS Enquadramento,
                          DtParecer,
                          ResumoParecer,
                          SugeridoReal
                    ")
                )
            );
            $select->joinLeft(
                array('e' => 'Enquadramento'),'p.idPronac = e.idPronac',
                array(''), 'SAC.dbo'
            );
            $select->joinInner(
                array('pr' => 'Projetos'),'p.idPronac = pr.idPronac',
                array(''), 'SAC.dbo'
            );
            $select->where('p.idTipoAgente = ?', 1);
            $select->where('p.idPronac = ?', $idPronac);

            //xd($select->assemble());
            return $this->fetchAll($select);
        }

    
}
?>
