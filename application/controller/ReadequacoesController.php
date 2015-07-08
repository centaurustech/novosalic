<?php

/**
 * Controller Readequacoes
 * @author Jefferson Alessandro - jeffersonassilva@gmail.com
 * @since 14/11/2013
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright  2010 - Ministerio da Cultura - Todos os direitos reservados.
 */
class ReadequacoesController extends GenericControllerNew {

    private $intTamPag = 10;
    private $idAgente = 0;
    private $idUsuario = 0;
    private $idOrgao = 0;
    private $idPerfil = 0;
    
    /**
     * Reescreve o metodo init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->idOrgao = $GrupoAtivo->codOrgao;
        $this->idPerfil = $GrupoAtivo->codGrupo;

        // verifica as permissoes
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = '1111'; //Permissao para proponentes.

        // pega o idAgente do usuário logado
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $this->view->usuarioInterno = false;
        
        if (isset($auth->getIdentity()->usu_codigo)) { // autenticacao novo salic
            $this->view->usuarioInterno = true;
            $this->idUsuario = $auth->getIdentity()->usu_codigo;
            
            //Recupera todos os grupos do Usuario
            $Usuario = new Usuario(); // objeto usuário
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
            foreach ($grupos as $grupo){
                $PermissoesGrupo[] = $grupo->gru_codigo;
            }
            
            parent::perfil(1, $PermissoesGrupo);
            $this->idAgente = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
            $this->idAgente = ($this->idAgente) ? $this->idAgente["idAgente"] : 0;
        
        } else { // autenticacao scriptcase
            parent::perfil(4, $PermissoesGrupo);
            $this->idUsuario = (isset($_GET["idusuario"])) ? $_GET["idusuario"] : 0;
            
            /* =============================================================================== */ 
            /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE AO PROJETO ====== */
            /* =============================================================================== */
            $this->verificarPermissaoAcesso(false, true, false);
        }
        parent::init();
        
        //SE CAIU A SECAO REDIRECIONA
        if(!$auth->hasIdentity()){
            $url = Zend_Controller_Front::getInstance()->getBaseUrl();
            JS::redirecionarURL($url);
        }

        //SE NENHUM PRONAC FOI ENVIADO, REDIRECIONA
        $idPronac = $this->_request->getParam("idPronac"); // pega o id do pronac via get
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        $this->view->idPronac = $idPronac;
    }

    /**
     * Tela de cadastro de readequações - visão do proponente
     * @require idPronac
     */
    public function indexAction() {
        //FUNÇÃO ACESSADA SOMENTE PELO PROPONENTE.
        $this->view->idPerfil = $this->idPerfil;
        if($this->idPerfil != 1111){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        if(isset($_POST['iduf'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $iduf = $_POST['iduf'];
            $cidade = CidadeDAO::buscar($iduf);
            $a = 0;
            $cidadeArray = array();
            foreach($cidade as $DadosCidade) {
                $cidadeArray[$a]['idCidade'] = $DadosCidade->id;
                $cidadeArray[$a]['nomeCidade'] = utf8_encode($DadosCidade->descricao);
                $a++;
            }
            echo json_encode($cidadeArray);
            die;
        }
        
        if(isset($_POST['idEtapa']) && isset($_POST['idProduto'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $tbItensPlanilhaProduto = new tbItensPlanilhaProduto();
            $itens = $tbItensPlanilhaProduto->itensPorItemEEtapaReadequacao($_POST['idEtapa'], $_POST['idProduto']);

            $a = 0;
            $itensArray = array();
            foreach($itens as $i) {
                $itensArray[$a]['idPlanilhaItens'] = $i->idPlanilhaItens;
                $itensArray[$a]['Item'] = utf8_encode($i->Item);
                $a++;
            }
            echo json_encode($itensArray);
            die;
        }
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }

        $this->view->idPronac = $idPronac;
        if(!empty($idPronac)){
            $Projetos = new Projetos();
            $this->view->projeto = $Projetos->buscar(array('IdPRONAC = ?'=>$idPronac))->current();
            
            $buscarRecurso = ManterorcamentoDAO::buscarFonteRecurso();
            $this->view->Recursos = $buscarRecurso;
            
            $buscarEstado = EstadoDAO::buscar();
            $this->view->UFs = $buscarEstado;
            
            $PlanoDistribuicaoProduto = new PlanoDistribuicaoProduto();
            $this->view->Produtos = $PlanoDistribuicaoProduto->comboProdutosParaInclusaoReadequacao($idPronac);
            
            $tbPlanilhaEtapa = new tbPlanilhaEtapa();
            $this->view->Etapas = $tbPlanilhaEtapa->buscar(array('stEstado = ?'=>1));
            
            $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
            $this->view->Unidade = $buscarUnidade;
            
            $tbTipoReadequacao = new tbTipoReadequacao();
            $this->view->TiposReadequacao = $tbTipoReadequacao->buscarTiposReadequacoesPermitidos($idPronac);
            
            $tbReadequacao = new tbReadequacao();
            $this->view->readequacoesCadastradas = $tbReadequacao->readequacoesCadastradasProponente(array('a.idPronac = ?'=>$idPronac, 'a.siEncaminhamento = ?'=>12), array(1));
        } else {
            parent::message("N&uacute;mero Pronac inv&aacute;lido!", "principalproponente", "ERROR");
        }
    }
    
    /* 
     * Criada em 06/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é acessada para alterar o item da planilha orçamentária.
     */
    public function alterarItemSolicitacaoAction() {
        $this->_helper->layout->disableLayout();
        $idPlanilhaAprovacao = $this->_request->getParam("idPlanilha");
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        
        /* DADOS DO ITEM ATIVO */
        $itemTipoPlanilha = $tbPlanilhaAprovacao->buscar(array('idPlanilhaAprovacao=?'=>$idPlanilhaAprovacao))->current();
        $where = array();
        if($itemTipoPlanilha->tpPlanilha == 'SR'){
            $where['idPlanilhaAprovacao = ?'] = !empty($itemTipoPlanilha->idPlanilhaAprovacaoPai) ? $itemTipoPlanilha->idPlanilhaAprovacaoPai : $itemTipoPlanilha->idPlanilhaAprovacao;
            $idPlan = !empty($itemTipoPlanilha->idPlanilhaAprovacaoPai) ? $itemTipoPlanilha->idPlanilhaAprovacaoPai : $itemTipoPlanilha->idPlanilhaAprovacao;
        } else {
            $where['idPlanilhaAprovacao = ?'] = $idPlanilhaAprovacao;
            $idPlan = $idPlanilhaAprovacao;
        }
        $where['stAtivo = ?'] = 'S';
        $planilhaAtiva = $tbPlanilhaAprovacao->buscarDadosAvaliacaoDeItemRemanejamento($where);
        
        /* DADOS DO ITEM PARA EDICAO DA READEQUACAO */
        $where = array();
        $where['idPlanilhaAprovacao = ?'] = $idPlanilhaAprovacao;
        $where['tpPlanilha = ?'] = 'SR';
        $where['stAtivo = ?'] = 'N';
        $planilhaEditaval = $tbPlanilhaAprovacao->buscarDadosAvaliacaoDeItemRemanejamento($where);
        
        $dadosPlanilhaAtiva = array();
        $dadosPlanilhaEditavel = array();
            
        /* PROJETO */
        $Projetos = new Projetos();
        $projeto = $Projetos->buscar(array('IdPRONAC = ?' => $_GET['idPronac']))->current();
        $dadosProjeto = array(
            'IdPRONAC' => $projeto->IdPRONAC,
            'PRONAC' => $projeto->AnoProjeto.$projeto->Sequencial,
            'NomeProjeto' => utf8_encode($projeto->NomeProjeto)
        );

        foreach ($planilhaAtiva as $registro) {
            //CALCULAR VALORES MINIMO E MAXIMO PARA VALIDACAO
            $dadosPlanilhaAtiva['idPlanilhaAprovacao'] = $registro['idPlanilhaAprovacao'];
            $dadosPlanilhaAtiva['idProduto'] = $registro['idProduto'];
            $dadosPlanilhaAtiva['descProduto'] = utf8_encode($registro['descProduto']);
            $dadosPlanilhaAtiva['idEtapa'] = $registro['idEtapa'];
            $dadosPlanilhaAtiva['descEtapa'] = utf8_encode($registro['descEtapa']);
            $dadosPlanilhaAtiva['idPlanilhaItem'] = $registro['idPlanilhaItem'];
            $dadosPlanilhaAtiva['descItem'] = utf8_encode($registro['descItem']);
            $dadosPlanilhaAtiva['idUnidade'] = $registro['idUnidade'];
            $dadosPlanilhaAtiva['descUnidade'] = utf8_encode($registro['descUnidade']);
            $dadosPlanilhaAtiva['Quantidade'] = $registro['Quantidade'];
            $dadosPlanilhaAtiva['Ocorrencia'] = $registro['Ocorrencia'];
            $dadosPlanilhaAtiva['ValorUnitario'] = utf8_encode('R$ '.number_format($registro['ValorUnitario'], 2, ',', '.'));
            $dadosPlanilhaAtiva['QtdeDias'] = $registro['QtdeDias'];
            $dadosPlanilhaAtiva['TotalSolicitado'] = utf8_encode('R$ '.number_format(($registro['Quantidade']*$registro['Ocorrencia']*$registro['ValorUnitario']), 2, ',', '.'));
            $dadosPlanilhaAtiva['Justificativa'] = utf8_encode($registro['Justificativa']);
        }

        if(count($planilhaEditaval) > 0){
            foreach ($planilhaEditaval as $registroEditavel) {
                $dadosPlanilhaEditavel['idPlanilhaAprovacao'] = $registroEditavel['idPlanilhaAprovacao'];
                $dadosPlanilhaEditavel['idProduto'] = $registroEditavel['idProduto'];
                $dadosPlanilhaEditavel['descProduto'] = utf8_encode($registroEditavel['descProduto']);
                $dadosPlanilhaEditavel['idEtapa'] = $registroEditavel['idEtapa'];
                $dadosPlanilhaEditavel['descEtapa'] = utf8_encode($registroEditavel['descEtapa']);
                $dadosPlanilhaEditavel['idPlanilhaItem'] = $registroEditavel['idPlanilhaItem'];
                $dadosPlanilhaEditavel['descItem'] = utf8_encode($registroEditavel['descItem']);
                $dadosPlanilhaEditavel['idUnidade'] = $registroEditavel['idUnidade'];
                $dadosPlanilhaEditavel['descUnidade'] = utf8_encode($registroEditavel['descUnidade']);
                $dadosPlanilhaEditavel['Quantidade'] = $registroEditavel['Quantidade'];
                $dadosPlanilhaEditavel['Ocorrencia'] = $registroEditavel['Ocorrencia'];
                $dadosPlanilhaEditavel['ValorUnitario'] = utf8_encode('R$ '.number_format($registroEditavel['ValorUnitario'], 2, ',', '.'));
                $dadosPlanilhaEditavel['QtdeDias'] = $registroEditavel['QtdeDias'];
                $dadosPlanilhaEditavel['TotalSolicitado'] = utf8_encode('R$ '.number_format(($registroEditavel['Quantidade']*$registroEditavel['Ocorrencia']*$registroEditavel['ValorUnitario']), 2, ',', '.'));
                $dadosPlanilhaEditavel['Justificativa'] = utf8_encode($registroEditavel['Justificativa']);
                $dadosPlanilhaEditavel['idAgente'] = $registroEditavel['idAgente'];
            }
        } else {
            $dadosPlanilhaEditavel = $dadosPlanilhaAtiva;
        }
        
        if(count($planilhaEditaval) > 0 && count($planilhaAtiva)==0){
            $dadosPlanilhaAtiva = $dadosPlanilhaEditavel;
        }
            
        $tbCompPagxPlanAprov = new tbComprovantePagamentoxPlanilhaAprovacao();
        $res = $tbCompPagxPlanAprov->buscarValorComprovadoDoItem($idPlan);
        $valoresDoItem = array(
            'vlComprovadoDoItem' => utf8_encode('R$ '.number_format($res->vlComprovado, 2, ',', '.')),
            'vlComprovadoDoItemValidacao' => utf8_encode(number_format($res->vlComprovado, 2, '', ''))
        );
            
        //$jsonEncode = json_encode($dadosPlanilha);
        echo json_encode(array('resposta'=>true, 'dadosPlanilhaAtiva'=>$dadosPlanilhaAtiva, 'dadosPlanilhaEditavel'=>$dadosPlanilhaEditavel, 'valoresDoItem'=>$valoresDoItem, 'dadosProjeto'=>$dadosProjeto));
        die();
    }
    
    /* 
     * Criada em 14/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Esse função é usada pelo proponente para solicitar a exclusão de um item da planilha orçamentária.
     */
    public function excluirItemSolicitacaoAction() {
        $this->_helper->layout->disableLayout();
        $idPlanilhaAprovacao = $this->_request->getParam("idPlanilha");
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        $verificarPlanilhaSR = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'tpPlanilha=?'=>'SR'));
        
        //VERIFICA SE JÁ POSSUI A PLANILHA TIPO SR. SE NÃO TIVER, CRIA, E DEPOIS DELETA O ITEM DESEJADO.
        if(count($verificarPlanilhaSR)==0){
            $planilhaAtiva = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'StAtivo=?'=>'S'));
            $planilhaSR = array();
            foreach ($planilhaAtiva as $value) {
                $planilhaSR['tpPlanilha'] = 'SR';
                $planilhaSR['dtPlanilha'] = new Zend_Db_Expr('GETDATE()');
                $planilhaSR['idPlanilhaProjeto'] = $value['idPlanilhaProjeto'];
                $planilhaSR['idPlanilhaProposta'] = $value['idPlanilhaProposta'];
                $planilhaSR['IdPRONAC'] = $value['IdPRONAC'];
                $planilhaSR['idProduto'] = $value['idProduto'];
                $planilhaSR['idEtapa'] = $value['idEtapa'];
                $planilhaSR['idPlanilhaItem'] = $value['idPlanilhaItem'];
                $planilhaSR['dsItem'] = $value['dsItem'];
                $planilhaSR['idUnidade'] = $value['idUnidade'];
                $planilhaSR['qtItem'] = $value['qtItem'];
                $planilhaSR['nrOcorrencia'] = $value['nrOcorrencia'];
                $planilhaSR['vlUnitario'] = $value['vlUnitario'];
                $planilhaSR['qtDias'] = $value['qtDias'];
                $planilhaSR['tpDespesa'] = $value['tpDespesa'];
                $planilhaSR['tpPessoa'] = $value['tpPessoa'];
                $planilhaSR['nrContraPartida'] = $value['nrContraPartida'];
                $planilhaSR['nrFonteRecurso'] = $value['nrFonteRecurso'];
                $planilhaSR['idUFDespesa'] = $value['idUFDespesa'];
                $planilhaSR['idMunicipioDespesa'] = $value['idMunicipioDespesa'];
                $planilhaSR['dsJustificativa'] = null;
                $planilhaSR['idAgente'] = 0;
                $planilhaSR['idPlanilhaAprovacaoPai'] = $value['idPlanilhaAprovacao'];
                $planilhaSR['idReadequacao'] = $value['idReadequacao'];
                $planilhaSR['tpAcao'] = 'N';
                $planilhaSR['idRecursoDecisao'] = $value['idRecursoDecisao'];
                $planilhaSR['stAtivo'] = 'N';
                $tbPlanilhaAprovacao->inserir($planilhaSR);
            }
        }
            
        /* DADOS DO ITEM PARA EXCLUSAO LÓGICA DO ITEM DA READEQUACAO */
        $dados = array();
        $dados['tpAcao'] = 'E';
            
        $whereIdPlanilha = "idPlanilhaAprovacaoPai = $idPlanilhaAprovacao";
        $itemTipoPlanilha = $tbPlanilhaAprovacao->buscar(array('idPlanilhaAprovacao=?'=>$idPlanilhaAprovacao))->current();
            
        if($itemTipoPlanilha->tpAcao == 'I'){
            $exclusaoLogica = $tbPlanilhaAprovacao->delete(array('idPlanilhaAprovacao = ?'=>$idPlanilhaAprovacao));
        } else {
            if($itemTipoPlanilha->tpPlanilha == 'SR'){
                $whereIdPlanilha = "idPlanilhaAprovacao = $idPlanilhaAprovacao";
            }
            $where = "stAtivo = 'N' AND tpPlanilha = 'SR' AND $whereIdPlanilha";
            $exclusaoLogica = $tbPlanilhaAprovacao->update($dados, $where);
        }
                
        if($exclusaoLogica){
            //$jsonEncode = json_encode($dadosPlanilha);
            echo json_encode(array('resposta'=>true));

                } else {
            echo json_encode(array('resposta'=>false));
                }
        die();
    }
    
    /* 
     * Criada em 10/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     */
    public function incluirItemPlanilhaReadequacaoAction() {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        $verificarPlanilhaSR = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'tpPlanilha=?'=>'SR'));
        
        //VERIFICA SE JA POSSUI A PLANILHA TIPO SR. SE NÃO TIVER, COPIA A ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        if(count($verificarPlanilhaSR)==0){
            $planilhaAtiva = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'StAtivo=?'=>'S'));
            $planilhaSR = array();
            foreach ($planilhaAtiva as $value) {
                $planilhaSR['tpPlanilha'] = 'SR';
                $planilhaSR['dtPlanilha'] = new Zend_Db_Expr('GETDATE()');
                $planilhaSR['idPlanilhaProjeto'] = $value['idPlanilhaProjeto'];
                $planilhaSR['idPlanilhaProposta'] = $value['idPlanilhaProposta'];
                $planilhaSR['IdPRONAC'] = $value['IdPRONAC'];
                $planilhaSR['idProduto'] = $value['idProduto'];
                $planilhaSR['idEtapa'] = $value['idEtapa'];
                $planilhaSR['idPlanilhaItem'] = $value['idPlanilhaItem'];
                $planilhaSR['dsItem'] = $value['dsItem'];
                $planilhaSR['idUnidade'] = $value['idUnidade'];
                $planilhaSR['qtItem'] = $value['qtItem'];
                $planilhaSR['nrOcorrencia'] = $value['nrOcorrencia'];
                $planilhaSR['vlUnitario'] = $value['vlUnitario'];
                $planilhaSR['qtDias'] = $value['qtDias'];
                $planilhaSR['tpDespesa'] = $value['tpDespesa'];
                $planilhaSR['tpPessoa'] = $value['tpPessoa'];
                $planilhaSR['nrContraPartida'] = $value['nrContraPartida'];
                $planilhaSR['nrFonteRecurso'] = $value['nrFonteRecurso'];
                $planilhaSR['idUFDespesa'] = $value['idUFDespesa'];
                $planilhaSR['idMunicipioDespesa'] = $value['idMunicipioDespesa'];
                $planilhaSR['dsJustificativa'] = null;
                $planilhaSR['idAgente'] = 0;
                $planilhaSR['idPlanilhaAprovacaoPai'] = $value['idPlanilhaAprovacao'];
                $planilhaSR['idReadequacao'] = $value['idReadequacao'];
                $planilhaSR['tpAcao'] = 'N';
                $planilhaSR['idRecursoDecisao'] = $value['idRecursoDecisao'];
                $planilhaSR['stAtivo'] = 'N';
                $tbPlanilhaAprovacao->inserir($planilhaSR);
            }
        }
        
        $idAgente = 0;
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $tblAgente = new Agentes();
        $rsAgente = $tblAgente->buscar(array('CNPJCPF = ?'=>$auth->getIdentity()->Cpf));
        if($rsAgente->count() > 0){
             $idAgente = $rsAgente[0]->idAgente;
        }
        
        $newValorUnitario = str_replace('.', '', $_POST['newValorUnitario']);
        $newValorUnitario = str_replace(',', '.', $newValorUnitario);
        
        /* DADOS DO ITEM PARA INCLUSÃO DA READEQUAÇÃO */
        $dadosInclusao = array();
        $dadosInclusao['tpPlanilha'] = 'SR';
        $dadosInclusao['dtPlanilha'] = new Zend_Db_Expr('GETDATE()');
        $dadosInclusao['IdPRONAC'] = $idPronac;
        $dadosInclusao['idProduto'] = $_POST['newProduto'];
        $dadosInclusao['idEtapa'] = $_POST['newEtapa'];
        $dadosInclusao['idPlanilhaItem'] = $_POST['newItem'];
        $dadosInclusao['dsItem'] = '';
        $dadosInclusao['idUnidade'] = $_POST['newUnidade'];
        $dadosInclusao['qtItem'] = $_POST['newQuantidade'];
        $dadosInclusao['nrOcorrencia'] = $_POST['newOcorrencia'];
        $dadosInclusao['vlUnitario'] = $newValorUnitario;
        $dadosInclusao['qtDias'] = $_POST['newDias'];
        $dadosInclusao['tpDespesa'] = 0;
        $dadosInclusao['tpPessoa'] = 0;
        $dadosInclusao['nrContraPartida'] = 0;
        $dadosInclusao['nrFonteRecurso'] = $_POST['newRecursos'];
        $dadosInclusao['idUFDespesa'] = $_POST['newUF'];
        $dadosInclusao['idMunicipioDespesa'] = $_POST['newMunicipio'];
        $dadosInclusao['dsJustificativa'] = utf8_decode($_POST['newJustificativa']);
        $dadosInclusao['idAgente'] = $idAgente;
        $dadosInclusao['stAtivo'] = 'N';
        $dadosInclusao['tpAcao'] = 'I';
        $insert = $tbPlanilhaAprovacao->inserir($dadosInclusao);
        
        if($insert){
            //$jsonEncode = json_encode($dadosPlanilha);
            echo json_encode(array('resposta'=>true));

            } else {
            echo json_encode(array('resposta'=>false));
            }
        die();
    }
            
    /* 
     * Criada em 06/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para alterar os dados do item da planilha orçamentária.
     */
    public function salvarAvaliacaoDoItemAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $cpf = isset($auth->getIdentity()->Cpf) ? $auth->getIdentity()->Cpf : $auth->getIdentity()->usu_identificacao;
            
        $tblAgente = new Agentes();
        $rsAgente = $tblAgente->buscar(array('CNPJCPF = ?'=>$cpf));
        $idAgente = 0;
        if($rsAgente->count() > 0){
             $idAgente = $rsAgente[0]->idAgente;
        }

        $ValorUnitario = str_replace('.', '', $_POST['ValorUnitario']);
        $ValorUnitario = str_replace(',', '.', $ValorUnitario);
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        $verificarPlanilhaSR = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'tpPlanilha=?'=>'SR'));
        
        if(count($verificarPlanilhaSR)==0){
            $planilhaAtiva = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'StAtivo=?'=>'S'));
            $planilhaSR = array();
            foreach ($planilhaAtiva as $value) {
                $planilhaSR['tpPlanilha'] = 'SR';
                $planilhaSR['dtPlanilha'] = new Zend_Db_Expr('GETDATE()');
                $planilhaSR['idPlanilhaProjeto'] = $value['idPlanilhaProjeto'];
                $planilhaSR['idPlanilhaProposta'] = $value['idPlanilhaProposta'];
                $planilhaSR['IdPRONAC'] = $value['IdPRONAC'];
                $planilhaSR['idProduto'] = $value['idProduto'];
                $planilhaSR['idEtapa'] = $value['idEtapa'];
                $planilhaSR['idPlanilhaItem'] = $value['idPlanilhaItem'];
                $planilhaSR['dsItem'] = $value['dsItem'];
                $planilhaSR['idUnidade'] = $value['idUnidade'];
                $planilhaSR['qtItem'] = $value['qtItem'];
                $planilhaSR['nrOcorrencia'] = $value['nrOcorrencia'];
                $planilhaSR['vlUnitario'] = $value['vlUnitario'];
                $planilhaSR['qtDias'] = $value['qtDias'];
                $planilhaSR['tpDespesa'] = $value['tpDespesa'];
                $planilhaSR['tpPessoa'] = $value['tpPessoa'];
                $planilhaSR['nrContraPartida'] = $value['nrContraPartida'];
                $planilhaSR['nrFonteRecurso'] = $value['nrFonteRecurso'];
                $planilhaSR['idUFDespesa'] = $value['idUFDespesa'];
                $planilhaSR['idMunicipioDespesa'] = $value['idMunicipioDespesa'];
                $planilhaSR['dsJustificativa'] = null;
                $planilhaSR['idAgente'] = 0;
                $planilhaSR['idPlanilhaAprovacaoPai'] = $value['idPlanilhaAprovacao'];
                $planilhaSR['idReadequacao'] = $value['idReadequacao'];
                $planilhaSR['tpAcao'] = 'N';
                $planilhaSR['idRecursoDecisao'] = $value['idRecursoDecisao'];
                $planilhaSR['stAtivo'] = 'N';
                $tbPlanilhaAprovacao->inserir($planilhaSR);
            }
        }
        
        $itemTipoPlanilha = $tbPlanilhaAprovacao->buscar(array('idPlanilhaAprovacao=?'=>$_POST['idPlanilha']))->current();
        if($itemTipoPlanilha->tpPlanilha == 'SR'){
            $editarItem = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'tpPlanilha=?'=>'SR', 'idPlanilhaAprovacao=?'=>$_POST['idPlanilha']))->current();
        } else {
            $editarItem = $tbPlanilhaAprovacao->buscar(array('IdPRONAC=?'=>$idPronac, 'tpPlanilha=?'=>'SR', 'idPlanilhaAprovacaoPai=?'=>$_POST['idPlanilha']))->current();
        }
        $editarItem->idUnidade = $_POST['Unidade'];
        $editarItem->qtItem = $_POST['Quantidade'];
        $editarItem->nrOcorrencia = $_POST['Ocorrencia'];
        $editarItem->vlUnitario = $ValorUnitario;
        $editarItem->qtDias = $_POST['QtdeDias'];
        $editarItem->dsJustificativa = utf8_decode($_POST['Justificativa']);
        $editarItem->idAgente = $idAgente;
        if($editarItem->tpAcao == 'N'){
            $editarItem->tpAcao = 'A';
        }
//        $editarItem->idAgente = $auth->getIdentity()->IdUsuario;
        $editarItem->save();
        
        echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!'));
        die();
    }
    
    /* 
     * Criada em 18/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada pelos técnicos, pareceristas e componentes da comissão para alterarem os itens da planilha orçamentária.
     */
    public function alteracoesTecnicasNoItemAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        $editarItem = $tbPlanilhaAprovacao->buscar(array('idPlanilhaAprovacao=?'=>$_POST['idPlanilha']))->current();
        //$editarItem->idAgente = $idAgente;
        if($editarItem->tpAcao == 'E'){
            $editarItem->tpAcao = 'N';
        } else if($editarItem->tpAcao == 'I'){
            $editarItem->delete();
        } else if($editarItem->tpAcao == 'A'){
            $itemAtivo = $tbPlanilhaAprovacao->buscar(array('idPlanilhaAprovacao=?'=>$editarItem->idPlanilhaAprovacaoPai))->current();
            $editarItem->idProduto = $itemAtivo->idProduto;
            $editarItem->idEtapa = $itemAtivo->idEtapa;
            $editarItem->idUnidade = $itemAtivo->idUnidade;
            $editarItem->qtItem = $itemAtivo->qtItem;
            $editarItem->nrOcorrencia = $itemAtivo->nrOcorrencia;
            $editarItem->vlUnitario = $itemAtivo->vlUnitario;
            $editarItem->qtDias = $itemAtivo->qtDias;
            $editarItem->qtDias = $itemAtivo->qtDias;
            $editarItem->tpDespesa = $itemAtivo->tpDespesa;
            $editarItem->tpPessoa = $itemAtivo->tpPessoa;
            $editarItem->nrContraPartida = $itemAtivo->nrContraPartida;
            $editarItem->nrFonteRecurso = $itemAtivo->nrFonteRecurso;
            $editarItem->idUFDespesa = $itemAtivo->idUFDespesa;
            $editarItem->idMunicipioDespesa = $itemAtivo->idMunicipioDespesa;
            $editarItem->idMunicipioDespesa = $itemAtivo->idMunicipioDespesa;
            $editarItem->tpAcao = 'N';
        }
        $editarItem->save();
        
        echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!'));
        die();
    }
    
    /* 
     * Criada em 18/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada pelos técnicos, pareceristas e componentes da comissão para alterarem os locais de realização.
     */
    public function alteracoesTecnicasNoLocalDeRealizacaoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $tbAbrangencia = new tbAbrangencia();
        $editarItem = $tbAbrangencia->buscar(array('idAbrangencia=?'=>$_POST['idAbrangencia']))->current();
        
        if($this->idPerfil == 94 || $this->idPerfil == 121){ //Parecerista e Técnico de Acompanhamento
            $editarItem->tpAnaliseTecnica = $_POST['tpAcao'];
            
        } else if($this->idPerfil == 118){ //Componente da Comissão
            $editarItem->tpAnaliseComissao = $_POST['tpAcao'];
            
        } else if($this->idPerfil == 1111){ //Proponente
            if($editarItem->tpSolicitacao == 'E'){
                $editarItem->tpSolicitacao = 'N';
            } else if($editarItem->tpSolicitacao == 'I'){
                $editarItem->delete();
                echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!')); die();
            }
        }
        $editarItem->save();
        
        echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!'));
        die();
    }
    
    /* 
     * Criada em 28/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada pelos técnicos, pareceristas e componentes da comissão para alterarem os planos de divulgação.
     */
    public function alteracoesTecnicasNoPlanoDeDivulgacaoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $tbPlanoDivulgacao = new tbPlanoDivulgacao();
        $editarItem = $tbPlanoDivulgacao->buscar(array('idPlanoDivulgacao=?'=>$_POST['idPlanoDivulgacao']))->current();
        
        if($this->idPerfil == 94 || $this->idPerfil == 121){ //Parecerista e Técnico de Acompanhamento
            $editarItem->tpAnaliseTecnica = $_POST['tpAcao'];
            
        } else if($this->idPerfil == 118){ //Componente da Comissão
            $editarItem->tpAnaliseComissao = $_POST['tpAcao'];
            
        } else if($this->idPerfil == 1111){ //Proponente
            if($editarItem->tpSolicitacao == 'E'){
                $editarItem->tpSolicitacao = 'N';
            } else if($editarItem->tpSolicitacao == 'I'){
                $editarItem->delete();
                echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!')); die();
            }
        }
        $editarItem->save();
        
        echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!'));
        die();
    }
    
    /* 
     * Criada em 1/04/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada pelos técnicos, pareceristas, proponente e componentes da comissão para alterarem os planos de distribuição.
     */
    public function alteracoesTecnicasNoPlanoDeDistribuicaoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $tbPlanoDistribuicao = new tbPlanoDistribuicao();
        $editarItem = $tbPlanoDistribuicao->buscar(array('idPlanoDistribuicao=?'=>$_POST['idPlanoDistribuicao']))->current();
        
        if($this->idPerfil == 94 || $this->idPerfil == 121){ //Parecerista e Técnico de Acompanhamento
            $editarItem->tpAnaliseTecnica = $_POST['tpAcao'];
            
        } else if($this->idPerfil == 118){ //Componente da Comissão
            $editarItem->tpAnaliseComissao = $_POST['tpAcao'];
            
        } else if($this->idPerfil == 1111){ //Proponente
            if($editarItem->tpSolicitacao == 'E'){
                $editarItem->tpSolicitacao = 'N';
            } else if($editarItem->tpSolicitacao == 'I'){
                $editarItem->delete();
                echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!')); die();
            }
        }
        $editarItem->save();
        
        echo json_encode(array('resposta'=>true, 'msg'=>'Dados salvos com sucesso!'));
        die();
    }
    
    public function criarCampoTipoReadequacaoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $get = Zend_Registry::get('get');
        $tipoReadequacao = $get->tpReadequacao;
        $valorPreCarregado = null;
//        x($tipoReadequacao);
        
        //ALTERAÇÃO DE RAZÃO SOCIAL DO PROPONENTE
        if($tipoReadequacao == 3){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscarDadosUC75($get->idPronac)->current();
            
            if($dadosProjeto){
                $valorPreCarregado = $dadosProjeto->Proponente;
            }
            
        } else if($tipoReadequacao == 4){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $ContaBancaria = new ContaBancaria();
                $dadosBancarios = $ContaBancaria->contaPorProjeto($get->idPronac);
                if(count($dadosBancarios)>0){
                    $valorPreCarregado = $dadosBancarios->Agencia;
                }
            }
            
        //SINOPSE DA OBRA
        } else if($tipoReadequacao == 5){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->Sinopse;
                }
            }
            
        //IMPACTO AMBIENTAL
        } else if($tipoReadequacao == 6){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->ImpactoAmbiental;
                }
            }
            
        //ESPECIFICACAO TECNICA
        } else if($tipoReadequacao == 7){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->EspecificacaoTecnica;
                }
            }
        
        //ESTRATÉGIA DE EXECUÇÃO
        } else if($tipoReadequacao == 8){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->EstrategiadeExecucao;
                }
            }
        
        //ALTERAÇÃO DE PROPONENTE
        } else if($tipoReadequacao == 10){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $valorPreCarregado = $dadosProjeto->CgcCpf;
            }
        
        //NOME DO PROJETO
        } else if($tipoReadequacao == 12){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $valorPreCarregado = $dadosProjeto->NomeProjeto;
            }
            
        //PERÍODO DE EXECUCAO
        } else if($tipoReadequacao == 13){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            $DtFimExecucao = Data::tratarDataZend($dadosProjeto->DtFimExecucao, 'brasileira');
            
            if($dadosProjeto){
                $valorPreCarregado = $DtFimExecucao;
            }
            
        //SÍNTESE DO PROJETO
        } else if($tipoReadequacao == 15){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $valorPreCarregado = $dadosProjeto->ResumoProjeto;
            }
            
        //OBJETIVOS
        } else if($tipoReadequacao == 16){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->Objetivos;
                }
            }
        
        //JUSTIFICATIVA
        } else if($tipoReadequacao == 17){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->Justificativa;
                }
            }
           
        //ACESSIBILIDADE
        } else if($tipoReadequacao == 18){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->Acessibilidade;
                }
            }
            
        //DEMOCRATIZACAO DE ACESSO
        } else if($tipoReadequacao == 19){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->DemocratizacaoDeAcesso;
                }
            }
            
        //ETAPAS DE TRABALHO
        } else if($tipoReadequacao == 20){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->EtapaDeTrabalho;
                }
            }
        
        //FICHA TECNICA
        } else if($tipoReadequacao == 21){
            $Projetos = new Projetos();
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$get->idPronac))->current();
            
            if($dadosProjeto){
                $PreProjeto = new PreProjeto();
                $dadosPreProjeto = $PreProjeto->buscar(array('idPreProjeto = ?'=>$dadosProjeto->idProjeto))->current();
                if($dadosPreProjeto){
                    $valorPreCarregado = $dadosPreProjeto->FichaTecnica;
                }
            }
        }
        
        $this->montaTela(
            'readequacoes/criar-campo-tipo-readequacao.phtml',
            array(
                'tipoReadequacao' => $tipoReadequacao,
                'valorPreCarregado' => $valorPreCarregado
            )
        );
    }
    
    public function carregarValorEntrePlanilhasAction() {
        $auth = Zend_Auth::getInstance(); // pega a autenticacao
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $get = Zend_Registry::get('get');
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        
        //BUSCAR VALOR TOTAL DA PLANILHA ATIVA
        $where = array();
        $where['a.IdPRONAC = ?'] = $idPronac;
        $where['a.stAtivo = ?'] = 'S';
        $PlanilhaAtiva = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();
        //x($PlanilhaAtiva->Total);
                
        //BUSCAR VALOR TOTAL DA PLANILHA DE READEQUADA
        $where = array();
        $where['a.IdPRONAC = ?'] = $idPronac;
        $where['a.tpPlanilha = ?'] = 'SR';
        $where['a.stAtivo = ?'] = 'N';
        $where['a.tpAcao != ?'] = 'E';
        $PlanilhaReadequada = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();
        
        if($PlanilhaReadequada->Total > 0){
            if($PlanilhaAtiva->Total == $PlanilhaReadequada->Total){
                $statusPlanilha = 'neutro';
            } else if($PlanilhaAtiva->Total > $PlanilhaReadequada->Total){
                $statusPlanilha = 'positivo';
            } else {
                $statusPlanilha = 'negativo';
            }
        } else {
            $PlanilhaAtiva->Total = 0;
            $PlanilhaReadequada->Total = 0;
            $statusPlanilha = 'neutro';
        }
        
        $this->montaTela(
            'readequacoes/carregar-valor-entre-planilhas.phtml', array(
            'statusPlanilha' => $statusPlanilha,
            'vlDiferencaPlanilhas' => 'R$ '.number_format(($PlanilhaReadequada->Total-$PlanilhaAtiva->Total), 2, ',', '.')
            )
        );
    }
    
    /* 
     * Criada em 19/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para carregar os dados do locais de realização do projeto.
     */
    public function carregarLocaisDeRealizacaoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        if(isset($_POST['iduf'])) {
            $iduf = $_POST['iduf'];
            $cidade = CidadeDAO::buscar($iduf);
            $a = 0;
            $cidadeArray = array();
            foreach($cidade as $DadosCidade) {
                $cidadeArray[$a]['idCidade'] = $DadosCidade->id;
                $cidadeArray[$a]['nomeCidade'] = utf8_encode($DadosCidade->descricao);
                $a++;
            }
            echo json_encode($cidadeArray);
            die;
        }
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbAbrangencia = new tbAbrangencia();
        $locais = $tbAbrangencia->buscarLocaisParaReadequacao($idPronac,'tbAbrangencia');
        
        if(count($locais)==0){
            $locais = $tbAbrangencia->buscarLocaisParaReadequacao($idPronac,'Abrangencia');
        }
        
        $tbPais = new Pais();
        $this->view->Paises = $tbPais->buscar(array(), array(3));
        
        $buscarEstado = EstadoDAO::buscar();
        $this->view->UFs = $buscarEstado;
            
        $get = Zend_Registry::get('get');
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'readequacoes/carregar-locais-de-realizacao.phtml',
            array(
                'idPronac' => $idPronac,
                'locaisDeRealizacao' => $locais,
                'link' => $link
            )
        );
    }
    
    /* 
     * Criada em 27/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para carregar os dados do locais de realização do projeto.
     */
    public function carregarLocaisDeRealizacaoReadequacoesAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        $idPronac = $this->_request->getParam("idPronac");
        $idReadequacao = $this->_request->getParam("idReadequacao");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbAbrangencia = new tbAbrangencia();
        $locais = $tbAbrangencia->buscarLocaisConsolidadoReadequacao($idReadequacao);
        
        $tbReadequacao = new tbReadequacao();
        $dadosReadequacao = $tbReadequacao->buscar(array('idReadequacao=?'=>$idReadequacao))->current();
        $siEncaminhamento = $dadosReadequacao->siEncaminhamento;
        
        $tbPais = new Pais();
        $this->view->Paises = $tbPais->buscar(array(), array(3));
        
        $buscarEstado = EstadoDAO::buscar();
        $this->view->UFs = $buscarEstado;
            
        $get = Zend_Registry::get('get');
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'readequacoes/carregar-locais-de-realizacao.phtml',
            array(
                'idPronac' => $idPronac,
                'locaisDeRealizacao' => $locais,
                'link' => $link,
                'idReadequacao' => $idReadequacao,
                'siEncaminhamento' => $siEncaminhamento
            )
        );
    }
    
    /* 
     * Criada em 10/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     */
    public function incluirLocalDeRealizacaoAction() {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        //VERIFICA SE JA POSSUI AS ABRANGENCIAS NA TABELA tbAbrangencia (READEQUACAO), SE NÃO TIVER, COPIA DA ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        $tbAbrangencia = new tbAbrangencia();
        $readequacaoLR = $tbAbrangencia->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S'));
        $locaisAtivos = $tbAbrangencia->buscarLocaisParaReadequacao($idPronac);
        
        if(count($readequacaoLR)==0){
            $locaisCopiados = array();
            foreach ($locaisAtivos as $value){
                $locaisCopiados['idReadequacao'] = NULL;
                $locaisCopiados['idPais'] = $value->idPais;
                $locaisCopiados['idUF'] = $value->idUF;
                $locaisCopiados['idMunicipioIBGE'] = $value->idCidade;
                $locaisCopiados['tpSolicitacao'] = 'N';
                $locaisCopiados['stAtivo'] = 'S';
                $locaisCopiados['idPronac'] = $idPronac;
                $tbAbrangencia->inserir($locaisCopiados);
            }
        }
        
        if($_POST['newPaisLR'] == 31){
            if(empty($_POST['newUFLR']) && empty($_POST['newMunicipioLR'])){
                $msg = utf8_encode('Ao escolher o Brasil, os campos de UF e Município se tornam obrigatórios no cadastro!');
                echo json_encode(array('resposta'=>false, 'msg'=>$msg)); die();
            }
            $verificaLocalRepetido = $tbAbrangencia->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S', 'idPais=?'=>$_POST['newPaisLR'], 'idUF=?'=>$_POST['newUFLR'], 'idMunicipioIBGE=?'=>$_POST['newMunicipioLR']));
        } else {
            $verificaLocalRepetido = $tbAbrangencia->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S', 'idPais=?'=>$_POST['newPaisLR']));
        }
        
        if(count($verificaLocalRepetido)==0){
            /* DADOS DO ITEM PARA INCLUSÃO DA READEQUAÇÃO */
            $dadosInclusao = array();
            $dadosInclusao['idReadequacao'] = NULL;
            $dadosInclusao['idPais'] = $_POST['newPaisLR'];
            $dadosInclusao['idUF'] = isset($_POST['newUFLR']) ? $_POST['newUFLR'] : 0;
            $dadosInclusao['idMunicipioIBGE'] = isset($_POST['newMunicipioLR']) ? $_POST['newMunicipioLR'] : 0;
            $dadosInclusao['tpSolicitacao'] = 'I';
            $dadosInclusao['stAtivo'] = 'S';
            $dadosInclusao['idPronac'] = $idPronac;
            $insert = $tbAbrangencia->inserir($dadosInclusao);
            if($insert){
                //$jsonEncode = json_encode($dadosPlanilha);
                echo json_encode(array('resposta'=>true));
            } else {
                echo json_encode(array('resposta'=>false));
            }
        } else {
            $msg = utf8_encode('Esse local de realização já foi cadastrado!');
            echo json_encode(array('resposta'=>false, 'msg'=>$msg));
        }
        die();
    }
    
    /* 
     * Criada em 25/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Esse função é usada pelo proponente para solicitar a exclusão de um local de realização.
     */
    public function excluirLocalDeRealizacaoAction() {
        $this->_helper->layout->disableLayout();
        $idAbrangencia = $this->_request->getParam("idAbrangencia");
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbAbrangencia = new tbAbrangencia();
        $readequacaoLR = $tbAbrangencia->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S'));
        
        //VERIFICA SE JA POSSUI AS ABRANGENCIAS NA TABELA tbAbrangencia (READEQUACAO), SE NÃO TIVER, COPIA DA ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        $locaisAtivos = $tbAbrangencia->buscarLocaisParaReadequacao($idPronac);
        if(count($readequacaoLR)==0){
            $locaisCopiados = array();
            foreach ($locaisAtivos as $value){
                $locaisCopiados['idReadequacao'] = NULL;
                $locaisCopiados['idPais'] = $value->idPais;
                $locaisCopiados['idUF'] = $value->idUF;
                $locaisCopiados['idMunicipioIBGE'] = $value->idCidade;
                $locaisCopiados['tpSolicitacao'] = 'N';
                $locaisCopiados['stAtivo'] = 'S';
                $locaisCopiados['idPronac'] = $idPronac;
                $tbAbrangencia->inserir($locaisCopiados);
            }
        }
        
        /* DADOS DO ITEM PARA EXCLUSAO LÓGICA DO ITEM DA READEQUACAO */
        $dados = array();
        $dados['tpSolicitacao'] = 'E';
        
        $itemLR = $tbAbrangencia->buscar(array('idAbrangencia=?'=>$idAbrangencia))->current();
        if($itemLR){
            if($itemLR->tpSolicitacao == 'I'){
                $exclusaoLogica = $tbAbrangencia->delete(array('idAbrangencia = ?'=>$idAbrangencia));
            } else {
                $where = "stAtivo = 'S' AND idAbrangencia = $idAbrangencia";
                $exclusaoLogica = $tbAbrangencia->update($dados, $where);
            }
        } else {
            $Abrangencia = new Abrangencia();
            $itemLR = $Abrangencia->find(array('idAbrangencia=?'=>$idAbrangencia))->current();
            $dadosArray = array(
                'idPais =?' => $itemLR->idPais,
                'idUF =?' => $itemLR->idUF,
                'idMunicipioIBGE =?' => $itemLR->idMunicipioIBGE,
                'idPronac =?' => $idPronac,
                'stAtivo =?' => 'S',
            );
            $itemLR = $tbAbrangencia->buscar($dadosArray)->current();
            $where = "stAtivo = 'S' AND idAbrangencia = $itemLR->idAbrangencia";
            $exclusaoLogica = $tbAbrangencia->update($dados, $where);
        }
        
        if($exclusaoLogica){
            //$jsonEncode = json_encode($dadosPlanilha);
            echo json_encode(array('resposta'=>true));

        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
    /* 
     * Criada em 28/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para carregar os dados do planos de divulgação do projeto.
     */
    public function carregarPlanosDeDivulgacaoAction(){
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        $tbPlanoDivulgacao = new tbPlanoDivulgacao();
        $planosDivulgacao = $tbPlanoDivulgacao->buscarPlanosDivulgacaoReadequacao($idPronac, 'tbPlanoDivulgacao');
        
        if(count($planosDivulgacao)==0){
            $planosDivulgacao = $tbPlanoDivulgacao->buscarPlanosDivulgacaoReadequacao($idPronac, 'PlanoDeDivulgacao');
        }
                
        $Verificacao = new Verificacao();
        $pecas = $Verificacao->buscar(array('idTipo=?'=>1), array('Descricao'));
        $veiculos = $Verificacao->buscar(array('idTipo=?'=>2), array('Descricao'));
            
        $get = Zend_Registry::get('get');
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'readequacoes/carregar-planos-de-divulgacao.phtml',
            array(
                'idPronac' => $idPronac,
                'planosDeDivulgacao' => $planosDivulgacao,
                'pecas' => $pecas,
                'veiculos' => $veiculos,
                'link' => $link
            )
        );
    }
    
    /* 
     * Criada em 27/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para carregar os dados dos planos de divulgação do projeto.
     */
    public function carregarPlanosDeDivulgacaoReadequacoesAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        $idPronac = $this->_request->getParam("idPronac");
        $idReadequacao = $this->_request->getParam("idReadequacao");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanoDivulgacao = new tbPlanoDivulgacao();
        $planosDivulgacao = $tbPlanoDivulgacao->buscarPlanosDivulgacaoConsolidadoReadequacao($idReadequacao);
        
        $tbReadequacao = new tbReadequacao();
        $dadosReadequacao = $tbReadequacao->buscar(array('idReadequacao=?'=>$idReadequacao))->current();
        $siEncaminhamento = $dadosReadequacao->siEncaminhamento;
                
        $Verificacao = new Verificacao();
        $pecas = $Verificacao->buscar(array('idTipo=?'=>1), array('Descricao'));
        $veiculos = $Verificacao->buscar(array('idTipo=?'=>2), array('Descricao'));
            
        $get = Zend_Registry::get('get');
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'readequacoes/carregar-planos-de-divulgacao.phtml',
            array(
                'idPronac' => $idPronac,
                'planosDeDivulgacao' => $planosDivulgacao,
                'pecas' => $pecas,
                'veiculos' => $veiculos,
                'link' => $link,
                'idReadequacao' => $idReadequacao,
                'siEncaminhamento' => $siEncaminhamento
            )
        );
    }
    
    /* 
     * Criada em 28/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para que o proponente faça uma solicitação de inclusão de plano de divulgação
     */
    public function incluirPlanosDeDivulgacaoAction() {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        //VERIFICA SE JA POSSUI OS PLANOS DE DIVULGAÇAO NA TABELA tPlanoDivulgacao (READEQUACAO), SE NÃO TIVER, COPIA DA ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        $tbPlanoDivulgacao = new tbPlanoDivulgacao();
        $readequacaoPDD = $tbPlanoDivulgacao->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S'));
        $planosAtivos = $tbPlanoDivulgacao->buscarPlanosDivulgacaoReadequacao($idPronac);
        
        if(count($readequacaoPDD)==0){
            $planosCopiados = array();
            foreach ($planosAtivos as $value){
                $planosCopiados['idReadequacao'] = NULL;
                $planosCopiados['idPeca'] = $value->idPeca;
                $planosCopiados['idVeiculo'] = $value->idVeiculo;
                $planosCopiados['tpSolicitacao'] = 'N';
                $planosCopiados['stAtivo'] = 'S';
                $planosCopiados['idPronac'] = $idPronac;
                $tbPlanoDivulgacao->inserir($planosCopiados);
            }
        }
        
        $verificaPlanoRepetido = $tbPlanoDivulgacao->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S', 'idPeca=?'=>$_POST['newPeca'], 'idVeiculo=?'=>$_POST['newVeiculo']));
        if(count($verificaPlanoRepetido)==0){
            /* DADOS DO PLANO PARA INCLUSÃO DA READEQUAÇÃO */
            $dadosInclusao = array();
            $dadosInclusao['idReadequacao'] = NULL;
            $dadosInclusao['idPeca'] = $_POST['newPeca'];
            $dadosInclusao['idVeiculo'] = $_POST['newVeiculo'];
            $dadosInclusao['tpSolicitacao'] = 'I';
            $dadosInclusao['stAtivo'] = 'S';
            $dadosInclusao['idPronac'] = $idPronac;
            $insert = $tbPlanoDivulgacao->inserir($dadosInclusao);
            if($insert){
                //$jsonEncode = json_encode($dadosPlanilha);
                echo json_encode(array('resposta'=>true));
        } else {
                echo json_encode(array('resposta'=>false));
        }
        } else {
            $msg = utf8_encode('Esse plano de divulgação já foi cadastrado!');
            echo json_encode(array('resposta'=>false, 'msg'=>$msg));
    }
        die();
    }
    
    /* 
     * Criada em 28/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Esse função é usada pelo proponente para solicitar a exclusão de um plano de divulgação.
     */
    public function excluirPlanoDeDivulgacaoAction() {
        $this->_helper->layout->disableLayout();
        $idPlanoDivulgacao = $this->_request->getParam("idPlanoDivulgacao");
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        //VERIFICA SE JA POSSUI OS PLANOS DE DIVULGAÇAO NA TABELA tPlanoDivulgacao (READEQUACAO), SE NÃO TIVER, COPIA DA ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        $tbPlanoDivulgacao = new tbPlanoDivulgacao();
        $readequacaoPDD = $tbPlanoDivulgacao->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S'));
        $planosAtivos = $tbPlanoDivulgacao->buscarPlanosDivulgacaoReadequacao($idPronac);
        
        if(count($readequacaoPDD)==0){
            $planosCopiados = array();
            foreach ($planosAtivos as $value){
                $planosCopiados['idReadequacao'] = NULL;
                $planosCopiados['idPeca'] = $value->idPeca;
                $planosCopiados['idVeiculo'] = $value->idVeiculo;
                $planosCopiados['tpSolicitacao'] = 'N';
                $planosCopiados['stAtivo'] = 'S';
                $planosCopiados['idPronac'] = $idPronac;
                $tbPlanoDivulgacao->inserir($planosCopiados);
            }
        }
        
        /* DADOS DO ITEM PARA EXCLUSAO LÓGICA DO ITEM DA READEQUACAO */
        $dados = array();
        $dados['tpSolicitacao'] = 'E';
        
        $itemPDD = $tbPlanoDivulgacao->buscar(array('idPlanoDivulgacao=?'=>$idPlanoDivulgacao))->current();
        if($itemPDD){
            if($itemPDD->tpSolicitacao == 'I'){
                $exclusaoLogica = $tbPlanoDivulgacao->delete(array('idPlanoDivulgacao = ?'=>$idPlanoDivulgacao));
            } else {
                $where = "stAtivo = 'S' AND idPlanoDivulgacao = $idPlanoDivulgacao";
                $exclusaoLogica = $tbPlanoDivulgacao->update($dados, $where);
            }
        } else {
            $PlanoDivulgacao = new PlanoDeDivulgacao();
            $itemPDD = $PlanoDivulgacao->find(array('idPlanoDivulgacao=?'=>$idPlanoDivulgacao))->current();
            $dadosArray = array(
                'idPeca =?' => $itemPDD->idPeca,
                'idVeiculo =?' => $itemPDD->idVeiculo,
                'idPronac =?' => $idPronac,
                'stAtivo =?' => 'S',
            );
            $itemPDD = $tbPlanoDivulgacao->buscar($dadosArray)->current();
            $where = "stAtivo = 'S' AND idPlanoDivulgacao = $itemPDD->idPlanoDivulgacao";
            $exclusaoLogica = $tbPlanoDivulgacao->update($dados, $where);
        }
        
        if($exclusaoLogica){
            //$jsonEncode = json_encode($dadosPlanilha);
            echo json_encode(array('resposta'=>true));

        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
    /* 
     * Criada em 31/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para carregar os dados do planos de distribuição do projeto.
     */
    public function carregarPlanosDeDistribuicaoAction(){
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanoDistribuicao = new tbPlanoDistribuicao();
        $planosDistribuicao = $tbPlanoDistribuicao->buscarPlanosDistribuicaoReadequacao($idPronac, 'tbPlanoDistribuicao');
        
        if(count($planosDistribuicao)==0){
            $planosDistribuicao = $tbPlanoDistribuicao->buscarPlanosDistribuicaoReadequacao($idPronac, 'PlanoDistribuicaoProduto');
        }
        
        $Produtos = new Produto();
        $produtos = $Produtos->buscar(array('stEstado=?'=>0), array('Descricao'));
        
        $Verificacao = new Verificacao();
        $posicoesLogomarca = $Verificacao->buscar(array('idTipo=?'=>3), array('Descricao'));
        
        $Area = new Area();
        $areas = $Area->buscar(array('Codigo != ?'=>7), array('Descricao'));
        
        $get = Zend_Registry::get('get');
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'readequacoes/carregar-planos-de-distribuicao.phtml',
            array(
                'idPronac' => $idPronac,
                'planosDeDistribuicao' => $planosDistribuicao,
                'produtos' => $produtos,
                'posicoesLogomarca' => $posicoesLogomarca,
                'areas' => $areas,
                'link' => $link
            )
        );
    }
    
    /* 
     * Criada em 27/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para carregar os dados dos planos de divulgação do projeto.
     */
    public function carregarPlanosDeDistribuicaoReadequacoesAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        $idPronac = $this->_request->getParam("idPronac");
        $idReadequacao = $this->_request->getParam("idReadequacao");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $tbPlanoDistribuicao = new tbPlanoDistribuicao();
        $planosDistribuicao = $tbPlanoDistribuicao->buscarPlanosDistribuicaoConsolidadoReadequacao($idReadequacao);
        
        $tbReadequacao = new tbReadequacao();
        $dadosReadequacao = $tbReadequacao->buscar(array('idReadequacao=?'=>$idReadequacao))->current();
        $siEncaminhamento = $dadosReadequacao->siEncaminhamento;
                
        $get = Zend_Registry::get('get');
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'readequacoes/carregar-planos-de-distribuicao.phtml',
            array(
                'idPronac' => $idPronac,
                'planosDeDistribuicao' => $planosDistribuicao,
                'link' => $link,
                'idReadequacao' => $idReadequacao,
                'siEncaminhamento' => $siEncaminhamento
            )
        );
    }
    
    /* 
     * Criada em 31/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função é usada para que o proponente faça uma solicitação de inclusão de plano de distribuição
     */
    public function incluirPlanosDeDistribuicaoAction() {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        //VERIFICA SE JA POSSUI OS PLANOS DE DISTRIBUIÇÃO NA TABELA tbPlanoDistribuicao (READEQUACAO), SE NÃO TIVER, COPIA DA ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        $tbPlanoDistribuicao = new tbPlanoDistribuicao();
        $readequacaoPDDist = $tbPlanoDistribuicao->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S'));
        $planosAtivos = $tbPlanoDistribuicao->buscarPlanosDistribuicaoReadequacao($idPronac);
        
        if(count($readequacaoPDDist)==0){
            $planosCopiados = array();
            foreach ($planosAtivos as $value){
                $planosCopiados['idReadequacao'] = NULL;
                $planosCopiados['idProduto'] = $value->idProduto;
                $planosCopiados['cdArea'] = $value->idArea;
                $planosCopiados['cdSegmento'] = $value->idSegmento;
                $planosCopiados['idPosicaoLogo'] = $value->idPosicaoDaLogo;
                $planosCopiados['qtProduzida'] = $value->QtdeProduzida;
                $planosCopiados['qtPatrocinador'] = $value->QtdePatrocinador;
                $planosCopiados['qtOutros'] = $value->QtdeOutros;
                $planosCopiados['qtProponente'] = $value->QtdeProponente;
                $planosCopiados['qtVendaNormal'] = $value->QtdeVendaNormal;
                $planosCopiados['qtVendaPromocional'] = $value->QtdeVendaPromocional;
                $planosCopiados['vlUnitarioNormal'] = $value->PrecoUnitarioNormal;
                $planosCopiados['vlUnitarioPromocional'] = $value->PrecoUnitarioPromocional;
                $planosCopiados['stPrincipal'] = $value->stPrincipal;
                $planosCopiados['tpSolicitacao'] = 'N';
                $planosCopiados['stAtivo'] = 'S';
                $planosCopiados['idPronac'] = $idPronac;
                $tbPlanoDistribuicao->inserir($planosCopiados);
            }
        }
        
        $verificaPlanoRepetido = $tbPlanoDistribuicao->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S', 'idProduto=?'=>$_POST['newPlanoDistribuicao']));
        if(count($verificaPlanoRepetido)==0){
            $QtdeProduzida = $_POST['newQntdNormal']+$_POST['newQntdPromocional']+$_POST['newQntdPatrocinador']+$_POST['newQntdPopulacaoBaixaRenda']+$_POST['newQntdDivulgacao'];
            $preconormal = str_replace(",", ".", str_replace(".", "", $_POST['newVlNormal']));
            $precopromocional = str_replace(",", ".", str_replace(".", "", $_POST['newVlPromocional']));
            
            /* DADOS DO PLANO PARA INCLUSÃO DA READEQUAÇÃO */
            $dadosInclusao = array();
            $dadosInclusao['idReadequacao'] = NULL;
            $dadosInclusao['idProduto'] = $_POST['newPlanoDistribuicao'];
            $dadosInclusao['cdArea'] = $_POST['newArea'];
            $dadosInclusao['cdSegmento'] = $_POST['newSegmento'];
            $dadosInclusao['idPosicaoLogo'] = $_POST['newPosicaoLogomarca'];
            $dadosInclusao['qtProduzida'] = $QtdeProduzida;
            $dadosInclusao['qtPatrocinador'] = $_POST['newQntdPatrocinador'];
            $dadosInclusao['qtProponente'] = $_POST['newQntdDivulgacao'];
            $dadosInclusao['qtOutros'] = $_POST['newQntdPopulacaoBaixaRenda'];
            $dadosInclusao['qtVendaNormal'] = $_POST['newQntdNormal'];
            $dadosInclusao['qtVendaPromocional'] = $_POST['newQntdPromocional'];
            $dadosInclusao['vlUnitarioNormal'] = $preconormal;
            $dadosInclusao['vlUnitarioPromocional'] = $precopromocional;
            $dadosInclusao['stPrincipal'] = 0;
            $dadosInclusao['tpSolicitacao'] = 'I';
            $dadosInclusao['stAtivo'] = 'S';
            $dadosInclusao['idPronac'] = $idPronac;
            $insert = $tbPlanoDistribuicao->inserir($dadosInclusao);
            
            if($insert){
                //$jsonEncode = json_encode($dadosPlanilha);
                echo json_encode(array('resposta'=>true));
            } else {
                echo json_encode(array('resposta'=>false));
            }
        } else {
            $msg = utf8_encode('Esse plano de distribuição já foi cadastrado!');
            echo json_encode(array('resposta'=>false, 'msg'=>$msg));
        }
        die();
    }
    
    /* 
     * Criada em 28/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Esse função é usada pelo proponente para solicitar a exclusão de um plano de distribuição.
     */
    public function excluirPlanoDeDistribuicaoAction() {
        $this->_helper->layout->disableLayout();
        $idPlanoDistribuicao = $this->_request->getParam("idPlanoDistribuicao");
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        //VERIFICA SE JA POSSUI OS PLANOS DE DISTRIBUIÇÃO NA TABELA tbPlanoDistribuicao (READEQUACAO), SE NÃO TIVER, COPIA DA ORIGINAL, E DEPOIS INCLUI O ITEM DESEJADO.
        $tbPlanoDistribuicao = new tbPlanoDistribuicao();
        $readequacaoPDDist = $tbPlanoDistribuicao->buscar(array('idPronac=?'=>$idPronac, 'stAtivo=?'=>'S'));
        $planosAtivos = $tbPlanoDistribuicao->buscarPlanosDistribuicaoReadequacao($idPronac);
        
        if(count($readequacaoPDDist)==0){
            $planosCopiados = array();
            foreach ($planosAtivos as $value){
                $planosCopiados['idReadequacao'] = NULL;
                $planosCopiados['idProduto'] = $value->idProduto;
                $planosCopiados['cdArea'] = $value->idArea;
                $planosCopiados['cdSegmento'] = $value->idSegmento;
                $planosCopiados['idPosicaoLogo'] = $value->idPosicaoDaLogo;
                $planosCopiados['qtProduzida'] = $value->QtdeProduzida;
                $planosCopiados['qtPatrocinador'] = $value->QtdePatrocinador;
                $planosCopiados['qtOutros'] = $value->QtdeOutros;
                $planosCopiados['qtProponente'] = $value->QtdeProponente;
                $planosCopiados['qtVendaNormal'] = $value->QtdeVendaNormal;
                $planosCopiados['qtVendaPromocional'] = $value->QtdeVendaPromocional;
                $planosCopiados['vlUnitarioNormal'] = $value->PrecoUnitarioNormal;
                $planosCopiados['vlUnitarioPromocional'] = $value->PrecoUnitarioPromocional;
                $planosCopiados['stPrincipal'] = $value->stPrincipal;
                $planosCopiados['tpSolicitacao'] = 'N';
                $planosCopiados['stAtivo'] = 'S';
                $planosCopiados['idPronac'] = $idPronac;
                $tbPlanoDistribuicao->inserir($planosCopiados);
            }
        }
        
        /* DADOS DO ITEM PARA EXCLUSAO LÓGICA DO ITEM DA READEQUACAO */
        $dados = array();
        $dados['tpSolicitacao'] = 'E';
        
        $itemPDDist = $tbPlanoDistribuicao->buscar(array('idPlanoDistribuicao=?'=>$idPlanoDistribuicao))->current();
        if($itemPDDist){
            if($itemPDDist->tpSolicitacao == 'I'){
                $exclusaoLogica = $tbPlanoDistribuicao->delete(array('idPlanoDistribuicao = ?'=>$idPlanoDistribuicao));
            } else {
                $where = "stAtivo = 'S' AND idPlanoDistribuicao = $idPlanoDistribuicao";
                $exclusaoLogica = $tbPlanoDistribuicao->update($dados, $where);
            }
        } else {
            $PlanoDistribuicao = new PlanoDistribuicao();
            $itemPDDist = $PlanoDistribuicao->find(array('idPlanoDistribuicao=?'=>$idPlanoDistribuicao))->current();
            $dadosArray = array(
                'idProduto =?' => $itemPDDist->idProduto,
                'idPronac =?' => $idPronac,
                'stAtivo =?' => 'S',
            );
            $itemPDDist = $tbPlanoDistribuicao->buscar($dadosArray)->current();
            $where = "stAtivo = 'S' AND idPlanoDistribuicao = $itemPDDist->idPlanoDistribuicao";
            $exclusaoLogica = $tbPlanoDistribuicao->update($dados, $where);
        }
        
        if($exclusaoLogica){
            //$jsonEncode = json_encode($dadosPlanilha);
            echo json_encode(array('resposta'=>true));

        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
    /* 
     * Criada em 14/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função para cadastrar uma solicitação de readequação.
     */
    public function incluirSolicitacaoReadequacaoAction() {
        //FUNÇÃO ACESSADA SOMENTE PELO PROPONENTE.
        if($this->idPerfil != 1111){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $post = Zend_Registry::get('post');
        $tbReadequacao = new tbReadequacao();
        $busca = array();
        $busca['idPronac =?'] = $idPronac;
        $busca['idTipoReadequacao =?'] = $post->tipoReadequacao;
        $busca['siEncaminhamento =?'] = 12;
        $dadosCadastrados = $tbReadequacao->buscar($busca)->current();
        
        if(count($dadosCadastrados)>0){
            parent::message('Tipo de solicitação de readequação já cadastrada!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
        }
        
        if($post->tipoReadequacao == 2){ //Planilha Orçamentária
            $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
            $planilhaReadequada = $tbPlanilhaAprovacao->buscar(array('IdPRONAC = ?'=>$idPronac, 'tpPlanilha = ?'=>'SR' ,'idReadequacao is null'=>''));
            if(count($planilhaReadequada)==0){
                parent::message('Não houve nenhuma alteração na planilha orçamentária do projeto!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
            }
        } else if($post->tipoReadequacao == 9){ //Local de Realização
            $tbAbrangencia = new tbAbrangencia();
            $locaisReadequados = $tbAbrangencia->buscar(array('idPronac = ?'=>$idPronac, 'idReadequacao is null'=>''));
            if(count($locaisReadequados)==0){
                parent::message('Não houve nenhuma alteração nos locais de realização do projeto!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
            }
        } else if($post->tipoReadequacao == 11){ //Planos de Distribuição
            $tbPlanoDistribuicao = new tbPlanoDistribuicao();
            $planosReadequados = $tbPlanoDistribuicao->buscar(array('idPronac = ?'=>$idPronac, 'idReadequacao is null'=>''));
            if(count($planosReadequados)==0){
                parent::message('Não houve nenhuma alteração nos planos de distribuição do projeto!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
            }
        } else if($post->tipoReadequacao == 14){ //Planos de Divulgação
            $tbPlanoDivulgacao = new tbPlanoDivulgacao();
            $planosReadequados = $tbPlanoDivulgacao->buscar(array('idPronac = ?'=>$idPronac, 'idReadequacao is null'=>''));
            if(count($planosReadequados)==0){
                parent::message('Não houve nenhuma alteração nos planos de divulgação do projeto!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
            }
        }
        
        try {
            
            $tbArquivoDAO = new tbArquivo();
            $tbArquivoImagemDAO = new tbArquivoImagem();
            $tbDocumentoDAO = new tbDocumento();

            // ==================== Dados do arquivo de upload ===============================			
            $arquivoNome = $_FILES['arquivo']['name']; // nome
            $arquivoTemp = $_FILES['arquivo']['tmp_name']; // nome temporï¿½rio
            $arquivoTipo = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho = $_FILES['arquivo']['size']; // tamanho

            $idDocumento = null;
            if(!empty($arquivoTemp)){
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensï¿½o
                $arquivoBinario = Upload::setBinario($arquivoTemp); // binï¿½rio
                $arquivoHash = Upload::setHash($arquivoTemp); // hash

                if ($arquivoExtensao != 'pdf' && $arquivoExtensao != 'PDF') // extensão do arquivo
                {
                    throw new Exception('A extensão do arquivo é inválida, envie somente arquivos <strong>.pdf</strong>!');
                }
                else if ($arquivoTamanho > 5242880) // tamanho máximo do arquivo: 5MB
                {
                    throw new Exception('O arquivo não pode ser maior do que <strong>5MB</strong>!');
                }

                $dadosArquivo = array(
                    'nmArquivo' => $arquivoNome,
                    'sgExtensao' => $arquivoExtensao,
                    'dsTipoPadronizado' => $arquivoTipo,
                    'nrTamanho' => $arquivoTamanho,
                    'dtEnvio' => new Zend_Db_Expr('GETDATE()'),
                    'dsHash' => $arquivoHash,
                    'stAtivo' => 'A'
                );
                $idArquivo = $tbArquivoDAO->inserir($dadosArquivo);

                // ==================== Insere na Tabela tbArquivoImagem ===============================
                $dadosBinario = array(
                    'idArquivo' => $idArquivo,
                    'biArquivo' => new Zend_Db_Expr("CONVERT(varbinary(MAX), {$arquivoBinario})")
                );
                $idArquivo = $tbArquivoImagemDAO->inserir($dadosBinario);

                // ==================== Insere na Tabela tbDocumento ===============================
                $dados = array(
                        'idTipoDocumento' => 38,
                        'idArquivo' => $idArquivo,
                        'dsDocumento' => 'Solicitação de Readequação',
                        'dtEmissaoDocumento' => NULL,
                        'dtValidadeDocumento' => NULL,
                        'idTipoEventoOrigem' => NULL,
                        'nmTitulo' => 'Readequacao'
                );
            
                $idDocumento = $tbDocumentoDAO->inserir($dados);
                $idDocumento = $idDocumento['idDocumento'];
            }
            
            $auth = Zend_Auth::getInstance(); // pega a autenticação
            $tblAgente = new Agentes();
            $rsAgente = $tblAgente->buscar(array('CNPJCPF=?'=>$auth->getIdentity()->Cpf))->current();
                
            $dados = array();
            $dados['idPronac'] = $idPronac;
            $dados['idTipoReadequacao'] = $post->tipoReadequacao;
            $dados['dtSolicitacao'] = new Zend_Db_Expr('GETDATE()');
            $dados['idSolicitante'] = $rsAgente->idAgente;
            $dados['dsJustificativa'] = $_POST['descJustificativa'];
            $dados['dsSolicitacao'] = $_POST['descSolicitacao'];
            $dados['idDocumento'] = $idDocumento;
            $dados['siEncaminhamento'] = 12;
            $dados['stEstado'] = 0;
            $idReadequacao = $tbReadequacao->inserir($dados);
            
            $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
            $dadosPlanilha = array();
            $dadosPlanilha['idReadequacao'] = $idReadequacao;
            $wherePlanilha = "IdPRONAC = $idPronac AND tpPlanilha = 'SR' AND idReadequacao is null";
            $tbPlanilhaAprovacao->update($dadosPlanilha, $wherePlanilha);
            
            if ($idReadequacao) {
                parent::message('Solicitação cadastrada com sucesso!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "CONFIRM");
            } else {
                throw new Exception("Erro ao cadastrar a readequação!");
            }
        } // fecha try
        catch(Exception $e) {
            parent::message($e->getMessage(), "readequacoes?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
        }
    }
    
    /* 
     * Criada em 14/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Essa função para excluir uma solicitação de readequação.
     */
    public function excluirSolicitacaoReadequacaoAction() {
        //FUNÇÃO ACESSADA SOMENTE PELO PROPONENTE.
        if($this->idPerfil != 1111){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        $get = Zend_Registry::get('get');
        try {
            
            $tbReadequacao = new tbReadequacao();
            $dados = $tbReadequacao->buscar(array('idReadequacao =?'=>$get->idReadequacao))->current();

            if(!empty($dados->idDocumento)){
                $tbDocumento = new tbDocumento();
                $dadosArquivo = $tbDocumento->buscar(array('idDocumento =?'=>$dados->idDocumento))->current();
                
                if($dadosArquivo){
//                    $vwAnexarComprovantes = new vwAnexarComprovantes();
//                    $x = $vwAnexarComprovantes->excluirArquivo($dadosArquivo->idArquivo);
                    
                    $tbDocumento = new tbDocumento();
                    $tbDocumento->excluir("idArquivo = {$dadosArquivo->idArquivo} and idDocumento= {$dados->idDocumento} ");

                    $tbArquivoImagem = new tbArquivoImagem();
                    $tbArquivoImagem->excluir("idArquivo =  {$dadosArquivo->idArquivo} ");

                    $tbArquivo = new tbArquivo();
                    $tbArquivo->excluir("idArquivo = {$dadosArquivo->idArquivo} ");
                }
            }
            
            //Se for readequação de planilha orçamentária, exclui a planilha SR gerada.
            if($dados->idTipoReadequacao == 2){
                $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
                $tbPlanilhaAprovacao->delete(array('IdPRONAC = ?'=>$idPronac, 'tpPlanilha = ?'=>'SR', 'idReadequacao = ?'=>$get->idReadequacao));
            }
            
            //Se for readequação de local de realização, exclui os registros na tbAbrangencia.
            if($dados->idTipoReadequacao == 9){
                $tbAbrangencia = new tbAbrangencia();
                $tbAbrangencia->delete(array('idPronac = ?'=>$idPronac, 'stAtivo = ?'=>'S'));
            }
            
            //Se for readequação de plano de distribuição, exclui os registros na tbPlanoDistribuicao.
            if($dados->idTipoReadequacao == 11){
                $tbPlanoDistribuicao = new tbPlanoDistribuicao();
                $tbPlanoDistribuicao->delete(array('idPronac = ?'=>$idPronac, 'stAtivo = ?'=>'S'));
            }
            
            //Se for readequação de plano de divulgação, exclui os registros na tbPlanoDivulgacao.
            if($dados->idTipoReadequacao == 14){
                $tbPlanoDivulgacao = new tbPlanoDivulgacao();
                $tbPlanoDivulgacao->delete(array('idPronac = ?'=>$idPronac, 'stAtivo = ?'=>'S'));
            }
            
            $exclusao = $tbReadequacao->delete(array('idPronac =?'=>$idPronac, 'idReadequacao =?'=>$get->idReadequacao));
            
            if ($exclusao) {
                parent::message('Tipo de readequação excluída com sucesso!', "readequacoes/index?idPronac=".Seguranca::encrypt($idPronac), "CONFIRM");
            } else {
                throw new Exception("Erro ao excluir o tipo de readequação!");
            }
        } // fecha try
        catch(Exception $e) {
            parent::message($e->getMessage(), "readequacoes?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
        }
    }
    
    public function finalizarSolicitacaoReadequacaoAction() {
        //FUNÇÃO ACESSADA SOMENTE PELO PROPONENTE.
        if($this->idPerfil != 1111){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idPronac = $this->_request->getParam("idPronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        try {
            $tbReadequacao = new tbReadequacao();
            $readequacoes = $tbReadequacao->buscar(array('idPronac=?'=>$idPronac, 'siEncaminhamento=?'=>12,'stEstado=?'=>0));
            
            $dados = array();
            $dados['siEncaminhamento'] = 1;
            $where = "idPronac = $idPronac AND siEncaminhamento = 12 AND stEstado = 0";
            $atualizar = $tbReadequacao->update($dados, $where);
            
            foreach ($readequacoes as $r) {
                if($r->idTipoReadequacao == 9){
                    $tbAbrangencia = new tbAbrangencia();
                    $dadosAb = array();
                    $dadosAb['idReadequacao'] = $r->idReadequacao;
                    $whereAb = "idPronac = $idPronac AND stAtivo = 'S' AND idReadequacao is null";
                    $tbAbrangencia->update($dadosAb, $whereAb);
                    
                } else if($r->idTipoReadequacao == 11){
                    $tbPlanoDistribuicao = new tbPlanoDistribuicao();
                    $dadosPDDist = array();
                    $dadosPDDist['idReadequacao'] = $r->idReadequacao;
                    $wherePDDist = "idPronac = $idPronac AND stAtivo = 'S' AND idReadequacao is null";
                    $tbPlanoDistribuicao->update($dadosPDDist, $wherePDDist);
                    
                } else if($r->idTipoReadequacao == 14){
                    $tbPlanoDivulgacao = new tbPlanoDivulgacao();
                    $dadosPDD = array();
                    $dadosPDD['idReadequacao'] = $r->idReadequacao;
                    $wherePDD = "idPronac = $idPronac AND stAtivo = 'S' AND idReadequacao is null";
                    $tbPlanoDivulgacao->update($dadosPDD, $wherePDD);
                }
            }
            if($atualizar) {
                //altera a situação do projeto
                //$alterarSituacao = ProjetoDAO::alterarSituacao($idPronac, 'D20');
                parent::message('Solicitação enviada com sucesso!', "consultardadosprojeto/index?idPronac=".Seguranca::encrypt($idPronac), "CONFIRM");
            } // fecha if
            else {
                throw new Exception("Erro ao finalizar as readequações!");
            }
        } // fecha try
        catch(Exception $e) {
            parent::message($e->getMessage(), "readequacoes?idPronac=".Seguranca::encrypt($idPronac), "ERROR");
        }
    }
    
    /* 
     * Alterada em 15/05/15
     */
    public function painelAction(){
        //FUNÇÃO ACESSADA SOMENTE PELOS PERFIS DE COORD. GERAL DE ACOMPANHAMENTO E COORD. DE ACOMPANHAMENTO.
        if($this->idPerfil != 122 && $this->idPerfil != 123){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
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
            $order = array(5); //Dt. Solicitação
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();

        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            $this->view->filtro = $filtro;
            switch ($filtro) {
                case '':
                    $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
                    $where['a.siEncaminhamento = ?'] = 1; // 1=Solicitado pelo proponente
                    break;
                case 'encaminhados':
                    $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
                    $where['a.siEncaminhamento in (?)'] = array(3,4,5,7); // 4=Encaminhado para Parecerista/Técnico; 5=Devolvido do Parecerista para o Coordenador da Unidade de Análise; 7=Encaminhado para o Componente da Comissão
                    $this->view->nmPagina = 'Em análise';
                    break;
                case 'analisados':
                    $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
                    $where['a.siEncaminhamento in (?)'] = array(6,10); // 6=Devolvido da Unidade de Analise para o MinC; 10=Devolvido pelo Tecnico para o Coordenador
                    $this->view->nmPagina = 'Analisados';
                    break;
            }
        } else {
            $this->view->nmPagina = 'Aguardando Análise';
            $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
            $where['a.siEncaminhamento = ?'] = 1; // 1=Solicitado pelo proponente
        }

        if((isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['b.AnoProjeto+b.Sequencial = ?'] = $_GET['pronac'];
            $this->view->pronac = $_GET['pronac'];
        }
        
        $Orgaos = new Orgaos();
        $idSecretaria = $Orgaos->buscar(array('codigo = ?'=>$this->idOrgao))->current();
        
        if(isset($idSecretaria) && !empty($idSecretaria)){
            if($idSecretaria->idSecretaria == 251){
                $where['b.Area <> ?'] = 2;
            } else if($idSecretaria->idSecretaria == 160){
                $where['b.Area = ?'] = 2;
            }
        }
        
        $tbReadequacao = New tbReadequacao();
        $total = $tbReadequacao->painelReadequacoes($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;
        
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $tbReadequacao->painelReadequacoes($where, $order, $tamanho, $inicio);
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
        
        $tbTitulacaoConselheiro = new tbTitulacaoConselheiro();
        $this->view->conselheiros = $tbTitulacaoConselheiro->buscarConselheirosTitularesTbUsuarios();
        
        $this->view->paginacao     = $paginacao;
        $this->view->qtdRegistros  = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
	}
    
    /* 
     * Alterada em 15/05/15
     */
    public function imprimirReadequacoesAction(){
        
        //FUNÇÃO ACESSADA SOMENTE PELOS PERFIS DE COORD. GERAL DE ACOMPANHAMENTO E COORD. DE ACOMPANHAMENTO.
        if($this->idPerfil != 122 && $this->idPerfil != 123){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
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
            $order = array(5); //Dt. Solicitação
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();

        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            $this->view->filtro = $filtro;
            switch ($filtro) {
                case '':
                    $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
                    $where['a.siEncaminhamento = ?'] = 1; // 1=Solicitado pelo proponente
                    break;
                case 'encaminhados':
                    $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
                    $where['a.siEncaminhamento in (?)'] = array(4,5,7); // 4=Encaminhado para Parecerista/Técnico; 5=Devolvido do Parecerista para o Coordenador da Unidade de Análise; 7=Encaminhado para o Componente da Comissão
                    $this->view->nmPagina = 'Em análise';
                    break;
                case 'analisados':
                    $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
                    $where['a.siEncaminhamento in (?)'] = array(6,10); // 6=Devolvido da Unidade de Analise para o MinC; 10=Devolvido pelo Tecnico para o Coordenador
                    $this->view->nmPagina = 'Analisados';
                    break;
            }
        } else {
            $this->view->nmPagina = 'Aguardando Análise';
            $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
            $where['a.siEncaminhamento = ?'] = 1; // 1=Solicitado pelo proponente
        }

        if((isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['b.AnoProjeto+b.Sequencial = ?'] = $_GET['pronac'];
            $this->view->pronac = $_GET['pronac'];
        }
        
        $Orgaos = new Orgaos();
        $idSecretaria = $Orgaos->buscar(array('codigo = ?'=>$this->idOrgao))->current();
        
        if(isset($idSecretaria) && !empty($idSecretaria)){
            if($idSecretaria->idSecretaria == 251){
                $where['b.Area <> ?'] = 2;
            } else if($idSecretaria->idSecretaria == 160){
                $where['b.Area = ?'] = 2;
            }
        }
        
        $tbReadequacao = New tbReadequacao();
        $total = $tbReadequacao->painelReadequacoes($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;
        
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $tbReadequacao->painelReadequacoes($where, $order, $tamanho, $inicio);

        $this->view->qtdRegistros = $total;
        $this->view->dados = $busca;
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
    }
    
    /* 
     * Alterada em 19/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função usada para o coordenador de acompanhamento deferir ou não a solicitação de readequação.
     */
    public function avaliarReadequacaoAction() {
        //FUNÇÃO ACESSADA SOMENTE PELOS PERFIS DE COORD. GERAL DE ACOMPANHAMENTO E COORD. DE ACOMPANHAMENTO.
        if($this->idPerfil != 122 && $this->idPerfil != 123){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idReadequacao = Seguranca::dencrypt($_GET['id']);
        
        $tbReadequacao = new tbReadequacao();
        $r = $tbReadequacao->buscarDadosReadequacoes(array('idReadequacao = ?'=>$idReadequacao))->current();
        
        if($r){
            $Projetos = new Projetos();
            $p = $Projetos->buscarProjetoXProponente(array('idPronac = ?' => $r->idPronac))->current();

            $this->view->readequacao = $r;
            $this->view->projeto = $p;
            $this->view->idPronac = $r->idPronac;
        } else {
            parent::message('Nenhum registro encontrado.', "readequacoes/painel", "ERROR");
        }
    }
    
    /* 
     * Alterada em 06/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     */
    public function buscarDestinatariosAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $vinculada = $_POST['vinculada'];
        $idPronac = $_POST['idPronac'];
        
        $a = 0;
        $dadosUsuarios = array();
        
        if($vinculada == 171 || $vinculada == 262){
            $dados = array();
            $dados['sis_codigo = ?'] = 21;
            $dados['uog_status = ?'] = 1;
            $dados['gru_codigo = ?'] = 121; //Tecnico de Acompanhamento
            if($vinculada == 171){
                $dados['org_superior = ?'] = 160;
            } else {
                $dados['org_superior = ?'] = 251;
            }
            
            $vw = new vwUsuariosOrgaosGrupos();
            $result = $vw->buscar($dados, array('usu_nome'));
            
            if(count($result) > 0){
                foreach ($result as $registro) {
                    $dadosUsuarios[$a]['id'] = $registro['usu_codigo'];
                    $dadosUsuarios[$a]['nome'] = utf8_encode($registro['usu_nome']);
                    $a++;
                }
                $jsonEncode = json_encode($dadosUsuarios);
                echo json_encode(array('resposta'=>true,'conteudo'=>$dadosUsuarios));

            } else {
                echo json_encode(array('resposta'=>false));
            }
            
        } else { //CNIC
            $tbTitulacaoConselheiro = new tbTitulacaoConselheiro();
            $result = $tbTitulacaoConselheiro->buscarConselheirosTitularesTbUsuarios();
            
            if(count($result) > 0){
                foreach ($result as $registro) {
                    $dadosUsuarios[$a]['id'] = $registro['id'];
                    $dadosUsuarios[$a]['nome'] = utf8_encode($registro['nome']);
                    $a++;
                }
                $jsonEncode = json_encode($dadosUsuarios);
                echo json_encode(array('resposta'=>true,'conteudo'=>$dadosUsuarios));

            } else {
                echo json_encode(array('resposta'=>false));
            }
        }
        die();
    }
    
    /* 
     * Alterada em 11/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * @description: Função utilizada pleo Coord. de Acompanhamento para avaliar a readequação e encaminhá-la.
     */
    public function salvarAvaliacaoAction() {
        
        //FUNÇÃO ACESSADA SOMENTE PELOS PERFIS DE COORD. GERAL DE ACOMPANHAMENTO E COORD. DE ACOMPANHAMENTO.
        if($this->idPerfil != 122 && $this->idPerfil != 123){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idReadequacao = $_POST['idReadequacao'];
        
        $tbReadequacao = new tbReadequacao();
        $r = $tbReadequacao->find(array('idReadequacao = ?'=>$idReadequacao))->current();
        $stValidacaoCoordenador = 0;
        $dataEnvio = null;
        
        if($r){
            $r->stAtendimento = $_POST['stAtendimento'];
            $r->dsAvaliacao = $_POST['dsAvaliacao'];
            $r->dtAvaliador = new Zend_Db_Expr('GETDATE()');
            $r->idAvaliador = $this->idUsuario;
            
            if($_POST['stAtendimento'] == 'I'){
                $r->siEncaminhamento = 2; //2=Solicitação indeferida
                $r->stEstado = 1;
            } else {
                if($_POST['vinculada'] == 262 || $_POST['vinculada'] == 171){
                    $r->siEncaminhamento = 4; //4=Enviado para Análise Técnica (SAV, SEFIC)
                    $dataEnvio = new Zend_Db_Expr('GETDATE()');
                } else if($_POST['vinculada'] == 400) {
                    $stValidacaoCoordenador = 1;
                    $r->siEncaminhamento = 7; //7=CNIC
                    $r->idAvaliador = $_POST['destinatario'];
                } else {
                    $r->siEncaminhamento = 3; //3=Enviado para o coordenador de parecer
                }
            }
            $r->save();
            
            if($_POST['stAtendimento'] == 'D'){
                $tbDistribuirReadequacao = new tbDistribuirReadequacao();
                $dados = array(
                    'idReadequacao' => $r->idReadequacao,
                    'idUnidade' => $_POST['vinculada'],
                    'DtEncaminhamento' => new Zend_Db_Expr('GETDATE()'),
                    'idAvaliador' => isset($_POST['destinatario']) ? $_POST['destinatario'] : null,
                    'dtEnvioAvaliador' => !empty($dataEnvio) ? $dataEnvio : null,
                    'stValidacaoCoordenador' => $stValidacaoCoordenador
                );
                $tbDistribuirReadequacao->inserir($dados);
            }
            
            parent::message('Dados salvos com sucesso!', "readequacoes/painel", "CONFIRM");
        
        } else {
            parent::message('Nenhum registro encontrado.', "readequacoes/painel", "ERROR");
        }
        
    }
    
    /* 
     * Alterada em 11/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * FUNÇÃO ACESSADA PELO TECNICO DE ACOMPANHAMENTO
    */
    public function painelReadequacoesAction() {
        
        //FUNÇÃO ACESSADA SOMENTE PELOS PERFIS DE COORD. DE PARECER, PARECERISTA E TECNICO DE ACOMPANHAMENTO.
        if($this->idPerfil != 93 && $this->idPerfil != 94 && $this->idPerfil != 121){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
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
            $order = array(1); //idDistribuirReadequacao
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['b.stEstado = ?'] = 0;
        $where['a.stValidacaoCoordenador = ?'] = 0;

        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            $this->view->filtro = $filtro;
            switch ($filtro) {
                case '':
                    if($this->idPerfil == 93){ //Coord. de Parecer
                        $where['b.siEncaminhamento = ?'] = 3;
                    } else if($this->idPerfil == 94 || $this->idPerfil == 121){
                        $where['b.siEncaminhamento = ?'] = 4;
                        $where['a.idAvaliador = ?'] = $this->idUsuario;
                    }
                    break;
                case 'analisados':
                    $where['b.siEncaminhamento = ?'] = 5;
                    $this->view->nmPagina = 'Analisados';
                    break;
            }
        } else {
            $this->view->nmPagina = 'Aguardando Análise';
            if($this->idPerfil == 93){
                $where['b.siEncaminhamento = ?'] = 3;
            } else if($this->idPerfil == 94 || $this->idPerfil == 121){
                $where['b.siEncaminhamento = ?'] = 4;
                    $where['a.idAvaliador = ?'] = $this->idUsuario;
                }
            }
        
        if((isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['c.AnoProjeto+c.Sequencial = ?'] = $_GET['pronac'];
            $this->view->pronac = $_GET['pronac'];
        }
        
        $Orgaos = new Orgaos();
        $idSecretaria = $Orgaos->buscar(array('codigo = ?'=>$this->idOrgao))->current();
        
        if(isset($idSecretaria) && !empty($idSecretaria)){
            if($idSecretaria->idSecretaria == 251){
                $where['c.Area <> ?'] = 2;
            } else if($idSecretaria->idSecretaria == 160){
                $where['c.Area = ?'] = 2;
            }
        }
        
        $tbReadequacao = New tbReadequacao();
        $total = $tbReadequacao->painelReadequacoesAnalise($where, $order, null, null, true, $this->idPerfil);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $tbReadequacao->painelReadequacoesAnalise($where, $order, $tamanho, $inicio, false, $this->idPerfil);
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
        $this->view->qtdRegistros  = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
        $this->view->idPerfil      = $this->idPerfil;
        $this->view->idOrgao       = $this->idOrgao;
    }
    
    /* 
     * Alterada em 12/03/14
     * Função acessada pelo Coordenador de Parecer, Coordenador de Acompanhamento, e Coordenador Geral de Acomapanhamento.
    */
    public function visualizarReadequacaoAction() {
        
        //FUNÇÃO ACESSADA SOMENTE PELOS PERFIS DE COORD. DE PARECER, COORD. DE ACOMPANHAMETO E COORD. GERAL DE ACOMPANHAMENTO.
        if($this->idPerfil != 93 && $this->idPerfil != 122 && $this->idPerfil != 123){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $get = Zend_Registry::get('get');
        $id = Seguranca::dencrypt($get->id);
        
        $tbReadequacao = new tbReadequacao();
        $d = $tbReadequacao->visualizarReadequacao(array('a.idReadequacao = ?'=>$id))->current();
        $this->view->dados = $d;
        
        $Projetos = new Projetos();
        $p = $Projetos->buscarProjetoXProponente(array('idPronac = ?' => $d->idPronac))->current();
        $this->view->projeto = $p;
        
        $tbReadequacaoXParecer = new tbReadequacaoXParecer();
        $this->view->Parecer = $tbReadequacaoXParecer->buscarPareceresReadequacao(array('a.idReadequacao = ?'=>$id, 'idTipoAgente =?'=>1))->current();
    }
    
     /* 
     * Alterada em 13/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelo Coordenador de Parecer para encaminhar a readequação para o Parecerista.
    */
    public function encaminharReadequacaoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        //$vinculada = $this->idOrgao;
        
        $post = Zend_Registry::get('post');
        $idAvaliador = (int) $post->parecerista;
        $idDistRead = (int) $post->idDistRead;
        $idReadequacao = (int) $post->idReadequacao;
        
        //Atualiza a tabela tbDistribuirReadequacao
        $dados = array();
        $dados['idAvaliador'] = $idAvaliador;
        $dados['DtEnvioAvaliador'] = new Zend_Db_Expr('GETDATE()');
        $where = "idDistribuirReadequacao = $idDistRead";
        $tbDistribuirReadequacao = new tbDistribuirReadequacao();
        $return = $tbDistribuirReadequacao->update($dados, $where);
        
        //Atualiza a tabela tbReadequacao
        $dados = array();
        $dados['siEncaminhamento'] = 4; // Enviado para análise técnica
        $where = "idReadequacao = $idReadequacao";
        $tbReadequacao = new tbReadequacao();
        $return2 = $tbReadequacao->update($dados, $where);
        
        if($return && $return2){
            echo json_encode(array('resposta'=>true));
        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
    /* 
     * Criada em 12/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelo Parecerista ou Técnico de acompanhamento para avaliar a readequação.
    */
    public function formAvaliarReadequacaoAction(){

        if($this->idPerfil != 94 && $this->idPerfil != 121){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $get = Zend_Registry::get('get');
        $idReadequacao = (int) Seguranca::dencrypt($get->id);
        $this->view->idReadequacao = $idReadequacao;
        
        $tbReadequacao = new tbReadequacao();
        $dados = $tbReadequacao->buscarDadosReadequacoes(array('idReadequacao = ?'=>$idReadequacao))->current();
        if(!$dados){
            parent::message("Readequação não encontrada!", "readequacoes/painel-readequacoes", "ERROR");
        }
        $this->view->dados = $dados;
        $this->view->idPronac = $dados->idPronac;
        
        $this->view->nmPagina = $dados->dsReadequacao;
        $d = array();
        $d['ProvidenciaTomada'] = 'Readequação enviado para avaliação técnica.';
        $d['dtSituacao'] = new Zend_Db_Expr('GETDATE()');
        $where = "IdPRONAC = $dados->idPronac";
        $Projetos = new Projetos();
        $Projetos->update($d, $where);
                
        $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
        $this->view->Unidade = $buscarUnidade;
                
        $tbReadequacaoXParecer = new tbReadequacaoXParecer();
        $this->view->Parecer = $tbReadequacaoXParecer->buscarPareceresReadequacao(array('a.idReadequacao = ?'=>$idReadequacao, 'b.idTipoAgente = ?'=>1))->current();
                
        //DADOS DO PROJETO
        $p = $Projetos->buscarProjetoXProponente(array('idPronac = ?' => $dados->idPronac))->current();
        $this->view->projeto = $p;
    }
    
    /* 
     * Criada em 12/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelo Parecerista ou Técnico de acompanhamento para avaliar a readequação.
    */
    public function salvarParecerTecnicoAction()
	{
        if($this->idPerfil != 94 && $this->idPerfil != 121){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idPronac = $_POST['idPronac'];
        $idReadequacao = $_POST['idReadequacao'];
        $dsParecer = $_POST['dsParecer'];
        $parecerProjeto = $_POST['parecerProjeto'];
        $campoTipoParecer = 8;
        $vlPlanilha = 0;
        
        $tbReadequacao = new tbReadequacao();
        $dadosRead = $tbReadequacao->buscar(array('idReadequacao=?'=>$idReadequacao))->current();
        
        //SE FOR READEQUAÇÃO DE PLANILHA ORÇAMENTÁRIA, O CAMPO TipoParecer DA TABELA SAC.dbo.Parecer MUDARÁ.
        if($dadosRead->idTipoReadequacao == 2){
            $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        
            //BUSCAR VALOR TOTAL DA PLANILHA ATIVA
            $where = array();
            $where['a.IdPRONAC = ?'] = $idPronac;
            $where['a.stAtivo = ?'] = 'S';
            $PlanilhaAtiva = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();

            //BUSCAR VALOR TOTAL DA PLANILHA DE READEQUADA
            $where = array();
            $where['a.IdPRONAC = ?'] = $idPronac;
            $where['a.tpPlanilha = ?'] = 'SR';
            $where['a.stAtivo = ?'] = 'N';
            $PlanilhaReadequada = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();
            
            if($PlanilhaAtiva->Total > $PlanilhaReadequada->Total){
                $vlPlanilha = $PlanilhaAtiva->Total-$PlanilhaReadequada->Total;
                $campoTipoParecer = 4;
            } else {
                $vlPlanilha = $PlanilhaReadequada->Total-$PlanilhaAtiva->Total;
                $campoTipoParecer = 2;
            }
        }
        
        try {
            //ATUALIAZA A SITUAÇÃO, ÁREA E SEGMENTO DO PROJETO
            $d = array();
            $d['ProvidenciaTomada'] = 'Readequação em análise pela área técnica.';
            //$d['dtSituacao'] = new Zend_Db_Expr('GETDATE()');
            $where = "IdPRONAC = $idPronac";
            $Projetos = new Projetos();
            $Projetos->update($d, $where);
            
            $enquadramentoDAO = new Enquadramento();
            $buscaEnquadramento = $enquadramentoDAO->buscarDados($idPronac, null, false);
            
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$idPronac));
            if(count($dadosProjeto)>0){
                
                //CADASTRA OU ATUALIZA O PARECER DO TECNICO
                $parecerDAO = new Parecer();
                $dadosParecer = array(
                    'idPRONAC' => $idPronac,
                    'AnoProjeto' => $dadosProjeto[0]->AnoProjeto,
                    'Sequencial' => $dadosProjeto[0]->Sequencial,
                    'TipoParecer' => $campoTipoParecer,
                    'ParecerFavoravel' => $parecerProjeto,
                    'DtParecer' => new Zend_Db_Expr("GETDATE()"),
                    'NumeroReuniao' => null,
                    'ResumoParecer' => $dsParecer,
                    'SugeridoReal' => $vlPlanilha,
                    'Atendimento' => 'S',
                    'idEnquadramento' => $buscaEnquadramento['IdEnquadramento'],
                    'stAtivo' => 1,
                    'idTipoAgente' => 1,
                    'Logon' => $this->idUsuario
                );
            
                foreach ($dadosParecer as $dp) {
                    $parecerAntigo = array(
                        'Atendimento' => 'S',
                        'stAtivo' => 0
                    );
                    $whereUpdateParecer = 'IdPRONAC = '.$idPronac;
                    $alteraParecer = $parecerDAO->alterar($parecerAntigo, $whereUpdateParecer);
                }
                
                $tbReadequacaoXParecer = new tbReadequacaoXParecer();
                $buscarParecer = $tbReadequacaoXParecer->buscarPareceresReadequacao(array('a.idReadequacao = ?'=>$idReadequacao))->current();
                
                if($buscarParecer){
                    $whereUpdateParecer = 'IdParecer = '.$buscarParecer->IdParecer;
                    $parecerDAO->alterar($dadosParecer, $whereUpdateParecer);
                    $idParecer = $buscarParecer->IdParecer;
                } else {
                    $idParecer = $parecerDAO->inserir($dadosParecer);
                }
                
                $tbReadequacaoXParecer = new tbReadequacaoXParecer();
                $parecerReadequacao = $tbReadequacaoXParecer->buscar(array('idReadequacao = ?'=>$idReadequacao, 'idParecer =?'=>$idParecer));
                if(count($parecerReadequacao) == 0){
                    $dadosInclusao = array(
                        'idReadequacao' => $idReadequacao,
                        'idParecer' => $idParecer
                    );
                    $tbReadequacaoXParecer->inserir($dadosInclusao);
                }
            }
            
            if(isset($_POST['finalizarAvaliacao']) && $_POST['finalizarAvaliacao'] == 1){
                
                $tbDistribuirReadequacao = new tbDistribuirReadequacao();
                $dDP = $tbDistribuirReadequacao->buscar(array('idReadequacao = ?'=>$idReadequacao));
                
                if(count($dDP)>0){
                    //ATUALIZA A TABELA tbDistribuirReadequacao
                    $dadosDP = array();
                    $dadosDP['DtRetornoAvaliador'] = new Zend_Db_Expr('GETDATE()');
                    $whereDP = "idDistribuirReadequacao = ".$dDP[0]->idDistribuirReadequacao;
                    $x = $tbDistribuirReadequacao->update($dadosDP, $whereDP);
                    
                    $siEncaminhamento = 5; //Devolvido da análise técnica
                    if($this->idPerfil == 121){
                        $siEncaminhamento = 10; //Devolver para Coordenador do MinC
                    }
                    //ATUALIZA A TABELA tbReadequacao
                    $dados = array();
                    $dados['siEncaminhamento'] = $siEncaminhamento;
                    $where = "idReadequacao = $idReadequacao";
                    $tbReadequacao->update($dados, $where);
                }
                parent::message("A avaliação da readequação foi finalizada com sucesso! ", "readequacoes/painel-readequacoes", "CONFIRM");
            }
            $idReadequacao = Seguranca::encrypt($idReadequacao);
            parent::message("Dados salvos com sucesso!", "readequacoes/form-avaliar-readequacao?id=$idReadequacao", "CONFIRM");
            
        } // fecha try
        catch (Exception $e) {
            parent::message($e->getMessage(), "readequacoes/form-avaliar-readequacao?id=$idReadequacao", "ERROR");
        }
	}
    
    public function coordParecerFinalizarReadequacaoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        
        $post = Zend_Registry::get('post');
        $idReadequacao = (int) $post->idReadequacao;
        
        //Atualiza a tabela tbReadequacao
        $dados = array();
        $dados['siEncaminhamento'] = 6; // Devolvido para o coordenador geral de acompanhamento
        $where = "idReadequacao = $idReadequacao";
        $tbReadequacao = new tbReadequacao();
        $return = $tbReadequacao->update($dados, $where);
        
        if($return){
            echo json_encode(array('resposta'=>true));
        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
    /* 
     * Alterada em 12/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelos coordenadores para devolver para análise técnica.
    */
    public function devolverReadequacaoAction() {
        $dados = array();
        $get = Zend_Registry::get('get');
        $idReadequacao = (int) $get->id;
        
        $tbReadequacao = new tbReadequacao();
        $dadosReadequacao = $tbReadequacao->find(array('idReadequacao=?'=>$idReadequacao))->current();
        
        $siEncaminhamento = $dadosReadequacao->siEncaminhamento;
        
        //RECURSOS TRATADOS POR PARECERISTA
        if($siEncaminhamento == 6){
            //Atualiza a tabela tbRecurso
            $dados['siEncaminhamento'] = 3; // Encaminhado do MinC para Unidade de Análise
            $where = "idReadequacao = $idReadequacao";
        } else {
            $dados['siEncaminhamento'] = 4; // Encaminhado para o Técnico
            $where = "idReadequacao = $idReadequacao";
        }
        $tbReadequacao->update($dados, $where);
        
        parent::message("Readequação devolvida com sucesso!", "readequacoes/painel?tipoFiltro=analisados", "CONFIRM");
    }
    
    /* 
     * Alterada em 14/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelo coordenador de análise para finalizar a readequação e encaminhar para o componente da comissão.
    */
    public function coordAnaliseFinalizarReadequacaoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        //$vinculada = $this->idOrgao;
        
        $post = Zend_Registry::get('post');
        $idComponente = (int) $post->componente;
        $idReadequacao = (int) $post->idReadequacao;
        
        $tbReadequacao = new tbReadequacao();
        $dadosReadequacao = $tbReadequacao->buscar(array('idReadequacao = ?'=>$idReadequacao))->current();
        $idPronac = $dadosReadequacao->idPronac;
        
        $tbDistribuirReadequacao = new tbDistribuirReadequacao();
        $dadosDistRead = $tbDistribuirReadequacao->buscar(array('idReadequacao=?'=>$idReadequacao));
        
        if(count($dadosDistRead)>0){
            //Atualiza a tabela tbDistribuirReadequacao
            $dadosDP = array();
            $dadosDP['stValidacaoCoordenador'] = 1;
            $dadosDP['DtValidacaoCoordenador'] = new Zend_Db_Expr('GETDATE()');
            $dadosDP['idCoordenador'] = $this->idUsuario;
            $whereDP = "idDistribuirReadequacao = ".$dadosDistRead[0]->idDistribuirReadequacao;
            $tbDistribuirReadequacao->update($dadosDP, $whereDP);
        }
        
        $reuniao = new Reuniao();
        $raberta = $reuniao->buscarReuniaoAberta();
        $idNrReuniao = ($raberta['stPlenaria'] == 'A') ? $raberta['idNrReuniao']+1 : $raberta['idNrReuniao'];
        
        //Atualiza a tabela tbReadequacao
        $dados = array();
        $dados['siEncaminhamento'] = 7; // Enviado para CNIC
        $dados['idNrReuniao'] = $idNrReuniao;
        $where = "idReadequacao = $idReadequacao";
        $return = $tbReadequacao->update($dados, $where);
        
        //Inser um novo registro distribuindo a readequação para o componente da comissão.
        $dadosDist = array(
            'idReadequacao' => $idReadequacao,
            'idUnidade' => 400,
            'DtEncaminhamento' => new Zend_Db_Expr('GETDATE()'),
            'idAvaliador' => $idComponente,
            'dtEnvioAvaliador' => new Zend_Db_Expr('GETDATE()')
        );
        $distribuicao = $tbDistribuirReadequacao->inserir($dadosDist);
        
        if($return && $distribuicao){
            echo json_encode(array('resposta'=>true));
        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
    /* 
     * Alterada em 14/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelo componente da comissão para analisar as readequações - painel.
    */
    public function analisarReadequacoesCnicAction()
	{
        if($this->idPerfil != 118){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
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
            $order = array(2); //idReadequacao
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        
        $where = array();
        $where['a.stEstado = ?'] = 0; // 0=Atual; 1=Historico
        $where['a.siEncaminhamento = ?'] = 7; // 7=Encaminhar para ao Componente da Comissão
        $where['d.idUnidade = ?'] = 400; // CNIC
        $where['d.idAvaliador = ?'] = $this->idUsuario;
        
        if((isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['b.AnoProjeto+b.Sequencial = ?'] = $_GET['pronac'];
            $this->view->pronac = $_GET['pronac'];
        }
        
        $tbReadequacao = New tbReadequacao();
        $total = $tbReadequacao->painelReadequacoesComponente($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;
        
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $tbReadequacao->painelReadequacoesComponente($where, $order, $tamanho, $inicio);
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
        $this->view->qtdRegistros  = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
	}

    /* 
     * Alterada em 17/03/2014
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     * Função acessada pelo componente da comissão para avaliar as readequações.
    */
    public function formAvaliarReadequacaoCnicAction(){

        if($this->idPerfil != 118){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $get = Zend_Registry::get('get');
        $idReadequacao = (int) Seguranca::dencrypt($get->id);
        
        $tbReadequacao = new tbReadequacao();
        $dados = $tbReadequacao->buscarDadosReadequacoesCnic(array('a.idReadequacao = ?'=>$idReadequacao, 'f.idUnidade != ?'=>400))->current();
        
        if(!$dados){
            $dados = $tbReadequacao->buscarDadosReadequacoesCnic(array('a.idReadequacao = ?'=>$idReadequacao, 'f.idUnidade = ?'=>400))->current();
        }
        
        $this->view->dados = $dados;
        $this->view->idPronac = $dados->idPronac;
        $this->view->nmPagina = $dados->dsReadequacao;
        
        //DADOS DO PROJETO
        $Projetos = new Projetos();
        $p = $Projetos->buscarProjetoXProponente(array('idPronac = ?' => $dados->idPronac))->current();
        $this->view->projeto = $p;
        
        $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
        $this->view->Unidade = $buscarUnidade;
        
        //DADOS DA AVALIAÇÃO TÉCNICA ou PARECERISTA
        $avaliacaoTecnica = $tbReadequacao->buscarDadosParecerReadequacao(array('a.idReadequacao = ?'=>$idReadequacao, 'c.idTipoAgente = ?'=>1))->current();
        $this->view->avaliacaoTecnica = $avaliacaoTecnica;
        
        //DADOS DA AVALIAÇÃO DO COMPONENTE
        $avaliacaoConselheiro = $tbReadequacao->buscarDadosParecerReadequacao(array('a.idReadequacao = ?'=>$idReadequacao, 'c.idTipoAgente = ?'=>6))->current();
        $this->view->avaliacaoConselheiro = $avaliacaoConselheiro;
    }
    
    /* 
     * Alterada em 15/05/2015
     * Função acessada pelo componente da comissão para avaliar as readequações.
    */
    public function componenteComissaoSalvarAvaliacaoAction(){
        if($this->idPerfil != 118){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $idPronac = $_POST['idPronac'];
        $idReadequacao = $_POST['idReadequacao'];
        $parecerProjeto = $_POST['parecerProjeto'];
        $dsParecer = $_POST['dsParecer'];
        $campoTipoParecer = 8;
        $vlPlanilha = 0;
        
        $tbReadequacao = new tbReadequacao();
        $dadosRead = $tbReadequacao->buscar(array('idReadequacao=?'=>$idReadequacao))->current();
        
        //SE FOR READEQUAÇÃO DE PLANILHA ORÇAMENTÁRIA, O CAMPO TipoParecer DA TABELA SAC.dbo.Parecer MUDARÁ.
        if($dadosRead->idTipoReadequacao == 2){
            $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        
            //BUSCAR VALOR TOTAL DA PLANILHA ATIVA
            $where = array();
            $where['a.IdPRONAC = ?'] = $idPronac;
            $where['a.stAtivo = ?'] = 'S';
            $PlanilhaAtiva = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();

            //BUSCAR VALOR TOTAL DA PLANILHA DE READEQUADA
            $where = array();
            $where['a.IdPRONAC = ?'] = $idPronac;
            $where['a.tpPlanilha = ?'] = 'SR';
            $where['a.stAtivo = ?'] = 'N';
            $PlanilhaReadequada = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();
            
            if($PlanilhaAtiva->Total > $PlanilhaReadequada->Total){
                $vlPlanilha = $PlanilhaAtiva->Total-$PlanilhaReadequada->Total;
                $campoTipoParecer = 4;
            } else {
                $vlPlanilha = $PlanilhaReadequada->Total-$PlanilhaAtiva->Total;
                $campoTipoParecer = 2;
            }
        }
        
        try {
            
            $Projetos = new Projetos();
            $enquadramentoDAO = new Enquadramento();
            $buscaEnquadramento = $enquadramentoDAO->buscarDados($idPronac, null, false);
            
            $dadosProjeto = $Projetos->buscar(array('IdPRONAC = ?'=>$idPronac));
            if(count($dadosProjeto)>0){
            
                //CADASTRA OU ATUALIZA O PARECER DO TECNICO
                $parecerDAO = new Parecer();
                $dadosParecer = array(
                    'idPRONAC' => $idPronac,
                    'AnoProjeto' => $dadosProjeto[0]->AnoProjeto,
                    'Sequencial' => $dadosProjeto[0]->Sequencial,
                    'TipoParecer' => $campoTipoParecer,
                    'ParecerFavoravel' => $parecerProjeto,
                    'DtParecer' => new Zend_Db_Expr("GETDATE()"),
                    'NumeroReuniao' => null,
                    'ResumoParecer' => $dsParecer,
                    'SugeridoReal' => $vlPlanilha,
                    'Atendimento' => 'S',
                    'idEnquadramento' => $buscaEnquadramento['IdEnquadramento'],
                    'stAtivo' => 1,
                    'idTipoAgente' => 6, //Componente da Comissão
                    'Logon' => $this->idUsuario
                );
                
                foreach ($dadosParecer as $dp) {
                    $parecerAntigo = array(
                        'Atendimento' => 'S',
                        'stAtivo' => 0
                    );
                    $whereUpdateParecer = 'IdPRONAC = '.$idPronac;
                    $alteraParecer = $parecerDAO->alterar($parecerAntigo, $whereUpdateParecer);
                }
                
                $tbReadequacaoXParecer = new tbReadequacaoXParecer();
                $buscarParecer = $tbReadequacaoXParecer->buscarPareceresReadequacao(array('a.idReadequacao = ?'=>$idReadequacao, 'b.idTipoAgente =?'=>6))->current();
                if($buscarParecer){
                    $whereUpdateParecer = 'IdParecer = '.$buscarParecer->IdParecer;
                    $parecerDAO->alterar($dadosParecer, $whereUpdateParecer);
                    $idParecer = $buscarParecer->IdParecer;
                } else {
                    $idParecer = $parecerDAO->inserir($dadosParecer);
                }
                
                $tbReadequacaoXParecer = new tbReadequacaoXParecer();
                $parecerReadequacao = $tbReadequacaoXParecer->buscar(array('idReadequacao = ?'=>$idReadequacao, 'idParecer =?'=>$idParecer));
                if(count($parecerReadequacao) == 0){
                    $dadosInclusao = array(
                        'idReadequacao' => $idReadequacao,
                        'idParecer' => $idParecer
                    );
                    $tbReadequacaoXParecer->inserir($dadosInclusao);
                }
            }
            
            if(isset($_POST['finalizarAvaliacao']) && $_POST['finalizarAvaliacao'] == 1){
                
                $tbDistribuirReadequacao = new tbDistribuirReadequacao();
                $dDP = $tbDistribuirReadequacao->buscar(array('idReadequacao = ?'=>$idReadequacao, 'idUnidade =?'=>400, 'idAvaliador=?'=>$this->idUsuario));
                
                if(count($dDP)>0){
                    //ATUALIZA A TABELA tbDistribuirReadequacao
                    $dadosDP = array();
                    $dadosDP['DtRetornoAvaliador'] = new Zend_Db_Expr('GETDATE()');
                    $whereDP = "idDistribuirReadequacao = ".$dDP[0]->idDistribuirReadequacao;
                    $x = $tbDistribuirReadequacao->update($dadosDP, $whereDP);
                    
                    $reuniao = new Reuniao();
                    $raberta = $reuniao->buscarReuniaoAberta();
                    $idNrReuniao = ($raberta['stPlenaria'] == 'A') ? $raberta['idNrReuniao']+1 : $raberta['idNrReuniao'];
                    
                    $read = $tbReadequacao->buscarReadequacao(array('idReadequacao =?'=>$idReadequacao))->current();
                    
                    $stEstado = 0;
                    if($_POST['plenaria']){
                        $campoSiEncaminhamento = 8; // 8=Enviado à Plenária
                    } else {
                        $campoSiEncaminhamento = 9; // 9=Enviado para Checklist Publicação
                        if(!in_array($read->idTipoReadequacao, array(2,3,10,12,15))){
                            $campoSiEncaminhamento = 15; // 15=Finaliza a readequação sem a necessidade de enviar para publicação no DOU.
                            $stEstado = 1;
                        }
                    }

                    //ATUALIZA A TABELA tbReadequacao
                    $dados = array();
                    $dados['siEncaminhamento'] = $campoSiEncaminhamento; // Devolvido da análise técnica
                    $dados['idNrReuniao'] = $idNrReuniao;
                    $dados['stEstado'] = $stEstado;
                    if($parecerProjeto == 2){
                        $dados['stAnalise'] = 'AC';
                    } else {
                        $dados['stAnalise'] = 'IC';
                    }
                    
                    $where = "idReadequacao = $idReadequacao";
                    $tbReadequacao = new tbReadequacao();
                    $tbReadequacao->update($dados, $where);
                    
                    if($campoSiEncaminhamento != 8){
                        
                        if($parecerProjeto == 2){ //Se for parecer favorável, atualiza os dados solicitados na readequação

                            // READEQUAÇÃO DE PLANILHA ORÇAMENTÁRIA
                            if($read->idTipoReadequacao == 2){
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
                                //BUSCAR VALOR TOTAL DA PLANILHA ATIVA
                                $where = array();
                                $where['a.IdPRONAC = ?'] = $read->idPronac;
                                $where['a.stAtivo = ?'] = 'S';
                                $PlanilhaAtiva = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();

                                //BUSCAR VALOR TOTAL DA PLANILHA DE READEQUADA
                                $where = array();
                                $where['a.IdPRONAC = ?'] = $read->idPronac;
                                $where['a.tpPlanilha = ?'] = 'SR';
                                $where['a.stAtivo = ?'] = 'N';
                                $PlanilhaReadequada = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();

                                if($PlanilhaAtiva->Total < $PlanilhaReadequada->Total){
                                    $TipoAprovacao = 2;
                                    $dadosPrj->Situacao = 'D28';
                                } else {
                                    $TipoAprovacao = 4;
                                    $dadosPrj->Situacao = 'D29';
                                }
                                $dadosPrj->save();

                                $AprovadoReal = number_format(($PlanilhaReadequada->Total-$PlanilhaAtiva->Total), 2, '.', '');
                                $tbAprovacao = new Aprovacao();
                                $dadosAprovacao = array(
                                    'IdPRONAC' => $read->idPronac,
                                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                                    'Sequencial' => $dadosPrj->Sequencial,
                                    'TipoAprovacao' => $TipoAprovacao,
                                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                                    'AprovadoReal' => $AprovadoReal,
                                    'Logon' => $this->idUsuario,
                                    'idReadequacao' => $idReadequacao
                                );
                                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);

                            // READEQUAÇÃO DE ALTERAÇÃO DE RAZÃO SOCIAL
                            } else if($read->idTipoReadequacao == 3){ //Se for readequação de alteração de razão social, atualiza os dados na AGENTES.dbo.Nomes.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $tbAprovacao = new Aprovacao();
                                $dadosAprovacao = array(
                                    'IdPRONAC' => $read->idPronac,
                                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                                    'Sequencial' => $dadosPrj->Sequencial,
                                    'TipoAprovacao' => 8,
                                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                                    'Logon' => $this->idUsuario,
                                    'idReadequacao' => $idReadequacao
                                );
                                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);

                            // READEQUAÇÃO DE AGÊNCIA BANCÁRIA
                            } else if($read->idTipoReadequacao == 4){ //Se for readequação de agência bancária, atualiza os dados na SAC.dbo.PreProjeto.// READEQUAÇÃO DE ALTERAÇÃO DE RAZÃO SOCIAL
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();
                                $agenciaBancaria = str_replace('-', '', $read->dsSolicitacao);

                                $tblContaBancaria = new ContaBancaria();
                                $arrayDadosBancarios = array(
                                    'Agencia' => $agenciaBancaria,
                                    'ContaBloqueada' => '000000000000',
                                    'DtLoteRemessaCB' => NULL,
                                    'LoteRemessaCB' => '00000',
                                    'OcorrenciaCB' => '000',
                                    'ContaLivre' => '000000000000',
                                    'DtLoteRemessaCL' => NULL,
                                    'LoteRemessaCL' => '00000',
                                    'OcorrenciaCL' => '000',
                                    'Logon' => $this->idUsuario,
                                    'idPronac' => $read->idPronac
                                );
                                $whereDadosBancarios['AnoProjeto = ?'] = $dadosPrj->AnoProjeto;
                                $whereDadosBancarios['Sequencial = ?'] = $dadosPrj->Sequencial;
                                $tblContaBancaria->alterar($arrayDadosBancarios, $whereDadosBancarios);

                            // READEQUAÇÃO DE SINOPSE DA OBRA
                            } else if($read->idTipoReadequacao == 5){ //Se for readequação de sinopse da obra, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->Sinopse = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE IMPACTO AMBIENTAL
                            } else if($read->idTipoReadequacao == 6){ //Se for readequação de impacto ambiental, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->ImpactoAmbiental = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE ESPECIFICAÇÃO TÉCNICA
                            } else if($read->idTipoReadequacao == 7){ //Se for readequação de especificação técnica, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->EspecificacaoTecnica = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE ESTRATÉGIA DE EXECUÇÃO
                            } else if($read->idTipoReadequacao == 8){ //Se for readequação de estratégia de execução, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->EstrategiadeExecucao = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE LOCAL DE REALIZAÇÃO
                            } else if($read->idTipoReadequacao == 9){ //Se for readequação de local de realização, atualiza os dados na SAC.dbo.Abrangencia.
                                $Abrangencia = new Abrangencia();
                                $tbAbrangencia = new tbAbrangencia();
                                $abrangencias = $tbAbrangencia->buscar(array('idReadequacao=?'=>$idReadequacao));
                                foreach ($abrangencias as $abg) {
                                    $Projetos = new Projetos();
                                    $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                    //Se não houve avalição do conselheiro, pega a avaliação técnica como referencia.
                                    $avaliacao = $abg->tpAnaliseComissao;
                                    if($abg->tpAnaliseComissao == 'N'){
                                        $avaliacao = $abg->tpAnaliseTecnica;
                                    }

                                    //Se a avaliação foi deferida, realiza as mudanças necessárias na tabela original.
                                    if($avaliacao == 'D'){
                                        if($abg->tpSolicitacao == 'E'){ //Se a abrangencia foi excluída, atualiza os status da abrangencia na SAC.dbo.Abrangencia
                                            $Abrangencia->delete(array('idProjeto = ?'=>$dadosPrj->idProjeto, 'idPais = ?'=>$abg->idPais, 'idUF = ?'=>$abg->idUF, 'idMunicipioIBGE = ?'=>$abg->idMunicipioIBGE));

                                        } else if($abg->tpSolicitacao == 'I') { //Se a abangência foi incluída, cria um novo registro na tabela SAC.dbo.Abrangencia
                                            $novoLocalRead = array();
                                            $novoLocalRead['idProjeto'] = $dadosPrj->idProjeto;
                                            $novoLocalRead['idPais'] = $abg->idPais;
                                            $novoLocalRead['idUF'] = $abg->idUF;
                                            $novoLocalRead['idMunicipioIBGE'] = $abg->idMunicipioIBGE;
                                            $novoLocalRead['Usuario'] = $this->idUsuario;
                                            $novoLocalRead['stAbrangencia'] = 1;
                                            $Abrangencia->salvar($novoLocalRead);
                                        }
                                    }
                                }

                                $dadosAbr = array();
                                $dadosAbr['stAtivo'] = 'N';
                                $whereAbr = "idPronac = $read->idPronac AND idReadequacao = $idReadequacao";
                                $tbAbrangencia->update($dadosAbr, $whereAbr);

                            // READEQUAÇÃO DE ALTERAÇÃO DE PROPONENTE
                            } else if($read->idTipoReadequacao == 10){ //Se for readequação de alteração de proponente, atualiza os dados na SAC.dbo.Projetos.

                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $tbAprovacao = new Aprovacao();
                                $dadosAprovacao = array(
                                    'IdPRONAC' => $read->idPronac,
                                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                                    'Sequencial' => $dadosPrj->Sequencial,
                                    'TipoAprovacao' => 8,
                                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                                    'Logon' => $this->idUsuario,
                                    'idReadequacao' => $idReadequacao
                                );
                                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);

                            // READEQUAÇÃO DE PLANO DE DISTRIBUIÇÃO
                            } else if($read->idTipoReadequacao == 11){ //Se for readequação de plano de distribuição, atualiza os dados na SAC.dbo.PlanoDistribuicaoProduto.
                                $PlanoDistribuicaoProduto = new PlanoDistribuicaoProduto();
                                $tbPlanoDistribuicao = new tbPlanoDistribuicao();
                                $planosDistribuicao = $tbPlanoDistribuicao->buscar(array('idReadequacao=?'=>$idReadequacao));

                                foreach ($planosDistribuicao as $plano) {
                                    $Projetos = new Projetos();
                                    $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                    //Se não houve avalição do conselheiro, pega a avaliação técnica como referencia.
                                    $avaliacao = $plano->tpAnaliseComissao;
                                    if($plano->tpAnaliseComissao == 'N'){
                                        $avaliacao = $plano->tpAnaliseTecnica;
                                    }

                                    //Se a avaliação foi deferida, realiza as mudanças necessárias na tabela original.
                                    if($avaliacao == 'D'){
                                        if($plano->tpSolicitacao == 'E'){ //Se o plano de distribuição foi excluído, atualiza os status do plano na SAC.dbo.PlanoDistribuicaoProduto
                                            $PlanoDistribuicaoProduto->delete(array('idProjeto = ?'=>$dadosPrj->idProjeto, 'idProduto = ?'=>$plano->idProduto, 'Area = ?'=>$plano->cdArea, 'Segmento = ?'=>$plano->cdSegmento));

                                        } else if($plano->tpSolicitacao == 'I') { //Se o plano de distribuição foi incluído, cria um novo registro na tabela SAC.dbo.PlanoDistribuicaoProduto
                                            $novoPlanoDistRead = array();
                                            $novoPlanoDistRead['idProjeto'] = $dadosPrj->idProjeto;
                                            $novoPlanoDistRead['idProduto'] = $plano->idProduto;
                                            $novoPlanoDistRead['Area'] = $plano->cdArea;
                                            $novoPlanoDistRead['Segmento'] = $plano->cdSegmento;
                                            $novoPlanoDistRead['idPosicaoDaLogo'] = $plano->idPosicaoLogo;
                                            $novoPlanoDistRead['QtdeProduzida'] = $plano->qtProduzida;
                                            $novoPlanoDistRead['QtdePatrocinador'] = $plano->qtPatrocinador;
                                            $novoPlanoDistRead['QtdeProponente'] = $plano->qtProponente;
                                            $novoPlanoDistRead['QtdeOutros'] = $plano->qtOutros;
                                            $novoPlanoDistRead['QtdeVendaNormal'] = $plano->qtVendaNormal;
                                            $novoPlanoDistRead['QtdeVendaPromocional'] = $plano->qtVendaPromocional;
                                            $novoPlanoDistRead['PrecoUnitarioNormal'] = $plano->vlUnitarioNormal;
                                            $novoPlanoDistRead['PrecoUnitarioPromocional'] = $plano->vlUnitarioPromocional;
                                            $novoPlanoDistRead['stPrincipal'] = 0;
                                            $novoPlanoDistRead['Usuario'] = $this->idUsuario;
                                            $novoPlanoDistRead['dsJustificativaPosicaoLogo'] = null;
                                            $novoPlanoDistRead['stPlanoDistribuicaoProduto'] = 1;
                                            $PlanoDistribuicaoProduto->inserir($novoPlanoDistRead);
                                        }
                                    }
                                }

                                $dadosPDD = array();
                                $dadosPDD['stAtivo'] = 'N';
                                $wherePDD = "idPronac = $read->idPronac AND idReadequacao = $idReadequacao";
                                $tbPlanoDistribuicao->update($dadosPDD, $wherePDD);

                            // READEQUAÇÃO DE NOME DO PROJETO
                            } else if($read->idTipoReadequacao == 12){ //Se for readequação de nome do projeto, insere o registo na tela de Checklist de Publicação.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $tbAprovacao = new Aprovacao();
                                $dadosAprovacao = array(
                                    'IdPRONAC' => $read->idPronac,
                                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                                    'Sequencial' => $dadosPrj->Sequencial,
                                    'TipoAprovacao' => 8,
                                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                                    'Logon' => $this->idUsuario,
                                    'idReadequacao' => $idReadequacao
                                );
                                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);

                            // READEQUAÇÃO DE PERÍODO DE EXECUÇÃO
                            } else if($read->idTipoReadequacao == 13){ //Se for readequação de período de execução, atualiza os dados na SAC.dbo.Projetos.
                                $dtFimExecucao = Data::dataAmericana($read->dsSolicitacao);
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                                $dadosPrj->DtFimExecucao = $dtFimExecucao;
                                $dadosPrj->save();

                            // READEQUAÇÃO DE PLANO DE DIVULGAÇÃO
                            } else if($read->idTipoReadequacao == 14){ //Se for readequação de plano de divulgacao, atualiza os dados na SAC.dbo.PlanoDeDivulgacao.
                                $PlanoDeDivulgacao = new PlanoDeDivulgacao();
                                $tbPlanoDivulgacao = new tbPlanoDivulgacao();
                                $planosDivulgacao = $tbPlanoDivulgacao->buscar(array('idReadequacao=?'=>$idReadequacao));

                                foreach ($planosDivulgacao as $plano) {
                                    $Projetos = new Projetos();
                                    $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                    //Se não houve avalição do conselheiro, pega a avaliação técnica como referencia.
                                    $avaliacao = $plano->tpAnaliseComissao;
                                    if($plano->tpAnaliseComissao == 'N'){
                                        $avaliacao = $plano->tpAnaliseTecnica;
                                    }

                                    //Se a avaliação foi deferida, realiza as mudanças necessárias na tabela original.
                                    if($avaliacao == 'D'){
                                        if($plano->tpSolicitacao == 'E'){ //Se o plano de divulgação foi excluído, atualiza os status do plano na SAC.dbo.PlanoDeDivulgacao
                                            $PlanoDivulgacaoEmQuestao = $PlanoDeDivulgacao->buscar(array('idProjeto = ?'=>$dadosPrj->idProjeto, 'idPeca = ?'=>$plano->idPeca, 'idVeiculo = ?'=>$plano->idVeiculo))->current();
                                            $tbLogomarca = new tbLogomarca();
                                            $dadosLogomarcaDaDivulgacao = $tbLogomarca->buscar(array('idPlanoDivulgacao = ?' => $PlanoDivulgacaoEmQuestao->idPlanoDivulgacao))->current();
                                            $dadosLogomarcaDaDivulgacao->delete();
                                            $PlanoDivulgacaoEmQuestao->delete();

                                        } else if($plano->tpSolicitacao == 'I') { //Se o plano de divulgação foi incluído, cria um novo registro na tabela SAC.dbo.PlanoDeDivulgacao
                                            $novoPlanoDivRead = array();
                                            $novoPlanoDivRead['idProjeto'] = $dadosPrj->idProjeto;
                                            $novoPlanoDivRead['idPeca'] = $plano->idPeca;
                                            $novoPlanoDivRead['idVeiculo'] = $plano->idVeiculo;
                                            $novoPlanoDivRead['Usuario'] = $this->idUsuario;
                                            $novoPlanoDivRead['siPlanoDeDivulgacao'] = 0;
                                            $novoPlanoDivRead['idDocumento'] = null;
                                            $novoPlanoDivRead['stPlanoDivulgacao'] = 1;
                                            $PlanoDeDivulgacao->inserir($novoPlanoDivRead);
                                        }
                                    }
                                }

                                $dadosPDD = array();
                                $dadosPDD['stAtivo'] = 'N';
                                $wherePDD = "idPronac = $read->idPronac AND idReadequacao = $idReadequacao";
                                $tbPlanoDivulgacao->update($dadosPDD, $wherePDD);

                            // READEQUAÇÃO DE RESUMO DO PROJETO
                            } else if($read->idTipoReadequacao == 15){ //Se for readequação de resumo do projeto, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $tbAprovacao = new Aprovacao();
                                $dadosAprovacao = array(
                                    'IdPRONAC' => $read->idPronac,
                                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                                    'Sequencial' => $dadosPrj->Sequencial,
                                    'TipoAprovacao' => 8,
                                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                                    'Logon' => $this->idUsuario,
                                    'idReadequacao' => $idReadequacao
                                );
                                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);

                            // READEQUAÇÃO DE OBJETIVOS
                            } else if($read->idTipoReadequacao == 16){ //Se for readequação de objetivos, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->Objetivos = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE JUSTIFICATIVA
                            } else if($read->idTipoReadequacao == 17){ //Se for readequação de justificativa, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->Justificativa = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE ACESSIBILIDADE
                            } else if($read->idTipoReadequacao == 18){ //Se for readequação de acesibilidade, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->Acessibilidade = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE DEMOCRATIZAÇÃO DE ACESSO
                            } else if($read->idTipoReadequacao == 19){ //Se for readequação de democratização de acesso, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->DemocratizacaoDeAcesso = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE ETAPAS DE TRABALHO
                            } else if($read->idTipoReadequacao == 20){ //Se for readequação de etapas de trabalho, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->EtapaDeTrabalho = $read->dsSolicitacao;
                                $dadosPreProjeto->save();

                            // READEQUAÇÃO DE FICHA TÉCNICA
                            } else if($read->idTipoReadequacao == 21){ //Se for readequação de ficha técnica, atualiza os dados na SAC.dbo.PreProjeto.
                                $Projetos = new Projetos();
                                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                                $PrePropojeto = new PreProjeto();
                                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                                $dadosPreProjeto->FichaTecnica = $read->dsSolicitacao;
                                $dadosPreProjeto->save();
                            }
                        }

                        //Atualiza a tabela tbDistribuirReadequacao
                        $dados = array();
                        $dados['stValidacaoCoordenador'] = 1;
                        $dados['DtValidacaoCoordenador'] = new Zend_Db_Expr('GETDATE()');
                        $dados['idCoordenador'] = $this->idUsuario;
                        $where = "idReadequacao = $idReadequacao";
                        $tbDistribuirReadequacao = new tbDistribuirReadequacao();
                        $tbDistribuirReadequacao->update($dados, $where);
                    }
                    
                    parent::message("A avaliação da readequação foi finalizada com sucesso!", "readequacoes/analisar-readequacoes-cnic", "CONFIRM");
                    
                } else {
                    parent::message("Erro ao avaliar a readequação!", "form-avaliar-readequacao-cnic?id=$idReadequacao", "ERROR");
                }
            }
            $idReadequacao = Seguranca::encrypt($idReadequacao);
            parent::message("Dados salvos com sucesso!", "readequacoes/form-avaliar-readequacao-cnic?id=$idReadequacao", "CONFIRM");
            
        } // fecha try
        catch (Exception $e) {
            parent::message($e->getMessage(), "readequacoes/form-avaliar-readequacao-cnic?id=$idReadequacao", "ERROR");
        }
	}
    
    /* 
     * Alterada em 15/05/2015
     * Função criada para finalizar ou encaminhar a readequação para checklist de publicação.
    */
    public function encaminharReadequacaoChecklistAction() {
        
        if($this->idPerfil != 93 && $this->idPerfil != 94 && $this->idPerfil != 121 && $this->idPerfil != 122 && $this->idPerfil != 123){
            parent::message("Você não tem permissão para acessar essa área do sistema!", "principal", "ALERT");
        }
        
        $auth = Zend_Auth::getInstance();
        $idCoordenador = $auth->getIdentity()->usu_codigo;
        
        $get = Zend_Registry::get('get');
        $idReadequacao = (int) $get->id;
        
        $reuniao = new Reuniao();
        $raberta = $reuniao->buscarReuniaoAberta();
        $idNrReuniao = ($raberta['stPlenaria'] == 'A') ? $raberta['idNrReuniao']+1 : $raberta['idNrReuniao'];
                        
        $tbReadequacaoXParecer = new tbReadequacaoXParecer();
        $dadosParecer = $tbReadequacaoXParecer->buscar(array('idReadequacao=?'=>$idReadequacao));
        foreach ($dadosParecer as $key => $dp) {
            $pareceres = array();
            $pareceres[$key] = $dp->idParecer;
        }
        
        $Parecer = new Parecer();
        $parecerTecnico = $Parecer->buscar(array('IdParecer=(?)'=>$pareceres), array('IdParecer'))->current();
        
        $tbReadequacao = new tbReadequacao();
                        
        if($parecerTecnico->ParecerFavoravel == 2){ //Se for parecer favorável, atualiza os dados solicitados na readequação
            $read = $tbReadequacao->buscarReadequacao(array('idReadequacao =?'=>$idReadequacao))->current();
            
            // READEQUAÇÃO DE PLANILHA ORÇAMENTÁRIA
            if($read->idTipoReadequacao == 2){
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                
                $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
                //BUSCAR VALOR TOTAL DA PLANILHA ATIVA
                $where = array();
                $where['a.IdPRONAC = ?'] = $read->idPronac;
                $where['a.stAtivo = ?'] = 'S';
                $PlanilhaAtiva = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();

                //BUSCAR VALOR TOTAL DA PLANILHA DE READEQUADA
                $where = array();
                $where['a.IdPRONAC = ?'] = $read->idPronac;
                $where['a.tpPlanilha = ?'] = 'SR';
                $where['a.stAtivo = ?'] = 'N';
                $PlanilhaReadequada = $tbPlanilhaAprovacao->valorTotalPlanilha($where)->current();

                if($PlanilhaAtiva->Total < $PlanilhaReadequada->Total){
                    $TipoAprovacao = 2;
                    $dadosPrj->Situacao = 'D28';
                } else {
                    $TipoAprovacao = 4;
                    $dadosPrj->Situacao = 'D29';
                }
                $dadosPrj->save();
                
                $AprovadoReal = number_format(($PlanilhaReadequada->Total-$PlanilhaAtiva->Total), 2, '.', '');
                $tbAprovacao = new Aprovacao();
                $dadosAprovacao = array(
                    'IdPRONAC' => $read->idPronac,
                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                    'Sequencial' => $dadosPrj->Sequencial,
                    'TipoAprovacao' => $TipoAprovacao,
                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                    'AprovadoReal' => $AprovadoReal,
                    'Logon' => $this->idUsuario,
                    'idReadequacao' => $idReadequacao
                );
                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);
            
            // READEQUAÇÃO DE ALTERAÇÃO DE RAZÃO SOCIAL
            } else if($read->idTipoReadequacao == 3){ //Se for readequação de alteração de razão social, atualiza os dados na AGENTES.dbo.Nomes.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                
                $tbAprovacao = new Aprovacao();
                $dadosAprovacao = array(
                    'IdPRONAC' => $read->idPronac,
                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                    'Sequencial' => $dadosPrj->Sequencial,
                    'TipoAprovacao' => 8,
                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                    'Logon' => $this->idUsuario,
                    'idReadequacao' => $idReadequacao
                );
                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);
                
            // READEQUAÇÃO DE AGÊNCIA BANCÁRIA
            } else if($read->idTipoReadequacao == 4){ //Se for readequação de agência bancária, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();
                $agenciaBancaria = str_replace('-', '', $read->dsSolicitacao);
                
                $tblContaBancaria = new ContaBancaria();
                $arrayDadosBancarios = array(
                    'Agencia' => $agenciaBancaria,
                    'ContaBloqueada' => '000000000000',
                    'DtLoteRemessaCB' => NULL,
                    'LoteRemessaCB' => '00000',
                    'OcorrenciaCB' => '000',
                    'ContaLivre' => '000000000000',
                    'DtLoteRemessaCL' => NULL,
                    'LoteRemessaCL' => '00000',
                    'OcorrenciaCL' => '000',
                    'Logon' => $this->idUsuario,
                    'idPronac' => $read->idPronac
                );
                $whereDadosBancarios['AnoProjeto = ?'] = $dadosPrj->AnoProjeto;
                $whereDadosBancarios['Sequencial = ?'] = $dadosPrj->Sequencial;
                $tblContaBancaria->alterar($arrayDadosBancarios, $whereDadosBancarios);
                
            // READEQUAÇÃO DE SINOPSE DA OBRA
            } else if($read->idTipoReadequacao == 5){ //Se for readequação de sinopse da obra, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->Sinopse = $read->dsSolicitacao;
                $dadosPreProjeto->save();
                
            // READEQUAÇÃO DE IMPACTO AMBIENTAL
            } else if($read->idTipoReadequacao == 6){ //Se for readequação de impacto ambiental, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->ImpactoAmbiental = $read->dsSolicitacao;
                $dadosPreProjeto->save();

            // READEQUAÇÃO DE ESPECIFICAÇÃO TÉCNICA
            } else if($read->idTipoReadequacao == 7){ //Se for readequação de especificação técnica, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->EspecificacaoTecnica = $read->dsSolicitacao;
                $dadosPreProjeto->save();
                
            // READEQUAÇÃO DE ESTRATÉGIA DE EXECUÇÃO
            } else if($read->idTipoReadequacao == 8){ //Se for readequação de estratégia de execução, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->EstrategiadeExecucao = $read->dsSolicitacao;
                $dadosPreProjeto->save();

            // READEQUAÇÃO DE LOCAL DE REALIZAÇÃO
            } else if($read->idTipoReadequacao == 9){ //Se for readequação de local de realização, atualiza os dados na SAC.dbo.Abrangencia.
                $Abrangencia = new Abrangencia();
                $tbAbrangencia = new tbAbrangencia();
                $abrangencias = $tbAbrangencia->buscar(array('idReadequacao=?'=>$idReadequacao));
                foreach ($abrangencias as $abg) {
                    $Projetos = new Projetos();
                    $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                    //Se não houve avalição do conselheiro, pega a avaliação técnica como referencia.
                    $avaliacao = $abg->tpAnaliseComissao;
                    if($abg->tpAnaliseComissao == 'N'){
                        $avaliacao = $abg->tpAnaliseTecnica;
                    }

                    //Se a avaliação foi deferida, realiza as mudanças necessárias na tabela original.
                    if($avaliacao == 'D'){
                        if($abg->tpSolicitacao == 'E'){ //Se a abrangencia foi excluída, atualiza os status da abrangencia na SAC.dbo.Abrangencia
                            $Abrangencia->delete(array('idProjeto = ?'=>$dadosPrj->idProjeto, 'idPais = ?'=>$abg->idPais, 'idUF = ?'=>$abg->idUF, 'idMunicipioIBGE = ?'=>$abg->idMunicipioIBGE));

                        } else if($abg->tpSolicitacao == 'I') { //Se a abangência foi incluída, cria um novo registro na tabela SAC.dbo.Abrangencia
                            $novoLocalRead = array();
                            $novoLocalRead['idProjeto'] = $dadosPrj->idProjeto;
                            $novoLocalRead['idPais'] = $abg->idPais;
                            $novoLocalRead['idUF'] = $abg->idUF;
                            $novoLocalRead['idMunicipioIBGE'] = $abg->idMunicipioIBGE;
                            $novoLocalRead['Usuario'] = $this->idUsuario;
                            $novoLocalRead['stAbrangencia'] = 1;
                            $Abrangencia->salvar($novoLocalRead);
                        }
                    }
                }
                
                $dadosAbr = array();
                $dadosAbr['stAtivo'] = 'N';
                $whereAbr = "idPronac = $read->idPronac AND idReadequacao = $idReadequacao";
                $tbAbrangencia->update($dadosAbr, $whereAbr);
                
            // READEQUAÇÃO DE ALTERAÇÃO DE PROPONENTE
            } else if($read->idTipoReadequacao == 10){ //Se for readequação de alteração de proponente, atualiza os dados na SAC.dbo.Projetos.
                
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                
                $tbAprovacao = new Aprovacao();
                $dadosAprovacao = array(
                    'IdPRONAC' => $read->idPronac,
                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                    'Sequencial' => $dadosPrj->Sequencial,
                    'TipoAprovacao' => 8,
                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                    'Logon' => $this->idUsuario,
                    'idReadequacao' => $idReadequacao
                );
                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);
            
            // READEQUAÇÃO DE PLANO DE DISTRIBUIÇÃO
            } else if($read->idTipoReadequacao == 11){ //Se for readequação de plano de distribuição, atualiza os dados na SAC.dbo.PlanoDistribuicaoProduto.
                $PlanoDistribuicaoProduto = new PlanoDistribuicaoProduto();
                $tbPlanoDistribuicao = new tbPlanoDistribuicao();
                $planosDistribuicao = $tbPlanoDistribuicao->buscar(array('idReadequacao=?'=>$idReadequacao));

                foreach ($planosDistribuicao as $plano) {
                    $Projetos = new Projetos();
                    $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                    //Se não houve avalição do conselheiro, pega a avaliação técnica como referencia.
                    $avaliacao = $plano->tpAnaliseComissao;
                    if($plano->tpAnaliseComissao == 'N'){
                        $avaliacao = $plano->tpAnaliseTecnica;
                    }

                    //Se a avaliação foi deferida, realiza as mudanças necessárias na tabela original.
                    if($avaliacao == 'D'){
                        if($plano->tpSolicitacao == 'E'){ //Se o plano de distribuição foi excluído, atualiza os status do plano na SAC.dbo.PlanoDistribuicaoProduto
                            $PlanoDistribuicaoProduto->delete(array('idProjeto = ?'=>$dadosPrj->idProjeto, 'idProduto = ?'=>$plano->idProduto, 'Area = ?'=>$plano->cdArea, 'Segmento = ?'=>$plano->cdSegmento));

                        } else if($plano->tpSolicitacao == 'I') { //Se o plano de distribuição foi incluído, cria um novo registro na tabela SAC.dbo.PlanoDistribuicaoProduto
                            $novoPlanoDistRead = array();
                            $novoPlanoDistRead['idProjeto'] = $dadosPrj->idProjeto;
                            $novoPlanoDistRead['idProduto'] = $plano->idProduto;
                            $novoPlanoDistRead['Area'] = $plano->cdArea;
                            $novoPlanoDistRead['Segmento'] = $plano->cdSegmento;
                            $novoPlanoDistRead['idPosicaoDaLogo'] = $plano->idPosicaoLogo;
                            $novoPlanoDistRead['QtdeProduzida'] = $plano->qtProduzida;
                            $novoPlanoDistRead['QtdePatrocinador'] = $plano->qtPatrocinador;
                            $novoPlanoDistRead['QtdeProponente'] = $plano->qtProponente;
                            $novoPlanoDistRead['QtdeOutros'] = $plano->qtOutros;
                            $novoPlanoDistRead['QtdeVendaNormal'] = $plano->qtVendaNormal;
                            $novoPlanoDistRead['QtdeVendaPromocional'] = $plano->qtVendaPromocional;
                            $novoPlanoDistRead['PrecoUnitarioNormal'] = $plano->vlUnitarioNormal;
                            $novoPlanoDistRead['PrecoUnitarioPromocional'] = $plano->vlUnitarioPromocional;
                            $novoPlanoDistRead['stPrincipal'] = 0;
                            $novoPlanoDistRead['Usuario'] = $this->idUsuario;
                            $novoPlanoDistRead['dsJustificativaPosicaoLogo'] = null;
                            $novoPlanoDistRead['stPlanoDistribuicaoProduto'] = 1;
                            $PlanoDistribuicaoProduto->inserir($novoPlanoDistRead);
                        }
                    }
                }

                $dadosPDD = array();
                $dadosPDD['stAtivo'] = 'N';
                $wherePDD = "idPronac = $read->idPronac AND idReadequacao = $idReadequacao";
                $tbPlanoDistribuicao->update($dadosPDD, $wherePDD);
                
            // READEQUAÇÃO DE NOME DO PROJETO
            } else if($read->idTipoReadequacao == 12){ //Se for readequação de nome do projeto, insere o registo na tela de Checklist de Publicação.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                
                $tbAprovacao = new Aprovacao();
                $dadosAprovacao = array(
                    'IdPRONAC' => $read->idPronac,
                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                    'Sequencial' => $dadosPrj->Sequencial,
                    'TipoAprovacao' => 8,
                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                    'Logon' => $this->idUsuario,
                    'idReadequacao' => $idReadequacao
                );
                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);
                
            // READEQUAÇÃO DE PERÍODO DE EXECUÇÃO
            } else if($read->idTipoReadequacao == 13){ //Se for readequação de período de execução, atualiza os dados na SAC.dbo.Projetos.
                $dtFimExecucao = Data::dataAmericana($read->dsSolicitacao);
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                $dadosPrj->DtFimExecucao = $dtFimExecucao;
                $dadosPrj->save();
            
            // READEQUAÇÃO DE PLANO DE DIVULGAÇÃO
            } else if($read->idTipoReadequacao == 14){ //Se for readequação de plano de divulgacao, atualiza os dados na SAC.dbo.PlanoDeDivulgacao.
                $PlanoDeDivulgacao = new PlanoDeDivulgacao();
                $tbPlanoDivulgacao = new tbPlanoDivulgacao();
                $planosDivulgacao = $tbPlanoDivulgacao->buscar(array('idReadequacao=?'=>$idReadequacao));

                foreach ($planosDivulgacao as $plano) {
                    $Projetos = new Projetos();
                    $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                    //Se não houve avalição do conselheiro, pega a avaliação técnica como referencia.
                    $avaliacao = $plano->tpAnaliseComissao;
                    if($plano->tpAnaliseComissao == 'N'){
                        $avaliacao = $plano->tpAnaliseTecnica;
                    }

                    //Se a avaliação foi deferida, realiza as mudanças necessárias na tabela original.
                    if($avaliacao == 'D'){
                        if($plano->tpSolicitacao == 'E'){ //Se o plano de divulgação foi excluído, atualiza os status do plano na SAC.dbo.PlanoDeDivulgacao
                            $PlanoDivulgacaoEmQuestao = $PlanoDeDivulgacao->buscar(array('idProjeto = ?'=>$dadosPrj->idProjeto, 'idPeca = ?'=>$plano->idPeca, 'idVeiculo = ?'=>$plano->idVeiculo))->current();
                            $tbLogomarca = new tbLogomarca();
                            $dadosLogomarcaDaDivulgacao = $tbLogomarca->buscar(array('idPlanoDivulgacao = ?' => $PlanoDivulgacaoEmQuestao->idPlanoDivulgacao))->current();
                            $dadosLogomarcaDaDivulgacao->delete();
                            $PlanoDivulgacaoEmQuestao->delete();

                        } else if($plano->tpSolicitacao == 'I') { //Se o plano de divulgação foi incluído, cria um novo registro na tabela SAC.dbo.PlanoDeDivulgacao
                            $novoPlanoDivRead = array();
                            $novoPlanoDivRead['idProjeto'] = $dadosPrj->idProjeto;
                            $novoPlanoDivRead['idPeca'] = $plano->idPeca;
                            $novoPlanoDivRead['idVeiculo'] = $plano->idVeiculo;
                            $novoPlanoDivRead['Usuario'] = $this->idUsuario;
                            $novoPlanoDivRead['siPlanoDeDivulgacao'] = 0;
                            $novoPlanoDivRead['idDocumento'] = null;
                            $novoPlanoDivRead['stPlanoDivulgacao'] = 1;
                            $PlanoDeDivulgacao->inserir($novoPlanoDivRead);
                        }
                    }
                }

                $dadosPDD = array();
                $dadosPDD['stAtivo'] = 'N';
                $wherePDD = "idPronac = $read->idPronac AND idReadequacao = $idReadequacao";
                $tbPlanoDivulgacao->update($dadosPDD, $wherePDD);
                
            // READEQUAÇÃO DE RESUMO DO PROJETO
            } else if($read->idTipoReadequacao == 15){ //Se for readequação de resumo do projeto, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$read->idPronac))->current();
                
                $tbAprovacao = new Aprovacao();
                $dadosAprovacao = array(
                    'IdPRONAC' => $read->idPronac,
                    'AnoProjeto' => $dadosPrj->AnoProjeto,
                    'Sequencial' => $dadosPrj->Sequencial,
                    'TipoAprovacao' => 8,
                    'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                    'ResumoAprovacao' => 'Parecer favorável para readequação',
                    'Logon' => $this->idUsuario,
                    'idReadequacao' => $idReadequacao
                );
                $idAprovacao = $tbAprovacao->inserir($dadosAprovacao);
                
            // READEQUAÇÃO DE OBJETIVOS
            } else if($read->idTipoReadequacao == 16){ //Se for readequação de objetivos, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->Objetivos = $read->dsSolicitacao;
                $dadosPreProjeto->save();
            
            // READEQUAÇÃO DE JUSTIFICATIVA
            } else if($read->idTipoReadequacao == 17){ //Se for readequação de justificativa, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->Justificativa = $read->dsSolicitacao;
                $dadosPreProjeto->save();
                            
            // READEQUAÇÃO DE ACESSIBILIDADE
            } else if($read->idTipoReadequacao == 18){ //Se for readequação de acesibilidade, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->Acessibilidade = $read->dsSolicitacao;
                $dadosPreProjeto->save();
            
            // READEQUAÇÃO DE DEMOCRATIZAÇÃO DE ACESSO
            } else if($read->idTipoReadequacao == 19){ //Se for readequação de democratização de acesso, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->DemocratizacaoDeAcesso = $read->dsSolicitacao;
                $dadosPreProjeto->save();
            
            // READEQUAÇÃO DE ETAPAS DE TRABALHO
            } else if($read->idTipoReadequacao == 20){ //Se for readequação de etapas de trabalho, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->EtapaDeTrabalho = $read->dsSolicitacao;
                $dadosPreProjeto->save();
                            
            // READEQUAÇÃO DE FICHA TÉCNICA
            } else if($read->idTipoReadequacao == 21){ //Se for readequação de ficha técnica, atualiza os dados na SAC.dbo.PreProjeto.
                $Projetos = new Projetos();
                $dadosPrj = $Projetos->buscar(array('IdPRONAC=?'=>$read->idPronac))->current();

                $PrePropojeto = new PreProjeto();
                $dadosPreProjeto = $PrePropojeto->find(array('idPreProjeto=?'=>$dadosPrj->idProjeto))->current();
                $dadosPreProjeto->FichaTecnica = $read->dsSolicitacao;
                $dadosPreProjeto->save();
            }
        }
                    
        //Atualiza a tabela tbReadequacao
        $dados = array();
        $dados['siEncaminhamento'] = 15; //Finalizam sem a necessidade de passar pela publicação no DOU.
        $dados['stEstado'] = 1;
        
        $tiposParaChecklist = array(2,3,10,12,15);
        if(in_array($read->idTipoReadequacao, $tiposParaChecklist)){
            $dados['siEncaminhamento'] = 9; //Encaminhado pelo sistema para o Checklist de Publicação
            $dados['stEstado'] = 0;
        }
        
        $dados['idNrReuniao'] = $idNrReuniao;
        $where = "idReadequacao = $idReadequacao";
        $return = $tbReadequacao->update($dados, $where);
        
        //Atualiza a tabela tbDistribuirReadequacao
        $dados = array();
        $dados['stValidacaoCoordenador'] = 1;
        $dados['DtValidacaoCoordenador'] = new Zend_Db_Expr('GETDATE()');
        $dados['idCoordenador'] = $idCoordenador;
        $where = "idReadequacao = $idReadequacao";
        $tbDistribuirReadequacao = new tbDistribuirReadequacao();
        $return2 = $tbDistribuirReadequacao->update($dados, $where);
        
        if(!$return && !$return2){
            parent::message("Não foi possível encaminhar a readequação para o Checklist de Publicação", "readequacoes/painel?tipoFiltro=analisados", "ERROR");
        }
        parent::message("Readequação encaminhada com sucesso!", "readequacoes/painel?tipoFiltro=analisados", "CONFIRM");
    }
    
}