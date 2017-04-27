<?php
// app/src/App/User/UserProvider.php
namespace App\Provider;
 
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
 
class UserProvider implements UserProviderInterface
{ 
    public function loadUserByUsername($username)
    {
        $user = User::find($username);
        if(!$user || is_null($user)) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        return $user;
    }
 
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
 
        return $this->loadUserByUsername($user->getUsername());
    }
 
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}