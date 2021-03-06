<?php

/**
 * @package    calcinai/phpi
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Calcinai\PHPi\External;

use Calcinai\PHPi\Pin;
use Calcinai\PHPi\Traits\EventEmitterTrait;

class Button {

    use EventEmitterTrait;

    /**
     * @var Pin
     */
    private $pin;

    /**
     * @var \React\EventLoop\LibEvLoop|\React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var bool
     */
    private $active_high;

    /**
     * @var float
     */
    private $press_period;

    /**
     * @var int
     */
    private $hold_period;


    const DEFAULT_HOLD_PERIOD = 1;
    const DEFAULT_PRESS_PERIOD = 0.05;

    const EVENT_PRESS   = 'press';
    const EVENT_HOLD    = 'hold';
    const EVENT_RELEASE = 'release';


    public function __construct(Pin $pin, $active_high = true, $press_period = self::DEFAULT_PRESS_PERIOD, $hold_period = self::DEFAULT_HOLD_PERIOD) {

        $this->pin = $pin;
        $this->loop = $pin->getBoard()->getLoop();
        $this->active_high = $active_high;

        $pin->setFunction(Pin\PinFunction::INPUT);
        $this->press_period = $press_period;
        $this->hold_period = $hold_period;
    }


    /**
     * Internal function for dealing with a press (high or low) event on the pin
     */
    private function onPinPressEvent(){

        //Mainly just connecting up events here

        $press_timer = $this->loop->addTimer($this->press_period, function() {
            $this->emit(self::EVENT_PRESS);
        });

        $hold_timer = $this->loop->addTimer($this->hold_period, function() {
            $this->emit(self::EVENT_HOLD);
        });


        $release_event = $this->active_high ? Pin::EVENT_LOW : Pin::EVENT_HIGH;

        $this->pin->once($release_event, function() use(&$press_timer, &$hold_timer){
            $press_timer->cancel();
            $hold_timer->cancel();

            $this->emit(self::EVENT_RELEASE);
        });
    }


    public function eventListenerAdded(){
        //Only interested in the first event added, no advantage to only firing the onces that are being listened to
        if($this->countListeners() !== 1){
            return;
        }

        //Do it like this so it can be hidden from userspace
        $press_event = $this->active_high ? Pin::EVENT_HIGH : Pin::EVENT_LOW;
        $this->pin->on($press_event, function(){ $this->onPinPressEvent(); });
    }

    public function eventListenerRemoved(){
        //Only interested in the last event removed
        if($this->countListeners() !== 0){
            return;
        }
        $this->pin->removeAllListeners();
    }


}