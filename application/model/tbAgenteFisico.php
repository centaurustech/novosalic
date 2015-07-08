<?php
/**
 * DAO tbAgenteFisico
 * @since 28/11/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbAgenteFisico extends GenericModel {

    protected $_banco  = "AGENTES";
    protected $_schema = "dbo";
    protected $_name   = "tbAgenteFisico";


    /**
     * Método para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o último id cadastrado)
     */
    public function cadastrarDados($dados) {
        return $this->insert($dados);
    } // fecha método cadastrarDados()


    /**
     * Método para alterar
     * @access public
     * @param array $dados
     * @param integer $where
     * @return integer (quantidade de registros alterados)
     */
    public function alterarDados($dados, $where) {
        $where = "idAgente = " . $where;
        return $this->update($dados, $where);
    } // fecha método alterarDados()



} // fecha class