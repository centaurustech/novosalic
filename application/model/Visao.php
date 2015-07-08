<?php

/**
 * DAO Visao
 * @author emanuel.sampaio - Politec
 * @since 23/08/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class Visao extends GenericModel
{
    /* dados da tabela */

    protected $_banco = 'Agentes';
    protected $_schema = 'dbo';
    protected $_name = 'Visao';
    protected $_primary = 'idVisao';

}
