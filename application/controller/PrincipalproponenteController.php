<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Principalproponente
 *
 * @author tisomar
 */
class PrincipalproponenteController extends GenericControllerNew {

    private $idAgente = null;
    
    public function init() {
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $GrupoAtivo->codGrupo = 1111;

        Zend_Layout::startMvc(array('layout' => 'layout_proponente'));
        parent::perfil(4); // autenticao zend
        parent::init(); // chama o init() do pai GenericControllerNew
        $auth = Zend_Auth::getInstance(); // instancia da autenticação
        $this->idUsuario = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        $Usuario = new Usuario(); // objeto usuário
		//xd($auth->getIdentity());
        //$idagente = $Usuario->getIdUsuario('',$auth->getIdentity()->Cpf);
        //xd($idagente);
        //$this->idAgente = $idagente['idAgente'];
        $Agente = new Agentes();
        //$buscarAgente = $Agente;
        $this->idAgente = $auth->getIdentity()->IdUsuario;
        //xd($idagente);
    }

// fecha método init()

    public function indexAction() {
        $a = new Agentes();
        Zend_Layout::startMvc(array('layout' => 'layout_proponente'));
        $this->view->saudacao = Data::saudacao() . "! " . Data::mostraData() . ".";

        $verificarvinculo = $a->buscarAgenteVinculoResponsavel(array('vr.idAgenteProponente = ?'=>$this->idAgente, 'vprp.siVinculoProposta = ?'=>0))->count();
//        $verificarvinculo = $v->buscar(array('idAgenteProponente = ?'=>$this->idAgente, 'siVinculo = ?'=>'0'))->count();
        if($verificarvinculo > 0){
            $this->view->vinculos = true;
        }
        else{
            $this->view->vinculos = false;
        }
        
        
        // Comunicados
        $tbComunicados = new tbComunicados();
		
		$where['stEstado = ?'] = 1;
		$where['stOpcao = ?'] = 1; 
		$ordem = array();
		
		$rs = $tbComunicados->listarComunicados($where, $ordem);
		
		$this->view->comunicados = $rs;
        
        
    } // fecha método indexAction()
    
    
    /**
	 * Método listarComunicados()
	 * @access public
	 * @param void
	 * @return List
	 */
	public function listarComunicadosAction()
	{
		//header("Content-Type: text/html; charset=ISO-8859-1");
		$this->_helper->layout->disableLayout();
		$post = Zend_Registry::get('post');
		$this->intTamPag = 1;
		
		$tbComunicados = new tbComunicados();
		
		$where = array();
			
		$periodo1 = $this->_request->getParam("periodo1");
		$periodo2 = $this->_request->getParam("periodo2");
		$stEstado = $this->_request->getParam("stEstado");
		$stOpcao  = $this->_request->getParam("stOpcao");

		if(!empty($periodo1) && !empty($periodo1))
		{
			$where['dtInicioVigencia >= ?']  = $periodo1;
			$where['dtTerminoVigencia <= ?'] = $periodo2;
		}
		
		if($stEstado != '')
		{
			$where['stEstado = ?'] = $stEstado;
		}
		
		if($stOpcao != '')
		{
			$where['stOpcao = ?'] = $stOpcao;
		}
	
		
		$pag = 1;
        //$get = Zend_Registry::get('get');
        if (isset($post->pag)) $pag = $post->pag;
        if (isset($post->tamPag)) $this->intTamPag = $post->tamPag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
        $fim    = $inicio + $this->intTamPag;
        
        $total = $tbComunicados->listarComunicados($where, array(), null, null, true);

        //xd($total);
        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        if ($fim>$total) $fim = $total;

        $ordem = array("6 DESC");
        $rs = $tbComunicados->listarComunicados($where, $ordem, $tamanho, $inicio);
		
		$this->view->registros 		  = $rs;
		$this->view->pag 			  = $pag;
		$this->view->total 			  = $total;
		$this->view->inicio 		  = ($inicio+1);
		$this->view->fim 			  = $fim;
		$this->view->totalPag 		  = $totalPag;
		$this->view->parametrosBusca  = $_POST;
	}
    
    
    
}

?>
