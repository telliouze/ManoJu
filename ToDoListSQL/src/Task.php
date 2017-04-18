<?php
// Définition de la classe tâches
class Task
{
    // Elle est représentée par 3 propriétés (dont l'id de la catégorie associée)
    private $description;
    private $category_id;
    private $id;

    // Constructeur de la classe (on passe un id de tâche à null par défaut, il est auto incrémenté)
    function __construct($description, $category_id,  $id=null)
    {
        $this->description = $description;
        $this->category_id = $category_id;
        $this->id = $id;
    }

    // Getters et setters
    function setDescription($new_description)
    {
        $this->description = (string) $new_description;
    }

    function getDescription()
    {
        return $this->description;
    }

    function getId()
    {
        return $this->id;
    }

    function getCategoryId()
    {
        return $this->category_id;
    }

    // Méthode d'ajout d'une catégorie en base
    function save()
    {
        $executed = $GLOBALS['DB']->exec("INSERT INTO tasks (description, category_id) VALUES ('{$this->getDescription()}',{$this->getCategoryId()});");
        if ($executed){
            $this->id = $GLOBALS['DB']->lastInsertId();
            return true;
        } else {
            return false;
        }
    }

    static function getAll()
    {
        // Requête de sélection de toutes les tâches avec une jointure sur la catégorie
        $returned_tasks = $GLOBALS['DB']->query("SELECT tasks.id AS task_id, description, category_id, name FROM tasks,categories WHERE tasks.category_id=categories.id;");
        $tasks = array();
        // On envoie à twig un tableau de tableaux ([tâche, catégorie])
        foreach($returned_tasks as $task){
            $task_detail = array($task['description'],$task['name']);
            array_push($tasks,$task_detail);
        }

        return $tasks;
    }

    // Méthode pour vider la table tasks
    static function deleteAll()
    {
        $executed = $GLOBALS['DB']->exec("DELETE FROM tasks;");
        if ($executed){
            return true;
        } else {
            return false;
        }
    }

    // Recherche d'une tâche par id
    static function find($search_id)
    {
        $returned_tasks = $GLOBALS['DB']->prepare("SELECT * FROM tasks WHERE id = :id");
        $returned_tasks->bindParam(':id', $search_id, PDO::PARAM_STR);
        $returned_tasks->execute();
        // On crée un tableau de tâches avec les tâches reçues de la requête
        foreach ($returned_tasks as $task){
            $task_description = $task['description'];
            $category_id = $task['category_id'];
            $task_id = $task['id'];
            if($task_id == $search_id){
                $found_task = new Task($task_description, $category_id, $task_id);
            }
        }
        return $found_task;
    }
}
?>