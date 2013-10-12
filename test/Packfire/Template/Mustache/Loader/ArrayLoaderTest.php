<?php
namespace Packfire\Template\Mustache\Loader;

class ArrayLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $loader = new ArrayLoader();
        $property = new \ReflectionProperty($loader, 'templates');
        $property->setAccessible(true);
        $templates = $property->getValue($loader);

        $this->assertEquals(array(), $templates);
    }

    public function testConstructor2()
    {
        $loader = new ArrayLoader(
            array(
                'test' => 'mic'
            )
        );
        $property = new \ReflectionProperty($loader, 'templates');
        $property->setAccessible(true);
        $templates = $property->getValue($loader);

        $this->assertEquals(array('test' => 'mic'), $templates);
    }

    public function testSetTemplates()
    {
        $loader = new ArrayLoader();

        $property = new \ReflectionProperty($loader, 'templates');
        $property->setAccessible(true);
        $templates = $property->getValue($loader);

        $this->assertEquals(array(), $templates);
        $loader->templates(
            array(
                'test' => 'mic'
            )
        );
        $templates = $property->getValue($loader);

        $this->assertEquals(array('test' => 'mic'), $templates);
        $this->assertEquals('mic', $loader->load('test'));
    }

    public function testAddTemplate()
    {
        $loader = new ArrayLoader();

        $property = new \ReflectionProperty($loader, 'templates');
        $property->setAccessible(true);
        $templates = $property->getValue($loader);

        $this->assertEquals(array(), $templates);
        $loader->add('test', 'mic');
        $templates = $property->getValue($loader);

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
