<?php
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\DBAL\Connection;


// Défiinition de la classe catégorie
class Category
{
    // Elle est représentée par 2 propriétés: nom et id de catégorie
    private $name;
    private $id;
    static $conn;

    // Constructeur de la classe
    function __construct($name, $id=null, Connection $conn)
    {
        $this->name = $name;
        $this->id = $id;
        $this->conn = $conn;
    }

    // Getters et setters
    function setName($new_name)
    {
        $this->name = (string) $new_name;
    }

    function getName()
    {
        return $this->name;
    }

    function getId()
    {
        return $this->id;
    }

    // Méthode d'ajout d'une catégorie en base
    function save()
    {
        // Dans la méthode exec() de la variable globale DB, on passe la requête à exécuter
        // On passe le nom de la catégorie en VALUE par le biais du GETTER getName()
        $executed = $GLOBALS['DB']->exec("INSERT INTO categories (name) VALUES ('{$this->getName()}');");
        if ($executed){
            // Lors de l'insertion de la catégorie en base, on garde l'id généré pour la propriété id de l'objet
            $this->id = $GLOBALS['DB']->lastInsertId();
            return true;
        } else {
            return false;
        }
    }

    // Méthode d'affichage de toutes les catégories
    static function getAll(Connection $conn)
    {
        $returned_categories = $conn->executeQuery("SELECT * FROM categories;");
        $categories = array();
        foreach($returned_categories as $category){
            $name = $category['name'];
            $id = $category['id'];
            $new_category = new Category($name, $id,$conn);
            array_push($categories,$new_category);
        }
        return $categories;
    }

    // Méthode de suppression de toutes les catégories
    static function deleteAll()
    {
        $executed = $GLOBALS['DB']->exec("DELETE FROM categories;");
        if ($executed){
            return true;
        } else {
            return false;
        }
    }

    // Statique : méthode paramétrée de recherche d'une catégorie par id
    static function find($search_id)
    {
        // Autre méthode d'exécution d'une requête paramétrée prepare/bindParam/execute
        $returned_categories = $GLOBALS['DB']->prepare("SELECT * FROM categories WHERE id = :id");
        $returned_categories->bindParam(':id', $search_id, PDO::PARAM_STR);
        $returned_categories->execute();
        foreach ($returned_categories as $category){
            $category_name = $category['name'];
            $category_id = $category['id'];
            if($category_id == $search_id){
                $found_category = new Category($category_name, $category_id);
            }
        }
        return $found_category;
    }

    // Récupération des tâches d'une catégorie
    function getTasks()
    {
        $tasks = Array();
        $returned_tasks = $GLOBALS['DB']->query("SELECT * FROM tasks WHERE category_id = {$this->getId()};");
        foreach ($returned_tasks as $task){
            $description = $task['description'];
            $task_id = $task['id'];
            $category_id = $task['category_id'];
            $new_task = new Task($description, $category_id, $task_id);
            array_push($tasks, $new_task);
        }
        return $tasks;
    }

    // Méthode de modification du nom d'une catégorie
    function update($new_name)
    {
        $executed = $GLOBALS['DB']->exec("UPDATE categories SET name = '{$new_name}' WHERE id = {$this->getId()};");
        if($executed){
            $this->setName($new_name);
            return true;
        } else {
            return false;
        }
    }

    // Suppression d'une catégorie
    function delete()
    {
        // 1. On supprime la catégorie de la table categories
        $executed = $GLOBALS['DB']->exec("DELETE FROM categories WHERE id = {$this->getId()};");
        if(!$executed){
            return false;
        }
        // 2. On supprime toutes les tâches de cette catégorie dans la table tasks
        $executed = $GLOBALS['DB']->exec("DELETE FROM tasks WHERE category_id = {$this->getId()};");
        if($executed){
            return true;
        } else {
            return false;
        }
    }

}
?>