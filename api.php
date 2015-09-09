<?php
/**
 * Created by Taras Kostyuk
 * This file solve the test task
 * I try to do this in SOLID way pls dont reproach if didnt do as you expect
 * But in this case this system can be more flexible as I think so
 */



/**
 * Class Ticket
 * Create a ticket class for appropriate vehicle
 */
class Ticket
{
    public function __construct(Transport $transport)
    {
        $this->vehicle = $transport;
    }

    public function setPlace(Seat $place)
    {
        $this->vehicle->setSeat($place);
    }
}

/**
 * Class Way
 * Set the way
 */
class Way
{
    public $A;
    public $B;

    public function __construct($A, $B)
    {
        $this->A = $A;
        $this->B = $B;
    }

    public function existStartPoint($A)
    {
        return $this->A === $A;
    }

    public function existEndPoint($B)
    {
        return $this->B === $B;
    }

    public function getStartPoint()
    {
        return $this->A;
    }

    public function getEndPoint()
    {
        return $this->B;
    }

    public function __toString()
    {
        return "from $this->A to $this->B";
    }
}

/**
 * Class Seat
 * Set a params of Seat
 * I dont wrote all params because it's not necessary but can be extensible
 */
class Seat
{
    public function __construct($type = 'normal', $number)
    {
        $this->type = $type;
        $this->number = $number;
    }
}

/**
 * Class Baggage
 * Set a params of baggage
 * I dont wrote all params because it's not necessary but can be extensible
 */
class Baggage
{
    public function __construct($weight)
    {
        $this->weight = $weight;
    }
}

/**
 * Class Gate
 * Set a params of gate in airport
 * I dont wrote all params because it's not necessary but can be extensible
 */
class Gate
{
    public function __construct($number)
    {
        $this->number = $number;
    }
}

/**
 * Class Transport
 * Set a basic info of vehicle
 * I dont wrote all params because it's not necessary but can be extensible
 */
class Transport
{
    public $number;
    public $seat;
    public $way;
    public $baggage;

    public function __construct(Way $way)
    {
        $this->way = $way;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function setSeat(Seat $seat)
    {
        $this->seat = $seat;
    }

    public function setBaggage(Baggage $baggage)
    {
        $this->baggage = $baggage;
    }
}

/**
 * Class Bus
 * Extend from vehicle and can include special params
 */
class Bus extends Transport
{
}

/**
 * Class Train
 * Extend from vehicle and can include special params
 */
class Train extends Transport
{
}

/**
 * Class Plain
 * Extend from vehicle and can include special params
 */
class Plain extends Transport
{
    public $gate;

    public function setGate(Gate $gate)
    {
        $this->gate = $gate;
    }
}

/**
 * Class TripManager
 * Manager by trip and do all needed calculation
 */
class TripManager
{
    private $strategy;
    private $tickets;
    private $way;

    public function __construct(StrategyCalculation $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setPartOfWay(Ticket $ticket)
    {
        $this->tickets[] = $ticket;
        $this->strategy->setPoint($ticket->vehicle->way->getStartPoint());
        $this->strategy->setPoint($ticket->vehicle->way->getEndPoint(), 0);
    }

    public function calculateWay()
    {
        $start = $this->strategy->getStartPoint();
        $end = $this->strategy->getEndPoint();

        $ticketCount = count($this->tickets) - 2;

        $wayStart = $this->getTicketByWay($start);
        $nextStart = $wayStart->vehicle->way->getEndPoint();
        $this->ways[] = $wayStart;

        for($i=0; $i < $ticketCount; $i++) {
            $wayNext = $this->getTicketByWay($nextStart);
            $nextStart = $wayNext->vehicle->way->getEndPoint();
            $this->ways[] = $wayNext;
        }

        $this->ways[] = $this->getTicketByWay($end, 0);

        return $this;
    }

    /**
     * Simple way to get ticket
     */
    private function getTicketByWay($city, $start = 1)
    {
        if($start) {
            foreach($this->tickets as $ticket) {
                if($ticket->vehicle->way->existStartPoint($city)) {
                    return $ticket;
                }
            }
        } else {
            foreach($this->tickets as $ticket) {
                if($ticket->vehicle->way->existEndPoint($city)) {
                    return $ticket;
                }
            }
        }
    }

    public function __toString()
    {
        $str = '';

        foreach($this->ways as $way) {
            $str .= $way->toString();
        }

        $str .= 'You have arrived at your final destination.';

        return $str;
    }
}

class StrategyCalculation
{
    public $pointStart;
    public $pointEnd;

    public function setPoint($point, $type = 1)
    {
        if($type) {
            $this->pointStart[] = $point;
        } else {
            $this->pointEnd[] = $point;
        }
    }

    /**
     * Can be issue with duplicate and two unice answered point
     * @return array
     */
    public function getStartPoint()
    {
        $start = array_diff($this->pointStart, $this->pointEnd);
        return current($start);
    }

    public function getEndPoint()
    {
        $end = array_diff($this->pointEnd, $this->pointStart);
        return current($end);
    }
}

$trip = new TripManager(new StrategyCalculation());
$trip->setPartOfWay(new Ticket(new Bus(new Way('Barcelona', 'Gerona Airport'))));
$trip->setPartOfWay(new Ticket(new Train(new Way('Madrid', 'Barcelona'))));
$trip->setPartOfWay(new Ticket(new Plain(new Way('Stockholm', 'New York'))));
$trip->setPartOfWay(new Ticket(new Plain(new Way('Gerona Airport', 'Stockholm'))));
echo $trip->calculateWay();
die();