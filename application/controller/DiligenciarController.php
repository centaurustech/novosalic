<?php

class DiligenciarController extends GenericControllerNew {

    private $idPronac = null;
    private $idPreProjeto = null;
    private $idProduto = null;
    private $situacao = null;
    private $tpDiligencia = null;
    private $usuarioLogado = null;
    private $idDiligencia = null;
    private $idAvaliacaoProposta = null;
    private $btnVoltar = null;// ajusta o link de voltar de acordo com o tipo de dilignecia

    public function init() {
        $this->view->title = "Salic - Sistema de Apoio �s Leis de Incentivo � Cultura"; // t�tulo da p�gina

        $auth = Zend_Auth::getInstance(); // instancia da autentica��o
        $PermissoesGrupo = array();

        //Da permissao de acesso a todos os grupos do usuario logado afim de atender o UC75
        if (isset($auth->getIdentity()->usu_codigo) ) {
            //Recupera todos os grupos do Usuario
            $Usuario    = new Usuario(); // objeto usu�rio
            $grupos     = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
            foreach ($grupos as $grupo) {
                $PermissoesGrupo[] = $grupo->gru_codigo;
            }
        }

        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);
        $this->usuarioLogado = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;

        //recupera ID do pre projeto (proposta)
        $this->idPronac = $this->getRequest()->getParam('idPronac');
        $this->idDiligencia = $this->getRequest()->getParam('idDiligencia');
        $this->idAvaliacaoProposta = $this->getRequest()->getParam('idAvaliacaoProposta');
        $this->situacao = $this->getRequest()->getParam('situacao');
        $this->tpDiligencia = $this->getRequest()->getParam('tpDiligencia');
        $this->idProduto = $this->getRequest()->getParam('idProduto');
        $this->idPreProjeto = $this->getRequest()->getParam('idPreProjeto');
        if ($this->tpDiligencia) {
            $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sess�o com o grupo ativo
            $urlArray = array('controller' => 'verificarreadequacaodeprojeto');

            // ajusta o link de voltar de acordo com o tipo de dilignecia
            if ($GrupoAtivo->codGrupo == 122) { // diligencia na readequacao - coordenador acompanhamento
                $this->btnVoltar = $this->view->url(array_merge($urlArray, array('action' => 'verificarreadequacaodeprojetocoordacompanhamento')));
            } elseif ($this->tpDiligencia == 171) { // diligencia na readequacao - tecnico acompanhamento
                $this->btnVoltar = $this->view->url(array_merge($urlArray, array('action' => 'verificarreadequacaodeprojetotecnico')));
            } elseif ($this->tpDiligencia == 179) { // diligencia na readequacao - parecerista
                $this->btnVoltar = $this->view->url(array_merge($urlArray, array('action' => 'verificarreadequacaodeprojetoparecerista')));
            } else {
                $this->btnVoltar = 'javascript:voltar()';
            }
        }

        $this->view->btnVoltar = $this->btnVoltar; // bot�o voltar dinamico

        parent::init(); // chama o init() do pai GenericControllerNew
    }

    /**
     * 
     */
    public function indexAction() {
        $this->_forward("listardiligenciaproponente");
    }

    /**
     * 
     */
    public function cadastrardiligenciaAction()
    {
        $verificacaodao     = new Verificacao();
        $Projetosdao        = new Projetos();
        $PreProjetodao      = new PreProjeto();
        $diligenciaDAO      = new Diligencia();
        $post               = Zend_Registry::get('post');

        $auth = Zend_Auth::getInstance(); // instancia da autentica��o
        $Usuario = new Usuario();
        $idagente = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
        $usu_identificacao = trim($idagente['usu_identificacao']);
        $idagente = $idagente['idAgente'];

        $utl = $diligenciaDAO->buscarUltDiligencia(array('idPronac = ?' => $this-> idPronac, 'stEnviado = ?' => 'N', 'stEstado  = ?' => 0, 'idSolicitante = ?' => new Zend_Db_Expr("isnull((SELECT usu_codigo FROM tabelas..usuarios WHERE usu_identificacao='".$usu_identificacao."'), (SELECT idAgente FROM Agentes.dbo.Agentes WHERE CNPJCPF='".$usu_identificacao."'))")))->current();
        if(count($utl) > 0) {
            $this->view->ultimo     = $utl;
        }
        $this->view->idPronac       = $this->idPronac;
        $this->view->idPreProjeto   = $this->idPreProjeto;
        $this->view->situacao       = $this->situacao;
        $this->view->idProduto      = $this->idProduto;
        $this->view->tpDiligencia   = $this->tpDiligencia;

        if ($this->view->idPronac) {
            $resp                   = $Projetosdao->dadosProjeto(array('pro.IdPRONAC = ?' => $this->view->idPronac));
            $this->view->nmCodigo   = 'PRONAC';
            $this->view->nmTipo     = 'DO PROJETO';
        }
        if ($this->view->idPreProjeto) {
            $resp                   = $PreProjetodao->dadosPreProjeto(array('pre.idPreProjeto = ?' => $this->view->idPreProjeto));
            $this->view->nmCodigo   = 'NR PROPOSTA';
            $this->view->nmTipo     = 'DA PROPOSTA';
        }
        $tipoDiligencia = $verificacaodao->tipoDiligencia(array('idVerificacao = ?' => $this->tpDiligencia));

        if (isset($resp) && is_object($resp) && count($resp)>0) {
            $this->view->pronac         =   $resp[0]->pronac;
            $this->view->nomeProjeto    =   $resp[0]->nomeProjeto;
        } else{
            $this->view->pronac         =   '';
            $this->view->nomeProjeto    =   '';
        }
        if (is_object($tipoDiligencia) && count($tipoDiligencia)>0) {
            $this->view->tipoDiligencia = $tipoDiligencia[0]->Descricao;
        } else {
            $this->view->tipoDiligencia = '';
        }

        $this->view->dataSolicitacao = date('d/m/Y H:i');
    }

    /**
     * 
     */
    public function visualizardiligenciaAction()
    {
        $verificacaodao         = new Verificacao();
        $Projetosdao            = new Projetos();
        $PreProjetodao          = new PreProjeto();
        $DocumentosExigidosDao  = new DocumentosExigidos();
        
        $post = Zend_Registry::get('post');
        //$this->view->dadosDiligencia    = $post;
        $this->view->idPronac           = $this->idPronac;
        $this->view->idPreProjeto       = $this->idPreProjeto;
        $this->view->idProduto          = $this->idProduto;
        
        $this->view->idDiligencia        = $this->getRequest()->getParam('idDiligencia');
        $this->view->idAvaliacaoProposta = $this->getRequest()->getParam('idAvaliacaoProposta');

        if ($this->view->idDiligencia) {
            $resp = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->view->idPronac, 'dil.idDiligencia = ?' => $this->view->idDiligencia));
            $this->view->nmCodigo       = 'PRONAC';
            $this->view->nmTipo         = 'DO PROJETO';
            $this->view->tipoDiligencia = $resp[0]->tipoDiligencia;
        }
        if ($this->view->idAvaliacaoProposta) {
            if ($this->idPronac) {
                $projeto        = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $idPreProjeto   = $projeto[0]->idProjeto;
            }

            if ($this->idPreProjeto)
                $idPreProjeto   = $this->idPreProjeto;
            $resp = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $idPreProjeto, ' aval.idAvaliacaoProposta = ?' => $this->view->idAvaliacaoProposta));

            $this->view->nmCodigo   = 'Nr PROPOSTA';
            $this->view->nmTipo     = 'DA PROPOSTA';
            $this->view->Descricao  = $resp[0]->Descricao;

        }
        
        $this->view->stEnviado      = $resp[0]->stEnviado;
        $this->view->pronac         = $resp[0]->pronac;
        $this->view->nomeProjeto    = $resp[0]->nomeProjeto;
        //$this->view->Proponente = $rd[0]->Proponente;
        $this->view->dataSolicitacao    = date('d/m/Y H:i', strtotime($resp[0]->dataSolicitacao));
        if ($resp[0]->dataResposta          != '')
            $this->view->dataResposta       = date('d/m/Y H:i', strtotime($resp[0]->dataResposta));
        $this->view->solicitacao        = $resp[0]->Solicitacao;
        $this->view->resposta           = $resp[0]->Resposta;
        if ($resp[0]->idCodigoDocumentosExigidos) {
            $documento                      = $DocumentosExigidosDao->listarDocumentosExigido($resp[0]->idCodigoDocumentosExigidos);
            $this->view->DocumentosExigido  = $documento[0]->Descricao;
            $this->view->Opcao              = $documento[0]->Opcao;
        }

        if($this->view->idDiligencia) {
            $arquivo = new Arquivo();
            $arquivos = $arquivo->buscarAnexosDiligencias($this->view->idDiligencia);
            $this->view->arquivos = $arquivos;
        }
    }

    public function imprimirdiligenciaAction() {
        $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
        $verificacaodao         = new Verificacao();
        $Projetosdao            = new Projetos();
        $PreProjetodao          = new PreProjeto();
        $DocumentosExigidosDao  = new DocumentosExigidos();

        $post = Zend_Registry::get('get');
        $this->view->idPronac           = $this->idPronac;
        $this->view->idPreProjeto       = $this->idPreProjeto;
        $this->view->idProduto          = $this->idProduto;

        $this->view->idDiligencia        = $post->idDiligencia;
        $this->view->idAvaliacaoProposta = $post->idAvaliacaoProposta;

        if ($this->view->idDiligencia) {
            $resp = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->view->idPronac, 'dil.idDiligencia = ?' => $this->view->idDiligencia));
            $this->view->nmCodigo       = 'PRONAC';
            $this->view->nmTipo         = 'DO PROJETO';
            $this->view->tipoDiligencia = $resp[0]->tipoDiligencia;
        }
        if ($this->view->idAvaliacaoProposta) {
            if ($this->idPronac) {
                $projeto        = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $idPreProjeto   = $projeto[0]->idProjeto;
            }

            if ($this->idPreProjeto)
                 $idPreProjeto   = $this->idPreProjeto;
                 $resp           = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $idPreProjeto, ' aval.idAvaliacaoProposta = ?' => $this->view->idAvaliacaoProposta));

                $this->view->nmCodigo   = 'Nr PROPOSTA';
                $this->view->nmTipo     = 'DA PROPOSTA';
                $this->view->Descricao  = $resp[0]->Descricao;

          }
                $this->view->stEnviado      = $resp[0]->stEnviado;
                $this->view->pronac         = $resp[0]->pronac;
                $this->view->nomeProjeto    = $resp[0]->nomeProjeto;
            //$this->view->Proponente = $rd[0]->Proponente;
                $this->view->dataSolicitacao    = date('d/m/Y H:i', strtotime($resp[0]->dataSolicitacao));
            if ($resp[0]->dataResposta          != '')
                $this->view->dataResposta       = date('d/m/Y H:i', strtotime($resp[0]->dataResposta));
                $this->view->solicitacao        = $resp[0]->Solicitacao;
                $this->view->resposta           = $resp[0]->Resposta;
//                $this->view->nmArquivo          = $resp[0]->nmArquivo;
//                $this->view->idArquivo          = $resp[0]->idArquivo;
            if ($resp[0]->idCodigoDocumentosExigidos) {
                $documento                      = $DocumentosExigidosDao->listarDocumentosExigido($resp[0]->idCodigoDocumentosExigidos);
                $this->view->DocumentosExigido  = $documento[0]->Descricao;
                $this->view->Opcao              = $documento[0]->Opcao;
        }

        if ($this->view->idDiligencia) {
            $arquivo = new Arquivo();
            $arquivos = $arquivo->buscarAnexosDiligencias($post->idDiligencia);
            $this->view->arquivos = $arquivos;
        }
    }
        	
    public function cadastrarrespostadiligenciaAction() {

        $post = Zend_Registry::get('post');
        $idArquivo = '';
        $Mensagem = '';
        if (!empty($_FILES) && is_file($_FILES['arquivo']['tmp_name'])) {
            $arquivoNome     = $_FILES['arquivo']['name']; // nome
            $arquivoTemp     = $_FILES['arquivo']['tmp_name']; // nome tempor�rio
            $arquivoTipo     = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho  = $_FILES['arquivo']['size']; // tamanho

            if (!empty($arquivoNome) && !empty($arquivoTemp)){
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extens�o
                $arquivoBinario  = Upload::setBinario($arquivoTemp); // bin�rio
                $arquivoHash     = Upload::setHash($arquivoTemp); // hash
            }

            $tipos = array('pdf');
            if (!in_array(strtolower($arquivoExtensao), $tipos)) {
                parent::message("Favor selecionar o arquivo no formato PDF!", "diligenciar/listardiligenciaproponente?idPronac=$this->idPronac", "ALERT");
            }

            if(!empty ($this->idPronac) && $this->idPronac != "0"){

                $dataString = file_get_contents($arquivoTemp);
                $arrData = unpack("H*hex", $dataString);
                $data = "0x".$arrData['hex'];

                // ==================== PERSISTE DADOS DO ARQUIVO =================//
                $dadosArquivo = array(
                    'nmArquivo'         => $arquivoNome,
                    'sgExtensao'        => $arquivoExtensao,
                    'biArquivo'         => $data,
                    'dsDocumento'       => 'Resposta de Dilig�ncia',
                    'idPronac'          => $this->idPronac,
                    'idTipoDocumento'   => 3,
                    'idDiligencia'      => $post->idDiligencia
                );
                $vw = new vwAnexarDocumentoDiligencia();
                $vw->inserirUploads($dadosArquivo);
            }

            $dados = array(
                'DtResposta' => new Zend_Db_Expr('GETDATE()'),
                'Resposta' => $_POST['dsResposta'],
                'idProponente' => $this->usuarioLogado,
                'stEnviado' => 'N'
            );
            $where = "idDiligencia = $post->idDiligencia";
            $diligenciaDAO = new Diligencia();
            $resp = $diligenciaDAO->update($dados, $where);
        }

        $verificacaodao         = new Verificacao();
        $Projetosdao            = new Projetos();
        $PreProjetodao          = new PreProjeto();
        $DocumentosExigidosDao  = new DocumentosExigidos();
        
        //xd($post);
        $this->view->idPronac               = $this->idPronac;
        $this->view->idPreProjeto           = $this->idPreProjeto;
        $this->view->idProduto              = $this->idProduto;
        $this->view->idDiligencia           = $this->idDiligencia;
        $this->view->idAvaliacaoProposta    = $this->idAvaliacaoProposta;
        $this->view->idUsuario              = Zend_Auth::getInstance()->getIdentity()->IdUsuario;

        if ($this->view->idDiligencia) {
            $resp                       = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->view->idPronac, 'dil.idDiligencia = ?' => $this->view->idDiligencia));
            $this->view->nmCodigo       = 'PRONAC';
            $this->view->nmTipo         = 'DO PROJETO';
            $this->view->tipoDiligencia = $resp[0]->tipoDiligencia;
        }
        if ($this->view->idAvaliacaoProposta) {
            if ($this->idPronac) {
                $projeto = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $idPreProjeto = $projeto[0]->idProjeto;
            }
            if ($this->idPreProjeto)
                $idPreProjeto       = $this->idPreProjeto;
            $resp = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $idPreProjeto, ' aval.idAvaliacaoProposta = ?' => $this->view->idAvaliacaoProposta));
            $this->view->nmCodigo   = 'Nr PROPOSTA';
            $this->view->nmTipo     = 'DA PROPOSTA';
        }

        $arquivo = new Arquivo();
        $arquivos = $arquivo->buscarAnexosDiligencias($this->idDiligencia);
        $this->view->arquivos = $arquivos;
        
        $this->view->arquivos                = $arquivos;
        $this->view->pronac                  = $resp[0]->pronac;
        $this->view->nomeProjeto             = $resp[0]->nomeProjeto;
        $this->view->dataSolicitacao         = date('d/m/Y H:i', strtotime($resp[0]->dataSolicitacao));
        if ($resp[0]->dataResposta          != '')
        $this->view->dataResposta            = date('d/m/Y H:i', strtotime($resp[0]->dataResposta));
        $this->view->solicitacao             = $resp[0]->Solicitacao;
        $this->view->resposta                = $resp[0]->Resposta;
        
        if ($resp[0]->idCodigoDocumentosExigidos)
        {
            $documento                       = $DocumentosExigidosDao->listarDocumentosExigido($resp[0]->idCodigoDocumentosExigidos);
            $this->view->DocumentosExigido   = $documento[0]->Descricao;
            $this->view->Opcao               = $documento[0]->Opcao;
        }
        if (!empty($resp[0]->stEnviado) && !empty($documento)) {
            $this->view->retorna            = "true";
        }
        $this->view->DocumentoExigido       = $DocumentosExigidosDao->listarDocumentosExigido();

        # comprovantes recusados
        $comprovantePagamentoModel = new ComprovantePagamento();
        $this->view->comprovantesDePagamento = $comprovantePagamentoModel->pesquisarComprovanteRecusado($this-> idPronac);
    }

    public function excluirarquivoAction(){
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

        $Arquivo = new vwAnexarDocumentoDiligencia();
        $result = $Arquivo->excluirArquivo($_POST['arquivo'], $_POST['diligencia']);

        if(count($result) > 0){
            echo json_encode(array('resposta'=>true));
        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }

    public function listardiligenciaproponenteAction() {

        //$this->operacoesDiligencia();
        //$post                     = Zend_Registry::get('post');
        $Projetosdao                = new Projetos();
        $PreProjetodao              = new PreProjeto();
        //$dao                      = new DiligenciarDao();
        //$this->view->idPronac     = 118389;
        $this->view->idPronac       = $this->idPronac;
        $this->view->idPreProjeto   = $this->idPreProjeto;
        $this->view->idProduto      = $this->idProduto;

        if ($this->view->idPronac) {

            if ($this->view->idProduto)
                $this->view->diligencias            = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->view->idPronac, 'dil.idProduto = ?' => $this->view->idProduto));
            else {
                $projeto                            = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $_idProjeto                         = isset($projeto[0]->idProjeto) && !empty($projeto[0]->idProjeto) ? $projeto[0]->idProjeto : 0;
                $this->view->diligenciasProposta    = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $_idProjeto,'aval.ConformidadeOK = ? '=>0));
                $this->view->diligencias            = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->view->idPronac));
            }
        }
        if ($this->view->idPreProjeto) {
            $this->view->diligenciasProposta        = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $this->view->idPreProjeto,'aval.ConformidadeOK <> ? '=>9));
            //$this->view->diligenciasProposta = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $this->view->idPreProjeto));
        }
    }

    /**
     * 
     */
    public function listardiligenciaanalistaAction()
    {
        $Projetosdao        = new Projetos();
        $PreProjetodao      = new PreProjeto();

        $this->view->idPronac           = $this->idPronac;
        $this->view->idPreProjeto       = $this->idPreProjeto;
        $this->view->situacao           = $this->situacao;
        $this->view->idProduto          = $this->idProduto;
        $this->view->tpDiligencia       = $this->tpDiligencia;

        if ($this->view->idPronac) {
            if ($this->idProduto) {
                $this->view->diligencias = $Projetosdao->listarDiligencias(
                    array(
                        'pro.IdPRONAC = ?' => $this->idPronac,
                        'dil.idProduto = ?' => $this->idProduto,
                        'dil.stEnviado = ?' => 'S'
                    )
                );
            } else {
                $projeto = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $_idProjeto = isset($projeto[0]->idProjeto) && !empty($projeto[0]->idProjeto) ? $projeto[0]->idProjeto : 0;
                $this->view->diligenciasProposta = $PreProjetodao->listarDiligenciasPreProjeto(
                    array(
                        'pre.idPreProjeto = ?' => $_idProjeto,
                        'aval.ConformidadeOK = ?' => 0
                    )
                );
                $this->view->diligencias = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->idPronac));
            }
        } else {
            if ($this->view->idPreProjeto) {
                $this->view->diligenciasProposta = $dao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $this->idPreProjeto,'aval.ConformidadeOK = ? '=>0));
            }
        }
    }

    private $situacaoProjetoResposta = array('C30' => 'anterior', 'E62' => 'anterior', 'E61' => 'anterior', 'E60' => 'anterior', 'E59' => 'anterior', 'E17' => 'E30', 'G18' => 'G54', 'D25' => 'anterior','D33'=>'anterior', 'B14' => 'anterior', 'E12' => 'anterior', 'E13' => 'anterior', 'E50' => 'anterior');
    private $situacaoProjetoNaoResposta = array('C30' => 'anterior', 'E62' => 'E66', 'E61' => 'E71', 'E60' => 'E69', 'E59' => 'anterior', 'E17' => 'E20', 'G18' => 'G20', 'D25' => 'mantem', 'B14' => 'anterior', 'E12' => 'anterior', 'E13' => 'anterior', 'E50' => 'anterior');

    private function situacaoProjeto($situacoes, $idPronac, $texto = '') {


        $ProjetoDAO             = new Projetos();
        $SituacaoDAO            = new Situacao();
        $HistoricoSituacaoDAO   = new HistoricoSituacao();
        $projeto                = $ProjetoDAO->buscar(array(' IdPRONAC = ?' => $idPronac));
        $situacao               = false;
        if (array_key_exists($projeto[0]->Situacao, $situacoes)) {
            switch ($situacoes[$projeto[0]->Situacao]) {
                case 'anterior':
                    
                    $historico = $HistoricoSituacaoDAO->buscar(array('AnoProjeto = ?' => $projeto[0]->AnoProjeto, 'Sequencial = ?' => $projeto[0]->Sequencial), array('Contador Desc'));

                    if ($historico)
                        $situacao = $historico[0]->Situacao;
                    else
                        $situacao = false;
                    break;
                case 'mantem':
                    $situacao = false;
                    break;
                default:
                    $situacao = $situacoes[$projeto[0]->Situacao];
                    break;
            }
            if ($situacao) {
                $situacaoDesc = $SituacaoDAO->buscar(array('Codigo = ?' => $situacao));
                if ($texto == '')
                    $texto = $situacaoDesc[0]->Descricao;
                $data = array(
                    'Situacao' => $situacaoDesc[0]->Codigo,
                    'DtSituacao' => new Zend_Db_Expr('GETDATE()'),
                    'ProvidenciaTomada' => $texto
                );
            }
        }
    }

    public function updatediligenciaAction() {
        $post = Zend_Registry::get('post');
        $diligenciaDAO = new Diligencia();
        $AvaliacaoPropostaDAO = new AvaliacaoProposta();
        $tblPreProjeto = new PreProjeto();
        $tblProjeto = new Projetos();

        $idArquivo = '';
        $Mensagem = '';
        if (is_file($_FILES['arquivo']['tmp_name'])) {

            $arquivoNome     = $_FILES['arquivo']['name']; // nome
            $arquivoTemp     = $_FILES['arquivo']['tmp_name']; // nome tempor�rio
            $arquivoTipo     = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho  = $_FILES['arquivo']['size']; // tamanho

            if (!empty($arquivoNome) && !empty($arquivoTemp)){
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extens�o
                $arquivoBinario  = Upload::setBinario($arquivoTemp); // bin�rio
                $arquivoHash     = Upload::setHash($arquivoTemp); // hash
            }

            $tipos = array('pdf');
            if (!in_array(strtolower($arquivoExtensao), $tipos)) {
                parent::message("Favor selecionar o arquivo no formato PDF!", "diligenciar/listardiligenciaproponente?idPronac=$this->idPronac", "ALERT");
            }

            if(!empty ($this->idPronac) && $this->idPronac != "0"){

                $dataString = file_get_contents($arquivoTemp);
                $arrData = unpack("H*hex", $dataString);
                $data = "0x".$arrData['hex'];

                // ==================== PERSISTE DADOS DO ARQUIVO =================//
                $dadosArquivo = array(
                    'nmArquivo'         => $arquivoNome,
                    'sgExtensao'        => $arquivoExtensao,
                    'biArquivo'         => $data,
                    'dsDocumento'       => 'Resposta de Dilig�ncia',
                    'idPronac'          => $this->idPronac,
                    'idTipoDocumento'   => 3,
                    'idDiligencia'      => $post->idDiligencia
                );
                $vw = new vwAnexarDocumentoDiligencia();
                $vw->inserirUploads($dadosArquivo);
            }
        }

        if(!empty ($retorno['Mensagem'])){
            $post->verificaEnviado = 'N';
        }

        if ($post->idDiligencia) {

            $rsProjeto = $tblProjeto->buscar(array("IdPRONAC = ?"=>$this->idPronac))->current();
            if(isset($rsProjeto->idProjeto) && !empty($rsProjeto->idProjeto)){
                $rsPreProjeto = $tblPreProjeto->buscar(array("idPreProjeto = ?"=>$rsProjeto->idProjeto))->current();
                $paramEdital = "";
                if($rsPreProjeto->idEdital && $rsPreProjeto->idEdital != 0){
                    $paramEdital = "&edital=sim";
                }
            }

            if ($post->idCodigoDocumentosExigidos)
                $idCodigoDocumentosExigidos = $post->idCodigoDocumentosExigidos;
            else
                $idCodigoDocumentosExigidos = new Zend_Db_Expr('null');

            $dados = array(
                'DtResposta' => new Zend_Db_Expr('GETDATE()'),
                'Resposta' => $_POST['resposta'],
                'idProponente' => $this->usuarioLogado,
                'stEnviado' => $post->verificaEnviado
            );
            $where = "idDiligencia = $post->idDiligencia";
            $resp = $diligenciaDAO->update($dados, $where);

            if ($post->verificaEnviado == 'S')
                $this->situacaoProjeto($this->situacaoProjetoResposta, $this->idPronac, 'Dilig�ncia respondida pelo proponente, esperando decis�o.');
            $aux = "?idPronac={$this->idPronac}&idDiligencia={$this->idDiligencia}&idProduto={$this->idProduto}{$paramEdital}";
        }

        if ($post->idAvaliacaoProposta) {

            $rsPreProjeto = $tblPreProjeto->buscar(array("idPreProjeto = ?"=>$this->idPreProjeto))->current();
            $paramEdital = "";
            if($rsPreProjeto->idEdital && $rsPreProjeto->idEdital != 0){
                $paramEdital = "&edital=sim";
            }

            if(!empty ($idArquivo)){
                $dados = array(
                    'dtResposta' => new Zend_Db_Expr('GETDATE()'),
                    'dsResposta' => $post->resposta,
                    'idArquivo' => $idArquivo,
                    'idCodigoDocumentosExigidos' => $post->idCodigoDocumentosExigidos,
                    'stEnviado' => $post->verificaEnviado
                );
            }
            else{
                $dados = array(
                    'dtResposta' => new Zend_Db_Expr('GETDATE()'),
                    'dsResposta' => $post->resposta,
                    'idCodigoDocumentosExigidos' => $post->idCodigoDocumentosExigidos,
                    'stEnviado' => $post->verificaEnviado
                );
            }
            $where = array('idAvaliacaoProposta = ?' => $post->idAvaliacaoProposta);
            $resp = $AvaliacaoPropostaDAO->update($dados, $where);

            $aux = "?idPreProjeto={$this->idPreProjeto}&idAvaliacaoProposta={$this->idAvaliacaoProposta}{$paramEdital}";
        }

        if(empty ($retorno['Mensagem'])){

            if ($resp) {
                if ($post->verificaEnviado == 'S') {
                    parent::message("Mensagem enviada com sucesso!", "diligenciar/listardiligenciaproponente{$aux}", "CONFIRM");
                    //$this->view->mensagem = 'Mensagem enviada com sucesso!';
                } else {
                    parent::message("Mensagem salva com sucesso!", "diligenciar/listardiligenciaproponente{$aux}", "CONFIRM");
                    //$this->view->mensagem = 'Mensagem salva com sucesso!';
                }
            } else {
                parent::message('N&atildeo foi possivel realizar a opera&ccedil;&atildeo solicitada!', "diligenciar/listardiligenciaproponente{$aux}", "ERROR");
                //$this->view->mensagem = 'N&atildeo foi possivel tente mais tarde!';
            }
        }
        else{
            parent::message($retorno['Mensagem'], "diligenciar/cadastrarrespostadiligencia{$aux}", "ERROR");
        }
    }

    /**
     * 
     */
    public function inserirdiligenciaAction()
    {
        $post = Zend_Registry::get('post');
        $diligenciaDAO = new Diligencia();
        $auth = Zend_Auth::getInstance();

        if (!$this->idPronac) {
            $this->_redirect(
                $this->view->url(
                    array(
                        'controller' => 'diligenciar',
                        'action' => 'cadastrardiligencia',
                        'tipoAnalise' => 'inicial',
                        'idPronac' => $this->getRequest()->getParam('idPronac'),
                        'situacao' => $this->getRequest()->getParam('situacao'),
                        'tpDiligencia' => $this->getRequest()->getParam('tpDiligencia'),
                    )
                )
            );
        }

        // caso ja tenha diligencia para o pronac
        if (isset($this->idPronac) && !empty($this->idPronac)) {
            $buscarDiligenciaResp = $diligenciaDAO->buscar(array('idPronac = ?' => $this->idPronac, 'DtResposta ?' => array(new Zend_Db_Expr('IS NULL')), 'stEnviado = ?'=>'S' ), array('idDiligencia DESC'),0,0,$post->idProduto);
            if (count($buscarDiligenciaResp) > 0) {
                $queryString = '?idPronac=' . $this->idPronac . '&situacao=' . $post->situacao . '&tpDiligencia=' . $post->tpDiligencia;
                parent::message('Existe dilig&ecirc;ncia aguardando resposta!', 'diligenciar/cadastrardiligencia' . $queryString, 'ALERT');
            }
        }

        $idagente = $auth->getIdentity()->usu_codigo;
        $idProduto = $post->idProduto ? $post->idProduto : new Zend_Db_Expr('null');

        $dados = array(
            'idPronac'          => $post->idPronac,
            'DtSolicitacao'     => null,
            'Solicitacao'       => filter_input(INPUT_POST, 'solicitacao'),
            'idSolicitante'     => null,
            'idTipoDiligencia'  => $post->tpDiligencia,
            'idProduto'         => $idProduto,
            'stEstado'          => 0,
            'stEnviado'         => 'N'
        );

        if(filter_input(INPUT_POST, 'btnEnvio') == 1){
            $dados['DtSolicitacao'] = new Zend_Db_Expr('GETDATE()');
            $dados['idSolicitante'] = $idagente;
            $dados['stEnviado'] = 'S';
        }
        $diligenciaDAO->inserir($dados);
        $this->view->mensagem = 'Dilig&ecirc;ncia enviada com sucesso!';
    }

    public function salvardiligenciaAction() {

        $post                   = Zend_Registry::get('post');
        $diligenciaDAO          = new Diligencia();
        $ProjetoDAO             = new Projetos();
        $AvaliacaoPropostaDAO   = new AvaliacaoProposta();
        $PreProjetoDAO          = new PreProjeto();
        $verificacaodao         = new Verificacao();
        $auth                   = Zend_Auth::getInstance();

        if ($this->idPronac) {
            $agente = $ProjetoDAO->buscarAgenteProjeto(array(' pro.IdPRONAC = ?' => $this->idPronac))->current();
            if ($post->idProduto)
                $idProduto = $post->idProduto;
            else
                $idProduto = new Zend_Db_Expr('null');

            $Usuario = new Usuario();
            $idagente = $auth->getIdentity()->usu_codigo;

            $stEnviado = 'N';
            if($_POST['btnEnvio'] == 1){
                $stEnviado = 'S';
            }
            
            $dados = array(
                'idPronac'          => $post->idPronac,
                'DtSolicitacao'     => new Zend_Db_Expr('GETDATE()'),
                'Solicitacao'       => $_POST['solicitacao'],
                'idSolicitante'     => $idagente,
                'idTipoDiligencia'  => $post->tpDiligencia,
                'idProduto'         => $idProduto,
                'stEstado'          => 0,
                'stEnviado'         => $stEnviado
            );

            $ult = $diligenciaDAO->alterar($dados, array('idDiligencia = ?' => $_POST['idDiligencia']));
            $tipoDiligencia = $verificacaodao->tipoDiligencia(array('idVerificacao = ?' => $post->tpDiligencia));

            $data = array(
                'Situacao'          => $this->situacao,
                'DtSituacao'        => date("Y-m-d"),
                'ProvidenciaTomada' => 'Projeto Diligenciado na Fase ' . $tipoDiligencia[0]->Descricao
            );
        }
        

        if($_POST['btnEnvio'] == 1){
            $msgAlert = 'Enviado com sucesso!';
        } else {
            $msgAlert = 'Salvo com sucesso!';
        }

        $aux = "?idPronac={$this->idPronac}&situacao={$this->situacao}&tpDiligencia={$post->tpDiligencia}";
        //parent::message("$msgAlert", "diligenciar/cadastrardiligencia{$aux}", "CONFIRM");
        parent::message("$msgAlert", "diligenciar/listardiligenciaanalista{$aux}", "CONFIRM");

    }
        
    private function eviarEmail($idPronac,$tpDiligencia){
        $auth = Zend_Auth::getInstance();
        $tbTextoEmailDAO    =   new tbTextoEmail();
        $projetosDAO        =   new Projetos();

        $where  =   array(
                        'idTextoEmail = ?'  =>  14
                    );
        $textoEmail     =   $tbTextoEmailDAO->buscar($where)->current();
  
        $dadosProjeto   =   $projetosDAO->dadosProjetoDiligencia($idPronac)->current();

        //para Produ�?o comentar linha abaixo e para teste descomente ela
        $email  =   'END-EMAIL';
        //para Produ�?o descomentar linha abaixo e para teste comente ela
        //$email   =  $dadosProjeto->Email;

        $mens = '<b>Projeto: ' . $dadosProjeto->pronac . ' - ' . $dadosProjeto->NomeProjeto . '<br> Proponente: ' .
         $dadosProjeto->Destinatario . '<br> </b>' . $textoEmail->dsTexto;

        
        
        $assunto = 'Diligencia na fase de ';
        switch ($tpDiligencia){
            case 124:
                $assunto .= ' Analise Tecnica Inicial';
                break;
            case 126:
                $assunto .= ' Comissao CNIC ';
                break;
            case 171:
                $assunto .= ' Readequacao ';
                break;
            case 172:
                $assunto .= ' Fiscalizacao ';
                break;
            case 173:
                $assunto .= ' Relatorio Trimestral ';
                break;
            case 174:
                $assunto .= ' Prestacao de Contas ';
                break;
            case 178:
                $assunto .= ' Readequacao CNIC ';
                break;
            case 179:
                $assunto .= ' Readequacao Parecerista ';
                break;
            case 180:
                $assunto .= ' Relatorio Final';
                break;
            case 181:
                $assunto .= ' Checklist Analise Inicial ';
                break;
            case 182:
                $assunto .= ' Checklist Readequacao ';
                break;
        }
        $perfil = "SALICWEB";

        $enviaEmail = EmailDAO::enviarEmail($email, $assunto, $mens, $perfil);

        $tbHistoricoEmailDAO = new tbHistoricoEmail();

        $dados = array(
                    'idPronac'=>$idPronac,
                    'idTextoemail'=>14,
                    'iDAvaliacaoProposta'=>new Zend_Db_Expr('NULL'),
                    'DtEmail'=>new Zend_Db_Expr('getdate()'),
                    'stEstado'=>1,
                    'idUsuario'=>$auth->getIdentity()->usu_codigo,
                 );
        
        $tbHistoricoEmailDAO->inserir($dados);


    }

    private function operacoesDiligencia() {
        
    }

    private function anexararquivo() {
        // pega as informa��es do arquivo
        $idArquivo = '';
        $Mensagem = '';
        if (is_file($_FILES['arquivo']['tmp_name'])) {

            $arquivoNome     = $_FILES['arquivo']['name']; // nome
            $arquivoTemp     = $_FILES['arquivo']['tmp_name']; // nome tempor�rio
            $arquivoTipo     = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho  = $_FILES['arquivo']['size']; // tamanho

            if (!empty($arquivoNome) && !empty($arquivoTemp)){
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extens�o
                $arquivoBinario  = Upload::setBinario($arquivoTemp); // bin�rio
                $arquivoHash     = Upload::setHash($arquivoTemp); // hash
            }

            if(!isset($_FILES['arquivo'])){
                parent::message("O arquivo n&atilde;o atende os requisitos informados no formul&aacute;rio.", "upload/form-enviar-arquivo-marca?idPronac=$idPronac", "ERROR");
            }

            if(empty($_FILES['arquivo']['tmp_name'])){
                parent::message("Favor selecionar um arquivo.", "upload/form-enviar-arquivo-marca?idPronac=$idPronac", "ERROR");
            }

            $tipos = array('bmp','gif','jpeg','jpg','png','raw','tif','pdf');
            if (!in_array(strtolower($arquivoExtensao), $tipos)) {
                parent::message("Favor selecionar o arquivo de Marca no formato BMP, GIF, JPEG, JPG, PNG, RAW, TIF ou PDF!", "upload/form-enviar-arquivo-marca?idPronac=$idPronac", "ERROR");
            }

            if(!empty ($idPronac) && $idPronac != "0"){

                $dataString = file_get_contents($arquivoTemp);
                $arrData = unpack("H*hex", $dataString);
                $data = "0x".$arrData['hex'];

                // ==================== PERSISTE DADOS DO ARQUIVO =================//
                $dadosArquivo = array(
                        'nmArquivo'         => $arquivoNome,
                        'sgExtensao'        => $arquivoExtensao,
                        'biArquivo'         => $data,
                        'dsDocumento'       => $observacao,
                        'idPronac'          => $idPronac);

                $Arquivo = new Arquivo();
                $idArquivo = $Arquivo->inserirMarca($dadosArquivo);
            }
        }
        return array('idArquivo'=>$idArquivo,'Mensagem'=>$Mensagem);
    }

    public function imprimirAction() {
        $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
        $verificacaodao         = new Verificacao();
        $Projetosdao            = new Projetos();
        $PreProjetodao          = new PreProjeto();
        $DocumentosExigidosDao  = new DocumentosExigidos();

        $post = Zend_Registry::get('post');
        $this->view->idPronac           = $this->idPronac;
        $this->view->idPreProjeto       = $this->idPreProjeto;
        $this->view->idProduto          = $this->idProduto;

        $this->view->idDiligencia        = $post->idDiligencia;
        $this->view->idAvaliacaoProposta = $post->idAvaliacaoProposta;

        if ($this->view->idDiligencia) {
            $resp = $Projetosdao->listarDiligencias(array('pro.IdPRONAC = ?' => $this->view->idPronac, 'dil.idDiligencia in (?)' => $this->view->idDiligencia));
            $this->view->nmCodigo       = 'PRONAC';
            $this->view->nmTipo         = 'DO PROJETO';
            $this->view->dadosDiligencia = $resp;
        }

        if ($this->view->idAvaliacaoProposta) {
            if ($this->idPronac) {
                $projeto = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $idPreProjeto = $projeto[0]->idProjeto;
            }

            if ($projeto[0]->idProjeto){
                $idPreProjeto = $projeto[0]->idProjeto;
            }

            $resp = $PreProjetodao->listarDiligenciasPreProjeto(array('pre.idPreProjeto = ?' => $idPreProjeto, ' aval.idAvaliacaoProposta in (?)' => $this->view->idAvaliacaoProposta));
            $this->view->nmCodigo   = 'Nr PROPOSTA';
            $this->view->nmTipo     = 'DA PROPOSTA';
            $this->view->dadosDiligencia  = $resp;
        }

        if ($resp[0]->idCodigoDocumentosExigidos) {
            $documento                      = $DocumentosExigidosDao->listarDocumentosExigido($resp[0]->idCodigoDocumentosExigidos);
            $this->view->DocumentosExigido  = $documento[0]->Descricao;
            $this->view->Opcao              = $documento[0]->Opcao;
        }

        $arquivos = array();
        if ($this->view->idDiligencia) {
            $arquivo = new Arquivo();
            foreach ($post->idDiligencia as $ids) {
                $arquivos[$ids] = $arquivo->buscarAnexosDiligencias($ids);
            }
        }
        $this->view->arquivos = $arquivos;
    }

    public function prorrogarAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $post                   = Zend_Registry::get('post');
        $Diligenciadao          = new Diligencia();
        $AvaliacaoPropostadao   = new AvaliacaoProposta();
        $Projetosdao            = new Projetos();
        $dados                  = array(
            'stProrrogacao' => "S"
        );
        if ($post->idPronac != '') {
            if ($post->idDiligencia) {

                $where = array(
                    'idPronac = ?'      => $post->idPronac,
                    'idDiligencia = ?'  => $post->idDiligencia
                );
                $prorrogado = $Diligenciadao->update($dados, $where);
            }
            if ($post->idAvaliacaoProposta) {
                $projeto = $Projetosdao->buscar(array('IdPRONAC = ?' => $this->idPronac));
                $where = array(
                    'idProjeto = ?'             => $projeto[0]->idProjeto,
                    'idAvaliacaoProposta = ?'   => $post->idAvaliacaoProposta
                );
                $prorrogado = $AvaliacaoPropostadao->update($dados, $where);
            }
        } else {
            $AvaliacaoPropostadao = new AvaliacaoProposta();
            $where = array(
                'idProjeto = ?'             => $post->idPreProjeto,
                'idAvaliacaoProposta = ?'   => $post->idAvaliacaoProposta
            );
            $prorrogado = $AvaliacaoPropostadao->update($dados, $where);
        }
        if ($prorrogado) {
            echo json_encode(array('result' => true, 'mensagem' => 'Prorrogado com sucesso.'));
        } else {
            echo json_encode(array('result' => false, 'mensagem' => 'N&atilde;o foi possivel!'));
        }
    }

    /*
      Descri�?o: checar os prazos para resposta de dilig?ncia.
      O cursor � utilizado por a trigger que altera a situa�?o do projeto, quando o
      propoenente responde a dilig?ncia, atua atrav�s do atribuito idPronac.
     */

    public function checarprazorespostaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $diligenciaDao          = new Diligencia();
        $AvaliacaoPropostaDao   = new AvaliacaoProposta();
        //atualizar situa�?o do projeto para diligencias n?o respondidas
        $diligenciaProjeto      = $diligenciaDao->diligenciasNaoRespondidas();
        foreach ($diligenciaProjeto as $value) {
            $this->situacaoProjeto($this->situacaoProjetoNaoResposta, $value->idPronac);
        }
        //resposta da diligencia Projeto
        $diligenciaProjeto = $diligenciaDao->diligenciasNaoRespondidas(true);
        $data = array(
            'DtResposta'    => new Zend_Db_Expr('GETDATE()'),
            'stEnviado'     => 'S',
            'RESPOSTA'      => 'O PROPONENTE N?O RESPONDEU A DILIG?NCIA NO PRAZO DETERMINADO PELA IN 3 DE 30 DE DEZEMBRO DE 2010; ESPERANDO DECIS?O.'
        );
        $where = array('idPronac in (?)' => $diligenciaProjeto);
        $diligenciaDao->update($data, $where);

        //resposta da diligencia Proposta
        $diligenciaProposta = $AvaliacaoPropostaDao->diligenciasNaoRespondidas(true);
        $data = array(
            'dtResposta'    => new Zend_Db_Expr('GETDATE()'),
            'stEnviado'     => 'S',
            'dsResposta'    => 'O PROPONENTE N?O RESPONDEU A DILIG?NCIA NO PRAZO DETERMINADO PELA IN 3 DE 30 DE DEZEMBRO DE 2010; ESPERANDO DECIS?O.'
        );
        $where = array('idPronac in (?)' => $diligenciaProposta);
        $AvaliacaoPropostaDao->update($data, $where);

        /* $dao        = new DiligenciarDao();

          $dao->checarprazoresposta();

          $this->situacaoProjeto($this->situacaoProjetoNaoResposta);

          $dao->checarprazorespostaProposta(); */
    }


	public function listardiligenciaadmissibilidadeAction()
	{
		$Projetosdao   = new Projetos();
		$PreProjetodao = new PreProjeto();

		ini_set('memory_limit', '-1');

		$this->view->diligencias         = $Projetosdao->listarDiligencias(array('dil.idTipoDiligencia = ?' => 124, 'dil.stEnviado = ?' => 'S', 'dil.stEstado = ?' => '0', 'dil.DtResposta ?' => new Zend_Db_Expr('IS NOT NULL'), 'dil.idProponente ?' => new Zend_Db_Expr('IS NOT NULL')));
		$this->view->diligenciasProposta = $PreProjetodao->listarDiligenciasPreProjeto(array('aval.dtResposta ?' => new Zend_Db_Expr('IS NOT NULL'), 'aval.stEnviado = ?' => 'S'));
	}
}
?>
