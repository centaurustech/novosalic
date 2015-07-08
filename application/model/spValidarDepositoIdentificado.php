<?php
/**
 * DAO spValidarDepositoIdentificado
 * @author Jefferson Alessandro
 * @since 22/04/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class spValidarDepositoIdentificado extends GenericModel {

    /* dados da tabela */
    protected $_banco   = "SAC";
    protected $_schema  = "dbo";
    protected $_name    = "spValidarDepositoIdentificado";

    /**
     * Método para executar a SP de movimentação bancária.
     * A mesma verifica se as inconsistências foram corrigidas.
     * @access public
     * @param void
     * @return bool
     */
    public function verificarInconsistencias($idUsuarioLogado) {
        try {
            // executa a sp
            $executar = "EXEC " . $this->_banco . "." . $this->_schema . "." . $this->_name . " ".$idUsuarioLogado;
            return $this->getAdapter()->query($executar);
        }
        catch (Zend_Exception $e) {
            return $e->getMessage();
        }
    } // fecha método verificarInconsistencias()

} // fecha class