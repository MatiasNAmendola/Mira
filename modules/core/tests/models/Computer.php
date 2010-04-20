<?php

class Computer extends Mira_Core_Vega 
{
    public function getFullString()
    {
        return $this->mark . $this->kind;
    }
}