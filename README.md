# PHPi

**INCOMPLETE (but functional) WIP -. If you'd like to help send me a message!**

Event driven bindings for the Raspberry Pi GPIO. Supports A, A+, B, Brev2, B+, 2B, 3B, Compute Module and Pi Zero.

This library interacts (almost) directly with the peripheral registers for maximum functionality and speed.  See note on `mmap/dma`

> As there is no ability to `mmap` in PHP, this has been delegated to a python subprocess.  The python has been kept to an absolute minimum 
> (<25 lines) in terms of complexity to allow flexibility at PHP level.

> **This means that you MUST have python installed alongside PHP for it to function (at all!).** This subprocess is plugable, so it should be easy enough to replace it with a PHP extension down the track.


## Setup

Using composer:

```json
  "require": {
  	"calcinai/phpi": "dev-master"
  }
```

Although it is possible to add this to your own autoloader, it's not recommended as you'll have no control of the dependencies.  If you haven't 
used composer before, I strongly recommend you check it out at https://getcomposer.org

## Usage

All of this code is designed to be run in cli mode, as root to permit the memory access. It is not recommended to try and run this in a synchronous 
nature (namely under apache/nginx) as this would introduce stability and security issues.  See below for more information about webservices.

Minimal example of reading and setting a pin

```php
//If you don't already have an event loop, you'll need to create one to pass in.
$loop = \React\EventLoop\Factory::create();
$board = \Calcinai\PHPi\Factory::create($loop);

$pin = $board->getPin(17) //BCM pin number
             ->setFunction(PinFunction::INPUT)
             ->setPull(Pin::PULL_UP);

//Will be === to Pin::LEVEL_HIGH or Pin::LEVEL_LOW
var_dump($pin->level());

$pin->setFunction(PinFunction::OUTPUT)
$pin->high();
$pin->low();
```

### GPIO

GPIO (input) is the default mode of the pin objects. Alternate functions can be accessed by using the ```->setFunction(PinFunction::x)``` method.  It is
 recommended to use the function names as opposed to `ALT0..5` unless you know exactly what you're doing, as quite a lot are reserved.
A few useful classes are also included for digital interaction.


### PWM

Hardware PWM is supported by this library, and to an extent, so is soft PWM.  As this code runs in the react event loop, it's not practical to 
interact with the ports more than a few hundred times/sec.


### The event loop

One of the original reasons for basing this project on the [react event loop](https://github.com/reactphp/event-loop) was for the other components 
that can be leveraged.  A good example is the websocket server; it will run seamlessly inline with this library to provide *real time, 
bidirectional, non-polling* interaction with the Pi from any modern browser.

See the websocket example included.
