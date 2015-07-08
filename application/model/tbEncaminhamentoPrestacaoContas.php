<?php
/**
 * Description of tbEncaminhamentoPrestacaoContas
 *
 * @author Emerson Silva
 */
class tbEncaminhamentoPrestacaoContas extends GenericModel {

    protected $_name   = 'tbEncaminhamentoPrestacaoContas';
    protected $_schema = 'scSAC';
    protected $_banco  = 'BDCORPORATIVO';
    //protected $_primary = 'idEncaminhamentoPrestacaoContas';
    
        
    
    
    /*
     * INSERT INTO [BDCORPORATIVO].[scSAC].[tbEncaminhamentoPrestacaoContas]
					([idPronac],[idAgenteOrigem],[dtInicioEncaminhamento],[dsJustificativa]
					,[idOrgao],[idAgenteDestino],[idTipoAgente],[dtFimEncaminhamento]
					,[stAtivo]) 
		VALUES (0611188,170,'2010-01-17','Modelo de Encaminhamento de Prestação de Conta para o técnico',
					5,115,9,'2010-04-18',0)
     **/
    

 /*   public function InsertEncaminhamentoPrestacaoContas($idPronac) {
        $id = $this->insert(array('idPronac'=>$idPronac,'idAgenteOrigem'=>'idAgenteOrigem',
                                  'dtInicioEncaminhamento'=>'dtInicioEncaminhamento',
                                  'dsJustificativa'=>'dsJustificativa','idOrgao'=>'idorgao',
                                  'idAgenteDestino'=>'idAgenteDestino','idTipoAgente'=>'idTipoAgente',
                                  'dtFimEncaminhamento'=>'dtFimEncaminhamento','stAtivo'=>'stAtivo' ));

        return $this->fetchRow($id);
    }

   */ 
   // $idPronac='115029';
    public function EncaminhamentoPrestacaoContas($idPronac){
    	$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('tbepc'=>$this->_schema.'.'.$this->_name),
                        array(
                              'tbepc.idAgenteDestino','tbepc.idAgenteOrigem',
                              'tbepc.dtInicioEncaminhamento','tbepc.idOrgao',
                              'tbepc.idPronac','tbepc.dtInicioEncaminhamento'
                              )
                      );
        $select->joinInner(
                            array('a'=>'Agentes'),
                            'a.idAgente = tbepc.idAgenteDestino',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('ag'=>'Agentes'),
                            'ag.idAgente = tbepc.idAgenteOrigem',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('u'=>'Usuarios'),
                            'u.usu_identificacao = a.CNPJCPF',
                            array('NomeDestino'=>'u.usu_nome'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('uu'=>'Usuarios'),
                            'uu.usu_identificacao = ag.CNPJCPF',
                            array('NomeOrigem'=>'uu.usu_nome'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('o'=>'Orgaos'),
                            'u.usu_orgao = o.org_codigo',
                            array('o.org_sigla'),
                            'TABELAS.dbo'
                           );
		$select->where('tbepc.idPronac = ?',$idPronac);  
	    //xd($select->assemble());
		
		return $this->fetchAll($select);
    }

    
    public function BuscaEncaminhamentoPrestacaoContas($idOrgao, $situacao, $idAgenteDestino){
    	//xd($idOrgao."-".$idAgenteDestino);
    	$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('tbepc'=>$this->_schema.'.'.$this->_name),
                        array(
						      'idAgenteOrigem',
						      'dtInicioEncaminhamento',
						      'dsJustificativa',
						      'idOrgaoDestino',
						      'idOrgaoOrigem',
						      'idAgenteDestino',
						      'cdGruposDestino',
						      'cdGruposOrigem',
						      'dtFimEncaminhamento',
						      'idSituacaoEncPrestContas',
						      'idSituacao',
						      'stAtivo',
                                                      'idEncPrestContas'
                              )
                      );
        $select->joinInner(
                            array('proj'=>'Projetos'),
                            'proj.IdPRONAC = tbepc.idPronac',
                            array("*"),
                            'SAC.dbo'
                           );
        $select->joinInner(
                            array('a'=>'Agentes'),
                            'a.idAgente = tbepc.idAgenteDestino',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('ag'=>'Agentes'),
                            'ag.idAgente = tbepc.idAgenteOrigem',
                            array(),
                            'AGENTES.dbo'
                           );
        /*$select->joinInner(
                            array('u'=>'Usuarios'),
                            'u.usu_identificacao = a.CNPJCPF',
                            array('NomeDestino'=>'u.usu_nome'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('uu'=>'Usuarios'),
                            'uu.usu_identificacao = ag.CNPJCPF',
                            array('NomeOrigem'=>'uu.usu_nome'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('o'=>'Orgaos'),
                            'u.usu_orgao = o.org_codigo',
                            array('o.org_sigla'),
                            'TABELAS.dbo'
                           );*/
        $select->joinInner(
                            array('s'=>'Segmento'),
                            'proj.Segmento = s.Codigo',
                            array('s.Descricao as Segmento'),
                            'SAC.dbo'
                           );                    
        $select->joinInner(
                            array('ar'=>'Area'),
                            'proj.Area = ar.Codigo',
                            array('ar.Descricao as Area'),
                            'SAC.dbo'
                           );                   

                $select->where('tbepc.idOrgaoDestino = ?',$idOrgao);
                $select->where('tbepc.idAgenteDestino = ?',$idAgenteDestino);
		        $select->where('tbepc.idSituacaoEncPrestContas = ?',$situacao);
		//$select->where('tbepc.idTipoAgente = ?',11);  
	    //xd($select->assemble());
		
		return $this->fetchAll($select);
    }
    

   public function HistoricoEncaminhamentoPrestacaoContas($idPronac){
    	$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('tbepc'=>$this->_name),
                        array(
                              '*',
                              'CONVERT(CHAR(23), tbepc.dtInicioEncaminhamento, 120) AS dtInicioEncaminhamento'
                              ),$this->_banco.'.'.$this->_schema
                      );
        $select->joinInner(
                            array('a'=>'Agentes'),
                            'a.idAgente = tbepc.idAgenteDestino',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('ag'=>'Agentes'),
                            'ag.idAgente = tbepc.idAgenteOrigem',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('n'=>'Nomes'),
                            'n.idAgente = tbepc.idAgenteOrigem',
                            array('n.Descricao as NomeOrigem'),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('no'=>'Nomes'),
                            'no.idAgente = tbepc.idAgenteDestino',
                            array('no.Descricao as NomeDestino'),
                            'AGENTES.dbo'
                           );             
                           
        $select->where('tbepc.idPronac = ?',$idPronac);
	
	return $this->fetchAll($select);
    }
    
    
   
    public function BuscaEmitirParecerPrestacaoContas($idPronac,$idOrgao){
    	$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('tbepc'=>$this->_schema.'.'.$this->_name),
                        array(
                              'idAgenteDestino',
                              'idAgenteOrigem',
                              'dtInicioEncaminhamento',
                              'idOrgao',
                              'idPronac',
                        	  'idSituacao'
                              )
                      );
        $select->joinInner(
                            array('proj'=>'Projetos'),
                            'proj.IdPRONAC = tbepc.idPronac',
                            array("*"),
                            'SAC.dbo'
                           );
        $select->joinInner(
                            array('a'=>'Agentes'),
                            'a.idAgente = tbepc.idAgenteDestino',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('ag'=>'Agentes'),
                            'ag.idAgente = tbepc.idAgenteOrigem',
                            array(),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('u'=>'Usuarios'),
                            'u.usu_identificacao = a.CNPJCPF',
                            array('NomeDestino'=>'u.usu_nome'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('uu'=>'Usuarios'),
                            'uu.usu_identificacao = ag.CNPJCPF',
                            array('NomeOrigem'=>'uu.usu_nome'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('o'=>'Orgaos'),
                            'u.usu_orgao = o.org_codigo',
                            array('o.org_sigla'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('s'=>'Segmento'),
                            'proj.Segmento = s.Codigo',
                            array('s.Descricao as Segmento'),
                            'SAC.dbo'
                           );                    
        $select->joinInner(
                            array('ar'=>'Area'),
                            'proj.Area = ar.Codigo',
                            array('ar.Descricao as Area'),
                            'SAC.dbo'
                           );                   
                           
		$select->where('tbepc.idOrgao = ?',$idOrgao);  
		$select->where('tbepc.idPronac = ?',$idPronac);  
	   // xd($select->assemble());
		
		return $this->fetchAll($select);
    }
    
	/*public function InsertParecerTecnicoPrestacaoContas($dados) {
        try {
            $insert = $this->insert($dados);
        } catch (Zend_Db_Table_Exception $e) {
            return ' -> tbEncaminhamentoPrestacaoContas. Erro:' . $e->getMessage();
        }
    }*/
    
    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    /*public function buscar($where=array(), $order=array(), $tamanho=-1, $inicio=-1) {
        $slct = $this->select();
        $slct->from($this, array("idTipoAgente", "idEncPrestContas"=>"idEncaminhamentoPrestacaoContas"));

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slct->limit($tamanho, $tmpInicio);
        }
        //xd($slct->__toString());
        return $this->fetchAll($slct);
    }*/



    public function buscarAtoresPrestacaoContas($idPronac, $idusuario=null){

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('epc'=>$this->_schema.'.'.$this->_name),
                        array()
                      );

        $select->joinInner(
                            array('usu2'=>'Usuarios'),
                            'epc.idAgenteDestino = usu2.usu_codigo',
                            array(),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('ag2'=>'Agentes'),
                            'ag2.CNPJCPF = usu2.usu_identificacao',
                            array('idAgente2'=>'ag2.idAgente'),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('nm2'=>'Nomes'),
                            'nm2.idAgente = ag2.idAgente',
                            array('Nome2'=>'nm2.Descricao'),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('org2'=>'Orgaos'),
                            'org2.Codigo = epc.idOrgaoDestino',
                            array('Orgao2'=>'org2.Sigla'),
                            'SAC.dbo'
                           );
        $select->joinInner(
                            array('gr2'=>'Grupos'),
                            'epc.cdGruposDestino = gr2.gru_codigo',
                            array('Perfil2'=>'gr2.gru_nome','cdPerfil2'=>'gr2.gru_codigo'),
                            'TABELAS.dbo'
                           );
        $select->joinInner(
                            array('nm'=>'Nomes'),
                            'nm.idAgente = epc.idAgenteOrigem',
                            array('Nome'=>'nm.Descricao','nm.idAgente'),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('org'=>'Orgaos'),
                            'org.Codigo = epc.idOrgaoOrigem',
                            array('Orgao'=>'org.Sigla'),
                            'SAC.dbo'
                           );
        $select->joinInner(
                            array('gr'=>'Grupos'),
                            'epc.cdGruposOrigem = gr.gru_codigo',
                            array('Perfil'=>'gr.gru_nome','cdPerfil'=>'gr.gru_codigo'),
                            'TABELAS.dbo'
                           );

        $select->where('idPronac = ?', $idPronac);
        //$select->where('usu2.usu_codigo <> ?', $idusuario);
//        xd($select->assemble());
        return $this->fetchAll($select);
    }
}
?>
