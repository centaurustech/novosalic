<?php
/**
 * 
 * @author Caio Lucena <caioflucena@gmail.com>
 */
class TipoRespostaTest extends PHPUnit_Framework_TestCase
{
    /**
     * 
     */
    public function testPesquisar()
    {
        $this->markTestSkipped('Confirmar id no banco.');
        $tipoRespostaModel = new TipoRespostaModel();
        $result = $tipoRespostaModel->pesquisar();
        foreach ($result as $tipoResposta) {
            switch ($tipoResposta['idTpResposta']) {
                case 1: $this->assertEquals('Texto (uma linha)', $tipoResposta['dsTpResposta']); break;
                case 2: $this->assertEquals('Texto (múltiplas linhas)', $tipoResposta['dsTpResposta']); break;
                case 3: $this->assertEquals('Escolha única', $tipoResposta['dsTpResposta']); break;
                case 4: $this->assertEquals('Escolha múltipla', $tipoResposta['dsTpResposta']); break;
                case 5: $this->assertEquals('Lista', $tipoResposta['dsTpResposta']); break;
                case 6: $this->assertEquals('Anexo', $tipoResposta['dsTpResposta']); break;
            }
        }
    }
}