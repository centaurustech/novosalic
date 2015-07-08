<?php

/**
 * AnexarDocumentosController
 * @author Equipe RUP - Politec
 * @since 28/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @link http://www.cultura.gov.br
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 */
require_once "GenericControllerNew.php";

class GerarimprimirpdfController extends GenericControllerNew
{
    public function init()
    {
   
        $this->view->title = "Salic - Sistema de Apoio às Leis de Incentivo à Cultura"; // título da página
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Usuario = new UsuarioDAO(); // objeto usuário
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo

        if ($auth->hasIdentity()) // caso o usuário esteja autenticado
        {
            // verifica as permissões
            $PermissoesGrupo = array();
            $PermissoesGrupo[] = 93;  // Coordenador de Parecerista
            $PermissoesGrupo[] = 94;  // Parecerista
            $PermissoesGrupo[] = 103; // Coordenador de Análise
            $PermissoesGrupo[] = 118; // Componente da Comissão
            $PermissoesGrupo[] = 119; // Presidente da Mesa
            $PermissoesGrupo[] = 120; // Coordenador Administrativo CNIC
            //if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) // verifica se o grupo ativo está no array de permissões
            //{
            //    parent::message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
            //}

            // pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
//            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
//
//            // manda os dados para a visão
//            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
//            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
//            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
//            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
            
        } // fecha if
        else // caso o usuário não esteja autenticado
        {
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }
        parent::init(); // chama o init() do pai GenericControllerNew
    }
    public function indexAction(){
       $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout

       function verifica($var){
           if($var or $var == 1){
               return "Sim";
           }else{
               return "Não";
           }
       }
       function data($data){
           if(!empty ($data)){
                $dataF = new data($data);
                return $dataF->dataBrasileira($dataF->tratarDataZend($data, "americano"));
           }
       }
       function tratatexto($valor){

           $valor = str_replace('<br>', '|br|', $valor);
           $valor = str_replace('</p>', '|br|', $valor);
           $valor = strip_tags($valor);
           $valor = str_replace('|br|', '<br>', $valor);
           return $valor;

       }

       $id_projeto = $_REQUEST['idPreProjeto'];
        
       //$consultaDadosProjeto = GerarImprimirpdfDAO::ConsultaDadosProjeto($id_projeto);//busca dados do preprojeto na tabela PreProjeto
       $tblPreProjeto = new PreProjeto();
       $consultaDadosProjeto = $tblPreProjeto->buscaCompleta(array("idPreProjeto = ?"=>$id_projeto))->current()->toArray();
       
        if($consultaDadosProjeto['Mecanismo'] == "1" and ($consultaDadosProjeto['idEdital'] == NULL or ($consultaDadosProjeto['idEdital'] == "0"))){
            $mecanismo = 'Incentivo Fiscal';
        }else{
            $mecanismo = 'FNC';
        }
        //xd($consultaDadosProjeto);

        $texto = '<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Leitor PDF</title>
        <style>
        body {
                font:12px "Trebuchet MS", Georgia, "Times New Roman", Times, serif;
                color:#666;
                line-height:14pt;
                margin:30px;
            }
            h2{
                margin:0px;
                margin-top:50px;
                border-bottom:1px #36963f solid;
                font-size:13pt;
            }

            p{
                padding:15px;
            }
        </style>
        </head>

        <body style="paddig:20px">
        <center><h1>Proposta Cultural</h1></center>
        <br>
        <h2>Identifica&ccedil;&atilde;o </h2>
        <table>
            <tr>
                <td width="300px">
                    N. da Proposta: <br><b>'.$id_projeto.'</b>
                </td>
                <td>
                    Nome da Proposta: <br><b>'.$consultaDadosProjeto['NomeProjeto'].'</b>
                </td>
             </tr>
           <tr>
                <td width="300px">
                    Proponente: <br><b>'.$consultaDadosProjeto['CNPJCPF'].' - '.$consultaDadosProjeto['NomeAgente'].'</b>
                </td>
                <td>
                    Mecanismo: <br>
                    <b>'.$mecanismo.'</b>

                </td>
            </tr>
        </table>
        <br /><br />
        <h2>Resumo da Proposta Cultural </h2>
        
        <p class="MsoNormal">'.$consultaDadosProjeto['ResumoDoProjeto'].'</p>
        <br /><br />
        <h2>Abrangência geográfica da proposta cultural</h2>
    <br>
        <p>
            <table cellpadding="3" style="width:100%">
            <tr>
                <th>País</th>
                <th>UF</th>
                <th>Cidade</th>
                <th>Dt.Início de Execuçãoo</th>
                <th>Dt.Final de Execução</th>
            </tr>
                ';
                foreach ( GerarImprimirpdfDAO::AbrangenciaGeografica($id_projeto) as $Abrangencia )//busca locais onde o preprojeto sera realizado
                {
                    $texto .= '<tr bgcolor="#EEEEEE"><td">'.$Abrangencia->Pais.'</td>';
                    $texto .= '<td>'.$Abrangencia->UF.'</td>';
                    $texto .= '<td>'.$Abrangencia->Cidade.'</td>';
                    $texto .= '<td>'.data($Abrangencia->DtInicioDeExecucao).'</td>';
                    $texto .= '<td>'.data($Abrangencia->DtFinalDeExecucao).'</td></tr>';
                }
    $texto .= '
          </table>
        </p>';

if($mecanismo == 'Incentivo Fiscal'){
///DADOS APENAS PARA O INCENTIVO FISCAL
        $texto .= '<h2>Informações Complementares</h2>
        <table style="width:100%" cellpadding="13" border="0">
            <tr>
                <td>Mecanismo: '.$mecanismo.'</td>
                <td><nobr>Data Fixa: '.verifica($consultaDadosProjeto['stDataFixa']).'</nobr></td>
                <td>Plano Anual: '.verifica($consultaDadosProjeto['stPlanoAnual']).'</td>
                <td>Ag.Bancária: '.$consultaDadosProjeto['AgenciaBancaria'].'</td>
                <td>Proposta Audiovisual: '.verifica($consultaDadosProjeto['AreaAbrangencia']).'</td>
            </tr>
        </table>
        <br>
        <table style="width:100%; margin-top:50px">
            <tr>
                <td style="padding-right:10px">
                    <h2>Período de Realização</h2>
                    <p>
                    Data Início: '.data($consultaDadosProjeto['DtInicioDeExecucao']).'<br />
                    Data Final: '.data($consultaDadosProjeto['DtFinalDeExecucao']).'
                    </p>
                </td>
                <td style="padding-left:10px">
                    <h2>Bem Tombado</h2>
                    <p>';
                    if ($consultaDadosProjeto['EsferaTombamento'] > 0){
                      $texto .= 'N. do Ato: '.$consultaDadosProjeto['NrAtoTombamento'].'
                                 Data do Ato: '.data($consultaDadosProjeto['DtAtoTombamento']).'
                                 Esfera do Ato: '.$consultaDadosProjeto['EsferaTombamento'];
                    }else{
                      $texto .= 'Bem não tombado';
                    }
                $texto .= '</p></td>
            </tr>
        </table>
        <h2>Objetivos do Projeto</h2>
        <p>'.tratatexto($consultaDadosProjeto['Objetivos']).'</p>
        <h2>Justificativa do Projeto</h2>
        <p>'.tratatexto($consultaDadosProjeto['Justificativa']).'</p>
        <h2>Acessibilidade</h2>
        <p>'.tratatexto($consultaDadosProjeto['Acessibilidade']).'</p>
        <h2>Democratização de Acesso</h2>
        <p>'.tratatexto($consultaDadosProjeto['DemocratizacaoDeAcesso']).'</p>
        <h2>Fases do Projeto</h2>
        <p>'.tratatexto($consultaDadosProjeto['EtapaDeTrabalho']).'</p>
        <h2>Ficha Técnica</h2>
        <p>'.tratatexto($consultaDadosProjeto['FichaTecnica']).'</p>
        <h2>Sinopse da obra</h2>
        <p>'.tratatexto($consultaDadosProjeto['Sinopse']).'</p>
        <h2>Impacto Ambiental</h2>
        <p>'.tratatexto($consultaDadosProjeto['ImpactoAmbiental']).'</p>
        <h2>Especificações técnicas do produto</h2>
        <p>'.tratatexto($consultaDadosProjeto['EspecificacaoTecnica']).'</p>
        <h2>Plano básico de Divulgação</h2>
        <p>
            <table cellpadding="3" style="width:100%">
                ';
                foreach (GerarImprimirpdfDAO::ConsultaDadosDivulgacao($id_projeto) as $DadosDivulgacao)//busca dados de divulgação do preprojeto
                {
                    $texto .= '<tr bgcolor="#EEEEEE"><td width="80%">'.$DadosDivulgacao->Peca.'</td>';
                    $texto .= '<td>'.$DadosDivulgacao->Veiculo.'</td></tr>';
                }
    $texto .= '
            </table>
        </p>
        <br><br>
      <h2>Plano de distribuição de produtos culturais</h2>
      <p>
        <table cellpadding="3" style="width:100%">
        <tr>
            <td style="font-size:8pt" valin="bottom"><b>Nome do Evento/Produto</b></td>
            <td style="font-size:8pt"><b>Qtde.Divulgação</b></td>
            <td style="font-size:8pt"><b>Qtde.Patrocinador</b></td>
            <td style="font-size:8pt"><b>Distribuíção Gratuíta</b></td>
            <td style="font-size:8pt"><b>Total Venda</b></td>
            <td style="font-size:8pt"><b>Total Venda Promocional</b></td>
            <td style="font-size:8pt"><b>Qtde Total</b></td>
            <td style="font-size:8pt"><b>Preco Uni. Promocional</b></td>
            <td style="font-size:8pt"><b>Preco Uni. Normal</b></td>
            <td style="font-size:8pt"><b>Receita Prev. Normal</b></td>
            <td style="font-size:8pt"><b>Receita Prev. Promocional</b></td>
        </tr>
        ';

                    $Total                          = 0;
                    $QtdeOutrosTotal                = 0;
                    $QtdeVendaNormalTotal           = 0;
                    $QtdeVendaPromocionalTotal      = 0;
                    $QtdeProduzidaTotal             = 0;
                    $PrecoUnitarioPromocionalTotal  = 0;
                    $PrecoUnitarioNormalTotal       = 0;
                    $ReceitaNormalTotal             = 0;
                    $ReceitaProTotal                = 0;

                foreach ( GerarImprimirpdfDAO::DistribuicaodeProduto($id_projeto) as $DadosDistribuicao )//busca dados de distribuição do preprojeto
                {
                    $QtdeOutrosTotal                = $QtdeOutrosTotal + $DadosDistribuicao->QtdeOutros;
                    $QtdeVendaNormalTotal           = $QtdeVendaNormalTotal + $DadosDistribuicao->QtdeVendaNormal;
                    $QtdeVendaPromocionalTotal      = $QtdeVendaPromocionalTotal + $DadosDistribuicao->QtdeVendaPromocional;
                    $QtdeProduzidaTotal             = $QtdeProduzidaTotal + $DadosDistribuicao->QtdeProduzida;
                    $PrecoUnitarioPromocionalTotal  = $PrecoUnitarioPromocionalTotal + $DadosDistribuicao->PrecoUnitarioPromocional;
                    $PrecoUnitarioNormalTotal       = $PrecoUnitarioNormalTotal + $DadosDistribuicao->PrecoUnitarioNormal;
                    $ReceitaNormalTotal             = $ReceitaNormalTotal + $DadosDistribuicao->ReceitaNormal;
                    $ReceitaProTotal                = $ReceitaProTotal + $DadosDistribuicao->ReceitaPro;
                    $Total++;
                    
                    
                    $texto .= '<tr bgcolor="#EEEEEE">';
                    $texto .= '<td>'.$DadosDistribuicao->Produto.'</td>';
                    $texto .= '<td>'.$DadosDistribuicao->QtdeProponente.'</td>';
                    $texto .= '<td>'.$DadosDistribuicao->QtdePatrocinador.'</td>';
                    $texto .= '<td>'.$DadosDistribuicao->QtdeOutros.'</td>';
                    $texto .= '<td>'.$DadosDistribuicao->QtdeVendaNormal.'</td>';
                    $texto .= '<td>'.$DadosDistribuicao->QtdeVendaPromocional.'</td>';
                    $texto .= '<td>'.$DadosDistribuicao->QtdeProduzida.'</td>';
                    $texto .= '<td>'.number_format($DadosDistribuicao->PrecoUnitarioPromocional,"2",",",".").'</td>';
                    $texto .= '<td>'.number_format($DadosDistribuicao->PrecoUnitarioNormal,"2",",",".").'</td>';
                    $texto .= '<td>'.number_format($DadosDistribuicao->ReceitaNormal,"2",",",".").'</td>';
                    $texto .= '<td>'.number_format($DadosDistribuicao->ReceitaPro,"2",",",".").'</td>';
                    $texto .= '</tr>';
                }
    $texto .= '
        <tr>
            <th colspan="3" align="left">Total Geral ('.$Total.')</th>
            <th align="left">'.$QtdeOutrosTotal.'</th>
            <th align="left">'.$QtdeVendaNormalTotal.'</th>
            <th align="left">'.$QtdeVendaPromocionalTotal.'</th>
            <th align="left">'.$QtdeProduzidaTotal.'</th>
            <th align="left">'.number_format($PrecoUnitarioPromocionalTotal,"2",",",".").'</th>
            <th align="left">'.number_format($PrecoUnitarioNormalTotal,"2",",",".").'</th>
            <th align="left">'.number_format($ReceitaNormalTotal,"2",",",".").'</th>
            <th align="left">'.number_format($ReceitaProTotal,"2",",",".").'</th>
        </tr>
        </table>
      </p>';


        $texto .='<h2>Planilha Orçamentária</h2>';
        $etapa = -1;
        $Produto = -1;
        $TotalProduto = 0;
        $TotalEtapa = 0;
        $TotalOrcamento = 0;

        foreach (GerarImprimirpdfDAO::Orcamento($id_projeto) as $Orcamento)//busca dados de orçamento do preprojeto
        {

            if (($Produto != $Orcamento->idProduto or $etapa != $Orcamento->idEtapa) and $Produto != -1){

                $texto .= '<tr>
                                <th colspan="9" align="left" style="font-size:9pt;">Total Produto: '.number_format($TotalProduto,2,',','.').'</th>
                           </tr>';
                $TotalEtapa = $TotalEtapa + $TotalProduto;
                $TotalProduto = 0;

            }

            if ($etapa != $Orcamento->idEtapa and $etapa != -1){

                $texto .= '<tr>
                                <th colspan="9" align="left" style="font-size:11pt;">Total Etapa: '.number_format($TotalEtapa,2,',','.').'</th>
                           </tr>
                           '; //</table>
                $TotalOrcamento = $TotalOrcamento + $TotalEtapa;
                $TotalEtapa = 0;
            }


             if ($etapa != $Orcamento->idEtapa or $Produto != $Orcamento->idProduto)
            {

                $texto .= '
                <table border=0 cellpading="5" width="100%" style="font-size:6pt; margin-top:20px">
                <tr>
                    <th colspan="11" align="left" style="font-size:12pt;">Etapa: '.$Orcamento->Etapa.'</th>
                </tr>
                <tr>
                   <th colspan="11" align="left" style="font-size:12pt;">Produto: '.$Orcamento->ProdutoF.' </th>
                </tr>
                <tr>
                    <td bgcolor="#EEEEEE" align="center"><b>Item</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Unid.</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Quant.</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Ocorr</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Valor Unid.</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Total</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Dias</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Fonte de Recurso</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>UF</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Município</b></td>
                    <td bgcolor="#EEEEEE" align="center"><b>Justificativa</b></td>
                </tr>';
                };
                $TotalProduto = $TotalProduto + (($Orcamento->Quantidade)*($Orcamento->Ocorrencia)*($Orcamento->ValorUnitario));
                $etapa = $Orcamento->idEtapa;
                $Produto = $Orcamento->idProduto;
                 $texto .= '<tr>';
                    $texto .= '<td>'.$Orcamento->Item.'</td>';
                    $texto .= '<td>'.$Orcamento->UnidadeF.'</td>';
                    $texto .= '<td>'.(int)$Orcamento->Quantidade.'</td>';
                    $texto .= '<td>'.(int)$Orcamento->Ocorrencia.'</td>';
                    $texto .= '<td>'.number_format($Orcamento->ValorUnitario,2,',','.').'</td>';
                    $texto .= '<td>'.number_format(($Orcamento->Quantidade)*($Orcamento->Ocorrencia)*($Orcamento->ValorUnitario),2,',','.').'</td>';
                    $texto .= '<td>'.$Orcamento->QtdeDias.'</td>';
                    $texto .= '<td>'.$Orcamento->FonteRecursoF.'</td>';
                    $texto .= '<td>'.$Orcamento->UfDespesaF.'</td>';
                    $texto .= '<td>'.$Orcamento->MunicipioDespesaF.'</td>';
                    $texto .= '<td>'.$Orcamento->dsJustificativa.'</td>';
                $texto .= '</tr>
                           <tr>
                                <td bgcolor="#EEEEEE"  colspan="11" style="height:1px"></td>
                           </tr>
                ';
         }
         
         $TotalOrcamento = $TotalOrcamento + $TotalProduto;
         
         $texto .= '
                
                       <table border=0 cellpading="5" width="100%" style="font-size:6pt;">
                           <tr>
                                <th colspan="9" align="left" style="font-size:9pt;">Total Produto: '.number_format($TotalProduto,2,',','.').'</th>
                           </tr>
                           <tr>
                                <th colspan="9" align="left" style="font-size:11pt;">Total Etapa: '.number_format($TotalEtapa + $TotalProduto,2,',','.').'</th>
                           </tr>
                           <tr>
                                <th colspan="9" align="left" bgcolor="#EEEEEE" style="font-size:11pt; padding-top:10px;">Orçamento Total: '.number_format($TotalOrcamento,2,',','.').'</th>
                           </tr>
                        </table>';


   //FIM DOS DADOS POR ENCENTIVO FISCAL
   }//Fim do IF para, este if exibe o conteudo apenas quando a proposta é por encentivo fiscal
        $texto .= '</div>
        </body>
        </html>';
        //xd("para");
        $pdf = new PDF($texto, 'pdf');
        $pdf->gerarRelatorio();
        
    }
}

?>
