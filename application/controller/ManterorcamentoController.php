<?php 
/**
 * Controller Disvincular Agentes
 * @author Equipe RUP - Politec
 * @since 07/06/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright ï¿½ 2010 - Ministï¿½rio da Cultura - Todos os direitos reservados.
 */

require_once "GenericControllerNew.php";

class ManterorcamentoController extends GenericControllerNew {

    private $idUsuario = null;
    private $idPreProjeto = null;
    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        $this->view->title = "Salic - Sistema de Apoio às Leis de Incentivo à Cultura"; // título da página
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $PermissoesGrupo = array();
        if (!$auth->hasIdentity()) // caso o usuário esteja autenticado
        {
             return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        } 

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
        parent::init(); // chama o init() do pai GenericControllerNew

        //recupera ID do pre projeto (proposta)
        if(!empty ($_REQUEST['idPreProjeto'])) {
            $this->idPreProjeto = $_REQUEST['idPreProjeto'];
            //VERIFICA SE A PROPOSTA ESTA COM O MINC
            $Movimentacao = new Movimentacao();
            $rsStatusAtual = $Movimentacao->buscarStatusAtualProposta($_REQUEST['idPreProjeto']);
            $this->view->movimentacaoAtual = isset($rsStatusAtual->Movimentacao) ? $rsStatusAtual->Movimentacao : '';
        }else {
            if($_REQUEST['idPreProjeto'] != '0'){
                parent::message("Necessário informar o número da proposta.", "/manterpropostaincentivofiscal/index", "ERROR");
            }
        }

        $this->idUsuario = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
    }

    /**
     * Redireciona para o fluxo inicial do sistema
     * @access public
     * @param void
     * @return void
     */
    public function indexAction() {
        // Usuario Logado
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $idusuario = $auth->getIdentity()->usu_codigo;
        //$idorgao = $auth->getIdentity()->usu_orgao;

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        //$codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
        $codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão

        $this->view->codOrgao = $codOrgao;
        $this->view->idUsuarioLogado = $idusuario;
        //$this->view->idorgao = $idorgao;

    }

    public function produtoscadastradosAction() {

        $buscarEstado = EstadoDAO::buscar();
        $this->view->Estados = $buscarEstado;

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasProdutos($this->idPreProjeto);
        $this->view->Etapa = $buscarEtapa;

        $buscarItem = ManterorcamentoDAO::buscarItensProdutos($this->idPreProjeto);
        $this->view->Item = $buscarItem;

//        $ExcluirItem = ManterorcamentoDAO::excluirItemProdutos($this->idPreProjeto);
//        $this->view->ExcluirItem = $ExcluirItem;

        $this->view->idPreProjeto = $this->idPreProjeto;

    }

    public function custosadministrativosAction() {

        $buscarEtapas = ManterorcamentoDAO::buscarCustosAdministrativos();
        $this->view->Etapas = $buscarEtapas;

        $buscarCustos = ManterorcamentoDAO::buscarItensCustosAdministrativos($this->idPreProjeto,"A");
        $this->view->EtapaCusto = $buscarCustos;
        
        $buscaDados = ManterorcamentoDAO::buscarDadosCadastrarCustos($this->idPreProjeto);
        $this->view->dados = $buscaDados;

        $buscarEstado = EstadoDAO::buscar();
        $this->view->Estados = $buscarEstado;

        $buscarEtapaCusto = ManterorcamentoDAO::buscarEtapasCusto();
        $this->view->Etapa = $buscarEtapaCusto;

        $this->view->idPreProjeto = $this->idPreProjeto;

    }

    public function planilhaorcamentariaAction() {
        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function planilhaorcamentariageralAction(){
        $this->view->tipoPlanilha = 0; // 0=Planilha Orçamentária da Proposta
    }

    public function consultarcomponenteAction() {
        $this->_helper->layout->disableLayout(); // desabilita o layout


        if(!empty($this->idPreProjeto) || $this->idPreProjeto=='0') {

            $buscarEstado = EstadoDAO::buscar();
            $this->view->Estados = $buscarEstado;

            $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
            $this->view->Produtos = $buscarProduto;

            $buscarEtapa = ManterorcamentoDAO::buscarEtapasProdutos($this->idPreProjeto);
            $this->view->Etapa = $buscarEtapa;

            $buscarItem = ManterorcamentoDAO::buscarItensProdutos($this->idPreProjeto);
            $this->view->Item = $buscarItem;

            $buscarPlanilhaProduto = ManterorcamentoDAO::buscarPlanilhaOrcamentariaP($this->idPreProjeto);
            $this->view->PlanilhaProduto = $buscarPlanilhaProduto;

            $buscarPlanilhaCustos = ManterorcamentoDAO::buscarPlanilhaOrcamentariaC($this->idPreProjeto);
            $this->view->PlanilhaCusto = $buscarPlanilhaCustos;

            $buscarPlanilha = ManterorcamentoDAO::buscarPlanilha($this->idPreProjeto);
            $this->view->Planilha = $buscarPlanilha;

            $buscarPlanilhaEtapa = ManterorcamentoDAO::buscarPlanilhaEtapa($this->idPreProjeto);
            $this->view->PlanilhaEtapa = $buscarPlanilhaEtapa;

            $this->view->idPreProjeto = $this->idPreProjeto;}
            
        else {
            return false;
        }
    }

    public function cadastrarprodutosAction() {
        $this->_helper->layout->disableLayout();

        $this->view->idPreProjeto = $this->idPreProjeto;
        
        /*if  ( isset ( $_GET['proposta'] ) ) {
            $idProposta = $_GET['proposta'];*/
        if  ( isset ( $_GET['idPreProjeto'] ) && isset ( $_GET['produto'] )) {
            $idPreProjeto = $_GET['idPreProjeto'];
            $idProduto = $_GET['produto'];
            $buscaDados = ManterorcamentoDAO::buscarDadosCadastrarProdutos($idPreProjeto, $idProduto);
            $this->view->Dados = $buscaDados;
            $this->view->idProduto = $idProduto;
        }

        if(isset($_POST['iduf'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $iduf = $_POST['iduf'];
            $cidade = CidadeDAO::buscar($iduf);
            $a = 0;
            foreach($cidade as $DadosCidade) {
                $cidadeArray[$a]['idCidade'] = $DadosCidade->id;
                $cidadeArray[$a]['nomeCidade'] = utf8_encode($DadosCidade->descricao);
                $a++;
            }
            echo json_encode($cidadeArray);
            die;
        }

        if(isset($_POST['idetapa'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $idetapa = $_POST['idetapa'];
            $idProduto = $_POST['idProduto'];
            $item = ManterorcamentoDAO::buscarItens($idetapa,$idProduto);
            if (count($item) <= 0) { $item = ManterorcamentoDAO::buscarItens($idetapa,null); }
            $a = 0;
            foreach($item as $Dadositem) {
                $itemArray[$a]['idItem'] = $Dadositem->idPlanilhaItens;
                $itemArray[$a]['nomeItem'] = utf8_encode($Dadositem->Descricao);
                $a++;
            }
            echo json_encode($itemArray);
            die;
        }

        $etapaSelecionada["id"] = $_GET["etapa"];
        $etapaSelecionada["etapaNome"] = $_GET["etapaNome"];
        $this->view->etapaSelecionada = $etapaSelecionada;

        $buscarEstado = EstadoDAO::buscar();
        $this->view->Estados = $buscarEstado;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasCadastrarProdutos();
        $this->view->Etapa = $buscarEtapa;

        $buscarRecurso = ManterorcamentoDAO::buscarFonteRecurso();
        $this->view->Recurso = $buscarRecurso;

        $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
        $this->view->Unidade = $buscarUnidade;

        $buscarItem = ManterorcamentoDAO::buscarItensProdutos($this->idPreProjeto);
        $this->view->Item = $buscarItem;

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;
        
        
    }

    public function cadastrarcustosAction() {
        $this->_helper->layout->disableLayout();

        /*if  ( isset ( $_GET['proposta'] ) ) {
            $idProposta = $_GET['proposta'];*/
        if  ( isset ( $_GET['idPreProjeto'] ) ) {
            $idPreProjeto = $_GET['idPreProjeto'];

            $buscaDados = ManterorcamentoDAO::buscarDadosCadastrarCustos($idPreProjeto);

            $this->view->dados = $buscaDados;
        }

        if( isset( $_GET['cadastro'] ) ) {
            $dados_cadastrados = ManterorcamentoDAO::buscarUltimosDadosCadastrados();
            $this->view->dados_cadastrados = $dados_cadastrados;

            $cidade = CidadeDAO::buscar($dados_cadastrados[0]['UfDespesa']);
            $this->view->municipios = $cidade;

            $itens = ManterorcamentoDAO::buscarItens($dados_cadastrados[0]['idEtapa']);
            $this->view->item = $itens;

        } else {
            $this->view->dados_cadastrados = array();
        }

        if(isset($_POST['iduf'])) {

            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $iduf = $_POST['iduf'];

            $cidade = CidadeDAO::buscar($iduf);
            $a = 0;
            foreach($cidade as $DadosCidade) {
                $cidadeArray[$a]['idCidade'] = $DadosCidade->id;
                $cidadeArray[$a]['nomeCidade'] = utf8_encode($DadosCidade->descricao);
                $a++;
            }
            echo json_encode($cidadeArray);
            die;
        }

        if(isset($_POST['idetapa'])) {
        	$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $idetapa = $_POST['idetapa'];
            $item = ManterorcamentoDAO::buscarItens($idetapa);
            //xd($item);
            $a = 0;
            foreach($item as $Dadositem) {
                $itemArray[$a]['idItem'] = $Dadositem->idPlanilhaItens;
                $itemArray[$a]['nomeItem'] = utf8_encode($Dadositem->Descricao);
                $a++;
            }
            echo json_encode($itemArray);
            die;
        }

        $etapaSelecionada["id"] = $_GET["etapa"];
        $etapaSelecionada["etapaNome"] = $_GET["etapaNome"];
        $this->view->etapaSelecionada = $etapaSelecionada;

        $buscarEstado = EstadoDAO::buscar();
        $this->view->Estados = $buscarEstado;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasCusto();
        $this->view->Etapa = $buscarEtapa;

        $buscarRecurso = ManterorcamentoDAO::buscarFonteRecurso();
        $this->view->Recurso = $buscarRecurso;

        $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
        $this->view->Unidade = $buscarUnidade;

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function editarprodutosAction () {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
   		
        if(isset($_POST['produto'])) {

            $idProposta = $_POST['proposta'];
            $idProduto = $_POST['produto'];
            $idUf = $_POST['uf'];
            $municipio = $_POST['municipio'];
            $idEtapa = $_POST['etapa'];
            $idItem = $_POST['item'];
            $unidade = $_POST['unidade'];
            $qtd = $_POST['qtd'];
            $ocorrencia = $_POST['ocorrencia'];
            $valor = str_replace(",",".",str_replace(".","",$_POST['vlunitario']));
            $qtdDias = $_POST['qtdDias'];
            $fonte = $_POST['fonterecurso'];
           
            $dados = array(
                    'idEtapa' =>$_POST['etapa'],
                    'idPlanilhaItem' =>$_POST['item'],
                    'Unidade' =>$_POST['unidade'],
                    'Quantidade' =>$_POST['qtd'],
                    'Ocorrencia' =>$_POST['ocorrencia'],
                    'ValorUnitario' => str_replace(",",".",str_replace(".","",$_POST['vlunitario'])),
                    'QtdeDias' =>$_POST['qtdDias'],
                    'FonteRecurso' =>$_POST['fonterecurso'],
                    'UfDespesa' =>$_POST['uf'],
                    'MunicipioDespesa' =>$_POST['municipio'],
                    'dsJustificativa' =>$_POST['editor1']
            );

            $where = "idPlanilhaProposta = ".$_POST['proposta'];
            
            $buscarProdutos = ManterorcamentoDAO::buscarDadosEditarProdutos(null, $idEtapa, $idProduto, $idItem, null, $idUf, $municipio, 
            														$unidade, $qtd, $ocorrencia, $valor, $qtdDias, $fonte);

       /*     if(count($buscarProdutos)  0){
            	//parent::message("Item já cadastrado com a mesma UF!", "manterorcamento/produtoscadastrados?idPreProjeto=".$idProposta,"ALERT");
            	$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
	            echo "Cadastro duplicado de Produto na mesma etapa envolvendo o mesmo Item, transa&ccedil;&atilde;o cancelada!";
	            die;
            }else{ 
                xd($dados);*/
	            ManterorcamentoDAO::editarPlanilhaProdutos($dados, $where);
                    $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
	            //parent::message("Alteração realizada com sucesso!", "manterorcamento/produtoscadastrados?idPreProjeto=".$this->idPreProjeto, "CONFIRM");
	            echo "Altera&ccedil;&atilde;o realizada com sucesso!";
	            die;
        /*    } */
        }

        if  ( isset ( $_GET ) ) {

            $idProposta = $_GET['idPreProjeto'];
            $idEtapa = $_GET['etapa'];
            $idProduto = $_GET['produto'];
            $idItem = $_GET['item'];
            $idPlanilhaProposta = $_GET['idPlanilhaProposta'];

            $buscaDados = ManterorcamentoDAO::buscarDadosEditarProdutos($idProposta, $idEtapa, $idProduto, $idItem, $idPlanilhaProposta);
            $this->view->Dados = $buscaDados;
        }

        if(isset($_POST['iduf'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $iduf = $_POST['iduf'];
            $cidade = CidadeDAO::buscar($iduf);
            $a = 0;
            foreach($cidade as $DadosCidade) {
                $cidadeArray[$a]['idCidade'] = $DadosCidade->id;
                $cidadeArray[$a]['nomeCidade'] = utf8_encode($DadosCidade->descricao);
                $a++;
            }
            echo json_encode($cidadeArray);
            die;
        }

        if(isset($_POST['idetapa'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $idetapa = $_POST['idetapa'];
            $item = ManterorcamentoDAO::buscarItens($idetapa);
            $a = 0;
            foreach($item as $Dadositem) {
                $itemArray[$a]['idItem'] = $Dadositem->idPlanilhaItens;
                $itemArray[$a]['nomeItem'] = utf8_encode($Dadositem->Descricao);
                $a++;
            }
            echo json_encode($itemArray);
            die;
        }

        $buscarEstado = EstadoDAO::buscar();
        $this->view->Estados = $buscarEstado;

        $cidade = CidadeDAO::buscar($buscaDados[0]->IdUf);
        $this->view->Cidades = $cidade;

        $itensEtapaCusto = ManterorcamentoDAO::buscarEtapasCusto();
        $this->view->itensEtapaCusto = $itensEtapaCusto;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasCadastrarProdutos();

        $this->view->Etapa = $buscarEtapa;

        $buscarRecurso = ManterorcamentoDAO::buscarFonteRecurso();
        $this->view->Recurso = $buscarRecurso;

        $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
        $this->view->Unidade = $buscarUnidade;

        //$buscarItem = ManterorcamentoDAO::buscarItensProdutos($idItem);

        $buscarItem = ManterorcamentoDAO::buscarItens($_GET['etapa']);

        $this->view->Item = $buscarItem;

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function editarcustosAction () {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
		
        if  ( isset ( $_GET ) && count($_GET)> 0 ) {
        	
            $buscaDados = ManterorcamentoDAO::buscarDadosCustos($_GET);
            $this->view->Dados = $buscaDados;
            
           // xd($buscaDados);
        }
		
        if(isset($_POST['iduf'])) {
        	
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $iduf = $_POST['iduf'];
            $cidade = CidadeDAO::buscar($iduf);
            $a = 0;
            foreach($cidade as $DadosCidade) {
                $cidadeArray[$a]['idCidade'] = $DadosCidade->id;
                $cidadeArray[$a]['nomeCidade'] = utf8_encode($DadosCidade->descricao);
                $a++;
            }
            echo json_encode($cidadeArray);
            die;
        }
		
        if(isset($_POST['idetapa'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $idetapa = $_POST['idetapa'];
            $item = ManterorcamentoDAO::buscarItens($idetapa);
            $a = 0;
            foreach($item as $Dadositem) {
                $itemArray[$a]['idItem'] = $Dadositem->idPlanilhaItens;
                $itemArray[$a]['nomeItem'] = utf8_encode($Dadositem->Descricao);
                $a++;
            }
            echo json_encode($itemArray);
            die;
        }

        $buscarEstado = EstadoDAO::buscar();
        $this->view->Estados = $buscarEstado;
		
        $cidade = CidadeDAO::buscar($buscaDados[0]->IdUf);
        $this->view->Cidades = $cidade;
        

        $itensEtapaCusto = ManterorcamentoDAO::buscarEtapasCusto();
        $this->view->itensEtapaCusto = $itensEtapaCusto;

        $buscarEtapa = ManterorcamentoDAO::buscarEtapasCadastrarProdutos();
        $this->view->Etapa = $buscarEtapa;

        $buscarRecurso = ManterorcamentoDAO::buscarFonteRecurso();
        $this->view->Recurso = $buscarRecurso;

        $buscarUnidade = ManterorcamentoDAO::buscarUnidade();
        $this->view->Unidade = $buscarUnidade;

        $buscarItem = ManterorcamentoDAO::buscarItensProdutos($this->idPreProjeto);
        $this->view->Item = $buscarItem;

        $buscarItens = ManterorcamentoDAO::buscarItens($_GET['etapa']);
        $this->view->ListaItens = $buscarItens;

        $buscaDados = ManterorcamentoDAO::buscarDadosCadastrarCustos($_GET['idPreProjeto']);

        $this->view->dados = $buscaDados;

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function salvarprodutosAction () {

        if  ( isset ( $_POST ) ) {

            $idProposta = $_POST['idPreProjeto'];
            $idProduto = $_POST['produto'];
            $idUf = $_POST['uf'];
            $idMunicipio = $_POST['municipio'];
            $idEtapa = $_POST['etapa'];
            $idItem = $_POST['item'];
            $fonte = $_POST['fonterecurso'];
            $unidade = $_POST['unidade'];
            $quantidade = $_POST['quantidade'];
            $ocorrencia = $_POST['ocorrencia'];
            $vlunitario = str_replace(",", ".",  str_replace(".", "",  $_POST['vlunitario']));
            $qtdDias = $_POST['qtdDias'];
            $justificativa = utf8_decode(substr(trim(strip_tags($_POST['editor1'])),0,500));

            $buscarProdutos = ManterorcamentoDAO::buscarDadosEditarProdutos($idProposta, $idEtapa, $idProduto, $idItem, null, $idUf);
            
            if($buscarProdutos){
            	//parent::message("Item já cadastrado com a mesma UF!", "manterorcamento/produtoscadastrados?idPreProjeto=".$idProposta,"ALERT");
            	$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
	            echo "Cadastro duplicado de Produto na mesma etapa envolvendo o mesmo Item, transa&ccedil;&atilde;o cancelada! Deseja cadastrar um novo item?";
	            die;
            }else{
	            $salvarProdutos = ManterorcamentoDAO::salvarNovoProduto($idProposta, $idProduto, $idEtapa, $idItem, $unidade, $quantidade, $ocorrencia, $vlunitario, $qtdDias, $fonte, $idUf, $idMunicipio, $justificativa, $this->idUsuario);
	            $this->view->SalvarNovo = $salvarProdutos;
	            
	            //parent::message("Cadastro realizado com sucesso!", "manterorcamento/produtoscadastrados?idPreProjeto=".$idProposta,"CONFIRM");
	            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
	            echo "Item cadastrado com sucesso. Deseja cadastrar um novo item?";
	            die;
            }
        }

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function salvarcustosAction () {

        if  ( isset ( $_POST ) ) {

            $idProposta = $_POST['idPreProjeto'];
            $idUf = $_POST['uf'];
            $idMunicipio = $_POST['municipio'];
            $idEtapa = $_POST['etapa'];
            $idItem = $_POST['item'];
            $fonte = $_POST['fonterecurso'];
            $unidade = $_POST['unidade'];
            $quantidade = $_POST['qtd'];
            $ocorrencia = $_POST['ocorrencia'];
            $vlunitario = str_replace(",", ".", str_replace(".", "",  $_POST['vlunitario']));
            $qtdDias = $_POST['qtdDias'];
            $dsJustificativa = utf8_decode(substr(trim(strip_tags($_POST['editor1'])),0,500));
            $tipoCusto = 'A';
            
            try {

                $db = Zend_Registry::get('db');
                $dados = array(	'idProjeto'=>$idProposta,
                                'idEtapa'=>$idEtapa,
                                'idPlanilhaItem'=>$idItem,
                                'Descricao'=>'',
                                'Unidade'=>$unidade,
                                'Quantidade'=>$quantidade,
                                'Ocorrencia'=>$ocorrencia,
                                'ValorUnitario'=>$vlunitario,
                                'QtdeDias'=>$qtdDias,
                                'TipoDespesa'=>'0',
                                'TipoPessoa'=>'0',
                                'Contrapartida'=>'0',
                                'FonteRecurso'=>$fonte,
                                'UfDespesa'=>$idUf,
                                'MunicipioDespesa'=>$idMunicipio,
                                'idUsuario'=>462,
                                'dsJustificativa'=>$dsJustificativa
                                );

                if($_POST['acao']== 'alterar') {
                	
                	$buscarCustos = ManterorcamentoDAO::buscarCustos($idProposta, $tipoCusto, $idEtapa, $idItem, $idUf, $idMunicipio, 
                								$fonte, $unidade, $quantidade, $ocorrencia, $vlunitario, $qtdDias, $dsJustificativa);
                	/*if($buscarCustos){
                		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
			            echo "Cadastro duplicado de Custo na mesma etapa envolvendo o mesmo Item, transa&ccedil;&atilde;o cancelada!";
			            die;
                	}else{*/
	                    $where = 'idPlanilhaProposta = ' . $_POST['idPlanilhaProposta'];
						
	                    $db->update('SAC.dbo.tbPlanilhaProposta',$dados, $where);
	                    //parent::message("Alteração realizada com sucesso!", "manterorcamento/custosadministrativos?idPreProjeto=".$idProposta ,"CONFIRM");
	                    $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
	                    echo "Altera&ccedil;&atilde;o realizada com sucesso!";
	                    die;
                	/*}*/
                }
                else {
                	$buscarCustos = ManterorcamentoDAO::buscarCustos($idProposta, $tipoCusto, $idEtapa, $idItem, $idUf, $idMunicipio);
                	if($buscarCustos){
                		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
			            echo "Cadastro duplicado de Custo na mesma etapa envolvendo o mesmo Item, transa&ccedil;&atilde;o cancelada! Deseja cadastrar um novo item?";
			            die;
                	}else{
	                    $db->insert('SAC.dbo.tbPlanilhaProposta',$dados);
	
	                    $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
	                    echo "Item cadastrado com sucesso. Deseja cadastrar um novo item?";
	                    die;
                	}
                }
            }
            catch (Zend_Exception $e) {

                //parent::message("Erro ao cadastrar dados", "manterorcamento/custosadministrativos?idPreProjeto=".$idProposta ,"ERROR");
                $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
                echo "Erro ao cadastrar dados";
                die;
            }
        }

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function salvarmesmoprodutoAction () {
        if  ( isset ( $_POST ) ) {
            $dados = array(
                    'idProjeto'=>$_POST['proposta'],
                    'idProduto'=>$POST['produto'],
                    'idEtapa'=>$_POST['etapa'],
                    'idPlanilhaItem'=>$_POST['etapa'],
                    'Unidade'=>$_POST['unidade'],
                    'Quantidade'=>$_POST['quantidade'],
                    'Ocorrencia'=>$_POST['ocorrencia'],
                    'valorUnitario'=>$_POST['vlunitario'],
                    'QtdeDias'=>$_POST['qtdDias'],
                    'FonteRecurso'=>$_POST['fonterecurso'],
                    'ufDespesa'=>$_POST['uf'],
                    'MunicipioDespesa'=>$_POST['municipio'],
                    idUsuario=>462
            );

            $where = "(pp.idProjeto = ". $_POST['proposta']." and pp.idProduto = ".$POST['produto']." and pp.idUsuario = 462)";
            print_r($dados);
            die;
            $salvarProdutos = ManterorcamentoDAO::updateProdutos($dados, $where);
            $this->view->Salvar = $salvarProdutos;
        }
        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function excluiritensprodutosAction()
    {

        $idPlanilhaProposta = $_GET['idPlanilhaProposta'];
        $retorno = $_GET['retorno'];

        $resposta = ManterorcamentoDAO::excluirItensProdutos($idPlanilhaProposta);

        if($resposta) {
            parent::message("Exclusão realizada com sucesso!", "manterorcamento/".$retorno."?idPreProjeto=".$this->idPreProjeto ,"CONFIRM");
        } else {
            parent::message("Erro ao excluir os dados", "manterorcamento/".$retorno."?idPreProjeto=".$this->idPreProjeto ,"ERROR");
        }
        $this->view->idPreProjeto = $this->idPreProjeto;
    }
    
}