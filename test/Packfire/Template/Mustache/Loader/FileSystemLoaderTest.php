<?php
namespace Packfire\Template\Mustache\Loader;

class FileSystemLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected static function getNonPublicValue($object, $name)
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    public function testConstructor()
    {
        $loader = new FileSystemLoader();
        $root = $this->getNonPublicValue($loader, 'root');
        $extension = $this->getNonPublicValue($loader, 'extension');

        $this->assertEquals(getcwd(), $root);
        $this->assertEquals('mustache', $extension);
    }

    public function testConstructor2()
    {
        $loader = new FileSystemLoader(__DIR__);
        $root = $this->getNonPublicValue($loader, 'root');
        $extension = $this->getNonPublicValue($loader, 'extension');

        $this->assertEquals(__DIR__, $root);
        $this->assertEquals('mustache', $extension);
    }

    public function testConstructor3()
    {
        $loader = new FileSystemLoader(__DIR__, array('extension' => 'html'));
        $root = $this->getNonPublicValue($loader, 'root');
        $extension = $this->getNonPublicValue($loader, 'extension');

        $this->assertEquals(__DIR__, $root);
        $this->assertEquals('html', $extension);
    }

    public function testConstructor4()
    {
        $loader = new FileSystemLoader(__DIR__, array('extension' => '.html'));
        $root = $this->getNonPublicValue($loader, 'root');
        $extension = $this->getNonPublicValue($loader, 'extension');

        $this->assertEquals(__DIR__, $root);
        $this->assertEquals('html', $extension);
    }

    public function testConstructor5()
    {
        $loader = new FileSystemLoader(__DIR__, array('extension' => ''));
        $root = $this->getNonPublicValue($loader, 'root');
        $extension = $this->getNonPublicValue($loader, 'extension');

        $this->assertEquals(__DIR__, $root);
        $this->assertEquals('', $extension);
    }

    public function testSetTemplates()
    {
        $loader = new FileSystemLoader();
        $templates = $this->getNonPublicValue($loader, 'templates');
        $this->assertEquals(array(), $templates);

        $loader->templates(
            array(
                'test' => 'mic'
            )
        );

        $templates = $this->getNonPublicValue($loader, 'templates');
        $this->assertEquals(array('test' => 'mic'), $templates);
    }

    public function testAddTemplate()
    {
        $loader = new FileSystemLoader();
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array(), $templates);
        $loader->add('test', 'mic');
        $templates = $this->getNonPublicValue($loader, 'templates');

        $this->assertEquals(array('test' => 'mic'), $templates);
    }

    public function testLoad()
    {
        $loader = new FileSystemLoader(__DIR__);
        $this->assertEquals('My name is {{name}}.', $loader->load('test'));
    }

    /**
     * @expectedException Packfire\Template\Mustache\Loader\TemplateNotFoundException
     */
    public function testLoadFail()
    {
        $loader = new FileSystemLoader(
        );
        $loader->load('unexpected');
    }
}
