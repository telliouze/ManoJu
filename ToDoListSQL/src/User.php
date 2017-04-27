<?php
namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface {
    public static $TABLE = "users";

    private $id;

    private $username;

    private $password;

    private $roles;

    private $salt;

    public function __contruct(int $id, string $username, string $password, string $salt, array $roles) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getId(){
        return $this->id;
    }

    public function setId(int $id){
        $this->id = $id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername(string $username){
        $this->username = $username;
    }

    public function getPassword(){
        return $this->password;
    }

    public function setPassword(string $password){
        $this->password = $password;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function setRoles(array $roles){
        $this->roles = $roles;
    }

    public function getSalt(){
        return $this->salt;
    }

    public function setSalt(string $salt){
        $this->salt = $salt;
    }

    public function eraseCredentials() {
        
    }

    static function find($username){
        $returned_user = $GLOBALS['DB']->prepare("SELECT * FROM user WHERE username = :username");
        $returned_user->bindParam(':username', $username, PDO::PARAM_STR);
        $returned_user->execute();
        foreach ($returned_user as $user){
            $user_username = $user['username'];
            $user_id = $user['id'];
            $user_roles = $user['roles'];
            $user_password = $user['password'];
            $user_salt = $user['salt'];
            if($user_username == $username){
                $found_user = new User($user_id, $user_username, $user_password, $user_salt, $user_roles);
            }
        }
        return $found_user;
    }
}