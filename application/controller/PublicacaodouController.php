<?php

/**
 * PublicacaoDouController
 * @author Equipe RUP - Politec
 * @since 20/07/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright 2010 - Ministerio da Cultura - Todos os direitos reservados.
 */
class PublicacaoDouController extends GenericControllerNew {

    /**
     * @var integer (variavel com o id do usuario logado)
     * @access privacte
     */
    public function init() {
        // verifica as permissoes
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 128; // Tecnico de Portaria
        // definicao do perfil
        parent::perfil(1, $PermissoesGrupo);

        parent::init(); // chama o init() do pai GenericControllerNew
    }

// fecha meodo init()

    /**
     * Redireciona para o fluxo inicial
     * @access public
     * @param void
     * @return void
     */
    public function indexAction() {
        
        $tblOrgao = new Orgaos();
        $rsOrgao  = $tblOrgao->buscar(array(), array("Sigla ASC"));
        $this->view->orgaos = $rsOrgao;

        $this->intTamPag = 50;
//        $this->intTamPag = 10;

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
            $order = array(1); //NomeProjeto
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão

        $Orgaos = new Orgaos();
        $orgaoSuperior = $Orgaos->codigoOrgaoSuperior($orgaoAtivo)->current();

        $wherenaopublicados = array();

        if($orgaoSuperior->Superior == 251){
            $wherenaopublicados['pr.Area <> ?'] = 2;
        } else {
            $wherenaopublicados['pr.Area = ?'] = 2;
        }
        $wherenaopublicados['ap.PortariaAprovacao is null or DtPublicacaoAprovacao is null or DtPortariaAprovacao is null'] = '';
        //$wherenaopublicados['YEAR(ap.DtInicioCaptacao) = YEAR(GETDATE())'] = '';


        if((isset($_GET['pronac']) && !empty($_GET['pronac']))){
            $this->view->pronac = $_GET['pronac'];
            $wherenaopublicados['pr.AnoProjeto+pr.Sequencial = ?'] = $_GET['pronac'];
        }
        
        if((isset($_GET['orgaoFiltro']) && !empty($_GET['orgaoFiltro']))){
            $this->view->orgaoFiltro = $_GET['orgaoFiltro'];
            $wherenaopublicados['pr.Orgao = ?'] = $_GET['orgaoFiltro'];
        }
        
        if(isset($_GET['situacao'])){
            $filtro = $_GET['situacao'];
            $this->view->filtro = $filtro;
            switch ($filtro) {
                case '':
                    $wherenaopublicados['pr.Situacao in (?)'] = array('D27');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 1;
                    break;
                case 'complementacao':
                    $this->view->nmPagina = 'Complementa&ccedil;&atilde;o';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('D28');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 2;
                    break;
                case 'prorrogacao':
                    $this->view->nmPagina = 'Prorroga&ccedil;&atilde;o';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('D22');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 3;
                    break;
                case 'reducao':
                    $this->view->nmPagina = 'Redu&ccedil;&atilde;o';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('D29');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 4;
                    break;
                case 'aprovacaoPrestacao':
                    $this->view->nmPagina = 'Aprova&ccedil;&atilde;o - Presta&ccedil;&atilde;o de Contas';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('D42');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 5;
                    break;
                case 'reprovacaoPrestacao':
                    $this->view->nmPagina = 'Reprova&ccedil;&atilde;o - Presta&ccedil;&atilde;o de Contas';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('D43');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 6;
                    break;
                case 'readequacao':
                    $this->view->nmPagina = 'Readequa&ccedil;&atilde;o';
                    $wherenaopublicados['r.siEncaminhamento = ?'] = 9;
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 8;
                    break;
            }
        } else {
            $wherenaopublicados['pr.Situacao in (?)'] = array('D27');
            $wherenaopublicados['ap.TipoAprovacao = ?'] = 1;
        }

        $projetos = New Projetos();
        if($this->_getParam('situacao') == 'readequacao'){
            $total = $projetos->buscarProjetosReadequacoes($wherenaopublicados, $order, null, null, true);
        } else {
            $total = $projetos->buscarProjetosAprovados($wherenaopublicados, $order, null, null, true);
        }
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        if($this->_getParam('situacao') == 'readequacao'){
            $busca = $projetos->buscarProjetosReadequacoes($wherenaopublicados, $order, $tamanho, $inicio);
        } else {
            $busca = $projetos->buscarProjetosAprovados($wherenaopublicados, $order, $tamanho, $inicio);
        }
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


        $buscaCargosPublicacao = PublicacaoDouDAO::buscaCargosPublicacao();
        $this->view->cargosPublicacao = $buscaCargosPublicacao;

        $buscaNomesPublicacao = PublicacaoDouDAO::buscaNomesPublicacao();
        $this->view->nomesPublicacao = $buscaNomesPublicacao;


        $wherepublicados["ap.dtPortariaAprovacao IS NOT NULL OR DtPublicacaoAprovacao IS NOT NULL or ap.PortariaAprovacao <> ''"] = '';
        if($orgaoSuperior->Superior == 251){
            $wherepublicados['pr.Area <> ?'] = 2;
        } else {
            $wherepublicados['pr.Area = ?'] = 2;
        }
        //$wherepublicados['YEAR(ap.DtInicioCaptacao) = YEAR(GETDATE())'] = '';

        if(isset($_GET['situacao'])){
            $filtro = $_GET['situacao'];
            switch ($filtro) {
                case '':
                    $wherepublicados['pr.Situacao = ?'] = 'D09';
                    $wherepublicados['ap.TipoAprovacao = ?'] = 1;
                    break;
                case 'complementacao':
                    $wherepublicados['pr.Situacao = ?'] = 'D16';
                    $wherepublicados['ap.TipoAprovacao = ?'] = 2;
                    break;
                case 'prorrogacao':
                    $wherepublicados['pr.Situacao = ?'] = 'D17';
                    $wherepublicados['ap.TipoAprovacao = ?'] = 3;
                    break;
                case 'reducao':
                    $wherepublicados['pr.Situacao = ?'] = 'D23';
                    $wherepublicados['ap.TipoAprovacao = ?'] = 4;
                    break;
                case 'aprovacaoPrestacao':
                    $this->view->nmPagina = 'Aprova&ccedil;&atilde;o - Presta&ccedil;&atilde;o de Contas';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('E19');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 5;
                    break;
                case 'reprovacaoPrestacao':
                    $this->view->nmPagina = 'Reprova&ccedil;&atilde;o - Presta&ccedil;&atilde;o de Contas';
                    $wherenaopublicados['pr.Situacao in (?)'] = array('L05');
                    $wherenaopublicados['ap.TipoAprovacao = ?'] = 6;
                    break;
            }
        } else {
            $wherepublicados['pr.Situacao = ?'] = 'D09';
            $wherepublicados['ap.TipoAprovacao = ?'] = 1;
        }

        // busca os projetos publicados
        $ap = new Aprovacao();
        $buscaportaria = $ap->buscarportaria($wherepublicados);
        $this->view->projetosPublicados = $buscaportaria;

        ini_set('memory_limit', '-1');
        if (isset($_POST['datapublicacao'])) {

            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $datapublicacao = $_POST['datapublicacao'];
            $portaria = $_POST['portaria'];

            $dados = array('dtpublicacaoaprovacao' => Data::dataAmericana($datapublicacao));

            try {
                PublicacaoDouDAO::alterardatapublicacao($dados, $portaria);
                echo json_encode(array('error' => false, 'datagravada' => $datapublicacao));
                die;
            } catch (Exception $e) {
                echo json_encode(array('error' => true));
                die;
            }
        }

        if (isset($_POST['portaria'])) {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $pr = new Projetos();
            $portaria = str_replace('-', '/', $_POST['portaria']);

            $where['ap.PortariaAprovacao = ?'] = $portaria;
            $buscarprojetos = $pr->buscarProjetosAprovados($where, array(), null, null, false);

            //$btnPublicar = "<td align=\"center\" colspan=\"4\"><input type=\"button\" class=\"btn_publicar\" onclick=\"confirmaPublicacao('{$portaria}');\" title=\"Publicar\"/> Publicar</td>";
            $btnPublicar = '<td align="center" colspan="4"><input type="button" class="btn_imprimir impressaoPublicados" title=Imprimir" portaria="'.$portaria.'"/></td>';

            $table = '<table class=\'tabela\'>';
            $table.= '<tr>';
            $table.= '<th width="80px">pronac</th>';
            $table.= '<th>nome projeto</th>';
            $table.= '<th width="200px">&aacute;rea</th>';
            $table.= '<th width="125px">valor aprovado</th>';
            $table.= '</tr>';

            foreach ($buscarprojetos as $projetos) {
                $table.= '<tr>';
                $table.= '<td align="center"><a href="' . Zend_Controller_Front::getInstance()->getBaseUrl() . '/consultardadosprojeto/index/?idPronac=' . $projetos->IdPRONAC . '" target="_blank" title="Ir para Consultar Dados do Projeto">' . $projetos->pronac . '</td>';
                $table.= '<td>' . utf8_encode($projetos->NomeProjeto) . '</td>';
                $table.= '<td>' . utf8_encode($projetos->area) . '</td>';
                $table.= '<td align="right">R$ ' . number_format($projetos->AprovadoReal, '2', ',', '.') . '</td>';
                $table.= '</tr>';
            }

            $table.= '<tr>';
            $table.= $btnPublicar;
            $table.= '</tr>';

            $table.= '<table>';
            echo $table;
            die;
        }
    }

// fecha meodo indexAction()

    public function gerarportariaAction() {
        ini_set('memory_limit', '-1');
//        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $ap = new Aprovacao();
        $pr = new Projetos();
        if ($this->getRequest()->isPost()) {
            // recebe os dados via post

            $post = Zend_Registry::get('post');
            $id = $post->idAprovacao;
            $dia = (int) date("d");
            $mes = (int) date("m");
            $ano = date("Y");
            $ano2Digitos = date("y");
            $semana = date("w");
            $dias = 86400;
            $datas = array();
            $datas['pascoa'] = easter_date($ano);
            $datas['sexta_santa'] = $datas['pascoa'] - (2 * $dias);
            $datas['carnaval'] = $datas['pascoa'] - (47 * $dias);
            $datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dias);

            $feriados = array(
                'Ano Novo' => date('Y') . '-01-01',
                'Carnaval' => date('Y-m-d', $datas['carnaval']),
                'Sexta-Feira Santa' => date('Y-m-d', $datas['sexta_santa']),
                'Pascoa' => date('Y-m-d', $datas['pascoa']),
                'Tiradentes' => date('Y') . '-04-21',
                'Dia do Trabalhador' => date('Y') . '-05-01',
                'Corpus Cristi' => date('Y-m-d', $datas['corpus_cristi']),
                'Dia da Independencia' => date('Y') . '-09-07',
                'Nossa Senhora de Aparecida' => date('Y') . '-10-12',
                'Dia de Finados' => date('Y') . '-11-02',
                'Proclamacao da Republica' => date('Y') . '-11-15',
                'Natal' => date('Y') . '-12-25'
            );
            $DtPortariaAprovacao = date("Y-m-d H:i:s");

            if ($semana == 5) {   // sexta
                // feriado na segunda
                if (in_array(strftime("%Y-%m-%d", strtotime("+3 days")), $feriados)) {
                    $DtPublicacaoAprovacao = strftime("%Y-%m-%d %H:%M:%S", strtotime("+4 days"));
                } else {
                    $DtPublicacaoAprovacao = strftime("%Y-%m-%d %H:%M:%S", strtotime("+3 days"));
                }
            } else {
                // feriado na sexta (supondo que hoje ï¿½ quinta)
                if (in_array(strftime("%Y-%m-%d", strtotime("+1 days")), $feriados) && !in_array(strftime("%Y-%m-%d", strtotime("+4 days")), $feriados)) {
                    $DtPublicacaoAprovacao = strftime("%Y-%m-%d %H:%M:%S", strtotime("+4 days"));
                }
                // feriado na sexta e na segunda
                else if (in_array(strftime("%Y-%m-%d", strtotime("+1 days")), $feriados) && in_array(strftime("%Y-%m-%d", strtotime("+4 days")), $feriados)) {
                    $DtPublicacaoAprovacao = strftime("%Y-%m-%d %H:%M:%S", strtotime("+5 days"));
                } else {
                    $DtPublicacaoAprovacao = strftime("%Y-%m-%d %H:%M:%S", strtotime("+1 days"));
                }
            }

            try {
                // manda todos os pronac para publicacao (alteracao)
                foreach ($id as $idaprovacao) {
                    //busca o idPronac do projeto
                    $buscaridpronac = $ap->buscar(array('idAprovacao = ?' => $idaprovacao))->current();

                    //busca a data final de execução do projeto em questão
                    $resultado = $pr->buscar(array('IdPRONAC = ?'=>$buscaridpronac->IdPRONAC))->current();
                    $dtFimCaptacao = $resultado->DtFimExecucao; //É isso mesmo que vc vê. A data fim captação vai receber o mesmo valor da fim de execução.
                    $dtFimExecucao = $resultado->DtFimExecucao;
                    $dtInicioExecucao = $resultado->DtInicioExecucao;

                    //se a data final de execução estiver em branco (projetos antigos) o sistema considera o 31/12/ano em questão
                    if($resultado->DtFimExecucao == '' || empty($resultado->DtFimExecucao)){
                        $dtFimCaptacao = date("Y", strtotime($DtPublicacaoAprovacao)) . '-12-31 ' . date("H:i:s");
                    } else {
                        //se o ano da data final de execução for maior do que o ano em questão, o fim de captação vai até 31/12/ano em questão
                        if(date("Y", strtotime($dtFimCaptacao)) > date("Y", strtotime($DtPublicacaoAprovacao))){
                            $dtFimCaptacao = date("Y", strtotime($DtPublicacaoAprovacao)) . '-12-31 ' . date("H:i:s");
                        }
                    }
                    // dados para realizar a publicacao
                    $dadosPortaria = array(
                        'PortariaAprovacao' => $post->nrPortaria.'/'.date('y'),
                        'DtPortariaAprovacao' => $DtPortariaAprovacao,
                        'DtPublicacaoAprovacao' => $DtPublicacaoAprovacao,
                        'DtInicioCaptacao' => $DtPublicacaoAprovacao,
                        'DtFimCaptacao' => $dtFimCaptacao
                    );

                    if($post->tipoPublicacao == 'prorrogacao'){
                        $pronac = $resultado->AnoProjeto.$resultado->Sequencial;
                        $datas = $ap->buscarDatasCaptacao($pronac, $buscaridpronac->idProrrogacao);
                        $dadosPortaria['DtInicioCaptacao'] = $datas[0]->DtInicio;
                        $dadosPortaria['DtFimCaptacao'] = $datas[0]->DtFinal;

                        if(strtotime($dtFimExecucao) < strtotime($datas[0]->DtFinal)){
                            $dtFimExecucao = $datas[0]->DtFinal;
                        }
                    }

                    $where = 'idAprovacao = ' . $idaprovacao;
                    $portariagerar = $ap->alterar($dadosPortaria, $where);

                    if ($portariagerar) {
                        $dadosSituacao = array(
                            'dtSituacao' => date('Y-m-d'),
                            'DtFimExecucao' => $dtFimExecucao
                        );

                        if($post->tipoPublicacao == 'prorrogacao' && (empty($dtInicioExecucao) || $dtInicioExecucao == '')){
                            $dadosSituacao['DtInicioExecucao'] = $datas[0]->DtInicio;
                        }

                        if(isset($post->tipoPublicacao)){
                            switch ($post->tipoPublicacao) {
                                case '':
                                    $dadosSituacao['Situacao'] = 'D09';
                                    $dadosSituacao['ProvidenciaTomada'] = 'Portaria de aprovação inicial encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                                    break;
                                case 'complementacao':
                                    $dadosSituacao['Situacao'] = 'D16';
                                    $dadosSituacao['ProvidenciaTomada'] = 'Portaria de complementação encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                                    break;
                                case 'prorrogacao':
                                    $dadosSituacao['Situacao'] = 'D17';
                                    $dadosSituacao['ProvidenciaTomada'] = 'Portaria de prorrogação encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                                    break;
                                case 'reducao':
                                    $dadosSituacao['Situacao'] = 'D23';
                                    $dadosSituacao['ProvidenciaTomada'] = 'Portaria de redução encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                                    break;
                                case 'aprovacaoPrestacao':
                                    $dadosSituacao['Situacao'] = 'D42';
                                    $dadosSituacao['ProvidenciaTomada'] = 'Portaria de Prestação de Contas encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                                    break;
                                case 'reprovacaoPrestacao':
                                    $dadosSituacao['Situacao'] = 'D43';
                                    $dadosSituacao['ProvidenciaTomada'] = 'Portaria de Prestação de Contas encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                                    break;
                            }
                        } else {
                            $dadosSituacao['Situacao'] = 'D09';
                            $dadosSituacao['ProvidenciaTomada'] = 'Portaria de aprovação inicial encaminhada à Imprensa Nacional para publicação no Diário Oficial da União.';
                        }

                        $where = 'IdPRONAC = ' . $buscaridpronac->IdPRONAC;
                        $pr->alterar($dadosSituacao, $where);
                    } // fecha if
                } // fecha foreach

                if($post->nome == 1){ //Ana Cristina da Cunha Wanzeler
                    $textoPortaria = '426 de 28 de maio de 2014 e o art. 4&ordm; da Portaria n&ordm; 120, de 30 de mar&ccedil;o de 2010';
                    $nm = 'Ivan Domingues das Neves';

                } else if($post->nome == 2) { //João Batista da Silva
                    $textoPortaria = '805 de 09 de outubro de 2013, e em cumprimento ao disposto na Lei 8.313, de 23 de dezembro de 1991, Decreto nº 5.761, de 27 de abril de 2006, Medida Provisória nº 2.228-1, de 06 de setembro de 2001, alterada pela Lei nº 10.454 de 13 de maio de 2002';
                    $nm = 'João Batista da Silva';

                } else if($post->nome == 3) { //Kleber da Silva Rocha
                    $textoPortaria = '909 de 19 de novembro de 2013 e o art. 4&ordm; da Portaria n&ordm; 120, de 30 de Mar&ccedil;o de 2010';
                    $nm = 'Kleber da Silva Rocha';

                } else if($post->nome == 4) { //Mário Henrique Costa Borgneth
                    $textoPortaria = '846 de 07 de novembro de 2013, e em cumprimento ao disposto na Lei 8.313, de 23 de dezembro de 1991, Decreto nº 5.761, de 27 de abril de 2006, Medida Provisória nº 2.228-1, de 06 de setembro de 2001, alterada pela Lei nº 10.454 de 13 de maio de 2002';
                    $nm = 'Mário Henrique Costa Borgneth';

                } else {
                    $textoPortaria = '17 de 12 de janeiro de 2010 e o art. 4&ordm; da Portaria n&ordm; 120, de 30 de Mar&ccedil;o de 2010';
                    $nm = 'Ivan Domingues das Neves';
                }

                $this->view->cargo = strtoupper(strtr($post->cargo ,"áéíóúâêôãõàèìòùç","ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
                $this->view->nome = strtoupper(strtr($nm ,"áéíóúâêôãõàèìòùç","ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
                $this->view->tipoPublicacao = $post->tipoPublicacao;
                $this->view->textoPortaria = $textoPortaria;

                parent::message("Portaria nº ".$_POST['nrPortaria']."/".$ano2Digitos." foi gerada com sucesso!", "publicacaodou/consultar-portaria?portaria=".$_POST['nrPortaria']."/".$ano2Digitos."&situacao=".$post->tipoPublicacao, "CONFIRM");

                // pega a portaria gerada
//                $portaria = PublicacaoDouDAO::ProjetoPortaria($_POST['nrPortaria'].'/'.date('y'), $dadosSituacao['Situacao']);
//                $this->view->portaria = $portaria;
            } // fecha try
            catch (Exception $e) {
                parent::message($e->getMessage(), "publicacaodou?situacao=".$post->tipoPublicacao, "ERROR");
            }
        } // fecha if
    }

// fecha meodo gerarportariaAction()

    public function retirarportariaAction() {
        ini_set('memory_limit', '-1');

        if ($_GET['PortariaAprovacao']) {
            $PortariaAprovacao = $_GET['PortariaAprovacao'];
            
            $dados = array();
            if(isset($_GET['tipo'])){
                switch ($_GET['tipo']) {
                    case '':
                        $dados['Situacao'] = 'D27';
                        $tipoPublicacao = 1;
                        break;
                    case 'complementacao':
                        $dados['Situacao'] = 'D28';
                        $tipoPublicacao = 2;
                        break;
                    case 'prorrogacao':
                        $dados['Situacao'] = 'D22';
                        $tipoPublicacao = 3;
                        break;
                    case 'reducao':
                        $dados['Situacao'] = 'D29';
                        $tipoPublicacao = 4;
                        break;
                    case 'aprovacaoPrestacao':
                        $dados['Situacao'] = 'D42';
                        $tipoPublicacao = 5;
                        break;
                    case 'reprovacaoPrestacao':
                        $dados['Situacao'] = 'D43';
                        $tipoPublicacao = 6;
                        break;
                    case 'readequacao':
                        $tipoPublicacao = 8;
                        break;
                }
            } else {
                $dados['Situacao'] = 'D27';
                $tipoPublicacao = 1;
            }
            
            $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
            $orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão

            $Orgaos = new Orgaos();
            $orgaoSuperior = $Orgaos->codigoOrgaoSuperior($orgaoAtivo)->current();
            $projetosPublicacao = PublicacaoDouDAO::buscarPortariaPublicacao($PortariaAprovacao, $orgaoSuperior, $tipoPublicacao);

            try {
                foreach ($projetosPublicacao as $projetosretirar) {
                    $tbProjetos = new Projetos();
                    $dadosProjeto = $tbProjetos->buscar(array('IdPRONAC = ?'=>$projetosretirar->IdPRONAC))->current();
                    
                    $dados['IdPRONAC'] = $projetosretirar->IdPRONAC;
                    $dados['DtSituacao'] = date('Y-m-d');
                    $dados['ProvidenciaTomada'] = 'Projeto encaminhado para a inclusão em portaria.';
                    
                    if($tipoPublicacao == 8){ //Se for readequação, não altera os dados da Situação
                        $dados['Situacao'] = $dadosProjeto->Situacao;
                        $dados['DtSituacao'] = $dadosProjeto->DtSituacao;
                    }
                    
                    $IdPRONAC = $projetosretirar->IdPRONAC;
                    $idAprovacao = $projetosretirar->idAprovacao;

                    PublicacaoDouDAO::retirarpublicacao($dados, $IdPRONAC);
                    PublicacaoDouDAO::apagarpublicacao($idAprovacao);
                }
                parent::message("Projetos retirados da publicação de portaria!", "publicacaodou?pronac=&situacao=".$_GET['tipo'], "CONFIRM");
            } // fecha try
            catch (Exception $e) {
                parent::message($e->getMessage(), "publicacaodou/index", "ERROR");
            }
        } // fecha if
    }

// fecha meodo retirarportariaAction()

    /**
     * Faz a publicação na portaria
     */
    public function publicarportariaAction() {
        ini_set('memory_limit', '-1');

        if ($_GET['PortariaAprovacao']) {
            $PortariaAprovacao = $_GET['PortariaAprovacao'];
            
            if(isset($_GET['tipo'])){
                switch ($_GET['tipo']) {
                    case '':
                        $TipoAprovacao = 1;
                        $situacaoAtual = 'D09';
                        break;
                    case 'complementacao':
                        $TipoAprovacao = 2;
                        $situacaoAtual = 'D16';
                        break;
                    case 'prorrogacao':
                        $TipoAprovacao = 3;
                        $situacaoAtual = 'D17';
                        break;
                    case 'reducao':
                        $TipoAprovacao = 4;
                        $situacaoAtual = 'D23';
                        break;
                    case 'aprovacaoPrestacao':
                        $TipoAprovacao = 5;
                        $situacaoAtual = 'D42';
                        break;
                    case 'reprovacaoPrestacao':
                        $TipoAprovacao = 6;
                        $situacaoAtual = 'D43';
                        break;
                    case 'readequacao':
                        $TipoAprovacao = 8;
                        //$situacaoAtual = 'D43';
                        break;
                }
            } else {
                $TipoAprovacao = 1;
                $situacaoAtual = 'D09';
            }
            
            $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
            $orgaoLogado = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão

            $Orgaos = new Orgaos();
            $orgaoSuperior = $Orgaos->codigoOrgaoSuperior($orgaoLogado)->current();

            $auth = Zend_Auth::getInstance(); // pega a autenticação
            $usuarioLogado = $auth->getIdentity()->usu_codigo;

            try {
                if($TipoAprovacao == 5){
                    PublicacaoDouDAO::situcaopublicacaodou($TipoAprovacao, $PortariaAprovacao, 'E19', $situacaoAtual, $usuarioLogado, $orgaoSuperior->Superior);
                } else if($TipoAprovacao == 6){
                    PublicacaoDouDAO::situcaopublicacaodou($TipoAprovacao, $PortariaAprovacao, 'L05', $situacaoAtual, $usuarioLogado, $orgaoSuperior->Superior);
                } else if($TipoAprovacao == 8){
                    
                    $where = array();
                    if($orgaoSuperior->Superior == 251){
                        $where['a.Area <> ?'] = 2;
                    } else {
                        $where['a.Area = ?'] = 2;
                    }
                    $where['b.TipoAprovacao = ?'] = 8;
                    $where['b.PortariaAprovacao = ?'] = $PortariaAprovacao;
                    
                    $ap = new Aprovacao();
                    $projetos = $ap->consultaPortariaReadequacoes($where);
                    foreach ($projetos as $p) {
                        
                        // READEQUAÇÃO DE ALTERAÇÃO DE RAZÃO SOCIAL
                        if($p->idTipoReadequacao == 3){

                            $Projetos = new Projetos();
                            $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$p->IdPRONAC))->current();
            
                            $Agentes = new Agentes();
                            $dadosAgente = $Agentes->buscar(array('CNPJCPF=?'=>$dadosPrj->CgcCpf))->current();
                            
                            $Nomes = new Nomes();
                            $dadosNomes = $Nomes->buscar(array('idAgente=?'=>$dadosAgente->idAgente))->current();
                            $dadosNomes->Descricao = $p->dsSolicitacao;
                            $dadosNomes->save();

                        // READEQUAÇÃO DE ALTERAÇÃO DE PROPONENTE
                        } else if($p->idTipoReadequacao == 10){

                            $Projetos = new Projetos();
                            $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$p->IdPRONAC))->current();

                            $cnpjcpf = Mascara::delMaskCPFCNPJ($p->dsSolicitacao);
                            $dadosPrj->CgcCpf = $cnpjcpf;
                            $dadosPrj->save();
                            
                        // READEQUAÇÃO DE NOME DO PROJETO
                        } else if($p->idTipoReadequacao == 12){
                            
                            $Projetos = new Projetos();
                            $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$p->IdPRONAC))->current();
                            $dadosPrj->NomeProjeto = $p->dsSolicitacao;
                            $dadosPrj->ProvidenciaTomada = 'Projeto aprovado e publicado no Di&aacute;rio Oficial da Uni&atilde;o.';
                            $dadosPrj->Logon = $usuarioLogado;
                            $dadosPrj->save();
                        
                        // READEQUAÇÃO DE RESUMO DO PROJETO
                        } else if($p->idTipoReadequacao == 15){
                            
                            $Projetos = new Projetos();
                            $dadosPrj = $Projetos->find(array('IdPRONAC=?'=>$p->IdPRONAC))->current();
                            $dadosPrj->ResumoProjeto = $p->dsSolicitacao;
                            $dadosPrj->ProvidenciaTomada = 'Projeto aprovado e publicado no Di&aacute;rio Oficial da Uni&atilde;o.';
                            $dadosPrj->Logon = $usuarioLogado;
                            $dadosPrj->save();
                        }
                        
                        $tbReadequacao = new tbReadequacao();
                        $dadosReadequacao = $tbReadequacao->buscar(array('idReadequacao = ?' => $p->idReadequacao))->current();
                        $dadosReadequacao->siEncaminhamento = 15;
                        $dadosReadequacao->stEstado = 1;
                        $dadosReadequacao->save();
                    }
                    parent::message("Portaria publicada com sucesso!", "publicacaodou/index?pronac=&situacao=".$this->_getParam('tipo'), "CONFIRM");
                    
                } else {
                    PublicacaoDouDAO::situcaopublicacaodou($TipoAprovacao, $PortariaAprovacao, 'E10', $situacaoAtual, $usuarioLogado, $orgaoSuperior->Superior);
                    PublicacaoDouDAO::situcaopublicacaodou($TipoAprovacao, $PortariaAprovacao, 'E12', $situacaoAtual, $usuarioLogado, $orgaoSuperior->Superior);
                }
                parent::message("Portaria publicada com sucesso!", "publicacaodou/index?pronac=&situacao=".$_GET['tipo'], "CONFIRM");
            } catch (Exception $e) {
                parent::message("Erro ao atualizar a portaria!" . $e->getMessage(), "publicacaodou/index?pronac=&situacao=".$_GET['tipo'], "ERROR");
            }

        } // fecha if
    }

    public function imprimirPublicadosAction(){
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $pr = new Projetos();
        $portaria = str_replace('-', '/', $_GET['portaria']);

        $where['ap.PortariaAprovacao = ?'] = $portaria;
        $buscarprojetos = $pr->buscarProjetosAprovados($where);
        $this->view->dados = $buscarprojetos;
        $this->view->portaria = $portaria;
        //xd($buscarprojetos);
    }

    public function consultarPortariaAction(){

        $numeroPortaria = $this->_getParam('portaria');
        $situacao = $this->_getParam('situacao');
        
        //Se foi feito a pesquisa pelo filtro
        if($_GET){
            
            if(isset($numeroPortaria) && empty($numeroPortaria)){
                parent::message("Favor informar o número da portaria!", "publicacaodou/consultar-portaria", "ALERT");
            }
            
            $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
            $orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão

            $Orgaos = new Orgaos();
            $orgaoSuperior = $Orgaos->codigoOrgaoSuperior($orgaoAtivo)->current();

            $where = array();
            if($orgaoSuperior->Superior == 251){
                $where['a.Area <> ?'] = 2;
            } else {
                $where['a.Area = ?'] = 2;
            }

            $this->view->filtro = $situacao;
            if(isset($situacao)){
                $filtro = $situacao;
                switch ($filtro) {
                    case '':
                        $where['a.Situacao = ?'] = 'D09';
                        $where['b.TipoAprovacao = ?'] = 1;
                        break;
                    case 'complementacao':
                        $where['a.Situacao = ?'] = 'D16';
                        $where['b.TipoAprovacao = ?'] = 2;
                        break;
                    case 'prorrogacao':
                        $where['a.Situacao = ?'] = 'D17';
                        $where['b.TipoAprovacao = ?'] = 3;
                        break;
                    case 'reducao':
                        $where['a.Situacao = ?'] = 'D23';
                        $where['b.TipoAprovacao = ?'] = 4;
                        break;
                    case 'aprovacaoPrestacao':
                        $where['a.Situacao = ?'] = 'D42';
                        $where['b.TipoAprovacao = ?'] = 5;
                        break;
                    case 'reprovacaoPrestacao':
                        $where['a.Situacao = ?'] = 'D43';
                        $where['b.TipoAprovacao = ?'] = 6;
                        break;
                    case 'readequacao':
                        $where['b.TipoAprovacao = ?'] = 8;
                        break;
                }
            } else {
                $where['a.Situacao = ?'] = 'D09';
                $where['b.TipoAprovacao = ?'] = 1;
            }
            $where['b.PortariaAprovacao = ?'] = $numeroPortaria;
            
            // busca os projetos publicados
            $ap = new Aprovacao();
            if($filtro == 'readequacao'){
                $buscaportaria = $ap->consultaPortariaReadequacoes($where);
            } else {
                $buscaportaria = $ap->consultaPortaria($where);
            }
            
            $this->view->projetosPublicados = $buscaportaria;
            $this->view->portaria = $numeroPortaria;

//            $buscaCargosPublicacao = PublicacaoDouDAO::buscaCargosPublicacao();
//            $this->view->cargosPublicacao = $buscaCargosPublicacao;

            $tbManterPortaria = new tbManterPortaria();
            $this->view->nomesPublicacao = $tbManterPortaria->buscar(array('stEstado = ?'=>1));
        }
    }

    public function gerarArquivoRtfAction(){
        ini_set('memory_limit', '-1');
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        
        $tbManterPortaria = new tbManterPortaria();
        $dados = $tbManterPortaria->buscar(array('idManterPortaria = ?'=>$_POST['nome']))->current();
        $textoPortaria = trim(strip_tags($dados->dsPortaria));
        $nm = $dados->dsAssinante;

        $this->view->cargo = strtoupper(strtr($dados->dsCargo ,"áéíóúâêôãõàèìòùç","ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
        $this->view->nome = strtoupper(strtr($nm ,"áéíóúâêôãõàèìòùç","ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
        $this->view->tipoPublicacao = isset($_POST['imprimitipoPublicacao']) && !empty($_POST['imprimitipoPublicacao']) ? $_POST['imprimitipoPublicacao'] : '';
        $this->view->textoPortaria = $textoPortaria;
        
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão

        $Orgaos = new Orgaos();
        $orgaoSuperior = $Orgaos->codigoOrgaoSuperior($orgaoAtivo)->current();

        if($this->view->tipoPublicacao == 'readequacao'){
            $portaria = PublicacaoDouDAO::ProjetoPortariaGerarRTFReadequacoes($_POST['nrportaria'], $orgaoSuperior);
        } else {
            $portaria = PublicacaoDouDAO::ProjetoPortariaGerarRTF($_POST['nrportaria'], $orgaoSuperior);
        }
        $this->view->portaria = $portaria;
    }
    
    public function imprimirTabelaPortariaAction(){
        ini_set('memory_limit', '-1');
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
        
        $Orgaos = new Orgaos();
        $orgaoSuperior = $Orgaos->codigoOrgaoSuperior($orgaoAtivo)->current();

        $where = array();
        if($orgaoSuperior->Superior == 251){
            $where['a.Area <> ?'] = 2;
        } else {
            $where['a.Area = ?'] = 2;
        }

        $this->view->filtro = $_POST['filtro'];
        if(isset($_POST['filtro'])){
            $filtro = $_POST['filtro'];
            switch ($filtro) {
                case '':
                    $where['a.Situacao = ?'] = 'D09';
                    $where['b.TipoAprovacao = ?'] = 1;
                    break;
                case 'complementacao':
                    $where['a.Situacao = ?'] = 'D16';
                    $where['b.TipoAprovacao = ?'] = 2;
                    break;
                case 'prorrogacao':
                    $where['a.Situacao = ?'] = 'D17';
                    $where['b.TipoAprovacao = ?'] = 3;
                    break;
                case 'reducao':
                    $where['a.Situacao = ?'] = 'D23';
                    $where['b.TipoAprovacao = ?'] = 4;
                    break;
                case 'aprovacaoPrestacao':
                    $where['a.Situacao = ?'] = 'D42';
                    $where['b.TipoAprovacao = ?'] = 5;
                    break;
                case 'reprovacaoPrestacao':
                    $where['a.Situacao = ?'] = 'D43';
                    $where['b.TipoAprovacao = ?'] = 6;
                    break;
            }
        } else {
            $where['a.Situacao = ?'] = 'D09';
            $where['b.TipoAprovacao = ?'] = 1;
        }
        $where['b.PortariaAprovacao = ?'] = $_POST['nrportaria'];

        // busca os projetos publicados
        $ap = new Aprovacao();
        $buscaportaria = $ap->consultaPortariaImpressao($where);
        $this->view->projetos = $buscaportaria;
        $this->view->portaria = $_POST['nrportaria'];
    }

// fecha meodo publicarportariaAction()
}

// fecha class