<?php
/**
 * DAO tbPlanilhaAprovacao
 * @since 26/12/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbPlanilhaAprovacao extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "tbPlanilhaAprovacao";

    /**
     * Método para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o último id cadastrado)
     */
    public function cadastrarDados($dados) {
        return $this->insert($dados);
    } // fecha método cadastrarDados()


    /**
     * Método para alterar
     * @access public
     * @param array $dados
     * @param integer $where
     * @return integer (quantidade de registros alterados)
     */
    public function alterarDados($dados, $where) {
        $where = "idPlanilhaAprovacao = " . $where;
        return $this->update($dados, $where);
    } // fecha método alterarDados()


    public function buscarItensOrcamentarios($where, $order = array()) {
        // criando objeto do tipo select
        $slct = $this->select();
        $slct->setIntegrityCheck(false);

        $slct->from(
                array('a' => $this->_name),
                array('idPlanilhaItem')
        );

        $slct->joinLeft(
                array('b' => 'tbPlanilhaItens'), "a.idPlanilhaItem = b.idPlanilhaItens",
                array('Descricao'), 'SAC.dbo'
        );

        // adicionando clausulas where
        foreach ($where as $coluna=>$valor) {
            $slct->where($coluna, $valor);
        }

        $slct->group('a.idPlanilhaItem');
        $slct->group('b.Descricao');

        //adicionando linha order ao select
        $slct->order($order);

        //xd($slct->assemble());
        // retornando os registros
        return $this->fetchAll($slct);

    } // fecha método alterarDados()

    public function copiandoPlanilhaRecurso($idPronac){
        
        $sql = "INSERT INTO SAC.dbo.tbPlanilhaAprovacao
                    (tpPlanilha,dtPlanilha,idPlanilhaProjeto,idPlanilhaProposta,idPronac,idProduto,idEtapa,idPlanilhaItem,dsItem,idUnidade,
                    qtItem,nrOcorrencia,vlUnitario,qtDias,tpDespesa,tpPessoa,nrContraPartida,nrFonteRecurso,idUFDespesa,idMunicipioDespesa,
                    dsJustificativa,idAgente,StAtivo)
              SELECT 'CO',GETDATE(),idPlanilhaProjeto,idPlanilhaProposta,'$idPronac',idProduto,idEtapa,idPlanilhaItem,Descricao,idUnidade,
                        Quantidade,Ocorrencia,ValorUnitario,QtdeDias,TipoDespesa,TipoPessoa,Contrapartida,FonteRecurso,UFDespesa,
                        MunicipioDespesa,Justificativa,idUsuario,'S'
                        FROM SAC.dbo.tbPlanilhaProjeto
                        WHERE idPronac = '$idPronac'
        ";
//        xd($sql);
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        // retornando os registros conforme objeto select
        return $db->fetchAll($sql);
    }
    
    public function copiandoPlanilhaRemanejamento($idPronac){
        
        $sql = "INSERT INTO SAC.dbo.tbPlanilhaAprovacao
                        (tpPlanilha,dtPlanilha,idPlanilhaProjeto,idPlanilhaProposta,idPronac,idProduto,idEtapa,idPlanilhaItem,dsItem,idUnidade,
                        qtItem,nrOcorrencia,vlUnitario,qtDias,tpDespesa,tpPessoa,nrContraPartida,nrFonteRecurso,idUFDespesa,idMunicipioDespesa,
                        dsJustificativa,idAgente,StAtivo)
               SELECT 'RP',GETDATE(),idPlanilhaProjeto,idPlanilhaProposta,'$idPronac',idProduto,idEtapa,idPlanilhaItem,dsItem,idUnidade,
                        qtItem,nrOcorrencia,vlUnitario,qtDias,tpDespesa,tpPessoa,nrContraPartida,nrFonteRecurso,idUFDespesa,idMunicipioDespesa,
                        dsJustificativa,idAgente,'N'
                        FROM SAC.dbo.tbPlanilhaAprovacao
                        WHERE idPronac = '$idPronac'
        ";
        //xd($sql);
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        // retornando os registros conforme objeto select
        return $db->fetchAll($sql);
    }
    
    public function buscarDadosAvaliacaoDeItemRemanejamento($where = array()){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('a' => $this->_name),
                array(
                    New Zend_Db_Expr('a.idPRONAC, a.idPlanilhaAprovacao, a.idProduto, b.Descricao as descProduto, a.idEtapa,
                        c.Descricao as descEtapa, a.idPlanilhaItem, d.Descricao as descItem,
                        a.idUnidade, e.Descricao as descUnidade, a.qtItem as Quantidade, a.nrOcorrencia as Ocorrencia, 
                        a.vlUnitario as ValorUnitario, a.qtDias as QtdeDias, CAST(a.dsJustificativa as TEXT) as Justificativa, a.idAgente'
                    )
                )
        );
        $select->joinLeft(
            array('b' => 'Produto'), "a.idProduto = b.Codigo",
            array(), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbPlanilhaEtapa'), "a.idEtapa = c.idPlanilhaEtapa",
            array(), 'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'tbPlanilhaItens'), "a.idPlanilhaItem = d.idPlanilhaItens",
            array(), 'SAC.dbo'
        );
        $select->joinInner(
            array('e' => 'tbPlanilhaUnidade'), "a.idUnidade = e.idUnidade",
            array(), 'SAC.dbo'
        );
        
        foreach($where as $key=>$valor){
            $select->where($key, $valor);
        }
        //xd($select->assemble());
        
        return $this->fetchAll($select);
    }
    
    public function valorTotalPlanilha($where = array()){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('a' => $this->_name),
                array(
                    New Zend_Db_Expr('SUM(a.qtItem*a.nrOcorrencia*a.vlUnitario) AS Total')
                )
        );
        
        foreach($where as $key=>$valor){
            $select->where($key, $valor);
        }
        //xd($select->assemble());
        
        return $this->fetchAll($select);
    }

} // fecha class