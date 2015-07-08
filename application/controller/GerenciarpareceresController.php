<?php
/**
 * ManterAgentesController
 * @author Equipe RUP - Politec
 * @since 09/08/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @link http://www.politec.com.br
 * @copyright © 2010 - Politec - Todos os direitos reservados.
 */

class GerenciarpareceresController extends GenericControllerNew
{
	/**
	 * @var integer (variável com o id do usuário logado)
	 * @access private
	 */
	private $getIdUsuario = 0;
        private $intTamPag = 10;
        
	public function init()
	{
		$auth = Zend_Auth::getInstance(); // pega a autenticação

		// define as permissões
		$PermissoesGrupo = array();
		$PermissoesGrupo[] = 93;  // Parecerista
		$PermissoesGrupo[] = 94;  // Coordenador de Parecer UC 101
		$PermissoesGrupo[] = 97;  // Gestor Salic
		$PermissoesGrupo[] = 103;  // Coordenador de Análise
		$PermissoesGrupo[] = 110;  // Técnico de Análise
		
		parent::perfil(1, $PermissoesGrupo);

		// pega o idAgente do usuário logado
		if (isset($auth->getIdentity()->usu_codigo))
		{
			$this->getIdUsuario = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
			if ($this->getIdUsuario)
			{
				$this->getIdUsuario = $this->getIdUsuario["idAgente"];
			}
			else
			{
				$this->getIdUsuario = 0;
			}
		}
		else
		{
			$this->getIdUsuario = $auth->getIdentity()->IdUsuario;
		}

		parent::init();
	} 
	
	public function gerarpdfAction()
	{
		$this->_helper->layout->disableLayout();       
	}
	
	public function gerarxlsAction()
	{
		$this->_helper->layout->disableLayout();       
	}
	
	
	public function indexAction(){
        $this->view->tipos = TramitarDocumentosDAO::listaTipoDocumentos();
        /* ================== PAGINACAO ======================*/

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if($this->_request->getParam("qtde")){
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if($this->_request->getParam("ordem")){
            $ordem = $this->_request->getParam("ordem");
            if($ordem == "ASC"){
                $novaOrdem = "DESC";
            }else{
                $novaOrdem = "ASC";
            }
        }else{
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if($this->_request->getParam("campo")){
            $campo = $this->_request->getParam("campo");
            $order = array($campo." ".$ordem);
            $ordenacao = "&campo=".$campo."&ordem=".$ordem;

        }else{
            $campo = null;
            $order = array("DtConsolidacao", "PRONAC");
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            
        /* ================== PAGINACAO ======================*/

        function formatadata($data){
            if($data){
                $dia = substr($data, 0, 2);
                $mes = substr($data, 3, 2);
                $ano = substr($data, 6, 4);
                $dataformatada = $ano."/".$mes."/".$dia;
            } else {
                $dataformatada = null;
            }
            return $dataformatada;
        }

        /** Usuario Logado ************************************************/
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $idusuario 	= $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
        $codGrupo = $GrupoAtivo->codGrupo; //  Perfil ativo na sessão

        $this->view->codOrgao = $codOrgao;
        $this->view->codGrupo = $codGrupo;
        $this->view->idUsuarioLogado = $idusuario;

        $orgSuperior = GerenciarPareceresDAO::buscarUnidades($codOrgao);
        //$orgSuperior = GerenciarPareceresDAO::buscarUnidades($idusuario);
        //xd($codOrgao);
        $org_superior = $orgSuperior[0]->org_superior;
        /******************************************************************/
        $tblProjeto = new Projetos();

        if($_POST){
            $post = Zend_Registry::get('post');

            $idPronac 	= $post->idpronac;
            $pronac 	= $post->pronac;
            $nometc 	= $post->nometc;
            $nomeP 	= $post->nomeP;
            $tipoPesqPronente = $post->tipoPesqPronente;
            $nomeProponente   = $post->nomeProponente;
            $dtI 	= formatadata($post->dtI);
            $dtF 	= formatadata($post->dtF);
            $sutuacaotc = $post->situacaotc;
            $situacao 	= $post->situacao;

            $where = array();
            if(!empty ($idPronac)){
                $where['p.IdPRONAC = ?'] = $idPronac;
                $this->view->idpronac = $idPronac;
            }

            //Pronac **************************************************************
            if(!empty ($pronac)){
                $where['(p.AnoProjeto + p.Sequencial) = ?'] = $pronac;
                $this->view->pronac = $pronac;
            }

            //Nome do Projeto *****************************************************
            if(!empty($nomeP) && $nometc == 1){
                $where['p.NomeProjeto LIKE ?'] = $nomeP."%";
                $this->view->nomeP = $nomeP;
                $this->view->nometc = $nometc;
            }
            if(!empty($nomeP) && $nometc == 2){
                $where['p.NomeProjeto LIKE ?'] = "%".$nomeP."%";
                $this->view->nomeP = $nomeP;
                $this->view->nometc = $nometc;
            }
            if(!empty($nomeP) && $nometc == 3){
                $where['p.NomeProjeto <> ?'] = $nomeP;
                $this->view->nomeP = $nomeP;
                $this->view->nometc = $nometc;
            }

            //Nome do Proponente *****************************************************
            if(!empty($nomeProponente) && $tipoPesqPronente == 1){
                $where['n.Descricao LIKE ?'] = $nomeProponente."%";
                $this->view->nomeProponente = $nomeProponente;
                $this->view->tipoPesqPronente = $tipoPesqPronente;
            }
            if(!empty($nomeProponente) && $tipoPesqPronente == 2){
                $where['n.Descricao LIKE ?'] = "%".$nomeProponente."%";
                $this->view->nomeProponente = $nomeProponente;
                $this->view->tipoPesqPronente = $tipoPesqPronente;
            }
            if(!empty($nomeProponente) && $tipoPesqPronente == 3){
                $where['n.Descricao <> ?'] = $nomeProponente;
                $this->view->nomeProponente = $nomeProponente;
                $this->view->tipoPesqPronente = $tipoPesqPronente;
            }

            //Data de consolidação
            if(($dtI) && ($dtF == null)){
                $where['convert(char(8),pa.DtParecer,112) = ?'] = str_replace("/", "", $dtI);
                $this->view->dtI = $dtI;
            }
            if($dtI && $dtF){
                $this->view->dtI = $dtI;
                $this->view->dtF = $dtF;
                $where['convert(char(8),pa.DtParecer,112) >= ?'] = str_replace("/", "", $dtI);
                $where['convert(char(8),pa.DtParecer,112) <= ?'] = str_replace("/", "", $dtF);
                //$sql .= " AND cast(convert(char(8),pa.DtParecer,112)as smalldatetime) between '".$dtI."' AND '".$dtF."' ";
            }


            /* Situação ************************************************************
            * C09 - Projeto fora da pauta - Proponente Inabilitado
            * C20 - Análise Técnica Concluida
            * C25 - Parecer Técnico desfavorável
            */
            if(($situacao) && ($sutuacaotc == 1)){
                $where['p.Situacao = ?'] = "'".$situacao."'";
                $this->view->situacaotc = $situacaotc;
                $this->view->situacao = $situacao;
            }
            if(($situacao) && ($sutuacaotc == 2)){
                $where['p.Situacao <> ?'] = "'".$situacao."'";
                $this->view->situacaotc = $situacaotc;
                $this->view->situacao = $situacao;
            }

            $where['p.Orgao = ?'] = $codOrgao;
            $where['p.Mecanismo = ?'] = 1;
            $where['p.AnoProjeto > ?'] = '08';
            $where['p.Situacao IN (?)'] = array("C09", "C20", "C25");

            $total = $tblProjeto->buscarProjetosConsolidados($where, $order, null, null, true);
            $fim = $inicio + $this->intTamPag;
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $tblProjeto->buscarProjetosConsolidados($where, $order, $tamanho, $inicio);

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
            $this->view->paginacao = $paginacao;
            //$busca = GerenciarPareceresDAO::projetosConsolidados($idPronac, $pronac, $nometc, $nomeP, $dtI, $dtF, $sutuacaotc, $situacao, $idSecretaria = $org_superior);

        } else {

            $where = array();
            $where['p.Orgao = ?'] = $codOrgao;
            $where['p.Mecanismo = ?'] = 1;
            $where['p.AnoProjeto > ?'] = '08';
            $where['p.Situacao IN (?)'] = array("C09", "C20", "C25");

            $total = $tblProjeto->buscarProjetosConsolidados($where, $order, null, null, true);
            $fim = $inicio + $this->intTamPag;
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $tblProjeto->buscarProjetosConsolidados($where, $order, $tamanho, $inicio);
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
            $this->view->paginacao = $paginacao;
            //$busca = GerenciarPareceresDAO::buscarProjetosConsolidados(null, $idSecretaria = $org_superior);
        }

        $this->view->qtdDocumentos = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
        $this->view->idusuario      = $idusuario; // idusuario
	}
    
	public function imprimirPareceresAction(){
        $this->view->tipos = TramitarDocumentosDAO::listaTipoDocumentos();
        /* ================== PAGINACAO ======================*/

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if($this->_request->getParam("qtde")){
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if($this->_request->getParam("ordem")){
            $ordem = $this->_request->getParam("ordem");
            if($ordem == "ASC"){
                $novaOrdem = "DESC";
            }else{
                $novaOrdem = "ASC";
            }
        }else{
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if($this->_request->getParam("campo")){
            $campo = $this->_request->getParam("campo");
            $order = array($campo." ".$ordem);
            $ordenacao = "&campo=".$campo."&ordem=".$ordem;

        }else{
            $campo = null;
            $order = array("DtConsolidacao", "PRONAC");
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            
        /* ================== PAGINACAO ======================*/

        function formatadata($data){
            if($data){
                $dia = substr($data, 0, 2);
                $mes = substr($data, 3, 2);
                $ano = substr($data, 6, 4);
                $dataformatada = $ano."/".$mes."/".$dia;
            } else {
                $dataformatada = null;
            }
            return $dataformatada;
        }

        /** Usuario Logado ************************************************/
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $idusuario 	= $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
        $codGrupo = $GrupoAtivo->codGrupo; //  Perfil ativo na sessão

        $this->view->codOrgao = $codOrgao;
        $this->view->codGrupo = $codGrupo;
        $this->view->idUsuarioLogado = $idusuario;

        $orgSuperior = GerenciarPareceresDAO::buscarUnidades($codOrgao);
        //$orgSuperior = GerenciarPareceresDAO::buscarUnidades($idusuario);
        //xd($codOrgao);
        $org_superior = $orgSuperior[0]->org_superior;
        /******************************************************************/
        $tblProjeto = new Projetos();

        if($_POST){
            $post = Zend_Registry::get('post');

            $idPronac 	= $post->idpronac;
            $pronac 	= $post->pronac;
            $nometc 	= $post->nometc;
            $nomeP 	= $post->nomeP;
            $tipoPesqPronente = $post->tipoPesqPronente;
            $nomeProponente   = $post->nomeProponente;
            $dtI 	= formatadata($post->dtI);
            $dtF 	= formatadata($post->dtF);
            $sutuacaotc = $post->situacaotc;
            $situacao 	= $post->situacao;

            $where = array();
            if(!empty ($idPronac)){
                $where['p.IdPRONAC = ?'] = $idPronac;
            }

            //Pronac **************************************************************
            if(!empty ($pronac)){
                $where['(p.AnoProjeto + p.Sequencial) = ?'] = $pronac;
            }

            //Nome do Projeto *****************************************************
            if(!empty($nomeP) && $nometc == 1){
                $where['p.NomeProjeto LIKE ?'] = $nomeP."%";
                //$sql .= " AND p.NomeProjeto like '".$nomeP."%' ";
            } else if(!empty($nomeP) && $nometc == 2){
                $where['p.NomeProjeto LIKE ?'] = "%".$nomeP."%";
            } else if(!empty($nomeP) && $nometc == 3){
                $where['p.NomeProjeto <> ?'] = $nomeP;
            } else {
                $where['p.NomeProjeto LIKE ?'] = "%".$nomeP."%";
            }

            //Nome do Proponente *****************************************************
            if(!empty($nomeProponente) && $tipoPesqPronente == 1){
                $where['n.Descricao LIKE ?'] = $nomeProponente."%";
                //$sql .= " AND p.NomeProjeto like '".$nomeP."%' ";
            }
            if(!empty($nomeProponente) && $tipoPesqPronente == 2){
                $where['n.Descricao LIKE ?'] = "%".$nomeProponente."%";
            }
            if(!empty($nomeProponente) && $tipoPesqPronente == 3){
                $where['n.Descricao <> ?'] = $nomeProponente;
            }

            //Data de consolidação
            if(($dtI) && ($dtF == null)){
                $where['convert(char(8),pa.DtParecer,112) = ?'] = str_replace("/", "", $dtI);
                //$sql .= " AND cast(convert(char(8),pa.DtParecer,112)as smalldatetime) = '".$dtI."'";
            }
            if($dtI && $dtF){
                $where['convert(char(8),pa.DtParecer,112) >= ?'] = str_replace("/", "", $dtI);
                $where['convert(char(8),pa.DtParecer,112) <= ?'] = str_replace("/", "", $dtF);
                //$sql .= " AND cast(convert(char(8),pa.DtParecer,112)as smalldatetime) between '".$dtI."' AND '".$dtF."' ";
            }

            /* Situação ************************************************************
            * C09 - Projeto fora da pauta - Proponente Inabilitado
            * C20 - Análise Técnica Concluida
            * C25 - Parecer Técnico desfavorável
            */
            if(($situacao) && ($sutuacaotc == 1)){
                $where['p.Situacao = ?'] = "'".$situacao."'";
            }
            if(($situacao) && ($sutuacaotc == 2)){
                $where['p.Situacao <> ?'] = "'".$situacao."'";
            }

            $where['p.Orgao = ?'] = $codOrgao;
            $where['p.Mecanismo = ?'] = 1;
            $where['p.AnoProjeto > ?'] = '08';
            $where['p.Situacao IN (?)'] = array("C09", "C20", "C25");

            $total = $tblProjeto->buscarProjetosConsolidados($where, $order, null, null, true);
            $fim = $inicio + $this->intTamPag;
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $tblProjeto->buscarProjetosConsolidados($where, $order, $tamanho, $inicio);

        } else {

            $where = array();
            $where['p.Orgao = ?'] = $codOrgao;
            $where['p.Mecanismo = ?'] = 1;
            $where['p.AnoProjeto > ?'] = '08';
            $where['p.Situacao IN (?)'] = array("C09", "C20", "C25");

            $total = $tblProjeto->buscarProjetosConsolidados($where, $order, null, null, true);
            $fim = $inicio + $this->intTamPag;
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $tblProjeto->buscarProjetosConsolidados($where, $order, $tamanho, $inicio);
        }

        $this->view->qtdDocumentos = $total;
        $this->view->dados         = $busca;
        $this->view->intTamPag     = $this->intTamPag;
        $this->view->idusuario      = $idusuario; // idusuario
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
	}
    
    public function xlsPareceresAction() {
        $this->view->tipos = TramitarDocumentosDAO::listaTipoDocumentos();
        /* ================== PAGINACAO ======================*/

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if($this->_request->getParam("qtde")){
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if($this->_request->getParam("ordem")){
            $ordem = $this->_request->getParam("ordem");
            if($ordem == "ASC"){
                $novaOrdem = "DESC";
            }else{
                $novaOrdem = "ASC";
            }
        }else{
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if($this->_request->getParam("campo")){
            $campo = $this->_request->getParam("campo");
            $order = array($campo." ".$ordem);
            $ordenacao = "&campo=".$campo."&ordem=".$ordem;

        }else{
            $campo = null;
            $order = array("DtConsolidacao", "PRONAC");
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            
        /* ================== PAGINACAO ======================*/

        function formatadata($data){
            if($data){
                $dia = substr($data, 0, 2);
                $mes = substr($data, 3, 2);
                $ano = substr($data, 6, 4);
                $dataformatada = $ano."/".$mes."/".$dia;
            } else {
                $dataformatada = null;
            }
            return $dataformatada;
        }

        /** Usuario Logado ************************************************/
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $idusuario 	= $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
        $codGrupo = $GrupoAtivo->codGrupo; //  Perfil ativo na sessão

        $this->view->codOrgao = $codOrgao;
        $this->view->codGrupo = $codGrupo;
        $this->view->idUsuarioLogado = $idusuario;

        $orgSuperior = GerenciarPareceresDAO::buscarUnidades($codOrgao);
        //$orgSuperior = GerenciarPareceresDAO::buscarUnidades($idusuario);
        //xd($codOrgao);
        $org_superior = $orgSuperior[0]->org_superior;
        /******************************************************************/
        $tblProjeto = new Projetos();

        $post = Zend_Registry::get('post');

        $idPronac 	= $post->idpronac;
        $pronac 	= $post->pronac;
        $nometc 	= $post->nometc;
        $nomeP 	= $post->nomeP;
        $tipoPesqPronente = $post->tipoPesqPronente;
        $nomeProponente   = $post->nomeProponente;
        $dtI 	= formatadata($post->dtI);
        $dtF 	= formatadata($post->dtF);
        $sutuacaotc = $post->situacaotc;
        $situacao 	= $post->situacao;

        $where = array();
        if(!empty ($idPronac)){
            $where['p.IdPRONAC = ?'] = $idPronac;
        }

        //Pronac **************************************************************
        if(!empty ($pronac)){
            $where['(p.AnoProjeto + p.Sequencial) = ?'] = $pronac;
        }

        //Nome do Projeto *****************************************************
        if(!empty($nomeP) && $nometc == 1){
            $where['p.NomeProjeto LIKE ?'] = $nomeP."%";
            //$sql .= " AND p.NomeProjeto like '".$nomeP."%' ";
        } else if(!empty($nomeP) && $nometc == 2){
            $where['p.NomeProjeto LIKE ?'] = "%".$nomeP."%";
        } else if(!empty($nomeP) && $nometc == 3){
            $where['p.NomeProjeto <> ?'] = $nomeP;
        } else {
            $where['p.NomeProjeto LIKE ?'] = "%".$nomeP."%";
        }

        //Nome do Proponente *****************************************************
        if(!empty($nomeProponente) && $tipoPesqPronente == 1){
            $where['n.Descricao LIKE ?'] = $nomeProponente."%";
            //$sql .= " AND p.NomeProjeto like '".$nomeP."%' ";
        }
        if(!empty($nomeProponente) && $tipoPesqPronente == 2){
            $where['n.Descricao LIKE ?'] = "%".$nomeProponente."%";
        }
        if(!empty($nomeProponente) && $tipoPesqPronente == 3){
            $where['n.Descricao <> ?'] = $nomeProponente;
        }

        //Data de consolidação
        if(($dtI) && ($dtF == null)){
            $where['convert(char(8),pa.DtParecer,112) = ?'] = str_replace("/", "", $dtI);
            //$sql .= " AND cast(convert(char(8),pa.DtParecer,112)as smalldatetime) = '".$dtI."'";
        }
        if($dtI && $dtF){
            $where['convert(char(8),pa.DtParecer,112) >= ?'] = str_replace("/", "", $dtI);
            $where['convert(char(8),pa.DtParecer,112) <= ?'] = str_replace("/", "", $dtF);
            //$sql .= " AND cast(convert(char(8),pa.DtParecer,112)as smalldatetime) between '".$dtI."' AND '".$dtF."' ";
        }

        /* Situação ************************************************************
        * C09 - Projeto fora da pauta - Proponente Inabilitado
        * C20 - Análise Técnica Concluida
        * C25 - Parecer Técnico desfavorável
        */
        if(($situacao) && ($sutuacaotc == 1)){
            $where['p.Situacao = ?'] = "'".$situacao."'";
        }
        if(($situacao) && ($sutuacaotc == 2)){
            $where['p.Situacao <> ?'] = "'".$situacao."'";
        }

        $where['p.Orgao = ?'] = $codOrgao;
        $where['p.Mecanismo = ?'] = 1;
        $where['p.AnoProjeto > ?'] = '08';
        $where['p.Situacao IN (?)'] = array("C09", "C20", "C25");

        $total = $tblProjeto->buscarProjetosConsolidados($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $tblProjeto->buscarProjetosConsolidados($where, $order, $tamanho, $inicio);

        $html = "<table cellspacing='0' cellpadding='2' border='1' align='center' width='99%'>
                <tr>
                    <th width='100' align='left'>PRONAC</th>
                    <th width='50' align='left'>Nome do Projeto</th>
                    <th width='100' align='left'>Situação</th>
                    <th width='100' align='center'>Nome do Proponente</th>
                    <th width='100' align='center'>Dt. Consolidação</th>
                </tr>";
        
        foreach($busca as $d){
            $html .= "  <tr>
                            <td>".$d->PRONAC."</td>
                            <td>".$d->NomeProjeto."</td>
                            <td>".$d->Situacao."</td>
                            <td>".$d->NomeProponente."</td>
                            <td align='center'>".$d->DtConsolidacao."</td>
                    </tr>";
        };
        $html .="</table>";
        $this->view->html = $html;
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
    }
	
	public function desconsolidarparecerAction()
	{
		
		$this->view->idpronac = $this->_request->getParam("idpronac");
		$this->view->idproduto   = $this->_request->getParam("idproduto");
		$this->view->tipoanalise = $this->_request->getParam("tipoanalise");
		
		$this->view->dados = GerenciarPareceresDAO::parecereConsolidar($this->_request->getParam("idpronac"), 
                $this->_request->getParam("idproduto"),
                $this->_request->getParam("tipoanalise"));
		
	}
	
	public function dadosdaanalisetecnicaAction()
	{
		/** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // instancia da autenticação
		$idusuario 	= $auth->getIdentity()->usu_codigo;
		/******************************************************************/
		
		$idPronac = $this->_request->getParam("idpronac");
	 	
	 	$this->view->dados = GerenciarPareceresDAO::projetosConsolidados($idPronac);
	 	
	 	$this->view->diligencias = GerenciarPareceresDAO::buscaDiligencias($idPronac);
		
		$this->view->idpronac = $idPronac;	
	}
	
	public function devolverparaanaliseAction()
	{
                $this->_helper->layout->disableLayout();
		$this->view->idpronac = $this->_request->getParam("idpronac");
		$this->view->dados = GerenciarPareceresDAO::produtoPrincipal($this->_request->getParam("idpronac") );
	}
	
	
	public function devolverprojetoAction() {

            /** Usuario Logado ************************************************/
            $auth = Zend_Auth::getInstance(); // instancia da autenticação
            $idusuario 	= $auth->getIdentity()->usu_codigo;
            /******************************************************************/

            $idpronac   = $this->_request->getParam("idpronac");
            $idorgao    = $this->_request->getParam("idorgao");
            $observacao = $this->_request->getParam("observacao");

            $db = Zend_Registry :: get('db');
            $db->setFetchMode(Zend_DB :: FETCH_OBJ);

            try {
                $db->beginTransaction();

                $tbDistribuirParecer = new tbDistribuirParecer();
                $dadosWhere["t.idPRONAC = ?"] = $idpronac;
                $dadosWhere["t.stEstado = ?"] = 0;
                $dadosWhere["t.TipoAnalise in (?)"] = array(1,3);
                $buscaDadosProjeto = $tbDistribuirParecer->dadosParaDistribuir($dadosWhere);

                foreach($buscaDadosProjeto as $dp){

                    $dadosE = array(
                            'idOrgao'       		=> $dp->idOrgao,
//                            'DtEnvio'       		=> $dp->DtEnvio,
                            'DtEnvio'       		=> new Zend_Db_Expr("GETDATE()"),
                            'idAgenteParecerista'	=> null,
                            'DtDistribuicao'		=> null,
                            'DtDevolucao'   		=> null,
                            'DtRetorno'     		=> new Zend_Db_Expr("GETDATE()"),
                            'FecharAnalise' 		=> 2,
                            'Observacao'    		=> $observacao,
                            'idUsuario'     		=> $idusuario,
                            'idPRONAC'      		=> $dp->IdPRONAC,
                            'idProduto'     		=> $dp->idProduto,
                            'TipoAnalise'   		=> 3, //O valor 3 e o novo valor acordado (entre os gestores) para tratar projetos que estarao acessiveis no mod. parecerista, pois para montar a grid o mod. busca projetos com TipoAnalise=3
                            'stEstado'      		=> 0,
                            'stPrincipal'   		=> $dp->stPrincipal,
                            'stDiligenciado'   		=> $dp->stDiligenciado
                    );

                    $where['idDistribuirParecer = ?']  = $dp->idDistribuirParecer;
                    $salvar = $tbDistribuirParecer->alterar(array('stEstado' => 1), $where);
                    $insere = $tbDistribuirParecer->inserir($dadosE);
                }
                $atualizaProjeto = GerenciarPareceresDAO::atualizaProjeto($idpronac, 'B11');
                $db->commit();
                parent::message("Devolvido com sucesso!", "gerenciarpareceres/index", "CONFIRM");

            } catch(Zend_Exception $ex) {
                $db->rollBack();
                parent::message($ex->getMessage(), "gerenciarpareceres/devolverparaanalise/idpronac/".$idpronac ,	"ERROR");
            }
        }
	
	
	public function devolveranaliseAction()
	{
		/** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // instancia da autenticação
		$idusuario 	= $auth->getIdentity()->usu_codigo;
		//$idorgao 	= $auth->getIdentity()->usu_orgao;
		
		$GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
		//$codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
		$codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
		
		
		$this->view->codOrgao = $codOrgao;
		$this->view->idUsuarioLogado = $idusuario;
		/******************************************************************/
		
		$idpronac    = $this->_request->getParam("idpronac");
		$idproduto   = $this->_request->getParam("idproduto");
		$observacao  = $this->_request->getParam("observacao");
		$tipoanalise = $this->_request->getParam("tipoanalise");
		
		if((strlen($observacao) < 11) or (strlen($observacao) > 505))
		{
			parent::message("Campo Justificativa deve conter no mínimo 10 e no máximo 500 caracteres.", 
							"gerenciarpareceres/devolverparaanalise/idproduto/".$idproduto."/tipoanalise/".$tipoanalise."/idpronac/".$idpronac ,
							"ALERT");
		}	
			
			
		$db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        	
		try
		{
			$db->beginTransaction();	
			$atualiza = GerenciarPareceresDAO::devolverParecer($idpronac, $idproduto, $observacao, $tipoanalise, $idusuario);
				
			$db->commit();
			parent::message("Devolvido com sucesso!", "gerenciarpareceres/pareceresaconsolidar", "CONFIRM");	
		}
		catch(Zend_Exception $ex)
		{
			$db->rollBack();
			parent::message("Erro ao devolver!", 
								"gerenciarpareceres/desconsolidarparecer/idproduto/".$idproduto."/tipoanalise/".$tipoanalise."/idpronac/".$idpronac ,
								"ERROR");
		}
		
	}
	
	
	public function dadosdopareceresAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
		
		$idPronac = $this->_request->getParam("idpronac");
	 		 	
	 	$this->view->dados = GerenciarPareceresDAO::pareceresTecnicos($idPronac);
	}
	
	public function visualizarparecerconsolidadoAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
		
		/** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // instancia da autenticação
		$idusuario 	= $auth->getIdentity()->usu_codigo;
		/******************************************************************/
		
	 	$idPronac = $this->_request->getParam("idpronac");
	 	
	 	$this->view->dados = GerenciarPareceresDAO::projetosConsolidados($idPronac);
	 	$this->view->dados2 = GerenciarPareceresDAO::projetosConsolidadosParte2($idPronac);
	} 
	
	public function pareceresaconsolidarAction()
	{
		/** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // instancia da autenticação
		$idusuario 	= $auth->getIdentity()->usu_codigo;
		//$idorgao 	= $auth->getIdentity()->usu_orgao;
		
		$GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
		//$codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
		$codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
		
		$this->view->codOrgao = $codOrgao;
		$this->view->idUsuarioLogado = $idusuario;
		/******************************************************************/
	 	
	 	$busca = GerenciarPareceresDAO::parecereConsolidar();
	 	
	 	
	 	Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginacao/paginacao.phtml');
		$paginator = Zend_Paginator::factory($busca); // dados a serem paginados
		$currentPage = $this->_getParam('page', 1);
		$paginator->setCurrentPageNumber($currentPage)->setItemCountPerPage(20);
	 	
	 	$this->view->dados = $paginator;
        $this->view->qtdDocumentos    = count($busca); // quantidade
	  	
	 	 
	} 
	
	
	public function pcvisualizartramitacaoAction()
	{
		/** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // instancia da autenticação
		$idusuario 	= $auth->getIdentity()->usu_codigo;
		//$idorgao 	= $auth->getIdentity()->usu_orgao;
		
		$GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
		//$codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
		$codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
		
		$this->view->codOrgao = $codOrgao;
		$this->view->idUsuarioLogado = $idusuario;
		/******************************************************************/
	 	
	 	
	 	$idPronac 		= $this->_request->getParam("idpronac");
	 	
	 	$idProduto 		= $this->_request->getParam("idproduto");
	
		$tipoanalisetc 	= $this->_request->getParam("tipoanalisetc");
	 	$tipoanalise 	= $this->_request->getParam("tipoanalise");	
 	
		$produtotc 	= $this->_request->getParam("produtotc");
	 	$produto 	= $this->_request->getParam("produto");	
 	
		$orgaotc 	= $this->_request->getParam("orgaotc");
	 	$orgao 	= $this->_request->getParam("orgao");	
 	
		$unidadetc 	= $this->_request->getParam("unidadetc");
	 	$unidade 	= $this->_request->getParam("unidade");	
 	
 		$busca = GerenciarPareceresDAO::historicoParecerProduto($idPronac, $idProduto, $tipoanalise, $produtotc, $produto, $orgaotc, $orgao, $unidadetc, $unidade);
 		
	 	Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginacao/paginacao.phtml');
		$paginator = Zend_Paginator::factory($busca); // dados a serem paginados
		$currentPage = $this->_getParam('page', 1);
		$paginator->setCurrentPageNumber($currentPage)->setItemCountPerPage(20);
	 	
		
		$html = "<table cellspacing='0' cellpadding='2' border='1' align='center' width='99%'>
					<tr>
						<td colspan='6' height='30' align='center'>VISUALIZAÇÃO DE TRAMITAÇÃO</td>
					</tr>
					<tr>
						<th width='100' align='left'>Produto</th>
						<th width='100' align='left'>TipoAnalise</th>
						<th width='100' align='left'>Unidade</th>
						<th width='120' align='center'>Data de Envio</th>
						<th width='150' align='left'>Observação</th>
						<th width='100' align='left'>Usuario</th>
					</tr>";
					foreach($busca as $d): 
			$html.=	"<tr>
						<td>".$d->Produto."</td>
						<td>".$d->TipoAnalise."</td>
						<td>".$d->Unidade."</td>
						<td align='center'>".$d->DtEnvio."</td>
						<td>".$d->Observacao."</td>
						<td>".$d->Usuario."</td>
					</tr>";
					endforeach;
		$html .="</table>";
		
		
	 	$this->view->dados = $paginator;
        $this->view->qtdDocumentos    = count($busca); // quantidade
        $this->view->html    = $html; // htmlpdf
	 	 
	} 
	
	public function consolidarpareceresAction()
	{
		/** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // instancia da autenticação
		$idusuario 	= $auth->getIdentity()->usu_codigo;
		//$idorgao 	= $auth->getIdentity()->usu_orgao;
		
		$GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
		//$codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
		$codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
		
		$this->view->codOrgao = $codOrgao;
		$this->view->idUsuarioLogado = $idusuario;
		/******************************************************************/
	 	
	 	 
	} 
	
	public function execconsolidacaoAction()
	{
		$exec = GerenciarPareceresDAO::execPareceres();
		//O Procedimento foi executado, porém, não retornou resultados
		if($exec)
		{
			parent::message("O Procedimento foi executado com sucesso!", "gerenciarpareceres/consolidarpareceres", "ALERT");	
		}
		else
		{
			parent::message("Erro ao executar o procedimento!", "gerenciarpareceres/consolidarpareceres", "ERROR");
		}
		
		
	}
	
	
	public function planilhadecustosAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
		
		$idPronac = $this->_request->getParam("idpronac");
		$dados = GerenciarPareceresDAO::analiseDeCustos($idPronac);
		$this->view->buscarProd = $dados;
	}
	
	public function tramitacaoprojetoAction() {

            $idPronac = $this->_request->getParam("idpronac");
            $dadosProjeto = GerenciarPareceresDAO::projetosConsolidados($idPronac , null, null, null, null, null, null, null);
            foreach($dadosProjeto as $dp) {
                $nomeProjeto = $dp->NomeProjeto;
                $PRONAC = $dp->PRONAC;
            }
            $dados = GerenciarPareceresDAO::historicoParecerProduto($idPronac, null, null);
            $p = '';
            $u = '';
            $html = "<table cellspacing='0' cellpadding='2' border='1' align='center' width='99%'>
                    <tr>
                            <td colspan='7' height='30' align='center'>Tramitação do Projeto: ".$nomeProjeto."</td>
                    </tr>
                    <tr>
                            <th width='100' align='left'>Produto</th>
                            <th width='50' align='left'>TipoAnalise</th>
                            <th width='100' align='left'>Unidade</th>
                            <th width='100' align='center'>Data de Envio</th>
                            <th width='100' align='center'>Data de Retorno</th>
                            <th width='100' align='left'>Observação</th>
                            <th width='50' align='left'>Usuario</th>
                    </tr>";
            $idproduto = '';
            $unidade = '';
            foreach($dados as $d):
                if($idproduto != $d->idProduto) {
                    $p = $d->Produto;
                }
                if($unidade != $d->Unidade) {
                    $u = $d->Unidade;
                }
                $html .= "  <tr>
                                <td>".$p."</td>
                                <td>".$d->TipoAnalise."</td>
                                <td>".$u."</td>
                                <td align='center'>".$d->DtEnvio."</td>
                                <td align='center'>".$d->DtDevolucao."</td>
                                <td>".$d->Observacao."</td>
                                <td>".$d->Usuario."</td>
                        </tr>";
                $idproduto = $d->idProduto;
                $unidade = $d->Unidade;
                $p = '';
                $u = '';
            endforeach;
            $html .="</table>";

            $this->view->dados = $dados;
            $this->view->qtd = count($dados);
            $this->view->html = $html;
            $this->view->nomeprojeto = $nomeProjeto;
            $this->view->PRONAC = $PRONAC;
            $this->view->idPronac = $idPronac;
        }

        public function imprimirTramitacaoAction() {

            $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
            $idPronac = $this->_request->getParam("idpronac");
            $dadosProjeto = GerenciarPareceresDAO::projetosConsolidados($idPronac , null, null, null, null, null, null, null);
            foreach($dadosProjeto as $dp) {
                $nomeProjeto = $dp->NomeProjeto;
                $PRONAC = $dp->PRONAC;
            }
            $dados = GerenciarPareceresDAO::historicoParecerProduto($idPronac, null, null);
            $p = '';
            $u = '';
            $html = "<table cellspacing='0' cellpadding='2' border='1' align='center' width='99%'>
                    <tr>
                            <td colspan='7' height='30' align='center'>Tramitação do Projeto: ".$nomeProjeto."</td>
                    </tr>
                    <tr>
                            <th width='100' align='left'>Produto</th>
                            <th width='50' align='left'>TipoAnalise</th>
                            <th width='100' align='left'>Unidade</th>
                            <th width='100' align='center'>Data de Envio</th>
                            <th width='100' align='center'>Data de Retorno</th>
                            <th width='100' align='left'>Observação</th>
                            <th width='50' align='left'>Usuario</th>
                    </tr>";
            $idproduto = '';
            $unidade = '';
            foreach($dados as $d):
                if($idproduto != $d->idProduto) {
                    $p = $d->Produto;
                }
                if($unidade != $d->Unidade) {
                    $u = $d->Unidade;
                }
                $html .= "  <tr>
                                <td>".$p."</td>
                                <td>".$d->TipoAnalise."</td>
                                <td>".$u."</td>
                                <td align='center'>".$d->DtEnvio."</td>
                                <td align='center'>".$d->DtDevolucao."</td>
                                <td>".$d->Observacao."</td>
                                <td>".$d->Usuario."</td>
                        </tr>";
                $idproduto = $d->idProduto;
                $unidade = $d->Unidade;
                $p = '';
                $u = '';
            endforeach;
            $html .="</table>";

            $this->view->dados = $dados;
            $this->view->qtd = count($dados);
            $this->view->html = $html;
            $this->view->nomeprojeto = $nomeProjeto;
            $this->view->PRONAC = $PRONAC;
            $this->view->idPronac = $idPronac;
        }
	
	public function buscarprojetoAction()
	{
		

	}
	
	public function buscarprodutoAction()
	{
		

	}

	public function desconsolidarAction()
	{
		
		$idpronac 	= $this->_request->getParam("idpronac");
		$pronac 	= $this->_request->getParam("pronac");

		Zend_Debug::dump($this->_request->getParams());
		exit();
		
		if($idpronac && $pronac)
		{
			
			// Tem que existir
			$emPauta = GerenciarPareceresDAO::emPauta($idpronac);
			
			// Não pode estár aprovado
			$projetoAprovado = GerenciarPareceresDAO::projetoAprovado($pronac);
			
			if(!$emPauta)
			{
				parent::message("O projeto não está em situação de pauta e não pode ser desconsolidado.", "gerenciarpareceres/index", "ALERT");
			}
				
			if($projetoAprovado)
			{
				parent::message("O projeto já está aprovado e não pode ser desconsolidado.", "gerenciarpareceres/index", "ALERT");
			}
			
			$db = Zend_Registry :: get('db');
	        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
	        	
			try
			{
				$db->beginTransaction();
	
				$delPerecer 				= GerenciarPareceresDAO::delPerecer($idpronac);
				$delEnquadramento 			= GerenciarPareceresDAO::delEnquadramento($idpronac);
				$updatetbAnaliseDeConteudo 	= GerenciarPareceresDAO::updatetbAnaliseDeConteudo($idpronac);
				$updatetbPlanilhaProjeto 	= GerenciarPareceresDAO::updatetbPlanilhaProjeto($idpronac);
				$updateProjetos 			= GerenciarPareceresDAO::updateProjetos($idpronac);
				
				$db->commit();
				parent::message("O Projeto foi Desconsolidado.", "gerenciarpareceres/index", "CONFIRM");	
			}
			catch(Zend_Exception $ex)
			{
				$db->rollBack();
				parent::message("Erro ao desconsolidar o projeto.", "gerenciarpareceres/index", "CONFIRM");
			}
			
					
		}
		else
		{
			parent::message("Projeto não encontrado!", "gerenciarpareceres/index", "ERROR");
		}
		
		
		
	}
	

	public function imprimirParecerTecnicoAction()
	{
            if ($this->getRequest()->isPost()) {

               $nrPronac = $this->_request->getParam("nrPronac");

               $ano = addslashes(substr($nrPronac,0,2));
               $sequencial = addslashes(substr($nrPronac,2,strlen($nrPronac)));

               $arrBusca = array(
                   'pr.anoprojeto =?' => $ano,
                   'pr.sequencial =?' => $sequencial,
               );
               
               $projeto = new Projetos();
               $rsProjeto = $projeto->buscarDadosParaImpressao($arrBusca)->current();
               
               if(count($rsProjeto)<=0){

                    $this->montaTela("gerenciarpareceres/imprimirparecertecnico.phtml",
                               array("mensagem"=>"<font color='red'>Projeto inexistente</font>"));
                    return;
               }
               
               $idPronac = $rsProjeto->IdPRONAC;

               $arrBuscaParecer = array(
                   'a.idUsuario IS NOT NULL' => '?',
                   'p.IdPRONAC =?' => $idPronac,
                   'dp.TipoAnalise =?' => 1,
                   'dp.stEstado =?' => 0,
                   'dp.FecharAnalise =?'=> 1,
               );
               $order = array(
                   'dp.DtDevolucao DESC'
               );
               //$rsPareceres = GerenciarPareceresDAO::pareceresTecnicos($idPronac);
               $rsPareceres = $projeto->buscarPareceresProjetoParaImpressao($arrBuscaParecer,$order);
               $dataValidacao = null;
               if($rsPareceres->count()>0){
                $dataValidacao = ConverteData($rsPareceres[0]->DtDevolucao,5);
               }

               $rsPlanilha = GerenciarPareceresDAO::analiseDeCustos($idPronac);
               
               //METODO QUE MONTA TELA DO USUARIO ENVIANDO TODOS OS PARAMENTROS NECESSARIO DENTRO DO ARRAY
               $this->montaTela("gerenciarpareceres/dadosimpressaoparecer.phtml",
                           array("dadosProjeto"=>$rsProjeto,
                                 "dadosPareceres"=>$rsPareceres,
                                 "dadosPlanilha"=>$rsPlanilha,
                                 "nrPronac"=>$nrPronac,
                                 "dataValidacao"=>$dataValidacao));
               return true;
            }

            //METODO QUE MONTA TELA DO USUARIO ENVIANDO TODOS OS PARAMENTROS NECESSARIO DENTRO DO ARRAY
            $this->montaTela("gerenciarpareceres/imprimirparecertecnico.phtml",
                       array());
	}
	
	

} // fecha class