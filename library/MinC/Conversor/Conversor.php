<?php 

/**
 * Description of Conversor
 *
 * @author 03051368105
 */
abstract class Conversor {

   final public static function converterBytes($quantidadeBytes) {

	$unidades = array('', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$tamanho = 0;
        $precisao = 2;

	do {
		$quantidadeBytes /= 1024;
		$tamanho++;
	} while ($quantidadeBytes > 1024);

	return sprintf("%1.{$precisao}f%s", $quantidadeBytes, " ".$unidades[$tamanho]);
    }

    final public static function iso88591ParaUTF8($stringISO_8859_1){

        return utf8_encode($stringISO_8859_1);

    }

    final public static function utf8ParaIso88591($string_UTF_8){

        return utf8_decode($string_UTF_8);

    }

    final public static function utf8ParaIso88591_Array($array){

        foreach ($array as $key => $value){

            $array[$key] = utf8_decode($value);

        }

        return $array;

    }

    final public static function iso88591ParaUtf8_Array($array){

        foreach ($array as $key => $value){

            $array[$key] = utf8_encode($value);

        }

        return $array;

    }


    final public static function jsonEncodeParaIso88591RetornaArray($resp){
        $retorno = '';
        foreach($resp as $r){
            if($retorno!=''){
                $retorno.=',';
            }
            $retorno.= Conversor::jsonEncodeParaIso88591($r);
        }
        return utf8_encode('['.$retorno.']');
    }
    final public static function jsonEncodeParaIso88591($array){
        if(is_array($array) || is_object($array)){
            return self::jsonComplemento($array);
        }
        else{
            return false;
        }
    }
    public static function jsonComplemento($array){
        $varJson = '';
        foreach ($array as $key=>$valor){
            if($varJson!='')
                $varJson .= ',';
            if(is_array($valor))
                $varJson .= '"'.$key.'":'.self::jsonComplemento($valor);
            else
                $varJson .= '"'.$key.'":"'.$valor.'"';
        }
        $varJson    =   '{'.$varJson.'}';
        //print_r($varJson);die;
        return $varJson;
    }

    final public static function stringParaASCII($string){

        $stringASCII = NULL;

        $array = Conversor::stringParaArrayDeString($string);

        for($x = 0; $x < count($array); $x++){

            $stringASCII .= ord($array[$x]);
        }

        return $stringASCII;

    }

    final private static function stringParaArrayDeString($string){

        $arrayString = str_split($string, 1);

        return $arrayString;

    }

}

?>
