<?php
declare(strict_types=1);
namespace Application\Entity;
/**
 * Profile
 * 
 */
class Profile
{
    
    protected $profile = '';
    
    /**
     * This method returns the current value of $profile
     * 
     * @return string $profile
     */
    public function getProfile() : string
    {
        return $this->profile;
    }
    
    /**
     * This method sets the value of $profile
     * 
     * @param string $profile
     * @return profile $this
     */
    public function setProfile(string $profile)
    {
        $this->profile = $profile;
        return $this;
    }
}
