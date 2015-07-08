<?php

/**
 * @name script_paEncerrarCNIC
 *
 * Script responsavel por conectar com o banco de dados e executar a storage procedure
 * de encerramento da CNIC, que deve ser feito ao fim de cada reuniao da plenaria.
 * Este arquivo deve ser invocado via linha de comando toda vez que a reuniao plenaria
 * da CNIC for encerrada.
 *
 * @since 02/02/2012
 * @version 1.0
 * @package / (deve ficar na raiz da aplicacao, paralelo ao index.php)
 */

    //variaveis
    $host       = "";      //host do servidor de baco
    $usuario    = "";  //usuario do banco
    $senha      = "";  //senha do usuario de bacno
    $banco      = "SAC";        //nome do banco
    $schema     = "dbo";        //schema do banco
    //$procedure  = "pocExecucao"; //nome da procedure (teste)
    $procedure  = "paEncerrarCNIC"; //nome da procedure
    $caminho    = getcwd()."/public/plenaria/"; //caminho para gravar o arquivo de log (a pasta necessita de permissao de escrita)
    $arquivo    = "log_exec_paEncerrarCNIC.txt"; //nome do arquivo de log

    //recupera parametro enviado pela linha de comando
    $arrPar = explode('=',$argv[1]); //recupera unico paramentro enviado (idReuniao)
    $idReuniao = $arrPar[1];

    //****************** CONEXAO COM BANCO DE DADOS ************************************/
    $con = mssql_connect($host,$usuario,$senha) or system("echo 'Em ".date('Y-m-d H:i:s')." : Nao foi possivel a conexao com o servidor. A SP \"paEncerrarCNIC\" nao foi executada.' >> ".$caminho.$arquivo);
    mssql_select_db($banco,$con) or system("echo Em ".date('Y-m-d H:i:s')." : Nao foi possivel selecionar o banco de dados. A SP \"paEncerrarCNIC\" nao foi executada. >> ".$caminho.$arquivo);

    //****************** TENTA EXECUTAR PROCEDURE *************************************/
    try
    {
        // executa a sp
        $sp = "EXEC " . $banco . "." . $schema . "." .$procedure. ' ' . $idReuniao;
        if(mssql_query($sp)){
            system("echo 'Em ".date('Y-m-d H:i:s')." : A SP \"paEncerrarCNIC\" foi executada com sucesso' >> ".$caminho.$arquivo);
        }else{
            system("echo 'Em ".date('Y-m-d H:i:s')." : Erro ao executar SP \"paEncerrarCNIC\"' >> ".$caminho.$arquivo);
        }
    }
    catch (Exception $e)
    {
        system("echo 'Em ".date('Y-m-d H:i:s')." : Erro ao executar SP \"paEncerrarCNIC\"' ".$e->getMessage()." >> ".$caminho.$arquivo);
    }

    //fecha conexao com o banco de dados
    mssql_close();
    die();

?>
