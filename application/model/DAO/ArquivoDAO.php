<?php

/**
 * DAO Arquivo
 * @author Equipe RUP - Politec
 * @since 28/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class ArquivoDAO extends Zend_Db_Table
{
    /* dados da tabela */

    protected $_schema = "";
    protected $_name = "BDCORPORATIVO.scCorp.tbArquivo";
    protected $_primary = "idArquivo";

    /**
     * Método para cadastrar informações dos arquivos
     * @access public
     * @static
     * @param array $dados
     * @return bool
     */
    public static function cadastrar($dados)
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        $cadastrar = $db->insert("BDCORPORATIVO.scCorp.tbArquivo", $dados);

        return $cadastrar ? true : false;
    }

    /**
     * Método para alterar informações dos arquivos
     * @access public
     * @static
     * @param array $dados
     * @param integer $id
     * @return bool
     */
    public static function alterar($dados, $id)
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        $where = "idArquivo = $id";
        $alterar = $db->update("BDCORPORATIVO.scCorp.tbArquivo", $dados, $where);

        return $alterar ? true : false;
    }

    /**
     * 
     */
    public static function deletar($idArquivo)
    {
        Zend_Registry::get('db')->exec("delete from BDCORPORATIVO.scCorp.tbArquivoImagem WHERE idArquivo = {$idArquivo}");
        Zend_Registry::get('db')->exec("delete from BDCORPORATIVO.scCorp.tbArquivo WHERE idArquivo = {$idArquivo}");
    }

    /**
     * Método para verificar se o arquivo existe (pelo hash)
     * @access public
     * @static
     * @param string $dsHash
     * @return object || bool
     */
    public static function verificarHash($dsHash)
    {
        $sql = "SELECT * FROM BDCORPORATIVO.scCorp.tbArquivo WHERE dsHash = '$dsHash'";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

    /**
     * Método para buscar o id do último arquivo cadastrado
     * @access public
     * @static
     * @param void
     * @return object || integer
     */
    public static function buscarIdArquivo()
    {
        $sql = "SELECT MAX(idArquivo) AS id FROM BDCORPORATIVO.scCorp.tbArquivo";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

}

/**
 * DAO ArquivoImagem
 * @author Equipe RUP - Politec
 * @since 28/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class ArquivoImagemDAO extends Zend_Db_Table
{
    /* dados da tabela */

    protected $_schema = "";
    protected $_name = "BDCORPORATIVO.scCorp.tbArquivoImagem";
    protected $_primary = "idArquivo";

    /**
     * Método para cadastrar os binários dos arquivos
     * @access public
     * @static
     * @param array $dados
     * @return object || bool
     */
    public static function cadastrar($dados)
    {
        /* @var $db Zend_Db_Adapter_Sqlsrv */
        $db = Zend_Registry::get('db');
        $dados['biArquivo'] = new Zend_Db_Expr("CONVERT(varbinary(MAX),{$dados['biArquivo']})");
        return $db->insert('BDCORPORATIVO.scCorp.tbArquivoImagem', $dados);
    }

    /**
     * Método para alterar os binários dos arquivos
     * @access public
     * @static
     * @param array $dados
     * @param integer $id
     * @return object || bool
     */
    public static function alterar($dados, $id)
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        $sql = "UPDATE BDCORPORATIVO.scCorp.tbArquivoImagem SET 
            biArquivo = {$dados['biArquivo']} WHERE idArquivo = $id";

        return $db->fetchAll($sql);
    }
}
