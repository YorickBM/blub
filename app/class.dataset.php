<?php

namespace ModulairDashboard;

/**
 * Dataset interface to streamline class functions
 * @author - Yorick <info@yorickblom.nl>
 */
interface Dataset {

    function getData();
    function getHeaders();

    function setFields($data);
    function fields();

    function toString();
    function getDelay();

}