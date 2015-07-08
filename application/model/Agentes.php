<?php 

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Agentes
 *
 * @author augusto 
 */

class Agentes extends GenericModel {

    protected $_banco = 'Agentes';
    protected $_name = 'Agentes';
    protected $_schema = 'dbo';
    protected $_primary = 'idAgente';
    
    public function BuscarComponente() {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('A' => $this->_name)
        );
        $select->joinInner(
                array('N' => 'Nomes'), 'N.idAgente = A.idAgente', array('N.Descricao as nomeConselheiro', 'N.idAgente as agente')
        );
        $select->joinInner(
                array('V' => 'Visao'), 'V.idAgente = A.idAgente'
        );
        $select->where('V.Visao = ?', 210);
        $select->order('N.Descricao');

        return $this->fetchAll($select);
    }

    public function BuscaAgente($cnpjcpf=null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('A' => $this->_name)
        );

        $select->where('A.CNPJCPF = ?', $cnpjcpf);
//        xd($select->assemble());
        return $this->fetchAll($select);
    }

    public function inserirAgentes($dados) {
        $insert = $this->insert($dados);
        return $insert;
    }

    /**
     * Retorna registros do banco de dados referente a Agentes(Proponente)
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscarAgenteNome($where=array(), $order=array(), $tamanho=-1, $inicio=-1) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name));
        $slct->joinInner(array('m' => 'Nomes'), 'a.idAgente=m.idAgente');

        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
//xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    public function buscarFornecedor($where) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('A' => $this->_name), array('A.CNPJCPF', 'A.idAgente')
        );
//        $select->joinInner(
//                array('U' => 'vwUsuariosOrgaosGrupos'), 'U.usu_identificacao = A.CNPJCPF', array(), 'tabelas.dbo'
//        );
        $select->joinInner(
                array('N' => 'Nomes'), 'N.idAgente = A.idAgente', array('N.Descricao AS nome')
        );
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        $select->group('A.CNPJCPF');
        $select->group('A.idAgente');
        $select->group('N.Descricao');
        
        $select->order('N.Descricao');
//        xd($select->assemble());

        return $this->fetchAll($select);
    }

    public function buscarFornecedorFiscalizacao($where) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('A' => $this->_name), array('A.CNPJCPF', 'A.idAgente')
        );
        $select->joinInner(
                array('U' => 'vwUsuariosOrgaosGrupos'), 'U.usu_identificacao = A.CNPJCPF', array(), 'tabelas.dbo'
        );
        $select->joinInner(
                array('N' => 'Nomes'), 'N.idAgente = A.idAgente', array('N.Descricao AS nome')
        );
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        $select->group('A.CNPJCPF');
        $select->group('A.idAgente');
        $select->group('N.Descricao');

        $select->order('N.Descricao');
//        xd($select->assemble());

        return $this->fetchAll($select);
    }

    public function buscarAgenteVinculoResponsavel($where=array(), $order=array(), $tamanho=-1, $inicio=-1) {
        $slct = $this->select();
        $slct->distinct();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('ag' => $this->_name), array('ag.CNPJCPF')
        );
        $slct->joinInner(
                array('nm' => 'Nomes'), "nm.idAgente = ag.idAgente", array('nm.Descricao as NomeAgente')
        );
        $slct->joinLeft(
                array('vr' => 'tbVinculo'), "vr.idUsuarioResponsavel  = ag.idAgente", array("vr.idVinculo as idVinculoResponsavel")
        );
        $slct->joinInner(
                array('vprp' => 'tbVinculoProposta'), "vprp.idVinculo = vr.idVinculo", array("vprp.siVinculoProposta", "vprp.idPreProjeto", 'vprp.idVinculoProposta')
        );
        $slct->joinLeft(array('pr'=>'Projetos'), 'vprp.idPreProjeto = pr.idProjeto', array('(pr.AnoProjeto+pr.Sequencial) as pronac'), 'SAC.dbo');
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
//        xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    public function buscarAgenteVinculoProponente($where=array(), $order=array(), $tamanho=-1, $inicio=-1) 
    {
        $slct = $this->select();
        $slct->distinct();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('ag' => $this->_name), 
                array('ag.CNPJCPF', 'ag.idAgente')
        );
        $slct->joinInner(
                array('nm' => 'Nomes'), "nm.idAgente = ag.idAgente", 
                array('nm.Descricao as NomeAgente')
        );
        $slct->joinLeft(
                array('vp' => 'tbVinculo'), "vp.idAgenteProponente  = ag.idAgente", 
                array("vp.idVinculo as idVinculoProponente", "siVinculo", "idUsuarioResponsavel")
        );
        $slct->joinLeft(
                array('vprp' => 'tbVinculoProposta'), "vprp.idVinculo = vp.idVinculo", 
                array("vprp.siVinculoProposta", "vprp.idPreProjeto", "vprp.idVinculo")
        );
        
        $slct->joinLeft(
                array('pr' => 'Projetos'), "pr.idProjeto = vprp.idPreProjeto", 
                array('pr.IdPRONAC'), 'SAC.dbo'
        );
        
        $slct->joinLeft(
                array('usu' => 'Usuarios'), "usu.usu_identificacao = ag.CNPJCPF", 
                array('usu.usu_identificacao as UsuarioVinculo'), 'TABELAS.dbo'
        );
                
        foreach ($where as $coluna => $valor) 
        {
            $slct->where($coluna, $valor);
        }
//        xd($slct->assemble());
        return $this->fetchAll($slct);
    }
    
    public function buscarNovoProponente($where=array(), $idResponsavel)  
    {
        $slct = $this->select();
        $slct->distinct();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('ag' => $this->_name), 
                array('ag.CNPJCPF', 'ag.idAgente')
        );
        $slct->joinInner(
                array('nm' => 'Nomes'), "nm.idAgente = ag.idAgente", 
                array('nm.Descricao as NomeAgente')
        );
        $slct->joinLeft(
                array('vp' => 'tbVinculo'), "vp.idAgenteProponente  = ag.idAgente AND vp.idUsuarioResponsavel = $idResponsavel", 
                array("vp.idVinculo as idVinculoProponente", "siVinculo", "idUsuarioResponsavel")
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

    
    
    public function todosPareceristas() {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name), array('*'));

        $slct->joinInner(array('n' => 'Nomes'), 'a.idAgente = n.idAgente', array('n.idAgente AS idParecerista', 'n.Descricao AS Nome')
        );

        $slct->joinInner(array('v' => 'Visao'), 'n.idAgente = v.idAgente', array()
        );

        $slct->where('v.Visao = ?', 209);
        $slct->where('n.TipoNome = ?', 18);

        $slct->order('n.Descricao');
        //xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    public function pareceristasDoOrgao($idOrgao) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name), array());

        $slct->joinInner(array('n' => 'Nomes'), 'a.idAgente = n.idAgente', array('n.idAgente AS idParecerista', 'n.Descricao AS Nome'));

        $slct->joinInner(array('u' => 'vwUsuariosOrgaosGrupos'), 'a.CNPJCPF = u.usu_identificacao AND u.sis_codigo = 21 AND u.gru_codigo = 94 OR u.gru_codigo = 105', array('u.org_superior AS idOrgao'), 'TABELAS.dbo');

        $slct->joinInner(array('v' => 'Visao'), 'n.idAgente = v.idAgente', array());


        $dadosWhere = array('v.Visao = ?' => 209, 'n.TipoNome = ?' => 18, 'u.org_superior = ?' => $idOrgao);

        foreach ($dadosWhere as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        $slct->order('n.Descricao');
        //xd($slct->assemble());
        return $this->fetchAll($slct);
    }
    
    public function consultaPareceristasDoOrgao($idOrgao = null) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->distinct();
        $slct->from(array('a' => $this->_name), array(), array(), 'Agentes.dbo');

        $slct->joinInner(array('n' => 'Nomes'), 'a.idAgente = n.idAgente', array('n.idAgente AS idParecerista', 'n.Descricao AS Nome'));
		
        if($idOrgao == null){
        	$slct->joinInner(array('u' => 'vwUsuariosOrgaosGrupos'), 'a.CNPJCPF = u.usu_identificacao', array(), 'TABELAS.dbo');	
        }else{
        	$slct->joinInner(array('u' => 'vwUsuariosOrgaosGrupos'), 'a.CNPJCPF = u.usu_identificacao', array('u.uog_orgao AS idOrgao'), 'TABELAS.dbo');
        }

        $slct->joinInner(array('v' => 'Visao'), 'n.idAgente = v.idAgente', array());

        $dadosWhere['v.Visao = ?'] = 209;
        $dadosWhere['n.TipoNome = ?'] = 18;
        $dadosWhere['u.sis_codigo = ?'] = 21;
        $dadosWhere['u.gru_codigo = ?'] = 94;

        if (!empty($idOrgao)) {
            $dadosWhere['u.uog_orgao = ?'] = $idOrgao;
        }
        foreach ($dadosWhere as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }


        /*$dadosOr['u.gru_codigo = ?'] = 105;

        foreach ($dadosOr as $coluna => $valor) {
            $slct->orWhere($coluna, $valor);
        }*/

        $slct->order('n.Descricao');
//        xd($slct->assemble());
//        return $slct->assemble();
        return $this->fetchAll($slct);
    }
    
    public function buscarPareceristas($idOrgao, $idArea, $idSegmento) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->distinct();
        $slct->from(
            array('a' => $this->_name),
            array(), 'AGENTES.dbo'
        );
        $slct->joinInner(
            array('n' => 'Nomes'), 'a.idAgente = n.idAgente',
            array('u.usu_codigo AS id', 'n.Descricao AS nome'), 'AGENTES.dbo'
        );
        $slct->joinInner(
            array('u' => 'vwUsuariosOrgaosGrupos'), 'a.CNPJCPF = u.usu_Identificacao AND sis_codigo = 21 AND (gru_codigo = 94 OR gru_codigo = 105)',
            array(), 'TABELAS.dbo'
        );
        $slct->joinInner(
            array('v' => 'Visao'), 'n.idAgente = v.idAgente',
            array(), 'AGENTES.dbo'
        );
        $slct->joinInner(
            array('c' => 'tbCredenciamentoParecerista'), 'a.idAgente = c.idAgente',
            array(), 'AGENTES.dbo'
        );

        $dadosWhere["v.Visao = ?"] = 209;
        $dadosWhere["n.TipoNome = ?"] = 18;
        $dadosWhere["c.idCodigoArea = ?"] = $idArea;
        $dadosWhere["c.idCodigoSegmento = ?"] = $idSegmento;
        $dadosWhere["c.idverificacao = ?"] = 251;
        $dadosWhere["u.org_superior = ?"] = $idOrgao;
        $dadosWhere["NOT EXISTS(SELECT TOP 1 * FROM Agentes.dbo.tbAusencia WHERE Getdate() BETWEEN dtInicioAusencia AND dtFimAusencia AND idAgente = a.idAgente)"] = '';
        
        foreach ($dadosWhere as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        $slct->order('n.Descricao');
        //xd($slct->assemble());
        
        return $this->fetchAll($slct);
    }

    public function consultaPareceristasPainel($nome, $cpf) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name), array('*'));

        $slct->joinInner(array('n' => 'Nomes'), 'a.idAgente = n.idAgente', array('n.idAgente AS idParecerista', 'n.Descricao AS Nome')
        );

        $slct->joinInner(array('v' => 'Visao'), 'n.idAgente = v.idAgente', array()
        );

        $slct->where('v.Visao = ?', 209);
        $slct->where('n.TipoNome = ?', 18);

        if (!empty($nome)) {
            $slct->where("n.Descricao like '%" . $nome . "%'");
        }
        if (!empty($cpf)) {
            $slct->where('a.CNPJCPF = ?', $cpf);
        }

        $slct->distinct();
        $slct->order('n.Descricao');
        //xd($slct->assemble());
//        return $slct->assemble();
        return $this->fetchAll($slct);
    }

    public function dadosParecerista($where) {

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array("ag" => $this->_name)
                , array("ag.idAgente")
        );
        $select->joinInner(
                array("nm" => "Nomes")
                , "nm.idAgente = ag.idAgente"
                , array(
            "nmParecerista" => "nm.Descricao"
                )
                , 'AGENTES.dbo'
        );
        $select->joinLeft(
                array("cp" => "tbCredenciamentoParecerista")
                , "cp.idAgente = ag.idAgente"
                , array(
            "nmParecerista" => "nm.Descricao",
            "qtPonto"
                )
                , 'AGENTES.dbo'
        );
        $select->joinLeft(
                array("ar" => "Area")
                , "ar.Codigo = cp.idCodigoArea"
                , array(
            'Area' => 'ar.Descricao'
                )
                , 'SAC.dbo'
        );
        $select->joinLeft(
                array("seg" => "Segmento")
                , "seg.Codigo = cp.idCodigoSegmento"
                , array(
            'Segmento' => 'seg.Descricao'
                )
                , 'SAC.dbo'
        );
        $select->joinLeft(
                array("au" => "tbAusencia")
                , "au.idAgente = ag.idAgente and au.idTipoAusencia = 2 and au.dtFimAusencia >= GETDATE()"
                , array(
            'au.dtFimAusencia',
            'au.dtInicioAusencia',
                )
                , 'AGENTES.dbo'
        );
        $select->joinInner(
                array("usu" => "Usuarios")
                , "ag.CNPJCPF = usu.usu_identificacao"
                , array()
                , 'TABELAS.dbo'
        );
        $select->joinInner(
                array("uog" => "UsuariosXOrgaosXGrupos")
                , "usu.usu_codigo = uog.uog_usuario and uog.uog_status = 1"
                , array()
                , 'TABELAS.dbo'
        );
        $select->joinInner(
                array("org" => "Orgaos")
                , "org.Codigo = uog.uog_orgao"
                , array()
                , 'SAC.dbo'
        );

        $select->where('uog.uog_grupo = 94');
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        return $this->fetchRow($select);
    }

    public function buscarAgentesCpfVinculo($where = array()) {
        $sl = $this->select();
//        $sl->distinct();
        $sl->setIntegrityCheck(false);
        $sl->from(
                array('ag' => $this->_name), array('ag.CNPJCPF', 'ag.idAgente')
        );
        $sl->joinLeft(
                array('v' => 'tbVinculo'), "v.idAgenteProponente = ag.idAgente ", array('v.siVinculo')
        );
        $sl->joinInner(
                array('nm' => 'nomes'), "nm.idAgente = ag.idAgente", array('nm.Descricao')
        );

        foreach ($where as $key => $valor) {
            $sl->where($key, $valor);
        }
//        xd($sl->assemble());
        return $this->fetchAll($sl);
    }

    public function buscarDirigentes($where=array(), $order=array(), $tamanho=-1, $inicio=-1)
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name),
                     array('CNPJCPFDirigente'=>'a.CNPJCPF','idAgente')
                    ,'AGENTES.dbo'
                    );

        $slct->joinInner(array('v' => 'Vinculacao'),
                         'a.idAgente = v.idAgente',
                         array()
                        ,'AGENTES.dbo'
                        );

        $slct->joinInner(array('n' => 'Nomes'),
                         'a.idAgente = n.idAgente',
                         array('NomeDirigente'=>'n.Descricao')
                        ,'AGENTES.dbo'
                        );

        foreach ($where as $coluna=>$valor)
        {
            $slct->where($coluna, $valor);
        }
        
        //adicionando linha order ao select
        $slct->order($order);
//        xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    public function buscarUfMunicioAgente($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $retornaSelect = false)
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name),
                    array('a.CNPJCPF',
                          'a.idAgente'),
                    'AGENTES.dbo'
                    );
         $slct->joinInner(array('e' => 'EnderecoNacional'),
                                'a.idAgente = e.idAgente',
                          array(),
                          'AGENTES.dbo'
                         );
         $slct->joinInner(array('mun' => 'Municipios'),
                                'mun.idMunicipioIBGE = e.Cidade',
                          array('mun.idMunicipioIBGE',
                                'mun.idUFIBGE',
                                'mun.Descricao as DescricaoMunicipio'),
                          'AGENTES.dbo'
                         );
         $slct->joinInner(array('uf' => 'UF'),
                                'uf.idUF = mun.idUFIBGE',
                          array('*'),
                          'AGENTES.dbo'
                         );

        foreach ($where as $coluna=>$valor)
        {
            $slct->where($coluna, $valor);
        }
        
        if($retornaSelect)
            return $slct;
        else
            return $this->fetchAll($slct);
    }

    /*===========================================================================*/
    /*====================== ABAIXO - METODOS DA CNIC ===========================*/
    /*===========================================================================*/
    
    public function buscarAgenteVinculo($where=array(), $order=array(), $tamanho=-1, $inicio=-1)
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name));
        $slct->joinInner(array('v' => 'TbVinculo'),
                         'a.idAgente=v.idUsuarioResponsavel');
        $slct->joinInner(array('n' => 'Nomes'),
                         'a.idAgente=n.idAgente');

        foreach ($where as $coluna=>$valor)
        {
            $slct->where($coluna, $valor);
        }
        //xd($slct->query());
        return $this->fetchAll($slct);
    }

    public function gerenciarResponsaveisListas($siVinculo, $idUsuario)
    {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
                array('a' => $this->_name),
                array('CNPJCPF as CNPJCPFProponente')
        );
        $a->joinInner(
                array('b' => 'tbVinculo'), "a.idAgente = b.idAgenteProponente",
                array('idVinculo', 'siVinculo', 'idUsuarioResponsavel'), 'AGENTES.dbo'
        );
        $a->joinInner(
                array('c' => 'SGCacesso'), "c.IdUsuario = b.idUsuarioResponsavel",
                array('IdUsuario', 'Cpf as CPFResponsavel', 'Nome AS NomeResponsavel'), 'CONTROLEDEACESSO.dbo'
        );
        $a->joinInner(
                array('d' => 'Nomes'), "b.idAgenteProponente = d.idAgente",
                array('Descricao as Proponente'), 'AGENTES.dbo'
        );
        $a->where('c.IdUsuario = ?', $idUsuario);
        $a->where('b.siVinculo = ?', $siVinculo);
        $a->where(new Zend_Db_Expr('a.CNPJCPF <> c.Cpf'));
        //************************************************//




        $b = $this->select();
        $b->setIntegrityCheck(false);
        $b->from(
                array('a' => $this->_name),
                array('CNPJCPF as CNPJCPFProponente')
        );
        $b->joinInner(
                array('b' => 'tbVinculo'), "a.idAgente = b.idAgenteProponente",
                array('idVinculo', 'siVinculo', 'idUsuarioResponsavel'), 'AGENTES.dbo'
        );
        $b->joinInner(
                array('c' => 'SGCacesso'), "a.CNPJCPF = c.Cpf",
                array('IdUsuario'), 'CONTROLEDEACESSO.dbo'
        );
        $b->joinInner(
                array('e' => 'SGCacesso'), "e.IdUsuario = b.idUsuarioResponsavel",
                array('Cpf AS CPFResponsavel', 'Nome AS NomeResponsavel'), 'CONTROLEDEACESSO.dbo'
        );
        $b->joinInner(
                array('d' => 'Nomes'), "b.idAgenteProponente = d.idAgente",
                array('Descricao as Proponente'), 'AGENTES.dbo'
        );
        $b->where('c.IdUsuario = ?', $idUsuario);
        $b->where('b.siVinculo = ?', $siVinculo);
        $b->where(new Zend_Db_Expr('a.CNPJCPF = c.Cpf'));
        //************************************************//



        $c = $this->select();
        $c->setIntegrityCheck(false);
        $c->from(
                array('a' => $this->_name),
                array('CNPJCPF as CNPJCPFProponente')
        );
        $c->joinInner(
                array('b' => 'tbVinculo'), "a.idAgente = b.idAgenteProponente",
                array('idVinculo', 'siVinculo', 'idUsuarioResponsavel'), 'AGENTES.dbo'
        );
        $c->joinInner(
                array('c' => 'Vinculacao'), "b.idAgenteProponente = c.idVinculoPrincipal",
                array(), 'AGENTES.dbo'
        );
        $c->joinInner(
                array('d' => 'Visao'), "c.idAgente = d.idAgente",
                array(), 'AGENTES.dbo'
        );
        $c->joinInner(
                array('e' => 'Agentes'), "d.idAgente = e.idAgente",
                array(), 'AGENTES.dbo'
        );
        $c->joinInner(
                array('f' => 'SGCacesso'), "e.CNPJCPF = f.Cpf",
                array('IdUsuario'), 'CONTROLEDEACESSO.dbo'
        );
        $c->joinInner(
                array('g' => 'SGCacesso'), "g.IdUsuario = b.idUsuarioResponsavel",
                array('Cpf as CPFResponsavel', 'Nome as NomeResponsavel'), 'CONTROLEDEACESSO.dbo'
        );
        $c->joinInner(
                array('h' => 'Nomes'), "c.idVinculoPrincipal = h.idAgente",
                array('Descricao as Proponente'), 'AGENTES.dbo'
        );
        $c->where('d.Visao = ?', 198);
        $c->where('f.IdUsuario = ?', $idUsuario);
        $c->where('b.siVinculo = ?', $siVinculo);
        $c->where(new Zend_Db_Expr('a.CNPJCPF <> f.Cpf'));
        //************************************************//


        $slctUnion = $this->select()
                            ->union(array('('.$a.')', '('.$b.')', '('.$c.')'))
                            ->order('Nome');

//        xd($slctUnion->assemble());
        return $this->fetchAll($slctUnion);
    }


    public function listarVincularPropostaCombo($idResponsavel)
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name),
                     array('CNPJCPF AS CNPJCPFProponente'),'AGENTES.dbo'
                    );
        $slct->joinInner(array('b' => 'tbVinculo'),
                         'a.idAgente = b.idAgenteProponente',
                         array('idVinculo', 'siVinculo', 'idUsuarioResponsavel'),'AGENTES.dbo'
                        );
        $slct->joinInner(array('c' => 'SGCacesso'),
                         'a.CNPJCPF = c.Cpf',
                         array('IdUsuario'),'CONTROLEDEACESSO.dbo'
                        );
        $slct->joinInner(array('d' => 'Nomes'),
                         'b.idAgenteProponente = d.idAgente',
                         array('Descricao AS Proponente'),'AGENTES.dbo'
                        );
        $slct->joinInner(array('e' => 'SGCacesso'),
                         'e.IdUsuario = b.idUsuarioResponsavel',
                         array('Cpf AS CPFResponsavel', 'Nome AS NomeResponsavel'),'CONTROLEDEACESSO.dbo'
                        );

        $slct->where('c.IdUsuario = ?', $idResponsavel);
        $slct->where('b.siVinculo = ?', 2);
        $slct->where(new Zend_Db_Expr('a.CNPJCPF = c.Cpf'));
//        xd($slct->assemble());
        return $this->fetchAll($slct);
    }

//    public function gerenciarResponsaveisListas($siVinculo, $idUsuario)
//    {
//        $b = $this->select();
//        $b->setIntegrityCheck(false);
//        $b->from(
//                array('a' => $this->_name),
//                array('CNPJCPF as CNPJCPFProponente')
//        );
//        $b->joinInner(
//                array('b' => 'tbVinculo'), "a.idAgente = b.idAgenteProponente",
//                array('idVinculo', 'siVinculo', 'idUsuarioResponsavel'), 'AGENTES.dbo'
//        );
//        $b->joinInner(
//                array('c' => 'Vinculacao'), "b.idAgenteProponente = c.idVinculoPrincipal",
//                array(), 'AGENTES.dbo'
//        );
//        $b->joinInner(
//                array('d' => 'Visao'), "c.idAgente = d.idAgente",
//                array(), 'AGENTES.dbo'
//        );
//        $b->joinInner(
//                array('e' => 'Agentes'), "d.idAgente = e.idAgente",
//                array(), 'AGENTES.dbo'
//        );
//        $b->joinInner(
//                array('f' => 'SGCacesso'), "e.CNPJCPF = f.Cpf",
//                array('IdUsuario'), 'CONTROLEDEACESSO.dbo'
//        );
//        $b->joinInner(
//                array('g' => 'SGCacesso'), "g.IdUsuario = b.idUsuarioResponsavel",
//                array('Cpf as CPFResponsavel', 'Nome as NomeResponsavel'), 'CONTROLEDEACESSO.dbo'
//        );
//        $b->joinInner(
//                array('h' => 'Nomes'), "c.idVinculoPrincipal = h.idAgente",
//                array('Descricao as Proponente'), 'AGENTES.dbo'
//        );
//        $b->where('d.Visao = ?', 198);
//        $b->where('f.IdUsuario = ?', $idUsuario);
//        $b->where('b.siVinculo = ?', $siVinculo);
//        $b->where(new Zend_Db_Expr('a.CNPJCPF <> f.Cpf'));
//
//
//        $slctUnion = $this->select()
//                            ->union(array('('.$a.')', '('.$b.')'))
//                            ->order('Nome');

//        xd($b->assemble());
//        return $this->fetchAll($b);
//    }

}

?>
