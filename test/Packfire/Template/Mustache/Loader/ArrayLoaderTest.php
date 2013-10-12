<?php
namespace Packfire\Template\Mustache\Loader;

class ArrayLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected static function getNonPublicValue($object, $name)
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    public function testConstructor()
    {
        $loader = new ArrayLoader();
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array(), $templates);
    }

    public function testConstructor2()
    {
        $loader = new ArrayLoader(
            array(
                'test' => 'mic'
            )
        );
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array('test' => 'mic'), $templates);
    }

    public function testSetTemplates()
    {
        $loader = new ArrayLoader();
        $templates = $this->getNonPublicValue($loader, 'templates');
        $this->assertEquals(array(), $templates);

        $loader->templates(
            array(
                'test' => 'mic'
            )
        );
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array('test' => 'mic'), $templates);
        $this->assertEquals('mic', $loader->load('test'));
    }

    public function testAddTemplate()
    {
        $loader = new ArrayLoader();
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array(), $templates);
        $loader->add('test', 'mic');
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array('test' => 'mic'), $templates);
        $this->assertEquals('mic', $loader->load('test'));
    }

    public function testLoad()
    {
        $loader = new ArrayLoader(
            array(
                'test' => 'mic'
            )
        );
        $this->assertEquals('mic', $loader->load('test'));
    }

    /**
     * @expectedException Packfire\Template\Mustache\Loader\TemplateNotFoundException
     */
    public function testLoadFail()
    {
        $loader = new ArrayLoader(
            array(
                'test' => 'mic'
            )
        );
        $loader->load('unexpected');
    }
}
