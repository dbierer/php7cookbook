<?php
declare(strict_types=1);
namespace Application\Entity;
/**
 * Address
 * 
 */
class Address
{
    
    protected $address = '';
    
    /**
     * This method returns the current value of $address
     * 
     * @return string $address
     */
    public function getAddress() : string
    {
        return $this->address;
    }
    
    /**
     * This method sets the value of $address
     * 
     * @param string $address
     * @return address $this
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
        return $this;
    }
}
