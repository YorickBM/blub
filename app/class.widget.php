<?php

namespace ModulairDashboard;

/**
 * Basic widget class to store data and settings for widgets
 * @author - Yorick <info@yorickblom.nl>
 */
class Widget {

    //Widget unique variables
    public $Id;
    public $Title;
    
    //Widget size & location variables
    public $Size;
    public $Position;

    //Widget internal data variables
    public $Sensor;
    public $Graph;
    public $FilterX, $FilterY;

    public function __construct($title, $position, $size, $sensor, $graph, $filterX, $filterY) {
        $this->Id = uniqid();
        $this->Title = $title;

        $this->Size = $size;
        $this->Position = $position;

        $this->Sensor = $sensor;
        $this->Graph = $graph;
        $this->FilterX = $filterX;
        $this->FilterY = $filterY;
    }
}

class Widget_Position {
    public $Row;
    public $Column;

    public function __construct($row, $column) {
        $this->Row = $row;
        $this->Column = $column;
    }
}

class Widget_Size {
    public $Width;
    public $Height;

    public function __construct($widht, $height) {
        $this->Width = $widht;
        $this->Height = $height;
    }
}