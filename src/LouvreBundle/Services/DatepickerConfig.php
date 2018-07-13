<?php
namespace LouvreBundle\Services;

class DatepickerConfig
{

    private $config;


public function __construct(){
$configDatepickerjson = file_get_contents("json/configDatepicker.json");

$configDatepickerjson =json_encode($configDatepickerjson);

    $this->setConfig($configDatepickerjson);
    
}




    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

}