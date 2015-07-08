<?php
/**
 * Classe de seguranчa em geral: sql injection, dentre outros...
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package library
 * @subpackage library.MinC.Seguranca
 * @copyright Љ 2010 - Ministщrio da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Seguranca
{
	/**
	 * Trata os dados vindos de formulсrios
	 * @access public
	 * @static
	 * @param string $variavel
	 * @return string
	 */
	public static function tratarVar($variavel)
	{
		// caso a funчуo get_magic_quotes_gpc() esteja desabilitada, forчa a inserчуo da barra antes das aspas
		$variavel = get_magic_quotes_gpc() ? $variavel : addslashes($variavel);

		// retira as barras antes das aspas
		$variavel = stripslashes($variavel);

		// converte tags html em entidades e retira os espaчos no inэcio e final das variсveis
		return htmlentities(trim($variavel), ENT_QUOTES); // converte aspas simples e duplas
	}  // fecha mщtodo tratarVar()



	/**
	 * Trata os dados vindos via ajax (trata as acentuaчѕes)
	 * @access public
	 * @static
	 * @param string $variavel
	 * @return string
	 */
	public static function tratarVarAjax($variavel)
	{
		// a funчуo utf8_decode() converte uma string com caracteres ISO-8859-1 codificadas com UTF-8 (no caso, dados vindos via AJAX)
		return self::tratarVar(utf8_decode($variavel));
	} // fecha mщtodo tratarVarAjax()



	/**
	 * Trata os dados vindos via ajax (somente)
	 * @access public
	 * @static
	 * @param string $variavel
	 * @return string
	 */
	public static function tratarVarAjaxUFT8($variavel)
	{
		// a funчуo utf8_decode() converte uma string com caracteres ISO-8859-1 codificadas com UTF-8 (no caso, dados vindos via AJAX)
		return utf8_decode($variavel);
	} // fecha mщtodo tratarVarAjaxUFT8()



	/**
	 * Trata os dados para o formato html
	 * @access public
	 * @static
	 * @param string $variavel
	 * @return string
	 */
	public static function tratarVarHTML($variavel)
	{
		return html_entity_decode($variavel, ENT_COMPAT); // irс converter aspas e deixar os apostrofos
	} // fecha mщtodo tratarVarHTML()



	/**
	 * Trata os dados do editor de texto
	 * @access public
	 * @static
	 * @param string $variavel
	 * @return string
	 */
	public static function tratarVarEditor($variavel)
	{
		if (get_magic_quotes_gpc())
			return htmlspecialchars(stripslashes($variavel));
		else
			return htmlspecialchars($variavel);
	} // fecha mщtodo tratarVarEditor()

        /**
	 *  Transforma o valor enviado para base64 e o ebaralha entre o hash gerado para o segredo enviado
	 * @access public
	 * @static
	 * @param string $valor - valor a ser codificado
	 * @param string $segredo (opcional) - valor utilizado para possibilitar que o hash gerado na codificacao do $valor, nunca seja o mesmo
         *                          para decriptografar, o segredo a ser enviado tem que ser o mesmo enviado ao criptografar
	 * @return string
	 */
        public static function encrypt($valor,$segredo='e@75efR!3450otS')
        {
           $novoValor = md5($segredo).base64_encode($valor).base64_encode($segredo);
           return $novoValor;
        }

        /**
	 *  Descriptografa o valor enviado para o metodo encrypt e retorna o valor limpo
	 * @access public
	 * @static
	 * @param string $valor - valor a ser decodificado
	 * @param string $segredo (opcional) - mesmo segredo enviado no momento de criptografar 
	 * @return string
	 */
        public static function dencrypt($valor,$segredo='e@75efR!3450otS')
        {
           $novoValor = substr($valor,strlen(md5($segredo)));
           $pos = strpos($novoValor, base64_encode($segredo));
           $novoValor = substr($novoValor,0,$pos);
           return base64_decode($novoValor);
        }
} // fecha class