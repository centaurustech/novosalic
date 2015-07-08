<?php 

/**
 * Controller Disvincular Agentes
 * @author Equipe RUP - Politec
 * @since 08/01/2013
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright 2010 - Ministério da Cultura - Todos os direitos reservados.
 */
class AnalisarsituacaoitemController extends GenericControllerNew {

    private $getIdAgente  = 0;
    private $getIdGrupo   = 0;
    private $getIdOrgao   = 0;
    private $getIdUsuario = 0;
    private $intTamPag = 10;
    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        // verifica as permissões
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 97;  // Gestor do SALIC
        parent::perfil(1, $PermissoesGrupo);

        $Usuario = new Usuario(); // objeto usuário
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $idagente = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->getIdAgente = $idagente['idAgente'];
        $this->getIdGrupo = $GrupoAtivo->codGrupo;
        $this->getIdOrgao = $GrupoAtivo->codOrgao;
        $this->getIdUsuario = $auth->getIdentity()->usu_codigo;
        parent::init();
    }

    public function indexAction() {

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

        }else {
            $campo = null;
            $order = array(4);
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['s.stEstado = ?'] = 0;

        $tbSolicitarItem = new tbSolicitarItem();
        $total = $tbSolicitarItem->listaSolicitacoesItens($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $tbSolicitarItem->listaSolicitacoesItens($where, $order, $tamanho, $inicio);
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
        $this->view->qtdRelatorios = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
    }

    public function detalharAction() {
        $item = 0;
        if($this->_request->getParam("item")) {
            $item = $this->_request->getParam("item");
        } else {
            parent::message("Item n&atilde;o encontrado!", "analisarsituacaoitem", "ERROR");
        }

        $where = array();
        $where['s.idSolicitarItem = ?'] = $item;

        $tbSolicitarItem = new tbSolicitarItem();
        $busca = $tbSolicitarItem->listaSolicitacoesItens($where, array(), null, null);
        $this->view->dados = $busca;
    }

    public function avaliarItemAction() {
        $tbSolicitarItem = new tbSolicitarItem();
        $busca = $tbSolicitarItem->buscarDadosItem($_POST['idItem']);

        $busca->Resposta = $_POST['resposta'];
        $busca->DtResposta = new Zend_Db_Expr('GETDATE()');
        $busca->stEstado = $_POST['avaliacao'];
        $busca->save();

        $tbSolicitarItem = new tbSolicitarItem();
        if($_POST['avaliacao']){
            $msg = 'rejeitado';
        } else {
            $msg = 'aprovado';
        }

        $pa = new paIncluirRecusarItem();
        $pa->incluirRecusarItem($_POST['idItem'], $this->getIdUsuario, $_POST['idItem'], $_POST['avaliacao']);
        parent::message("Item $msg com sucesso!", "analisarsituacaoitem", "CONFIRM");
    }

}