<?php
/**
 * Controller RealizarPrestacaoDeContas
 * @author Equipe RUP - Politec
 * @since 20/09/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 */

class RealizarPrestacaoDeContasController extends GenericControllerNew {
	private $getIdUsuario = 0;
	private $getIdAgenteLogado = 0;
	private $codGrupo 	   = null;
	private $codOrgao 	   = null;
	public  $intTamPag     = 15;
	private $modalidade    = array('Selecione','Convite','Tomada de Preços','Concorr&ecirc;ncia','Concurso','Preg&atilde;o');
	private $tipoDocumento = array('Selecione','Boleto Banc&aacute;rio','Cupom Fiscal','Nota Fiscal/Fatura','Recibo de Pagamento','Aut&ocirc;nomo', 'Guia De Recolhimento');
	private $tipoSituacao  = array('1'=>'Executado integralmente','2'=>'Executado parcialmente','3'=>'N&atilde;o Executado','4'=>'Sem informa&ccedil;&atilde;o');
	private $cdGruposDestinoAtual  = null;
	private $arrSituacoesDePrestacaoContas  = array("E17","E18","E19","E20","E22","E23","E24","E25",
                                                         "E27","E30","E46","L03","L04","L05","L06","L07",
                                                         "E68","G18","G20","G21","G22","G24","G43","G47",
                                                         "L07","G19","G25","G43","G47");//todas as situacoes de prestacao de contas

	private $arrSituacoesDePrestacaoContasMenosGrid  = array("E19","E20","E23","E25","E27","E30","E46","L03",
                                                                 "L04","L07","E68","G18","G20","G21","G22","G47",
                                                                 "L07","G19","G25","G43","G47");//todas as situacoes de prestacao de contas excluindo as situacoes ja prevista nas 4 grids principais

        private $arrSituacoesAguardandoAnalise       = array('E24','E68','E67','G43','G24'); //todas as situacoes do primeiro grid
        private $arrSituacoesDevolvidosAposAnalise   = array('E27'); //todas as situacoes do segundo grid
        private $arrSituacoesDiligenciados           = array('E17', 'E20', 'E30'); //todas as situacoes do terceiro grid
        private $arrSituacoesTCE                     = array('E22','L05','L06'); //todas as situacoes do quarto grid
        private $arrSituacoesGrids                   = array();

        private $situcaoEncaminhamentoAtual = null;
	
	/**
	 * Reescreve o método init()
	 * @access public
	 * @param void
	 * @return void
	 */
	
	public function init()
        {
            $arrSituacoesGrids = implode(',',$this->arrSituacoesAguardandoAnalise).','.implode(',',$this->arrSituacoesDevolvidosAposAnalise).','.implode(',',$this->arrSituacoesDiligenciados).','.implode(',',$this->arrSituacoesTCE);
            $arrSituacoesGrids = explode(',',$arrSituacoesGrids);
            $this->arrSituacoesGrids = $arrSituacoesGrids;

            $PermissoesGrupo [] = 124;
            $PermissoesGrupo [] = 125;
            $PermissoesGrupo [] = 126;
            $PermissoesGrupo [] = 125;
            $PermissoesGrupo [] = 94;
            $PermissoesGrupo [] = 93;
            $PermissoesGrupo [] = 82;
            $PermissoesGrupo [] = 132;
            $PermissoesGrupo [] = 100;

            parent::perfil ( 1, $PermissoesGrupo );

            // cria a sessão com o grupo ativo
            $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');

            // pega a autenticação
            $auth 		  = Zend_Auth::getInstance ();
            $GrupoUsuario = $GrupoAtivo->codGrupo;

            // instancia da autenticação
            $auth = Zend_Auth::getInstance();
            $this->getIdUsuario = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
            $tblAgente = new Agentes();
            $rsAgente = $tblAgente->buscar(array('CNPJCPF = ?'=>$auth->getIdentity()->usu_identificacao))->current();
            if(!empty($rsAgente)){
                $this->getIdAgenteLogado = $rsAgente->idAgente;
            }
            parent::init (); // chama o init() do pai GenericControllerNew

            //situacao do projeto (Executado integralmente','Executado parcialmente','N&atilde;o Executado','Sem informa&ccedil;&atilde;o)
            $this->view->tipoSituacao = $this->tipoSituacao;

            //guarda o grupo do usuario logado
            $this->view->codGrupo = $_SESSION['GrupoAtivo']['codGrupo'];
            $this->codGrupo 	  = $_SESSION['GrupoAtivo']['codGrupo'];
            $this->codOrgao       = $_SESSION['GrupoAtivo']['codOrgao'];
            $this->view->codOrgao = $_SESSION['GrupoAtivo']['codOrgao'];

            $this->view->comboestados = Estado::buscar();

            $idpronac = $this->_request->getParam("idPronac");
            if(!empty($idpronac)){
                $tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas();
                $rsEPC = $tblEncaminhamentoPrestacaoContas->buscar(array("idPronac = ?"=>$idpronac, 'stAtivo=?'=>1))->current();
                if(!empty($rsEPC)){
                    $this->situcaoEncaminhamentoAtual = $rsEPC->idSituacaoEncPrestContas;
                    $this->cdGruposDestinoAtual       = $rsEPC->cdGruposDestino;

                    $this->view->situcaoEncaminhamentoAtual = $this->situcaoEncaminhamentoAtual;
                    $this->view->cdGruposDestinoAtual       = $this->cdGruposDestinoAtual;
                }
            }
	} // fecha método init()

        /**
         * Não deletar essa action pois é usada para renderizar a view.
         * Quando loga no sistema com o perfil de Técnico de prestação de contas
         * Acessa o menu 'Prestação de Contas' -> 'Imprimir Laudo Final' e clicar
         * em 'Cancelar' vem para essa action
         */
        public function indexAction()
        {

        }

	public function montaArrBuscaCoincidentes($post)
        {
            $arrBusca=array();
            
            //NOME PROJETO
            if(!empty($post->NomeProjeto)){
                $projeto = utf8_decode($post->NomeProjeto);
                if($post->tipoPesqNomeProjeto == 'QC'){
                        if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                }else if($post->tipoPesqNomeProjeto == 'EIG'){
                        if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto = ?"] = "{$projeto}"; }
                }else if($post->tipoPesqNomeProjeto == 'IIG'){
                        if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "{$projeto}%"; }
                }
                //if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
            }

            //UF
            if(!empty($post->uf)){
                $arrBusca["p.UfProjeto = ?"] = $post->uf;
                if(isset($post->cidade) && !empty($post->cidade)){
                    $arrBusca["ab.idMunicipioIBGE = ?"] = $post->cidade;
                }
            }

            //PERIODO EXECUCAO
            if (isset($post->tpPeriodoExecucao) && !empty($post->tpPeriodoExecucao)){
                if ($post->tpPeriodoExecucao == 1){ // exatamente igual
                    
                   if (isset($post->dtExecucao) && !empty($post->dtExecucao)) { 
                       $arrBusca["p.DtInicioExecucao >= '". Data::dataAmericana($post->dtExecucao) . " 00:00:00.000' AND p.DtInicioExecucao <= '". Data::dataAmericana($post->dtExecucao) . " 23:59:59.999'" ] = '?';
                    }
                   if ( isset($post->dtExecucao_Final) && !empty($post->dtExecucao_Final)) { 
                       $arrBusca["p.DtFimExecucao >= '". Data::dataAmericana($post->dtExecucao_Final) . " 00:00:00.000' AND p.DtFimExecucao <= '". Data::dataAmericana($post->dtExecucao_Final) . " 23:59:59.999'" ] = '?';
                   }
                } else if ($post->tpPeriodoExecucao == 2) { // que inicia
                    if ( isset($post->dtExecucao) && !empty($post->dtExecucao)) {
                        //$arrBusca['p.DtInicioExecucao >= ?'] = Data::dataAmericana($post->dtExecucao) . " 00:00:00.000";
                        $arrBusca["p.DtInicioExecucao >= '". Data::dataAmericana($post->dtExecucao) . " 00:00:00.000' AND p.DtInicioExecucao <= '". Data::dataAmericana($post->dtExecucao) . " 23:59:59.999'" ] = '?';                    
                    }
                } else if ($post->tpPeriodoExecucao == 3) { // que finaliza
                    if ( isset($post->dtExecucao_Final) && !empty($post->dtExecucao_Final)) {
                        //$arrBusca['p.DtFimExecucao = ?'] = Data::dataAmericana($post->dtExecucao_Final) . " 00:00:00.000";
                        $arrBusca["p.DtFimExecucao >= '". Data::dataAmericana($post->dtExecucao_Final) . " 00:00:00.000' AND p.DtFimExecucao <= '". Data::dataAmericana($post->dtExecucao_Final) . " 23:59:59.999'" ] = '?';
                    }
                } else if ($post->tpPeriodoExecucao == 4) { // entre
                    
                    if ( isset($post->dtExecucao) && !empty($post->dtExecucao)) {$arrBusca['p.DtInicioExecucao >= ?'] = Data::dataAmericana($post->dtExecucao) . " 00:00:00.000";}
                    if ( isset($post->dtExecucao_Final) && !empty($post->dtExecucao_Final)) {$arrBusca['p.DtFimExecucao <= ?'] = Data::dataAmericana($post->dtExecucao_Final) . " 23:59:59.999";}
                }
            }
            //xd($arrBusca);
            //$arrBusca = GenericControllerNew::montaBuscaData($post, "tpPeriodoExecucao", "dtExecucao", "p.DtInicioExecucao", "dtExecucao_Final", $arrBusca);
            
            if(!empty($post->area)){
                if($post->tipoPesqArea == 'EIG'){
                        if(!empty($post->area)){ $arrBusca["a.Codigo = ?"] = $post->area; }
                }else if($post->tipoPesqArea == 'DI'){
                        if(!empty($post->area)){ $arrBusca["a.Codigo <> ?"] = $post->area; }
                }
            }

            //MECANISMO
            if (isset($post->mecanismo) && !empty($post->mecanismo)){
                $arrBusca['p.Mecanismo = ?'] =$post->mecanismo;
            }

            //ORGAO USUARIO LOGADO
            $arrBusca['p.Orgao = ?'] = $this->codOrgao;

            return $arrBusca;
        }

	public function incluiRegrasGridsPrincipais($arrBusca,$post)
        {            
            //CONDICOES DE PROJETOS DEVOLVIDO APOS ANALISE
            if(isset($post->situacao) && !empty($post->situacao) && in_array($post->situacao,$this->arrSituacoesDevolvidosAposAnalise)){
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise OU Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;
            }

            //CONDICOES DE PROJETOS DILIGENCIADOS
            if(isset($post->situacao) && !empty($post->situacao) && in_array($post->situacao,$this->arrSituacoesDiligenciados)){
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise OU Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;
                //$arrBusca['d.DtSolicitacao = (SELECT top 1 d2.DtSolicitacao FROM SAC..tbDiligencia d2 WHERE d2.idPronac = d.idPronac ORDER BY d2.DtSolicitacao DESC)'] = '(?)'; //seleciona a ultima diligencia realizada
                //$arrBusca['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas
            }

            //CONDICOES DE PROJETOS em TCE
            if(isset($post->situacao) && !empty($post->situacao) && in_array($post->situacao,$this->arrSituacoesTCE)){
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise, e Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;
            }
            return $arrBusca;
        }

        public function projetosAguardandoAnaliseAction()
        {
            $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
            $idPronac = $this->_request->getParam("idPronac");

            $post   = Zend_Registry::get('post');
            $this->intTamPag = 10;

            $bln_encaminhamento = true;
            $bln_dadosDiligencia = false;
            
            $pag = 1;
            if (isset($post->pagAG)) $pag = $post->pagAG;
            if (isset($post->tamPagAG)) $this->intTamPag = $post->tamPagAG;

            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            $fim = $inicio + $this->intTamPag;

            // ======= CONDICOES DE COORD. DE PRESTACAO DE CONTAS ==============
            if($this->codGrupo == '125' || $this->codGrupo == '126')
            {
                //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
                $arrBusca = $this->montaArrBuscaCoincidentes($post);
                $arrBusca['p.Situacao in (?)']= $this->arrSituacoesAguardandoAnalise;
                $bln_encaminhamento = false;

                //DILIGENCIA
                if(!empty($post->diligencia)){
                    if($post->diligencia == "abertas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                    }elseif($post->diligencia == "respondidas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                    }
                    $bln_dadosDiligencia = true;
                }
            }

            // ======= CONDICOES DE TECNICO DE PRESTACAO DE CONTAS =============
            if($this->codGrupo == '124')
            {
                $arrBusca = array();
                $arrBusca['p.Situacao = ?']                    = 'E27';
                $arrBusca['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                $arrBusca['e.cdGruposDestino = ?']             = 124; //grupo do tecnico de prestacao de contas
                $arrBusca['e.idAgenteDestino = ?']             = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                $arrBusca['e.stAtivo = ?']                     = 1;
                $bln_encaminhamento = true;
            }

            // ======= CONDICOES DE CHEFE DE DIVISAO ===========================
            if($this->codGrupo == '132')
            {
                $arrBusca = array();
                $arrBusca['p.Situacao <> ?']                    = 'E17'; //exclui projetos diligenciados
                $arrBusca['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                $arrBusca['e.cdGruposDestino = ?']             = 132; //grupo do chefe de divisao
                //$arrBusca['e.idAgenteDestino = ?']             = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                $arrBusca['e.stAtivo = ?']                     = 1;
                $bln_encaminhamento = true;
            }

            $total = 0;
            $tblProjetos = new Projetos ();
            $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, $bln_encaminhamento, $bln_dadosDiligencia);

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            if ($fim>$total) $fim = $total;

            $ordem = array();
            if(!empty($post->ordenacaoAG)){ $ordem[] = "{$post->ordenacaoAG} {$post->tipoOrdenacaoAG}"; }else{$ordem = array('1 ASC');}

            $rs = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, $ordem, $tamanho, $inicio, false, $bln_encaminhamento, $bln_dadosDiligencia);
            
            $this->view->registrosAG 	  = $rs;
            $this->view->pagAG 		  = $pag;
            $this->view->totalAG 	  = $total;
            $this->view->inicioAG 	  = ++$inicio;
            $this->view->fimAG 		  = $fim;
            $this->view->totalPagAG 	  = $totalPag;
            $this->view->parametrosBuscaAG= $_POST;
        }

        public function projetosDevolvidosAposAnaliseAction()
        {

            $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
            $idPronac = $this->_request->getParam("idPronac");
            
            $post   = Zend_Registry::get('post');
            $this->intTamPag = 10;

            $bln_encaminhamento = true;

            $pag = 1;
            //$get = Zend_Registry::get('get');
            if (isset($post->pagDA)) $pag = $post->pagDA;
            if (isset($post->tamPagDA)) $this->intTamPag = $post->tamPagDA;

            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            $fim = $inicio + $this->intTamPag;

            //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
            $arrBusca = $this->montaArrBuscaCoincidentes($post);

            //SITUACAO
            /*if ( isset($post->situacao) && !empty($post->situacao)){
                //$arrBusca['p.Situacao = ?'] = $post->situacao;
                $arrBusca["p.Situacao = '".$post->situacao."' AND p.Situacao not in ('E24','E68','E67','G43','G24','E17','E22','L05','L06') "] = '(?)';
                if($post->situacao != 'E18'){
                    $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
                }
            }else{
                $arrBusca['p.Situacao = ?']='E18';
            }*/
            $arrBusca['p.Situacao in (?)']= $this->arrSituacoesDevolvidosAposAnalise;
            if(isset($post->situacao) && !empty($post->situacao) && $post->situacao != 'E18'){
                $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
            }

            //DILIGENCIA
            if(!empty($post->diligencia)){
                if($post->diligencia == "abertas"){
                    $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                }elseif($post->diligencia == "respondidas"){
                    $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                }
            }
            
            //CONDICOES DE DEVOLVIDO APOS ANALISE
            $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise OU Em analise
            $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
            $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
            $arrBusca['e.stAtivo = ?']= 1;

            $total = 0;
            $tblProjetos = new Projetos ();
            $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, $bln_encaminhamento, true);

            //xd($total);
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            if ($fim>$total) $fim = $total;

            $ordem = array();
            if(!empty($post->ordenacaoDA)){ $ordem[] = "{$post->ordenacaoDA} {$post->tipoOrdenacaoDA}"; }else{$ordem = array('1 ASC');}

            $rs = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, $ordem, $tamanho, $inicio, null, $bln_encaminhamento, true);

            $this->view->registrosDA 	  = $rs;
            $this->view->pagDA 		  = $pag;
            $this->view->totalDA          = $total;
            $this->view->inicioDA 	  = ($inicio+1);
            $this->view->fimDA		  = $fim;
            $this->view->totalPagDA 	  = $totalPag;
            $this->view->parametrosBuscaDA = $_POST;

        }

        public function projetosDiligenciadosAction()
        {

            $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
            $idPronac = $this->_request->getParam("idPronac");
            //$codPerfil = $this->_request->getParam("idPronac");

            $post   = Zend_Registry::get('post');
            $this->intTamPag = 10;

            $pag = 1;
            //$get = Zend_Registry::get('get');
            if (isset($post->pagDI)) $pag = $post->pagDI;
            if (isset($post->tamPagDI)) $this->intTamPag = $post->tamPagDI;

            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            $fim = $inicio + $this->intTamPag;

            // ======= CONDICOES DE COORD. DE PRESTACAO DE CONTAS ==============
            if($this->codGrupo == '125' || $this->codGrupo == '126')
            {
                //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
                $arrBusca = $this->montaArrBuscaCoincidentes($post);
                $arrBusca['p.Situacao in (?)']= $this->arrSituacoesDiligenciados;
                if(isset($post->situacao) && !empty($post->situacao) && $post->situacao != 'E17'){
                    $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
                }

                //DILIGENCIA
                if(!empty($post->diligencia)){
                    if($post->diligencia == "abertas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                    }elseif($post->diligencia == "respondidas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                    }
                }
                
                //CONDICOES DE PARA ESTAR COM O COORD. DE PRESTACAO DE CONTAS
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise OU Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;
                $arrBusca['d.DtSolicitacao = (SELECT top 1 d2.DtSolicitacao FROM SAC..tbDiligencia d2 WHERE d2.idPronac = d.idPronac ORDER BY d2.DtSolicitacao DESC)'] = '(?)'; //seleciona a ultima diligencia realizada
                $arrBusca['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas
            }

            // ======= CONDICOES DE TECNICO DE PRESTACAO DE CONTAS =============
            if($this->codGrupo == '124')
            {
                $arrBusca = array();
				$arrBusca['p.Situacao in (?)']                 = $this->arrSituacoesDiligenciados;
				$arrBusca['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
				$arrBusca['e.cdGruposDestino = ?']             = 124; //grupo do tecnico de prestacao de contas
                $arrBusca['e.cdGruposOrigem IN (?)']           = array('125','126'); //grupo de coordenador de prestacao de contas
				$arrBusca['e.stAtivo = ?']                     = 1;
				$arrBusca['d.idTipoDiligencia = ?']            = 174; //Diligencia na Prestacao de contas
            }

            // ======= CONDICOES DE CHEFE DE DIVISAO ===========================
            if($this->codGrupo == '132')
            {
                $arrBusca = array();
				$arrBusca['p.Situacao = ?']                    = 'E17';
				$arrBusca['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
				$arrBusca['e.cdGruposDestino = ?']             = 132; //grupo do chefe de divisao
				$arrBusca['e.stAtivo = ?']                     = 1;
                $arrBusca['d.idTipoDiligencia = ?']            = 174; //Diligencia na Prestacao de contas
            }

            $total = 0;
            $tblProjetos = new Projetos ();
            $total = $tblProjetos->buscaProjetoDiligenciadosPrestacaoContas($arrBusca, array(), null, null, true);
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            if ($fim>$total) $fim = $total;

            $ordem = array();
            if(!empty($post->ordenacaoDI)){ $ordem[] = "{$post->ordenacaoDI} {$post->tipoOrdenacaoDI}"; }else{$ordem = array('1 ASC');}

            $rs = $tblProjetos->buscaProjetoDiligenciadosPrestacaoContas($arrBusca, $ordem, $tamanho, $inicio);

            $this->view->registrosDI 	  = $rs;
            $this->view->pagDI 		  = $pag;
            $this->view->totalDI          = $total;
            $this->view->inicioDI 	  = ($inicio+1);
            $this->view->fimDI		  = $fim;
            $this->view->totalPagDI 	  = $totalPag;
            $this->view->parametrosBuscaDI = $_POST;
        }

        public function projetosTceAction()
        {

            $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
            $idPronac = $this->_request->getParam("idPronac");

            $post   = Zend_Registry::get('post');
            $this->intTamPag = 10;
            
            $bln_encaminhamento = true;
            $bln_dadosDiligencia = false;
            
            $pag = 1;
            //$get = Zend_Registry::get('get');
            if (isset($post->pagTCE)) $pag = $post->pagTCE;
            if (isset($post->tamPagTCE)) $this->intTamPag = $post->tamPagTCE;

            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            $fim = $inicio + $this->intTamPag;

            //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
            $arrBusca = $this->montaArrBuscaCoincidentes($post);

            //SITUACAO
            /*if ( isset($post->situacao) && !empty($post->situacao)){
                $arrBusca["p.Situacao = '".$post->situacao."' AND p.Situacao not in ('E24','E68','E67','G43','G24') "] = '(?)';
                $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
            }else{
		//$arrBusca['p.Situacao = ?']='E22';
                $arrBusca['p.Situacao in (?)']= array('E22','L05','L06');
            }*/
            $arrBusca['p.Situacao in (?)'] = $this->arrSituacoesTCE;
            if ( isset($post->situacao) && !empty($post->situacao)){
                $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
            }
            
            //DILIGENCIA
            if(!empty($post->diligencia)){
                if($post->diligencia == "abertas"){
                    $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                }elseif($post->diligencia == "respondidas"){
                    $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                }
            }

            //CONDICOES DE PARA ESTAR COM O COORD. DE PRESTACAO DE CONTAS
            $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise, e Em analise
            $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
            $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
            $arrBusca['e.stAtivo = ?']= 1;

            $total = 0;
            $tblProjetos = new Projetos ();
            $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, $bln_encaminhamento, $bln_dadosDiligencia);

            //xd($total);
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            if ($fim>$total) $fim = $total;

            $ordem = array();
            if(!empty($post->ordenacaoTCE)){ $ordem[] = "{$post->ordenacaoTCE} {$post->tipoOrdenacaoTCE}"; }else{$ordem = array('1 ASC');}

            $rs = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, $ordem, $tamanho, $inicio, null, $bln_encaminhamento, $bln_dadosDiligencia);

            $this->view->registrosTCE 	    = $rs;
            $this->view->pagTCE             = $pag;
            $this->view->totalTCE           = $total;
            $this->view->inicioTCE 	    = ($inicio+1);
            $this->view->fimTCE		    = $fim;
            $this->view->totalPagTCE 	    = $totalPag;
            $this->view->parametrosBuscaTCE = $_POST;

        }

        public function projetosOutrasSituacoesAction()
        {

            $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
            $idPronac = $this->_request->getParam("idPronac");

            $post   = Zend_Registry::get('post');
            $this->intTamPag = 10;
            $bln_encaminhamento = false;
            $bln_dadosDiligencia = false;
            
            $pag = 1;
            //$get = Zend_Registry::get('get');
            if (isset($post->pagOS)) $pag = $post->pagOS;
            if (isset($post->tamPagOS)) $this->intTamPag = $post->tamPagOS;

            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            $fim = $inicio + $this->intTamPag;

            //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
            $arrBusca = $this->montaArrBuscaCoincidentes($post);

            //PRONAC
            if(!empty($post->pronac)){
                $arrBusca["p.AnoProjeto + p.Sequencial = ?"] = trim($post->pronac);
                $arrBusca["p.Situacao IN (?) "] = $this->arrSituacoesDePrestacaoContas;
            }
            
            //SITUACAO
            if ( isset($post->situacao) && !empty($post->situacao)){
                //$situacoesGrids = implode('\',\'',$this->arrSituacoesGrids);
                //$situacoesGrids = "'".$situacoesGrids."'";
                //$arrBusca["p.Situacao = '".$post->situacao."' AND p.Situacao NOT IN ({$situacoesGrids}) "] = '(?)';
                $arrBusca = $this->incluiRegrasGridsPrincipais($arrBusca,$post);
                $arrBusca["p.Situacao = ? "] = $post->situacao;
                if(in_array($post->situacao,$this->arrSituacoesDevolvidosAposAnalise) || in_array($post->situacao,$this->arrSituacoesDiligenciados)  || in_array($post->situacao,$this->arrSituacoesTCE) ){
                    $bln_encaminhamento = true;
                }
                if(in_array($post->situacao,$this->arrSituacoesDiligenciados)){
                    $bln_dadosDiligencia = true;
                }
            }else{
                //deve fazer este filtro apenas de nao for enviado o PRONAC na pesquisa
                if(empty($post->pronac)){
                    $situacoesDePrestacaoContasMenosGrid = implode('\',\'',$this->arrSituacoesDePrestacaoContasMenosGrid);
                    $situacoesDePrestacaoContasMenosGrid = "'".$situacoesDePrestacaoContasMenosGrid."'";
                    $arrBusca["p.Situacao IN ({$situacoesDePrestacaoContasMenosGrid}) "] = '(?)';
                    //$arrBusca["p.Situacao IN ('".implode(',',$this->arrSituacoesDePrestacaoContasMenosGrid)."') AND p.Situacao NOT IN ('".implode(',',$this->arrSituacoesGrids)."') "] = '(?)';
                }
            }

            $total = 0;
            $tblProjetos = new Projetos ();
            $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, $bln_encaminhamento, $bln_dadosDiligencia);

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            if ($fim>$total) $fim = $total;

            $ordem = array();
            if(!empty($post->ordenacaoOS)){ $ordem[] = "{$post->ordenacaoOS} {$post->tipoOrdenacaoOS}"; }else{$ordem = array('1 ASC');}

            $rs = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, $ordem, $tamanho, $inicio, null, $bln_encaminhamento, $bln_dadosDiligencia);

            $this->view->registrosOS 	  = $rs;
            $this->view->pagOS 		  = $pag;
            $this->view->totalOS          = $total;
            $this->view->inicioOS 	  = ($inicio+1);
            $this->view->fimOS		  = $fim;
            $this->view->totalPagOS 	  = $totalPag;
            $this->view->parametrosBuscaOS = $_POST;

        }

    /**
     * @todo verificar o uso do input filter a nivel de plugin para remover do controller o uso do registro
     * @todo refatorar modularizando
     */
    public function coordenadorgeralprestacaocontasAction()
    {
        $post   = Zend_Registry::get('post');
        $bln_envioufiltro = 'false';
        $this->view->parametroPesquisado = 'OUTRAS SITUAÇÕES';

        // cria a sessão com o grupo ativo
        $GrupoAtivo = new Zend_Session_Namespace ( 'GrupoAtivo' ); 
        $this->view->Grupo = $GrupoAtivo->codGrupo;

        $db = Zend_Registry::get ( 'db' );
        $db->setFetchMode ( Zend_DB::FETCH_OBJ );

        //manteM os parametros de pesquisa enviados da tela anterior para ser capturado pelo ajax na hora de abrir o painel
        $this->view->parametrosBuscaPrestacaoContas  = $_POST;
        $this->view->bln_pronacValido = "true";

        $tblProjeto = new Projetos();
        $tblSituacao = new Situacao();

        $situacoes = array(
            "E17","E18","E19","E20","E22","E23","E24","E25","E27","E30","E46","L03","L04","L05","L06","L07",
            "E68","G18","G20","G21","G22","G24","G43","G47","L07","G19","G25","G43","G47"
        );
        $rsSituacao   = $tblSituacao->buscar(array("Codigo IN (?)"=>$situacoes));

        //se pesquisou pela SITUACAO do projeto
        if (isset($post->situacao) && !empty($post->situacao)){
            $descricaoSituacao  = $tblSituacao->buscar(array("Codigo = ?"=>$post->situacao))->current();
        }
        if(isset($descricaoSituacao) && !empty($descricaoSituacao)){
            $this->view->parametroPesquisado = $descricaoSituacao->Codigo.' - '.$descricaoSituacao->Descricao;
        }
        //se pesquisou pelo PRONAC
        if (isset($post->pronac) && !empty($post->pronac)){
            $rsProjeto = $tblProjeto->buscar(array("AnoProjeto+Sequencial = ?" => $post->pronac))->current();
            if(empty($rsProjeto)){ $this->view->bln_pronacValido = "false"; }
        }
        if(isset($rsProjeto) && !empty($rsProjeto)){
            $this->view->parametroPesquisado = 'PRONAC: '.$post->pronac.' - '.$rsProjeto->NomeProjeto;
        }
        //IF - RECUPERA ORGAOS PARA POPULAR COMBO AO ENCAMINHAR PROJETO
        if (isset ( $_POST ['verifica'] ) and $_POST ['verifica'] == 'a') {
            $idOrgaoDestino = $_POST ['idorgao'];
            // desabilita o Zend_Layout
            $this->_helper->layout->disableLayout (); 

            $tblProjetos  = new Projetos();
            $AgentesOrgao = $tblProjetos->buscarComboOrgaos($idOrgaoDestino);

            $a = 0;
            if (count($AgentesOrgao)>0) {
                foreach($AgentesOrgao as $agentes) {
                    $dadosAgente[$a]['usu_codigo'] = $agentes->usu_codigo;
                    $dadosAgente[$a]['usu_nome']   = utf8_encode ( $agentes->usu_nome );
                    $dadosAgente[$a]['Perfil']     = utf8_encode ( $agentes->gru_nome );
                    $dadosAgente[$a]['idperfil']   = $agentes->gru_codigo;
                    $dadosAgente[$a]['idAgente']   = utf8_encode ( $agentes->idAgente );
                    $a ++;
                }

                $jsonEncode = json_encode($dadosAgente);

                //echo $jsonEncode;
                echo json_encode(array ('resposta' => true, 'conteudo' => $dadosAgente) );
            } else {
                echo json_encode(array ('resposta' => false) );
            }
            die ();
        }

        //IF - BUSCA NOMES DOS TECNICOS QUANDO ENVIA O ORGAO PARA ENCAMINHAR PROJETO
        if (isset ( $_POST ['verifica2'] ) and $_POST ['verifica2'] == 'x') {
            $idagente = $_POST ['idagente'];
            if ($idagente != '') {
                $this->_helper->layout->disableLayout (); // desabilita o Zend_Layout
                $AgentesPerfil = ReadequacaoProjetos::dadosAgentesPerfil ( $idagente );
                $AgentesPerfil = $db->fetchAll ( $AgentesPerfil );
                $idperfil      = $AgentesPerfil [0]->idVerificacao;
                echo $idperfil;
            } else {
                echo "";
            }
            die ();
        }

        $sqllistasDeEntidadesVinculadas = ReadequacaoProjetos::retornaSQLlista ( "listasDeEntidadesVinculadas", NULL );
        $listaEntidades             = $db->fetchAll ( $sqllistasDeEntidadesVinculadas );
        $this->view->listaEntidades = $listaEntidades;

        /*=====================================================*/
        /*============== TOTAL AGUARDANDO ANALISE =============*/
        /*=====================================================*/
        $bln_dadosDiligencia = false;
        //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
        $arrBusca = $this->montaArrBuscaCoincidentes($post);

        //NOME PROJETO
                if(!empty($post->NomeProjeto)){
                    $projeto = ($post->NomeProjeto);
                    if($post->tipoPesqNomeProjeto == 'QC'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                    }else if($post->tipoPesqNomeProjeto == 'EIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto = ?"] = "{$projeto}"; }
                    }else if($post->tipoPesqNomeProjeto == 'IIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "{$projeto}%"; }
                    }
                    //if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                }
                $arrBusca['p.Situacao in (?)']= $this->arrSituacoesAguardandoAnalise;

                //DILIGENCIA
                if(!empty($post->diligencia)){
                    if($post->diligencia == "abertas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                    }elseif($post->diligencia == "respondidas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                    }
                    $bln_dadosDiligencia = true;
                }
                $total = 0;
                $tblProjetos = new Projetos ();
                //$total = $tbl->buscarProjetosVotoAlterado($arrBusca, array(), null, null, true);
                $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, false, $bln_dadosDiligencia);
                $this->view->totalAguardandoAnalise = $total;

                if ( (isset($post->tpPeriodoExecucao) && !empty($post->tpPeriodoExecucao)) || !empty($post->pronac) || !empty($post->NomeProjeto) || !empty($post->uf) || !empty($post->mecanismo) || !empty($post->situacao) || !empty($post->diligencia)){
                    $bln_envioufiltro='true';
                }
                $this->view->bln_envioufiltro = $bln_envioufiltro;

                /*=====================================================*/
        /*============= TOTAL DEVOLVIDOS APOS ANALISE =========*/
        /*=====================================================*/
                
                //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
                $arrBusca = $this->montaArrBuscaCoincidentes($post);
                
                //NOME PROJETO
                if(!empty($post->NomeProjeto)){
                    $projeto = ($post->NomeProjeto);
                    if($post->tipoPesqNomeProjeto == 'QC'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                    }else if($post->tipoPesqNomeProjeto == 'EIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto = ?"] = "{$projeto}"; }
                    }else if($post->tipoPesqNomeProjeto == 'IIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "{$projeto}%"; }
                    }
                }
                
                //SITUACAO
                $arrBusca['p.Situacao in (?)']= $this->arrSituacoesDevolvidosAposAnalise;
                if(isset($post->situacao) && !empty($post->situacao) && $post->situacao != 'E18'){
                    $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
                }

                //DILIGENCIA
                if(!empty($post->diligencia)){
                    if($post->diligencia == "abertas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                    }elseif($post->diligencia == "respondidas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                    }
                }
                //CONDICOES DE DEVOLVIDO APOS ANALISE
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise OU Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;

                $total = 0;
                $tblProjetos = new Projetos ();
                $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, true, true);
                $this->view->totalDevolvidosAposAnalise = $total;

                /*=====================================================*/
        /*============= TOTAL DILIGENCIADOS ===================*/
        /*=====================================================*/
                
                //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
                $arrBusca = $this->montaArrBuscaCoincidentes($post);
                
                //NOME PROJETO
                if(!empty($post->NomeProjeto)){
                    $projeto = ($post->NomeProjeto);
                    if($post->tipoPesqNomeProjeto == 'QC'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                    }else if($post->tipoPesqNomeProjeto == 'EIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto = ?"] = "{$projeto}"; }
                    }else if($post->tipoPesqNomeProjeto == 'IIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "{$projeto}%"; }
                    }
                }
                //SITUACAO
                $arrBusca['p.Situacao in (?)']= $this->arrSituacoesDiligenciados;
                if(isset($post->situacao) && !empty($post->situacao) && $post->situacao != 'E17'){
                    $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
                }

                //DILIGENCIA
                if(!empty($post->diligencia)){
                    if($post->diligencia == "abertas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                    }elseif($post->diligencia == "respondidas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                    }
                }
                //CONDICOES DE PARA ESTAR COM O COORD. DE PRESTACAO DE CONTAS
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise OU Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;
                $arrBusca['d.DtSolicitacao = (SELECT top 1 d2.DtSolicitacao FROM SAC..tbDiligencia d2 WHERE d2.idPronac = d.idPronac ORDER BY d2.DtSolicitacao DESC)'] = '(?)'; //seleciona a ultima diligencia realizada
                $arrBusca['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas

                $total = 0;
                $tblProjetos = new Projetos ();
                $total = $tblProjetos->buscaProjetoDiligenciadosPrestacaoContas($arrBusca, array(), null, null, true);
                $this->view->totalDiligenciados = $total;


                /*=====================================================*/
        /*============= TOTAL PROJETOS TCE ====================*/
        /*=====================================================*/
                
                //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
                $arrBusca = $this->montaArrBuscaCoincidentes($post);
                
                //NOME PROJETO
                if(!empty($post->NomeProjeto)){
                    $projeto = ($post->NomeProjeto);
                    if($post->tipoPesqNomeProjeto == 'QC'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                    }else if($post->tipoPesqNomeProjeto == 'EIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto = ?"] = "{$projeto}"; }
                    }else if($post->tipoPesqNomeProjeto == 'IIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "{$projeto}%"; }
                    }
                }

                $arrBusca['p.Situacao in (?)'] = $this->arrSituacoesTCE;
                if ( isset($post->situacao) && !empty($post->situacao)){
                    $arrBusca["NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbEncaminhamentoPrestacaoContas where idOrgaoDestino in ('177','12')and stAtivo=1)"] = '(?)'; //eliminando projetos que estao em consultoria
                }
                //DILIGENCIA
                if(!empty($post->diligencia)){
                    if($post->diligencia == "abertas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NULL AND idTipoDiligencia = '174')"] = '(?)';
                    }elseif($post->diligencia == "respondidas"){
                        $arrBusca["EXISTS(SELECT TOP 1 * FROM SAC.dbo.tbDiligencia d where d.idPronac = p.idPronac AND d.DtSolicitacao IS NOT NULL AND d.DtResposta IS NOT NULL AND stEnviado = 'S' AND idTipoDiligencia = '174')"] = '(?)';
                    }
                }
                //CONDICOES DE PARA ESTAR COM O COORD. DE PRESTACAO DE CONTAS
                $arrBusca['e.idSituacaoEncPrestContas in (?)']= array('1','2'); //Status Aguardando analise, e Em analise
                $arrBusca['e.cdGruposDestino IN (?)']= array('125','126'); //grupo de coordenador de prestacao de contas
                $arrBusca['e.cdGruposOrigem = ?']= array('132'); //grupo do chefe de divisao
                $arrBusca['e.stAtivo = ?']= 1;

                $total = 0;
                $tblProjetos = new Projetos ();
                $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, true);
		$this->view->totalProjetosTCE = $total;

                /*=====================================================*/
		/*============== OUTRAS SITUACOES =====================*/
		/*=====================================================*/
                $bln_encaminhamento = false;
                $bln_dadosDiligencia = false;

                //MONTA ARRAY BUSCA COM PARAMETROS COINCIDENTES PARA TODOS OS GRIDS DO COORD.
                $arrBusca = $this->montaArrBuscaCoincidentes($post);
                
                //NOME PROJETO
                if(!empty($post->NomeProjeto)){
                    $projeto = ($post->NomeProjeto);
                    if($post->tipoPesqNomeProjeto == 'QC'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "%{$projeto}%"; }
                    }else if($post->tipoPesqNomeProjeto == 'EIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto = ?"] = "{$projeto}"; }
                    }else if($post->tipoPesqNomeProjeto == 'IIG'){
                            if(!empty($post->NomeProjeto)){ $arrBusca["p.NomeProjeto like (?)"] = "{$projeto}%"; }
                    }
                }
                
                //PRONAC
                if(!empty($post->pronac)){
                    $arrBusca["p.AnoProjeto + p.Sequencial = ?"] = trim($post->pronac);
                    $arrBusca["p.Situacao IN (?) "] = $this->arrSituacoesDePrestacaoContas;
                }
                //SITUACAO
        if ( isset($post->situacao) && !empty($post->situacao)){
            $arrBusca = $this->incluiRegrasGridsPrincipais($arrBusca,$post);
            $arrBusca["p.Situacao = ? "] = $post->situacao;
            if(in_array($post->situacao,$this->arrSituacoesDevolvidosAposAnalise) || in_array($post->situacao,$this->arrSituacoesDiligenciados)  || in_array($post->situacao,$this->arrSituacoesTCE) ){
                $bln_encaminhamento = true;
            }
            if(in_array($post->situacao,$this->arrSituacoesDiligenciados)){
                $bln_dadosDiligencia = true;
            }
        }else{
                //deve fazer este filtro apenas se nao for enviado o PRONAC na pesquisa
            if(empty($post->pronac)){
                $situacoesDePrestacaoContasMenosGrid = implode('\',\'',$this->arrSituacoesDePrestacaoContasMenosGrid);
                $situacoesDePrestacaoContasMenosGrid = "'".$situacoesDePrestacaoContasMenosGrid."'";
                $arrBusca["p.Situacao IN ({$situacoesDePrestacaoContasMenosGrid}) "] = '(?)';
            }
        }

        $total = 0;
        $tblProjetos = new Projetos ();
        $total = $tblProjetos->buscarProjetosPrestacaoContas($arrBusca, array(), null, null, true, $bln_encaminhamento, $bln_dadosDiligencia, true);
        $this->view->totalProjetosOS = $total;
    }

	public function historicoencaminhamentoAction(){
		
		// desabilita o Zend_Layout
		$this->_helper->layout->disableLayout (); 

		$post     = Zend_Registry::get("get");
		$idPronac = $post->idPronac;
		
		$this->view->Historico = array();

		if (!empty ($idPronac)) {
			$tblProjeto = new Projetos();
			$rsProjeto  =  $tblProjeto->find($idPronac)->current();

			$this->view->PRONAC      = $rsProjeto->AnoProjeto.$rsProjeto->Sequencial;
			$this->view->NomeProjeto = $rsProjeto->NomeProjeto;

			$tblEncaminhamento     = new tbEncaminhamentoPrestacaoContas();
			$rsHistorico           = $tblEncaminhamento->HistoricoEncaminhamentoPrestacaoContas($idPronac);
			$this->view->Historico = $rsHistorico;
		}
	}

	/**
	 * Laudo Final
	 * @access public
	 * @param void
	 * @return void
	 */
	public function laudofinalAction(){

            $auth = Zend_Auth::getInstance(); // pega a autenticação
            $get = Zend_Registry::get ('get');
            $idpronac = $this->getRequest()->getParam('idPronac');
            $nomeProponente = null;

            $projetosDAO = new Projetos ();
            $tblAgente = new Agentes();

            $rsProjeto = $projetosDAO->buscar(array ('IdPRONAC = ? '=> "{$idpronac}"));
            $pronac = $rsProjeto[0]->AnoProjeto . $rsProjeto[0]->Sequencial;

            //Recuperando nome do proponente
            $rsAgente = $tblAgente->buscar(array("CNPJCPF = ? "=>$rsProjeto[0]->CgcCpf))->current();

            if(!empty($rsAgente)){
                $nomeProponente = $tblAgente->buscarAgenteNome(array("a.idAgente = ?"=>$rsAgente->idAgente))->current();
            }
            if(!empty($nomeProponente)){
                $nomeProponente = $nomeProponente->Descricao;
            }

            $this->view->nomeProponente = $nomeProponente;
            $this->view->pronac         = $rsProjeto[0]->AnoProjeto . $rsProjeto[0]->Sequencial;
            $this->view->nomeProjeto    = $rsProjeto[0]->NomeProjeto;
            $this->view->idPronac       = $rsProjeto[0]->IdPRONAC;

            $RelatorioTecnico = new tbRelatorioTecnico();
            $rsParecerTecnico = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>124))->current();
            $rsParecerChefe   = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>132))->current();
            $rsParecerCoord   = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>125))->current();

            $nomeTecnico = (!empty($rsParecerTecnico)) ? $tblAgente->buscarAgenteNome(array("a.idAgente = ?"=>$rsParecerTecnico->idAgente))->current() : '';
            $nomeChefe   = (!empty($rsParecerChefe)) ? $tblAgente->buscarAgenteNome(array("a.idAgente = ?"=>$rsParecerChefe->idAgente))->current() : '';
            $nomeCoord   = (!empty($rsParecerCoord)) ? $tblAgente->buscarAgenteNome(array("a.idAgente = ?"=>$rsParecerCoord->idAgente))->current() : '';

            if(is_object($rsParecerTecnico)){
                $this->view->parecerTecnico = $rsParecerTecnico;
                $this->view->parecerChefe   = $rsParecerChefe;
                $this->view->parecerCoord   = $rsParecerCoord;

                $this->view->nomeTecnico = $nomeTecnico;
                $this->view->nomeChefe   = $nomeChefe;
                $this->view->nomeCoord   = $nomeCoord;
            }else{
                $this->view->parecerTecnico = array();
                $this->view->parecerChefe   = array();
                $this->view->parecerCoord   = array();
            }
            /*********************************************************************************/

            $this->view->dadosInabilitado   = array();
            $this->view->resultadoParecer   = null;
            $this->view->tipoInabilitacao   = null;

            //resultado parecer
            if($rsProjeto[0]->Situacao == 'E19'){
                $this->view->resultadoParecer = 'Aprovado Integralmente';
            }
            if($rsProjeto[0]->Situacao == 'E22'){
                $this->view->resultadoParecer = 'Indeferido';
            }
            if($rsProjeto[0]->Situacao == 'L03'){
                $this->view->resultadoParecer = 'Aprovado com Ressalvas';
            }

            $tblInabilitado = new Inabilitado();
            $rsInabilitado = $tblInabilitado->buscar(array('AnoProjeto+Sequencial=?'=>$pronac))->current();
            $this->view->dadosInabilitado = $rsInabilitado;

            if(is_object($rsInabilitado) && isset($rsInabilitado->idTipoInabilitado) && !empty($rsInabilitado->idTipoInabilitado)){
                $tbTipoInabilitado =  new tbTipoInabilitado();
                $rsTipoInabilitado = $tbTipoInabilitado->buscar(array('idTipoInabilitado=?'=>$rsInabilitado->idTipoInabilitado))->current();
                if(is_object($rsTipoInabilitado)){
                    $this->view->tipoInabilitacao = $rsTipoInabilitado->dsTipoInabilitado;
                }
            }

            //NUMERO DO PROCESSO
            $processo = null;
            $siglaOrgaoGuia = null;
            $docs = TramitarprojetosDAO::buscaProjetoPDF($idpronac);

            foreach ($docs as $d){
                //$idDocumento = $d->idDocumento;
                $processo = Mascara::addMaskProcesso($d->Processo);
                $siglaOrgaoGuia = $d->Sigla;
                $orgaoOrigemGuia = $d->OrgaoOrigem;
            }
            $this->view->processo = $processo;
            $this->view->siglaOrgaoGuia = $siglaOrgaoGuia;
            $this->view->emissor  = $auth->getIdentity()->usu_nome;
            
            $tbAssinantesPrestacao = new tbAssinantesPrestacao();
            $assinantes = $tbAssinantesPrestacao->buscar(array('stAtivo = ?'=>1));
            
            $CoordIncFisc = array();
            $CoordGeral = array();
            $Diretores = array();
            $Secretarios = array();
            foreach ($assinantes as $ass) {
                switch ($ass->tpCargo) {
                    case '1':
                        $CoordIncFisc[] = $ass;
                        break;
                    case '2':
                        $CoordGeral[] = $ass;
                        break;
                    case '3':
                        $Diretores[] = $ass;
                        break;
                    case '4':
                        $Secretarios[] = $ass;
                        break;
                    default:
                        break;
                }
            }
            $this->view->CoordIncFisc = $CoordIncFisc;
            $this->view->CoordGeral = $CoordGeral;
            $this->view->Diretores = $Diretores;
            $this->view->Secretarios = $Secretarios;
	}

	/*Laudo Final*/
	public function laudofinalInabilitadoAction(){
		
		$get      = Zend_Registry::get ('get');
		$idpronac = $get->idPronac;

		$projetosDAO = new Projetos ();
		$resposta    = $projetosDAO->buscar(array ('IdPRONAC = ? '=> "{$idpronac}"));
		
		$this->view->pronac      = $resposta [0]->AnoProjeto . $resposta [0]->Sequencial;
		$this->view->nomeProjeto = $resposta[0]->NomeProjeto;
		$this->view->idPronac    = $resposta[0]->IdPRONAC;

		$RelatorioTecnico = new tbRelatorioTecnico();
		$rsParecerTecnico = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>124))->current();
		$rsParecerChefe   = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>132))->current();

		if(is_object($rsParecerTecnico)){
			$this->view->parecerTecnico = $rsParecerTecnico;
			$this->view->parecerChefe   = $rsParecerChefe;
		}else{
			$this->view->parecerTecnico = array();
			$this->view->parecerChefe   = array();
		}
	}

    /*Laudo Final - Final*/
    public function gravarlaudofinalAction()
    {
        $post = Zend_Registry::get ('post');
        $idPronac = $post->idPronac;
        $parecer = NULL;
        
        $idTipoInabilitado = null;
        $arrOpcaoEscolhida = array();
        $arrOpcoes = array("A1A","A1B","A1C","A2A","A2B","A3A","A3B");

        foreach ($arrOpcoes as $chave => $valor) {
            if (key_exists($valor, $_POST)) {
                $arrOpcaoEscolhida[] = $valor;
            }
        }

        if (in_array($arrOpcoes[0], $arrOpcaoEscolhida) && in_array($arrOpcoes[1], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 1;
        } elseif (in_array($arrOpcoes[1], $arrOpcaoEscolhida) && in_array($arrOpcoes[2], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 2;
        } elseif (in_array($arrOpcoes[0], $arrOpcaoEscolhida) && in_array($arrOpcoes[2], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 3;
        } elseif (in_array($arrOpcoes[3], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 4;
        } elseif (in_array($arrOpcoes[4], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 5;
        } elseif (in_array($arrOpcoes[5], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 6;
        } elseif (in_array($arrOpcoes[6], $arrOpcaoEscolhida)) {
            $idTipoInabilitado = 7;
        }

        $relatorioTecnico = new tbRelatorioTecnico();

        $dados ['meRelatorio'] = (trim($parecer));
        $dados ['dtRelatorio'] = date("Y-m-d H:i:s");
        $dados ['IdPRONAC'] = $idPronac;
        $dados ['idAgente'] = $this->getIdAgenteLogado;
        $dados ['cdGrupo'] = $this->codGrupo;
        $dados ['siManifestacao'] = $post->IN == 'aprovado' ? 1 : 0;

        try {
            $relatorioTecnico->inserir($dados);
            
            //===== inlcui parecer do coordenador (laudo final)
            $tbLaudoFinal = new tbLaudoFinal();
            $dadosLaudoFinal ['idPronac'] = $idPronac;
            $dadosLaudoFinal ['nmCoordIncentivos'] = $post->coordenadorIncentivoFiscal;
            $dadosLaudoFinal ['nmCoordPrestacao'] = $post->coordenadorPrestacaoDeContas;
            $dadosLaudoFinal ['nmDiretor'] = $post->diretorIncentivoACultura;
            $dadosLaudoFinal ['nmSecretario'] = $post->coordenadorIncentivoACultura;
            $dadosLaudoFinal ['dtLaudoFinal'] = date("Y-m-d H:i:s");
            $tbLaudoFinal->inserir($dadosLaudoFinal);

            //alteracao projeto
            $tblProjeto = new Projetos ();
            $rsProjeto = $tblProjeto->find($idPronac)->current();

            $cpfCnpj = $rsProjeto->CgcCpf;
            $anoProjeto = $rsProjeto->AnoProjeto;
            $sequencial = $rsProjeto->Sequencial;
            $idProjeto = $rsProjeto->idProjeto;

            if (!empty($idTipoInabilitado)) {
                $tblInabilitado = new Inabilitado();

                $arrBusca = array();
                $arrBusca['CgcCpf = ?'] = $cpfCnpj;
                $arrBusca['AnoProjeto = ?'] = $anoProjeto;
                $arrBusca['Sequencial = ?'] = $sequencial;
                $rsInabilitado = $tblInabilitado->buscar($arrBusca)->current();
                //verifica se o proponente ja esta inabilitado para esse projeto nesse ano
                if (empty($rsInabilitado)) {
                    $dadosInab['CgcCpf'] = $cpfCnpj;
                    $dadosInab['AnoProjeto'] = $anoProjeto;
                    $dadosInab['Sequencial'] = $sequencial;
                    $dadosInab['Orgao'] = $this->codOrgao;
                    $dadosInab['Logon'] = $this->getIdUsuario;
                    $dadosInab['Habilitado'] = "N";
                    $dadosInab['idProjeto'] = $idProjeto;
                    $dadosInab['idTipoInabilitado'] = $idTipoInabilitado;
                    $dadosInab['dtInabilitado'] = date("Y-m-d H:i:s");
                    $tblInabilitado->inserir($dadosInab);
                } else {
                    $rsInabilitado->Orgao = $this->codOrgao;
                    $rsInabilitado->Logon = $this->getIdUsuario;
                    $rsInabilitado->Habilitado = "N";
                    $rsInabilitado->idTipoInabilitado = $idTipoInabilitado;
                    $rsInabilitado->dtInabilitado = date("Y-m-d H:i:s");
                    $rsInabilitado->save();
                }
            }

            $this->_forward('gerarpdf');
//            parent::message('Laudo final da prestação de contas emitido com sucesso!', "realizarprestacaodecontas/laudofinal?idPronac={$idPronac}&gerarGuia=true", 'CONFIRM');
        } catch (Exception $e) {
            parent::message('Erro ao gravar laudo final!', "realizarprestacaodecontas/laudofinal?idPronac=" . $idPronac, 'ERROR');
            //$this->_redirect("realizarprestacaodecontas/laudofinal?idPronac=".$idPronac."&tipoMsg=ERROR&msg=Erro ao gravar laudo final! ");
            return;
        }
    }
    
    /*
     * Consultar Laudo Final
     * Perfis: Coord. de Prestação de Contas, Téc. de Prestação de Contas e Chefe de Divisão
     */
    public function consultarLaudoFinalAction()
    {
        
    }
    
    /*
     * Analisar Laudo Final
     * Perfis: Coord. Geral de Prestação de Contas
     */
    public function analisarLaudoFinalAction()
    {
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $GrupoUsuario = $GrupoAtivo->codGrupo;
        
        if($GrupoUsuario != 126){ //Se o perfil for diferente de Coord. Geral de Prestação de Contas, não permite o acesso dessa funcionalidade.
            parent::message('Você não tem permissão para acessar essa funcionalidade.', "principal", 'ALERT');
        }
        
        $this->intTamPag = 10;

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
            $order = array(2); //Pronac
            $ordenacao = null;
        }

        $pag = 1;
        $post  = Zend_Registry::get('get');
        if (isset($post->pag)) $pag = $post->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['p.Orgao = ?'] = $this->codOrgao;
        $where['p.Situacao in (?)'] = array('E27');
        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
        $where['e.cdGruposDestino in (?)'] = array('125','126');
        $where['e.cdGruposOrigem = ?'] = 132;
        $where['e.stAtivo = ?'] = 1;
        $where['rt.cdGrupo in (?)'] = array(125,126);
                    
        if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
        }
        
        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            $this->view->filtro = $filtro;
            switch ($filtro) {
                case 'aprovado': //Aprovados
                    $where['rt.siManifestacao = ?'] = 1;
                    break;
                case 'reprovado': //Reprovados
                    $where['rt.siManifestacao = ?'] = 0;
                    break;
                default: //Aguardando Análise
                    break;
            }
        }

        $Projetos = new Projetos();
        $total = $Projetos->buscarPainelCoordGeralPrestDeContas($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $Projetos->buscarPainelCoordGeralPrestDeContas($where, $order, $tamanho, $inicio, false);

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
    
    public function imprimirAnalisesLaudoFinalAction()
    {
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
        $this->intTamPag = 10;

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
            $order = array(2); //Pronac
            $ordenacao = null;
        }

        $pag = 1;
        $post  = Zend_Registry::get('post');
        if (isset($post->pag)) $pag = $post->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['p.Orgao = ?'] = $this->codOrgao;
        $where['p.Situacao in (?)'] = array('E27');
        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
        $where['e.cdGruposDestino in (?)'] = array('125','126');
        $where['e.cdGruposOrigem = ?'] = 132;
        $where['e.stAtivo = ?'] = 1;
        $where['rt.cdGrupo in (?)'] = array(125,126);
                    
        if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
        }
        
        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            $this->view->filtro = $filtro;
            switch ($filtro) {
                case 'aprovado': //Aprovados
                    $where['rt.siManifestacao = ?'] = 1;
                    break;
                case 'reprovado': //Reprovados
                    $where['rt.siManifestacao = ?'] = 0;
                    break;
                default: //Aguardando Análise
                    break;
            }
        }

        $Projetos = new Projetos();
        $total = $Projetos->buscarPainelCoordGeralPrestDeContas($where, $order, null, null, true, $filtro);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $Projetos->buscarPainelCoordGeralPrestDeContas($where, $order, $tamanho, $inicio, false, $filtro);

        if(isset($post->xls) && $post->xls){
            
            $html = '';
            $html .= '<table style="border: 1px">';
            $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 16; font-weight: bold;" colspan="9">Analisar Laudo Final</td></tr>';
            $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 10" colspan="9">Data do Arquivo: '. Data::mostraData() .'</td></tr>';
            $html .='<tr><td colspan="9"></td></tr>';

            $html .= '<tr>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">#</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">PRONAC</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Nome do Projeto</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Situação</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Área / Segmento</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Cidade</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Mecanismo</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Recebimento</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Status</th>';
            $html .= '</tr>';

            $i=1;
            foreach ($busca as $projeto){

                $mecanismo = $projeto->Mecanismo;
                if($mecanismo == 'Mecenato'){
                    $mecanismo = "Incentivo Fiscal";
                }
                
                $siManifestacao = 'Reprovado';
                if($projeto->siManifestacao == 1){
                    $siManifestacao = 'Aprovado';
                }

                $dt = Data::tratarDataZend($projeto->DtSituacao, 'brasileira');

                $html .= '<tr>';
                $html .= '<td style="border: 1px dotted black;">'.$i.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$projeto->Pronac.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$projeto->NomeProjeto.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$projeto->Situacao.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$projeto->Area.' / '.$projeto->Segmento.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$projeto->UfProjeto.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$mecanismo.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$dt.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$siManifestacao.'</td>';
                $html .= '</tr>';
                $i++;
            }
            $html .= '</table>';

            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=Painel_Analisar_Laudo_Final.xls;");
            echo $html; die();

        } else {
            $this->view->dados = $busca;
        }
    }
    
    /*
     * Imprimir Laudo Final
     * Perfis: Coord. de Prestação de Contas, Téc. de Prestação de Contas e Chefe de Divisão
     */
    public function imprimirLaudoFinalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $tbProjetos = new Projetos();
        $dados = $tbProjetos->dadosImpressaoLaudo($this->getRequest()->getParam('pronacPesquisa'))->current();
//        exit(var_dump($dados));

        $tbRelatorioTecnico = new tbRelatorioTecnico();
        $resultados = $tbRelatorioTecnico->buscar(array('IdPRONAC=?' => $dados->IdPRONAC));
        $totalRegistros = $resultados->count();
        if (empty($totalRegistros)) {
            parent::message(
                    'Este PRONAC não possui Laudo Final.',
                    '/realizarprestacaodecontas/consultar-laudo-final',
                    'ERROR'
                    );
            return;
        }

        $isAprovado = '';
        foreach ($resultados as $r) {
            if($r->cdGrupo == 125 || $r->cdGrupo == 126){
                $isAprovado = $r->siManifestacao == 1 ? 'aprovado' : 'reprovado';
            }
        }

        $folder = '';
        if ('aprovado' == $isAprovado) {
            $folder = 'aprovado';
        } elseif ('reprovado' == $isAprovado) {
            $folder = 'reprovado';
        }

        if (empty($folder)) {
            parent::message(
                    'Informe se a prestação de contas foi Aprovada ou Reprovada',
                    '/realizarprestacaodecontas/laudofinal/idPronac/' . $dados->IdPRONAC,
                    'ERROR'
                    );
            return;
        }
        $partialPath = "realizarprestacaodecontas/partial/laudo-final/";
        $partials = array(
            $this->view->partial("{$partialPath}/{$folder}/laudo.phtml"),
            $this->view->partial("{$partialPath}/{$folder}/comunicado.phtml"),
            $this->view->partial("{$partialPath}/parecer-tecnico.phtml"),
        );

        $html = implode('<div style="page-break-before: always;">', $partials);
        $html .= '<script language="javascript" type="text/javascript" src="/minc/salic/public/js/jquery-1.4.2.min.js"></script><script type="text/javascript">$(document).ready(function(){window.print();});</script>';
        $html .= $this->view->partial("{$partialPath}/parecer-chefe-de-divisao.phtml");

        foreach ($dados as $key => $value) {
            $html = str_replace("{{$key}}", $value, $html);
        }

        foreach ($resultados as $key => $value) {
            if($value->cdGrupo == 124){
                $manifestacao = $value['siManifestacao'] == 1 ? 'Regular' : 'Irregular';
                $html = str_replace("{manifestacaoParecerTecnico}", $manifestacao, $html);
                $html = str_replace("{parecerDoTecnico}", $value['meRelatorio'], $html);
                
            } else if($value->cdGrupo == 132){
                $manifestacao = $value['siManifestacao'] == 1 ? 'Regular' : 'Irregular';
                $html = str_replace("{manifestacaoChefeDeDivisao}", $manifestacao, $html);
                $html = str_replace("{parecerDoChefeDeDivisao}", $value['meRelatorio'], $html);
            }
        }
        $tbLaudoFinal = new tbLaudoFinal();
        $dadosLaudo = $tbLaudoFinal->buscar(array('idPronac=?'=>$dados->IdPRONAC));
        foreach ($dadosLaudo as $key => $value) {
            $html = str_replace("{coordenadorIncentivoFiscal}", $value->nmCoordIncentivos, $html);
            $html = str_replace("{coordenadorPrestacaoDeContas}", $value->nmCoordPrestacao, $html);
            $html = str_replace("{diretorIncentivoACultura}", $value->nmDiretor, $html);
            $html = str_replace("{coordenadorIncentivoACultura}", $value->nmSecretario, $html);
        }

        echo $html;
    }

    /**
     * Avaliação final do laudo de prestação de contas - Perfil: Coord. Geral de Prestação de Contas
     * @access public
     * @param void
     * @return void
     */
    public function avaliacaoFinalDoLaudoAction() {
        $get = Zend_Registry::get('get');
        
        // cria a sessão com o grupo ativo
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');

        //  Órgão ativo na sessão
        $codOrgao = $GrupoAtivo->codOrgao;
        
        $tblProjeto = new Projetos ();
        $rsProjeto = $tblProjeto->find($get->idPronac)->current();
        
        try {

            if($get->avaliacao == 'aprovado') {
                // Aprovado
                $situacao = 'D42';
                $ProvidenciaTomada = 'Aguardando Publicação de Portaria da Prestação de Contas';
                $textoPrestacaoDeContas = 'Aprovação da Prestação de Contas';
                $TpApPrestacaoDeContas = 5;
            } elseif ($get->avaliacao == 'reprovado') {
                // Reprovado
                $situacao = 'D43';
                $ProvidenciaTomada = 'Aguardando Portaria da Prestação de Contas';
                $textoPrestacaoDeContas = 'Reprovação da Prestação de Contas';
                $TpApPrestacaoDeContas = 6;
            } else {
                parent::message('Erro ao tentar salvar os dados. Entre em contato com o administrador do sistema!', "realizarprestacaodecontas/analisar-laudo-final", 'ERRO');
            }

            // altera a situação do projeto
            $tblProjeto->alterarSituacao($get->idPronac, '', $situacao, $ProvidenciaTomada);

            $auth = Zend_Auth::getInstance(); // pega a autenticação
            $agente = GerenciarPautaReuniaoDAO::consultaAgenteUsuario($auth->getIdentity()->usu_codigo);
            $idagente = $agente['idAgente'];

            $Aprovacao = new Aprovacao();
            $dadosAprovacao = array(
                'IdPRONAC' => $rsProjeto->IdPRONAC,
                'AnoProjeto' => $rsProjeto->AnoProjeto,
                'Sequencial' => $rsProjeto->Sequencial,
                'TipoAprovacao' => $TpApPrestacaoDeContas,
                'DtAprovacao' => new Zend_Db_Expr('GETDATE()'),
                'ResumoAprovacao' => $textoPrestacaoDeContas,
                'Logon' => $idagente
            );
            $Aprovacao->inserir($dadosAprovacao);
            parent::message('Projeto encaminhado para publicação do Diário Oficial com sucesso.', "realizarprestacaodecontas/analisar-laudo-final", 'CONFIRM');
            
        } catch (Exception $e) {
            parent::message('Erro ao encaminhar o projeto para publicação do Diário Oficial.', "realizarprestacaodecontas/analisar-laudo-final", 'ERROR');
        }
    }

    /**
     * Encaminha a prestação de contas
     * 
     * @access public
     * @param void
     * @return void
     */
    public function encaminharprestacaodecontasAction() {
        $tipoFiltro = $this->_request->getParam('tipoFiltro');
        $this->view->pag = 1; //Se tirar isso, não funciona. Por isso não foi retirado!

        /** Usuario Logado *********************************************** */
        // caso o formulário seja enviado via post
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo

        $auth               = Zend_Auth::getInstance();
        $Usuario            = new Usuario();
        $idagente           = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
        $idAgenteOrigem     = $idagente['idAgente'];
        $this->usu_codigo   = $auth->getIdentity()->usu_codigo;

            // recebe os dados via post
            $post = Zend_Registry::get('post');
            if ($this->getRequest()->isPost() && !empty($post->dsjustificativa)) {
                
                $idPronac               = $post->idPronac;
                $dtInicioEncaminhamento = new Zend_Db_Expr('GETDATE()');
                $dsJustificativa        = $post->dsjustificativa;
                $idOrgaoOrigem          = $this->codOrgao;
                $idOrgaoDestino         = $post->passaValor;
                $arrAgenteGrupo         = explode("/", $post->recebeValor);
                $idAgenteOrigem         = $idagente['idAgente'];
                $idAgenteDestino        = $arrAgenteGrupo[0];
                $idGrupoDestino         = $arrAgenteGrupo[1];
                $idSituacaoPrestContas  = $post->idSituacaoPrestContas;

                try {
                    //GRUPO : ORGAO
                    //100: 177 AECI
                    //100: 12 CONJUR
                    //SE O ENCAMINHAMENTO FOR DO COORDENADOR PARA O TECNICO - ALTERA SITUACAO DO PROJETO
                    if (($this->codGrupo == 125 || $this->codGrupo == 126) && $idGrupoDestino == 124) {
                        // altera a situação do projeto AO ENCAMINHAR PARA O TECNICO
                        $tblProjeto = new Projetos();
                        $tblProjeto->alterarSituacao($idPronac, '', 'E27', 'Comprovação Financeira do Projeto em Análise');
                    }

                    //BUSCA ULTIMO STATUS DO PROJETO
                    $tblProjeto = new Projetos();
                    $rsProjeto  = $tblProjeto->find($idPronac)->current();
                    $idSituacao = $rsProjeto->Situacao;

                    //ENCAMINHA PROJETO
                    $dados = array(
                        'idPronac'                  => $idPronac,
                        'idAgenteOrigem'            => $this->getIdAgenteLogado,
                        'idAgenteDestino'           => $idAgenteDestino,
                        'idOrgaoOrigem'             => $idOrgaoOrigem,
                        'idOrgaoDestino'            => $idOrgaoDestino,
                        'dtInicioEncaminhamento'    => $dtInicioEncaminhamento,
                        'dtFimEncaminhamento'       => new Zend_Db_Expr('GETDATE()'),
                        'dsJustificativa'           => $dsJustificativa,
                        'cdGruposOrigem'            => $this->codGrupo,
                        'cdGruposDestino'           => $idGrupoDestino,
                        'idSituacaoEncPrestContas'  => $idSituacaoPrestContas,
                        'idSituacao'                => $idSituacao,
                        'stAtivo'                   => 1
                    );
                    $tblEncaminhamento = new EncaminhamentoPrestacaoContas();
                    $tblEncaminhamento->inserir($dados);
                    if($this->codGrupo == 132){
                        parent::message('Solicitação enviada com sucesso!', "realizarprestacaodecontas/chefedivisaoprestacaocontas?tipoFiltro=diligenciados", 'CONFIRM');
                    } else if($this->codGrupo == 124){
                        parent::message('Solicitação enviada com sucesso!', "realizarprestacaodecontas/tecnicoprestacaocontas?tipoFiltro=diligenciados", 'CONFIRM');
                    } else {
                        parent::message('Solicitação enviada com sucesso!', "realizarprestacaodecontas/painel?tipoFiltro=".$tipoFiltro, 'CONFIRM');
                    }
                } catch (Exception $e) {
                    parent::message('Erro ao tentar salvar os dados!', "principal", 'ERRO');
                }
            } else {
                // desabilita o Zend_Layout
                $this->_helper->layout->disableLayout();

                $post = Zend_Registry::get("post");
                $idPronac = $post->idPronac;
                $idOrgaoDestino = $post->idOrgaoDestino;
                $idSituacaoPrestContas = $post->idSituacaoPrestContas;
                $this->view->nomemodal = 'encaminhar';
                $this->view->Historico = array();
                $this->view->ocultarJustificativa = false;
                if(isset($post->ocultarJustificativa) && !empty($post->ocultarJustificativa) && $post->ocultarJustificativa){
                    $this->view->ocultarJustificativa = true;
                }

                $tblEncaminhamento = new tbEncaminhamentoPrestacaoContas();
                $rsEnc = $tblEncaminhamento->buscar(array('idPronac = ?' => $idPronac, 'idOrgaoDestino=?' => $idOrgaoDestino), array('dtFimEncaminhamento'));
                $this->view->consultorias = $rsEnc;

                $rsEncResp = $tblEncaminhamento->buscar(array('idPronac = ?' => $idPronac, 'idOrgaoOrigem=?' => $idOrgaoDestino), array('dtFimEncaminhamento'));
                $this->view->consultoriasResp = $rsEncResp;

                if (!empty($idPronac)) {
                    //$idPronac = 130978;
                    $tblProjeto = new Projetos();
                    $rsProjeto = $tblProjeto->find($idPronac)->current();

                    $this->view->PRONAC = $rsProjeto->AnoProjeto . $rsProjeto->Sequencial;
                    $this->view->NomeProjeto = $rsProjeto->NomeProjeto;
                    $this->view->idPronac = $idPronac;
                    $this->view->idSituacaoPrestContas = $idSituacaoPrestContas;

                    $db = Zend_Registry::get('db');
                    $db->setFetchMode(Zend_DB::FETCH_OBJ);

                    $orgaos = new Orgaos();
                    $arrBusca = array();

                    if ($idOrgaoDestino == '177' || $idOrgaoDestino == '12') {
                        $arrBusca['Codigo = ?'] = $idOrgaoDestino;
                        if ($idOrgaoDestino == '177') {
                            $this->view->nomemodal = 'aeci';
                        }
                        if ($idOrgaoDestino == '12') {
                            $this->view->nomemodal = 'conjur';
                        }
                    } else {
                        $arrBusca['Vinculo = 1 OR Codigo = (' . $idOrgaoDestino . ')'] = '?';
                    }
                    $this->view->listaEntidades = $orgaos->buscar($arrBusca, array('Sigla'));

                    $tblEncaminhamento = new tbEncaminhamentoPrestacaoContas();
                    $rsHistorico = $tblEncaminhamento->HistoricoEncaminhamentoPrestacaoContas($idPronac);
                    $this->view->Historico = $rsHistorico;
                }
            }
        }

    public function carregarDestinatariosTecnicosAction()
    {
        //IF - RECUPERA ORGAOS PARA POPULAR COMBO AO ENCAMINHAR PROJETO
        if (isset ( $_POST ['verifica'] ) and $_POST ['verifica'] == 'a') {
            $idOrgaoDestino = $_POST ['idorgao'];
            // desabilita o Zend_Layout
            $this->_helper->layout->disableLayout (); 

            $tblProjetos  = new Projetos();
            $AgentesOrgao = $tblProjetos->buscarComboOrgaos($idOrgaoDestino);

            $a = 0;
            if (count($AgentesOrgao)>0) {
                foreach($AgentesOrgao as $agentes) {
                    $dadosAgente[$a]['usu_codigo'] = $agentes->usu_codigo;
                    $dadosAgente[$a]['usu_nome']   = utf8_encode ( $agentes->usu_nome );
                    $dadosAgente[$a]['Perfil']     = utf8_encode ( $agentes->gru_nome );
                    $dadosAgente[$a]['idperfil']   = $agentes->gru_codigo;
                    $dadosAgente[$a]['idAgente']   = utf8_encode ( $agentes->idAgente );
                    $a ++;
                }

                $jsonEncode = json_encode($dadosAgente);

                //echo $jsonEncode;
                echo json_encode(array ('resposta' => true, 'conteudo' => $dadosAgente) );
            } else {
                echo json_encode(array ('resposta' => false) );
            }
            die ();
        }
    }

    // fecha método encaminharprestacaodecontasAction()
	

	/*Emcaminhamento para o Chefe de Divisão*/
	public function encaminharchefedivisaoAction() {

		// caso o formulário seja enviado via post
		// cria a sessão com o grupo ativo
		$GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); 
		
		// pega a autenticação
		$auth         = Zend_Auth::getInstance (); 
		$GrupoUsuario = $GrupoAtivo->codGrupo;
		if ($this->getRequest ()->isPost ()) {			
			// recebe os dados via post
			$post = Zend_Registry::get ( 'post' );
			
			$idPronac               = $post->idPronac;
			$idAgenteOrigem         = $this->getIdAgenteLogado;
			$dtInicioEncaminhamento = new Zend_Db_Expr ( 'GETDATE()' );
			$dsJustificativa        = $post->dsjustificativa;
			$idOrgaoDestino         = $post->passaValor;
			$idAgenteDestino        = explode ( "/", $post->recebeValor );
			$idAgenteDestino        = $idAgenteDestino [0];
			$idGrupo                = $idAgenteDestino [1];
			$gru_codigo             = $GrupoUsuario;
			$stSituacao             = 1;
				
			// monta o array de dados para cadastro
			$dados = array ('idPronac' => $idPronac, 'idAgenteOrigem' => $idAgenteOrigem, 'dtInicioEncaminhamento' => $dtInicioEncaminhamento, 'dsJustificativa' => $dsJustificativa, 'idOrgaoDestino' => $idOrgaoDestino, 'idAgenteDestino' => $idAgenteDestino, 'cdGruposDestino' => $GrupoUsuario, 'dtFimEncaminhamento' => new Zend_Db_Expr ( 'GETDATE()' ), 'idSituacaoEncPrestContas' => $idSituacaoEncPrestContas, 'idSituacao' => "E27" );
			
			// cadastra
			$EncaminhamentoPrestacaoContas = new EncaminhamentoPrestacaoContas ( $idPronac );
			$cadastrar                     = $EncaminhamentoPrestacaoContas->cadastrar ( $dados );
				
			// altera a situação do projeto
			$alterar_situacao = ProjetoDAO::alterarSituacao ( $idPronac, 'E27' );
				
			$updateprojetos = new Projetos ();				
			$updateprojetos->alterarSituacao($idPronac,NULL,'E27','Encaminhado');
				
			if ($cadastrar) {
				parent::message ( "cadastrado com sucesso!", "realizarprestacaodecontas/coordenadorgeralprestacaocontas", "CONFIRM" );
			} else {
				parent::message ( "Desculpe ocorreu um erro!", "realizarprestacaodecontas/coordenadorgeralprestacaocontas", "ERROR" );
			}
		} // fecha $_POST
	} // fecha método encaminharprestacaodecontasAction()

	/*Buscar Projeto do Coordenados Geral e Coordenador de Prestação de Contas*/
	public function coordenadorprestacaocontasAction() {

		$prescontas                  = new Projetos ();
		$dados                       = $prescontas->BuscarPrestacaoContas ( 'E24' );
		$this->view->CoordPresContas = $dados;

		$dados                                    = $prescontas->BuscarPrestacaoContas ( 'E17' );
		$this->view->CoordPresContasDiligenciados = $dados;
	}

	/*Buscar Projeto do Tecnico de Prestação de Contas*/
	public function tecnicoprestacaocontasAction()
	{
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
                $order = array(2); //Pronac
                $ordenacao = null;
            }

            $pag = 1;
            $post  = Zend_Registry::get('get');
            if (isset($post->pag)) $pag = $post->pag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

            /* ================== PAGINACAO ======================*/
            $where = array();
            if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
                $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
                $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            }

            if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
                $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
                switch ($filtro) {
                    case 'diligenciados': //Projetos diligenciados
                        $where['p.Situacao in (?)'] = array('E17', 'E30');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 124; //grupo do tecnico de prestacao de contas
                        $where['e.cdGruposOrigem IN (?)'] = array('125','126'); //grupo de coordenador de prestacao de contas
                        $where['e.idAgenteDestino = ?'] = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                        $where['e.stAtivo = ?'] = 1;
                        $where['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas
                        break;
                    default: //Aguardando Análise
                        $where['p.Situacao = ?'] = 'E27';
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 124; //grupo do tecnico de prestacao de contas
                        $where['e.idAgenteDestino = ?'] = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                        $where['e.stAtivo = ?'] = 1;
                        break;
                }
                
            } else { //Aguardando Análise
                $filtro = '';
                $where['p.Situacao = ?'] = 'E27';
                $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                $where['e.cdGruposDestino = ?'] = 124; //grupo do tecnico de prestacao de contas
                $where['e.idAgenteDestino = ?'] = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                $where['e.stAtivo = ?'] = 1;
            }
            $this->view->filtro = $filtro;
            
            $Projetos = new Projetos();
            $total = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, null, null, true, $filtro);
            $fim = $inicio + $this->intTamPag;

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, $tamanho, $inicio, false, $filtro);

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
        
	public function imprimirTecnicoPrestacaoDeContasAction()
	{
            $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
            
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
                $order = array(2); //Pronac
                $ordenacao = null;
            }

            $pag = 1;
            $post  = Zend_Registry::get('post');
            if (isset($post->pag)) $pag = $post->pag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

            /* ================== PAGINACAO ======================*/
            $where = array();
            if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
                $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
                $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            }

            if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
                $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
                switch ($filtro) {
                    case 'diligenciados': //Projetos diligenciados
                        $this->view->tituloPag = 'Projetos diligenciados';
                        $where['p.Situacao in (?)'] = array('E17', 'E30');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 124; //grupo do tecnico de prestacao de contas
                        $where['e.cdGruposOrigem IN (?)'] = array('125','126'); //grupo de coordenador de prestacao de contas
                        $where['e.idAgenteDestino = ?'] = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                        $where['e.stAtivo = ?'] = 1;
                        $where['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas
                        break;
                    default: //Aguardando Análise
                        $this->view->tituloPag = 'Aguardando Análise';
                        $where['p.Situacao = ?'] = 'E27';
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 124; //grupo do tecnico de prestacao de contas
                        $where['e.idAgenteDestino = ?'] = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                        $where['e.stAtivo = ?'] = 1;
                        break;
                }
                
            } else { //Aguardando Análise
                $this->view->tituloPag = 'Aguardando Análise';
                $filtro = '';
                $where['p.Situacao = ?'] = 'E27';
                $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                $where['e.cdGruposDestino = ?'] = 124; //grupo do tecnico de prestacao de contas
                $where['e.idAgenteDestino = ?'] = $this->getIdAgenteLogado; //id Tecnico de Prestação de Contas
                $where['e.stAtivo = ?'] = 1;
            }
            $this->view->filtro = $filtro;
            
            $Projetos = new Projetos();
            $total = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, null, null, true, $filtro);
            $fim = $inicio + $this->intTamPag;

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, $tamanho, $inicio, false, $filtro);

            if(isset($post->xls) && $post->xls){
                $html = '';
                $html .= '<table style="border: 1px">';
                $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 16; font-weight: bold;" colspan="8">Analisar prestação de contas - '.$this->view->tituloPag.'</td></tr>';
                $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 10" colspan="8">Data do Arquivo: '. Data::mostraData() .'</td></tr>';
                $html .='<tr><td colspan="8"></td></tr>';

                $html .= '<tr>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">#</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">PRONAC</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Nome do Projeto</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Situação</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Área / Segmento</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Estado</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Mecanismo</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Recebimento</th>';
                $html .= '</tr>';

                $i=1;
                foreach ($busca as $projeto){
                    
                    $mecanismo = $projeto->Mecanismo;
                    if($mecanismo == 'Mecenato'){
                        $mecanismo = "Incentivo Fiscal";
                    }
                    $dtSituacao = Data::tratarDataZend($projeto->DtSituacao, 'brasileira');
                    
                    $html .= '<tr>';
                    $html .= '<td style="border: 1px dotted black;">'.$i.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Pronac.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->NomeProjeto.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Situacao.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Area.' / '.$projeto->Segmento.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->UfProjeto.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$mecanismo.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$dtSituacao.'</td>';
                    $html .= '</tr>';
                    $i++;
                }
                $html .= '</table>';

                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: inline; filename=Analisar_Prestacao_de_Contas.xls;");
                echo $html; die();

            } else {
                $this->view->dados = $busca;
            }
	}

	/*PLANILHA Oraçmentaria COMPROVADA*/
	public function dadosProjeto() {
		$idpronac = $this->getRequest()->getParam('idPronac');
		$projetosDAO = new Projetos ();
		$resposta    = $projetosDAO->buscar ( array ('IdPRONAC = ? ' => "{$idpronac}" ) );
		$this->view->pronac      = $resposta [0]->AnoProjeto . $resposta [0]->Sequencial;
		$this->view->nomeProjeto = $resposta [0]->NomeProjeto;
	}
	
	public function planilhaorcamentariaAction()
	{
        // pega a autenticação
        $auth = Zend_Auth::getInstance ();
        $this->view->codGrupo = $_SESSION['GrupoAtivo']['codGrupo'];

        $this->dadosProjeto ();
        $this->view->idPronac = $this->getRequest()->getParam('idPronac');
        $this->view->itemAvaliadoFilter = $this->getRequest()->getParam('itemAvaliadoFilter');

        $dao = new PlanilhaAprovacao ();
        $resposta = $dao->buscarItensPagamento(
            $this->view->idPronac,
            ($this->view->itemAvaliadoFilter ? $this->view->itemAvaliadoFilter : null)
        );

        $tblEncaminhamento = new EncaminhamentoPrestacaoContas();
        $rsEncaminhamento = $tblEncaminhamento->buscar(array('idPronac=?'=>$this->view->idPronac,'stAtivo=?'=>1))->current();

        if(is_object($rsEncaminhamento))
            $this->view->situacaoAtual = $rsEncaminhamento->idSituacaoEncPrestContas;
        else
            $this->view->situacaoAtual = 1;

        $arrayA = array();
        $arrayP = array();
        
        if (is_object($resposta)) {
            foreach ($resposta as $val) {
                $modalidade = '';
                if($val->idCotacao != '') {
                    $modalidade = 'Cota&ccedil;&atilde;o';
                    $idmod = 'cot'.$val->idCotacao.'_'.$val->idFornecedorCotacao;
                }

                if($val->idDispensaLicitacao != '') {
                    $modalidade = 'Dispensa';
                    $idmod = 'dis'.$val->idDispensaLicitacao;
                }

                if($val->idLicitacao != '') {
                    $modalidade =   'Licita&ccedil;&atilde;o';
                    $idmod = 'lic'.$val->idLicitacao;
                }

                if ($val->idContrato != '') {
                    if ($modalidade != '') {
                        $modalidade .=   ' /';
                    }
                    $modalidade .=   ' Contrato';
                    $idmod = 'con'.$val->idContrato;
                }

                if($modalidade == '') {
                    $modalidade = '-';
                    $idmod = 'sem';
                }

                if($val->tpCusto == 'A') {
                    $arrayA[($val->descEtapa)][$val->uf.' '.($val->cidade)][$val->idPlanilhaAprovacao] = array(
                        ($val->descItem),
                        $val->Total,
                        $val->tpDocumento,
                        $val->vlComprovado,
                        $modalidade,
                        $idmod,
                        $val->idPlanilhaItens,
                        $val->ComprovacaoValidada
                    );
                }

                if($val->tpCusto == 'P') {
                    $arrayP[($val->Descricao)][($val->descEtapa)][$val->uf.' '.($val->cidade)][$val->idPlanilhaAprovacao] = array(
                        ($val->descItem),
                        $val->Total,
                        $val->tpDocumento,
                        $val->vlComprovado,
                        $modalidade,
                        $idmod,
                        $val->idPlanilhaItens,
                        $val->ComprovacaoValidada
                    );
                }
            }
        }

        $this->view->incFiscaisA = array(utf8_encode('Administra&ccedil;&atilde;o do Projeto') =>$arrayA);
        $this->view->incFiscaisP = array(utf8_encode('Custo por Produto') =>$arrayP);
    }

	public function emitirparecertecnicoAction() {

		$idpronac    = $this->getRequest()->getParam('idPronac');
		$projetosDAO = new Projetos();
		$resposta    = $projetosDAO->buscar(array ('IdPRONAC = ? '=> "{$idpronac}"));
		
		$this->view->pronac      = $resposta [0]->AnoProjeto . $resposta [0]->Sequencial;
		$this->view->nomeProjeto = $resposta[0]->NomeProjeto;
		$this->view->idPronac    = $resposta[0]->IdPRONAC;

		$tblEncaminhamento = new EncaminhamentoPrestacaoContas();
		$rsEncaminhamento  = $tblEncaminhamento->buscar(array('idPronac=?'=>$idpronac,'stAtivo=?'=>1))->current();

		if(is_object($rsEncaminhamento))
		$this->view->situacaoAtual = $rsEncaminhamento->idSituacaoEncPrestContas;
		else
		$this->view->situacaoAtual = 1;

		$RelatorioTecnico = new tbRelatorioTecnico();
		$rsParecerTecnico = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>124))->current();
		$rsParecerChefe   = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>132))->current();

		if(is_object($rsParecerTecnico)){
			$this->view->parecerTecnico = $rsParecerTecnico;
			$this->view->parecerChefe   = $rsParecerChefe;
		}else{
			$this->view->parecerTecnico = array();
			$this->view->parecerChefe   = array();
		}
	}

	public function existeparecerAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$post     = Zend_Registry::get ('post');
		$idpronac = $post->idPronac;

		$RelatorioTecnico = new tbRelatorioTecnico();
		$rsParecer        = $RelatorioTecnico->buscar(array('IdPRONAC=?'=>$idpronac,'cdGrupo=?'=>$this->codGrupo))->current();

		$retorno = false;
		if(!empty ($rsParecer))
		$retorno = true;
		echo json_encode(array('retorno'=>$retorno));
	}

	public function parecertecnicoAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$post             = Zend_Registry::get ( 'post' );
		$idPronac         = $post->idPronac;
		$parecer          = $post->ParecerTecnico;
		$bln_chefedivisao = $post->parecerChefeDivisao;

		$relatorioTecnico = new tbRelatorioTecnico();
		$rsParecer        = $relatorioTecnico->buscar(array('IdPRONAC=?'=>$idPronac,'cdGrupo=?'=>$this->codGrupo))->current();

		$dados ['meRelatorio']  =   utf8_decode(trim($parecer));
		$dados ['dtRelatorio']  =   date("Y-m-d H:i:s");
		$dados ['IdPRONAC']     =   $idPronac;
		$dados ['idAgente']     =   $this->getIdAgenteLogado;
		$dados ['cdGrupo']      =   $this->codGrupo;
		$dados ['siManifestacao'] = $this->getRequest()->getParam('manifestacao');

		try{
			if(!empty ($rsParecer)){
				$where = array(
                                'IdPRONAC = ?'  =>  $idPronac,
                                'cdGrupo = ?'   =>  $this->codGrupo,
                                'idAgente = ?'  =>  $this->getIdAgenteLogado
				);
				$relatorioTecnico->update($dados,$where);
			}
			else{
				//inlcui parecer
				$relatorioTecnico->inserir($dados);
			}
			$this->_helper->flashMessenger->addMessage('Parecer salvo com sucesso!');
			$this->_helper->flashMessengerType->addMessage('CONFIRM');
			$this->_redirect("realizarprestacaodecontas/emitirparecertecnico/idPronac/{$idPronac}");
		}catch (Exception $e){
			$this->_redirect("realizarprestacaodecontas/dadosprojeto?idPronac=".$idPronac."&tipoMsg=ERROR&msg=Erro ao gravar Parecer técnico!");
			return;
		}
	}

	public function respostaconsultoriaAction() {
		
		$idEncPrestContas = $this->_request->getParam('idEncPrestContas');

		if (!empty ($idEncPrestContas)) {

			$tblEncaminhamento          = new tbEncaminhamentoPrestacaoContas();
			$rsEnc                      = $tblEncaminhamento->buscar(array('idEncPrestContas = ?'=>$idEncPrestContas,'idOrgaoDestino=?'=>$this->codOrgao,'idSituacaoEncPrestContas=?'=>1),array('dtFimEncaminhamento DESC'))->current();
			$this->view->solicitacao    =   utf8_decode(htmlentities($rsEnc->dsJustificativa));
			$idPronac                   =   $rsEnc->idPronac;

			$tblProjeto = new Projetos();
			$rsProjeto  =  $tblProjeto->find($idPronac)->current();

			$this->view->PRONAC      = $rsProjeto->AnoProjeto.$rsProjeto->Sequencial;
			$this->view->NomeProjeto = $rsProjeto->NomeProjeto;

		}
		$this->view->idOrgao          = $this->codOrgao;
		$this->view->idEncPrestContas = $idEncPrestContas;
	}

	public function gravarrespostaconsultoriaAction(){
		
		$idEncPrestContas   =   $this->_request->getParam('idEncPrestContas');
		$dsresposta         =   $this->_request->getParam('dsresposta');
		
		if (!empty($dsresposta)) {
			$tblEncaminhamento = new tbEncaminhamentoPrestacaoContas();
			$rsEnc             = $tblEncaminhamento->buscar(array('idEncPrestContas = ?'=>$idEncPrestContas,'idOrgaoDestino=?'=>$this->codOrgao,'idSituacaoEncPrestContas=?'=>1),array('dtFimEncaminhamento DESC'))->current();

			$idPronac                   = $rsEnc->idPronac;
			$idAgenteOrigem             = $rsEnc->idAgenteDestino;
			$idOrgaoOrigem              = $rsEnc->idOrgaoDestino;
			$idGrupoOrigem              = $rsEnc->cdGruposDestino;
			$idOrgaoDestino             = $rsEnc->idOrgaoOrigem;
			$idGrupoDestino             = $rsEnc->cdGruposOrigem;
			$idAgenteDestino            = $rsEnc->idAgenteOrigem;
			$dsJustificativa            = $dsresposta;
			$idSituacaoEncPrestContas   = 3;
			$idSituacao                 = $rsEnc->idSituacao;

			$tblEncaminhamento->update(array('idSituacaoEncPrestContas'=>2),array('idEncPrestContas = ?'=>$idEncPrestContas,'idOrgaoDestino=?'=>$this->codOrgao,'idSituacaoEncPrestContas=?'=>1));

			try{
				//GRUPO : ORGAO
				//100: 177 AECI
				//100: 12 CONJUR
				// monta o array de dados para cadastro
				$dados = array ('idPronac'                  =>  $idPronac,
                                'dtInicioEncaminhamento'    =>  new Zend_Db_Expr ( 'GETDATE()' ),
                                'dsJustificativa'           =>  $dsJustificativa,
                                'dtFimEncaminhamento'       =>  new Zend_Db_Expr ( 'GETDATE()' ),
                                'idSituacaoEncPrestContas'  =>  $idSituacaoEncPrestContas,
                                'idSituacao'                =>  $idSituacao,
                                'idAgenteDestino'           =>  $idAgenteDestino,
                                'idOrgaoDestino'            =>  $idOrgaoDestino,
                                'cdGruposDestino'           =>  $idGrupoDestino,
                                'idAgenteOrigem'            =>  $idAgenteOrigem,
                                'idOrgaoOrigem'             =>  $idOrgaoOrigem,
                                'cdGruposOrigem'            =>  $idGrupoOrigem
				);
				$EncaminhamentoPrestacaoContas = new EncaminhamentoPrestacaoContas();
				$cadastrar                     = $EncaminhamentoPrestacaoContas->inserir($dados);

				if($this->codOrgao == 177) {
					$this->_redirect("realizarprestacaodecontas/aeciprestacaocontas?tipoMsg=CONFIRM&msg=Consultoria enviada com sucesso!");
				}else if($this->codOrgao == 12){
					$this->_redirect("realizarprestacaodecontas/conjurprestacaocontas?tipoMsg=CONFIRM&msg=Consultoria enviada com sucesso!");
				}
				return;
			}catch (Exception $e){
				if($this->codOrgao == 177) {
					$this->_redirect("realizarprestacaodecontas/aeciprestacaocontas?tipoMsg=ERROR&msg=Erro ao enviar o Projeto.");
				}else if($this->codOrgao == 12){
					$this->_redirect("realizarprestacaodecontas/conjurprestacaocontas?tipoMsg=ERROR&msg=Erro ao enviar o Projeto.");
				}
				return;
			}
		}else{
			$this->_redirect("realizarprestacaodecontas/conjurprestacaocontas?tipoMsg=ERROR&msg=Dados obrigatórios não informados");
		}
	}

	public function cadastrarrelatoriotecnicoAction() {
		$this->_helper->layout->disableLayout ();
		
		$valido       = true;
		$licitacaoDAO = new Licitacao ();
		$post         = Zend_Registry::get ( 'post' );

		$cadastro ['meRelatorio'] = utf8_decode ( $post->ParecerTecnico );
		$cadastro ['dtRelatorio'] = data::dataAmericana ( $post->dataPublicacaoEdital );
		$cadastro ['IdPRONAC']    = $post->IdPRONAC;
		$cadastro ['idAgente']    = $post->idAgente;
	}

	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method tecnicoprestacaocontas
	 * @since 11/02/2011
	 * @version 1.0
	 * @access Tecnico Prestação de Contas
	 */
	public function tecnicoprestacaocontassAction() {

		$auth = Zend_Auth::getInstance (); 
		
		$tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas ();
		$rs                               = $tblEncaminhamentoPrestacaoContas->BuscaEncaminhamentoPrestacaoContas ( "E27", $auth->getIdentity ()->usu_orgao, "E27" );
		$this->view->TecPresContas        = $rs;
	}

	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method chefedivisaoprestacaocontas
	 * @since 18/02/2011
	 * @version 1.0
	 * @access Chefe de Divisão
	 */
	public function chefedivisaoprestacaocontasAction() {

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
                $order = array(2); //Pronac
                $ordenacao = null;
            }

            $pag = 1;
            $post  = Zend_Registry::get('get');
            if (isset($post->pag)) $pag = $post->pag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

            /* ================== PAGINACAO ======================*/
            $where = array();
            if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
                $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
                $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            }

            if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
                $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
                switch ($filtro) {
                    case 'diligenciados': //Projetos diligenciados
                        $where['p.Situacao = ?'] = 'E17';
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 132; //grupo do chefe de divisão
                        $where['e.stAtivo = ?'] = 1;
                        $where['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas
                        break;
                    default: //Aguardando Análise
                        $where['p.Situacao <> ?'] = 'E17';
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 132; //grupo do chefe de divisão
                        $where['e.stAtivo = ?'] = 1;
                        break;
                }
                
            } else { //Aguardando Análise
                $filtro = '';
                $where['p.Situacao <> ?'] = 'E17';
                $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                $where['e.cdGruposDestino = ?'] = 132; //grupo do chefe de divisão
                $where['e.stAtivo = ?'] = 1;
            }
            $this->view->filtro = $filtro;
            
            $Projetos = new Projetos();
            $total = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, null, null, true, $filtro);
            $fim = $inicio + $this->intTamPag;

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, $tamanho, $inicio, false, $filtro);

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
        
        public function imprimirChefeDivisaoPrestacaoDeContasAction()
	{
            $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
            
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
                $order = array(2); //Pronac
                $ordenacao = null;
            }

            $pag = 1;
            $post  = Zend_Registry::get('post');
            if (isset($post->pag)) $pag = $post->pag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

            /* ================== PAGINACAO ======================*/
            $where = array();
            if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
                $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
                $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            }

            if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
                $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
                switch ($filtro) {
                    case 'diligenciados': //Projetos diligenciados
                        $this->view->tituloPag = 'Projetos diligenciados';
                        $where['p.Situacao = ?'] = 'E17';
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 132; //grupo do chefe de divisão
                        $where['e.stAtivo = ?'] = 1;
                        $where['d.idTipoDiligencia = ?'] = 174; //Diligencia na Prestacao de contas
                        break;
                    default: //Aguardando Análise
                        $this->view->tituloPag = 'Aguardando Análise';
                        $where['p.Situacao <> ?'] = 'E17';
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                        $where['e.cdGruposDestino = ?'] = 132; //grupo do chefe de divisão
                        $where['e.stAtivo = ?'] = 1;
                        break;
                }
                
            } else { //Aguardando Análise
                $this->view->tituloPag = 'Aguardando Análise';
                $filtro = '';
                $where['p.Situacao <> ?'] = 'E17';
                $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2'); //Situacao Aguardando analise, e Em analise
                $where['e.cdGruposDestino = ?'] = 132; //grupo do chefe de divisão
                $where['e.stAtivo = ?'] = 1;
            }
            $this->view->filtro = $filtro;
            
            $Projetos = new Projetos();
            $total = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, null, null, true, $filtro);
            $fim = $inicio + $this->intTamPag;

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $Projetos->buscarPainelTecPrestacaoDeContas($where, $order, $tamanho, $inicio, false, $filtro);

            if(isset($post->xls) && $post->xls){
                $html = '';
                $html .= '<table style="border: 1px">';
                $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 16; font-weight: bold;" colspan="8">Analisar prestação de contas - '.$this->view->tituloPag.'</td></tr>';
                $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 10" colspan="8">Data do Arquivo: '. Data::mostraData() .'</td></tr>';
                $html .='<tr><td colspan="8"></td></tr>';

                $html .= '<tr>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">#</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">PRONAC</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Nome do Projeto</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Situação</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Área / Segmento</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Estado</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Mecanismo</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Recebimento</th>';
                $html .= '</tr>';

                $i=1;
                foreach ($busca as $projeto){
                    
                    $mecanismo = $projeto->Mecanismo;
                    if($mecanismo == 'Mecenato'){
                        $mecanismo = "Incentivo Fiscal";
                    }
                    $dtSituacao = Data::tratarDataZend($projeto->DtSituacao, 'brasileira');
                    
                    $html .= '<tr>';
                    $html .= '<td style="border: 1px dotted black;">'.$i.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Pronac.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->NomeProjeto.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Situacao.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Area.' / '.$projeto->Segmento.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->UfProjeto.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$mecanismo.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$dtSituacao.'</td>';
                    $html .= '</tr>';
                    $i++;
                }
                $html .= '</table>';

                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: inline; filename=Analisar_Prestacao_de_Contas.xls;");
                echo $html; die();

            } else {
                $this->view->dados = $busca;
            }
	}

	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method aeciprestacaocontasAction
	 * @since 11/02/2011
	 * @version 1.0
	 * @access AECI
	 */
	public function aeciprestacaocontasAction() {

		$auth = Zend_Auth::getInstance (); 
		
		$Usuario = new Usuario();

		$idagente       = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
		$idAgenteOrigem = $idagente['idAgente'];

		$tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas ();
		$rs 							  = $tblEncaminhamentoPrestacaoContas->BuscaEncaminhamentoPrestacaoContas ($this->codOrgao,"1",$idAgenteOrigem );

		$this->view->AeciPresContas = $rs;
	}

	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method conjurprestacaocontasAction
	 * @since 11/02/2011
	 * @version 1.0
	 * @access Conjur
	 */
	public function conjurprestacaocontasAction() {


		$auth = Zend_Auth::getInstance (); 
		$auth->getIdentity();
		
		$Usuario = new Usuario();

		$idagente       = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
		$idAgenteOrigem = $idagente['idAgente'];

		$tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas ();
		$rs 							  = $tblEncaminhamentoPrestacaoContas->BuscaEncaminhamentoPrestacaoContas ( $this->codOrgao, "1", $idAgenteOrigem);

		$this->view->ConjurPresContas = $rs;
	}

	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method pareceristaprestacaocontasAction
	 * @since 12/01/2011
	 * @version 1.0
	 * @access Parecerista
	 */
	public function pareceristaprestacaocontasAction() {
		
		$Usuario        = new Usuario();
		$auth           = Zend_Auth::getInstance (); // pega a autenticação
		$idagente       = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
		$idAgenteOrigem = $idagente['idAgente'];

		$tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas ();
		$rs                               = $tblEncaminhamentoPrestacaoContas->BuscaEncaminhamentoPrestacaoContas ( $this->codOrgao, "1", $idAgenteOrigem );

		$this->view->PareceristaPresContas = $rs;
	}
	
	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method coordenadorpareceristaprestacaocontasAction
	 * @since 13/02/2011
	 * @author Emerson Silva
	 * @version 1.0
	 * @access Coordenador Parecerista
	 */
	public function coordenadorpareceristaprestacaocontasAction() {

		$Usuario        = new Usuario();
		$auth           = Zend_Auth::getInstance (); // pega a autenticação
		$idagente       = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
		$idAgenteOrigem = $idagente['idAgente'];
		
		$tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas ();
		$rs = $tblEncaminhamentoPrestacaoContas->BuscaEncaminhamentoPrestacaoContas ( $this->codOrgao, "1", $idAgenteOrigem );

		$this->view->CoordParecerPresContas = $rs;
	}

        /**
         * @method analisarComprovacaoAction
         */
        public function analisarComprovacaoAction()
        {
            $idPronac = $this->_request->getParam("idPronac");
            $idPlanilhaItem = $this->_request->getParam("idPlanilhaItem");
            $idPlanilhaAprovacao = $this->_request->getParam("idPlanilhaAprovacao");

            $planilhaAprovacaoModel = new PlanilhaAprovacao();
            $this->view->projeto = $planilhaAprovacaoModel
                    ->dadosdoitem($this->_request->getParam("idPlanilhaAprovacao"), $idPronac)
                    ->current();

            if (!$this->view->projeto) {
                $this->_helper->flashMessengerType->addMessage('ALERT');
                $this->_helper->flashMessenger->addMessage('Não houve comprovação para este item.');
                $this->_redirect("realizarprestacaodecontas/planilhaorcamentaria/idPronac/{$idPronac}");
            } else {
                $this->view->tipoComprovante = $this->tipoDocumento;
                $this->view->comprovantesPagamento = $planilhaAprovacaoModel->buscarcomprovantepagamento(
                        $idPronac, $idPlanilhaAprovacao
                );
            }

            $this->view->idPronac = $idPronac;
            $this->view->idPlanilhaItem = $idPlanilhaItem;
            $this->view->idPlanilhaAprovacao = $idPlanilhaAprovacao;
        }

	/**
	 * Controller RealizarPrestacaoDeContas
	 * @method analisaritemAction
	 * @since 14/02/2011
	 * @version 1.0
	 * @access AECI
	 */
	public function analisaritemAction() {

		$idPronac            = $this->_request->getParam("idPronac");
		$idPlanilhaAprovacao = $this->_request->getParam("idPlanilhaAprovacao");
		$idPlanilhaItem = $this->_request->getParam("idPlanilhaItem");

		$tblPlanilhaAprovacao = new PlanilhaAprovacao();
		$rsPlanilha           = $tblPlanilhaAprovacao->dadosdoitem($idPlanilhaAprovacao,$idPronac)->current();

		if (!empty ($rsPlanilha->modalidadeLicitacao)) {
                    $rsPlanilha->modalidadeLicitacao = $this->modalidade[$rsPlanilha->modalidadeLicitacao];
                }
		$this->view->AnalisarItem = $rsPlanilha;

		if(count($rsPlanilha)> 0)
		{
			$planilhaAprovacaoDao             = new PlanilhaAprovacao();
			$this->view->ComprovantePagamento = $planilhaAprovacaoDao->buscarcomprovantepagamento($rsPlanilha->IdPRONAC,$idPlanilhaItem);
			
			$this->view->idPronac              = $rsPlanilha->IdPRONAC;
			$this->view->tipoDocumentoConteudo = $this->tipoDocumento;
			$this->view->idPlanilhaAprovacao   = $idPlanilhaAprovacao;
			$this->view->idPlanilhaItem        = $idPlanilhaItem;
		}else{
			$this->_redirect("realizarprestacaodecontas/planilhaorcamentaria?idPronac={$idPronac}&tipoMsg=ALERT&msg=Não houve comprovação para este item.");
		}
	}

    public function validaritemAction()
    {
        $auth = Zend_Auth::getInstance(); // pega a autenticação

        $idPronac = $this->_request->getParam("idPronac");
        $idPlanilhaItem = $this->_request->getParam("idPlanilhaItem");
        $idPlanilhaAprovacao = $this->_request->getParam("idPlanilhaAprovacao");
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector
            ->setExit(false)
            ->setGotoSimple(
                'analisar-comprovacao',
                'realizarprestacaodecontas',
                null,
                array(
                    'idPronac' => $idPronac,
                    'idPlanilhaAprovacao' => $idPlanilhaAprovacao,
                    'idPlanilhaItem' => $idPlanilhaItem,
                )
            );

        if (!$this->getRequest()->isPost()) {
            $this->_helper->flashMessenger->addMessage('Erro ao validar item.');
            $this->_helper->flashMessengerType->addMessage('ERROR');
            $redirector->redirectAndExit();
        }

        $itemValidado = false;
        $tblComprovantePag = new ComprovantePagamentoxPlanilhaAprovacao();
        $tblComprovantePag->getAdapter()->beginTransaction();
        foreach ($this->getRequest()->getParam('comprovantePagamento') as $comprovantePagamento) {
            try {
                if (!isset($comprovantePagamento['situacao'])) { continue; }
                $rsComprovantePag = $tblComprovantePag
                    ->buscar(
                        array(
                            'idComprovantePagamento=?' => $comprovantePagamento['idComprovantePagamento'],
                            'idPlanilhaAprovacao=?' => $comprovantePagamento['idPlanilhaAprovacao']
                        )
                    )
                    ->current();
                $rsComprovantePag->dtValidacao = date('Y/m/d H:i:s');
                $rsComprovantePag->dsJustificativa = isset($comprovantePagamento['observacao']) ? $comprovantePagamento['observacao'] : null;
                $rsComprovantePag->stItemAvaliado = $comprovantePagamento['situacao'];
                # validacao de valor
                $tblComprovantePag->validarValorComprovado(
                    $idPronac,
                    $idPlanilhaAprovacao,
                    $idPlanilhaItem,
                    $rsComprovantePag->vlComprovado
                );
                $rsComprovantePag->save();
                $itemValidado = true;
            } catch (Exception $e) {
                $this->_helper->flashMessenger->addMessage($e->getMessage());
                $this->_helper->flashMessengerType->addMessage('ERROR');
                $tblComprovantePag->getAdapter()->rollBack();
                $redirector->redirectAndExit();
            }
        }
        if ($itemValidado) {
            $this->_helper->flashMessenger->addMessage('Item validado com sucesso!');
            $this->_helper->flashMessengerType->addMessage('CONFIRM');
        } else {
            $this->_helper->flashMessenger->addMessage('Preencha os dados para validação de item.');
            $this->_helper->flashMessengerType->addMessage('ERROR');
        }
        $tblComprovantePag->getAdapter()->commit();
        $redirector->redirectAndExit();
    }

    public function dadosprojetoAction() {
		
		if (isset($_REQUEST['idPronac'])) {
			$dados = array();
			$dados['idPronac'] = (int) $_REQUEST['idPronac'];
			if (is_numeric($dados['idPronac'])) {
				if (isset($dados['idPronac']))
				{
					$idPronac = $dados['idPronac'];
					//UC 13 - MANTER MENSAGENS (Habilitar o menu superior)
					$this->view->idPronac = $idPronac;
					$this->view->menumsg  = 'true';
				}
				$rst = ConsultarDadosProjetoDAO::obterDadosProjeto($dados);
				if(count($rst)>0){
					$this->view->projeto  = $rst[0];
					$this->view->idpronac = $_REQUEST['idPronac'];
					//xd($rst[0]);
				}else{
					parent::message("Nenhum projeto encontrado com o n&uacute;mero de Pronac informado.", "listarprojetos/listarprojetos", "ERROR");
					return;
				}
			}else{
				parent::message("N&uacute;mero Pronac inv&aacute;lido!", "listarprojetos/listarprojetos", "ERROR");
				return;
			}
		}else{
			parent::message("N&uacute;mero Pronac inv&aacute;lido!", "listarprojetos/listarprojetos", "ERROR");
			return;
		}
	}

	public function enviarcoordenadorAction(){

		$get = Zend_Registry::get("get");
		
		$pronac   = $this->getRequest()->getParam('idPronac');
		$situacao = $this->getRequest()->getParam('situacao');

		$tblEncaminhamento = new tbEncaminhamentoPrestacaoContas();
        $rsEPC = $tblEncaminhamento->buscar(array("idPronac = ?" => $pronac, 'stAtivo=?' => 1))->current();

        $tblRelatorio = new tbRelatorioTecnico();
        $rsRelatorio  = $tblRelatorio->buscar(array('IdPRONAC = ?'=>$pronac,'idAgente=?'=>$this->getIdAgenteLogado,'cdGrupo=?'=>132));
        if($rsRelatorio->count() > 0){

		    //DESLIGA STATUS ATUAL
            $rsEPC->stAtivo = 0;
            $rsEPC->save();

			try{
				//SE O ENCAMINHAMENTO FOR DO CHEFE DE DIVISAO PARA O COORDENADOR - ALTERA SITUACAO DO PROJETO
				$tblProjeto = new Projetos();
				$tblProjeto->alterarSituacao($pronac,'','E27');
	
				//GRAVA REGISTRO FINALIZADO PELO CHEFE
				$dados = array ('idPronac'          => $pronac,
	                            'idAgenteOrigem'    => $rsEPC->idAgenteOrigem,
	                            'idAgenteDestino'   => $rsEPC->idAgenteDestino,
	                            'idOrgaoOrigem'     => $rsEPC->idOrgaoOrigem,
	                            'idOrgaoDestino'    => $rsEPC->idOrgaoDestino,
				
	                            'dtInicioEncaminhamento' => new Zend_Db_Expr ( 'GETDATE()' ),
	                            'dtFimEncaminhamento'    => new Zend_Db_Expr ( 'GETDATE()' ),
				
	                            'dsJustificativa'   => $rsEPC->dsJustificativa,
	                            'cdGruposOrigem'    => $rsEPC->cdGruposOrigem,
	                            'cdGruposDestino'   => $rsEPC->cdGruposDestino,
				
	                            'idSituacaoEncPrestContas' => 3, //projeto Finalizado
				
	                            'idSituacao'        => $rsEPC->idSituacao,
	                            'stAtivo'           => 0);
				$tblEncaminhamento->inserir($dados);
	
	
				//ENCAMINHA PROJETO PARA COORDENADOR
				$dados = array ('idPronac'          => $pronac,
	                            'idAgenteOrigem'    => $rsEPC->idAgenteOrigem,
	                            'idAgenteDestino'   => 0,
	                            'idOrgaoOrigem'     => $rsEPC->idOrgaoOrigem,
	                            'idOrgaoDestino'    => 0,
				
	                            'dtInicioEncaminhamento' => new Zend_Db_Expr ( 'GETDATE()' ),
	                            'dtFimEncaminhamento'    => new Zend_Db_Expr ( 'GETDATE()' ),
	
	                            'dsJustificativa'   => $rsEPC->dsJustificativa,
	                            'cdGruposOrigem'    => $rsEPC->cdGruposDestino,
	                            'cdGruposDestino'   => 125,
				
	                            'idSituacaoEncPrestContas' => 1,
				
	                            'idSituacao'        => 'E27',
	                            'stAtivo'           => 1);
				$tblEncaminhamento->inserir($dados);
				$this->_redirect("realizarprestacaodecontas/chefedivisaoprestacaocontas?tipoMsg=CONFIRM&msg=Finalizado com sucesso!");
				return;
			}catch (Exception $e){
				$this->_redirect("realizarprestacaodecontas/chefedivisaoprestacaocontas?tipoMsg=ERROR&msg={$e->getMessage()}");
				return;
			}
		}else{
			$this->_redirect("realizarprestacaodecontas/emitirparecertecnico?idPronac={$pronac}&tipoMsg=ALERT&msg=Para Finalizar a Análise é necessário Emitir parecer.");
		}
	}

	public function enviarchefedivisaoAction(){

		$get = Zend_Registry::get("get");
		
		$pronac   = $this->getRequest()->getParam('idPronac');
		$situacao = $this->getRequest()->getParam('situacao');
		 
		$tblEncaminhamento = new tbEncaminhamentoPrestacaoContas();
		$rsEPC             = $tblEncaminhamento->buscar(array("idPronac = ?"=>$pronac, 'stAtivo=?'=>1))->current();

		$tblRelatorio = new tbRelatorioTecnico();
		$rsRelatorio  = $tblRelatorio->buscar(array('IdPRONAC = ?'=>$pronac,'idAgente=?'=>$this->getIdAgenteLogado,'cdGrupo=?'=>124));

		if($rsRelatorio->count() > 0){
			//DESLIGA STATUS ATUAL
			$rsEPC->stAtivo = 0;
			$rsEPC->save();

			try{
				//GRUPO : ORGAO
				//100: 177 AECI
				//100: 12 CONJUR
				//GRAVA REGISTRO FINALIZADO PELO TECNICO
				$dados = array ('idPronac'          => $pronac,
                                'idAgenteOrigem'    => $rsEPC->idAgenteOrigem,
                                'idAgenteDestino'   => $rsEPC->idAgenteDestino,
                                'idOrgaoOrigem'     => $rsEPC->idOrgaoOrigem,
                                'idOrgaoDestino'    => $rsEPC->idOrgaoDestino,
				
                                'dtInicioEncaminhamento' => new Zend_Db_Expr ( 'GETDATE()' ),
                                'dtFimEncaminhamento'    => new Zend_Db_Expr ( 'GETDATE()' ),
				
                                'dsJustificativa'   => $rsEPC->dsJustificativa,
                                'cdGruposOrigem'    => $rsEPC->cdGruposOrigem,
                                'cdGruposDestino'   => $rsEPC->cdGruposDestino,
				
                                'idSituacaoEncPrestContas' => 3, //projeto Finalizado
				
                                'idSituacao'        => $rsEPC->idSituacao,
                                'stAtivo'           => 0);
				$tblEncaminhamento->inserir($dados);


				//ENCAMINHA PROJETO PARA CHEFE DIVISAO
				$dados = array ('idPronac'          => $pronac,
                                'idAgenteOrigem'    => $rsEPC->idAgenteOrigem,
                                'idAgenteDestino'   => 0,
                                'idOrgaoOrigem'     => $rsEPC->idOrgaoOrigem,
                                'idOrgaoDestino'    => 0,
				
                                'dtInicioEncaminhamento' => new Zend_Db_Expr ( 'GETDATE()' ),
                                'dtFimEncaminhamento'    => new Zend_Db_Expr ( 'GETDATE()' ),
				
                                'dsJustificativa'   => $rsEPC->dsJustificativa,
                                'cdGruposOrigem'    => $rsEPC->cdGruposDestino,
                                'cdGruposDestino'   => 132,
				
                                'idSituacaoEncPrestContas' => 1,

                                'idSituacao'        => $rsEPC->idSituacao,
                                'stAtivo'           => 1);
				$tblEncaminhamento->inserir($dados);

				$this->_redirect("realizarprestacaodecontas/tecnicoprestacaocontas?tipoMsg=CONFIRM&msg=Finalizado com sucesso!");
				return;
			}catch (Exception $e){
				$this->_redirect("realizarprestacaodecontas/tecnicoprestacaocontas?tipoMsg=CONFIRM&msg=Finalizado com sucesso!");
				return;
			}
		}else{
			$this->_redirect("realizarprestacaodecontas/emitirparecertecnico?idPronac={$pronac}&tipoMsg=ALERT&msg=Para Finalizar a Análise é necessário Emitir parecer.");
		}
	}

	public function alterarstatusprojetoAction(){
		
		$get = Zend_Registry::get("get");
		 
		$auth = Zend_Auth::getInstance (); // pega a autenticação
		
		$pronac   = $get->idPronac;
		$situacao = $get->situacao;
		 
		$tblEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas();
		$rsEPC = $tblEncaminhamentoPrestacaoContas->buscar(array("idPronac = ?"=>$pronac, 'stAtivo=?'=>1))->current();
		if(count($rsEPC)>0){
			//DESLIGA STATUS ATUAL
			$rsEPC->stAtivo = 0;
			$rsEPC->save();

			//GRAVA REGISTRO COM NOVO STATUS
			$dados = array ('idPronac'          => $pronac,
                            'idAgenteOrigem'    => $rsEPC->idAgenteOrigem,
                            'idAgenteDestino'   => $rsEPC->idAgenteDestino,
                            'idOrgaoOrigem'     => $rsEPC->idOrgaoOrigem,
                            'idOrgaoDestino'    => $rsEPC->idOrgaoDestino,
			
                            'dtInicioEncaminhamento' => new Zend_Db_Expr ( 'GETDATE()' ),
                            'dtFimEncaminhamento'    => new Zend_Db_Expr ( 'GETDATE()' ),
			
                            'dsJustificativa'   => $rsEPC->dsJustificativa,
                            'cdGruposOrigem'    => $rsEPC->cdGruposOrigem,
                            'cdGruposDestino'   => $rsEPC->cdGruposDestino,
			
                            'idSituacaoEncPrestContas' => 2, //projeto Em Analise
			
                            'idSituacao'        => $rsEPC->idSituacao);

			if($tblEncaminhamentoPrestacaoContas->inserir($dados)){
				$this->_redirect("realizarprestacaodecontas/planilhaorcamentaria?idPronac={$pronac}&tipoMsg=CONFIRM&msg=Projeto em análise!");
			}else{
				$this->_redirect("realizarprestacaodecontas/planilhaorcamentaria?idPronac={$pronac}&tipoMsg=ERROR&msg=Falha ao alterar status do Projeto!");
			}
		}else{
			$this->_redirect("realizarprestacaodecontas/planilhaorcamentaria?idPronac={$pronac}&tipoMsg=ALERT&msg=PRONAC inexistente!");
		}
	}

	public function recuperardataultimasituacaoAction() {
		
		$this->_helper->layout->disableLayout (); // desabilita o Zend_Layout
		$this->_helper->viewRenderer->setNoRender(true);

		$NrPronac = $this->_request->getParam("NrPronac");
		
		if(!empty($NrPronac)){
			$tblHistoricoSituacao = new HistoricoSituacao();
			$rsHitorico           = $tblHistoricoSituacao->buscarSituacaoAnterior($NrPronac);
			if(count($rsHitorico)>0){
				$data = date('d/m/Y',strtotime($rsHitorico->DtSituacao));
				$dias = data::CompararDatas($rsHitorico->DtSituacao);
				$dias = (round($dias));
			}else{
				$data = "00/00/0000";
				$dias = "0";
			}
			echo json_encode(array('dataImpressao'=>$data,'dias'=>$dias));
			return;
		}else{
			$data = "00/00/0000";
			$dias = "0";
			echo json_encode(array('dataImpressao'=>$data,'dias'=>$dias));
			return;
		}
	}

    public function gerarpdfAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $isAprovado = $this->getRequest()->getParam('IN');
        $folder = '';
        if ('aprovado' == $isAprovado) {
            $folder = 'aprovado';
        } elseif ('reprovado' == $isAprovado) {
            $folder = 'reprovado';
        }
        if (empty($folder)) {
            parent::message(
                    'Informe se a prestação de contas foi Aprovada ou Reprovada',
                    '/realizarprestacaodecontas/laudofinal/idPronac/' . $this->getRequest()->getParam('idPronac'),
                    'ERROR'
                    );
            return;
        }
        $partialPath = "realizarprestacaodecontas/partial/laudo-final/";
        $partials = array(
            $this->view->partial("{$partialPath}/{$folder}/laudo.phtml"),
            $this->view->partial("{$partialPath}/{$folder}/comunicado.phtml"),
        );

        if ($this->getRequest()->getParam('pt')) {
            $partials[] = $this->view->partial("{$partialPath}/parecer-tecnico.phtml");
        }

        $html = implode('<div style="page-break-before: always;">', $partials);
        $html .= '<script language="javascript" type="text/javascript" src="/minc/salic/public/js/jquery-1.4.2.min.js"></script><script type="text/javascript">$(document).ready(function(){window.print();});</script>';

        if ($this->getRequest()->getParam('pch')) {
            $html .= $this->view->partial("{$partialPath}/parecer-chefe-de-divisao.phtml");
        }

        foreach ($this->getRequest()->getPost() as $key => $value) {
            $html = str_replace("{{$key}}", $value, $html);
        }

        echo $html;
    }

        /*Buscar Situação PC*/
	public function buscarsituacaoAction()
	{
	
	
	}
	
	/*Fim Situação PC*/
	public function imprimirguiaarquivoAction()
	{
		//** Usuario Logado ************************************************/
		$auth = Zend_Auth::getInstance(); // pega a autenticação

		/******************************************************************/
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$get      = Zend_Registry::get('get');
		$idpronac = $get->idPronac;

		$htmlDinamico = '';
		$data         = date('d/m/Y H:i:s');
		//buscaProjeto
		$docs         = TramitarprojetosDAO::buscaProjetoPDF($idpronac);
		//xd($docs);
		foreach ($docs as $d):

		//$idDocumento = $d->idDocumento;
		$Processo      = Mascara::addMaskProcesso($d->Processo);
		$Orgao         = $d->Sigla;
		$OrgaoOrigem   = $d->OrgaoOrigem;
		$NomeProjeto   = $d->NomeProjeto;
		$Pronac        = $d->pronacp;
		//$dsTipoDocumento = $d->dsTipoDocumento;

		$htmlDinamico .="<tr>
							<td align='left'>".$Processo."</td>
							<td align='left'>".$Pronac."</td>
							<td align='left'>".$NomeProjeto."</td>
							<td align='left'>".$data."</td>
						</tr>";
		endforeach;
		$html = "<html><head></head>
					     <body>
						 <br /><br />
						 <center>
						 <img src='./public/img/brasaoArmas.jpg'/>
						 </center>
						 <center>Guia de Arquivamento de projetos</center>
						 <br /><br />
						 <center>
							<table cellspacing='0' cellpadding='2' border='1' align='center' width='99%'>
								<tr align='center'>
									<td colspan='4'>
									<h2>MINISTÉRIO DA CULTURA</h2>
									<h3>Guia de Arquivamento de projetos - Enviado</h3></td>
								</tr>
								<tr>
									<td colspan='4' align='left'><b>Origem : ".$Orgao."</b></td>
								</tr>
								<tr>
									<td colspan='4' align='left'><b>Destino :DGI/CGRL/COAL/DCA</b></td>
								</tr>
								<tr>
									<td colspan='4' align='left'><b>Emissor :".$auth->getIdentity()->usu_nome."</b></td>
								</tr>
								<tr>
									<th align='left'>Processo</th>
									<th align='left'>PRONAC</th>
									<th align='left'>Nome do Projeto</th>
									<th align='left'>Dt.Envio</th>
								</tr>";

		$html .= $htmlDinamico;

		$html .="
                                                <tr>
                                                    <td colspan='4'>
                                                    Recebi os documentos acima relacionados <br>
                                                    Em ___/____/______ as ______:______ horas
                                                    </td>
                                                </tr>
							</table>
						</center>
					     </body></html>";

		$pdf = new PDF($html, 'pdf', 'Guia_Prestacao');
		$pdf->gerarRelatorio();
	}

        public function relatorioFinalAction()
        {
            $idPronac = $this->_request->getParam("idPronac");
            if(!empty($idPronac))
            {
                $this->view->projeto = array();
                $this->view->relatorio = array();
                $this->view->relatorioConsolidado = array();
                $this->view->beneficiario = array();
                $this->view->movel = array();
                $this->view->guiaFNC = array();
                $this->view->comprovantesExecucao = array();
                $this->view->imovel = array();
                $this->view->idAcessoA = array();
                $this->view->idAcessoB = array();
                $this->view->idRelatorioConsolidado = array();
                $this->view->acessibilidade = array();
                $this->view->democratizacao = array();
                $this->view->RelatorioConsolidado = array();

                $tblProjeto = new Projetos();
                $rsProjeto = $tblProjeto->buscar(array("idPronac = ?"=>$idPronac))->current();
                $this->view->projeto = $rsProjeto;

                if(count($rsProjeto) > 0) {
                    $tblRelatorio = new tbRelatorio();
                    $rsRelatorio = $tblRelatorio->buscar(array("idPRONAC = ?"=>$idPronac,"tpRelatorio = ?"=>'C',"idAgenteAvaliador > ?"=>0))->current();
                    $this->view->relatorio = $rsRelatorio;
                }

                $rsRelatorioConsolidado = array();
                if(isset($rsRelatorio) && count($rsRelatorio) > 0) {
                    $tblRelatorioConsolidado = new tbRelatorioConsolidado();
                    $rsRelatorioConsolidado = $tblRelatorioConsolidado->consultarDados(array("idRelatorio = ?"=>$rsRelatorio->idRelatorio))->current();
                    $this->view->relatorioConsolidado = $rsRelatorioConsolidado;

                    $tblBeneficiario = new tbBeneficiario();
                    $rsBeneficiario = $tblBeneficiario->buscar(array("idRelatorio = ?"=>$rsRelatorio->idRelatorio))->current();
                    $this->view->beneficiario = $rsBeneficiario;

                    if(isset($rsRelatorio->idDistribuicaoProduto) && $rsRelatorio->idDistribuicaoProduto) {
                        $tblDistribuicaoProduto = new tbDistribuicaoProduto();
                        $rsDistribuicaoProduto = $tblDistribuicaoProduto->buscarDistribuicaoProduto($rsRelatorio->idDistribuicaoProduto);
                        $this->view->movel = $rsDistribuicaoProduto;
                    }

                    if(!empty($rsDistribuicaoProduto->current()->idDocumento)) {
                        $tblDocumento = new tbDocumento();
                        $rsDocumento = $tblDocumento->buscardocumentosrelatorio($rsDistribuicaoProduto->current()->idDocumento);
                        $this->view->guiaFNC = $rsDocumento;
                    }

                    //Recuperando dados de tbComprovanteExecucao
                    $tblTbComprovanteExecucao = new tbComprovanteExecucao();
                    $rsTbComprovanteExecucao = $tblTbComprovanteExecucao->buscarDocumentosPronac6($rsRelatorio->idPRONAC, "C");
                    $this->view->comprovantesExecucao = $rsTbComprovanteExecucao;
                }

                if(isset($rsRelatorioConsolidado) && count($rsRelatorioConsolidado) > 0) {
                    $tblImovel = new tbImovel();
                    $rsImovel = $tblImovel->buscar(array("idImovel = ?"=>$rsRelatorioConsolidado->idImovel))->current();
                    $this->view->imovel = $rsImovel;
                }

                if(isset($rsImovel) && count($rsImovel) > 0) {
                   $tblDocumento = new tbDocumento();
                   $rsDocumentoImovel = $tblDocumento->buscardocumentosrelatorio($rsImovel['idDocumento']);
                   $this->view->ComprovanteCotacao = $rsDocumentoImovel;
                }

                $tblAcesso = new Acesso();
                $rsAcesso = $tblAcesso->consultarAcessoPronac($idPronac, 1);  // Acessibilidade
                if(isset($rsAcesso[0]->idAcesso)){
                    $this->view->idAcessoA = $rsAcesso[0]->idAcesso;
                    $rsAcesso2 = $tblAcesso->consultarAcessoPronac($idPronac, 2);  // Democratizaï¿½?o
                    $this->view->idAcessoB = $rsAcesso2[0]->idAcesso;
                }

                if (isset($rsAcesso2) && count($rsAcesso2) > 0) {
                    $tbRelConsolidado = new tbRelatorioConsolidado();
                    $rsRel = $tbRelConsolidado->consultarDados2($rsAcesso2[0]->idRelatorioConsolidado);
                    if( is_object($rsRel) )
                        $this->view->idRelatorioConsolidado = $rsRel[0]->idRelatorioConsolidado;

                    $this->view->acessibilidade = $rsAcesso->current();
                    $this->view->democratizacao = $rsAcesso2->current();
                    $this->view->RelatorioConsolidado = $rsRel->current();
                }
            }
        }
        
        public function painelAction(){
            
            if(isset($_GET['msg']) && $_GET['msg'] == 'sucessoLaudoFinal'){
                parent::message('Laudo final da prestação de contas emitido com sucesso!', "realizarprestacaodecontas/painel?pag=1&tipoFiltro=devolvidos", 'CONFIRM');
            }
            
            $tblSituacao = new Situacao();
            $rsSitucao = $tblSituacao->buscar(array("Codigo IN (?)"=>array('E17','E20','E22','E27','E30','E68','E77','L05','L06')));
            $this->view->situacoes = $rsSitucao;
            $this->intTamPag = 10;
            
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
                if($campo == 6){
                    $order = array("6 ".$ordem,"7 ".$ordem);
                }
                $ordenacao = "&campo=".$campo."&ordem=".$ordem;
                
            } else {
                $campo = null;
                $order = array(2); //Pronac
                $ordenacao = null;
            }

            $pag = 1;
            $post  = Zend_Registry::get('get');
            if (isset($post->pag)) $pag = $post->pag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

            /* ================== PAGINACAO ======================*/
            $where = array();
            if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
                $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
                $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            }

            if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
                $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
                $this->view->filtro = $filtro;
                switch ($filtro) {
                    case 'emanalise': //Em análise
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E27');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1');
                        $where['e.cdGruposDestino in (?)'] = array('124');
                        $where['e.stAtivo = ?'] = 1;
                        break;
                    case 'devolvidos': //Devolvidos após análise
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E27');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
                        $where['e.cdGruposDestino in (?)'] = array('125','126');
                        $where['e.cdGruposOrigem = ?'] = 132;
                        $where['e.stAtivo = ?'] = 1;
                        $where['NOT EXISTS(SELECT * FROM SAC.dbo.tbRelatorioTecnico tbrt WHERE tbrt.IdPRONAC = p.IdPRONAC AND tbrt.cdGrupo IN (125,126))'] = '';
                        break;
                    case 'diligenciados': //Projetos diligenciados
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E17', 'E20', 'E30');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
                        $where['e.cdGruposDestino in (?)'] = array('125','126');
                        $where['e.cdGruposOrigem = ?'] = 132;
                        $where['e.stAtivo = ?'] = 1;
                        $where['d.DtSolicitacao = (SELECT top 1 d2.DtSolicitacao FROM SAC.dbo.tbDiligencia d2 WHERE d2.idPronac = d.idPronac ORDER BY d2.DtSolicitacao DESC)'] = '';
                        $where['d.idTipoDiligencia = ?'] = 174;
                        break;
                    case 'tce': //Projetos em TCE
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E22', 'L05', 'L06');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
                        $where['e.cdGruposDestino in (?)'] = array('125','126');
                        $where['e.cdGruposOrigem = ?'] = 132;
                        $where['e.stAtivo = ?'] = 1;
                        break;
                    default: //Aguardando Análise
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E68', 'E77');
                        break;
                }
                
            } else { //Aguardando Análise
                $filtro = '';
                $where['p.Orgao = ?'] = $this->codOrgao;
                $where['p.Situacao in (?)'] = array('E68', 'E77');
            }
            
            if((isset($_POST['situacao']) && !empty($_POST['situacao'])) || (isset($_GET['situacao']) && !empty($_GET['situacao']))){
                $where["p.Situacao in (?)"] = isset($_POST['situacao']) ? $_POST['situacao'] : $_GET['situacao'];
                $this->view->situacao = isset($_POST['situacao']) ? $_POST['situacao'] : $_GET['situacao'];
            }
            
            $Projetos = new Projetos();
            $total = $Projetos->buscarPainelPrestacaoDeContas($where, $order, null, null, true, $filtro);
            $fim = $inicio + $this->intTamPag;

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $Projetos->buscarPainelPrestacaoDeContas($where, $order, $tamanho, $inicio, false, $filtro);

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
        
        public function imprimirPainelAction()
	{
            $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
            $this->intTamPag = 10;
            
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
                $order = array(2); //Pronac
                $ordenacao = null;
            }

            $pag = 1;
            $post  = Zend_Registry::get('post');
            if (isset($post->pag)) $pag = $post->pag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

            /* ================== PAGINACAO ======================*/

            $where = array();

            if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
                $where["p.AnoProjeto+p.Sequencial = ?"] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
                $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            }

            if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
                $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
                $this->view->filtro = $filtro;
                switch ($filtro) {
                    case 'emanalise': //Em análise
                        $this->view->tituloPag = 'Em análise';
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E27');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1');
                        $where['e.cdGruposDestino in (?)'] = array('124');
                        $where['e.cdGruposOrigem = ?'] = 125;
                        $where['e.stAtivo = ?'] = 1;
                        break;
                    case 'devolvidos': //Devolvidos após análise
                        $this->view->tituloPag = 'Devolvidos após análise';
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E27');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
                        $where['e.cdGruposDestino in (?)'] = array('125','126');
                        $where['e.cdGruposOrigem = ?'] = 132;
                        $where['e.stAtivo = ?'] = 1;
                        break;
                    case 'diligenciados': //Projetos diligenciados
                        $this->view->tituloPag = 'Projetos diligenciados';
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E17', 'E20', 'E30');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
                        $where['e.cdGruposDestino in (?)'] = array('125','126');
                        $where['e.cdGruposOrigem = ?'] = 132;
                        $where['e.stAtivo = ?'] = 1;
                        $where['d.DtSolicitacao = (SELECT top 1 d2.DtSolicitacao FROM SAC.dbo.tbDiligencia d2 WHERE d2.idPronac = d.idPronac ORDER BY d2.DtSolicitacao DESC)'] = '';
                        $where['d.idTipoDiligencia = ?'] = 174;
                        break;
                    case 'tce': //Projetos em TCE
                        $this->view->tituloPag = 'Projetos em TCE';
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E22', 'L05', 'L06');
                        $where['e.idSituacaoEncPrestContas in (?)'] = array('1','2');
                        $where['e.cdGruposDestino in (?)'] = array('125','126');
                        $where['e.cdGruposOrigem = ?'] = 132;
                        $where['e.stAtivo = ?'] = 1;
                        break;
                    default: //Aguardando Análise
                        $this->view->tituloPag = 'Aguardando Análise';
                        $where['p.Orgao = ?'] = $this->codOrgao;
                        $where['p.Situacao in (?)'] = array('E68', 'E77');
                        break;
                }
                
            } else { //Aguardando Análise
                $this->view->tituloPag = 'Aguardando Análise';
                $filtro = '';
                $where['p.Orgao = ?'] = $this->codOrgao;
                $where['p.Situacao in (?)'] = array('E68', 'E77');
            }
            
            if((isset($_POST['situacao']) && !empty($_POST['situacao'])) || (isset($_GET['situacao']) && !empty($_GET['situacao']))){
                $where["p.Situacao in (?)"] = isset($_POST['situacao']) ? $_POST['situacao'] : $_GET['situacao'];
                $this->view->situacao = isset($_POST['situacao']) ? $_POST['situacao'] : $_GET['situacao'];
            }
            
            $Projetos = new Projetos();
            $total = $Projetos->buscarPainelPrestacaoDeContas($where, $order, null, null, true, $filtro);
            $fim = $inicio + $this->intTamPag;

            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
            $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
            $busca = $Projetos->buscarPainelPrestacaoDeContas($where, $order, $tamanho, $inicio, false, $filtro);
            
            if(isset($post->xls) && $post->xls){
                if(!isset($filtro) || (isset($filtro) && $filtro != 'devolvidos')){
                    $colspan = 8;
                } else {
                    $colspan = 9;
                }
                
                if(isset($filtro) && $filtro == 'emanalise'){
                    $colspan = 9;
                }
                
                $html = '';
                $html .= '<table style="border: 1px">';
                $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 16; font-weight: bold;" colspan="'.$colspan.'">Analisar prestação de contas - '.$this->view->tituloPag.'</td></tr>';
                $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 10" colspan="'.$colspan.'">Data do Arquivo: '. Data::mostraData() .'</td></tr>';
                $html .='<tr><td colspan="'.$colspan.'"></td></tr>';

                if(!isset($filtro) || (isset($filtro) && $filtro != 'devolvidos')){
                    if(isset($filtro) && $filtro == 'emanalise'){
                        $addLinha = '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Encaminhamento</th>';
                    } else {
                        $addLinha = '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Recebimento</th>';
                    }
                } else {
                    $addLinha = '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Início</th>
                                 <th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Fim</th>';
                }
                
                $addTec = '';
                if(isset($filtro) && $filtro == 'emanalise'){
                    $addTec = '<th style="border: 1px dotted black; background-color: #9BBB59;">Técnico</th>';
                }
                
                $html .= '<tr>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">#</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">PRONAC</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Nome do Projeto</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Situação</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Área / Segmento</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Estado</th>';
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Mecanismo</th>';
                $html .= $addLinha;
                $html .= $addTec;
                $html .= '</tr>';

                $i=1;
                foreach ($busca as $projeto){
                    
                    $mecanismo = $projeto->Mecanismo;
                    if($mecanismo == 'Mecenato'){
                        $mecanismo = "Incentivo Fiscal";
                    }
                    
                    if(!isset($filtro) || (isset($filtro) && $filtro != 'devolvidos')){
                        if(isset($filtro) && $filtro == 'emanalise'){
                            $dt = Data::tratarDataZend($projeto->dtInicioEncaminhamento, 'brasileira');
                        } else {
                            $dt = Data::tratarDataZend($projeto->DtSituacao, 'brasileira');
                        }
                        $addValores = '<td style="border: 1px dotted black;">'.$dt.'</td>';
                    } else {
                        $dtInicioEncaminhamento = Data::tratarDataZend($projeto->dtInicioEncaminhamento, 'brasileira');
                        $dtFimEncaminhamento = Data::tratarDataZend($projeto->dtFimEncaminhamento, 'brasileira');
                        $addValores = '<td style="border: 1px dotted black;">'.$dtInicioEncaminhamento.'</td>
                                       <td style="border: 1px dotted black;">'.$dtFimEncaminhamento.'</td>';
                    }
                    
                    $addValTec = '';
                    if(isset($filtro) && $filtro == 'emanalise'){
                        $addValTec = '<td style="border: 1px dotted black;">'.$projeto->nmAgente.'</td>';
                    }
                    
                    $html .= '<tr>';
                    $html .= '<td style="border: 1px dotted black;">'.$i.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Pronac.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->NomeProjeto.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Situacao.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->Area.' / '.$projeto->Segmento.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$projeto->UfProjeto.'</td>';
                    $html .= '<td style="border: 1px dotted black;">'.$mecanismo.'</td>';
                    $html .= $addValores;
                    $html .= $addValTec;
                    $html .= '</tr>';
                    $i++;
                }
                $html .= '</table>';

                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: inline; filename=Painel_Analisar_Prestacao_de_Contas.xls;");
                echo $html; die();

            } else {
                $this->view->dados = $busca;
            }
	}
        
        public function cancelamentoDoEncaminhamentoAction(){
            $get  = Zend_Registry::get('get');
            
            try {
                $tbEncaminhamentoPrestacaoContas = new tbEncaminhamentoPrestacaoContas();
                $busca = $tbEncaminhamentoPrestacaoContas->buscar(array('idPronac = ?' => $get->idPronac, 'idEncPrestContas = ?' => $get->enc))->current();
                $busca->delete();

                $tblProjeto = new Projetos();
                $tblProjeto->alterarSituacao($get->idPronac, '', 'E68', 'Prestação de Contas apresentada - Aguardando Análise');
                parent::message('Projeto devolvido com sucesso!', "realizarprestacaodecontas/painel?tipoFiltro=emanalise", 'CONFIRM');
                
            } catch (Exception $e) {
                parent::message('Erro ao devolver o projeto!', "realizarprestacaodecontas/painel?tipoFiltro=emanalise", 'ERROR');
                return;
            }
        }
        
        
    public function manterAssinantesAction(){

        $this->intTamPag = 10;

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
            $order = array(2); //Nome do Assinante
            $ordenacao = null;
        }

        $pag = 1;
        $post  = Zend_Registry::get('post');
        if (isset($post->pag)) $pag = $post->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();

        $filtro = '';
        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            switch ($filtro) {
                case 'coordIncFiscTec': //Coordenador (a) de Incentivos Fiscais e Apoio Técnico
                    $where['a.tpCargo = ?'] = 1;
                    break;
                case 'coordGeral': //Coordenador (a) Geral de Prestação de Contas
                    $where['a.tpCargo = ?'] = 2;
                    break;
                case 'diretorExecutivo': //Diretor (a) Executivo de Incentivo à Cultura
                    $where['a.tpCargo = ?'] = 3;
                    break;
                case 'secretarioFomento': //Secretário (a) de Fomento e Incentivo à Cultura
                    $where['a.tpCargo = ?'] = 4;
                    break;
                default: //Todos os cargos
                    break;
            }
        }
        $this->view->filtro = $filtro;

        $tbAssinantesPrestacao = new tbAssinantesPrestacao();
        $total = $tbAssinantesPrestacao->buscarAssinantesPrestacaoDeContas($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $tbAssinantesPrestacao->buscarAssinantesPrestacaoDeContas($where, $order, $tamanho, $inicio, false);

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
    
    public function imprimirManterAssinantesAction(){

        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
        $this->intTamPag = 10;

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
            $order = array(2); //Nome do Assinante
            $ordenacao = null;
        }

        $pag = 1;
        $post  = Zend_Registry::get('post');
        if (isset($post->pag)) $pag = $post->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();

        $filtro = '';
        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $filtro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            switch ($filtro) {
                case 'coordIncFiscTec': //Coordenador (a) de Incentivos Fiscais e Apoio Técnico
                    $where['a.tpCargo = ?'] = 1;
                    break;
                case 'coordGeral': //Coordenador (a) Geral de Prestação de Contas
                    $where['a.tpCargo = ?'] = 2;
                    break;
                case 'diretorExecutivo': //Diretor (a) Executivo de Incentivo à Cultura
                    $where['a.tpCargo = ?'] = 3;
                    break;
                case 'secretarioFomento': //Secretário (a) de Fomento e Incentivo à Cultura
                    $where['a.tpCargo = ?'] = 4;
                    break;
                default: //Todos os cargos
                    break;
            }
        }
        $this->view->filtro = $filtro;

        $tbAssinantesPrestacao = new tbAssinantesPrestacao();
        $total = $tbAssinantesPrestacao->buscarAssinantesPrestacaoDeContas($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $tbAssinantesPrestacao->buscarAssinantesPrestacaoDeContas($where, $order, $tamanho, $inicio, false);

        if(isset($post->xls) && $post->xls){
            $html = '';
            $html .= '<table style="border: 1px">';
            $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 16; font-weight: bold;" colspan="5">Manter Assinantes</td></tr>';
            $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 10" colspan="5">Data do Arquivo: '. Data::mostraData() .'</td></tr>';
            $html .='<tr><td colspan="5"></td></tr>';

            $html .= '<tr>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">#</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Nome do Assinante</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Tipo do Cargo</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Cadastro</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Situação</th>';
            $html .= '</tr>';

            $i=1;
            foreach ($busca as $d){
                
                switch ($d->tpCargo) {
                    case '1':
                        $tpCargo = 'Coordenador (a) de Incentivos Fiscais e Apoio Técnico';
                        break;
                    case '2':
                        $tpCargo = 'Coordenador (a) Geral de Prestação de Contas';
                        break;
                    case '3':
                        $tpCargo = 'Diretor (a) Executivo de Incentivo à Cultura';
                        break;
                    case '4':
                        $tpCargo = 'Secretário (a) de Fomento e Incentivo à Cultura';
                        break;
                    default:
                        $tpCargo = ' - ';
                        break;
                }
                
                $dtCadastro = Data::tratarDataZend($d->dtCadastro, 'brasileira');
                $stAtivo = 'Ativo';
                if($d->stAtivo == 0){
                    $stAtivo = 'Inativo';
                }
                
                $html .= '<tr>';
                $html .= '<td style="border: 1px dotted black;">'.$i.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$d->nmAssinante.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$tpCargo.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$dtCadastro.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$stAtivo.'</td>';
                $html .= '</tr>';
                $i++;
            }
            $html .= '</table>';

            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=Manter_Assinantes.xls;");
            echo $html; die();

        } else {
            $this->view->dados = $busca;
        }
    }
    
    public function incluirAssinantesPrestacaoAction(){
        $post = Zend_Registry::get('post');
        $tbAssinantesPrestacao = new tbAssinantesPrestacao();
        
        $auth = Zend_Auth::getInstance();
        $this->usu_codigo = $auth->getIdentity()->usu_codigo;
            
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        try {
            $db->beginTransaction();
            $dados = array(
                'nmAssinante'=> $post->nmAssinante,
                'tpCargo'=> $post->tpCargo,
                'dtCadastro'=> new Zend_Db_Expr('GETDATE()'),
                'idUsuario'=> $this->usu_codigo,
                'stAtivo'=> 1
            );
            $tbAssinantesPrestacao->inserir($dados);
            $db->commit();
            parent::message("Assinante cadastrado com sucesso!", "realizarprestacaodecontas/manter-assinantes", "CONFIRM");
        }
        catch(Zend_Exception $e) {
            $db->rollBack();
            parent::message("Erro ao realizar cadastro do asssinante.", "realizarprestacaodecontas/manter-assinantes", "ERROR");
        }
    }
    
    public function editarAssinantesPrestacaoAction(){
        $post = Zend_Registry::get('post');
        $tbAssinantesPrestacao = new tbAssinantesPrestacao();
        
        $auth = Zend_Auth::getInstance();
        $this->usu_codigo = $auth->getIdentity()->usu_codigo;
            
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        try {
            $db->beginTransaction();
            $dados = $tbAssinantesPrestacao->buscar(array("idAssinantesPrestacao = ?" => $post->idAssinante))->current();
            $dados->nmAssinante = $post->nmAssinante;
            $dados->tpCargo = $post->tpCargo;
            $dados->idUsuario = $this->usu_codigo;
            $dados->stAtivo = $post->stAtivo;
            $dados->save();
            
            $db->commit();
            parent::message("Assinante alterado com sucesso!", "realizarprestacaodecontas/manter-assinantes", "CONFIRM");
        }
        catch(Zend_Exception $e) {
            $db->rollBack();
            parent::message("Erro ao tentar atualizar os dados do asssinante.", "realizarprestacaodecontas/manter-assinantes", "ERROR");
        }
    }
    
}  //fecha class
