<?php

class PDFCreator {

    /**
     *
     * @var type 
     */
    private $_entrada;
    private $_orientacaoPapel;

    /**
     *
     * @param type $entrada 
     */
    public function __construct($entrada , $orientacao = 'P') {

        $this->_entrada = $entrada;
        $this->_orientacaoPapel = $orientacao;
    }

    /**
     * @param $quebra_linha
     * auto: é o valor padrão. Só coloca a quebra de página se for necessário;
     * always: coloca sempre uma quebra de linha, depois ou antes do elemento;
     * avoid: evita colocar uma quebra de linha antes ou depois;
     * left: insere uma ou duas quebras de página, de modo que se possa assegurar que a seguinte página seja uma página esquerda (page-break-after) 
     * 		ou para assegurar que a página onde se começa o elemento seja uma página esquerda (page-break-before). 
     * 		Imaginemos um livro aberto, que tem páginas à esquerda e à direita para saber ao que se refere uma página esquerda.
	 * right: insere uma ou duas quebras de página, para assegurar que se possa inserir o elemento ao princípio de uma página de direita (page-break-before)
	 * 		ou para assegurar que depois do elemento comece uma página direita (page-break-after).
     */
    public function gerarPdf($quebra_linha = 'avoid') {

        ini_set("memory_limit", "2048M");
        set_time_limit(380);
        error_reporting(0);


        $patterns = array();
        $patterns[] = '/<thead>/is';
        $patterns[] = '/<\/thead>/is';
        $patterns[] = '/<tbody>/is';
        $patterns[] = '/<\/tbody>/is';
        $patterns[] = '/<a.*?>/is';
        $patterns[] = '/–/is';

        $replaces = array();
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '&ndash;';



        $this->_entrada = preg_replace($patterns, $replaces, $this->_entrada);


        $output = '
          <html>
            <head>
                <style type="text/css">
        ';       
        if($this->_orientacaoPapel == 'L'){
            $output .= '
                @page {
                    size:landscape;
                }
                ';
        }
        $output .= '                
                *, p {
                    font-family:"Lucida Grande", Verdana, Arial, sans-serif; 
                    font-size:10px;
                }
                .texto-pequeno {
                    font-size:8px;
                }
                table { border: 1px solid #666666;  border-collapse:collapse; border-color:#ccc; }
                
                td, th { 
                    border: 1px solid #666666; 
                    line-height:13px; 
                    vertical-align:top; 
                    padding:4px; 
                    }                

                th {
                    background:#D0D0D0;
                    text-transform:uppercase;
                    font-weight: bold;
                    
                }
                                
                td.titulo {
                    padding: 3px;
                    font-weight:bold;
                    background:#E5E5E5;
                }
                .pagebreak {
                    page-break-after: '.$quebra_linha.';
                } 
                .zebrar {
                    background:#F3F3F3;
                }
                table, tr, th, td {
                    page-break-before: '.$quebra_linha.';
                    page-break-after: '.$quebra_linha.';
                    page-break-inside: '.$quebra_linha.';
                }
              </style>
            </head>
            <body>
        ';

        $output .= $this->_entrada;
        

        $output .= '
            </body>
          </html>
          ';

        //xd($output);

        //$ua = $_SERVER["SERVER_SOFTWARE"];
        //preg_match('/\((.*?)\)/is', $ua,$so);
        
       $documentRoot = str_replace("/index.php",'', $_SERVER["DOCUMENT_ROOT"].$_SERVER["PHP_SELF"]);


        $nmArquivo = "relatorio_" . date("His").microtime(true);

        //xd($documentRoot.'/public/tmpPDF/' . $nmArquivo);
        

//        if($so[1] == "Win32" || $so[1] == "Win64"){
//            $caminho = 'C:\Arquivos de programas\xampplite\htdocs\integracao\public\tmpPDF\\' . $nmArquivo;
//            $cmd = '"\Arquivos de programas\BrOffice.org\program\soffice.exe" -norestore -nofirststartwizard -nologo -headless -pt PDFCreator "'.$caminho.'.html"';
//        }else{
            $caminho = $documentRoot.'/public/tmpPDF/' . $nmArquivo;
            $cmd = "xhtml2pdf {$caminho}.html {$caminho}.pdf";
//       }



        $fp = fopen($caminho . '.html', 'a');

        if (fwrite($fp, $output) === FALSE) {
            echo "N&atilde;o foi possível escrever no arquivo ";
            exit;
        }


        fclose($fp);


        chmod($caminho . '.html', 0777);


       system($cmd, $rs);

        $ctLoop = 0;
        if ($rs == 0) {
            while (!file_exists($caminho.'.pdf')) {
                if($ctLoop > 15){
                    $error = true;
                    break;
                }
                sleep(1);
                $ctLoop++;
            }

            if($error){
                xd("Erro na gere&ccedil;&atilde;o de PDF");
            }
        }

        if (is_readable($caminho . '.pdf')) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$nmArquivo.'.pdf"');

            // Envia o arquivo para o cliente
            readfile($caminho . '.pdf');

            //unlink($caminho . '.html');
            //unlink($caminho . '.pdf');
        }
    }

}
?>