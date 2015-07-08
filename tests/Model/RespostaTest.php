<?php
/**
 * 
 * @author Caio Lucena <caioflucena@gmail.com>
 * @todo Usar questao de teste para teste de resposta
 */
class RespostaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var integer
     */
    private $idQuestaoTest;
    private $modelTable;

    /**
     * 
     */
    protected function setUp()
    {
        $respostaModel = new RespostaModel();
        $reflection = new ReflectionObject($respostaModel);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        $table = $property->getValue($respostaModel);
        $select = $table->select()->setIntegrityCheck(false)->from('tbQuestao', 'idQuestao');
        $questao = $table->fetchRow($select)->toArray();
        $this->idQuestaoTest = $questao['idQuestao'];
    }

    /**
     * 
     */
    protected function tearDown()
    {
        $respostaModel = new RespostaModel();
        $result = $respostaModel->pesquisarPorTipoQuestao(null, $this->idQuestaoTest);

        $reflection = new ReflectionObject($respostaModel);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        $table = $property->getValue($respostaModel);
        $select = $table->select()->setIntegrityCheck(false)->from('tbResposta', 'idResposta')->where('dsResposta like ?', 'Resposta Questao%');
        $result = $table->fetchAll($select)->toArray();
        foreach ($result as $resposta) {
            $table->delete(array('idResposta = ?' => $resposta['idResposta']));
        }
    }

    /**
     * 
     */
    public function testCadastrarText()
    {
        $respostaModel = new RespostaModel(null, RespostaModel::TEXT, $this->idQuestaoTest, 'Resposta Questao text');
        $resposta = $respostaModel->toStdClass();
        $this->assertNull($resposta->resposta);
        $respostaModel->cadastrar();
        $resposta = $respostaModel->toStdClass();
        $this->assertNotNull($resposta->resposta);
    }

    /**
     * @expectedException Exception 
     * @expectedExceptionMessage Categoria inválida para cadastro de Guia.
     */
    public function testCadastrarValidar()
    {
        $respostaTextModel = new RespostaTextModel();
        $respostaTextModel->cadastrar();
    }
}
