<?php 
/**
 * DAO vwUsuariosOrgaosGrupos
 * @since 13/09/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class vwUsuariosOrgaosGrupos extends GenericModel {

    /* dados da tabela */
    protected $_banco  = 'TABELAS';
    protected $_schema = 'dbo';
    protected $_name   = 'vwUsuariosOrgaosGrupos';
    protected $_primary = 'usu_codigo';
    
} // fecha class