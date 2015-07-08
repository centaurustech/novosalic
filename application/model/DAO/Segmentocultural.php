<?php

/**
 * Modelo Segmentocultural
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.models
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class Segmentocultural extends Zend_Db_Table {

    protected $_name = 'SAC.dbo.Segmento'; // nome da tabela

    /**
     * Método para buscar os segmentos culturais de uma determinada área
     * @access public
     * @param integer $idArea
     * @return object $db->fetchAll($sql)
     */

    public static function buscar($idArea) {
        $sql = "SELECT S.Codigo AS id, S.Descricao AS descricao ";
        $sql.= "FROM SAC.dbo.Area AS A, SAC.dbo.Segmento AS S ";
        $sql.= "WHERE LEFT(S.Codigo, 1) = A.Codigo ";
        $sql.= "AND A.Codigo = " . $idArea . " ";
        $sql.= "ORDER BY S.Descricao;";

        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = "Erro ao buscar Segmento Cultural: " . $e->getMessage();
        }
        return $db->fetchAll($sql);
    }

    public static function buscarSegmento($idArea) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        $sql = "select Codigo AS id, Segmento AS descricao ";
        $sql.= "from SAC.dbo.vSegmento ";
        $sql.= "where Area = '$idArea'";
        return $db->fetchAll($sql);
    }

// fecha buscar()

    public static function carregarSegmentosArea(StdClass $dados = null) {
        $sql = "select Codigo as codigo, Segmento as descricao from SAC.dbo.vSegmento";
        if($dados){
        	$sql .= " where Area = {$dados->codigo}";
        }
        
        $sql .= " order by 2";

        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = "Erro ao buscar Segmento Cultural: " . $e->getMessage();
        }

        return $db->fetchAll($sql);
    }

}

// fecha class