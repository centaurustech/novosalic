<?php

class PlanilhaProposta extends GenericModel {

    //protected $_name = 'SAC.dbo.tbPlanilhaProposta';
    protected $_banco = 'SAC';
    protected $_name = 'tbPlanilhaProposta';

    public function somarPlanilhaProposta($idprojeto, $fonte=null, $outras=null, $where=array()) {
        $somar = $this->select();
        $somar->from($this,
                        array(
                            'sum(Quantidade*Ocorrencia*ValorUnitario) as soma'
                        )
                )
                ->where('idProjeto = ?', $idprojeto)
                ->where('idProduto <> ?', '206');
        if ($fonte) {
            $somar->where('FonteRecurso = ?', $fonte);
        }
        if ($outras) {
            $somar->where('FonteRecurso <> ?', $outras);
        }
        
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $somar->where($coluna, $valor);
        }
        
        return $this->fetchRow($somar);
    }
    
    public function somarPlanilhaPropostaDivulgacao($idprojeto, $fonte=null, $outras=null) {
        $somar = $this->select();
        $somar->from($this,
                        array(
                            'sum(Quantidade*Ocorrencia*ValorUnitario) as soma'
                        )
                )
                ->where('idProjeto = ?', $idprojeto)
                ->where('idEtapa = ?', 3);
        if ($fonte) {
            $somar->where('FonteRecurso = ?', $fonte);
        }
        if ($outras) {
            $somar->where('FonteRecurso <> ?', $outras);
        }
        //xd($somar->assemble());
        return $this->fetchRow($somar);
    }
    
    //Criado no dia 07/10/2013 - Jefferson Alessandro
    public function buscarDadosAvaliacaoDeItem($idPlanilhaProposta){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('a' => $this->_name),
                array(
                    New Zend_Db_Expr('a.idPlanilhaProposta, a.idProduto, b.Descricao as descProduto, a.idEtapa,
                        c.Descricao as descEtapa, a.idPlanilhaItem, d.Descricao as descItem, a.Unidade, e.Descricao as descUnidade,
                        a.Quantidade, a.Ocorrencia, a.ValorUnitario, a.QtdeDias'
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
            array('e' => 'tbPlanilhaUnidade'), "a.Unidade = e.idUnidade",
            array(), 'SAC.dbo'
        );
        $select->where('a.idPlanilhaProposta = ?', $idPlanilhaProposta);
        
        return $this->fetchAll($select);
    }

}