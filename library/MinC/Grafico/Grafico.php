<?php
/** 
 * Description of Grafico
 *
 * @author Danilo Lisboa
 */

ini_set("display_errors", 1);

define("CAMINHO_PCHART", __DIR__."/pChart/pChart");
define("CAMINHO_PCHART_FONT", __DIR__."/pChart/Fonts");

include(CAMINHO_PCHART."/pData.class");
include(CAMINHO_PCHART."/pChart.class");

class Grafico {

    protected $_tipoGrafico;
    protected $_tituloGrafico;
    protected $_tituloEixoX;
    protected $_tituloEixoY;
    protected $_larguraGrafico = 500;
    protected $_alturaGrafico = 300;
    protected $_dados = array();
    protected $_qtdeSeries = 0;
    protected $_tituloDados = array();
    protected $_tituloItens = array();
    protected $_legenda;
    protected $_tema;
    protected $_tamanhoAuto = false;
    protected $_anguloValores = 45;
    protected $_exibirValores = true;
    protected $_modoGrafico = "analitico";
    protected $_margem = 50;
    protected $_corFundo = array("r"=>240, "g"=>240, "b"=>240);
    protected $_corMargem = array("r"=>0, "g"=>0, "b"=>0);
    protected $_corLabels = array("r"=>150, "g"=>150, "b"=>150);
    protected $_corValores = array("r"=>0, "g"=>0, "b"=>0);

    public function  __construct($tipoGrafico="barras") {
        $this->setTipoGrafico($tipoGrafico);
        $this->setTituloGrafico();
        //$this->setTema();
    }

    public function setTema($tema="Minc") {
        switch (strtolower($tema)){
            case "softy":
                $this->_tema = new SoftyTheme();
                break;
            case "universal":
                $this->_tema = new UniversalTheme();
                break;
            case "vivid":
                $this->_tema = new VividTheme();
                break;
            case "rose":
                $this->_tema = new RoseTheme();
                break;
            case "pastel":
                $this->_tema = new PastelTheme();
                break;
            case "orange":
                $this->_tema = new OrangeTheme();
                break;
            case "ocean":
                $this->_tema = new OceanTheme();
                break;
            case "aqua":
                $this->_tema = new AquaTheme();
                break;
            case "green":
                $this->_tema = new GreenTheme();
                break;
            case "minc":
            default:
                $this->_tema = new GreenTheme();
                break;
        }
    }

    public function setTituloGrafico($tituloGrafico="GRAFICO") {
        $this->_tituloGrafico = html_entity_decode($tituloGrafico);
    }

    public function setTipoGrafico($tipoGrafico="barras") {
        $this->_tipoGrafico = $tipoGrafico;
    }

    public function setTituloEixoXY($tituloX, $tituloY){
        $this->_tituloEixoX = html_entity_decode($tituloX);
        $this->_tituloEixoY = html_entity_decode($tituloY);
    }

    public function setTamanho($largura, $altura=null){
        $this->_larguraGrafico = $largura;
        if($altura != null){
            $this->_alturaGrafico = $altura;
        }else{
            $this->_alturaGrafico = $this->_larguraGrafico * 0.6; // Calculando a altura para manter a proporcao
        }
    }

    public function setDados(Array $dados){
        $this->_dados = $dados;
    }

    public function addDados(Array $dados, $titulo=null){
        $this->_dados[] = $dados;
        $this->_qtdeSeries += 1;
        if($titulo){
            $this->_tituloDados[] = $titulo;
        }else{
            $this->_tituloDados[] = count($this->_dados);
        }
    }

    public function setTituloItens(Array $titulo=array()){
        $this->_tituloItens = $titulo;
    }

    public function setLegenda($legenda){
        $this->_legenda = $legenda;
    }

    public function setTamanhoAuto($tamanho=false) {
        $this->_tamanhoAuto = $tamanho;
    }

    public function setAnguloValores($angulo) {
        $this->_anguloValores = $angulo;
    }

    public function setExibirValores($exibir=true) {
        $this->_exibirValores = $exibir;
    }

    public function setMogoGrafico($modo="analitico"){
        $this->_modoGrafico = $modo;
    }

    public function setMargem($margem) {
        $this->_margem = $margem;
    }

    public function setCorFundo($cor) {
        $this->_corFundo = $this->rgb2hex2rgb($cor);
    }

    public function setCorMargem($cor) {
        $this->_corMargem = $this->rgb2hex2rgb($cor);
    }

    public function setCorLabels($cor) {
        $this->_corLabels = $this->rgb2hex2rgb($cor);
    }

    public function setCorValores($cor) {
        $this->_corValores = $this->rgb2hex2rgb($cor);
    }

    public function gerar(){
        if($this->_modoGrafico != "analitico"){
            $arrAux = array();
            foreach($this->_dados as $dados){
                if(is_array($dados)){
                    $somaValores = 0;
                    foreach($dados as $dadoFinal){
                        $somaValores += $dadoFinal;
                    }
                }
                $arrAux[0][] = $somaValores;
            }
            $this->_dados = $arrAux;
            $this->_tituloItens = $this->_tituloDados;
            $this->_tituloDados = null;
            $this->setExibirValores(true);
            $this->_qtdeSeries = 1;
        }

        if($this->_tipoGrafico == "pizza3d"){
            $this->gerarGraficoPizza("pizza3d");
        }elseif($this->_tipoGrafico == "pizza"){
            $this->gerarGraficoPizza("pizza");
        }elseif($this->_tipoGrafico == "linha"){
            $this->gerarGraficoLinhas();
        }else{
            $this->gerarGraficoBarras();
        }
    }

    public function gerarGraficoBarras(){
        if(count($this->_dados[0])<1){
            die("Algum dado deve ser passado. <br>Ex. \$grafico->setDados(array(10,20,30));");
        }
        if(count($this->_dados[0]) != count($this->_tituloItens)){
            die("A quantidade de titulos passados difere da quantidade de valores. Certifique-se de que eles estejam em igual numero.");
        }

        // Montando plotagens com os arrays de dados passados
        $DataSet = new pData();

        // Definindo labels dos eixos
        if(!empty($this->_tituloEixoX)){
            $DataSet->SetXAxisName($this->_tituloEixoX);
            $DataSet->SetYAxisName($this->_tituloEixoY);
        }

        // Montando plotagens com os arrays de dados passados
        for($i=0; $i<count($this->_dados); $i++){
            $DataSet->AddPoint($this->_dados[$i],$this->_tituloDados[$i]);
            $DataSet->AddSerie($this->_tituloDados[$i]);
            //x($this->_tituloItens[$i]);
        }

        // Definindo labels dos dados
        if(count($this->_tituloItens)>0){
            $DataSet->AddPoint($this->_tituloItens,"labels");
            $DataSet->SetAbsciseLabelSerie("labels");
        }

        // Initialise the graph
        $Test = new pChart($this->_larguraGrafico*2,$this->_alturaGrafico*2);
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",8);
        $Test->setGraphArea($this->_margem*3,$this->_margem*2,120*$this->_larguraGrafico/100,130*$this->_alturaGrafico/100-10);
        $Test->drawFilledRoundedRectangle(7,7,150*$this->_larguraGrafico/100,150*$this->_alturaGrafico/100,5,$this->_corFundo["r"],$this->_corFundo["g"],$this->_corFundo["b"]);
        $Test->drawRoundedRectangle(5,5,150*$this->_larguraGrafico/100+3,150*$this->_alturaGrafico/100+3,5,$this->_corMargem["r"],$this->_corMargem["g"],$this->_corMargem["b"]);
        $Test->drawGraphArea(255,255,255,TRUE);
        $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,$this->_corLabels["r"],$this->_corLabels["g"],$this->_corLabels["b"],TRUE,$this->_anguloValores,0,TRUE);
        $Test->drawGrid(4,TRUE);

        // Draw the 0 line
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",10);
        $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
        if($this->_qtdeSeries <= 1 && $this->_exibirValores==true){
            $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),$this->_tituloDados);
        }

        // Draw the limit graph
        $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),.1,100);

        // Finish the graph
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",8);
        $Test->drawLegend(72*$this->_larguraGrafico/100,50,$DataSet->GetDataDescription(),255,249,223,15,15,15);
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",14);
        $Test->drawTitle($this->_larguraGrafico/2-20,30,$this->_tituloGrafico,$this->_corLabels["r"],$this->_corLabels["g"],$this->_corLabels["b"]);
        $Test->Stroke();
    }

    public function gerarGraficoLinhas(){
        if(count($this->_dados[0])<1){
            die("Algum dado deve ser passado. <br>Ex. \$grafico->setDados(array(10,20,30));");
        }
        if(count($this->_dados[0]) != count($this->_tituloItens)){
            die("A quantidade de titulos passados difere da quantidade de valores. Certifique-se de que eles estejam em igual numero.");
        }

        // Montando plotagens com os arrays de dados passados
        $DataSet = new pData();

        // Definindo labels dos eixos
        if(!empty($this->_tituloEixoX)){
            $DataSet->SetXAxisName($this->_tituloEixoX);
            $DataSet->SetYAxisName($this->_tituloEixoY);
        }

        // Montando plotagens com os arrays de dados passados
        for($i=0; $i<count($this->_dados); $i++){
            $DataSet->AddPoint($this->_dados[$i],$this->_tituloDados[$i]);
            $DataSet->AddSerie($this->_tituloDados[$i]);
            //x($this->_tituloItens[$i]);
        }

        // Definindo labels dos dados
        if(count($this->_tituloItens)>0){
            $DataSet->AddPoint($this->_tituloItens,"labels");
            $DataSet->SetAbsciseLabelSerie("labels");
        }

        // Initialise the graph
        $Test = new pChart($this->_larguraGrafico*2,$this->_alturaGrafico*2);
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",8);
        $Test->setGraphArea($this->_margem*3,$this->_margem*2,120*$this->_larguraGrafico/100,130*$this->_alturaGrafico/100-10);
        $Test->drawFilledRoundedRectangle(7,7,150*$this->_larguraGrafico/100,150*$this->_alturaGrafico/100,5,$this->_corFundo["r"],$this->_corFundo["g"],$this->_corFundo["b"]);
        $Test->drawRoundedRectangle(5,5,150*$this->_larguraGrafico/100+3,150*$this->_alturaGrafico/100+3,5,$this->_corMargem["r"],$this->_corMargem["g"],$this->_corMargem["b"]);
        $Test->drawGraphArea(255,255,255,TRUE);
        $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,$this->_corLabels["r"],$this->_corLabels["g"],$this->_corLabels["b"],TRUE,$this->_anguloValores,0,TRUE);
        $Test->drawGrid(4,TRUE);

        // Draw the 0 line
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",10);
        $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
        if($this->_qtdeSeries <= 1 && $this->_exibirValores==true){
            $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),$this->_tituloDados);
        }

        // Draw the limit graph
        $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());

        // Finish the graph
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",8);
        $Test->drawLegend(72*$this->_larguraGrafico/100,50,$DataSet->GetDataDescription(),255,249,223,15,15,15);
        $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",14);
        $Test->drawTitle($this->_larguraGrafico/2-20,30,$this->_tituloGrafico,$this->_corLabels["r"],$this->_corLabels["g"],$this->_corLabels["b"]);
        $Test->Stroke();
    }

    public function gerarGraficoPizza($tipoPizza){
        // Montando plotagens com os arrays de dados passados
        $DataSet = new pData();

        // Definindo labels dos eixos
        if(!empty($this->_tituloEixoX)){
            $DataSet->SetXAxisName($this->_tituloEixoX);
            $DataSet->SetYAxisName($this->_tituloEixoY);
        }

        // Definindo labels dos dados
        if(count($this->_tituloItens)>0) {
            $DataSet->AddPoint($this->_tituloItens,"labels");
            $DataSet->SetAbsciseLabelSerie("labels");
        }
        // Montando plotagens com os arrays de dados passados
        for($i=0; $i<count($this->_dados); $i++){
            $DataSet->AddPoint($this->_dados[$i],$this->_tituloDados[$i]);
            $DataSet->AddSerie($this->_tituloDados[$i]);
            
            // Initialise the graph
            /*$Test = new pChart($this->_larguraGrafico,$this->_alturaGrafico);
            $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",8);
            $Test->setGraphArea($this->_margem,$this->_margem+30,75*$this->_larguraGrafico/100,80*$this->_alturaGrafico/100-15);
            $Test->drawFilledRoundedRectangle(7,7,98*$this->_larguraGrafico/100,98*$this->_alturaGrafico/100,5,$this->_corFundo["r"],$this->_corFundo["g"],$this->_corFundo["b"]);
            $Test->drawRoundedRectangle(5,5,98*$this->_larguraGrafico/100+3,98*$this->_alturaGrafico/100+3,5,$this->_corMargem["r"],$this->_corMargem["g"],$this->_corMargem["b"]);*/

            $Test = new pChart($this->_larguraGrafico*2,$this->_alturaGrafico*2);
            $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",8);
            $Test->setGraphArea($this->_margem+100,$this->_margem+50,120*$this->_larguraGrafico/100,130*$this->_alturaGrafico/100-10);
            $Test->drawFilledRoundedRectangle(7,7,190*$this->_larguraGrafico/100,130*$this->_alturaGrafico/100,5,$this->_corFundo["r"],$this->_corFundo["g"],$this->_corFundo["b"]);
            $Test->drawRoundedRectangle(5,5,190*$this->_larguraGrafico/100+3,130*$this->_alturaGrafico/100+3,5,$this->_corMargem["r"],$this->_corMargem["g"],$this->_corMargem["b"]);

            // Draw the 0 line
            $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",10);

            if($this->_exibirValores == true){
                $auxExibirValores = PIE_VALUES;
            }else{
                $auxExibirValores = PIE_NOLABEL;
            }

            // Draw the limit graph
            if($tipoPizza == "pizza"){
                $Test->drawBasicPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),35*$this->_larguraGrafico/100,55*$this->_alturaGrafico/100-15,25*$this->_larguraGrafico/100,$auxExibirValores,TRUE,50,20,5);
            }else{
                $Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),35*$this->_larguraGrafico/100,55*$this->_alturaGrafico/100-15,25*$this->_larguraGrafico/100,$auxExibirValores,TRUE,50,20,5);
            }
            $Test->drawPieLegend(70*$this->_larguraGrafico/100,35*$this->_alturaGrafico/100-15,$DataSet->GetData(),$DataSet->GetDataDescription(),255,249,223,15,15,15);

            $Test->setFontProperties(CAMINHO_PCHART_FONT."/tahoma.ttf",14);
            $Test->drawTitle($this->_larguraGrafico/2-20,30,$this->_tituloGrafico.": ".$this->_tituloDados[$i],$this->_corLabels["r"],$this->_corLabels["g"],$this->_corLabels["b"]);
            $Test->Render(CAMINHO_PCHART."/../../../../../public/pizza{$i}.png");
            echo "<img src='".$_SERVER['REQUEST_URI']."/../../public/pizza{$i}.png'>";
            $DataSet->removeAllSeries();
        }
    }

    public static function formConfiguracao($baseUrl, $action=null, $btnSubmit=true){
        $html = '
            <link rel="stylesheet" media="screen" type="text/css" href="'.$baseUrl.'/public/colorpicker/css/colorpicker.css" />
            <script type="text/javascript" src="'.$baseUrl.'/public/colorpicker/js/colorpicker.js"></script>

            <script>
                $(document).ready(function(){
                    $(\'#cgCorFundo\').ColorPicker({
                        color: \'#F0F0F0\',
                        onShow: function (colpkr) {
                                $(colpkr).fadeIn(500);
                                return false;
                        },
                        onHide: function (colpkr) {
                                $(colpkr).fadeOut(500);
                                return false;
                        },
                        onChange: function (hsb, hex, rgb) {
                                $("#cgCorFundo").val(\'#\' + hex).next("div").css(\'background-color\', \'#\' + hex);
                        }
                    });
                    $(\'#cgCorMargem\').ColorPicker({
                        color: \'#000000\',
                        onShow: function (colpkr) {
                                $(colpkr).fadeIn(500);
                                return false;
                        },
                        onHide: function (colpkr) {
                                $(colpkr).fadeOut(500);
                                return false;
                        },
                        onChange: function (hsb, hex, rgb) {
                                $("#cgCorMargem").val(\'#\' + hex).next("div").css(\'background-color\', \'#\' + hex);
                        }
                    });
                    $(\'#cgCorLabels\').ColorPicker({
                        color: \'#969696\',
                        onShow: function (colpkr) {
                                $(colpkr).fadeIn(500);
                                return false;
                        },
                        onHide: function (colpkr) {
                                $(colpkr).fadeOut(500);
                                return false;
                        },
                        onChange: function (hsb, hex, rgb) {
                                $("#cgCorLabels").val(\'#\' + hex).next("div").css(\'background-color\', \'#\' + hex);
                        }
                    });
                    $(\'#cgCorValores\').ColorPicker({
                        color: \'#000000\',
                        onShow: function (colpkr) {
                                $(colpkr).fadeIn(500);
                                return false;
                        },
                        onHide: function (colpkr) {
                                $(colpkr).fadeOut(500);
                                return false;
                        },
                        onChange: function (hsb, hex, rgb) {
                                $("#cgCorValores").val(\'#\' + hex).next("div").css(\'background-color\', \'#\' + hex);
                        }
                    });
                    $("#cgLargura").numeric();
                    $("#cgAltura").numeric();
                    $("#cgMargem").numeric();
                    $("#cgAnguloValores").numeric();

                    $(".cgSalvar").click(function(){
                        $("#btn_configurar_grafico").show();
                        $("#confGrafico").toggle();
                    });
                });
            </script>
        ';
        if($action){
            $html .= '<form action="'.$action.'" method="post" name="frmGraficoResumo" id="frmGraficoResumo" target="grafico">';
        }
        $html .= '
            <table class="tabela" id="confGrafico" style="width: 40%; display: none;">
                <tr>
                    <th>Configura&ccedil;&atilde;o dos Gráficos</th>
                    <th align="right"><input class="btn_cancelar cgSalvar" value=""/></th>
                </tr>
                <tr>
                    <td width="40%">Tipo Gráfico</td>
                    <td>
                        <select name="cgTipoGrafico" class="input_simples w200">
                            <option value="barra">Barra</option>
                            <option value="linha">Linha</option>
                            <option value="pizza3d">Pizza 3D</option>
                            <option value="pizza">Pizza</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Modo de gera&ccedil;&atilde;o</td>
                    <td>
                        <select name="cgModo" class="input_simples w200">
                            <option value="analitico">Analítico</option>
                            <option value="sintetico">Sintético</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Exibir Valores</td>
                    <td>
                        <select name="cgExibirValores" class="input_simples w200">
                            <option value="sim">Sim</option>
                            <option value="nao">N&atilde;o</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Angulo dos Valores</td>
                    <td>
                        <input name="cgAnguloValores" id="cgAnguloValores" value="45" maxlength="3" class="input_simples w200"/>
                    </td>
                </tr>
                <tr>
                    <td>Largura em Pixels</td>
                    <td>
                        <input name="cgLargura" id="cgLargura" value="500" maxlength="3" class="input_simples w200"/>
                    </td>
                </tr>
                <tr>
                    <td>Altura em Pixels</td>
                    <td>
                        <input name="cgAltura" id="cgAltura" value="300" maxlength="3"  class="input_simples w200"/>
                    </td>
                </tr>
                <tr>
                    <td>Margem em Pixels</td>
                    <td>
                        <input name="cgMargem" id="cgMargem" value="50" maxlength="2"  class="input_simples w200"/>
                    </td>
                </tr>
                <tr>
                    <td>Manter Aspecto</td>
                    <td>
                        <select name="cgManterAspecto" class="input_simples w200">
                            <option value="sim">Sim</option>
                            <option value="nao">N&atilde;o</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Cor de Fundo</td>
                    <td>
                        <input type="text" name="cgCorFundo" id="cgCorFundo" class="input_simples w200" padrao="#F0F0F0" value="#F0F0F0" readonly style=" float: left;"/>
                        <div class="mostradorCor" style="border:1px #000000 solid; width: 15px; height: 15px; background-color: #F0F0F0; float: left; vertical-align: middle;"></div>
                    </td>
                </tr>
                <tr>
                    <td>Cor Margens</td>
                    <td>
                        <input type="text" name="cgCorMargem" id="cgCorMargem" class="input_simples w200" padrao="#000000" value="#000000" readonly style=" float: left;"/>
                        <div class="mostradorCor" style="border:1px #000000 solid; width: 15px; height: 15px; background-color: #000000; float: left; vertical-align: middle;"></div>
                    </td>
                </tr>
                <tr>
                    <td>Cor Labels</td>
                    <td>
                        <input type="text" name="cgCorLabels" id="cgCorLabels" class="input_simples w200" padrao="#969696" value="#969696" readonly style=" float: left;"/>
                        <div class="mostradorCor" style="border:1px #000000 solid; width: 15px; height: 15px; background-color: #969696; float: left; vertical-align: middle;"></div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td>Cor Valores</td>
                    <td>
                        <input type="text" name="cgCorValores" id="cgCorValores" class="input_simples w200" padrao="#000000" value="#000000" readonly style=" float: left;"/>
                        <div class="mostradorCor" style="border:1px #000000 solid; width: 15px; height: 15px; background-color: #000000; float: left; vertical-align: middle;"></div>
                    </td>
                </tr>
                -->
                <tr>
                    <td colspan="2" align="center">';
                    if($btnSubmit){
                        $html .= '<input type="button" class="btn_gerar_grafico" value="" onclick="$(\'#frmGraficoResumo\').submit();"/>';
                    }
                    $html .= '
                        <input type="reset" class="btn_limpar" value="" onclick="$(\'.mostradorCor\').each(function(){ $(this).css(\'background-color\', $(this).prev(\'input\').attr(\'padrao\')) });"/>
                        <input class="btn_salvar cgSalvar" value=""/>
                    </td>
                </tr>
            </table>
        ';
        if($action){
            $html .= '</form>';
        }
        return $html;
    }

    public function configurar(Array $parametros){
        if($parametros["cgLargura"]){
            if($parametros["cgAltura"] && $parametros["cgManterAspecto"] != "sim"){
                $this->setTamanho($parametros["cgLargura"],$parametros["cgAltura"]);
            }else{
                $this->setTamanho($parametros["cgLargura"]);
            }
        }

        if($parametros["cgAnguloValores"]){
            $this->setAnguloValores($parametros["cgAnguloValores"]);
        }

        if($parametros["cgExibirValores"] == "sim"){
            $this->setExibirValores(true);
        }else{
            $this->setExibirValores(false);
        }

        if($parametros["cgMargem"] && $parametros["cgMargem"]>50){
            $this->setMargem($parametros["cgMargem"]);
        }

        if($parametros["cgCorFundo"]){
            $this->setCorFundo($parametros["cgCorFundo"]);
        }

        if($parametros["cgCorMargem"]){
            $this->setCorMargem($parametros["cgCorMargem"]);
        }

        if($parametros["cgCorLabels"]){
            $this->setCorLabels($parametros["cgCorLabels"]);
        }

        if($parametros["cgModo"] != "analitico"){
            $this->setMogoGrafico($parametros["cgModo"]);
        }

//        if($parametros["cgCorValores"]){
//            $this->setCorValores($parametros["cgCorValores"]);
//        }
    }

    /*
     * Este metodo aceita valores hexadecimais(#EFEFEF) ou valores RGB(255 255 255 ou 255.255.255 ou 255,255,255)
     */
    public function rgb2hex2rgb($c) {
        if(!$c) return false;
        $c = trim($c);
        $out = false;
        if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $c)) {
            $c = str_replace('#','', $c);
            $l = strlen($c) == 3 ? 1 : (strlen($c) == 6 ? 2 : false);

            if($l) {
                unset($out);
                $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,1*$l));
                $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 1*$l,1*$l));
                $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 2*$l,1*$l));
            }else $out = false;

        }elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $c)) {
            $spr = str_replace(array(',',' ','.'), ':', $c);
            $e = explode(":", $spr);
            if(count($e) != 3) return false;
            $out = '#';
            for($i = 0; $i<3; $i++)
                $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i]));

            for($i = 0; $i<3; $i++)
                $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i];

            $out = strtoupper($out);
        }else $out = false;

        return $out;
    }
}
?>
