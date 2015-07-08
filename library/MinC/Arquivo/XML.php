<?php
/**
 * Classe para trabalhar com XML
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package library
 * @subpackage library.MinC.Arquivo
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class XML
{
	/**
	 * Método para gerar o XML de um combo 
	 * que é gerado através de outro via AJAX
	 *
	 * @access public
	 * @static
	 * @param string $tag
	 * @param object $objeto
	 * @return string $xml
	 */
	public static function gerarComboSimplesAJAX($tag, $objeto)
	{
		$gmtDate = gmdate("D, d M Y H:i:s");
		header("Expires: {$gmtDate} GMT");
		header("Last-Modified: {$gmtDate} GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: text/html; charset=ISO-8859-1", true);

		$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		$xml.= "<$tag>\n";

		foreach ($objeto as $obj)
		{
			$xml.= "<item>\n";
			$xml.= "<id>".$obj->id."</id>\n";
			$xml.= "<descricao>".$obj->descricao."</descricao>\n";
			$xml.= "</item>\n";
		}

		$xml.= "</$tag>\n";
		Header("Content-type: application/xml; charset=iso-8859-1");

		return $xml;
	} // fecha método gerarComboSimplesAJAX()

} // fecha class