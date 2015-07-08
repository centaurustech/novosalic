<?php 
/**
 * DAO vwAnexarComprovantes
 * @since 17/12/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class vwAnexarComprovantes extends GenericModel {

    /* dados da tabela */
    protected $_banco  = 'SAC';
    protected $_schema = 'dbo';
    protected $_name   = 'vwAnexarComprovantes';


    public function excluirArquivo($idArquivo) {
        $where = "idArquivo = " . $idArquivo;
        return $this->delete($where);
    }

} // fecha class