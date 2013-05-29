![Packfire Mustache](http://i.imgur.com/HQo3a.png)

Packfire Mustache is a lightweight Mustache implementation for Packfire. Prior to the adoptation of Mustache, Packfire has used a simple token-replace templating engine that uses single curly braces to indicate tokens.

    <h1>
      {title}
    </h1>
    <p>{message}</p>

However, the simple templating parser is way too primitive and does not support nesting and listing like Mustache does. Hence, Packfire adopted the Mustache templating engine as its default templating engine.

However, the [PHP Mustache implementation](https://github.com/bobthecow/mustache.php) by [bobthecow](https://github.com/bobthecow) was found to be too heavy and thus I  decided to come up with our own. Mustache has been tested against the provided spec tests.

Packfire Mustache uses double curly braces to indicate tokens, supports escaping by default and supports nested block tokens:

    Hello {{name}}
    You have just won ${{value}}!
    {{#in_ca}}
        Well, ${{taxed_value}}, after taxes.
    {{/in_ca}}

To find out more about how Packfire Mustache works you can refer to the [original Mustache manual](http://mustache.github.com/mustache.5.html) as Mustache is designed to be platform-independent and cross-platform compatible.

##Installation

[Packfire Mustache](https://packagist.org/packages/packfire/mustache) can be installed via [Composer](https://getcomposer.org/):

    {
		"require": {
			"packfire/mustache": "1.0.*"
		}
	}

Then run the Composer installation command:

    $ composer install

##Usage

A quick example:

	use Packfire\Template\Mustache\Mustache;

    $m = new Mustache('Hello {{planet}}!');
    echo $m->parameters(array('planet' => 'World'))->render();
    // "Hello World!"

And a more in-depth example--this is the canonical Mustache template:

    Hello {{name}}
    You have just won ${{value}}!
    {{#in_ca}}
        Well, ${{taxed_value}}, after taxes.
    {{/in_ca}}

Along with the associated `Mustache` class:

	use Packfire\Template\Mustache\Mustache;

    class Chris extends Mustache {
        public $name = "Chris";
        public $value = 10000;
        
        public function taxed_value() {
            return $this->value - ($this->value * 0.4);
        }
        
        public $in_ca = true;
    }


Render it like so:

    $chris = new Chris;
    echo $chris->template($template)->render();

Here's the same thing, a different way:

Create a view object--which could also be an associative array, but those don't do functions quite as well:

    class Chris {
        public $name = "Chris";
        public $value = 10000;
        
        public function taxed_value() {
            return $this->value - ($this->value * 0.4);
        }
        
        public $in_ca = true;
    }


And render it:

	use Packfire\Template\Mustache\Mustache;

    $chris = new Chris();
    $m = new Mustache();
    echo $m->template($template)->parameters($chris)->render();

