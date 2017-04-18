<?php
/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

require_once "src/Category.php";
require_once "src/Task.php";

$server = 'mysql:host=localhost:3306;dbname=to_do_test';
$username = 'root';
$password = 'root';
$DB = new PDO($server,$username,$password);

class CategoryTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Category::deleteAll();
        Task::deleteAll();
    }
    
    function test_getName()
    {
        //Arrange
        $name = "Work stuff";
        $test_category = new Category($name);

        //Act
        $result = $test_category->getName();

        //Assert
        $this->assertEquals($name, $result);
    }

    function test_save()
    {
        //Arrange
        $name = "Work stuff";
        $test_category = new Category($name);

        //Act
        $executed = $test_category->save();

        //Assert
        $this->assertTrue($executed, "Category not successfully saved to database");
    }

    function test_getAll()
    {
        //Arrange
        $name = "Work stuff";
        $name2 = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();
        $test_category2 = new Category($name2);
        $test_category2->save();

        //Act
        $result = Category::getAll();

        //Assert
        $this->assertEquals([$test_category,$test_category2], $result);
    }

    function test_deleteAll()
    {
        //Arrange
        $name = "Work stuff";
        $name2 = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();
        $test_category2 = new Category($name2);
        $test_category2->save();

        //Act
        Category::deleteAll();

        //Assert
        $result = Category::getAll();
        $this->assertEquals([],$result);
    }

    function test_getId()
    {
        //Arrange
        $name = "Work stuff";
        $test_category = new Category($name);
        $test_category->save();

        //Act
        $result = $test_category->getId();

        //Assert
        $this->assertTrue(is_numeric($result));
    }

    function test_find()
    {
        //Arrange
        $name = "Work stuff";
        $name2 = "Home stuff";
        $test_category = new Category($name);
        $test_category->save();
        $test_category2 = new Category($name2);
        $test_category2->save();

        //Act
        $id = $test_category->getId();
        $result = Category::find($id);

        //Assert
        $this->assertEquals($test_category, $result);
    }

    function test_getTasks()
    {
        //Arrange
        $name = "Work stuff";
        $test_category = new Category($name);
        $test_category->save();

        $test_category_id = $test_category->getId();

        $description = "Email client";
        $test_task = new Task($description, $test_category_id);
        $test_task->save();

        $description2 = "Meet with boss";
        $test_task2 = new Task($description2, $test_category_id);
        $test_task2->save();

        //Act
        $result = $test_category->getTasks();

        //Assert
        $this->assertEquals([$test_task, $test_task2], $result);
    }

    function test_update()
    {
        //Arrange
        $name = "Work stuff";
        $test_category = new Category($name);
        $test_category->save();

        $new_name = "Home stuff";

        //Act
        $test_category->update($new_name);

        //Assert
        $this->assertEquals("Home stuff", $test_category->getName());
    }

    function test_delete()
    {
        //Arrange
        $name = "Work stuff";
        $test_category = new Category($name);
        $test_category->save();

        $name2 = "Home stuff";
        $test_category2 = new Category($name2);
        $test_category2->save();

        //Act
        $test_category->delete();

        //Assert
        $this->assertEquals([$test_category2],Category::getAll());
    }

}
?>