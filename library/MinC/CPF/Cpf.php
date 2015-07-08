<?php
/*
 * Created on 11/05/2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class Cpf
 {
 	public static function converteCpf($cpf)
 	{
 		$quebra  = explode(".", $cpf); 
		$quebra2 = explode("-", $quebra[2]); 
		$dado    = $quebra[0].$quebra[1].$quebra2[0].$quebra2[1];
		return $dado;
 	}
 }
?>
