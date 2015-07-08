<?php
/**
 * Controle Genérico (Utilizado por todos os controles)
 * Trata as mensagens do sistema
 * @author Equipe RUP - Politec
 * @since 12/08/2010
 * @version 2.0
 * @package application
 * @subpackage application.controllers
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class GenericControllerNew extends Zend_Controller_Action
{
	/**
	 * Variável com a mensagem
	 * @var $_msg
	 */
	protected $_msg;



	/**
	 * Variável com a página de redirecionamento
	 * @var $_url
	 */
	protected $_url;



	/**
	 * Variável com o tipo de mensagem
	 * Valores: ALERT, CONFIRM, ERROR ou vazio
	 * @var $_type
	 */
	protected $_type;



	/**
	 * Variável com a URL padrao do sistema
	 * @var $_urlPadrao
	 */
	protected $_urlPadrao;


	private  $idResponsavel  		= 0;
    private  $idAgente 				= 0;
	private  $idUsuario 			= 0;
	
	/**
	 * Reescreve o método init() para aceitar 
	 * as mensagens e redirecionamentos. 
	 * Teremos que chamá-lo dentro do 
	 * método init() da classe filha assim: parent::init();
	 * @access public
	 * @param void
	 * @return void
	 */
	public function init()
	{
            //SE CAIU A SECAO REDIRECIONA
            $auth = Zend_Auth::getInstance(); // pega a autenticação
//            xd($_SERVER['PHP_SELF']);
//            if(!$auth->hasIdentity()){
//                $url = Zend_Controller_Front::getInstance()->getBaseUrl();
//                JS::redirecionarURL($url);
//            }

            $this->_msg  = $this->_helper->getHelper('FlashMessenger');
            $this->_url  = $this->_helper->getHelper('Redirector');
            $this->_type = $this->_helper->getHelper('FlashMessengerType');


            $this->_urlPadrao = Zend_Controller_Front::getInstance()->getBaseUrl();
            if (isset($auth->getIdentity()->usu_codigo))
            {
                $Usuario      = new Usuario(); // objeto usuário
                $Agente = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
                $idAgente = $Agente['idAgente'];
                // manda os dados para a visão
                $this->view->idAgente    = $idAgente;
            }

                
		/******************************************************************************************************************************/

		  @$cpf = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_identificacao : $auth->getIdentity()->Cpf;        
	      
		  if ($cpf):
		        
		        // Busca na SGCAcesso
		        $sgcAcesso 	 = new Sgcacesso();
		        $buscaAcesso = $sgcAcesso->buscar(array('Cpf = ?' => $cpf));
		        
		        // Busca na Usuarios
		        $usuarioDAO   = new Usuario();
		        $buscaUsuario = $usuarioDAO->buscar(array('usu_identificacao = ?' => $cpf));
		
		        // Busca na Agentes
		        $agentesDAO  = new Agentes();
		        $buscaAgente = $agentesDAO->BuscaAgente($cpf);
		        
		        if( count($buscaAcesso) > 0){ $this->idResponsavel = $buscaAcesso[0]->IdUsuario; }
		        if( count($buscaAgente) > 0 ){ $this->idAgente 	   = $buscaAgente[0]->idAgente; }
		        if( count($buscaUsuario) > 0 ){ $this->idUsuario   = $buscaUsuario[0]->usu_codigo; }
		        
		        $this->view->idAgenteKeyLog 		= $this->idAgente;
		        $this->view->idResponsavelKeyLog 	= $this->idResponsavel;
		        $this->view->idUsuarioKeyLog 		= $this->idUsuario;
	        
	      endif;
		        
        /****************************************************************************************************************************/                

                
	} // fecha init()



	/**
	 * Método para chamar as mensagens e fazer o redirecionamento
	 * @access protected
	 * @param string $msg
	 * @param string $url
	 * @param string $type
	 * @return void
	 */
	protected function message($msg, $url, $type = null)
	{
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->flashMessenger->addMessage($msg);
		$this->_helper->flashMessengerType->addMessage($type);
		$this->_redirect($url);
		exit();
	} // fecha message()



	/**
	 * Reescreve o método postDispatch() que é responsável 
	 * por executar uma ação após a execução de um método
	 * @access public
	 * @param void
	 * @return void
	 */
	public function postDispatch()
	{
		if ($this->_msg->hasMessages())
		{
			$this->view->message = implode("<br />", $this->_msg->getMessages());
		}
		if ($this->_type->hasMessages())
		{
			$this->view->message_type = implode("<br />", $this->_type->getMessages());
		}
		parent::postDispatch(); // chama o método pai
	} // fecha postDispatch()



	/**
	 * Método responsável pela autenticação e perfis
	 * @access protected
	 * @param integer $tipo
	 * 		0 => somente autenticação zend
	 * 		1 => autenticação e permissões zend (AMBIENTE MINC)
	 * 		2 => autenticação scriptcase (AMBIENTE PROPONENTE)
	 * 		3 => autenticação scriptcase e autenticação/permissão zend (AMBIENTE PROPONENTE E MINC)
	 * @param array $permissoes (array com as permissões para acesso)
	 * @return void
	 */
	protected function perfil($tipo = 0, $permissoes = null)
	{
		$auth         = Zend_Auth::getInstance(); // pega a autenticação
		$Usuario      = new Usuario(); // objeto usuário
		$UsuarioAtivo = new Zend_Session_Namespace('UsuarioAtivo'); // cria a sessão com o usuário ativo
		$GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
	
		// somente autenticação zend
		if ($tipo == 0 || empty($tipo))
		{
			if ($auth->hasIdentity()) // caso o usuário esteja autenticado
			{
				// pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
				if (isset($auth->getIdentity()->usu_codigo) && !empty($auth->getIdentity()->usu_codigo))
				{
					$grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
                                $Agente = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
                                $idAgente = $Agente['idAgente'];
                                $Cpflogado = $Agente['usu_identificacao'];
				}
				else
				{
					return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
				}

				// manda os dados para a visão
                                $this->view->idAgente    = $idAgente;
				$this->view->usuario     = $auth->getIdentity(); // manda os dados do usuário para a visão
				$this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
				$this->view->grupoAtivo  = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
				$this->view->orgaoAtivo  = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
			} // fecha if
			else // caso o usuário não esteja autenticado
			{
				return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
			}
		} // fecha if
		// autenticação e permissões zend (AMBIENTE MINC)
		else if ($tipo === 1)
		{
			if ($auth->hasIdentity()) // caso o usuário esteja autenticado
			{
				if (!in_array($GrupoAtivo->codGrupo, $permissoes)) // verifica se o grupo ativo está no array de permissões
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
				}

				// pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
				$grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

				// manda os dados para a visão
                                $Agente = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
                                $idAgente = $Agente['idAgente'];
				$this->view->usuario     = $auth->getIdentity(); // manda os dados do usuário para a visão
				$this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
				$this->view->grupoAtivo  = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
				$this->view->orgaoAtivo  = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
			} // fecha if
			else // caso o usuário não esteja autenticado
			{
				return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
			}
		} // fecha else if


		// autenticação scriptcase (AMBIENTE PROPONENTE)
		else if ($tipo == 2)
		{
			// configurações do layout padrão para o scriptcase
			Zend_Layout::startMvc(array('layout' => 'layout_scriptcase'));

			// pega o id do usuário logado pelo scriptcase (sessão)
			//$codUsuario = isset($_SESSION['gusuario']['id']) ? $_SESSION['gusuario']['id'] : $UsuarioAtivo->codUsuario;
			$codUsuario = isset($_GET['idusuario']) ? (int) $_GET['idusuario'] : $UsuarioAtivo->codUsuario;
			//$codUsuario = 366;
			if (isset($codUsuario) && !empty($codUsuario))
			{
				$UsuarioAtivo->codUsuario = $codUsuario;
			}
			else // caso o usuário não esteja autenticado
			{
				$this->message("Você não tem permissão para acessar essa área do sistema!", "index", "ALERT");
			}

			// tenta fazer a autenticação do usuário logado no scriptcase para o zend
			$autenticar = UsuarioDAO::loginScriptcase($codUsuario);

			if ($autenticar && $auth->hasIdentity()) // caso o usuário seja passado pelo scriptcase e esteja autenticado
			{
				// manda os dados para a visão
				$this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
			} // fecha if
			else // caso o usuário não esteja autenticado
			{
				$this->message("Você não tem permissão para acessar essa área do sistema!", "index", "ALERT");
			}
		} // fecha else if


		// autenticação scriptcase e autenticação/permissão zend (AMBIENTE PROPONENTE E MINC)
		else if ($tipo == 3)
		{

			// ========== INÍCIO AUTENTICAÇÃO SCRIPTCASE ==========
			// pega o id do usuário logado pelo scriptcase
			//$codUsuario = isset($_SESSION['gusuario']['id']) ? $_SESSION['gusuario']['id'] : $UsuarioAtivo->codUsuario;
			$codUsuario = isset($_GET['idusuario']) ? (int) $_GET['idusuario'] : $UsuarioAtivo->codUsuario;
			//$codUsuario = 366;
			if (isset($codUsuario) && !empty($codUsuario))
			{
				// configurações do layout padrão para o scriptcase
				Zend_Layout::startMvc(array('layout' => 'layout_scriptcase'));

				$UsuarioAtivo->codUsuario = $codUsuario;

				// tenta fazer a autenticação do usuário logado no scriptcase para o zend
				$autenticar = UsuarioDAO::loginScriptcase($codUsuario);

				if ($autenticar && $auth->hasIdentity()) // caso o usuário seja passado pelo scriptcase e esteja autenticado
				{
					// manda os dados para a visão
					$this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
				} // fecha if
				else // caso o usuário não esteja autenticado
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "index", "ALERT");
				}
			} // fecha if
			// ========== FIM AUTENTICAÇÃO SCRIPTCASE ==========


			// ========== INÍCIO AUTENTICAÇÃO ZEND ==========
			else // caso o usuário não esteja autenticado pelo scriptcase
			{
				if (!in_array($GrupoAtivo->codGrupo, $permissoes)) // verifica se o grupo ativo está no array de permissões
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
				}

				// pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
				if (isset($auth->getIdentity()->usu_codigo) && !empty($auth->getIdentity()->usu_codigo))
				{
					$grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
				}
				else
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
				}

				// manda os dados para a visão
				$this->view->usuario     = $auth->getIdentity(); // manda os dados do usuário para a visão
				$this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
				$this->view->grupoAtivo  = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
				$this->view->orgaoAtivo  = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
			} // fecha else
		} // fecha else if

		// autenticação migracao e autenticação/permissão zend (AMBIENTE DE MIGRAÇ?O E MINC)
		else if ($tipo == 4)
		{

			// ========== INÍCIO AUTENTICAÇÃO MIGRAÇ?O ==========
			// pega o id do usuário logado pelo scriptcase
			//$codUsuario = isset($_SESSION['gusuario']['id']) ? $_SESSION['gusuario']['id'] : $UsuarioAtivo->codUsuario;
			$codUsuario = isset($auth->getIdentity()->IdUsuario) ? (int) $auth->getIdentity()->IdUsuario : $UsuarioAtivo->codUsuario;
			//$codUsuario = 366;
			if (isset($codUsuario) && !empty($codUsuario))
			{
				// configurações do layout padrão para o proponente
				Zend_Layout::startMvc(array('layout' => 'layout_proponente'));

				$UsuarioAtivo->codUsuario = $codUsuario;

				// tenta fazer a autenticação do usuário logado no scriptcase para o zend
				$autenticar = UsuarioDAO::loginScriptcase($codUsuario);

				if ($autenticar && $auth->hasIdentity()) // caso o usuário seja passado pelo scriptcase e esteja autenticado
				{
					// manda os dados para a visão
					$this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
				} // fecha if
				else // caso o usuário não esteja autenticado
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "index", "ALERT");
				}
			} // fecha if
			// ========== FIM AUTENTICAÇÃO MIGRAÇ?O ==========


			// ========== INÍCIO AUTENTICAÇÃO ZEND ==========
			else // caso o usuário não esteja autenticado pelo scriptcase
			{
				if (!in_array($GrupoAtivo->codGrupo, $permissoes)) // verifica se o grupo ativo está no array de permissões
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
				}

				// pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
				if (isset($auth->getIdentity()->usu_codigo) && !empty($auth->getIdentity()->usu_codigo))
				{
					$grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
				}
				else
				{
					$this->message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
				}

				// manda os dados para a visão
				$this->view->usuario     = $auth->getIdentity(); // manda os dados do usuário para a visão
				$this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
				$this->view->grupoAtivo  = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
				$this->view->orgaoAtivo  = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
			} // fecha else
		} // fecha else if
		// ========== FIM AUTENTICAÇÃO ZEND ==========

                if(!empty($grupos)){
                    $tblSGCacesso = new Sgcacesso();
                    $rsSGCacesso = $tblSGCacesso->buscar(array("Cpf = ? "=>$auth->getIdentity()->usu_identificacao));
                    if($rsSGCacesso->count() > 0){
                        $this->view->arrayGrupoProponente = array("gru_codigo"=>1111, "uog_orgao"=>2222, "gru_nome"=>"Proponente");
                    }
                }

	} // fecha método perfil()

        /**
         * Monta a tela de retorno ao usuario
         * @param string $corpo - arquivo tpl do corpo
         * @param array $dados - array com os dados a serem inseridos na tela, no seguinte formato "nome"=>"valor"
         * @param boolean $exibeHeader - true ou false para exibir header, menu e rodape
         * @return void
         */
        public function montaTela($view, $dados=array())
        {
            // percorrendo array de dados e inserindo no template
            foreach ($dados as $dado=>$valor)
            {
                    $this->view->assign($dado, $valor);
            }

            // retorna o tempalte master, com corpo e variaveis setadas
            $this->renderScript($view);
        }

        /**
         * Recebe codigo em HTML e gera um PDF
         * @return void
         */
        public function gerarPdfAction() {
            @ini_set("memory_limit", "5000M");
            @ini_set('max_execution_time', 3000);
            @set_time_limit(9500);
            @error_reporting(0);

            $this->_helper->layout->disableLayout();
            @$this->_helper->viewRenderer->setNoRender();

            @$output = '
                            <style>
                                    th{
                                    background:#ABDA5D;
                                    color:#3A7300;
                                    text-transform:uppercase;
                                    font-size:14px;
                                    font-weight: bold;
                                    font-family: sans-serif;
                                    height: 16px;
                                    line-height: 16px;
                            }
                            td{
                                    color:#000;
                                    font-size:14px;
                                    font-family: sans-serif;
                                    height: 14px;
                                    line-height: 14px;
                            }
                            .destacar{
                                    background:#DFEFC2;
                            }
                            .blue{
                                    color: blue;
                            }
                            .red{
                                    color: red;
                            }
                            .orange{
                                    color: orange;
                            }
                            .green{
                                    color: green;
                            }

                            .direita{
                                    text-align: right;
                            }

                            .centro{
                                    text-align: center;
                            }

                            </style>';



            @$output .= $_POST['html'];

            $patterns = array();
            $patterns[] = '/<table.*?>/is';
            $patterns[] = '/size="3px"/is';
            $patterns[] = '/size="4px"/is';
            $patterns[] = '/size="2px"/is';
            $patterns[] = '/<thead>/is';
            $patterns[] = '/<\/thead>/is';
            $patterns[] = '/<tbody>/is';
            $patterns[] = '/<\/tbody>/is';
            $patterns[] = '/<col.*?>/is';
            $patterns[] = '/<a.*?>/is';
            $patterns[] = '/<img.*?>/is';

            $replaces = array();
            $replaces[] = '<table cellpadding="0" cellspacing="1" border="1" width="90%" align="center">';
            $replaces[] = 'size="14px"';
            $replaces[] = 'size="14px"';
            $replaces[] = 'size="11px"';
            $replaces[] = '';
            $replaces[] = '';
            $replaces[] = '';
            $replaces[] = '';
            $replaces[] = '';
            $replaces[] = '';
            $replaces[] = '';

            //ANTIGO METODO QUE GERAVA PDF UTILIZANDO A BIBLIOTECA DOMPDF
//            $output = preg_replace($patterns,$replaces,$output);
//            $pdf = new PDF($output, 'pdf');
//            $pdf->gerarRelatorio('h');
            //$this->view->dados = ManterpropostaeditalDAO::listarEditalResumo(array());

            //METODO QUE GERA PDF UTILIZANDO A BIBLIOTECA MPDF
            @$output = preg_replace($patterns,$replaces,utf8_encode($output));
            @$pdf=new mPDF('pt','A4',12,'',8,8,5,14,9,9,'P');
            @$pdf->allow_charset_conversion = true;
            @$pdf->charset_in='UTF-8';
            @$pdf->WriteHTML($output);
            @$pdf->Output();

            //DEBUG
//            $mtime = microtime();
//            $mtime = explode(" ",$mtime);
//            $mtime = $mtime[1] + $mtime[0];
//            $starttime = $mtime;
//            $mtime = microtime();
//            $mtime = explode(" ",$mtime);
//            $mtime = $mtime[1] + $mtime[0];
//            $endtime = $mtime;
//            $totaltime = ($endtime - $starttime);
//            $pdf->WriteHTML("PDF Gerado em ".$totaltime." segundos");


        }

        public function gerarXlsAction(){
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->layout->disableLayout();

            $html = $_POST['html'];
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=file.xls;");
            echo $html;
        }
        
        
        public function html2PdfAction(){
            $orientacao = false;
            
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
        
            if($this->_getParam('orientacao') == 'L'){
                $orientacao = true;
            }
            
            $pdf = new PDFCreator($_POST['html'],$orientacao);
        
            $pdf->gerarPdf();
        }

        public static function montaBuscaData(Zend_Filter_Input $post, $tpBuscaData, $cmpData, $cmpBD, $cmpDataFinal = null, Array $arrayJoin = null ){
            $arrBusca = array();
            $aux1 = $post->__get($cmpData);
            $aux2 = $post->__get($tpBuscaData);
            if(!empty ($aux1) || $aux2 != ''){
                if($post->__get($tpBuscaData) == "igual"){
                    $arrBusca["{$cmpBD} >= ?"] = ConverteData($post->__get($cmpData), 13)." 00:00:00";
                    $arrBusca["{$cmpBD} <= ?"] = ConverteData($post->__get($cmpData), 13)." 23:59:59";

                }elseif($post->__get($tpBuscaData) == "maior"){
                    $arrBusca["{$cmpBD} >= ?"] = ConverteData($post->__get($cmpData), 13)." 00:00:00";

                }elseif($post->__get($tpBuscaData) == "menor"){
                    $arrBusca["{$cmpBD} <= ?"] = ConverteData($post->__get($cmpData), 13)." 00:00:00";

                }elseif($post->__get($tpBuscaData) == "entre"){
                    
                    $arrBusca["{$cmpBD} >= ?"] = ConverteData($post->__get($cmpData), 13)." 00:00:00";
                    $arrBusca["{$cmpBD} <= ?"] = ConverteData($post->__get($cmpDataFinal), 13)." 23:59:59";
                    
                }elseif($post->__get($tpBuscaData) == "OT"){
                    $arrBusca["{$cmpBD} = ?"] = date("Y-m-d",strtotime("-1 day"))." 00:00:00";

                }elseif($post->__get($tpBuscaData) == "U7"){
                    $arrBusca["{$cmpBD} > ?"] = date("Y-m-d",strtotime("-7 day"))." 00:00:00";
                    $arrBusca["{$cmpBD} < ?"] = date("Y-m-d")." 23:59:59";

                }elseif($post->__get($tpBuscaData) == "SP"){
                    /*$arrBusca["{$cmpBD} > ?"] = date("Y-m-").(date("d")-7)." 00:00:00";
                    $arrBusca["{$cmpBD} < ?"] = date("Y-m-d")." 23:59:59";*/
                    
                   $dia_semana = date('w');
                   $primeiro_dia = date('Y-m-d', strtotime("-".$dia_semana."day"));
                   $domingo = date('Y-m-d',  strtotime($primeiro_dia."-1 week"));
                   $sabado =  date('Y-m-d',  strtotime($domingo."6 day"));
                   
                   $arrBusca["{$cmpBD} >= ?"] = $domingo." 00:00:00";
                   $arrBusca["{$cmpBD} <= ?"] = $sabado." 23:59:59";
                   

                }elseif($post->__get($tpBuscaData) == "MM"){
                    $arrBusca["{$cmpBD} > ?"] = date("Y-m-01")." 00:00:00";
                    $arrBusca["{$cmpBD} < ?"] = date("Y-m-d")." 23:59:59";

                }elseif($post->__get($tpBuscaData) == "UM"){
                    $arrBusca["{$cmpBD} >= ?"] = date("Y-m",strtotime("-1 month"))."-01 00:00:00";
                    //$arrBusca["{$cmpBD} <= ?"] = date("d/m/Y", mktime(0, 0, 0, date("m",  strtotime("-1 month"))+1, 0, date("Y")));
                    $arrBusca["{$cmpBD} <= ?"] = date("Y-m-d", mktime(0, 0, 0, date("m",  strtotime("-1 month"))+1, 0, date("Y")));

                }elseif($post->__get($tpBuscaData) == ""){
                    
                }else{
                    $arrBusca["{$cmpBD} > ?"] = ConverteData($post->__get($cmpData), 13)." 00:00:00";

                    if($post->__get($cmpDataFinal) != ""){
                        $arrBusca["{$cmpBD} < ?"] = ConverteData($post->__get($cmpDataFinal), 13)." 23:59:59";
                    }
                }
            }

            if(!empty($arrayJoin)){
                $arrBusca = array_merge($arrayJoin, $arrBusca);
            }

            return $arrBusca;
        }
        
        
        public function prepararXlsPdfAction(){
            Zend_Layout::startMvc(array('layout' => 'layout_scriptcase'));
            ini_set('max_execution_time', 900);
            $this->_response->clearHeaders();  
            $dados = $this->_getAllParams();
            
            $this->view->dados = $dados;
            $this->view->tipo = $dados['tipo'];
            
            if($dados['view']){
                $this->montaTela($dados['view'],$dados);
            }
            
        }


	/**
	 * Método para verificar se o usuário logado tem permissão para acessar o projeto
	 * OBS: SERVE APENAS PARA RESPONSÁVEL E AGENTE (PROPONENTE E PROCURADOR)
	 * @access public
	 * @param bool $obrigatoriedadeIdProjeto
	 * @param bool $obrigatoriedadeIdPronac
	 * @return void
	 */
	public function verificarPermissaoAcesso($proposta = false, $projeto = false, $administrativo = false)
	{
            $msgERRO = '';
            $auth = Zend_Auth::getInstance(); // pega a autenticação

            if (!isset($auth->getIdentity()->usu_codigo)) { // autenticacao novo salic

                //Verifica Permissão de Projeto
                if($projeto){
                    $msgERRO = 'Você não tem permissão para acessar esse Projeto!';
                    $idUsuarioLogado = $auth->getIdentity()->IdUsuario;
                    $idPronac  = $this->_request->getParam('idpronac')  ? $this->_request->getParam('idpronac')  : $this->_request->getParam('idPronac');
                    if (strlen($idPronac) > 7){
                        $idPronac = Seguranca::dencrypt($idPronac);
                    }
                    $fnVerificarPermissao = new fnVerificarPermissao();
                    $consulta = $fnVerificarPermissao->verificarPermissaoProjeto($idPronac, $idUsuarioLogado);
                    $permissao = $consulta->Permissao;
                }

                //Verifica Permissão de Proposta
                if($proposta){
                    $msgERRO = 'Você não tem permissão para acessar essa Proposta!';
                    $idUsuarioLogado = $auth->getIdentity()->IdUsuario;
                    $idPreProjeto = $this->_request->getParam('idPreProjeto');

                    $fnVerificarPermissao = new fnVerificarPermissao();
                    $consulta = $fnVerificarPermissao->verificarPermissaoProposta($idPreProjeto, $idUsuarioLogado);
                    $permissao = $consulta->Permissao;
                }

                if($administrativo){
//                    $idUsuarioLogado = $auth->getIdentity()->IdUsuario;
//                    $fnVerificarPermissao = new fnVerificarPermissao();
//                    $consulta = $fnVerificarPermissao->verificarPermissaoAdministrativo($idUsuarioLogado);
//                    xd($consulta);
//                    $permissao = $consulta->Permissao;
                }

                //Se o usuario nao tiver permissao pra acessar o Projeto / Proposta / Administrativo, exibe a msg de alerta.
                if(!$permissao){
                    $this->message($msgERRO, 'principalproponente', 'ALERT');
                }

            }

	} // fecha método verificarPermissaoAcesso()
    
    public static function validarSenhaInicial(){
        return 'ae56f49edf70ec03b98f53ea6d2bc622';
    }
    
    /**
	 * Método para montar as Planilhas Orçamentárias
	 * OBS: A planilha deve vir no padrão da spPlanilhaOrcamentaria
	 * @access public
	 * @param array
	 */
	public function montarPlanilhaOrcamentaria($planilhaOrcamentaria, $tipoPlanilha = null)
	{
        $planilha = array();
        $count = 0;
        $seq = 1;
        if($tipoPlanilha == 0){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaProposta'] = $resuplanilha->idPlanilhaProposta;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSolicitado'] = $resuplanilha->vlSolicitado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustProponente'] = $resuplanilha->JustProponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $count++;
                $seq++;
            }
        } else if($tipoPlanilha == 1){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaProposta'] = $resuplanilha->idPlanilhaProposta;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSolicitado'] = $resuplanilha->vlSolicitado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustProponente'] = $resuplanilha->JustProponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $count++;
                $seq++;
            }
        } else if($tipoPlanilha == 2){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaProjeto'] = $resuplanilha->idPlanilhaProjeto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSolicitado'] = $resuplanilha->vlSolicitado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustProponente'] = $resuplanilha->JustProponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSugerido'] = $resuplanilha->vlSugerido;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustParecerista'] = $resuplanilha->JustParecerista;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $count++;
                $seq++;
            }
        } else if($tipoPlanilha == 3){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaAprovacao'] = $resuplanilha->idPlanilhaAprovacao;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSolicitado'] = $resuplanilha->vlSolicitado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSugerido'] = $resuplanilha->vlSugerido;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlAprovado'] = $resuplanilha->vlAprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlComprovado'] = $resuplanilha->vlComprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustProponente'] = $resuplanilha->JustProponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustParecerista'] = $resuplanilha->JustParecerista;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustComponente'] = $resuplanilha->JustComponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['tpPlanilha'] = $resuplanilha->tpPlanilha;
                $count++;
                $seq++;
            }
        } else if($tipoPlanilha == 4){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaAprovacao'] = $resuplanilha->idPlanilhaAprovacao;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaProjeto'] = $resuplanilha->idPlanilhaProjeto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSolicitado'] = $resuplanilha->vlSolicitado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlSugerido'] = $resuplanilha->vlSugerido;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlAprovado'] = $resuplanilha->vlAprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustProponente'] = $resuplanilha->JustProponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustParecerista'] = $resuplanilha->JustParecerista;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['JustComponente'] = $resuplanilha->JustComponente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $count++;
                $seq++;
            }
        } else if($tipoPlanilha == 5){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaAprovacao'] = $resuplanilha->idPlanilhaAprovacao;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaAprovacaoPai'] = $resuplanilha->idPlanilhaAprovacaoPai;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idEtapa'] = $resuplanilha->idEtapa;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['FonteRecurso'] = $resuplanilha->FonteRecurso;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Unidade'] = $resuplanilha->Unidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlAprovado'] = $resuplanilha->vlAprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlComprovado'] = $resuplanilha->vlComprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['dsJustificativa'] = $resuplanilha->dsJustificativa;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idAgente'] = $resuplanilha->idAgente;
                $count++;
                $seq++;
            }
        } else if($tipoPlanilha == 6){
            foreach ($planilhaOrcamentaria as $resuplanilha) {
                $produto = $resuplanilha->Produto == null ? 'Adminitra&ccedil;&atilde;o do Projeto' : $resuplanilha->Produto;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Seq'] = $seq;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaAprovacao'] = $resuplanilha->idPlanilhaAprovacao;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idPlanilhaAprovacaoPai'] = $resuplanilha->idPlanilhaAprovacaoPai;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idEtapa'] = $resuplanilha->idEtapa;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Item'] = $resuplanilha->Item;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idFonte'] = $resuplanilha->idFonte;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['FonteRecurso'] = $resuplanilha->FonteRecurso;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Unidade'] = $resuplanilha->Unidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Quantidade'] = $resuplanilha->Quantidade;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['Ocorrencia'] = $resuplanilha->Ocorrencia;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlUnitario'] = $resuplanilha->vlUnitario;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlAprovado'] = $resuplanilha->vlAprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['vlComprovado'] = $resuplanilha->vlComprovado;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['QtdeDias'] = $resuplanilha->QtdeDias;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['dsJustificativa'] = $resuplanilha->dsJustificativa;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['idAgente'] = $resuplanilha->idAgente;
                $planilha[$resuplanilha->FonteRecurso][$produto][$resuplanilha->idEtapa . ' - ' . $resuplanilha->Etapa][$resuplanilha->UF . ' - ' . $resuplanilha->Municipio][$count]['tpAcao'] = $resuplanilha->tpAcao;
                $count++;
                $seq++;
            }
        }
        //xd($planilha);
        return $planilha;
	} // fecha método verificarPermissaoAcesso()

} // fecha class
