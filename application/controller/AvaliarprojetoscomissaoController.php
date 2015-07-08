<?php



class AvaliarprojetoscomissaoController extends GenericControllerNew {
	
	private $intTamPag = 10;
	private $idAgente = null;
	
	public function init() {
        $this->view->title = "Salic - Sistema de Apoio рs Leis de Incentivo р Cultura"; // tэtulo da pсgina
        $auth = Zend_Auth::getInstance(); // pega a autenticaчуo
        $Usuario = new UsuarioDAO(); // objeto usuсrio
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo

        if ($auth->hasIdentity()) { // caso o usuсrio estja autenticado
            // verifica as permissѕes
            $PermissoesGrupo = array();
            $PermissoesGrupo[] = 114; //Coordenador de Editais
	        
	        if(isset($auth->getIdentity()->usu_codigo)){
	        	parent::perfil(1, $PermissoesGrupo);
	        }else{
	        	parent::perfil(4, $PermissoesGrupo);
	        }
	        
            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) { // verifica se o grupo ativo estс no array de permissѕes
                parent::message("Vocъ nуo tem permissуo para acessar essa сrea do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, orgуos e grupos do usuсrio (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

            // manda os dados para a visуo
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuсrio para a visуo
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuсrio para a visуo
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuсrio para a visуo
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o ѓrgуo ativo do usuсrio para a visуo

        } // fecha if
        else {
            // caso o usuсrio nуo esteja autenticado
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }

        $cpf = $auth->getIdentity()->usu_identificacao;
        $cpf =  trim($cpf);
        $dados = array('CNPJCPF = ?' => $cpf);

        $idAge = new Agentes();
        $idAgente = $idAge->buscarAgenteNome($dados);
        $this->idAgente = $idAgente;
        
        parent::init(); // chama o init() do pai GenericControllerNew
    }
    
    public function indexAction() {
        /** Usuario Logado *********************************************** */
        $auth = Zend_Auth::getInstance(); // instancia da autenticaчуo
        $idusuario = $auth->getIdentity()->usu_codigo;
        $idorgao = $auth->getIdentity()->usu_orgao;
        //$this->_redirect("tramitarprojetos/despacharprojetos");
        
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo
        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessуo
        $codOrgao = $GrupoAtivo->codOrgao; //  гrgуo ativo na sessуo
        $this->view->codGrupo = $codGrupo;

        /*         * *************************************************************** */
    }
    
    public function avaliarcomissaoAction(){
        
    	$tblProjeto = new Projetos();
        $buscaRegiao = AvaliarProjetosComissaoDAO::buscaRegiao();
    	$this->view->buscaRegiao = $buscaRegiao; 

    	$buscaUF = AvaliarProjetosComissaoDAO::buscaUF();
    	$this->view->buscaUF = $buscaUF; 
    	
    	$buscaEdital = AvaliarProjetosComissaoDAO::buscaEdital();
    	$this->view->buscaEdital = $buscaEdital; 
        
    	//PARA APROVAR O PROJETO
        if(!empty($_POST['pronacs'])) {
        	$pronac = $_POST['pronacs'];
        	$idPreProjeto = $_POST['pre'];
        	$nota = str_replace(',', '.', $_POST['nrNota']);
        	$situacao = 'G52';
        	
        	$tblProjeto->alterarSituacao(null,$pronac, $situacao);
        	$aprovacao = AvaliarProjetosComissaoDAO::buscarAprovacao($idPreProjeto);
        	
        	if($aprovacao){
        		AvaliarProjetosComissaoDAO::aprovarProjeto($idPreProjeto, $nota, null, 1, 1);
        	}else {
        		AvaliarProjetosComissaoDAO::aprovarProjeto($idPreProjeto, $nota, null, 1);
        	}
        	
        	parent::message("Projeto aprovado com sucesso!", "Avaliarprojetoscomissao/avaliarcomissao", "CONFIRM");
        }
        
        //PARA REPROVAR O PROJETO
    	if(!empty($_POST['just']) && isset($_POST['pro'])) {
        	$pronac = $_POST['pro'];
        	$nota = str_replace(',', '.', $_POST['nota']);
        	$idPreProjeto = $_POST['idPreProj'];
        	$justificativa = $_POST['justificativa'];
        	$situacao = 'G52';
        	
        	AvaliarProjetosComissaoDAO::alterarNota($nota, $idPreProjeto);
        	$tblProjeto->alterarSituacao(null,$pronac, $situacao);

    		$aprovacao = AvaliarProjetosComissaoDAO::buscarAprovacao($idPreProjeto);
        	
        	if($aprovacao){
        		AvaliarProjetosComissaoDAO::aprovarProjeto($idPreProjeto, $nota, $justificativa, 0, 1);
        	}else {
        		AvaliarProjetosComissaoDAO::aprovarProjeto($idPreProjeto, $nota, $justificativa, 0);
        	}
        	
        	parent::message("Projeto reprovado com sucesso!", "Avaliarprojetoscomissao/avaliarcomissao", "CONFIRM");
        }
        
        //PARA ALTERAR A NOTA DO PROJETO
    	if(isset($_POST['pro']) and empty($_POST['just'])) {
    		
        	$nota = str_replace(',', '.', $_POST['nota']);
        	$idPreProjeto = $_POST['idPreProj'];
        	$justificativa = $_POST['justificativa'];
//        	$idAgente = $this->idAgente;

        	AvaliarProjetosComissaoDAO::alterarNota($nota, $idPreProjeto);
        	
    		$aprovacao = AvaliarProjetosComissaoDAO::buscarAprovacao($idPreProjeto);
        	
        	if($aprovacao){
        		AvaliarProjetosComissaoDAO::aprovarProjeto($idPreProjeto, $nota, $justificativa, null, 1);
        	}
        	        	
        	parent::message("Altera&ccedil;&atilde;o realizada com sucesso!", "Avaliarprojetoscomissao/avaliarcomissao", "CONFIRM");
        }    
    }

    public function listarprojetosAction(){
    	
    	$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
    		
    	//DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
		if($this->_request->getParam("qtde")){
        	$this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();
            
        $pag = 1;
        $get = Zend_Registry::get('get');
        //xd($get);
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
        
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
            //xd($campo);
            $order = array($campo." ".$ordem);
            $ordenacao = "&campo=".$campo."&ordem=".$ordem;
                
        }else{
        	$campo = null;
            $order = array("12 DESC"); //ordenado por Nota 
            $ordenacao = null;
        }

        $this->view->filtros = '';
    	
    	$where = array();
    	$where['pro.Situacao = ? ']='G51';
    	//SE ENVIOU EDITAL
    	if($this->_request->getParam("edital")){
    		//$where['fod.nmFormDocumento = ? ']=$this->_request->getParam("edital");
    		$where['edi.idEdital = ? ']=$this->_request->getParam("edital");
    		$this->view->Edital = $this->_request->getParam("edital");
    		$this->view->filtros = '&edital='.$this->_request->getParam("edital");
    	}
    	
    	//SE ENVIOU REGIAO
    	$regiao = trim($this->_request->getParam("regiao"));
    	
    	if(!empty($regiao)){
    		$arrUfs = AvaliarProjetosComissaoDAO::buscaUF($regiao);
    		foreach($arrUfs as $uf){
    			$arrFiltroUfs[]=$uf->Uf;
    		}
    		$where['pro.UfProjeto in (?) ']= $arrFiltroUfs;
    		$this->view->Edital = $this->_request->getParam("edital");
    		$this->view->RegiaoSel = $regiao;
    		$this->view->buscaUF = $arrUfs; 
    		$this->view->filtros .= '&regiao='.$regiao;
    	}else{
    		
	    	$buscaUF = AvaliarProjetosComissaoDAO::buscaUF();
	    	$this->view->buscaUF = $buscaUF; 
    	}
    	
    	//SE ENVIOU UF
    	if($this->_request->getParam("uf")){
    		$where['pro.UfProjeto = ? ']= $this->_request->getParam("uf");
    		$this->view->UfSel = $this->_request->getParam("uf");
    		$this->view->filtros .= '&uf='.$this->_request->getParam("uf");
    	}
    	
    	$tblProjeto = new Projetos();
    	
    	$total = $tblProjeto->buscaProjetosComissao($where, $order, null, null, true);
    	
    	$fim = $inicio + $this->intTamPag; 
    	
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $buscaProjetosComissao = $tblProjeto->buscaProjetosComissao($where, $order, $tamanho, $inicio);//$tblProjeto->buscarProjetosConsolidados($where, $order, $tamanho, $inicio);

	    $this->view->buscaProjetos = $buscaProjetosComissao;
        
        $buscaRegiao = AvaliarProjetosComissaoDAO::buscaRegiao();
    	$this->view->buscaRegiao = $buscaRegiao; 
    	
    	$buscaEdital = AvaliarProjetosComissaoDAO::buscaEdital();
    	$this->view->buscaEdital = $buscaEdital; 
    	
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
    	
    }

}


?>