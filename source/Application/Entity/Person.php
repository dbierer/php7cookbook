<?php
namespace Application\Entity;

class Person
{

	protected $firstName  = '';
	protected $lastName   = '';
	protected $address    = '';
	protected $city 	  = '';
	protected $stateProv  = '';
	protected $postalCode = '';
	protected $country    = '';
	
    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getStateProv()
    {
        return $this->stateProv;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function setStateProv($stateProv)
    {
        $this->stateProv = $stateProv;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

}
