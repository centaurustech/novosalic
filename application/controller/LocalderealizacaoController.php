<?php
/**
 * LocalDeRealizacaoController
 * @author Equipe RUP - Politec
 * @since 15/12/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @copyright ? 2010 - Ministerio da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class LocalderealizacaoController extends GenericControllerNew {
    private $idPreProjeto = null;
    private $usuarioLogado = null;

    /**
     * Reescreve o metodo init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {

        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $PermissoesGrupo = array();

        //Da permissao de acesso a todos os grupos do usuario logado afim de atender o UC75
        if(isset($auth->getIdentity()->usu_codigo)){
            //Recupera todos os grupos do Usuario
            $Usuario = new Usuario(); // objeto usuário
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
            foreach ($grupos as $grupo){
                $PermissoesGrupo[] = $grupo->gru_codigo;
            }
        }
                        
        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);

        $this->usuarioLogado = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        parent::init();
     
        //recupera ID do pre projeto (proposta)
        if(!empty ($_REQUEST['idPreProjeto'])) {
            $this->idPreProjeto = $_REQUEST['idPreProjeto'];

            //VERIFICA SE A PROPOSTA ESTA COM O MINC
            $Movimentacao = new Movimentacao();
            $rsStatusAtual = $Movimentacao->buscarStatusAtualProposta($_REQUEST['idPreProjeto']);
            $this->view->movimentacaoAtual = isset($rsStatusAtual->Movimentacao) ? $rsStatusAtual->Movimentacao : '';
        }else {
            //xd($_REQUEST['idPreProjeto']);
            if($_REQUEST['idPreProjeto'] != '0'){
                parent::message("Necessário informar o número da proposta.", "/manterpropostaincentivofiscal/index", "ERROR");
            }
        }
		
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $this->idUsuario = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        
        //*******************************************
        //VALIDA ITENS DO MENU (Documento pendentes)
        //*******************************************
        $get = Zend_Registry::get("get");
        $this->view->documentosPendentes = AnalisarPropostaDAO::buscarDocumentoPendente($get->idPreProjeto);

        if(!empty($this->view->documentosPendentes)) {
            $verificarmenu = 1;
            $this->view->verificarmenu = $verificarmenu;
        }
        else {
            $verificarmenu = 0;
            $this->view->verificarmenu = $verificarmenu;
        }

        //(Enviar Proposta ao MinC , Excluir Proposta)
        $mov = new Movimentacao();
        $movBuscar = $mov->buscar(array('idProjeto = ?' => $get->idPreProjeto), array('idMovimentacao desc'), 1, 0)->current();

        if(isset($movBuscar->Movimentacao) && $movBuscar->Movimentacao != 95) {
            $enviado = 'true';
            $this->view->enviado = $enviado;
        }else {
            $enviado = 'false';
            $this->view->enviado = $enviado;
        }
        //*****************
        //FIM DA VALIDAÇ?O
        //*****************
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
    }

    /**
     * Metodo que monta grid de locais de realizacao
     * @param void
     * @return objeto
     */
    public function indexAction() {
        //RECUPERA OS LOCAIS DE REALIZACAO CADASTRADOS
        $arrBusca = array();
        $arrBusca['idProjeto']=$this->idPreProjeto;
        $arrBusca['stAbrangencia']=1;
        $tblAbrangencia = new Abrangencia();
        $rsAbrangencia = $tblAbrangencia->buscar($arrBusca);

        $arrDados = array("localizacoes"=>$rsAbrangencia,
                "acaoAlterar"=>$this->_urlPadrao."/localderealizacao/form-local-de-realizacao",
                "acaoExcluir"=>$this->_urlPadrao."/localderealizacao/excluir" );
        
        //METODO QUE MONTA TELA DO USUARIO ENVIANDO TODOS OS PARAMENTROS NECESSARIO DENTRO DO ARRAY DADOS
        $this->montaTela("localderealizacao/index.phtml", $arrDados);

    }

    /**
     * Metodo que monta o formulario de cadastro e altera&ccedil;&atilde;o de locais de realizacao
     * @param void
     * @return objeto
     */
    public function formLocalDeRealizacaoAction() {
        //recupera parametros
        $get = Zend_Registry::get('get');
        $idAbrangencia = $get->cod;

        //RECUPERA OS PAISES
        $rsPais = DeslocamentoDAO::buscarPais();

        //RECUPRA OS ESTADOS
        $rsEstados = Estado::buscar();

        //RECUPERA LOCALIZACOES CADASTRADAS
        $tblAbrangencia = new Abrangencia();
        $arrBusca = array();
        $arrBusca['idProjeto']=$this->idPreProjeto;
        $arrBusca['stAbrangencia']=1;
        if(!empty($idAbrangencia)) {
            $arrBusca['idAbrangencia']=$idAbrangencia;
        }
        $rsAbrangencia = $tblAbrangencia->buscar($arrBusca);

        $arrDados = array("paises"=>$rsPais,
                "estados"=>$rsEstados,
                "localizacoes"=>$rsAbrangencia,
                "idAbrangencia"=>$idAbrangencia,
                "acao"=>$this->_urlPadrao."/localderealizacao/salvar" );

        //METODO QUE MONTA TELA DO USUARIO ENVIANDO TODOS OS PARAMENTROS NECESSARIO DENTRO DO ARRAY DADOS
        $this->montaTela("localderealizacao/formlocalderealizacao.phtml", $arrDados);
    }

    /**
     * Metodo responsavel por gravar os locais de realizacao em banco (INSERT e UPDATE)
     * @param void
     * @return objeto
     */
    public function salvarAction() {
        $post = Zend_Registry::get("post");
        $idAbrangencia = $post->cod;
        //xd($idAbrangencia);
        //instancia classe modelo
        $tblAbrangencia = new Abrangencia();

        if(isset($_REQUEST['edital'])) {
            $edital = "&edital=s";
        }else {
            $edital = "";
        }

        $qtdeLocais = $post->qtdeLocais;
        $locais = array();
        $locaisinvalidos = array();
        for ($i=1; $i<=$qtdeLocais; $i++) {

            $pais 	= $post->__get("pais_".$i);
            $uf 	= $post->__get("uf_".$i);
            $municipio 	= $post->__get("cidade_".$i);
            $local_c    = $pais.$uf.$municipio;

            if (!in_array($local_c, $locaisinvalidos) || empty($local_c)) {

                $locais[$i]["idPais"]          = $post->__get("pais_".$i);

                if ($locais[$i]["idPais"] == 31) {
                    $locais[$i]["idUF"]            = $post->__get("uf_".$i);
                    $locais[$i]["idMunicipioIBGE"] = $post->__get("cidade_".$i);
                }
                else {
                    $locais[$i]["idUF"]            = "0";
                    $locais[$i]["idMunicipioIBGE"] = "0";
                }
            }else {
                parent::message("Registro já cadastrado, transação cancelada!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ALERT");
                //xd("registro ja cadastrado");
                //return;
            }
            $locaisinvalidos[$i] = $local_c;

        }

        //$db = Zend_Registry :: get('db');
        //$db->setFetchMode(Zend_DB :: FETCH_OBJ);
        try {
            $global = 0;
            //$db->beginTransaction();

            //incluindo novos registros
            if(empty($idAbrangencia)) {
                //APAGA TODOS OS REGISTROS PARA CADASTRALOS NOVAMENTE
                $tblAbrangencia->excluirPeloProjeto($this->idPreProjeto);
            }
            else {

                foreach($locais as $d) {
                	               	
                    $p =  $d['idPais'];
                    if ($p == 31) {
                        $u =  $d['idUF'];
                        $m =  $d['idMunicipioIBGE'];
                    }
                    else {
                        $u =  0;
                        $m =  0;
                    }

                }

                $resultado = $tblAbrangencia->verificarIgual($p, $u, $m, $this->idPreProjeto);
                
                if(count($resultado)>0){
                    parent::message("Registro já cadastrado, transação cancelada!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ALERT");
                    return;
                }

            }

            //INSERE LOCAIS DE REALIZACAO (tabela SAC.dbo.Abrangencia)
            for ($i=1; $i<=count($locais); $i++) {
                $dados = array( "idProjeto"=>$this->idPreProjeto,
                		"stAbrangencia" => 1,
                        //"Usuario"  =>$this->view->usuario->IdUsuario,
                        "Usuario"  =>$this->usuarioLogado,
                        "idPais"   =>$locais[$i]["idPais"],
                        "idUF"     =>($locais[$i]["idPais"]==31)?$locais[$i]["idUF"]:0,
                        "idMunicipioIBGE"=>($locais[$i]["idPais"]==31)?$locais[$i]["idMunicipioIBGE"]:0);

                $dados['stAbrangencia']=1;
                $dados['idAbrangencia']=$idAbrangencia;
                //x($dados);
                if (!empty($dados["idProjeto"]) && !empty($dados["idPais"])) {
                	               	
                    $retorno = $tblAbrangencia->salvar($dados);
                }
            }
            //$db->commit();
            if($idAbrangencia) {
                parent::message("Alteração realizada com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");
            }
            else {
                parent::message("Cadastro realizado com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");

            }

        }catch(Zend_Exception $ex) {        	
            //$db->rollback();            
            parent::message("N&atilde;o foi poss&iacute;vel realizar a opera&ccedil;&atilde;o! <br>", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ERROR");
        }

    }

    /**
     * Metodo responsavel por apagar um local de realiza&ccedil;&atilde;o gravado
     * @param void
     * @return objeto
     */
    public function excluirAction() {
        $get = Zend_Registry::get("get");
        $idAbrangencia = $get->cod;
       	
        if(isset($_REQUEST['edital'])) {
            $edital = "&edital=s";
        }else {
            $edital = "";
        }

        $tblAbrangencia = new Abrangencia();
        //EXCLUI REGISTRO DA TABELA ABRANGENCIA
        if($tblAbrangencia->excluir($idAbrangencia)) {

            parent::message("Exclusão realizada com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");

        }else {
            parent::message("N&atilde;o foi poss&iacute;vel realizar a opera&ccedil;&atilde;o!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ERROR");
        }
    }

    /**
     * Metodo que retorna lista de locais de realizacao
     * @param void
     * @return objeto
     */
    public function consultarcomponenteAction() {
        //recebe o id via GET
        $get = Zend_Registry::get('get');
        $idProjeto = $get->idPreProjeto;
        $this->_helper->layout->disableLayout(); // desabilita o layout
        if(!empty($idProjeto) || $idProjeto=='0') {
            //RECUPERA OS LOCAIS DE REALIZACAO CADASTRADOS
            $arrBusca = array();
            $arrBusca['idProjeto']=$idProjeto;
            $arrBusca['stAbrangencia']=1;

            $tblAbrangencia = new Abrangencia();
            $rsAbrangencia = $tblAbrangencia->buscar($arrBusca);
            $this->view->localizacoes = $rsAbrangencia;
        }
        else {
            return false;
        }
    }

    public function formInserirAction() {
        $get = Zend_Registry::get('get');
        $idProjeto = $get->idPreProjeto;
        $this->view->idPreProjeto = $idProjeto;
        
        //RECUPERA OS PAISES
        $rsPais = DeslocamentoDAO::buscarPais();
        $this->view->paises = $rsPais;

        //RECUPERA OS ESTADOS
        $rsEstados = Estado::buscar();
        $this->view->estados = $rsEstados;
    }

    public function cidadesAction() {
        $this->_helper->layout->disableLayout();
        $post = Zend_Registry::get('post');
        $idEstado = $post->idEstado;

        //RECUPERA AS CIDADES
        $Municipios = new Municipios();
        $rsCidades = $Municipios->buscar(array('idUFIBGE = ?' => $idEstado));
        if(count($rsCidades)>0){
            $html = '';
            foreach ($rsCidades as $cidades) {
                $html .= '<option value="'.$cidades->idMunicipioIBGE.'">'.utf8_encode($cidades->Descricao).'</option>';
            }
            echo $html;
        } else {
            echo '';
        }
        die();
    }

    public function salvarLocalRealizacaoAction() {
        $post = Zend_Registry::get("post");
        $idAbrangencia = $post->cod;
        $tblAbrangencia = new Abrangencia();

        //RECUPERA LOCALIZACOES CADASTRADAS
        $arrBusca = array();
        $arrBusca['idProjeto']=$this->idPreProjeto;
        $arrBusca['stAbrangencia']=1;
        $arrBusca['p.idPais']=$post->pais;
        if($post->pais == 31){
            $arrBusca['u.idUF']=$post->estados;
            $arrBusca['m.idMunicipioIBGE']=$post->cidades;

        }
        
        if(!empty($idAbrangencia)) {
            $arrBusca['idAbrangencia']=$idAbrangencia;
        }
        $rsAbrangencia = $tblAbrangencia->buscar($arrBusca);
        
        if(count($rsAbrangencia)>0 && empty($idAbrangencia)){
            parent::message("Local de Realização já cadastrado!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ALERT");
        }

        if(isset($_REQUEST['edital'])) {
            $edital = "&edital=s";
        }else {
            $edital = "";
        }

        $pais = $post->pais;
        $estados = $post->estados;
        $cidades = $post->cidades;

        //INSERE LOCAIS DE REALIZACAO (tabela SAC.dbo.Abrangencia)
        $dados = array(
                    "idProjeto"=>$this->idPreProjeto,
                    "stAbrangencia" => 1,
                    "Usuario"  =>$this->usuarioLogado,
                    "idPais"   =>$pais,
                    "idUF"     =>($pais==31) ? $estados : 0,
                    "idMunicipioIBGE"=>($pais==31) ? $cidades : 0
                );

        if (!empty($dados["idProjeto"]) && !empty($dados["idPais"])) {
            $retorno = $tblAbrangencia->insert($dados);
        }
        
        parent::message("Cadastro realizado com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");

    }
}
