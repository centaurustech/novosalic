<?php
/**
 * Controller Admissibilidade
 * @author Equipe RUP - Politec
 * @since 07/06/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright ï¿½ 2010 - Ministï¿½rio da Cultura - Todos os direitos reservados.
 **/

require_once "GenericControllerNew.php";

class AdmissibilidadeController extends GenericControllerNew {

    private $idPreProjeto = null;
    private $idUsuario = null;
    private $intTamPag = 50;
    private $codOrgaoSuperior = null;
    private $codGrupo = null;
    private $codOrgao = null;
    private $COD_CLASSIFICACAO_DOCUMENTO = 23;

    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        
        // verifica as permissões
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 90;  // Protocolo - Documento
        $PermissoesGrupo[] = 91;  // Protocolo - Recebimento
        $PermissoesGrupo[] = 92;  // Tecnico de Admissibilidade
        $PermissoesGrupo[] = 93;  // Coordenador de Parecerista
        $PermissoesGrupo[] = 94;  // Parecerista
        $PermissoesGrupo[] = 95;  // Consulta
        $PermissoesGrupo[] = 96;  // Consulta Gerencial
        $PermissoesGrupo[] = 97;  // Gestor do SALIC
        $PermissoesGrupo[] = 99;  // Acompanhamento
        $PermissoesGrupo[] = 100; // Prestação de Contas
        $PermissoesGrupo[] = 103; // Coordenador de Analise
        $PermissoesGrupo[] = 104; // Protocolo - Envio / Recebimento
        $PermissoesGrupo[] = 110; // Tecnico de Analise
        $PermissoesGrupo[] = 113; // Coordenador de Arquivo
        $PermissoesGrupo[] = 114; // Coordenador de Editais
        $PermissoesGrupo[] = 115; // Atendimento Representacoes
        $PermissoesGrupo[] = 119; // Presidente da CNIC
        $PermissoesGrupo[] = 120; // Coordenador CNIC
        $PermissoesGrupo[] = 121; // Tecnico de Acompanhamento
        $PermissoesGrupo[] = 122; // Coordenador de Acompanhamento
        $PermissoesGrupo[] = 123; // Coordenador Geral de Acompanhamento
        $PermissoesGrupo[] = 124; // Tecnico de Prestacao de Contas
        $PermissoesGrupo[] = 125; // Coordenador de Prestacao de Contas
        $PermissoesGrupo[] = 127; // Coordenador de Atendimento
        $PermissoesGrupo[] = 128; // Tecnico de Portaria
        $PermissoesGrupo[] = 131; // Coordenador de Admissibilidade
        $PermissoesGrupo[] = 133; // Membros Natos da CNIC
        $PermissoesGrupo[] = 134; // Coordenador de Fiscalizacao
        $PermissoesGrupo[] = 135; // Tecnico de Fiscalizacao
        $PermissoesGrupo[] = 136; // Coordenador de Entidade Vinculada
        $PermissoesGrupo[] = 138; // Coordenador de Avaliacao
        $PermissoesGrupo[] = 139; // Tecnico de Avaliacao
        $PermissoesGrupo[] = 140; // Tecnico de Admissibilidade Edital
        //parent::perfil(1, $PermissoesGrupo);
        //parent::init();
        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);
        parent::init();

        //recupera ID do pre projeto (proposta)
        if(!empty ($_REQUEST['idPreProjeto'])){
            $this->idPreProjeto = $_REQUEST['idPreProjeto'];
        }

        isset($auth->getIdentity()->usu_codigo) ? $this->idUsuario = $auth->getIdentity()->usu_codigo : $this->idUsuario = $auth->getIdentity()->IdUsuario;
        //$this->idUsuario = $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        if(isset($auth->getIdentity()->usu_codigo)){
            
            $this->codGrupo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
            $this->codOrgao = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão

            $this->codOrgaoSuperior = (!empty($auth->getIdentity()->usu_org_max_superior))?$auth->getIdentity()->usu_org_max_superior:$auth->getIdentity()->usu_orgao;
        }

    }

    /**
     * Redireciona para o fluxo inicial do sistema
     * @access public
     * @param void
     * @return void
     */

    public function indexAction(){
        $this->_redirect("/admissibilidade/listar-propostas");
    }

    public function validarAcessoAdmissibilidade(){
        if(empty($this->idPreProjeto)){
            parent::message("Necessário informar o número da proposta.", "/admissibilidade/listar-propostas", "ALERT");
        }
    }
    public function listarpropostasproponenteAction() {

    }

    public function exibirpropostaculturalAction() {
        $idPreProjeto = $this->idPreProjeto;
        $dados = AnalisarPropostaDAO::buscarGeral($idPreProjeto);
        $this->view->itensGeral = $dados;
        
        //========== inicio codigo dirigente ================
        /*==================================================*/
        $arrMandatos = array();
        $this->view->mandatos = $arrMandatos;
        $preProjeto = new PreProjeto();
        $rsDirigentes = array();
        
        $Empresa = $preProjeto->buscar(array('idPreProjeto = ?' => $this->idPreProjeto))->current();
        $idEmpresa = $Empresa->idAgente;

        $Projetos = new Projetos();
        $dadosProjeto = $Projetos->buscar(array('idProjeto = ?' => $this->idPreProjeto))->current();

        $Pronac = null;
        if(count($dadosProjeto)>0){
            $Pronac = $dadosProjeto->AnoProjeto.$dadosProjeto->Sequencial;
        }
        $this->view->Pronac = $Pronac;
        
        if(isset($dados[0]->CNPJCPFdigirente) && $dados[0]->CNPJCPFdigirente != "") {
            $tblAgente = new Agentes();
            $tblNomes = new Nomes();
            foreach ($dados as $v) {
                $rsAgente = $tblAgente->buscarAgenteNome(array('CNPJCPF=?'=>$v->CNPJCPFdigirente))->current();
                $rsDirigentes[$rsAgente->idAgente]['CNPJCPFDirigente'] = $rsAgente->CNPJCPF;
                $rsDirigentes[$rsAgente->idAgente]['idAgente'] = $rsAgente->idAgente;
                $rsDirigentes[$rsAgente->idAgente]['NomeDirigente'] = $rsAgente->Descricao;
            }

            $tbDirigenteMandato = new tbAgentesxVerificacao();
            foreach($rsDirigentes as $dirigente) {
                $rsMandato = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idEmpresa, 'idDirigente = ?' => $dirigente['idAgente'],'stMandato = ?' => 0));
                $NomeDirigente = $dirigente['NomeDirigente'];
                $arrMandatos[$NomeDirigente] = $rsMandato;
            }
        }

        $this->view->dirigentes = $rsDirigentes;
        $this->view->mandatos   = $arrMandatos;
        //============== fim codigo dirigente ================
        /*==================================================*/
        
        $propostaPorEdital = false;
        if($this->view->itensGeral[0]->idEdital && $this->view->itensGeral[0]->idEdital != 0){
            $propostaPorEdital = true;
        }
        $this->view->isEdital   = $propostaPorEdital;
        $this->view->itensTelefone              = AnalisarPropostaDAO::buscarTelefone($this->view->itensGeral[0]->idAgente);
        $this->view->itensPlanosDistribuicao    = AnalisarPropostaDAO::buscarPlanoDeDistribucaoProduto($idPreProjeto);
        $this->view->itensFonteRecurso          = AnalisarPropostaDAO::buscarFonteDeRecurso($idPreProjeto);
        $this->view->itensLocalRealiazacao      = AnalisarPropostaDAO::buscarLocalDeRealizacao($idPreProjeto);
        $this->view->itensDeslocamento          = AnalisarPropostaDAO::buscarDeslocamento($idPreProjeto);
        $this->view->itensPlanoDivulgacao       = AnalisarPropostaDAO::buscarPlanoDeDivulgacao($idPreProjeto);

        //DOCUMENTOS ANEXADOS PROPOSTA
        $tbl = new tbDocumentosPreProjeto();
        $rs = $tbl->buscarDocumentos(array("idProjeto = ?" => $this->idPreProjeto));
        $this->view->arquivosProposta = $rs;

        //DOCUMENTOS ANEXADOS PROPONENTE
        $tbA = new tbDocumentosAgentes();
        $rsA = $tbA->buscarDocumentos(array("idAgente = ?" => $dados[0]->idAgente));
        $this->view->arquivosProponente = $rsA;

        //DOCUMENTOS ANEXADOS NA DILIGENCIA
        $tblAvaliacaoProposta = new AvaliacaoProposta();
        $rsAvaliacaoProposta = $tblAvaliacaoProposta->buscar(array("idProjeto = ?"=>$idPreProjeto, "idArquivo ?"=>new Zend_Db_Expr("IS NOT NULL")));
        $tbArquivo = new tbArquivo();
        $arrDadosArquivo = array();
        $arrRelacionamentoAvaliacaoDocumentosExigidos = array();
        if(count($rsAvaliacaoProposta) > 0){
            foreach($rsAvaliacaoProposta as $avaliacao){
                $arrDadosArquivo[$avaliacao->idArquivo] = $tbArquivo->buscar(array("idArquivo = ?"=>$avaliacao->idArquivo));
                $arrRelacionamentoAvaliacaoDocumentosExigidos[$avaliacao->idArquivo] = $avaliacao->idCodigoDocumentosExigidos;
            }
        }
        $this->view->relacionamentoAvaliacaoDocumentosExigidos = $arrRelacionamentoAvaliacaoDocumentosExigidos;
        $this->view->itensDocumentoPreProjeto   = $arrDadosArquivo;

        //PEGANDO RELACAO DE DOCUMENTOS EXIGIDOS(GERAL, OU SEJA, TODO MUNDO)
        $tblDocumentosExigidos = new DocumentosExigidos();
        $rsDocumentosExigidos = $tblDocumentosExigidos->buscar()->toArray();
        $arrDocumentosExigidos = array();
        foreach($rsDocumentosExigidos as $documentoExigido){
            $arrDocumentosExigidos[$documentoExigido["Codigo"]] = $documentoExigido;
        }
        $this->view->documentosExigidos = $arrDocumentosExigidos;
        $this->view->itensHistorico = AnalisarPropostaDAO::buscarHistorico($idPreProjeto);
        $this->view->itensPlanilhaOrcamentaria = AnalisarPropostaDAO::buscarPlanilhaOrcamentaria($idPreProjeto);

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasProdutos($this->idPreProjeto);
        $this->view->Etapa = $buscarEtapa;

        $buscarItem = ManterorcamentoDAO::buscarItensProdutos($this->idPreProjeto);
        $this->view->Item = $buscarItem;
        $this->view->AnaliseCustos = PreProjeto::analiseDeCustos($this->idPreProjeto);
        
        $this->view->idPreProjeto = $this->idPreProjeto;
        $pesquisaView = $this->_getParam('pesquisa');
        if($pesquisaView == 'proposta') {
            $this->view->menu = 'inativo';
            $this->view->tituloTopo = 'Consultar dados da proposta';
        }

        if($propostaPorEdital){
            $tbFormDocumentoDAO = new tbFormDocumento();
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?'=>$this->view->itensGeral[0]->idEdital,'idClassificaDocumento = ?'=>$this->COD_CLASSIFICACAO_DOCUMENTO));
            
            //busca o nome do EDITAL
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?'=>$this->view->itensGeral[0]->idEdital));
            $nmEdital = $edital[0]->nmFormDocumento;
            $this->view->nmEdital = $nmEdital;
            
            $arrPerguntas = array();
            $arrRespostas = array();
            $tbPerguntaDAO = new tbPergunta();
            $tbRespostaDAO = new tbResposta();

            foreach($edital as $registro){
                $questoes = $tbPerguntaDAO->montarQuestionario($registro["nrFormDocumento"],$registro["nrVersaoDocumento"]);
                $questionario = '';
                if(is_object($questoes) and count($questoes) > 0){
                    foreach ($questoes as $questao) {
                        $resposta = '';
                        $where = array(
                                'nrFormDocumento = ?'       =>$registro["nrFormDocumento"]
                                ,'nrVersaoDocumento = ?'    =>$registro["nrVersaoDocumento"]
                                ,'nrPergunta = ?'           =>$questao->nrPergunta
                                ,'idProjeto = ?'            =>$this->idPreProjeto
                        );
                        $resposta = $tbRespostaDAO->buscar($where);
                        $arrPerguntas[$registro["nrFormDocumento"]]["titulo"] = $registro["nmFormDocumento"];
                        $arrPerguntas[$registro["nrFormDocumento"]]["pergunta"][] = $questao->toArray();
                        $arrRespostas[] = $resposta->toArray();
                    }
                }
            }
            $this->view->perguntas = $arrPerguntas;
            $this->view->respostas = $arrRespostas;

            $this->montaTela("admissibilidade/proposta-por-edital.phtml");
        } else {
            $this->montaTela("admissibilidade/proposta-por-incentivo-fiscal.phtml");
        }
    }

    public function abrirDocumentosAnexadosAction() {
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout

        $idProjeto = $this->_request->getParam("idProjeto");
        $id = $this->_request->getParam('id');
        $tipo = $this->_request->getParam('tipo');
        $tipoDoc = null;
        $bln = "false";

        if($tipo == '1') {
            $tipoDoc = "tbDocumentosAgentes"; //SAC.dbo.tbDocumentosAgentes
        } else if($tipo == '2') {
            $tipoDoc = "tbDocumentosPreProjeto"; //SAC.dbo.tbDocumentosPreProjeto
        } else if($tipo == '3') {
            $tipoDoc = "tbDocumento"; //SAC.dbo.tbDocumento
        }

        // Configuração o php.ini para 10MB
        @ini_set("mssql.textsize",      10485760);
        @ini_set("mssql.textlimit",     10485760);
        @ini_set("upload_max_filesize", "10M");

        // busca o arquivo
        $resultado = UploadDAO::abrirdocumentosanexados($id, $tipoDoc);
        if(count($resultado) > 0) {
            if($tipo == 1){
                $this->_forward("abrirdocumentosanexadosbinario", "upload", "", array('id'=>$id,'busca'=>$tipoDoc));
            } else {
                $this->_forward("abrirdocumentosanexados", "upload", "", array('id'=>$id,'busca'=>$tipoDoc));
            }
            $bln = "true";
        }

        if($bln == "false") {
            $url = Zend_Controller_Front::getInstance()->getBaseUrl()."/consultardadosprojeto/?idPronac={$idPronac}";
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->flashMessenger->addMessage("N&atilde;o foi poss&iacute;vel abrir o arquivo especificado. Tente anex&aacute;-lo novamente.");
            $this->_helper->flashMessengerType->addMessage("ERROR");
            JS::redirecionarURL($url);
            exit();
        }
    }

    public function abrirDocumentosAnexadosAdmissibilidadeAction() {
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout

        $idProjeto = $this->_request->getParam("idProjeto");
        $id = $this->_request->getParam('id');
        $tipo = $this->_request->getParam('tipo');
        $tipoDoc = null;
        $bln = "false";

        if($tipo == '1') {
            $tipoDoc = "tbDocumentosAgentes"; //SAC.dbo.tbDocumentosAgentes
        } else if($tipo == '2') {
            $tipoDoc = "tbDocumentosPreProjeto"; //SAC.dbo.tbDocumentosPreProjeto
        } else if($tipo == '3') {
            $tipoDoc = "tbDocumento"; //SAC.dbo.tbDocumento
        }

        // Configuração o php.ini para 10MB
        @ini_set("mssql.textsize",      10485760);
        @ini_set("mssql.textlimit",     10485760);
        @ini_set("upload_max_filesize", "10M");

        // busca o arquivo
        $resultado = UploadDAO::abrirdocumentosanexados($id, $tipoDoc);
        if(count($resultado) > 0) {
            $this->_forward("abrirdocumentosanexados", "upload", "", array('id'=>$id,'busca'=>$tipoDoc));
            $bln = "true";
        }

        if($bln == "false") {
            $url = Zend_Controller_Front::getInstance()->getBaseUrl()."/consultardadosprojeto/?idPronac={$idPronac}";
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->flashMessenger->addMessage("N&atilde;o foi poss&iacute;vel abrir o arquivo especificado. Tente anex&aacute;-lo novamente.");
            $this->_helper->flashMessengerType->addMessage("ERROR");
            JS::redirecionarURL($url);
            exit();
        }
    }
    

    public function incluiravaliacaoAction() {

        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();
        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?"=>$this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto  = strip_tags($rsProposta->NomeProjeto);
        $this->view->dataAtual  = date("d/m/Y");
        $this->view->dataAtualBd  = date("Y/m/d H:i:s");
    }

    public function salvaravaliacaoAction(){

         $dao                   = new AnalisarPropostaDAO();
         $post                  = Zend_Registry::get('post');
         $dado                  = array();
         $dados['idProjeto']  = $post->idPreProjeto;
         $dados['idTecnico']     = $this->idUsuario;
         $dados['dtEnvio']       = $post->dataAtual;
         $dados['dtAvaliacao']   = $post->dataAtual;
         $dados['avaliacao']     = $_POST['despacho'];
         $dados['ConformidadeOK']= $post->conformidade;
         $dados['stEstado']      = 0;
         $dados['stEnviado']     = 'N';

         $projetoExiste = AnalisarPropostaDAO::verificarAvaliacao($post->idPreProjeto);

         //Esse if so existe por que nao existe objeto de negocio.
         if(count($projetoExiste)>0){
             $dados['dtEnvio'] = null;
         }

         $avaliacaoProposta = new tbAvaliacaoProposta();
         $insert = $avaliacaoProposta->inserir($dados);
         parent::message("Despacho encaminhado com sucesso!", "/admissibilidade/exibirpropostacultural?idPreProjeto=".$post->idPreProjeto."&gravado=sim", "CONFIRM");
    }

    private function eviarEmail($idProjeto,$Mensagem,$pronac = null){
        $auth = Zend_Auth::getInstance();
        $tbTextoEmailDAO    =   new tbTextoEmail();
        $preProjetosDAO     =   new PreProjeto();

        $dadosProjeto   =   $preProjetosDAO->dadosProjetoDiligencia($idProjeto);

		$tbHistoricoEmailDAO = new tbHistoricoEmail();

		foreach ($dadosProjeto as $d) :
			//para Produç?o comentar linha abaixo e para teste descomente ela
			//para Produç?o descomentar linha abaixo e para teste comente ela
			$email   = trim(strtolower($d->Email));
                        if($pronac){
                            $mens    = '<b>Proposta: ' .$d->idProjeto. ' - ' .$d->NomeProjeto. '<br> Pronac: '.$pronac.'<br> </b>' . $Mensagem;
                            $assunto = 'Proposta transformada em Projeto Cultural';
                        } else {
                            $mens    = '<b>Proposta: ' .$d->idProjeto. ' - ' .$d->NomeProjeto. '<br> Proponente: '.$d->Destinatario.'<br> </b>' . $Mensagem;
                            $assunto = 'Avaliacao da proposta';
                        }
			$perfil  = "PerfilGrupoPRONAC";

			$enviaEmail = EmailDAO::enviarEmail($email, $assunto, $mens, $perfil);

			$dados = array(
				'idProjeto'           => $idProjeto,
				'idTextoemail'        => new Zend_Db_Expr('NULL'),
				'iDAvaliacaoProposta' => new Zend_Db_Expr('NULL'),
				'DtEmail'             => new Zend_Db_Expr('getdate()'),
				'stEstado'            => 1,
				'idUsuario'           => $auth->getIdentity()->usu_codigo,
			);

			$tbHistoricoEmailDAO->inserir($dados);
		endforeach;
    }

    public function analisedocumentalAction() {

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto = ?"=>$this->idPreProjeto))->current();
        $this->view->proposta = $rsProposta;

        $tblAgente = new Agentes();
        $rsAgente = $tblAgente->buscarAgenteNome(array("a.idAgente = ?"=>$rsProposta->idAgente))->current();
        $this->view->agente = $rsAgente;

        $idPreProjeto = $this->idPreProjeto;
        $dao = new AnalisarPropostaDAO();
        $this->view->itensDocumentoPendente = AnalisarPropostaDAO::buscarDocumentoPendente($idPreProjeto);

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function buscardocumentoAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $get                  = Zend_Registry::get('get');
        $idOpcao              = $get->idOpcao;
        $idDocumento              = $get->idDocumento;
        $dao = new AnalisarPropostaDAO();

        $options                 = AnalisarPropostaDAO::buscarDocumentoOpcao($idOpcao);

        $selected = "";
        $htmlOptions = "<option value=''> - Selecione - </option>";
        foreach ($options as $option){
            $selected = "";
            if($option->codigo == $idDocumento) { $selected = "selected='selected' "; }
            $htmlOptions .= "<option value='{$option->codigo}' {$selected}>".ucfirst(strtolower(utf8_decode(htmlentities($option->descricao))))." </option>";
        }
        echo $htmlOptions;
    }

    public function inserirdocumentoAction(){

        $dao                    = new AnalisarPropostaDAO();
        $post                   = Zend_Registry::get('post');
        $dados                   = array();
        $dados['idPreProjeto']   = $this->idPreProjeto;
        $dados['CodigoDocumento']= $post->documento;
        //xd($dados);
        try{
            if($post->tipoDocumento == 1){
                AnalisarPropostaDAO::inserirDocumentoProponente($dados);
            }else{
                AnalisarPropostaDAO::inserirDocumentoProjeto($dados);
            }

            //inserir avaliacao
            $tblAvaliacao = new AvaliacaoProposta();
            $dadosAvaliacao["idProjeto"] = $this->idPreProjeto;
            $dadosAvaliacao["idTecnico"] = $this->idUsuario;
            $dadosAvaliacao["DtEnvio"] = date("Y-m-d H:i:s");
            $dadosAvaliacao["DtAvaliacao"] = date("Y-m-d H:i:s");
            $dadosAvaliacao["Avaliacao"] = "Documenta&ccedil;&atilde;o pendente";
            $dadosAvaliacao["ConformidadeOK"] = 1;
            $dadosAvaliacao["stEstado"] = 1;
            $dadosAvaliacao["idCodigoDocumentosExigidos"] = $post->documento;

            $tblAvaliacao->inserir($dadosAvaliacao);
            $where = array(
                            'CONVERT(VARCHAR,DtEnvio,103) = ?'  => new Zend_Db_Expr('CONVERT(VARCHAR,GETDATE(),103)'),
                            'idProjeto = ?'                     => $this->idPreProjeto,
                            'idCodigoDocumentosExigidos is not null'    => ''
                );
            $docs = $tblAvaliacao->buscar($where);

            if(count($docs) == 1){ //So poder enviar um email
                
                $msg = new Zend_Config_Ini(getcwd().'/public/admissibilidade/mensagens_email_proponente.ini', 'pendencia_documental');

                $this->eviarEmail($this->idPreProjeto,$msg->msg);
                
            }
            
            
             
            parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/analisedocumental?idPreProjeto=".$this->idPreProjeto, "CONFIRM");
            
//            // Retornando proposta para movimentacao 95
//            $dadosMovimentacao['idProjeto'] = $this->idPreProjeto;
//            $dadosMovimentacao['Movimentacao'] = 95;
//            $dadosMovimentacao['DtMovimentacao'] = date("Y-m-d");
//            $dadosMovimentacao['stEstado'] = 0;
//            $dadosMovimentacao['Usuario'] = $this->idUsuario;
//
//            $tblMovimentacao = new Movimentacao();
//            //Mudando as movimentacoes anteriores para o stEstado = 1
//
//            $rsRetorno = $tblMovimentacao->update(array("stEstado"=>1), "idProjeto = {$this->idPreProjeto}");
//
//            $rsMovimentacao = $tblMovimentacao->inserir($dadosMovimentacao);
            
            // Verificando se movimentacao ja existe
//            $rsBuscaMovimentacao = $tblMovimentacao->buscar(array("Movimentacao = ?"=>97, "idProjeto = ?"=>$dadosMovimentacao['idProjeto']));
//            if($rsBuscaMovimentacao->count() < 1){
//                // Salvando movimentacao
//                $rsMovimentacao = $tblMovimentacao->salvar($dadosMovimentacao);
//            }
        }catch (Exception $e){
            //xd($e->getMessage());
            parent::message("Erro ao realizar opera&ccedil;&atilde;o", "/admissibilidade/analisedocumental?idPreProjeto=".$this->idPreProjeto, "ERROR");
        }

        

    }

    public function updatedocumentoAction(){
        $dao                    = new AnalisarPropostaDAO();
        $post                   = Zend_Registry::get('post');
        $dado                   = array();
        $dado['idPreProjeto']   = $post->idprojeto;
        $dado['CodigoDocumento']= $post->documento;
        $dado['iddocantigo']    = $post->iddocantigo;

        if($post->tipoDocumento == 1){
            AnalisarPropostaDAO::updateDocumentoProponente($dado);
        }else{
            AnalisarPropostaDAO::updateDocumentoProjeto($dado);
        }

        //Enviar e-mail
        //historico do e-mail
        parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/analisedocumental?idPreProjeto=".$this->idPreProjeto, "CONFIRM");

    }

    public function deletedocumentoAction(){
        $dao                    = new AnalisarPropostaDAO();
        $get                   = Zend_Registry::get('get');


        if($get->tipoDocumento == 1){
           AnalisarPropostaDAO::deleteDocumentoProponente($get->idDocumento);
        }else{
            AnalisarPropostaDAO::deleteDocumentoProjeto($get->idDocumento);
        }

        //Enviar e-mail
        //historico do e-mail
        parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/analisedocumental?idPreProjeto=".$this->idPreProjeto, "CONFIRM");

    }

    public function despacharpropostaAction() {
        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();

        $dao = new AnalisarPropostaDAO();
        $this->view->itensDespacho  = AnalisarPropostaDAO::buscarDespacho($this->idPreProjeto);
    }

    public function transformarPropostaEmProjetoAction() {
        //verifica se id preprojeto foi enviado
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $get = Zend_Registry::get('get');

        $this->validarAcessoAdmissibilidade();
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $idOrgao = $auth->getIdentity()->usu_orgao;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto = ?"=>$this->idPreProjeto))->current();

        //O codigo deste IF serve apenas para mostrar a mensagem ao usuario
        if($get->recuperarUnidade != ""){
            //Buscando produto principal
            $tblPlanoDistribuicao = new PlanoDistribuicao();
            $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscar(array("idProjeto = ?"=>$this->idPreProjeto, "stPrincipal = ?"=>1))->current();

            $tblOrgaos = new Orgaos();
            if($rsProposta->idEdital == 0 && empty($rsProposta->idEdital)){
                //Se existe plano de distribuicao, entao pega-se o orgao baseado no produto principal
                $rsOrgaos = $tblOrgaos->buscarOrgaoPorSegmento($rsPlanoDistribuicao->Segmento)->current();
            }else{
                //Se nao existe plano de distribuicao, entao esta e uma proposta por edital,
                //entao pega-se o orgao do edital
                $tblEdital = new Edital();
                $rsEdital =  $tblEdital->buscar(array("idEdital = ?"=>$rsProposta->idEdital))->current();
                
                $rsOrgaos = $tblOrgaos->buscar(array("Codigo = ?"=>$rsEdital->idOrgao))->current();
            }
            //xd($rsOrgaos);
            $msg = "Deseja Transformar a proposta Nr. {$this->idPreProjeto}, em Projeto? <br>A mesma ser&aacute; enviada para a Unidade: {$rsOrgaos->Sigla}, para An&aacute;lise T&eacute;cnica.<br> Confirma a opera&ccedil;&atilde;o?";
            die($msg);
        }

        //Buscando produto principal
        $tblPlanoDistribuicao = new PlanoDistribuicao();
        $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscar(array("idProjeto = ?"=>$this->idPreProjeto, "stPrincipal = ?"=>1))->current();

        $tblOrgaos = new Orgaos();
        if($rsProposta->idEdital == 0 || empty($rsProposta->idEdital)){
            //Se existe plano de distribuicao, entao pega-se o orgao baseado no produto principal
            $rsOrgaos = $tblOrgaos->buscarOrgaoPorSegmento($rsPlanoDistribuicao->Segmento)->current();
            //$idOrgao = $rsOrgaos->Codigo;
            $idOrgao = $this->codOrgao;
        }else{
            //Se nao existe plano de distribuicao, entao esta e uma proposta por edital,
            //entao pega-se o orgao do edital
            $tblEdital = new Edital();
            $rsEdital =  $tblEdital->buscar(array("idEdital = ?"=>$rsProposta->idEdital))->current();

            $rsOrgaos = $tblOrgaos->buscar(array("Codigo = ?"=>$rsEdital->idOrgao))->current();
            //$idOrgao = $rsOrgaos->Codigo;
            $idOrgao = $this->codOrgao;
        }
        
        $tblAgente = new Agentes();
        $rsAgente = $tblAgente->buscarAgenteNome(array("a.idAgente = ?"=>$rsProposta->idAgente))->current();

        $cnpjcpf = $rsAgente->CNPJCPF;

        try{
            $aux = new paTransformarPropostaEmProjeto();
            $aux = $aux->execSP($this->idPreProjeto, $cnpjcpf, $idOrgao, $this->idUsuario);
            
            $tblProjeto = new Projetos();

            $rsProjeto = $tblProjeto->buscar(array("idProjeto = ?" => $this->idPreProjeto), "IdPRONAC DESC")->current();
            if(!empty($rsProjeto )){
                
                $nrPronac = $rsProjeto->AnoProjeto.$rsProjeto->Sequencial;
                
                echo "A Proposta ".$this->idPreProjeto." foi transformada no Projeto No. ".$nrPronac ;
                echo '<br><br><a href="../gerenciarparecertecnico/dadosetiqueta?pronac='.$nrPronac.'&etiqueta=nao" target="_blank">Imprimir etiqueta</a>';
            }

        } catch (Exception $e){
            echo "Erro ao tentar transformar proposta em projeto!";
        }
        
    }

    public function encaminharpropostaAction() {
        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();
        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?"=>$this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto  = isset($rsProposta->NomeProjeto) ? strip_tags($rsProposta->NomeProjeto) : '';
    }

    public function salvardespachoAction(){
        $post   = Zend_Registry::get('post');

        $dados                  = array();
        $dados['idPreProjeto']  = $post->idPreProjeto;
        $dados['idTecnico']     = $this->idUsuario;
        $dados['despacho']      = trim($post->despacho);

//        $despachoExiste = AnalisarPropostaDAO::verificarDespacho($post->idPreProjeto);

//        if(count($despachoExiste)>0){
//            AnalisarPropostaDAO::updateEstadoDespacho($post->idPreProjeto);
//        }
        AnalisarPropostaDAO::inserirDespacho($dados);

        //if($despachoExiste[0]->Tipo == 129 ){
//        $movimentacaoExiste = AnalisarPropostaDAO::verificarMovimentcaoDespacho($post->idPreProjeto);

//        if(count($movimentacaoExiste)>0 && $movimentacaoExiste[0]->Movimentacao == 127)
//        {
//            $dados['movimentacao'] = 128;
//
//            //APAGA DOCUMENTOS PENDENTES CONFORME REGRA DA TRIGGER
//            AnalisarPropostaDAO::deleteDocumentoProponentePeloProjeto($post->idPreProjeto);
//            AnalisarPropostaDAO::deleteDocumentoProjetoPeloProjeto($post->idPreProjeto);
//
//        }else{
//            $dados['movimentacao'] = 127;
//        }
        //xd($dados);
//        AnalisarPropostaDAO::updateEstadoMovimentacao($post->idPreProjeto);
//        AnalisarPropostaDAO::inserirMovimentacao($dados);
        //}

        //Envia Email
//        $msg = new Zend_Config_Ini(getcwd().'/public/admissibilidade/mensagens_email_proponente.ini', 'sem_pendencia_documental');

//        $this->eviarEmail($this->idPreProjeto,$msg->msg);
        
        if(isset($post->devolver) && $post->devolver == 1){
            
            parent::message("Mensagem enviada com sucesso!", "/admissibilidade/gerenciamentodepropostas", "CONFIRM");
            return true;
            exit();
        }else{

            parent::message("Despacho encaminhado com sucesso!", "/admissibilidade/listar-propostas", "CONFIRM");
            return;
        }

    }


    public function arquivarpropostaAction() {
        $dao = new AnalisarPropostaDAO();
        try{
            //Enviar e-mail informando arquivamento e a justificativa
            //$dao->deletePreProjeto($this->idPreProjeto);
            $tblPreProjeto = new PreProjeto();
            $rsPreProjeto = $tblPreProjeto->find($this->idPreProjeto)->current();
            $rsPreProjeto->DtArquivamento=date("Y/m/d H:i:s");
            $rsPreProjeto->stEstado = 0;
            $rsPreProjeto->save();
            
            //Enviar e-mail informando arquivamento e a justificativa
            $tipo = ($rsPreProjeto->idEdital) ? 'arquivamento_proposta_Edital' : 'arquivamento_proposta_IF';
            $msg = new Zend_Config_Ini(getcwd().'/public/admissibilidade/mensagens_email_proponente.ini', $tipo);

            $this->eviarEmail($this->idPreProjeto,$msg->msg);
            
            parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/listar-propostas", "CONFIRM");
            return;
            die();
        }catch (Exception $e){
            parent::message("Erro ao realizar opera&ccedil;&atilde;o!", "/admissibilidade/listar-propostas", "ERROR");
        }
    }


    public function confirmararquivarpropostaAction() {

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?"=>$this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto  = strip_tags($rsProposta->NomeProjeto);
        
    }
    
    public function arquivarAction(){
        $dao    = new AnalisarPropostaDAO();
        $post   = Zend_Registry::get('post');
        //xd($post);
        AnalisarPropostaDAO::deletePreProjeto($post->idprojeto);
        ///Enviar e-mail informando arquivamento e a justificativa
        
        
        


    }
    
    public function imprimirpropostaculturalAction() {
        
        $this->_helper->layout->disableLayout();
        
        $idPreProjeto = $this->idPreProjeto;
        $dao = new AnalisarPropostaDAO();
        $this->view->itensGeral = AnalisarPropostaDAO::buscarGeral($idPreProjeto);
        $propostaPorEdital = false;
        if($this->view->itensGeral[0]->idEdital && $this->view->itensGeral[0]->idEdital != 0){
            $propostaPorEdital = true;
        }
        $this->view->itensTelefone              = AnalisarPropostaDAO::buscarTelefone($this->view->itensGeral[0]->idAgente);
        $this->view->itensPlanosDistribuicao    = AnalisarPropostaDAO::buscarPlanoDeDistribucaoProduto($idPreProjeto);

        $this->view->itensFonteRecurso          = AnalisarPropostaDAO::buscarFonteDeRecurso($idPreProjeto);
        $this->view->itensLocalRealiazacao      = AnalisarPropostaDAO::buscarLocalDeRealizacao($idPreProjeto);
        $this->view->itensDeslocamento          = AnalisarPropostaDAO::buscarDeslocamento($idPreProjeto);
        $this->view->itensPlanoDivulgacao       = AnalisarPropostaDAO::buscarPlanoDeDivulgacao($idPreProjeto);

        $tblAvaliacaoProposta = new AvaliacaoProposta();
        $rsAvaliacaoProposta = $tblAvaliacaoProposta->buscar(array("idProjeto = ?"=>$idPreProjeto, "idArquivo ?"=>new Zend_Db_Expr("IS NOT NULL")));
        $tbArquivo = new tbArquivo();
        $arrDadosArquivo = array();
        $arrRelacionamentoAvaliacaoDocumentosExigidos = array();
        if(count($rsAvaliacaoProposta) > 0){
            foreach($rsAvaliacaoProposta as $avaliacao){
                $arrDadosArquivo[$avaliacao->idArquivo] = $tbArquivo->buscar(array("idArquivo = ?"=>$avaliacao->idArquivo));
                $arrRelacionamentoAvaliacaoDocumentosExigidos[$avaliacao->idArquivo] = $avaliacao->idCodigoDocumentosExigidos;
            }
        }
        $this->view->relacionamentoAvaliacaoDocumentosExigidos = $arrRelacionamentoAvaliacaoDocumentosExigidos;
        $this->view->itensDocumentoPreProjeto   = $arrDadosArquivo;

        //PEGANDO RELACAO DE DOCUMENTOS EXIGIDOS(GERAL, OU SEJA, TODO MUNDO)
        $tblDocumentosExigidos = new DocumentosExigidos();
        $rsDocumentosExigidos = $tblDocumentosExigidos->buscar()->toArray();
        $arrDocumentosExigidos = array();
        foreach($rsDocumentosExigidos as $documentoExigido){
            $arrDocumentosExigidos[$documentoExigido["Codigo"]] = $documentoExigido;
        }
        $this->view->documentosExigidos = $arrDocumentosExigidos;

        $this->view->itensHistorico             = AnalisarPropostaDAO::buscarHistorico($idPreProjeto);

        /*
         * PEGANDO DOCUMENTOS ANEXADOS
         */
        $tblAvaliacaoProposta = new AvaliacaoProposta();
        $rsAvaliacaoProposta = $tblAvaliacaoProposta->buscar(array("idProjeto = ?"=>$idPreProjeto, "idArquivo ?"=>new Zend_Db_Expr("IS NOT NULL")));
        $tbArquivo = new tbArquivo();
        $arrDadosArquivo = array();
        $arrRelacionamentoAvaliacaoDocumentosExigidos = array();
        if(count($rsAvaliacaoProposta) > 0){
            foreach($rsAvaliacaoProposta as $avaliacao){
                $arrDadosArquivo[$avaliacao->idArquivo] = $tbArquivo->buscar(array("idArquivo = ?"=>$avaliacao->idArquivo));
                $arrRelacionamentoAvaliacaoDocumentosExigidos[$avaliacao->idArquivo] = $avaliacao->idCodigoDocumentosExigidos;
            }
        }
        //x($arrRelacionamentoAvaliacaoDocumentosExigidos);
        $this->view->relacionamentoAvaliacaoDocumentosExigidos = $arrRelacionamentoAvaliacaoDocumentosExigidos;
        $this->view->itensDocumentoPreProjeto   = $arrDadosArquivo;

        //PEGANDO RELACAO DE DOCUMENTOS EXIGIDOS(GERAL, OU SEJA, TODO MUNDO)
        $tblDocumentosExigidos = new DocumentosExigidos();
        $rsDocumentosExigidos = $tblDocumentosExigidos->buscar()->toArray();
        $arrDocumentosExigidos = array();
        foreach($rsDocumentosExigidos as $documentoExigido){
            $arrDocumentosExigidos[$documentoExigido["Codigo"]] = $documentoExigido;
        }
        //xd($arrDocumentosExigidos);
        $this->view->documentosExigidos = $arrDocumentosExigidos;
        //xd($rsDocumentosExigidos);
        /*
         * FINAL - PEGANDO DOCUMENTOS ANEXADOS
         */

        $this->view->itensPlanilhaOrcamentaria  = $dao->buscarPlanilhaOrcamentaria($idPreProjeto);

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasProdutos($this->idPreProjeto);
        $this->view->Etapa = $buscarEtapa;

        $buscarItem = ManterorcamentoDAO::buscarItensProdutos($this->idPreProjeto);
        $this->view->Item = $buscarItem;

        $this->view->AnaliseCustos = PreProjeto::analiseDeCustos($this->idPreProjeto);
        $this->view->idPreProjeto = $this->idPreProjeto;

        //========== inicio codigo dirigente ================
        /*==================================================*/
        $arrMandatos = array();
        $this->view->mandatos = $arrMandatos;
        $preProjeto = new PreProjeto();
        $rsDirigentes = array();
        
        $Empresa = $preProjeto->buscar(array('idPreProjeto = ?' => $this->idPreProjeto))->current();
        $idEmpresa = $Empresa->idAgente;
        
        if(isset($this->view->itensGeral[0]->CNPJCPFdigirente) && $this->view->itensGeral[0]->CNPJCPFdigirente != "") {
            $tblAgente = new Agentes();
            $tblNomes = new Nomes();
            foreach ($this->view->itensGeral as $v) {
                $rsAgente = $tblAgente->buscarAgenteNome(array('CNPJCPF=?'=>$v->CNPJCPFdigirente))->current();
                $rsDirigentes[$rsAgente->idAgente]['CNPJCPFDirigente'] = $rsAgente->CNPJCPF;
                $rsDirigentes[$rsAgente->idAgente]['idAgente'] = $rsAgente->idAgente;
                $rsDirigentes[$rsAgente->idAgente]['NomeDirigente'] = $rsAgente->Descricao;
            }

            $tbDirigenteMandato = new tbAgentesxVerificacao();
            foreach($rsDirigentes as $dirigente) {
                $rsMandato = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idEmpresa, 'idDirigente = ?' => $dirigente['idAgente'],'stMandato = ?' => 0));
                $NomeDirigente = $dirigente['NomeDirigente'];
                $arrMandatos[$NomeDirigente] = $rsMandato;
            }
        }
        
        //$tbDirigentes = $geral->buscarDirigentes($idPronac);
        $this->view->dirigentes = $rsDirigentes;        
        $this->view->mandatos   = $arrMandatos;
        //============== fim codigo dirigente ================
        /*==================================================*/
        

        if($propostaPorEdital){
            $tbFormDocumentoDAO =   new tbFormDocumento();
            $edital             =   $tbFormDocumentoDAO->buscar(array('idEdital = ?'=>$this->view->itensGeral[0]->idEdital,'idClassificaDocumento = ?'=>$this->COD_CLASSIFICACAO_DOCUMENTO));
            $arrPerguntas = array();
            $arrRespostas = array();
            $tbPerguntaDAO  =   new tbPergunta();
            $tbRespostaDAO  =   new tbResposta();
            foreach($edital as $registro){

                $questoes       =   $tbPerguntaDAO->montarQuestionario($registro["nrFormDocumento"],$registro["nrVersaoDocumento"]);
                $questionario   =   '';
                if(is_object($questoes) and count($questoes) > 0){
                    foreach ($questoes as $questao){
                        $resposta = '';
                        $where = array(
                            'nrFormDocumento = ?'       =>$registro["nrFormDocumento"]
                            ,'nrVersaoDocumento = ?'    =>$registro["nrVersaoDocumento"]
                            ,'nrPergunta = ?'           =>$questao->nrPergunta
                            ,'idProjeto = ?'            =>$this->idPreProjeto
                        );
                        $resposta = $tbRespostaDAO->buscar($where);

                        $arrPerguntas[$registro["nrFormDocumento"]]["titulo"] = $registro["nmFormDocumento"];
                        $arrPerguntas[$registro["nrFormDocumento"]]["pergunta"][] = $questao->toArray();
                        $arrRespostas[] = $resposta->toArray();

                    }
                }
            }
                        
            $this->view->perguntas = $arrPerguntas;
            $this->view->respostas = $arrRespostas;            

            $this->montaTela("admissibilidade/imprimir-proposta-por-edital.phtml");
        }else{
            $this->montaTela("admissibilidade/imprimir-proposta-por-incentivo-fiscal.phtml");
        }        
        

    }


    public function gerarpdfAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        //$post = Zend_Registry::get('post');
        
        $pdf = new PDFCreator($_POST['html']);
        if (isset($_GET['quebra_linha'])) {
        	$pdf->gerarPdf($_GET['quebra_linha']);
        } else {
        	$pdf->gerarPdf();
        }

    }

    public function listaranalisevisualtecnicoAction() {

    }

    public function consultarhistoricoanalisevisualAction() {

    }

    public function imprimiretiquetaprojetoAction() {

    }

    public function imprimiretiquetaprojetoconsultaAction() {

    }

    public function alterarunianalisepropostaconsultaAction() {

    }

    public function frmalterarunianalisepropostaAction() {
        $this->_helper->layout->disableLayout();
        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->unidadeAnaliseProposta($_POST["nrProposta"])->current();
        //xd($rsProposta->toArray());
        if($rsProposta){
            $rsOrgaoSecretaria = $tblProposta->orgaoSecretaria($rsProposta->idTecnico);
        }else{
            echo "<font color='black' size='2'><b>Nenhum registro encontrado</b></font>";
            die;
        }
        //xd($rsOrgaoSecretaria);

        $this->view->orgaoUsuarioLogado = $this->codOrgaoSuperior;
        $this->view->proposta = $rsProposta;
        $this->view->orgaoSecretaria = $rsOrgaoSecretaria;
    }

    public function alterarunianalisepropostaAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        try{
            $tblTbAvaliacaoProposta = new AvaliacaoProposta();
            $rsAvaliacaoProposta = $tblTbAvaliacaoProposta->find($_POST["idAvaliacaoProposta"])->current();

            $params            = new stdClass();
            $params->usu_cod   = $rsAvaliacaoProposta->idTecnico;
            $params->idProjeto = $rsAvaliacaoProposta->idProjeto;
            AdmissibilidadeDAO::redistribuirAnalise($params);

            //parent::message("Localiza&ccedil;&atilde;o alterada com sucesso", "/admissibilidade/alterarunianalisepropostaconsulta", "CONFIRM");
            echo "<font color='green' size='2'><b>Localiza&ccedil;&atilde;o alterada com sucesso</b></font>";
            die;
        }catch(Exception $e){
            //parent::message("Falha ao realizar opera&ccedil;&atilde;o", "/admissibilidade/alterarunianalisepropostaconsulta", "CONFIRM");
            echo "<font color='red' size='2'><b>Falha ao realizar opera&ccedil;&atilde;o</b></font>";
            die;
        }
    }

    public function redistribuiranaliseAction() {
        if ($this->codOrgaoSuperior) {
            $params                = new stdClass();
            $params->usu_orgao     = $this->codOrgaoSuperior;
            $analistas             = AdmissibilidadeDAO::consultarRedistribuirAnalise($params);
            $this->view->analistas = array();
            $this->view->urlResumo = $this->_urlPadrao."/admissibilidade/resumo-distribuicao-propostas";
            $i = 0;
            foreach($analistas as $analista) {
                $this->view->analistas[$analista->Tecnico][$analista->Fase][$i]['nrProposta']     = $analista->idProjeto;
                $this->view->analistas[$analista->Tecnico][$analista->Fase][$i]['NomeProjeto']    = $analista->NomeProjeto;
                $this->view->analistas[$analista->Tecnico][$analista->Fase][$i]['DtMovimentacao'] = ConverteData($analista->DtMovimentacao, 5);;
                $this->view->analistas[$analista->Tecnico][$analista->Fase][$i]['fase']           = $analista->Fase;
                $i++;
            }
        }
    }

    public function redistribuiranaliseitemAction() {
        if($_REQUEST['idProjeto'] && isset($_REQUEST['usu_cod'])) {
            $params            = new stdClass();
            $params->usu_cod   = $_REQUEST['usu_cod'];
            $params->idProjeto = $_REQUEST['idProjeto'];
            AdmissibilidadeDAO::redistribuirAnalise($params);
            //$this->view->mensagem = 'Alteração realizada com sucesso.';
            parent::message("An&aacute;lise redistribu&iacute;da com sucesso.", "/admissibilidade/redistribuiranalise", "CONFIRM");
        }
        if($_REQUEST['idProjeto'] && $_REQUEST['fase']) { 
            $params = new stdClass();
            $params->idProjeto    = $_REQUEST['idProjeto'];
            $params->fase         = $_REQUEST['fase'];
            $this->view->analista = AdmissibilidadeDAO::consultarRedistribuirAnaliseItem($params); 
            if($this->view->analista) {
                $params = new stdClass();
                $params->usu_nome   = $this->view->analista->Tecnico;
                $params->gru_codigo = $_SESSION['GrupoAtivo']['codOrgao'];
                //$params->usu_orgao  = $this->codOrgaoSuperior;
                $params->usu_orgao  = $this->codOrgao;
                $this->view->novosAnalistas = AdmissibilidadeDAO::consultarRedistribuirAnaliseItemSelect($params);
            }
        }
    }

    public function gerenciaranalistasAction() {
		
        if ($this->codOrgao) {
            $params = new stdClass();            
            $params->cod_grupo = $this->codGrupo;
            $params->cod_orgao = $this->codOrgao;
            $this->view->analistas = AdmissibilidadeDAO::gerenciarAnalistas($params);
        }
    }

    public function gerenciaranalistaAction() {
        if($_REQUEST['usu_cod'] && $_REQUEST['usu_orgao'] && $_REQUEST['gru_codigo'] && isset($_REQUEST['status'])) {
            $params = new stdClass();
            $params->uog_status   = $_REQUEST['status'];
            $params->usu_cod      = $_REQUEST['usu_cod'];
            $params->gru_codigo   = $_REQUEST['gru_codigo'];
            $params->usu_orgao    = $_REQUEST['usu_orgao'];

            $msgComplementar = "Altera&ccedil;&atilde;o realizada com sucesso!";
            
            if((int)$params->uog_status === 0){
                $tblPreProjeto = new PreProjeto();
                $tecnicoTemProposta = $tblPreProjeto->tecnicoTemProposta($params->usu_cod);

                if($tecnicoTemProposta){
                    $msgComplementar = "O Analista foi desabilitado, por&eacute;m existem Propostas distribu&iacute;das para o mesmo!";
                }
            }
            
            $atualizar = AdmissibilidadeDAO::atualizarAnalista($params);
			parent::message($msgComplementar, "/admissibilidade/gerenciaranalistas", "CONFIRM");
        }
        
        if($_REQUEST['usu_cod'] && $_REQUEST['usu_orgao'] && $_REQUEST['gru_codigo']) {
            $params               = new stdClass();
            $params->usu_cod      = $_REQUEST['usu_cod'];
            $params->usu_orgao    = $_REQUEST['usu_orgao'];
            $params->gru_codigo   = $_REQUEST['gru_codigo'];
            $this->view->analista = AdmissibilidadeDAO::gerenciarAnalista($params);
        }
    }

    public function gerenciamentodepropostasAction() {

        //cod_grupo = 131 se perfil do tipo coordenador.
        //if($_SESSION['GrupoAtivo']['codOrgao'] != 160 && $_SESSION['GrupoAtivo']['codOrgao'] != 251) {
        if($_SESSION['GrupoAtivo']['codGrupo'] != 131) {

            $this->view->mensagem = "Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar essa funcionalidade.";

        } else {

            $post   = Zend_Registry::get('post');
            $numeroProposta      = $post->numeroProposta;
            $tipoNome            = $post->tiponome;
            $nomeProposta        = $post->nomeProposta;
            $tipoCpf             = $post->tipocpf;
            $cpfCnpj             = retiraMascara($post->cpfCnpj);
            $analista            = $post->analista;
            $tipodata            = $post->tipodata;
            $dataPropostaInicial = $post->dataPropostaInicial;

            $arrBusca = array();
            //NUM. PROPOSTA
            if(!empty($numeroProposta)){
                $arrBusca[" idPreProjeto = "]="'".$numeroProposta."'";
            }
            //NOME DA PROPOSTA
            if(!empty($nomeProposta)){
                if($tipoNome=="contendo") {
                    $arrBusca[" NomeProjeto LIKE "]="'%".$nomeProposta."%'";
                }elseif($tipoNome=="inicioIgual") {
                    $arrBusca[" NomeProjeto LIKE "]="'".$nomeProposta."%'";
                }
            }
            //CPF / CNPJ PROPONENTE
            if(!empty($cpfCnpj)){
                 if($tipoCpf=="contendo") {
                    $arrBusca[" CNPJCPF LIKE "]="'%".$cpfCnpj."%'";
                }elseif($tipoCpf=="igual") {
                    $arrBusca[" CNPJCPF = "]="'".$cpfCnpj."'";;
                }elseif($tipoCpf=="inicioIgual") {
                    $arrBusca[" CNPJCPF LIKE "]="'".$cpfCnpj."%'";;
                }elseif($tipoCpf=="diferente") {
                    $arrBusca[" CNPJCPF <> "]="'".$cpfCnpj."'";;
                }
            }
            //ANALISTA
            if(!empty($analista)){
                $arrBusca[" idTecnico = "]="'".$analista."'";
            }elseif($analista == "0"){
                $arrBusca[" idTecnico <> "]="''";
            }

            if(!empty ($dataPropostaInicial) || $tipodata != ''){
                if($tipodata == "igual"){
                    $arrBusca['x.DtAvaliacao > '] = "'".ConverteData($post->dataPropostaInicial, 13)." 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'".ConverteData($post->dataPropostaInicial, 13)." 23:59:59'";

                }elseif($tipodata == "maior"){
                    $arrBusca['x.DtAvaliacao >= '] = "'".ConverteData($post->dataPropostaInicial, 13)." 00:00:00'";

                }elseif($tipodata == "menor"){
                    $arrBusca['x.DtAvaliacao <= '] = "'".ConverteData($post->dataPropostaInicial, 13)." 00:00:00'";

                }elseif($tipodata == "OT"){
                    $arrBusca['x.DtAvaliacao = '] = "'".date("Y-m-").(date("d")-1)." 00:00:00'";

                }elseif($tipodata == "U7"){
                    $arrBusca['x.DtAvaliacao > '] = "'".date("Y-m-").(date("d")-7)." 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'".date("Y-m-d")." 23:59:59'";

                }elseif($tipodata == "SP"){
                    $arrBusca['x.DtAvaliacao > '] = "'".date("Y-m-").(date("d")-7)." 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'".date("Y-m-d")." 23:59:59'";

                }elseif($tipodata == "MM"){
                    $arrBusca['x.DtAvaliacao > '] = "'".date("Y-m-01")." 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'".date("Y-m-d")." 23:59:59'";

                }elseif($tipodata == "UM"){
                    $arrBusca['x.DtAvaliacao > '] = "'".date("Y-").(date("m")-1)."-01 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'".date("Y-").(date("m")-1)."-31 23:59:59'";

                }else{
                    $arrBusca['x.DtAvaliacao > '] = "'".ConverteData($post->dataPropostaInicial, 13)." 00:00:00'";

                    if($post->dataPropostaFinal != ""){
                        $arrBusca['x.DtAvaliacao < '] = "'".ConverteData($post->dataPropostaFinal, 13)." 23:59:59'";
                    }
                }

            }
            //ORGAO USUARIO SUPERIOR LOGADO
            //$arrBusca[" SAC.dbo.fnIdOrgaoSuperiorAnalista(x.idTecnico) = "] = $_SESSION['GrupoAtivo']['codOrgao'];
            $arrBusca[" SAC.dbo.fnIdOrgaoSuperiorAnalista(x.idTecnico) = "] = $this->codOrgaoSuperior;
            //$arrBusca[" TABELAS.dbo.fnCodigoOrgaoEstrutura(u.usu_orgao, 1) = "] = $this->codOrgaoSuperior;


            $this->view->analistas = AdmissibilidadeDAO::consultarGerenciamentoProposta($arrBusca,array("Tecnico ASC"));

            if(!$this->view->analistas) {
                $this->view->mensagem = 'Nenhum registro encontrado';
            } else {
                $array = array();
                foreach($this->view->analistas as $analistas) {
                    
                    $array[$analistas->Tecnico][$analistas->idProjeto]['NomeProposta']        = $analistas->NomeProposta;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idAgente']            = $analistas->idAgente;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['CNPJCPF']             = $analistas->CNPJCPF;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idUsuario']           = $analistas->idUsuario;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['Tecnico']             = $analistas->Tecnico;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idSecretaria']        = $analistas->idSecretaria;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['DtAdmissibilidade']   = ConverteData($analistas->DtAdmissibilidade, 5);
                    $array[$analistas->Tecnico][$analistas->idProjeto]['dias']                = $analistas->dias;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idAvaliacaoProposta'] = $analistas->idAvaliacaoProposta;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idMovimentacao']      = $analistas->idMovimentacao;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['stTipoDemanda']       = $analistas->stTipoDemanda;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['stPlanoAnual']        = $analistas->stPlanoAnual;
                }
                $this->view->analistas = $array;

            }

            $this->view->urlResumo = $this->_urlPadrao."/admissibilidade/resumo-gerenciamento-proposta";
        }
    }

    public function devolverpropostaAction() {

        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();
        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?"=>$this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto  = strip_tags($rsProposta->NomeProjeto);

    }

    public function propostaPorProponenteAction(){
        $get = Zend_Registry::get("get");
        $idAgente = $get->agente;

        $tblProposta = new Proposta();
        $rsPropostas = $tblProposta->buscar(array("idagente = ?"=>$idAgente), array("nomeprojeto ASC"));


        //Descobrindo os dados do Agente/Proponente
        $tblAgente = new Nomes();
        $rsAgente = $tblAgente->buscar(array("idAgente = ? "=>$idAgente))->current();

        //Descobrindo a movimentação corrente de cada proposta
        if(count($rsPropostas)>0){
            //Conectando com movimentacao
            $tblMovimentacao = new Movimentacao();
            //Conectando com projetos
            $tblProjetos = new Projetos();
            $tbAvaliacao = new AvaliacaoProposta();
            $tblUsuario = new Usuario();

            $movimentacoes = array();
            foreach ($rsPropostas as $proposta){
                //Buscando movimentação desta proposta
                $rsMovimentacao = $tblMovimentacao->buscar(array("idprojeto = ?"=>$proposta->idPreProjeto, "stestado = ?"=>0))->current();
                $movimentacoes[$proposta->idPreProjeto]["tecnico"] = "";
                
                if(count($rsMovimentacao)){
                    //Descobrindo se esta proposta ja existe em projetos
                    $rsProjeto = $tblProjetos->buscar(array("idprojeto = ?"=>$proposta->idPreProjeto));

                    //Descobrindo tecnico
                    $tecnico = $tblProposta->buscarConformidadeVisualTecnico($proposta->idPreProjeto);

                    $movimentacoes[$proposta->idPreProjeto]["codMovimentacao"] = $rsMovimentacao->Movimentacao;

                    if ($rsMovimentacao->Movimentacao == 95)
                    {
                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#0000FF>Proposta com Proponente</font>";
                    }elseif ($rsMovimentacao->Movimentacao == 96)
                    {
                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta em Análise' . "</font>";
                        
                        $rsAvaliacao = $tbAvaliacao->buscar(array("idProjeto = ?"=>$proposta->idPreProjeto, "ConformidadeOK =?"=> 9, "stEstado =?"=>0))->current();

                        if(count($rsAvaliacao)>0){
                            $rsUsuario = $tblUsuario->find($rsAvaliacao->idTecnico)->current();
                            
                            if(count($rsUsuario)>0){
                                $usuarioNome = $rsUsuario->usu_nome;
                                $movimentacoes[$proposta->idPreProjeto]["tecnico"] = $usuarioNome;
                            }
                        }
                        //$movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#0000FF>Proposta com Proponente</font>";
                        /*if (!count($tecnico)>0)
                        {
                            $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta em Análise' . "</font>";
                        }*/
                    }
                    elseif ($rsMovimentacao->Movimentacao == 97 and (!count($rsProjeto)>0))
                    {
                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta aguardando documentos' . "</font>";
                    }
                    elseif (count($rsProjeto)>0)
                    {
                        $rsAvaliacao = $tbAvaliacao->buscar(array("idProjeto = ?"=>$proposta->idPreProjeto, "ConformidadeOK =?"=> 1, "stEstado =?"=>0))->current();
                        $rsUsuario = $tblUsuario->find($rsAvaliacao->idTecnico)->current();

                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta transformada em projeto' . "</font>";
                        if(count($rsUsuario)>0){
                            $movimentacoes[$proposta->idPreProjeto]["tecnico"] = $rsUsuario->usu_nome;
                        }
                    }
                    else
                    {
                        $usuarioNome = "";
                        $tipoUsuario = "";
                        $rsUsuario = null;
                        
                        /*$rsUsuario = $tblUsuario->find($rsMovimentacao->Usuario)->current();
                        // Verificando se usuario e um coordenador
                        if(!empty($rsUsuario)>0){
                            if($tblUsuario->ECoordenador($rsUsuario->usu_codigo)){
                                $tipoUsuario = "Coordenador";
                            }else{
                                $tipoUsuario = "Analista";
                            }
                            $usuarioNome = $rsUsuario->usu_nome;
                        }*/
                        
                        $rsAvaliacao = $tbAvaliacao->buscar(array("idProjeto = ?"=>$proposta->idPreProjeto, "ConformidadeOK =?"=> 1, "stEstado =?"=>0))->current();
                        if($rsAvaliacao){
                            $rsUsuario = $tblUsuario->find($rsAvaliacao->idTecnico)->current();
                        }

                        if($rsMovimentacao->Movimentacao == 127){
                            $tipoUsuario = "Coordenador";
                        }else{
                            $tipoUsuario = "Analista";
                        }

                        if(count($rsUsuario)>0){
                            $usuarioNome = $rsUsuario->usu_nome;
                        }

                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "Proposta com o {$tipoUsuario}";
                        $movimentacoes[$proposta->idPreProjeto]["tecnico"] = $usuarioNome;
                    }
                }else{
                    $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "";
                }
            }
        }

        $arrDados = array(
                        "propostas"=>$rsPropostas,
                        "agente"=>$rsAgente,
                        "movimentacoes"=>$movimentacoes
                    );

        $this->montaTela("admissibilidade/listarpropostasproponente.phtml", $arrDados);
    }

    public function listarPropostasAnaliseVisualTecnicoAction(){
        $post   = Zend_Registry::get('post');
        $orgSuperior = $this->codOrgaoSuperior;
        $view = $this->getRequest()->getParam("view");

        if(!empty($view)){
            $arrBusca = array("Tecnico "=>"IS NOT NULL");
        }else{
            $arrBusca = array("idOrgao = "=>$orgSuperior);
        }
        if(is_numeric($post->avaliacao)){ $arrBusca["ConformidadeOK = "] = "'$post->avaliacao'"; }
        if(!empty($post->tecnico)){ $arrBusca["Tecnico = "] = "'$post->tecnico'"; }

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseVisualTecnico($arrBusca, array("Tecnico ASC"));
        //xd($rsProposta);
        $arrTecnicosPropostasReavaliacao = array();
        $arrTecnicosPropostasInicial = array();

        $arrTecnicos = array();
        foreach($rsProposta as $proposta){

            if($proposta->ConformidadeOK == "0"){
                $arrTecnicosPropostasReavaliacao[$proposta->Tecnico][] = $proposta;
            }
            if($proposta->ConformidadeOK == "9"){
                $arrTecnicosPropostasInicial[$proposta->Tecnico][] = $proposta;
            }
            //$arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $arrDados = array(
                        "propostas"=>$rsProposta,
                        //"tecnicosPropostas"=>$arrTecnicosPropostas,
                        "tecnicosPropostasReavaliacao"=>$arrTecnicosPropostasReavaliacao,
                        "tecnicosPropostasInicial"=>$arrTecnicosPropostasInicial,
                        "urlXLS"=>$this->view->baseUrl()."/admissibilidade/xls-propostas-analise-visual-tecnico",
                        "urlPDF"=>$this->view->baseUrl()."/admissibilidade/pdf-propostas-analise-visual-tecnico",
                        "urlResumo"=>$this->view->baseUrl()."/admissibilidade/resumo-propostas-analise-visual-tecnico"
                    );

        if(!empty($view)){
            header("Content-Type: text/html; charset=ISO-8859-1");
            $this->_helper->layout->disableLayout();
            
            $this->montaTela($this->getRequest()->getParam("view"), $arrDados);
        }else{
            $this->montaTela("admissibilidade/listarpropostasanalisevisualtecnico.phtml", $arrDados);
        }
    }

    public function listarPropostasAnaliseDocumentalTecnicoAction(){
        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseDocumentalTecnico(array("sac.dbo.fnIdOrgaoSuperiorAnalista(a.idTecnico) = "=>$usuario, "ConformidadeOK = "=>1), array("Tecnico ASC"));

        $arrTecnicosPropostas = array();
        $idDoc = 0;
        $nomeTec = "";
        foreach($rsProposta as $proposta){
            if($proposta->CodigoDocumento != $idDoc || $proposta->Tecnico != $nomeTec){
                $arrTecnicosPropostas[$proposta->Tecnico][$proposta->NomeProjeto][] = $proposta;
                $idDoc = $proposta->CodigoDocumento;
                $nomeTec = $proposta->Tecnico;
            }
        }
        
        $arrDados = array(
                        "propostas"=>$rsProposta,
                        "tecnicosPropostas"=>$arrTecnicosPropostas,
                        "urlXLS"=>$this->view->baseUrl()."/admissibilidade/xls-propostas-analise-visual-tecnico",
                        "urlPDF"=>$this->view->baseUrl()."/admissibilidade/pdf-propostas-analise-visual-tecnico"
                    );

        $this->montaTela("admissibilidade/listarpropostasanalisedocumentaltecnico.phtml", $arrDados);
    }

    public function listarPropostasAnaliseFinalAction(){
        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseFinal(array("idOrgao = "=>$usuario), array("Tecnico ASC"));

        $arrTecnicosPropostas = array();
        foreach($rsProposta as $proposta){
            $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $arrDados = array(
                        "propostas"=>$rsProposta,
                        "tecnicosPropostas"=>$arrTecnicosPropostas,
                        "urlXLS"=>$this->view->baseUrl()."/admissibilidade/xls-propostas-analise-final",
                        "urlPDF"=>$this->view->baseUrl()."/admissibilidade/pdf-propostas-analise-final",
                        "urlResumo"=>$this->_urlPadrao."/admissibilidade/resumo-proposta-analise-final"
                    );

        $this->montaTela("admissibilidade/listarpropostasanalisefinal.phtml", $arrDados);
    }

    public function xlsPropostasAnaliseFinalAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseFinal(array("idOrgao = "=>$usuario), array("Tecnico ASC"));

        $html = "<table>
                <tr>
                    <td>Nr. Proposta</td>
                    <td>Nome da Proposta</td>
                    <td>Dt.Movimentação</td>
                </tr>
                ";
        foreach($rsProposta as $proposta){
           $html .= "<tr><td>{$proposta->idPreProjeto}</td>" ;
           $html .= "<td>{$proposta->NomeProjeto}</td>" ;
           $html .= "<td>{$proposta->DtMovimentacao}</td></tr>" ;
        }
        $html .= "</table>" ;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: inline; filename=file.xls;");
        echo $html;
    }

    public function xlsPropostasAnaliseVisualTecnicoAction(){
        set_time_limit(320);

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseVisualTecnico(array("idOrgao = "=>$usuario), array("Tecnico ASC"));

        $html = "<table>
                <tr>
                    <td>Nr. Proposta</td>
                    <td>Nome da Proposta</td>
                    <td>Dt.Movimentação</td>
                </tr>
                ";
        foreach($rsProposta as $proposta){
           $html .= "<tr><td>{$proposta->idProjeto}</td>" ;
           $html .= "<td>{$proposta->NomeProjeto}</td>" ;
           $html .= "<td>{$proposta->DtMovimentacao}</td></tr>" ;
        }
        $html .= "</table>" ;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: inline; filename=file.xls;");
        echo $html;
    }

    public function pdfPropostasAnaliseFinalAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseFinal(array("idOrgao = "=>$usuario), array("Tecnico ASC"));

        $arrTecnicos = array();
        foreach($rsProposta as $proposta) {
            $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $html = '
                <table width="100%">
                    <tr>
                        <th style="font-size:36px;">
                            Proposta em análise final
                        </th>
                    </tr>
                ';
        $ultimoRegistro = null;
        $ct = 1;
        if(!empty($arrTecnicosPropostas)) {
            foreach($arrTecnicosPropostas as $tecnico=>$propostas) {
                $html .= '
                    <tr>
                        <td align="left" style="border:1px #000000 solid; font-size:14px; font-weight:bold;">
                            Analista : '.$tecnico.'
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" cellspacing="2" style="border:1px #000000 solid;">
                                <tr>
                                    <th width="15%" style="border-bottom:1px #000000 solid;">Nr. Proposta</th>
                                    <th width="65%" style="border-bottom:1px #000000 solid;">Nome da Proposta</th>
                                    <th width="20%" style="border-bottom:1px #000000 solid;">Dt. Movimentação</th>
                                </tr>
                ';
                                foreach($propostas as $proposta){
                $html .= '
                                <tr>
                                    <td align="center" style="font-size:12px;">'.$proposta->idPreProjeto.'</td>
                                    <td align="center" style="font-size:12px;">'.$proposta->NomeProjeto.'</td>
                                    <td align="center" style="font-size:12px;">'.ConverteData($proposta->DtMovimentacao,5).'</td>
                                </tr>
                ';
                                }
                $html .= '
                            </table>
                        </td>
                    </tr>
                ';
                $ct++;
            }
        }

        $html .= '
                </table>
                ';
        //echo $html; die;
        $pdf = new PDF($html, 'pdf');
        $pdf->gerarRelatorio();

    }

    public function pdfPropostasAnaliseVisualTecnicoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        set_time_limit(320);

        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta();
        $rsProposta = $tblProposta->buscarPropostaAnaliseVisualTecnico(array("idOrgao = "=>$usuario), array("Tecnico ASC"));

        $arrTecnicos = array();
        foreach($rsProposta as $proposta) {
            $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $html = '
                <table width="100%">
                    <tr>
                        <th style="font-size:36px;">
                            Avaliação: Reavaliação
                        </th>
                    </tr>
                ';
        $ultimoRegistro = null;
        $ct = 1;
        if(!empty($arrTecnicosPropostas)) {
            foreach($arrTecnicosPropostas as $tecnico=>$propostas) {
                $html .= '
                    <tr>
                        <td align="left" style="border:1px #000000 solid; font-size:14px; font-weight:bold;">
                            Analista : '.$tecnico.'
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" cellspacing="2" style="border:1px #000000 solid;">
                                <tr>
                                    <th width="15%" style="border-bottom:1px #000000 solid;">Nr. Proposta</th>
                                    <th width="65%" style="border-bottom:1px #000000 solid;">Nome da Proposta</th>
                                    <th width="20%" style="border-bottom:1px #000000 solid;">Dt. Movimentação</th>
                                </tr>
                ';
                                foreach($propostas as $proposta){
                                if($proposta->ConformidadeOK == 0){
                $html .= '
                                <tr>
                                    <td align="center" style="font-size:12px;">'.$proposta->idProjeto.'</td>
                                    <td align="center" style="font-size:12px;">'.$proposta->NomeProjeto.'</td>
                                    <td align="center" style="font-size:12px;">'.ConverteData($proposta->DtMovimentacao,5).'</td>
                                </tr>
                ';
                                }
                                }
                $html .= '
                            </table>
                        </td>
                    </tr>
                ';
                $ct++;
            }
        }

        $html .= '
                </table>
                ';

        $html .= '
                <table width="100%">
                    <tr>
                        <th style="font-size:36px;">
                            Avaliação: Inicial
                        </th>
                    </tr>
                ';
        $ultimoRegistro = null;
        $ct = 1;
        if(!empty($arrTecnicosPropostas)) {
            foreach($arrTecnicosPropostas as $tecnico=>$propostas) {
                $html .= '
                    <tr>
                        <td align="left" style="border:1px #000000 solid; font-size:14px; font-weight:bold;">
                            Analista : '.$tecnico.'
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" cellspacing="2" style="border:1px #000000 solid;">
                                <tr>
                                    <th width="15%" style="border-bottom:1px #000000 solid;">Nr. Proposta</th>
                                    <th width="65%" style="border-bottom:1px #000000 solid;">Nome da Proposta</th>
                                    <th width="20%" style="border-bottom:1px #000000 solid;">Dt. Movimentação</th>
                                </tr>
                ';
                                foreach($propostas as $proposta){
                                if($proposta->ConformidadeOK == 9){
                $html .= '
                                <tr>
                                    <td align="center" style="font-size:12px;">'.$proposta->idProjeto.'</td>
                                    <td align="center" style="font-size:12px;">'.$proposta->NomeProjeto.'</td>
                                    <td align="center" style="font-size:12px;">'.ConverteData($proposta->DtMovimentacao,5).'</td>
                                </tr>
                ';
                                }
                                }
                $html .= '
                            </table>
                        </td>
                    </tr>
                ';
                $ct++;
            }
        }

        $html .= '
                </table>
                ';
        //echo $html; die;
        $pdf = new PDF($html, 'pdf');
        $pdf->gerarRelatorio();

    }

    public function historicoAnaliseVisualAction(){
        $post = Zend_Registry::get("get");
        $usuario = $this->codOrgaoSuperior;
		
        if(empty($post->busca)){
            $tblProposta = new Proposta();
            $rsTecnicos = $tblProposta->buscarTecnicosHistoricoAnaliseVisual($usuario);

            $arrDados = array(
                            "tecnicos"=>$rsTecnicos,
                            "urlForm"=>$this->view->baseUrl()."/admissibilidade/historico-analise-visual"
                        );

            $this->montaTela("admissibilidade/consultarhistoricoanalisevisual.phtml", $arrDados);
        }else{
            $tecnico = ($post->tecnico != "")?$post->tecnico:null;
            $dtInicio = ($post->dataPropostaInicial != "")?ConverteData($post->dataPropostaInicial,13):null;
            $dtFim = ($post->dataPropostaFinal != "")?ConverteData($post->dataPropostaFinal,13):null;

            $situacao = (!empty($post->situacao))?$post->situacao:null;

            $tblProposta = new Proposta();
            $rsProposta = $tblProposta->buscarHistoricoAnaliseVisual($usuario,$tecnico,$situacao,$dtInicio,$dtFim);

            $arrTecnicosPropostas = array();
            foreach($rsProposta as $proposta){
                $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
            }

            $arrDados = array(
                            "propostas"=>$rsProposta,
                            "tecnicosPropostas"=>$arrTecnicosPropostas,
                            "urlResumo"=>$this->_urlPadrao."/admissibilidade/resumo-historico-analise-visual"
                        );

            $this->montaTela("admissibilidade/listarhistoricoanalisevisual.phtml", $arrDados);
        }
    }

    public function resumoHistoricoAnaliseVisualAction(){

        $arrDados = array(
                        "resumo"=>$_POST,
                        "urlGerarGrafico"=>$this->_urlPadrao."/admissibilidade/grafico-historico-analise-visual"
                    );
        $this->montaTela("admissibilidade/resumohistoricoanalisevisual.phtml", $arrDados);
    }

    public function graficoHistoricoAnaliseVisualAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Resumo", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach($_POST as $chave=>$valor){
            $aux = explode("gVal_", $chave);
            if(isset($aux[1])){
                $titulos[] = str_replace("_", " ", $aux[1]);
                $valores[] = $valor;
            }
        }
        if(count($valores)>0){
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        }else{
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gráfico.";
        }
    }

    public function avaliacaoHistoricoAnaliseVisualAction(){
        $get = Zend_Registry::get("get");
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $tblProposta = new Proposta();
        $rsAvaliacao = $tblProposta->buscarAvaliacaoHistoricoAnaliseVisual($get->idAvaliacao);

        $avaliacao = trim($rsAvaliacao[0]->Avaliacao);
        if(!empty ($avaliacao)){
            echo $rsAvaliacao[0]->Avaliacao;
        }else{
            echo "<br><br><p align='center'><font color='red'><b>Nenhuma avalia&ccedil;&atilde;o encontrada.</b></font></p>";
        }
    }

    public function listarPropostasAction(){
        $usuario = $_SESSION['Zend_Auth']['storage']->usu_codigo;
        $post = Zend_Registry::get("post");

        //$analistas = AdmissibilidadeDAO::consultarRedistribuirAnalise($params);
        //$usuario = 605; //Apagar esta linha quando este modulo for para producao

        $rsPropostaInicial = array();
        $rsPropostaVisual = array();
        $rsPropostaDocumental = array();
        $rsPropostaFinal = array();
        $arrBusca['x.idTecnico = '] = $usuario;

        $tblProposta = new Proposta();

        if($post->numeroProposta != ""){
            $arrBusca['p.idPreProjeto = '] = $post->numeroProposta;
        }
        if($post->nomeProposta != ""){
            if($post->tiponome == "igual"){
                $arrBusca['p.NomeProjeto = '] = $post->nomeProposta;
            }elseif($post->tiponome == "contendo"){
                $arrBusca['p.NomeProjeto LIKE '] = "('%".$post->nomeProposta."%')";
            }
        }
        if($post->dataPropostaInicial != ""){
            if($post->tipodata == "igual"){
                $arrBusca['x.DtAvaliacao > '] = "'".ConverteData($post->dataPropostaInicial, 13)." 00:00:00'";
                $arrBusca['x.DtAvaliacao < '] = "'".ConverteData($post->dataPropostaInicial, 13)." 23:59:59'";
            }else{
                $arrBusca['x.DtAvaliacao > '] = "'".ConverteData($post->dataPropostaInicial, 13)." 00:00:00'";
                if($post->dataPropostaFinal != ""){
                    $arrBusca['x.DtAvaliacao < '] = "'".ConverteData($post->dataPropostaFinal, 13)." 23:59:59'";
                }
            }
        }

        if($post->situacao != ""){
            if($post->situacao == "inicial"){
                if($post->tipobuscasituacao == "igual"){
                    $arrBusca['m.Movimentacao = '] = 96;
                    $rsPropostaInicial = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 96 >> INICIAL
                }
            }
            if($post->situacao == "visual"){
                if($post->tipobuscasituacao == "igual"){
                    $arrBusca['m.Movimentacao = '] = 97;
                    $rsPropostaVisual = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 96 >> INICIAL
                }
            }
            /*if($post->situacao == "documental"){
                if($post->tipobuscasituacao == "igual"){
                    $arrBusca['m.Movimentacao = '] = 97;
                    $rsPropostaVisual = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 96 >> INICIAL
                }
            }*/
            if($post->situacao == "final"){
                if($post->tipobuscasituacao == "igual"){
                    $arrBusca['m.Movimentacao = '] = 128;
                    $rsPropostaFinal = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 96 >> INICIAL
                }
            }
        }else{
            $arrBusca['m.Movimentacao = '] = 96;
            $rsPropostaInicial = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 96 >> INICIAL

            $arrBusca['m.Movimentacao = '] = 97;
            $rsPropostaVisual = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 97 >> VISUAL

            //$arrBusca['m.Movimentacao = '] = ?;
            //$rsPropostaDocumental = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = ? >> DOCUMENTAL

            $arrBusca['m.Movimentacao = '] = 128;
            $rsPropostaFinal = $tblProposta->buscarPropostaAdmissibilidade($arrBusca, array("x.DtAvaliacao DESC")); //m.Movimentacao = 128 >> FINAL
        }

        //recuperando a unidade do usuario logado
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $idOrgao = $auth->getIdentity()->usu_orgao;
        $tblOrgao = new Orgaos();
        $rsOrgao = $tblOrgao->buscar(array("Codigo = ?"=>$idOrgao))->current();
        //xd($rsOrgao);

        $arrDados = array(
                        "propostasInicial"=>$rsPropostaInicial,
                        "propostasVisual"=>$rsPropostaVisual,
                        "propostasDocumental"=>$rsPropostaDocumental,
                        "propostasFinal"=>$rsPropostaFinal,
                        "orgao"=>$rsOrgao,
                        "formularioLocalizar"=>$this->_urlPadrao."/admissibilidade/localizar",
                        "urlResumo"=>$this->_urlPadrao."/admissibilidade/resumo-propostas"
                    );

        $this->montaTela("admissibilidade/listarpropostas.phtml", $arrDados);
    }

    public function listarPropostasNaoEnviadasAction(){
        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        if (isset($get->tamPag)) $this->intTamPag = $get->tamPag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
        $fim = $inicio + $this->intTamPag;

        $rsPropostasNaoEnviadas = array();

        $tblProposta = new Proposta();

        // =========== PROPOSTAS NAO ENVIADAS AO MINC AINDA =======================
        $arrBusca['m.Movimentacao = ?'] = 95;
        $rsPropostasNaoEnviadas = $tblProposta->buscarPropostaAdmissibilidadeZend($arrBusca, array("idProjeto DESC"), $this->intTamPag, $inicio); //m.Movimentacao = 95 >> NAO ENVIADA

        $total = $tblProposta->_totalRegistros;
        
        if ($fim>$total) $fim = $total;
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));

        $arrDados = array(
                        "pag"=>$pag,
                        "total"=>$total,
                        "inicio"=>($inicio+1),
                        "fim"=>$fim,
                        "totalPag"=>$totalPag,
                        "propostasNaoEnviadas"=>$rsPropostasNaoEnviadas,
                        "formularioLocalizar"=>$this->_urlPadrao."/admissibilidade/localizar",
                        "urlResumo"=>$this->_urlPadrao."/admissibilidade/resumo-propostas",
                        "urlPaginacao"=>$this->_urlPadrao."/admissibilidade/listar-propostas-nao-enviadas?"
                    );

        $this->montaTela("admissibilidade/listarpropostasnaoenviadas.phtml", $arrDados);
    }

    public function resumoPropostasAction(){
        $arrDados = array(
                        "resumo"=>$_POST,
                        "urlGerarGrafico"=>$this->_urlPadrao."/admissibilidade/grafico-admissibilidade-propostas"
                    );
        $this->montaTela("admissibilidade/resumopropostas.phtml", $arrDados);
    }

    public function resumoDistribuicaoPropostasAction(){
        if ($this->codOrgao) {
            $params                = new stdClass();
            $params->usu_orgao     = $this->codOrgao;
            $analistas             = AdmissibilidadeDAO::consultarRedistribuirAnalise($params);
            $this->view->analistas = array();
            $this->view->urlResumo = $this->_urlPadrao."/admissibilidade/resumo-distribuicao-propostas";
            $i = 0;
            foreach($analistas as $analista) {
                $dados[$analista->Tecnico][$analista->Fase][$i]['nrProposta']     = $analista->idProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['NomeProjeto']    = $analista->NomeProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['DtMovimentacao'] = $analista->DtMovimentacao;
                $dados[$analista->Tecnico][$analista->Fase][$i]['fase']           = $analista->Fase;
                $i++;
            }
        }

        $arrDados = array(
                        "resumo"=>$_POST,
                        "analistas"=>$dados,
                        "urlGerarGrafico"=>$this->_urlPadrao."/admissibilidade/grafico-distribuicao-propostas"
                    );
        $this->montaTela("admissibilidade/resumodistribuicaopropostas.phtml", $arrDados);
    }

    public function resumoGerenciamentoPropostaAction(){

        $arrDados = array(
                        "resumo"=>$_POST,
                        "urlGerarGrafico"=>$this->_urlPadrao."/admissibilidade/grafico-gerenciamento-propostas"
                    );
        $this->montaTela("admissibilidade/resumogerenciamentopropostas.phtml", $arrDados);
    }

    public function resumoPropostaAnaliseFinalAction(){

        $arrDados = array(
                        "resumo"=>$_POST,
                        "urlGerarGrafico"=>$this->_urlPadrao."/admissibilidade/grafico-propostas-analise-final"
                    );
        $this->montaTela("admissibilidade/resumopropostasanalisefinal.phtml", $arrDados);
    }

    public function resumoPropostasAnaliseVisualTecnicoAction(){

        if(!$_POST){
            $this->_redirect("/admissibilidade/listar-propostas-analise-visual-tecnico");
        }
        //x($_POST);
        $arrReavaliacao = array();
        $arrInicial = array();
        //prepara dados para gerar grafico
        foreach ($_POST as $analista=>$qtde)
        {
            $arrTempReavaliacao = explode("reavaliacao_", $analista);
            if(isset($arrTempReavaliacao[1])){
                $arrReavaliacao[str_replace("_", " ", $arrTempReavaliacao[1])]=$qtde;
            }

            $arrTempInicial = explode("inicial_", $analista);
            if(isset($arrTempInicial[1])){
                $arrInicial[str_replace("_", " ", $arrTempInicial[1])]=$qtde;
            }
        }
        $arrDados = array(
                        "resumoReavaliacao"=>$arrReavaliacao,
                        "resumoInicial"=>$arrInicial,
                        "urlGerarGrafico"=>$this->_urlPadrao."/admissibilidade/grafico-proposta-analise-visual-tecnico"
                    );
        $this->montaTela("admissibilidade/resumopropostaanalisevisualtecnico.phtml", $arrDados);
    }

    public function graficoDistribuicaoPropostasAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        error_reporting(E_ERROR);

        if ($this->codOrgao) {
            $params                = new stdClass();
            $params->usu_orgao     = $this->codOrgao;
            $analistas             = AdmissibilidadeDAO::consultarRedistribuirAnalise($params);
            $this->view->analistas = array();
            $this->view->urlResumo = $this->_urlPadrao."/admissibilidade/resumo-distribuicao-propostas";
            $i = 0;
            foreach($analistas as $analista) {
                $dados[$analista->Tecnico][$analista->Fase][$i]['nrProposta']     = $analista->idProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['NomeProjeto']    = $analista->NomeProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['DtMovimentacao'] = $analista->DtMovimentacao;
                $dados[$analista->Tecnico][$analista->Fase][$i]['fase']           = $analista->Fase;
                $i++;
            }
        }

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Avalia&ccedil;&atilde;o", "Registros");

        $grafico->configurar($_POST);
        //PREPARA NOME DAS FASES
        foreach ($dados as $nomeAnalista => $fases) {
            if(isset($_POST["todos"]) || isset($_POST[str_replace(".","_", str_replace(" ", "_", $nomeAnalista))])){
                foreach ($fases as $faseNome => $faseItems) {
                    $arrSeries[] = $faseNome;
                }
            }
        }
        $arrSeries = array_unique($arrSeries);


        $aux = array();
        foreach ($dados as $nomeAnalista => $fases) {
            if(isset($_POST["todos"]) || isset($_POST[str_replace(".","_", str_replace(" ", "_", $nomeAnalista))])){
                foreach ($fases as $faseNome => $faseItems) {
                    $valores[] = count($faseItems);
                    $titulos[] = $faseNome;
                }
                if(count($arrSeries) != count($valores)){
                    $valores[] = 0;
                }

                $grafico->addDados($valores, $nomeAnalista);
                $valores = array();
            }
        }

        /*$titulos = array("dan","dan1","dan2","dan3","dan4");
        $valores = array(1,2,3,4,5);
        $valores2 = array(1,2,3,4,5);
        $grafico->addDados($valores,"visual");
        $grafico->addDados($valores2, "documental");*/
        $grafico->setTituloItens(array_unique($titulos));
        $grafico->gerar();
    }

    public function graficoAdmissibilidadePropostasAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Avaliacao", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach($_POST as $chave=>$valor){
            $aux = explode("gVal_", $chave);
            if(isset($aux[1])){
                $titulos[] = $aux[1];
                $valores[] = $valor;
            }
        }

        if(count($valores)>0){
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        }else{
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gráfico.";
        }
    }

    public function graficoGerenciamentoPropostasAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Técnicos", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach($_POST as $chave=>$valor){
            $aux = explode("gVal_", $chave);
            if(isset($aux[1])){
                $titulos[] = str_replace("_", " ", $aux[1]);
                $valores[] = $valor;
            }
        }
        if(count($valores)>0){
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        }else{
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gráfico.";
        }
    }

    public function graficoPropostaAnaliseVisualTecnicoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        error_reporting(E_ERROR);
        if(!$_POST){
            $this->_redirect("/admissibilidade/resumo-propostas-analise-visual-tecnico");
        }

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Avaliação", "Registros");
        $grafico->configurar($_POST);

        $analista = array();
        $aux = array();
        foreach($_POST as $nomeAnalista=>$qtde){

            if(isset($_POST["todos"])){
                $nomeReavaliacao = explode("gValReavaliacao_", $nomeAnalista);
                $nomeInicial = explode("gValInicial_", $nomeAnalista);

                if(isset($nomeReavaliacao[1])){
                    $analista[] = $nomeReavaliacao[1];
                }elseif(isset($nomeInicial[1])){
                    $analista[] = $nomeInicial[1];
                }
            }elseif(isset($_POST["reavaliacao"])){
                $nomeReavaliacao = explode("gValReavaliacao_", $nomeAnalista);
                if(isset($nomeReavaliacao[1])){
                    $analista[] = $nomeReavaliacao[1];
                }
            }elseif(isset($_POST["inicial"])){
                $nomeInicial = explode("gValInicial_", $nomeAnalista);
                if(isset($nomeInicial[1])){
                   $analista[] = $nomeInicial[1];
                }
            }/*elseif(isset($_POST[$nomeAnalista])){
                $chaves = array_keys($_POST);
                $analista[]=$chaves[0];
                break;
            }*/

            /*if(isset($nomeInicial[1])){
                $analista[] = str_replace("_", " ", $nomeInicial[1]);
                $valores[$nomeInicial[1]][] = $qtde;
                $valores2[] = $qtde;
            }else{
                $valores[$aux1[1]][] = 0;
            }*/
        }

        $analista = array_unique($analista);
        foreach($analista as $nome){
            if(isset($_POST["todos"])){

                if(array_key_exists("gValReavaliacao_".$nome,$_POST) && array_key_exists("gValInicial_".$nome,$_POST)){

                    $valores[] = $_POST["gValReavaliacao_".$nome];
                    $valores[] = $_POST["gValInicial_".$nome];

                }elseif(array_key_exists("gValReavaliacao_".$nome,$_POST) && !array_key_exists("gValInicial_".$nome,$_POST)){
                    $valores[] = $_POST["gValReavaliacao_".$nome];
                    $valores[] = 0;

                }elseif(!array_key_exists("gValReavaliacao_".$nome,$_POST) && array_key_exists("gValInicial_".$nome,$_POST)){
                    $valores[] = 0;
                    $valores[] = $_POST["gValInicial_".$nome];

                }else{
                    $valores[] = 0;
                    $valores[] = 0;
                }
            }elseif(isset($_POST["reavaliacao"])){

               $valores[] = $_POST["gValReavaliacao_".$nome];

            }elseif(isset($_POST["inicial"])){

                $valores[] = $_POST["gValInicial_".$nome];
            }

            $grafico->addDados($valores, str_replace("_", " ", $nome));
            $valores = array();
        }

        $arrTitulo = array();
        if(isset($_POST["reavaliacao"])){
            $arrTitulo[]="Reavaliação";
        }elseif(isset($_POST["inicial"])){
            $arrTitulo[]="Inicial";
        }else{
            $arrTitulo[]="Reavaliação";
            $arrTitulo[]="Inicial";
        }

        $grafico->setTituloItens($arrTitulo);
        $grafico->gerar();
    }

    public function graficoPropostasAnaliseFinalAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Resumo", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach($_POST as $chave=>$valor){
            $aux = explode("gVal_", $chave);
            if(isset($aux[1])){
                $titulos[] = str_replace("_", " ", $aux[1]);
                $valores[] = $valor;
            }
        }
        if(count($valores)>0){
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        }else{
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gráfico.";
        }
    }

    public function localizarAction(){
        $arrDados = array(
                        "urlAcao"=>$this->_urlPadrao."/admissibilidade/listar-propostas"
                    );

        $this->montaTela("admissibilidade/localizarpropostas.phtml", $arrDados);
    }


    public function localizarGerenciamentoPropostaAction(){
        $params = new stdClass();
        $params->usu_nome   = "";
        $params->gru_codigo = $_SESSION['GrupoAtivo']['codOrgao'];
        $params->usu_orgao  = $this->codOrgaoSuperior;
        $this->view->novosAnalistas = AdmissibilidadeDAO::consultarRedistribuirAnaliseItemSelect($params);
        $arrDados = array(
                        "urlAcao"=>$this->_urlPadrao."/admissibilidade/gerenciamentodepropostas",
                        "urlResumo"=>$this->_urlPadrao."/admissibilidade/resumo-gerenciamento-proposta"
                    );

        $this->montaTela("admissibilidade/localizarpropostasgerenciamento.phtml", $arrDados);
    }

    public function desarquivarpropostasAction(){

    }

    public function buscarPropostaAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $nrProposta = $_POST['nrProposta'];

        $dados = array();
        if(!empty($nrProposta)){
            $dados['idPreProjeto = ?'] = $nrProposta;
            $dados['stEstado = ?'] = 0;
            $dados['dtArquivamento is not null'] = '';
            $PreProjeto = new PreProjeto();
            $result = $PreProjeto->buscar($dados);

            $a = 0;
            if(count($result) > 0){
                foreach ($result as $registro) {
                    $dadosProposta[$a]['idPreProjeto'] = $registro['idPreProjeto'];
                    $dadosProposta[$a]['NomeProjeto'] = utf8_encode($registro['NomeProjeto']);
                    $a++;
                }
                $jsonEncode = json_encode($dadosProposta);
                echo json_encode(array('resposta'=>true,'conteudo'=>$dadosProposta));

            } else {
                echo json_encode(array('resposta'=>false));
            }

        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }

    public function desarquivamentoPropostaAction(){
        $post = Zend_Registry::get('post');

        if($post->desarquivamento){

            $dados = array(
//                'DtArquivamento' => new Zend_Db_Expr('GETDATE()'),
                'DtArquivamento' => null,
                'stEstado' => 1
            );
            $where = array('idPreProjeto = ?' => $post->nrProposta);

            $PreProjeto = new PreProjeto();
            $result = $PreProjeto->update($dados, $where);

            parent::message("Proposta desarquivada com sucesso!", "/admissibilidade/desarquivarpropostas", "CONFIRM");

        } else {
            parent::message("Erro ao desarquivar proposta!", "/admissibilidade/desarquivarpropostas", "ERROR");
        }
    }

    public function painelProjetosDistribuidosAction(){
        //select codigo,sigla from orgaos WHERE Status = 0 and vinculo = 1 order by sigla
        $where = array(
            'Status = ?' => 0,
            'vinculo = ?' => 1,
        );

        $Orgaos = new Orgaos();
        $dados = $Orgaos->buscar($where, array('Sigla'));
        $this->view->orgaos = $dados;
    }

    public function listaProjetosDistribuidosAction(){

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if($this->_request->getParam("qtde")) {
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if($this->_request->getParam("ordem")) {
            $ordem = $this->_request->getParam("ordem");
            if($ordem == "ASC") {
                $novaOrdem = "DESC";
            }else {
                $novaOrdem = "ASC";
            }
        }else {
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if($this->_request->getParam("campo")) {
            $campo = $this->_request->getParam("campo");
            $order = array($campo." ".$ordem);
            $ordenacao = "&campo=".$campo."&ordem=".$ordem;

        } else {
            $campo = null;
            $order = array(2,5,6); //Pronac,Produto,DescricaoAnalise
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');

        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();

        if(isset($get->pronac) && !empty($get->pronac)){
            $where['Pronac = ?'] = $get->pronac;
            $this->view->pronac = $get->pronac;
        }

        if(isset($get->estado) && !empty($get->estado)){
            if($get->estado == 1){
                $situacao = 'Em análise';
            } else {
                $situacao = '<font color=red>Concluida</font>';
            }
            $where['Situacao = ?'] = $situacao;
            $this->view->estado = $get->estado;
        }

        if(isset($get->orgao) && !empty($get->orgao)){
            $where['idOrgao = ?'] = $get->orgao;
            $this->view->orgao = $get->orgao;
        }
        
        $vwProjetoDistribuidoVinculada = New vwProjetoDistribuidoVinculada();
        $total = $vwProjetoDistribuidoVinculada->buscarUnidades($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $vwProjetoDistribuidoVinculada->buscarUnidades($where, $order, $tamanho, $inicio);

        $paginacao = array(
            "pag"=>$pag,
            "qtde"=>$this->intTamPag,
            "campo"=>$campo,
            "ordem"=>$ordem,
            "ordenacao"=>$ordenacao,
            "novaOrdem"=>$novaOrdem,
            "total"=>$total,
            "inicio"=>($inicio+1),
            "fim"=>$fim,
            "totalPag"=>$totalPag,
            "Itenspag"=>$this->intTamPag,
            "tamanho"=>$tamanho
         );

        $this->view->paginacao     = $paginacao;
        $this->view->qtdDocumentos = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
    }

    public function imprimirProjetosDistribuidosAction() {

        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
        $post = Zend_Registry::get('post');

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if($this->_request->getParam("qtde")) {
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if($this->_request->getParam("ordem")) {
            $ordem = $this->_request->getParam("ordem");
            if($ordem == "ASC") {
                $novaOrdem = "DESC";
            }else {
                $novaOrdem = "ASC";
            }
        }else {
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if($this->_request->getParam("campo")) {
            $campo = $this->_request->getParam("campo");
            $order = array($campo." ".$ordem);
            $ordenacao = "&campo=".$campo."&ordem=".$ordem;
        } else {
            $campo = null;
            $order = array(2,5,6); //Pronac,Produto,DescricaoAnalise
            $ordenacao = null;
        }

        /* ================== PAGINACAO ======================*/
        $where = array();

        if(isset($post->pronac) && !empty($post->pronac)){
            $where['Pronac = ?'] = $post->pronac;
            $this->view->pronac = $post->pronac;
        }

        if(isset($post->estado) && !empty($post->estado)){
            if($post->estado == 1){
                $situacao = 'Em análise';
            } else {
                $situacao = '<font color=red>Concluida</font>';
            }
            $where['Situacao = ?'] = $situacao;
            $this->view->estado = $post->estado;
        }

        if(isset($post->orgao) && !empty($post->orgao)){
            $where['idOrgao = ?'] = $post->orgao;
            $this->view->orgao = $post->orgao;
        }

        $vwProjetoDistribuidoVinculada = New vwProjetoDistribuidoVinculada();
        $busca = $vwProjetoDistribuidoVinculada->buscarUnidades($where, $order);

        $this->view->dados = $busca;
    }

}
