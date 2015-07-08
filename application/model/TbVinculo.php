<?php 
class TbVinculo extends GenericModel{
    
    protected $_banco = 'Agentes';
    protected $_name = 'tbVinculo';
    


	public function buscarVinculoProponenteResponsavel($where=array()) 
    {
    	$slct = $this->select();
        $slct->distinct();
        $slct->setIntegrityCheck(false);
        
        $slct->from(
                array('VI' => $this->_name), 
                array('*')
        );
        
        $slct->joinInner(
                array('AG' => 'Agentes'), "AG.idAgente = VI.idAgenteProponente", 
                array('AG.CNPJCPF')
        );
        
        $slct->joinInner(
                array('NM' => 'Nomes'), "NM.idAgente = AG.idAgente", 
                array('NM.Descricao AS NomeProponente')
        );
        
        $slct->joinLeft(
                array('SGA' => 'SGCacesso'), "SGA.IdUsuario = VI.idUsuarioResponsavel", 
                array('SGA.IdUsuario AS idUsuarioResponsavel', 'SGA.Nome AS NomeResponsavel', 'SGA.Cpf AS CpfResponsavel'), 'CONTROLEDEACESSO.dbo'
        );

        $slct->joinLeft(
                array('VP' => 'tbVinculoProposta'), "VP.idVinculo = VI.idVinculo", 
                array('VP.idVinculoProposta', 'VP.siVinculoProposta')
        );
       
        foreach ($where as $coluna => $valor) 
        {
            $slct->where($coluna, $valor);
        }
       // xd($slct->assemble());
        return $this->fetchAll($slct);
    }
	
	
	/* Método que lista os vinculos do Proponente ao Responsavel
     * 
     * */
    public function buscarProponenteResponsavel($idUsuarioLogado, $mecanismo = false)
    {
        $slct1 = $this->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(
                            array('a' => 'Projetos'),
                            array(new Zend_Db_Expr('0 as Ordem'),'CgcCpf as CNPJCPF'), 'SAC.dbo'
                    )
                    ->joinInner(
                            array('b' => 'Agentes'), "a.CgcCpf = b.CNPJCPF",
                            array('idAgente', 'dbo.fnNome(b.idAgente) as NomeProponente'), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('c' => 'SGCacesso'), "a.CgcCpf = c.Cpf",
                            array(), 'CONTROLEDEACESSO.dbo'
                    )
                    ->where('c.IdUsuario = ?', $idUsuarioLogado);

        if(!empty($mecanismo)){
            $slct1->where('a.Mecanismo = ?', $mecanismo);
        }


        $slct2 = $this->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(
                            array('a' => 'Projetos'),
                            array(new Zend_Db_Expr('1 as Ordem')), 'SAC.dbo'
                    )
                    ->joinInner(
                            array('b' => 'Agentes'), "a.CgcCpf = b.CNPJCPF",
                            array('CNPJCPF', 'idAgente', 'dbo.fnNome(b.idAgente) as NomeProponente'), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('c' => 'tbProcuradorProjeto'), "a.IdPRONAC = c.idPronac",
                            array(), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('d' => 'tbProcuracao'), "c.idProcuracao = d.idProcuracao",
                            array(), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('f' => 'Agentes'), "d.idAgente = f.idAgente",
                            array(), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('e' => 'SGCacesso'), "f.CNPJCPF = e.Cpf",
                            array(), 'CONTROLEDEACESSO.dbo'
                    )
                    ->where('c.siEstado = ?', 2)
                    ->where('e.IdUsuario = ?', $idUsuarioLogado);
        
        if(!empty($mecanismo)){
            $slct2->where('a.Mecanismo = ?', $mecanismo);
        }

        $slct3 = $this->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(
                            array('a' => 'Projetos'),
                            array(new Zend_Db_Expr('2 as Ordem')), 'SAC.dbo'
                    )
                    ->joinInner(
                            array('b' => 'Agentes'), "a.CgcCpf = b.CNPJCPF",
                            array('CNPJCPF', 'idAgente', 'dbo.fnNome(b.idAgente) as NomeProponente'), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('c' => 'Vinculacao'), "b.idAgente = c.idVinculoPrincipal",
                            array(), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('d' => 'Agentes'), "c.idAgente = d.idAgente",
                            array(), 'AGENTES.dbo'
                    )
                    ->joinInner(
                            array('e' => 'SGCacesso'), "d.CNPJCPF = e.Cpf",
                            array(), 'CONTROLEDEACESSO.dbo'
                    )
                    ->where('e.IdUsuario = ?', $idUsuarioLogado);

        if(!empty($mecanismo)){
            $slct3->where('a.Mecanismo = ?', $mecanismo);
        }

        $slctUnion = $this->select()
                            ->union(array('('.$slct1.')', '('.$slct2.')', '('.$slct3.')'))
                            ->order('Ordem ASC')
                            ->order('NomeProponente ASC');
//        xd($slctUnion->assemble());

        return $this->fetchAll($slctUnion);
    }
	
	
	/* Método que lista os responsáveis
     * 
     * */
    public function buscarResponsaveis($where=array() , $idAgenteProponente) 
    {
    	$slct = $this->select();
        $slct->distinct();
        $slct->setIntegrityCheck(false);
        
        $slct->from(
                array('SGA' => 'SGCacesso'), 
                array('SGA.Nome AS NomeResponsavel', 'SGA.Cpf AS CpfResponsavel', 'SGA.IdUsuario AS idResponsavel'), 'CONTROLEDEACESSO.dbo'
        );

        $slct->joinLeft(
                array('VI' => $this->_name),'VI.idUsuarioResponsavel = SGA.IdUsuario AND idAgenteProponente = '.$idAgenteProponente, 
                array('*')
        );
        
        $slct->joinLeft(
                array('ag' => 'Agentes'), "SGA.Cpf = ag.CNPJCPF",
                array('ag.CNPJCPF', 'ag.idAgente')
        );
        $slct->joinLeft(
                array('v' => 'Visao'), "v.idAgente = ag.idAgente AND v.Visao = 146", 
                array('v.visao as UsuarioVinculo'), 'AGENTES.dbo'
        );
        
        foreach ($where as $coluna => $valor) 
        {
            $slct->where($coluna, $valor);
        }
        //xd($slct->assemble());
        return $this->fetchAll($slct);
    }



	/**
	 * Método para buscar os Proponentes vinculados a um determinado Responsável, bem como, os Projetos desses Proponentes
	 * @access public
	 * @param integer $idResponsavel
	 * @param integer $idPronac
	 * @return object
	 */
	public function buscarProponentesProjetosResponsavel($idResponsavel, $idPronac)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(array('v' => $this->_name));

		$select->joinInner(
			array('a' => 'Agentes'),
			'v.idAgenteProponente = a.idAgente',
			array(),
			'AGENTES.dbo'
		);

		$select->joinInner(
			array('p' => 'Projetos'),
			'a.CNPJCPF = p.CgcCpf',
			array(),
			'SAC.dbo'
		);

		$select->where('v.idUsuarioResponsavel = ?', $idResponsavel);
		$select->where('p.IdPRONAC             = ?', $idPronac);

		$select->order('v.idUsuarioResponsavel ASC');
		$select->order('v.idAgenteProponente ASC');
		$select->order('p.NomeProjeto ASC');

		return $this->fetchAll($select);
	} // fecha método buscarProponentesProjetosResponsavel()



        /**
	 * Método para buscar os Proponentes vinculados a um determinado Responsável
	 * @access public
	 * @param integer $idResponsavel
	 * @param integer $idPronac
	 * @return object
	 */
	public function buscarProponentes($idResponsavel)
	{
            $slctResponsavel = $this->select()
                                ->setIntegrityCheck(false)
                                ->distinct()
                                ->from(
                                        array('a' => 'Agentes'),
                                        array('CNPJCPF', 'idAgente', 'dbo.fnNome(a.idAgente) AS NomeProponente'), 'AGENTES.dbo'
                                )
                                ->joinInner(
                                        array('c' => 'SGCacesso'), "a.CNPJCPF = c.Cpf",
                                        array(), 'CONTROLEDEACESSO.dbo'
                                )

                                ->where('c.IdUsuario = ?', $idResponsavel);


            $slctProponentes = $this->select()
                                ->setIntegrityCheck(false)
                                ->distinct()
                                ->from(
                                        array('a' => 'Agentes'),
                                        array('CNPJCPF', 'idAgente', 'dbo.fnNome(idAgente) as NomeProponente'), 'AGENTES.dbo'
                                )
                                ->joinInner(
                                        array('v' => 'tbVinculo'), "a.idAgente = v.idAgenteProponente",
                                        array(), 'AGENTES.dbo'
                                )
                                ->joinInner(
                                        array('k' => 'SGCacesso'), "k.IdUsuario = v.idUsuarioResponsavel",
                                        array(), 'CONTROLEDEACESSO.dbo'
                                )
                                ->where('k.IdUsuario = ?', $idResponsavel)
                                ->where('v.siVinculo = ?', 2);


            $slctUnion = $this->select()
                            ->union(array('('.$slctResponsavel.')', '('.$slctProponentes.')'))
                            ->order('NomeProponente ASC');

            return $this->fetchAll($slctUnion);

	} // fecha método buscarProponentes()


}
