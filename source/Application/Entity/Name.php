<?php
declare(strict_types=1);
namespace Application\Entity;
/**
 * Name
 * 
 */
class Name
{
    
    protected $name = '';
    
    /**
     * This method returns the current value of $name
     * 
     * @return string $name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * This method sets the value of $name
     * 
     * @param string $name
     * @return name $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }
}
