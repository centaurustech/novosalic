<?php 
class AvaliaracompanhamentoprojetoController extends GenericControllerNew {

    private $intTamPag = 10;
    private $getIdOrgao = 0;

    public function init() {

        $this->view->title = "Salic - Sistema de Apoio às Leis de Incentivo à Cultura";
        $auth = Zend_Auth::getInstance();
        $Usuario = new UsuarioDAO();
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');

        if ($auth->hasIdentity()) // caso o usuário esteja autenticado
        {
            // verifica as permissões
            $PermissoesGrupo = array();
            $PermissoesGrupo[] = 122;
            $PermissoesGrupo[] = 121;
            $PermissoesGrupo[] = 129;
            $PermissoesGrupo[] = 94;  // Parecerista
            $PermissoesGrupo[] = 135; // tecnico
            $PermissoesGrupo[] = 134; // coordenador
            $PermissoesGrupo[] = 138; // tecnico de avaliação
            $PermissoesGrupo[] = 139; // coordenador de avaliação
            $PermissoesGrupo[] = 124; //Tecnico de Prestação de Contas
            $PermissoesGrupo[] = 132; // Chefe de Divisão

            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) // verifica se o grupo ativo está no array de permissões
            {
                parent::message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, órgãos e grupos do usuário (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades(isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario, 21);

            // manda os dados para a visão
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
            $this->orgSup = $GrupoAtivo->codOrgao;
            $this->usu_orgao = $GrupoAtivo->codOrgao;
            
            $this->getIdOrgao = $GrupoAtivo->codOrgao;
        } // fecha if
        else // caso o usuário não esteja autenticado
        {
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }

        //recupera ID do pre projeto (proposta)
        parent::init(); // chama o init() do pai GenericControllerNew
    } // fecha método init()*/


    public function indexAction() {
        //** Usuario Logado ************************************************/
        $auth               = Zend_Auth::getInstance(); // pega a autenticação
        $idusuario          = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        $GrupoAtivo         = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao           = $GrupoAtivo->codOrgao; //  órgão ativo na sessão
        $codPerfil          = $GrupoAtivo->codGrupo; //  órgão ativo na sessão
        $this->view->codOrgao = $codOrgao;
        $this->view->idUsuarioLogado = $idusuario;
        /******************************************************************/

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
            $order = array(2); //Pronac
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['b.Orgao = ?'] = $codOrgao;

        if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['AnoProjeto+Sequencial = ?'] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
        }
        
        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $tipoFiltro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            switch ($tipoFiltro) {
                case 'emanalise': //Em análise
                    $tipoFiltro = 'emanalise';
                    $filtro = 'Em análise';
                    $where['a.siCumprimentoObjeto = ?'] = 3;
                    break;
                case 'analisados': //Analisados
                    $tipoFiltro = 'analisados';
                    $filtro = 'Analisados';
                    $where['a.siCumprimentoObjeto = ?'] = 5;
                    break;
                default: //Aguardando Análise
                    $tipoFiltro = 'aguardando';
                    $filtro = 'Aguardando Análise';
                    $where['a.siCumprimentoObjeto = ?'] = 2;
                    break;
            }

        } else { //Aguardando Análise
            $tipoFiltro = 'aguardando';
            $filtro = 'Aguardando Análise';
            $where['a.siCumprimentoObjeto = ?'] = 2;
        }

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $total = $tbCumprimentoObjeto->listaRelatorios($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $tbCumprimentoObjeto->listaRelatorios($where, $order, $tamanho, $inicio);

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

        $this->view->paginacao      = $paginacao;
        $this->view->qtdRegistros   = $total;
        $this->view->dados          = $busca;
        $this->view->intTamPag      = $this->intTamPag;
        $this->view->filtro         = $filtro;
        $this->view->tipoFiltro     = $tipoFiltro;

        $pa = new paUsuariosDoPerfil();
        $usuarios = $pa->buscarUsuarios($codPerfil, $codOrgao);
        $this->view->Usuarios = $usuarios;
    }
    
    public function imprimirPainelAction() {
        
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
        
        //** Usuario Logado ************************************************/
        $auth               = Zend_Auth::getInstance(); // pega a autenticação
        $idusuario          = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        $GrupoAtivo         = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao           = $GrupoAtivo->codOrgao; //  órgão ativo na sessão
        $codPerfil          = $GrupoAtivo->codGrupo; //  órgão ativo na sessão
        $this->view->codOrgao = $codOrgao;
        $this->view->idUsuarioLogado = $idusuario;
        /******************************************************************/

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
            $order = array(2); //Pronac
            $ordenacao = null;
        }

        $pag = 1;
        $post = Zend_Registry::get('post');
        if (isset($post->pag)) $pag = $post->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['b.Orgao = ?'] = $codOrgao;

        if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['AnoProjeto+Sequencial = ?'] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
        }
        if(isset($_POST['tipoFiltro']) || isset($_GET['tipoFiltro'])){
            $tipoFiltro = isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : $_GET['tipoFiltro'];
            switch ($tipoFiltro) {
                case 'emanalise': //Em análise
                    $tipoFiltro = 'emanalise';
                    $filtro = 'Em análise';
                    $where['a.siCumprimentoObjeto = ?'] = 3;
                    break;
                case 'analisados': //Analisados
                    $tipoFiltro = 'analisados';
                    $filtro = 'Analisados';
                    $where['a.siCumprimentoObjeto = ?'] = 5;
                    break;
                default: //Aguardando Análise
                    $tipoFiltro = 'aguardando';
                    $filtro = 'Aguardando Análise';
                    $where['a.siCumprimentoObjeto = ?'] = 2;
                    break;
            }

        } else { //Aguardando Análise
            $tipoFiltro = 'aguardando';
            $filtro = 'Aguardando Análise';
            $where['a.siCumprimentoObjeto = ?'] = 2;
        }

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $total = $tbCumprimentoObjeto->listaRelatorios($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;
        $busca = $tbCumprimentoObjeto->listaRelatorios($where, $order, $tamanho, $inicio);

        if(isset($post->xls) && $post->xls){
            $colspan = 7;
            if(isset($tipoFiltro) && $tipoFiltro != 'aguardando'){
                $colspan = 8;
            }

            $html = '';
            $html .= '<table style="border: 1px">';
            $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 16; font-weight: bold;" colspan="'.$colspan.'">Analisar Comprovação do Objeto - '.$filtro.'</td></tr>';
            $html .='<tr><td style="border: 1px dotted black; background-color: #EAF1DD; font-size: 10" colspan="'.$colspan.'">Data do Arquivo: '. Data::mostraData() .'</td></tr>';
            $html .='<tr><td colspan="'.$colspan.'"></td></tr>';

            $html .= '<tr>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">#</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">PRONAC</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Nome do Projeto</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">UF</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Mecanismo</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Situação</th>';
            $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Dt. Relatório</th>';
            if(isset($tipoFiltro) && $tipoFiltro != 'aguardando'){
                $html .= '<th style="border: 1px dotted black; background-color: #9BBB59;">Técnico</th>';
            }
            $html .= '</tr>';

            $pa = new paUsuariosDoPerfil();
            $usuarios = $pa->buscarUsuarios($codPerfil, $codOrgao);
            
            $i=1;
            foreach ($busca as $dp){

                if($dp->Mecanismo == 1){
                    $mecanismo = 'Incentivo Fiscal Federal';
                } else if($dp->Mecanismo != 2){
                    $mecanismo = 'FNC';
                } else if($dp->Mecanismo != 6){
                    $mecanismo = 'Recursos do Tesouro';
                }
                
                if(isset($tipoFiltro) && $tipoFiltro != 'aguardando'){
                    foreach ($usuarios as $user) {
                        if($user->idUsuario == $dp->idTecnicoAvaliador){
                            $nomeTec = $user->Nome;
                        }
                    }
                }

                $html .= '<tr>';
                $html .= '<td style="border: 1px dotted black;">'.$i.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$dp->Pronac.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$dp->NomeProjeto.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$dp->UfProjeto.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$mecanismo.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.$dp->Situacao.' - '.$dp->dsSituacao.'</td>';
                $html .= '<td style="border: 1px dotted black;">'.Data::tratarDataZend($dp->dtCadastro, 'Brasileiro').'</td>';
                if(isset($tipoFiltro) && $tipoFiltro != 'aguardando'){
                    $html .= '<td style="border: 1px dotted black;">'.$nomeTec.'</td>';
                }
                $html .= '</tr>';
                $i++;
            }
            $html .= '</table>';

            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=Analisar_Comprovacao_do_Objeto.xls;");
            echo $html; die();

        } else {
            $this->view->dados = $busca;
            $this->view->filtro = $filtro;
            $this->view->tipoFiltro = $tipoFiltro;
            
            $pa = new paUsuariosDoPerfil();
            $usuarios = $pa->buscarUsuarios($codPerfil, $codOrgao);
            $this->view->Usuarios = $usuarios;
        }
    }

    public function visualizarRelatorioAction() {

        $idpronac = $this->_request->getParam("idPronac");
        $this->view->idPronac = $idpronac;
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $DadosRelatorio = $tbCumprimentoObjeto->buscarCumprimentoObjeto(array('idPronac = ?' => $idpronac, 'siCumprimentoObjeto in (?)'=>array(2,5)));
        $this->view->DadosRelatorio = $DadosRelatorio;
        $this->view->cumprimentoDoObjeto = $tbCumprimentoObjeto;
        if(count($DadosRelatorio)==0){
            parent::message("Relatório não encontrado!", "avaliaracompanhamentoprojeto/index", "ALERT");
        }

        $LocaisDeRealizacao = $projetos->buscarLocaisDeRealizacao($idpronac);
        $this->view->LocaisDeRealizacao = $LocaisDeRealizacao;

        $PlanoDeDivulgacao = $projetos->buscarPlanoDeDivulgacao($idpronac);
        $this->view->PlanoDeDivulgacao = $PlanoDeDivulgacao;

        $PlanoDistribuicaoProduto = new PlanoDistribuicaoProduto();
        $PlanoDeDistribuicao = $PlanoDistribuicaoProduto->buscarPlanoDeDistribuicao($idpronac);
        $this->view->PlanoDeDistribuicao = $PlanoDeDistribuicao;

        $tbBeneficiarioProdutoCultural = new tbBeneficiarioProdutoCultural();
        $PlanosCadastrados = $tbBeneficiarioProdutoCultural->buscarPlanosCadastrados($idpronac);
        $this->view->PlanosCadastrados = $PlanosCadastrados;

        $DadosCompMetas = $projetos->buscarMetasComprovadas($idpronac);
        $this->view->DadosCompMetas = $DadosCompMetas;

        $DadosItensOrcam = $projetos->buscarItensComprovados($idpronac);
        $this->view->DadosItensOrcam = $DadosItensOrcam;

        $Arquivo = new Arquivo();
        $dadosComprovantes = $Arquivo->buscarComprovantesExecucao($idpronac);
        $this->view->DadosComprovantes = $dadosComprovantes;

        $tbTermoAceiteObra = new tbTermoAceiteObra();
        $AceiteObras = $tbTermoAceiteObra->buscarTermoAceiteObraArquivos(array('idPronac=?'=>$idpronac));
        $this->view->AceiteObras = $AceiteObras;

        $tbBensDoados = new tbBensDoados();
        $BensCadastrados = $tbBensDoados->buscarBensCadastrados(array('a.idPronac=?'=>$idpronac), array('b.Descricao'));
        $this->view->BensCadastrados = $BensCadastrados;

        if($DadosRelatorio->siCumprimentoObjeto >= 5 ){
            $Usuario = new UsuarioDAO();
            $nmUsuarioCadastrador = $Usuario->buscarUsuario($DadosRelatorio->idTecnicoAvaliador);
            $nmChefiaImediata = $Usuario->buscarUsuario($DadosRelatorio->idChefiaImediata);
            $this->view->TecnicoAvaliador = $nmUsuarioCadastrador;
            $this->view->ChefiaImediata = $nmChefiaImediata;
        }
    }

    public function imprimirAction() {

        $idpronac = $this->_request->getParam("pronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }
        
        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $DadosRelatorio = $tbCumprimentoObjeto->buscarCumprimentoObjeto(array('idPronac = ?' => $idpronac, 'siCumprimentoObjeto!=?'=>1));
        $this->view->DadosRelatorio = $DadosRelatorio;
        if(count($DadosRelatorio)==0){
            parent::message("Relatório não encontrado!", "avaliaracompanhamentoprojeto/index", "ALERT");
        }

        $LocaisDeRealizacao = $projetos->buscarLocaisDeRealizacao($idpronac);
        $this->view->LocaisDeRealizacao = $LocaisDeRealizacao;

        $PlanoDeDivulgacao = $projetos->buscarPlanoDeDivulgacao($idpronac);
        $this->view->PlanoDeDivulgacao = $PlanoDeDivulgacao;

        $PlanoDistribuicaoProduto = new PlanoDistribuicaoProduto();
        $PlanoDeDistribuicao = $PlanoDistribuicaoProduto->buscarPlanoDeDistribuicao($idpronac);
        $this->view->PlanoDeDistribuicao = $PlanoDeDistribuicao;

        $tbBeneficiarioProdutoCultural = new tbBeneficiarioProdutoCultural();
        $PlanosCadastrados = $tbBeneficiarioProdutoCultural->buscarPlanosCadastrados($idpronac);
        $this->view->PlanosCadastrados = $PlanosCadastrados;

        $DadosCompMetas = $projetos->buscarMetasComprovadas($idpronac);
        $this->view->DadosCompMetas = $DadosCompMetas;

        $DadosItensOrcam = $projetos->buscarItensComprovados($idpronac);
        $this->view->DadosItensOrcam = $DadosItensOrcam;

        $Arquivo = new Arquivo();
        $dadosComprovantes = $Arquivo->buscarComprovantesExecucao($idpronac);
        $this->view->DadosComprovantes = $dadosComprovantes;

        $tbTermoAceiteObra = new tbTermoAceiteObra();
        $AceiteObras = $tbTermoAceiteObra->buscarTermoAceiteObraArquivos(array('idPronac=?'=>$idpronac));
        $this->view->AceiteObras = $AceiteObras;

        $tbBensDoados = new tbBensDoados();
        $BensCadastrados = $tbBensDoados->buscarBensCadastrados(array('a.idPronac=?'=>$idpronac), array('b.Descricao'));
        $this->view->BensCadastrados = $BensCadastrados;

        if($DadosRelatorio->siCumprimentoObjeto >= 5 ){
            $Usuario = new UsuarioDAO();
            $nmUsuarioCadastrador = $Usuario->buscarUsuario($DadosRelatorio->idTecnicoAvaliador);
            $nmChefiaImediata = $Usuario->buscarUsuario($DadosRelatorio->idChefiaImediata);
            $this->view->TecnicoAvaliador = $nmUsuarioCadastrador;
            $this->view->ChefiaImediata = $nmChefiaImediata;
        }

        $this->_helper->layout->disableLayout();// Desabilita o Zend Layout
    }

    public function encaminharRelatorioAction() {
        $post = Zend_Registry::get('post');
        $idPronac = (int) $post->pronac;

        $dados = array();
        $dados['idTecnicoAvaliador'] = (int) $post->tecnico;
        $dados['siCumprimentoObjeto'] = 3;
        $where = "idPronac = $idPronac";

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $return = $tbCumprimentoObjeto->update($dados, $where);

        if($return){
            parent::message("Relatório encaminhado com sucesso!", "avaliaracompanhamentoprojeto/index", "CONFIRM");
        } else {
            parent::message("Relatório não foi encaminhado. Contate o Administrador do sistema!", "avaliaracompanhamentoprojeto/index", "ERROR");
        }
    }

    public function indexTecnicoAction() {
        //** Usuario Logado ************************************************/
        $auth               = Zend_Auth::getInstance(); // pega a autenticação
        $idusuario          = $auth->getIdentity()->usu_codigo;
        $GrupoAtivo         = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao           = $GrupoAtivo->codOrgao; //  órgão ativo na sessão
        $codPerfil          = $GrupoAtivo->codGrupo; //  órgão ativo na sessão
        $this->view->codOrgao = $codOrgao;
        $this->view->idUsuarioLogado = $idusuario;
        /******************************************************************/

        if($codPerfil!=139){
            parent::message("Você não tem permissão para acessar essa funcionalidade!", "principal", "ALERT");
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

        }else {
            $campo = null;
            $order = array('NomeProjeto');
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();
        $where['b.Orgao = ?'] = $codOrgao;
        $where['a.siCumprimentoObjeto in (?)'] = array(3,4);
        $where['a.idTecnicoAvaliador = ?'] = $idusuario;

        if((isset($_POST['pronac']) && !empty($_POST['pronac'])) || (isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $where['AnoProjeto+Sequencial = ?'] = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
            $this->view->pronacProjeto = isset($_POST['pronac']) ? $_POST['pronac'] : $_GET['pronac'];
        }

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $total = $tbCumprimentoObjeto->listaRelatorios($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $tbCumprimentoObjeto->listaRelatorios($where, $order, $tamanho, $inicio);

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

    public function devolverRelatorioAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

        //** Usuario Logado ************************************************/
        $auth               = Zend_Auth::getInstance(); // pega a autenticação
        $idusuario          = $auth->getIdentity()->usu_codigo;
        /******************************************************************/

        $post = Zend_Registry::get('post');
        $idPronac = (int) $post->pronac;

        $dados = array();
        $dados['idTecnicoAvaliador'] = null;
        $dados['siCumprimentoObjeto'] = 2;
        $where = "idPronac = $idPronac AND idTecnicoAvaliador = $idusuario";

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $return = $tbCumprimentoObjeto->update($dados, $where);

        if($return){
            echo json_encode(array('resposta'=>true));
        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }

    public function parecerTecnicoAction() {

        //** Usuario Logado ************************************************/
        $auth               = Zend_Auth::getInstance(); // pega a autenticação
        $idusuario          = $auth->getIdentity()->usu_codigo;
        $nmusuario          = $auth->getIdentity()->usu_nome;
        $GrupoAtivo         = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao           = $GrupoAtivo->codOrgao; //  órgão ativo na sessão
        $codPerfil          = $GrupoAtivo->codGrupo; //  órgão ativo na sessão
        /******************************************************************/

        if($codPerfil!=139){
            parent::message("Você não tem permissão para acessar essa funcionalidade!", "principal", "ALERT");
        }

        $idpronac = $this->_request->getParam("idpronac");
        $idrelatorio = $this->_request->getParam("relatorio");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        $where = array();
        $where['a.idPronac = ?'] = $idpronac;
        $where['a.idTecnicoAvaliador = ?'] = $idusuario;
        $where['a.siCumprimentoObjeto in (?)'] = array(3,4);
        $where['b.Orgao = ?'] = $codOrgao;

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $DadosRelatorio = $tbCumprimentoObjeto->listaRelatorios($where, array(), null, null, false);

        if (count($DadosRelatorio)==0) {
            parent::message('Relatório não encontrado!', "avaliaracompanhamentoprojeto/index-tecnico", "ALERT");
        }

        $this->view->DadosRelatorio = $DadosRelatorio;
        $this->view->idPronac = $idpronac;
        $this->view->idRelatorio = $idrelatorio;
        $this->view->idusuario = $idusuario;
        $this->view->nmusuario = $nmusuario;

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $dadosParecer = $tbCumprimentoObjeto->buscarCumprimentoObjeto(array('idPronac=?'=>$idpronac,'idTecnicoAvaliador=?'=>$idusuario));
        $this->view->DadosParecer = $dadosParecer;

        $pa = new paCoordenadorDoPerfil();
        $usuarios = $pa->buscarUsuarios($codPerfil, $codOrgao);
        $this->view->Usuarios = $usuarios;
    }

    public function etapasDeTrabalhoFinalAction() {
        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $DadosRelatorio = $tbCumprimentoObjeto->buscarCumprimentoObjeto(array('idPronac=?'=>$idpronac));
        $this->view->DadosRelatorio = $DadosRelatorio;
        $this->view->cumprimentoDoObjeto = $tbCumprimentoObjeto;
        $this->view->idPronac = $idpronac;
    }

    public function localDeRealizacaoFinalAction() {

        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $LocaisDeRealizacao = $projetos->buscarLocaisDeRealizacao($idpronac);
        $this->view->LocaisDeRealizacao = $LocaisDeRealizacao;
        $this->view->idPronac = $idpronac;
    }

    public function planoDeDivulgacaoFinalAction() {

        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $dadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $dadosProjeto;

        $PlanoDeDivulgacao = $projetos->buscarPlanoDeDivulgacao($idpronac);
        $this->view->PlanoDeDivulgacao = $PlanoDeDivulgacao;

        $Verificacao = new Verificacao();
        $Peca = $Verificacao->buscar(array('idTipo =?'=>1,'stEstado =?'=>1));
        $this->view->Peca = $Peca;

        $Veiculo = $Verificacao->buscar(array('idTipo =?'=>2,'stEstado =?'=>1));
        $this->view->Veiculo = $Veiculo;
        $this->view->idPronac = $idpronac;

        $tbArquivoImagem = new tbArquivoImagem();
        $this->view->marcas = $tbArquivoImagem->marcasAnexadas($dadosProjeto->pronac);
    }

    public function planoDeDistribuicaoFinalAction() {

        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $PlanoDistribuicaoProduto = new PlanoDistribuicaoProduto();
        $PlanoDeDistribuicao = $PlanoDistribuicaoProduto->buscarPlanoDeDistribuicao($idpronac);
        $this->view->PlanoDeDistribuicao = $PlanoDeDistribuicao;

        $tbBeneficiarioProdutoCultural = new tbBeneficiarioProdutoCultural();
        $PlanosCadastrados = $tbBeneficiarioProdutoCultural->buscarPlanosCadastrados($idpronac);
        $this->view->PlanosCadastrados = $PlanosCadastrados;
        $this->view->idPronac = $idpronac;
    }

    public function metasComprovadasFinalAction() {

        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        //****** Dados da Comprovação de Metas *****//
        $DadosCompMetas = $projetos->buscarMetasComprovadas($idpronac);
        $this->view->DadosCompMetas = $DadosCompMetas;
        $this->view->idPronac = $idpronac;
    }

    public function itensComprovadosFinalAction() {

        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $DadosItensOrcam = $projetos->buscarItensComprovados($idpronac);
        $this->view->DadosItensOrcam = $DadosItensOrcam;
        $this->view->idPronac = $idpronac;
    }

    public function comprovantesDeExecucaoFinalAction() {

        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $Arquivo = new Arquivo();
        $dadosComprovantes = $Arquivo->buscarComprovantesExecucao($idpronac);
        $this->view->DadosComprovantes = $dadosComprovantes;
        $this->view->idPronac = $idpronac;
    }

    public function aceiteDeObraFinalAction() {
        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $tbTermoAceiteObra = new tbTermoAceiteObra();
        $DadosRelatorio = $tbTermoAceiteObra->buscarTermoAceiteObraArquivos(array('idPronac=?'=>$idpronac));
        $this->view->DadosRelatorio = $DadosRelatorio;
        $this->view->idPronac = $idpronac;
    }

    public function bensFinalAction() {
        //** Verifica se o usuário logado tem permissão de acesso **//
        $this->verificarPermissaoAcesso(false, true, false);

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
        $projetos = new Projetos();
        $DadosProjeto = $projetos->buscarProjetoXProponente(array('idPronac = ?' => $idpronac))->current();
        $this->view->DadosProjeto = $DadosProjeto;

        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        $DadosItens = $tbPlanilhaAprovacao->buscarItensOrcamentarios(array('a.idPronac=?'=>$idpronac), array('b.Descricao'));
        $this->view->DadosItens = $DadosItens;

        $tbBensDoados = new tbBensDoados();
        $BensCadastrados = $tbBensDoados->buscarBensCadastrados(array('a.idPronac=?'=>$idpronac), array('b.Descricao'));
        $this->view->BensCadastrados = $BensCadastrados;
        $this->view->idPronac = $idpronac;
    }

    public function avaliarRelatorioAction() {

        //** Usuario Logado ************************************************/
        $auth               = Zend_Auth::getInstance(); // pega a autenticação
        $idusuario          = $auth->getIdentity()->usu_codigo;
        $GrupoAtivo         = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $codOrgao           = $GrupoAtivo->codOrgao; //  órgão ativo na sessão
        /******************************************************************/

        $idpronac = $this->_request->getParam("idpronac");
        if (strlen($idpronac) > 7) {
            $idpronac = Seguranca::dencrypt($idpronac);
        }

        $where = array();
        $where['idPronac = ?'] = $idpronac;
        $where['idTecnicoAvaliador = ?'] = $idusuario;
        $where['siCumprimentoObjeto in (?)'] = array(3,4);

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $DadosRelatorio = $tbCumprimentoObjeto->buscarCumprimentoObjeto($where);

        if (empty($DadosRelatorio)) {
            parent::message('Relatório não encontrado!', "avaliaracompanhamentoprojeto/index-tecnico", "ALERT");
        }

        $siComprovante = 4;
        $msg = 'Relatório salvo com sucesso!';
        $controller = "avaliaracompanhamentoprojeto/parecer-tecnico?idpronac=".$idpronac;
        if(isset($_POST['finalizar']) && !empty($_POST['finalizar'])){
            $siComprovante = 5;
            $msg = 'Relatório finalizado com sucesso!';
            $controller = 'avaliaracompanhamentoprojeto/index-tecnico';
        }

        $dados = array(
            'dsInformacaoAdicional' => $_POST['informacaoAdicional'],
            'dsOrientacao' => $_POST['orientacao'],
            'dsConclusao' => $_POST['conclusao'],
            'stResultadoAvaliacao' => $_POST['resultadoAvaliacao'],
            'idChefiaImediata' => $_POST['chefiaImediata'],
            'siCumprimentoObjeto' => $siComprovante
        );
        
        $whereFinal = 'idCumprimentoObjeto = '.$DadosRelatorio->idCumprimentoObjeto;
        $resultado = $tbCumprimentoObjeto->alterar($dados, $whereFinal);

        if($resultado){
            parent::message($msg, $controller, "CONFIRM");
        } else {
            parent::message('Não foi possível salvar o relatório!', "analisarexecucaofisicatecnico", "ERROR");
        }
    }

    public function finalizarRelatorioAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

        $post = Zend_Registry::get('post');
        $idPronac = (int) $post->pronac;

        $dados = array();
        $dados['siCumprimentoObjeto'] = 6;
        $where = "idPronac = $idPronac";

        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        $projetoModel = new Projetos();
        $return = $tbCumprimentoObjeto->update($dados, $where);
        $projetoModel->mudarSituacao($idPronac, 'E68', 'E24');

        if($return){
            echo json_encode(array('resposta'=>true));
        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }

    public function recursosPorFonteAction()
    {
        $planilhaAprovacaoModel = new PlanilhaAprovacao();
        $this->view->recursosPorFonte = $planilhaAprovacaoModel
                ->buscarRecursosDaFonte($this->getRequest()->getParam('idpronac'));
        //Passando o pronac para ser usada no menu lateral esquerdo
        $this->view->idPronac = $this->getRequest()->getParam('idpronac');
    }

}
