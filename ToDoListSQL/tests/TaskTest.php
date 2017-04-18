<?php
/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

require_once "src/Task.php";
require_once "src/Category.php";

$server = 'mysql:host=localhost:3306;dbname=to_do_test';
$username = 'root';
$password = 'root';
$DB = new PDO($server,$username,$password);

class TaskTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Task::deleteAll();
        Category::deleteAll();
    }
    
    function test_save()
    {
        //Arrange
        $name = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();

        $description = "Wash the dog";
        $category_id = $test_category->getId();
        $test_task = new Task($description, $category_id);

        //Act
        $test_task->save();

        //Assert
        $this->assertTrue($executed, "Task not successfully saved to database");
    }

    function test_getAll()
    {
        //Arrange
        $name = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();
        $category_id = $test_category->getId();

        $description = "Wash th dog";
        $description2 = "Water the lawn";
        $test_task = new Task($description, $category_id);
        $test_task->save();
        $test_task2 = new Task($description2, $category_id);
        $test_task2->save();

        //Act
        $result = Task::getAll();

        //Assert
        $this->assertEquals([$test_task,$test_task2], $result);
    }

    function test_deleteAll()
    {
        //Arrange
        $name = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();
        $category_id = $test_category->getId();

        $description = "Wash th dog";
        $description2 = "Water the lawn";
        $test_task = new Task($description, $category_id);
        $test_task->save();
        $test_task2 = new Task($description2, $category_id);
        $test_task2->save();

        //Act
        Task::deleteAll();

        //Assert
        $result = Task::getAll();
        $this->assertEquals([],$result);
    }

    function test_getId()
    {
        //Arrange
        $name = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();

        $description = "Wash the dog";
        $category_id = $test_category->getId();
        $test_task = new Task($description, $category_id);
        $test_task->save();

        //Act
        $result = $test_task->getId();

        //Assert
        $this->assertTrue(is_numeric($result));
    }

    function test_find()
    {
        //Arrange
        $name = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();
        $category_id = $test_category->getId();

        $description = "Wash th dog";
        $description2 = "Water the lawn";
        $test_task = new Task($description, $category_id);
        $test_task->save();
        $test_task2 = new Task($description2, $category_id);
        $test_task2->save();

        //Act
        $result = Task::find($test_task->getId());

        //Assert
        $this->assertEquals($test_task, $result);
    }

    function test_getCategoryId()
    {
        //Arrange
        $name = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();

        $description = "Wash the dog";
        $category_id = $test_category->getId();
        $test_task = new Task($description, $category_id);
        $test_task->save();

        //Act
        $result = $test_task->getCategoryId();

        //Assert
        $this->assertEquals($category_id,$result);
    }
}
?>