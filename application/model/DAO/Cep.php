<?php
/**
 * Modelo Cep
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @package application
 * @subpackage application.models
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Cep
{
	/**
	 * Método para buscar os dados do CEP (efetua a busca no web service)
	 * @access public
	 * @static
	 * @param integer $cep
	 * @return string $retorno
	 */
	public static function buscar($cep)
	{
		ini_set("allow_url_fopen", "On"); // função habilitada
		ini_set("allow_url_include", "On"); // função habilitada

		$resultado = @file_get_contents('http://republicavirtual.com.br/web_cep.php?cep='.urlencode($cep).'&formato=query_string');

		if (!$resultado)
		{
			$resultado = "&resultado=0&resultado_txt=erro+ao+buscar+cep";
		}

		ini_set("allow_url_fopen", "Off"); // função desabilitada
		ini_set("allow_url_include", "Off"); // função desabilitada

		parse_str($resultado, $retorno);

		return $retorno;
	} // fecha buscar()



	/**
	 * Método para buscar o cep no banco de dados
	 * @access public
	 * @static
	 * @param integer $cep
	 * @return string $retorno
	 */
	public static function buscarCepDB($cep)
	{
		$sql = "SELECT 
					LTRIM(RTRIM(lor.cdCep)) AS cep,
					LTRIM(RTRIM(
					 LTRIM(RTRIM(CAST(ISNULL(lor.nmLogradouro, '')   AS VARCHAR))) + ' ' +  
					 LTRIM(RTRIM(CAST(ISNULL(lor.nrLote, '')         AS VARCHAR))) + ' ' +
					 LTRIM(RTRIM(CAST(ISNULL(lor.dsComplemento1, '') AS VARCHAR))) + ' ' +
					 LTRIM(RTRIM(CAST(ISNULL(lor.cdComplemento1, '') AS VARCHAR))) + ' ' +
					 LTRIM(RTRIM(CAST(ISNULL(lor.dsComplemento2, '') AS VARCHAR))) + ' ' +
					 LTRIM(RTRIM(CAST(ISNULL(lor.cdComplemento2, '') AS VARCHAR)))
					)) AS logradouro, 
					LTRIM(RTRIM(lor.nmTipoLogradouro)) AS tipo_logradouro,
					LTRIM(RTRIM(bro.nmBairro)) AS bairro,
					LTRIM(RTRIM(loc.nmLocalidade)) AS cidade,
					LTRIM(RTRIM(loc.cdUf)) AS uf,

					-- busca de acordo com a cidade e a sigla do estado
					(SELECT TOP 1 cid.idMunicipioIBGE 
						FROM AGENTES.dbo.UF uf, AGENTES.dbo.Municipios cid -- busca o código cidade
						WHERE uf.idUF = cid.idUFIBGE 
							AND cid.Descricao = loc.nmLocalidade
							AND uf.Sigla = loc.cdUf) AS idCidadeMunicipios,

					-- busca de acordo com a cidade e a sigla do estado
					(SELECT TOP 1 cid.Descricao 
						FROM AGENTES.dbo.UF uf, AGENTES.dbo.Municipios cid -- busca pela cidade
						WHERE uf.idUF = cid.idUFIBGE 
							AND cid.Descricao = loc.nmLocalidade
							AND uf.Sigla = loc.cdUf) AS dsCidadeMunicipios,

					-- busca de acordo com a sigla do estado
					(SELECT TOP 1 cid.idMunicipioIBGE 
						FROM AGENTES.dbo.UF uf, AGENTES.dbo.Municipios cid -- busca o código da cidade pelo uf
						WHERE uf.idUF = cid.idUFIBGE AND uf.Sigla = loc.cdUf) AS idCidadeUF,

					-- busca de acordo com a sigla do estado
					(SELECT TOP 1 cid.Descricao 
						FROM AGENTES.dbo.UF uf, AGENTES.dbo.Municipios cid -- busca pelo uf
						WHERE uf.idUF = cid.idUFIBGE AND uf.Sigla = loc.cdUf) AS dsCidadeUF

				FROM 
					--BDCORPORATIVO.scDNE.tbLocalidade loc 
					--INNER JOIN BDCORPORATIVO.scDNE.tbLogradouroUf lor 
					BDDNE.scDNE.tbLocalidade loc 
					LEFT JOIN BDDNE.scDNE.tbLogradouroUf lor
						ON lor.nrLocalidade = loc.nrLocalidade
					LEFT JOIN BDCORPORATIVO.scDNE.tbBairro bro 
						ON bro.nrBairro = lor.nrInicioBairro 
						OR bro.nrBairro = lor.nrFimBairro
                                WHERE lor.cdCep = '$cep' OR loc.cdCep = '$cep' ";

			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_ASSOC);

			return $db->fetchRow($sql);
	} // fecha método buscarCepDB()

} // fecha class