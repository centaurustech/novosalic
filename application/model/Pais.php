<?php
/**
 * DAO Pais
 * @author emanuel.sampaio <emanuelonline@gmail.com>
 * @since 18/04/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */

class Pais extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "AGENTES";
	protected $_schema  = "dbo";
	protected $_name    = "Pais";
} // fecha class