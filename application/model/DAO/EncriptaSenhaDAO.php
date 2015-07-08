<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EncriptaSenhaDAO
 *
 * @author tisomar
 */
class EncriptaSenhaDAO extends Zend_Db_Table {

    public static function encriptaSenha($cpf, $senha)
	{
		$sql = "SELECT tabelas.dbo.fnEncriptaSenha('" . $cpf . "', '$senha' ) as senha";
                $db = Zend_Registry::get('db');
                $db->setFetchMode(Zend_DB::FETCH_OBJ);
                return $db->fetchAll($sql);
	} // fecha método enviarEmail()

}
?>
