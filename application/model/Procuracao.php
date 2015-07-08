<?php

class Procuracao extends GenericModel {

    protected $_banco = 'Agentes';
    protected $_name = 'dbo.tbProcuracao';

    public function buscarProcuracaoProjeto($where = array()) {
        $s = $this->select();
        $s->setIntegrityCheck(false);
        $s->from(
                array('p' => $this->_name), 
                array(
                    'p.idProcuracao',
                    'p.idDocumento',
                    'p.siProcuracao',
                    'p.dsJustificativa',
                    'p.dsObservacao'
                )
        );
        $s->joinInner(
                array('vprp' => 'tbVinculoProposta'), "vprp.siVinculoProposta in (2)",
                array(
                    'vprp.idPreProjeto',
                    'vprp.idVinculoProposta',
                    'vprp.siVinculoProposta'
                )
                
        );
        $s->joinLeft(
                array('pr' => 'Projetos'), "pr.idProjeto = vprp.idPreProjeto", 
                array(	'(pr.AnoProjeto+pr.Sequencial) as pronac',
                		'pr.OrgaoOrigem',
                		'NomeProjeto'), 'SAC.dbo'
                
        );
        $s->joinLeft(
                array('org' => 'Orgaos'), "pr.OrgaoOrigem = org.Codigo", 
                array('org.idSecretaria as OrgaoSuperior'), 'SAC.dbo'
                
        );
        $s->joinInner(
                array('v' => 'tbVinculo'), "v.idVinculo = vprp.idVinculo", 
                array(
                    'v.idUsuarioResponsavel',
                    'v.idAgenteProponente',
                    'v.dtVinculo'
                )
        );
        $s->joinInner(
                array('nmr' => 'SGCacesso'), "nmr.IdUsuario = v.idUsuarioResponsavel",
                array(
                    'nmr.Nome as NomeResponsavel'
                ), 'CONTROLEDEACESSO.dbo'
        );
        $s->joinInner(
                array('nmp' => 'Nomes'), "nmp.idAgente = v.idAgenteProponente", 
                array(
                    'nmp.Descricao as NomeProponente',
                )
        );
        $s->joinInner(
                array('d' => 'tbDocumento'), "p.idDocumento = d.idDocumento", 
                array(''),
                "BDCORPORATIVO.scCorp"
        );
        $s->joinInner(
                array('a' => 'tbArquivo'), "d.idArquivo = a.idArquivo", 
                array(
                    'a.idArquivo',
                    'a.dtEnvio',
                    'a.nmArquivo'
                ),
                "BDCORPORATIVO.scCorp"
        );
        foreach($where as $key=>$valor){
            $s->where($key, $valor);
        }
        
        $s->order('p.siProcuracao desc');
        
//        xd($s->assemble());
        return $this->fetchAll($s);
    }
    
    public function buscarProcuracaoAceita($where = array()) {
        $s = $this->select();
        $s->setIntegrityCheck(false);
        $s->from(
                array('p' => $this->_name), 
                array(
                    'p.idProcuracao',
                    'p.idDocumento',
                    'p.siProcuracao',
                    'p.dsJustificativa'
                )
        );
        $s->joinInner(
                array('vprp' => 'tbVinculoProposta'), "p.idVinculoProposta = vprp.idVinculoProposta", 
                array(
                    'vprp.idPreProjeto',
                    'vprp.idVinculoProposta',
                    'vprp.siVinculoProposta'
                )
                
        );
        
        foreach($where as $key=>$valor){
            $s->where($key, $valor);
        }
//       xd($s->assemble());
        return $this->fetchAll($s);
    }


    public function buscarProcuracoes($where = array()) {
        $s = $this->select();
        $s->setIntegrityCheck(false);
        $s->from(
                array('p' => $this->_name),
                array(
                    'p.idProcuracao',
                    'p.idDocumento',
                    'p.siProcuracao',
                    'p.dsJustificativa',
                    'p.dsObservacao'
                )
        );
        $s->joinInner(
                array('vprp' => 'tbVinculoProposta'), "vprp.siVinculoProposta in (2)",
                array(
                    'vprp.idPreProjeto',
                    'vprp.idVinculoProposta',
                    'vprp.siVinculoProposta'
                )

        );
        $s->joinInner(
                array('pr' => 'Projetos'), "pr.idProjeto = vprp.idPreProjeto",
                array(	'(pr.AnoProjeto+pr.Sequencial) as pronac',
                		'pr.OrgaoOrigem',
                		'NomeProjeto'), 'SAC.dbo'

        );
        $s->joinInner(
                array('org' => 'Orgaos'), "pr.Orgao = org.Codigo",
                array('org.idSecretaria as OrgaoSuperior'), 'SAC.dbo'

        );
        $s->joinInner(
                array('v' => 'tbVinculo'), "v.idVinculo = vprp.idVinculo",
                array(
                    'v.idUsuarioResponsavel',
                    'v.idAgenteProponente',
                    'v.dtVinculo'
                )
        );
        $s->joinInner(
                array('nmr' => 'SGCacesso'), "nmr.IdUsuario = v.idUsuarioResponsavel",
                array(
                    'nmr.Nome as NomeResponsavel'
                ), 'CONTROLEDEACESSO.dbo'
        );
        $s->joinInner(
                array('nmp' => 'Nomes'), "nmp.idAgente = v.idAgenteProponente",
                array(
                    'nmp.Descricao as NomeProponente',
                )
        );
        $s->joinInner(
                array('d' => 'tbDocumento'), "p.idDocumento = d.idDocumento",
                array(''),
                "BDCORPORATIVO.scCorp"
        );
        $s->joinInner(
                array('a' => 'tbArquivo'), "d.idArquivo = a.idArquivo",
                array(
                    'a.idArquivo',
                    'a.dtEnvio',
                    'a.nmArquivo'
                ),
                "BDCORPORATIVO.scCorp"
        );
        foreach($where as $key=>$valor){
            $s->where($key, $valor);
        }
        $s->order('p.siProcuracao desc');
//        xd($s->assemble());
        return $this->fetchAll($s);
    }

}

?>
