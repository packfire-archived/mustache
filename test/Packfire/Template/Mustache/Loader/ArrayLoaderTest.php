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
}
