<?php
// comments can go here
declare(strict_types=1);
// or here ...
namespace Application\Generic;

// previously you had to do this:
/*
use Application\Entity\Name;
use Application\Entity\Address;
use Application\Entity\Profile;
*/

// this shows the PHP 7 group use syntax:
use Application\Entity\
{
    Name,
    Address,
    Profile
};

/**
 * This is a demonstration class which uses classes in other namespaces
 *
 */
class Test
{

    public $name;
    public $address;
    public $profile;

    public function __construct(Name $name, Address $address, Profile $profile)
    {
        $this->name = $name;
        $this->address = $address;
        $this->profile =  $profile;
    }
}
