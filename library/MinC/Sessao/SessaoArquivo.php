<?php
/*
* Created on 03/05/2010
*
* To change the template for this generated file go to
* Window - Preferences - PHPeclipse - PHP - Code Templates
*/

class SessaoArquivo
{

	private static $instance;
	
	public $namespace = null;
	
	// Contrutor da sessуo, responsсvel pela inicializaчуo
	private function __construct()
	{
		Zend_Session::start();

		$this->namespace = new Zend_Session_Namespace('arquivo');
		
		if( !isset( $this->namespace->initialized ) )
		{
			Zend_Session::regenerateId();

			$this->namespace->initialized = true;
		}
	}

	// Singleton para verificar se sessуo jс foi inicializada
	public static function getInstance()
	{
		if( !isset( self::$instance ) )
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	// Recebe os valores da sessуo
	public function getSessVar( $var , $default = null )
	{

		if( isset( $this->namespace->$var ) )
		{
			return $this->namespace->$var;
		}
		else
		{
			return $default;
		}
	}

	// Seta os valores na sessуo
	public function setSessVar( $var , $value )
	{
		if( !empty( $var ) && !empty( $value ) )
		{
			$this->namespace->$var = $value;
		}
	}

	// Mata a sessуo arquivo
	public static function emptySess()
	{
		
		Zend_Session::namespaceUnset('arquivo');
	}

}

?>