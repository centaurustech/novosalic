<?php 

class PreProjeto extends Zend_Db_Table
{
    //protected $_schema = "SAC";
    protected $_name = "PreProjeto";
    protected $_primary = "idPreProjeto";
    public function __construct() {
        $db = new Conexao(Zend_Registry::get('DIR_CONFIG'), "conexao_sac");
        parent::__construct();
    }

    
    public static function retirarProjetos($idUsuario, $idUsuarioR, $idAgente)
    {

        $sql = "UPDATE SAC.dbo.PreProjeto SET idUsuario = ".$idUsuario." WHERE idUsuario = ".$idUsuarioR." and idAgente = ".$idAgente;

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->query($sql);

    }

    public static function retirarProjetosVinculos($siVinculoProposta, $idVinculo)
    {

        $sql = "UPDATE Agentes.dbo.tbVinculoProposta SET siVinculoProposta = $siVinculoProposta WHERE idVinculo = $idVinculo";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->query($sql);

    }
    
    
    public static function listaProjetos($idUsuario) {

        $sql = "SELECT p.idPreProjeto,idagente,NomeProjeto,Mecanismo,stTipoDemanda
                FROM SAC.dbo.PreProjeto p
                WHERE stEstado = 1
                AND stTipoDemanda like 'NA'
                AND idUsuario =  $idUsuario
                AND not exists (SELECT * FROM SAC.dbo.projetos pr WHERE pr.idProjeto = p.idPreProjeto) ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    /**
     * Retorna registros do banco de dados referente a Agentes(Proponente)
     * @param aray $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscar($where=array(), $order=array(), $tamanho=-1, $inicio=-1) 
    {
        
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from($this, array("*",
                                  "DtInicioDeExecucaoForm"=>"CONVERT(CHAR(10),DtInicioDeExecucao,103)",
                                  "DtFinalDeExecucaoForm"=>"CONVERT(CHAR(10),DtFinalDeExecucao,103)",
                                  "DtAtoTombamentoForm"=>"CONVERT(CHAR(10),DtAtoTombamento,103)",
                                  "dtAceiteForm"=>"CONVERT(CHAR(10),dtAceite,103)",
                                  "DtArquivamentoForm"=>"CONVERT(CHAR(10),DtArquivamento,103)",
                                  "CAST(ResumoDoProjeto as TEXT) as ResumoDoProjeto",
                                  "CAST(Objetivos as TEXT) as Objetivos",
                                  "CAST(Justificativa as TEXT) as Justificativa",
                                  "CAST(Acessibilidade as TEXT) as Acessibilidade",
                                  "CAST(DemocratizacaoDeAcesso as TEXT) as DemocratizacaoDeAcesso",
                                  "CAST(EtapaDeTrabalho as TEXT) as EtapaDeTrabalho",
                                  "CAST(FichaTecnica as TEXT) as FichaTecnica",
                                  "CAST(Sinopse as TEXT) as Sinopse",
                                  "CAST(ImpactoAmbiental as TEXT) as ImpactoAmbiental",
                                  "CAST(EspecificacaoTecnica as TEXT) as EspecificacaoTecnica",
                                  "CAST(EstrategiadeExecucao as TEXT) as EstrategiadeExecucao"
                                ));

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna=>$valor)
        {
            $slct->where($coluna, $valor);
        }
        
        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1)
        {
                $tmpInicio = 0;
                if ($inicio > -1)
                {
                        $tmpInicio = $inicio;
                }
                $slct->limit($tamanho, $tmpInicio);
        }
        //xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    /**
     * Retorna registros do banco de dados referente a Agentes(Proponente)
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscaCompleta($where=array(), $order=array(), $tamanho=-1, $inicio=-1)
    {

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('a' => $this->_name),
                            array("a.*",
                                  "a.ResumoDoProjeto"=>"CAST(a.ResumoDoProjeto AS TEXT) as ResumoDoProjeto",
                                  "a.Objetivos"=>"CAST(a.Objetivos AS TEXT) as Objetivos",
                                  "a.Justificativa"=>"CAST(a.Justificativa AS TEXT) as Justificativa",
                                  "a.Acessibilidade"=>"CAST(a.Acessibilidade AS TEXT) as Acessibilidade",
                                  "a.DemocratizacaoDeAcesso"=>"CAST(a.DemocratizacaoDeAcesso AS TEXT) as DemocratizacaoDeAcesso",
                                  "a.EtapaDeTrabalho"=>"CAST(a.EtapaDeTrabalho AS TEXT) as EtapaDeTrabalho",
                                  "a.FichaTecnica"=>"CAST(a.FichaTecnica AS TEXT) as FichaTecnica",
                                  "a.Sinopse"=>"CAST(a.Sinopse AS TEXT) as Sinopse",
                                  "a.ImpactoAmbiental"=>"CAST(a.ImpactoAmbiental AS TEXT) as ImpactoAmbiental",
                                  "a.EspecificacaoTecnica"=>"CAST(a.EspecificacaoTecnica AS TEXT) as EspecificacaoTecnica",
                                  "a.EstrategiadeExecucao"=>"CAST(a.EstrategiadeExecucao AS TEXT) as EstrategiadeExecucao",
                                  "a.DtInicioDeExecucaoForm"=>"CONVERT(CHAR(10),DtInicioDeExecucao,103)",
                                  "a.DtFinalDeExecucaoForm"=>"CONVERT(CHAR(10),DtFinalDeExecucao,103)",
                                  "a.DtAtoTombamentoForm"=>"CONVERT(CHAR(10),DtAtoTombamento,103)",
                                  "a.dtAceiteForm"=>"CONVERT(CHAR(10),dtAceite,103)",
                                  "a.DtArquivamentoForm"=>"CONVERT(CHAR(10),DtArquivamento,103)"
                                ));

        $slct->joinInner(array('ag' => 'Agentes'),
                         'a.idAgente = ag.idAgente',
                         array("ag.CNPJCPF as CNPJCPF"),
                         'AGENTES.dbo');

        $slct->joinInner(array('m' => 'Nomes'),
                         'a.idAgente = m.idAgente',
                         array("m.Descricao as NomeAgente"),
                         'AGENTES.dbo');
                         
                         //xd($slct);

      /*  $slct->joinLeft(array('mc' => 'Mecanismo'),
                         'a.Mecanismo = mc.Codigo',
                         array("mc.Descricao as Mecanismo"));
        */
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna=>$valor)
        {
            $slct->where($coluna, $valor);
        }
        $slct->where(new Zend_Db_Expr("NOT EXISTS(select 1 from SAC.dbo.Projetos pr where a.idPreProjeto = pr.idProjeto)"));
        
        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1)
        {
                $tmpInicio = 0;
                if ($inicio > -1)
                {
                        $tmpInicio = $inicio;
                }
                $slct->limit($tamanho, $tmpInicio);
        }
//        xd($slct->assemble());
        //xd($this->fetchAll($slct));
        return $this->fetchAll($slct);
    }

    /**
     * Grava registro. Se seja passado um ID ele altera um registro existente
     * @param array $dados - array com dados referentes as colunas da tabela no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @return ID do registro inserido/alterado ou FALSE em caso de erro
     */
    public function salvar($dados)
    {
        
        //DECIDINDO SE INCLUI OU ALTERA UM REGISTRO
        if(isset($dados['idPreProjeto']) && !empty ($dados['idPreProjeto'])){
            //UPDATE
            $rsPreProjeto = $this->find($dados['idPreProjeto'])->current();
        }else{
            //INSERT
            $dados['idPreProjeto'] = null;
            return $this->insert($dados);
            //$rsPreProjeto = $this->createRow();
        }

        //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS
       $rsPreProjeto->idAgente              = $dados["idAgente"];
       $rsPreProjeto->NomeProjeto           = $dados["NomeProjeto"];
       $rsPreProjeto->Mecanismo             = $dados["Mecanismo"];
       $rsPreProjeto->AgenciaBancaria       = $dados["AgenciaBancaria"];
       $rsPreProjeto->AreaAbrangencia       = $dados["AreaAbrangencia"];
       $rsPreProjeto->DtInicioDeExecucao    = $dados["DtInicioDeExecucao"];
       $rsPreProjeto->DtFinalDeExecucao     = $dados["DtFinalDeExecucao"];
       $rsPreProjeto->NrAtoTombamento       = $dados["NrAtoTombamento"];
       $rsPreProjeto->DtAtoTombamento       = $dados["DtAtoTombamento"];
       $rsPreProjeto->EsferaTombamento      = $dados["EsferaTombamento"];
       $rsPreProjeto->ResumoDoProjeto       = $dados["ResumoDoProjeto"];
       $rsPreProjeto->Objetivos             = $dados["Objetivos"];
       $rsPreProjeto->Justificativa         = $dados["Justificativa"];
       $rsPreProjeto->Acessibilidade        = $dados["Acessibilidade"];
       $rsPreProjeto->DemocratizacaoDeAcesso = $dados["DemocratizacaoDeAcesso"];
       $rsPreProjeto->EtapaDeTrabalho       = $dados["EtapaDeTrabalho"];
       $rsPreProjeto->FichaTecnica          = $dados["FichaTecnica"];
       $rsPreProjeto->Sinopse               = $dados["Sinopse"];
       $rsPreProjeto->ImpactoAmbiental      = $dados["ImpactoAmbiental"];
       $rsPreProjeto->EspecificacaoTecnica  = $dados["EspecificacaoTecnica"];
       $rsPreProjeto->EstrategiadeExecucao  = $dados["EstrategiadeExecucao"];
       $rsPreProjeto->dtAceite              = $dados["dtAceite"];
       $rsPreProjeto->DtArquivamento        = (isset($dados["DtArquivamento"])) ? $dados["DtArquivamento"] : null;
       $rsPreProjeto->stEstado              = $dados["stEstado"];
       $rsPreProjeto->stDataFixa            = $dados["stDataFixa"];
       $rsPreProjeto->stPlanoAnual          = $dados["stPlanoAnual"];
       $rsPreProjeto->idUsuario             = $dados["idUsuario"];
       $rsPreProjeto->stTipoDemanda         = $dados["stTipoDemanda"];
       $rsPreProjeto->idEdital              = (isset($dados["idEdital"])) ? $dados["idEdital"] : null;

       //SALVANDO O OBJETO
       $id = $rsPreProjeto->save();

       if($id){
            return $id;
       }else{
            return false;
       }
    }

    public static function consultaTodosProjetos($idAgente, $idResponsavel, $arrBusca) {

        $sql = "SELECT 0 as Ordem,a.idPreProjeto,a.NomeProjeto,ag.cnpjcpf AS CNPJCPF,m.descricao AS NomeAgente,a.idUsuario,a.idAgente
                FROM   preprojeto AS a
                       INNER JOIN AGENTES.dbo.agentes ag on (a.idagente = ag.idagente)
                       INNER JOIN AGENTES.dbo.nomes    m on (a.idagente = m.idagente)
                WHERE  a.idAgente = $idAgente ";

        foreach ($arrBusca as $value) {
            $sql .= ' AND '.$value.' ';
        }

        $sql .= " AND NOT EXISTS(SELECT 1 FROM   sac.dbo.projetos pr WHERE  a.idpreprojeto = pr.idprojeto)
                UNION ALL
                SELECT 1 as Ordem,a.idPreProjeto,a.NomeProjeto,ag.cnpjcpf AS CNPJCPF,m.descricao AS NomeAgente,a.idUsuario,a.idAgente
                FROM   preprojeto AS a
                       INNER JOIN AGENTES.dbo.agentes            ag on (a.idagente = ag.idagente)
                       INNER JOIN AGENTES.dbo.nomes              m   on (a.idagente = m.idagente)
                       INNER JOIN ControleDeAcesso.dbo.SGCacesso s  on (a.idUsuario = s.IdUsuario)
                WHERE  a.idusuario = $idResponsavel and ag.CNPJCPF <> s.Cpf
                       AND ( NOT EXISTS(SELECT 1 FROM   sac.dbo.projetos pr WHERE  a.idpreprojeto = pr.idprojeto) )";

        foreach ($arrBusca as $value) {
            $sql .= ' AND '.$value.' ';
        }

        $sql .= "ORDER  BY 1,m.Descricao ASC";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }


    public static function consultaprojetos($idagente) {

        $sql = "SELECT idPreProjeto, idagente, NomeProjeto, Mecanismo
                FROM SAC.dbo.PreProjeto
                WHERE idagente = '$idagente'
                ORDER BY nomeprojeto";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function inserirProposta($dados) {

//        $sql = "INSERT into SAC.dbo.PreProjeto
//                VALUES ('$idagente','".$NomeProjeto."','1','$AgenciaBancaria','0','".$DtInicioDeExecucao."','".$DtFinalDeExecucao."','".$Justificativa."','".$NrAtoTombamento."','".$DtAtoTombamento."','$EsferaTombamento','".$ResumoDoProjeto."','".$Objetivos."','".$Acessibilidade."','".$DemocratizacaoDeAcesso."','".$EtapaDeTrabalho."','".$FichaTecnica."','".$Sinopse."','".$ImpactoAmbiental."','".$EspecificacaoTecnica."','',GETDATE(),'','0','$stDataFixa','$stPlanoAnual','777','NA','')";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $cadastrar = $db->insert("SAC.dbo.PreProjeto", $dados);

        if ($cadastrar)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function alterarDados($dados, $where) {

//        $sql = "INSERT into SAC.dbo.PreProjeto
//                VALUES ('$idagente','".$NomeProjeto."','1','$AgenciaBancaria','0','".$DtInicioDeExecucao."','".$DtFinalDeExecucao."','".$Justificativa."','".$NrAtoTombamento."','".$DtAtoTombamento."','$EsferaTombamento','".$ResumoDoProjeto."','".$Objetivos."','".$Acessibilidade."','".$DemocratizacaoDeAcesso."','".$EtapaDeTrabalho."','".$FichaTecnica."','".$Sinopse."','".$ImpactoAmbiental."','".$EspecificacaoTecnica."','',GETDATE(),'','0','$stDataFixa','$stPlanoAnual','777','NA','')";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $cadastrar = $db->update("SAC.dbo.PreProjeto", $dados, $where);

        if ($cadastrar)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    public static function listaUF() {

        $sql = "SELECT * FROM AGENTES.dbo.UF ORDER BY Sigla";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);

    }

    public static function buscaIdAgente($CNPJCPF) {
        $sql = "select * from Agentes.dbo.Agentes where CNPJCPF ='$CNPJCPF' ";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

    public static function inserirAgentes($dadosAgentes) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $Agentes = $db->insert("Agentes.dbo.Agentes", $dadosAgentes);
    }

    public static function inserirNomes($dadosNomes) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $Nomes = $db->insert("Agentes.dbo.Nomes", $dadosNomes);
    }

    public static function inserirEnderecoNacional($dadosEnderecoNacional) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $Nomes = $db->insert("Agentes.dbo.EnderecoNacional", $dadosEnderecoNacional);
    }

    public static function inserirVisao($dadosVisao) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $Nomes = $db->insert("Agentes.dbo.Visao", $dadosVisao);
    }

    public static function editarproposta($idPreProjeto) {

        $sql = "SELECT * FROM SAC.dbo.PreProjeto WHERE idPreProjeto = $idPreProjeto ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);

    }

    public function recuperarTecnicosOrgao($idOrgaoSuperior) {

        $sql = " SELECT usu_codigo,uog_orgao FROM tabelas.dbo.vwUsuariosOrgaosGrupos
                  WHERE sis_codigo=21 and gru_codigo=92 and uog_orgao = {$idOrgaoSuperior} and uog_status = 1";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);

    }

    function listarDiligenciasPreProjeto($consulta = array(),$retornaSelect = false){//AQUI

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('pre'=>$this->_name),
                        array(
                                'nomeProjeto'=>'pre.NomeProjeto',
                        
                                'pronac'=>'pre.idPreProjeto'
                             )
                     );
                     
                     
                     
                     
                     
        $select->joinInner(
                array('aval' => 'tbAvaliacaoProposta'),
                'aval.idProjeto = pre.idPreProjeto',
                array(
                        'aval.stProrrogacao',
                        'idDiligencia'=>'aval.idAvaliacaoProposta',
                        'dataSolicitacao'=>'CONVERT(VARCHAR,aval.DtAvaliacao,120)',
                        'dataResposta'=>'CONVERT(VARCHAR,aval.dtResposta,120)',
                        'Solicitacao'=>'aval.Avaliacao',
                        'Resposta'=>'aval.dsResposta',
                        'aval.idCodigoDocumentosExigidos',
                        'aval.stEnviado'
                    )
        );
        
       
        	
        $select->joinLeft(
                array('arq' => 'tbArquivo'),
                'arq.idArquivo = aval.idArquivo',
                array(
                        'arq.nmArquivo',
                        'arq.idArquivo'
                    ),
                'BDCORPORATIVO.scCorp'
        );
        
        $select->joinLeft(
                array('a' => 'AGENTES'),
                'pre.idAgente = a.idAgente',
                array(
                        'a.idAgente'
                    ),
                'AGENTES.dbo'
        );
        $select->joinLeft(
                array('n' => 'NOMES'),
                'a.idAgente = n.idAgente',
                array(
                        'n.Descricao'
                    ),
                'AGENTES.dbo'
        );
 
        //$select->where('aval.stEstado = ?', 0);
        foreach ($consulta as $coluna=>$valor)
        {
            $select->where($coluna, $valor);
        }

        if($retornaSelect)
        {
        	
            return $select;
        }
        else
        {
        	//xd($select->assemble());
        	return $this->fetchAll($select);
        }
            
    }

    function dadosPreProjeto($consulta = array()){

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('pre'=>$this->_name),
                        array(
                                'nomeProjeto'=>'pre.NomeProjeto',
                                'pronac'=>'pre.idPreProjeto'
                             )
                     );

        foreach ($consulta as $coluna=>$valor)
        {
            $select->where($coluna, $valor);
        }

        return $this->fetchAll($select);

    }

    function buscarAgentePreProjeto($consulta = array()){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('pre'=>$this->_name),
                        array(
                                'pre.idAgente'
                             )
                     );

        foreach ($consulta as $coluna=>$valor)
        {
            $select->where($coluna, $valor);
        }

        return $this->fetchAll($select);
    }




        public function listaAvaliadores($where=array()) {

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('pp' => $this->_name),
                array('pp.idPreProjeto')
        );
        $slct->joinInner(
                array('ave' => 'tbAvaliadorEdital'),
                'ave.idEdital = pp.idEdital',
                array('ave.idAvaliador'),
                'BDCORPORATIVO.scSAC'
        );
        $slct->joinInner(array('nom' => 'Nomes'),
                'nom.idAgente = ave.idAvaliador',
                array('nom.Descricao'),
                'AGENTES.dbo'
        );
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
        //xd($slct->assemble());
        //xd($this->fetchAll($slct));
        return $this->fetchAll($slct);

    }



    public function listaApenasAvaliadores($where=array()) {

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('pp' => $this->_name),
                array('')
        );
        $slct->joinInner(
                array('ave' => 'tbAvaliadorEdital'),
                'ave.idEdital = pp.idEdital',
                array(new Zend_Db_Expr('distinct(ave.idAvaliador)')),
                'BDCORPORATIVO.scSAC'
        );
        $slct->joinInner(array('nom' => 'Nomes'),
                'nom.idAgente = ave.idAvaliador',
                array('nom.Descricao'),
                'AGENTES.dbo'
        );
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
        //xd($slct->assemble());
        //xd($this->fetchAll($slct));
        return $this->fetchAll($slct);

    }

    public function buscarPropostaEditalCompleto($where=array())
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('p' => $this->_name),
                array('p.idPreProjeto,
                       p.idAgente,
                       p.NomeProjeto,
                       p.Mecanismo,
                       p.AgenciaBancaria,
                       p.DtInicioDeExecucao,
                       p.DtFinalDeExecucao,
                       p.stTipoDemanda,
                       p.idEdital,
                       CAST(p.ResumoDoProjeto as TEXT) as ResumoDoProjeto')
                        
        );
        $slct->joinLeft(
                array('fd' => 'tbFormDocumento'),
                'fd.idEdital = p.idEdital OR p.idEdital IS NOT NULL',
                array('fd.nrFormDocumento', 'fd.nrVersaoDocumento'),
                'BDCORPORATIVO.scQuiz'
        );
        
        $slct->joinInner(array('nm' => 'Nomes'),
                'nm.idAgente = p.idAgente',
                array('nm.Descricao as nomeAgente'),
                'AGENTES.dbo'
        );
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
        //xd($slct->assemble());
        //xd($this->fetchAll($slct));
        return $this->fetchAll($slct);

    }

    public function dadosProjetoDiligencia($idProjeto){

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                    array('p' => $this->_name),
                    array(
                        'idProjeto'=>'p.idPreProjeto',
                        'p.NomeProjeto'
                        )
            );
        $slct->joinInner(
                array("a"=>"Agentes"),
                "a.idAgente = p.idAgente",
                array(),
                'agentes.dbo'
                );
        $slct->joinInner(
                array("n"=>"Nomes"),
                "a.idAgente = n.idAgente",
                array('Destinatario'=>'Descricao'),
                'AGENTES.dbo'
                );
        $slct->joinInner(
                array("int"=>"Internet"),
                "a.idAgente = int.idAgente",
                array('Email'=>'Descricao'),
                'AGENTES.dbo'
                );

        $slct->where('p.idPreProjeto = ?',$idProjeto);
        $slct->where('a.Status = 0'); // Status do registro da pessoa, 0 - para ativo e 1 - para inativo

        return $this->fetchAll($slct);
    }

    public static function analiseDeCustos($idPreProjeto)
    {
        $sql = "
            SELECT a.idPreProjeto,
            a.NomeProjeto,
            z.idProduto,
            --PAP.dsJustificativa dsJustificativaConselheiro,
            --(PAP.qtItem * PAP.nrOcorrencia * PAP.vlUnitario) AS VlSugeridoConselheiro,
            CASE WHEN z.idProduto = 0 THEN 'Administração do Projeto' ELSE c.Descricao END AS Produto,
            CONVERT(varchar(8),d.idPlanilhaEtapa) + ' - ' + d.Descricao AS Etapa,
            d.idPlanilhaEtapa,
            i.Descricao AS Item,
            z.Quantidade * z.Ocorrencia * z.ValorUnitario AS VlSolicitado,
            e.Descricao AS Unidade,
            z.Quantidade,
            z.Ocorrencia,
            z.ValorUnitario,
            z.QtdeDias,
            z.TipoDespesa,
            z.TipoPessoa,
            z.Contrapartida,
            z.dsJustificativa as JustificativaProponente,
            z.FonteRecurso AS idFonte,
            x.Descricao AS FonteRecurso,
            f.UF,
            f.idUF,
            f.Municipio,
            f.idMunicipio,
            ROUND(z.Quantidade * z.Ocorrencia * z.ValorUnitario, 2) AS Sugerido,
            --CAST(z.Justificativa AS TEXT) AS Justificativa,
            z.idUsuario
            FROM SAC.dbo.PreProjeto AS a
            --INNER JOIN SAC.dbo.tbPlanilhaProjeto AS b ON a.IdPRONAC = b.idPRONAC
            INNER JOIN SAC.dbo.tbPlanilhaProposta AS z ON z.idProjeto = a.idPreProjeto
            --left JOIN SAC.dbo.tbPlanilhaAprovacao PAP on (PAP.idPlanilhaProposta = z.idPlanilhaProposta)
            LEFT OUTER JOIN SAC.dbo.Produto AS c ON c.Codigo = z.idProduto
            INNER JOIN SAC.dbo.tbPlanilhaEtapa AS d ON d.idPlanilhaEtapa = z.idEtapa
            INNER JOIN SAC.dbo.tbPlanilhaUnidade AS e ON e.idUnidade = z.Unidade
            INNER JOIN SAC.dbo.tbPlanilhaItens AS i ON i.idPlanilhaItens = z.idPlanilhaItem
            INNER JOIN SAC.dbo.Verificacao AS x ON x.idVerificacao = z.FonteRecurso
            INNER JOIN AGENTES.dbo.vUFMunicipio AS f ON f.idUF = z.UfDespesa AND f.idMunicipio = z.MunicipioDespesa
            WHERE a.idPreProjeto = {$idPreProjeto}
            ORDER BY x.Descricao, Produto, Etapa, UF, Item";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

    public function tecnicoTemProposta($idTecnico){

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                    array("ap" => "tbAvaliacaoProposta"),
                    array("*"),
                    "SAC.dbo"
            );
        $slct->joinInner(
                array("m"=>"tbMovimentacao"),
                "ap.idProjeto = m.idProjeto",
                array(),
                "SAC.dbo"
                );

        $condicao = new Zend_Db_Expr("select top 1 * from SAC..Projetos p where p.idProjeto = ap.idProjeto");
        $slct->where("ap.idTecnico = ?",$idTecnico);
        $slct->where("m.stEstado = 0");
        $slct->where("m.Movimentacao IN (96,97,128)");
        $slct->where("NOT EXISTS({$condicao})");

        $rs = $this->fetchAll($slct)->toArray();
        if(count($rs) > 0){
            return true;
        }

        return false;
    }
    
    
    
 	public static function alteraproponente($idPreProjeto, $idAgente) 
 	{

        $sql = "UPDATE SAC.dbo.PreProjeto SET idAgente = ".$idAgente." WHERE idPreProjeto = $idPreProjeto ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);

    }
    
    
 	public static function alteraresponsavel($idPreProjeto, $idResponsavel) 
 	{

        $sql = "UPDATE SAC.dbo.PreProjeto SET idUsuario = ".$idResponsavel." WHERE idPreProjeto = $idPreProjeto ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);

    }
    
    
    
    /* Busca as propostas/projetos vinculados ao proponente
     * UC 89 Fluxo: FA6
     * 
     */
 	public function buscarPropostaProjetos($where=array())
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('pp' => $this->_name),
                array('pp.idPreProjeto,
                       pp.idAgente,
                       pp.NomeProjeto,
                       pp.Mecanismo,
                       pp.AgenciaBancaria,
                       pp.DtInicioDeExecucao,
                       pp.DtFinalDeExecucao,
                       pp.stTipoDemanda,
                       pp.idUsuario,
                       pp.idEdital,
                       CAST(pp.ResumoDoProjeto as TEXT) as ResumoDoProjeto')
                        
        );
        
        $slct->joinLeft(
                array('resp' => 'SGCacesso'), 'resp.IdUsuario = pp.idUsuario',
                array('resp.Nome','resp.Cpf'),'CONTROLEDEACESSO.dbo'
        );
        
        $slct->joinLeft(
                array('pr' => 'Projetos'), 'pp.idPreProjeto = pr.idProjeto',
                array('pr.idProjeto','(pr.AnoProjeto+pr.Sequencial) as PRONAC')
        );
        
        /*
        $slct->joinLeft(
                array('vi' => 'tbVinculo'), 'pp.idAgente = vi.idAgenteProponente',
                array('vi.siVinculo', 'vi.idVinculo'),'AGENTES.dbo'
        );
        */
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
        
        $slct->order('pp.idPreProjeto');
        $slct->order('pp.NomeProjeto');
        //xd($slct->assemble());
        
        return $this->fetchAll($slct);

    }

    public function buscarPropProjVinculados($idAgenteProponente){
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('pp' => $this->_name),
                array('pp.idPreProjeto',
                       'pp.idAgente',
                       'pp.NomeProjeto',
                       'pp.Mecanismo',
                       'pp.AgenciaBancaria',
                       'pp.DtInicioDeExecucao',
                       'pp.DtFinalDeExecucao',
                       'pp.stTipoDemanda',
                       'pp.idUsuario',
                       'pp.idEdital')
                );

        $slct->joinLeft(
                array('resp' => 'SGCacesso'), 'resp.IdUsuario = pp.idUsuario',
                array('resp.Nome','resp.Cpf'),'CONTROLEDEACESSO.dbo'
                );

        $slct->where('idAgente = ?', $idAgenteProponente);
        $slct->where('stEstado = ?', 1);
        $slct->where('NOT EXISTS(SELECT * FROM Projetos p WHERE p.idProjeto = pp.idPreProjeto)', '');
        $slct->order('pp.idPreProjeto');
        $slct->order('pp.NomeProjeto');

        return $this->fetchAll($slct);

    }

    public function buscarVinculadosProponenteDirigentes($arrayIdAgentes){
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('pp' => $this->_name),
                array('pp.idPreProjeto',
                       'pp.idAgente',
                       'pp.NomeProjeto',
                       'pp.Mecanismo',
                       'pp.AgenciaBancaria',
                       'pp.DtInicioDeExecucao',
                       'pp.DtFinalDeExecucao',
                       'pp.stTipoDemanda',
                       'pp.idUsuario',
                       'pp.idEdital')
                );

        $slct->joinLeft(
                array('resp' => 'SGCacesso'), 'resp.IdUsuario = pp.idUsuario',
                array('resp.Nome','resp.Cpf'),'CONTROLEDEACESSO.dbo'
                );

        $slct->where('idAgente in (?)', $arrayIdAgentes);
        $slct->where('stEstado = ?', 1);
        $slct->where('NOT EXISTS(SELECT * FROM Projetos p WHERE p.idProjeto = pp.idPreProjeto)', '');
        $slct->order('pp.idPreProjeto');
        $slct->order('pp.NomeProjeto');

        return $this->fetchAll($slct);

    }

    public static function gerenciarResponsaveisPendentes($siVinculo, $idAgente = null) {

        $sql = "SELECT distinct k.Cpf, k.IdUsuario as idResponsavel, k.Nome AS NomeResponsavel, v.idVinculo, v.siVinculo, v.idUsuarioResponsavel,k.IdUsuario
                FROM AGENTES.dbo.Agentes  a
                LEFT JOIN AGENTES.dbo.tbVinculo v          on (a.idAgente = v.idAgenteProponente)
                INNER JOIN CONTROLEDEACESSO.dbo.SGCacesso k on (k.IdUsuario = v.idUsuarioResponsavel)
                WHERE (a.idAgente= $idAgente) AND (v.siVinculo = $siVinculo) and a.CNPJCPF <> k.Cpf
                ORDER BY k.Nome  ASC";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
//        xd($sql);

        return $db->fetchAll($sql);
    }

    public static function gerenciarResponsaveisVinculados($siVinculo, $idAgente = null) {

        $sql = "SELECT distinct k.Cpf, k.IdUsuario as idResponsavel, k.Nome AS NomeResponsavel, y.idVinculo, y.siVinculo, y.idUsuarioResponsavel,r.IdUsuario
                FROM SAC.dbo.PreProjeto j
                INNER JOIN AGENTES.dbo.Agentes  a           on (j.idAgente = a.idAgente)
                INNER JOIN AGENTES.dbo.tbVinculoProposta v  on (j.idPreProjeto = v.idPreProjeto)
                INNER JOIN AGENTES.dbo.tbVinculo y          on (v.idVinculo = y.idVinculo)
                INNER JOIN CONTROLEDEACESSO.dbo.SGCacesso k on (k.IdUsuario = y.idUsuarioResponsavel)
                INNER JOIN CONTROLEDEACESSO.dbo.SGCacesso r on (r.Cpf = a.CNPJCPF)
                WHERE (j.idAgente= $idAgente) AND (y.siVinculo = $siVinculo) and a.CNPJCPF <> k.Cpf
                ORDER BY k.Nome  ASC ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function listarPropostasResultado($idAgente, $idResponsavel, $idAgenteCombo) {

        $filtro = '';
        if(!empty($idAgenteCombo)){
            $filtro = " AND b.idAgente = $idAgenteCombo ";
        }
        
        $sql = "
            SELECT b.CNPJCPF, b.idAgente, dbo.Fnnome(b.idAgente) AS NomeProponente, a.idPreProjeto, a.NomeProjeto--,'Proponente - Pessoa Física' as TipoDeAgente
                FROM SAC.dbo.PreProjeto a
                    INNER JOIN AGENTES.dbo.Agentes b ON ( a.idAgente = b.idAgente )
                WHERE  a.idAgente = $idAgente
                    $filtro
                    AND a.stEstado = 1
                    AND NOT EXISTS(SELECT 1 FROM SAC.dbo.Projetos pr WHERE a.idPreProjeto = pr.idProjeto)
                    AND a.Mecanismo = '1'
            UNION ALL
            SELECT b.CNPJCPF, b.idAgente, dbo.Fnnome(b.idAgente) AS NomeProponente, a.idPreProjeto, a.NomeProjeto--,'Dirigente' as TipoDeAgente
                FROM SAC.dbo.PreProjeto a
                    INNER JOIN AGENTES.dbo.Agentes b ON ( a.idAgente = b.idAgente )
                    INNER JOIN AGENTES.dbo.Vinculacao c ON ( b.idAgente = c.idVinculoPrincipal )
                    INNER JOIN AGENTES.dbo.Agentes d ON ( c.idAgente = d.idAgente )
                    INNER JOIN CONTROLEDEACESSO.dbo.SGCacesso e ON ( d.CNPJCPF = e.Cpf )
                WHERE e.IdUsuario = $idResponsavel
                    $filtro
                    AND a.stEstado = 1
                    AND NOT EXISTS(SELECT 1 FROM SAC.dbo.Projetos f WHERE a.idPreProjeto = f.idProjeto)
                    AND a.Mecanismo = '1'
            UNION ALL
            SELECT  b.CNPJCPF, b.idAgente, dbo.Fnnome(b.idAgente) AS NomeProponente, a.idPreProjeto, a.NomeProjeto--,'Responsável' as TipoDeAgente
                FROM SAC.dbo.PreProjeto a
                    INNER JOIN AGENTES.dbo.Agentes b ON ( a.idAgente = b.idAgente )
                    INNER JOIN AGENTES.dbo.Nomes c ON ( b.idAgente = c.idAgente )
                    INNER JOIN CONTROLEDEACESSO.dbo.SGCacesso d ON ( a.idUsuario = d.IdUsuario )
                    INNER JOIN AGENTES.dbo.tbVinculoProposta e ON ( a.idPreProjeto = e.idPreProjeto )
                    INNER JOIN AGENTES.dbo.tbVinculo f ON ( e.idVinculo = f.idVinculo )
                WHERE f.idUsuarioResponsavel = $idResponsavel
                    $filtro
                    AND a.stEstado = 1
                    AND e.siVinculoProposta = 2
                    AND NOT EXISTS(SELECT 1 FROM SAC.dbo.Projetos z WHERE a.idPreProjeto = z.idProjeto)
                    AND a.Mecanismo = '1' ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        //xd($sql);
        return $db->fetchAll($sql);
    }
    
}

?>