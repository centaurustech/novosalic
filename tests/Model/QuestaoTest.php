<?php
/**
 * 
 * @author Caio Lucena <caioflucena@gmail.com>
 * @todo Usar guia de teste para teste de questoes
 */
class QuestaoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var integer
     */
    private $idGuiaTest;

    /**
     * 
     */
    protected function setUp()
    {
        $questaoModel = new QuestaoModel();
        $reflection = new ReflectionObject($questaoModel);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        $table = $property->getValue($questaoModel);
        $select = $table->select()->setIntegrityCheck(false)->from('tbGuia', 'idGuia');
        $guia = $table->fetchRow($select)->toArray();
        $this->idGuiaTest = $guia['idGuia'];
    }

    /**
     * 
     */
    protected function tearDown()
    {
        $questaoModel = new QuestaoModel();
        $result = $questaoModel->pesquisarPorGuiaCategoriaModuloEdital(null, 1);

        $reflection = new ReflectionObject($questaoModel);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        $table = $property->getValue($questaoModel);
        $select = $table->select()->setIntegrityCheck(false)->from('tbQuestao', 'idQuestao')->where('dsQuestao = ?', 'Questao teste');
        $result = $table->fetchAll($select)->toArray();
        foreach ($result as $questao) {
            $table->delete(array('idQuestao = ?' => $questao['idQuestao']));
        }
    }

    /**
     * 
     */
    public function testCadastrar()
    {
        $questaoModel = new QuestaoModel(null, $this->idGuiaTest, 'Questao teste', 1);
        $questaoModel->cadastrar();
        $questao = $questaoModel->toStdClass();
        $this->assertNotNull($questao->questao);
    }

    /**
     * @expectedException Exception 
     * @expectedExceptionMessage Categoria inválida para cadastro de Guia.
     */
    public function testCadastrarValidar()
    {
        $questaoModel = new QuestaoModel();
        $questaoModel->cadastrar();
    }
}