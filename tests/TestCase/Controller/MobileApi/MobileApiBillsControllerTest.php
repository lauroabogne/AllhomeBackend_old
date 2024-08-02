<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase\Controller;

use App\Controller\MobileApi\MobileApiBillsController;
use Cake\Core\Configure;
use Cake\TestSuite\Constraint\Response\StatusCode;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;


class MobileApiBillsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    // public $fixtures = [
    //     'app.Bills',
    // ];

   
    public function setUp(): void
    {
        parent::setUp();
    }

    // public function testDatabaseConnection()
    // {
    //     $connection = ConnectionManager::get('test');
    //     $isConnected = false;

    //     try {
    //         // Perform a simple query to check the connection
            
    //         $result = $connection->execute('SHOW TABLES')->fetchAll();
    //         debug($result);
    //         if (!empty($result)) {
    //             $isConnected = true;
    //         }
    //     } catch (\Exception $e) {

    //         debug($e->getMessage());
    //         $isConnected = false;
    //     }
    //     $this->assertTrue($isConnected, 'Database connection should be established.');
    // }
    
    public function testIndex()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $controller = new MobileApiBillsController();
        $request = new ServerRequest();
        $controller->setRequest($request);
        $response = $controller->computeRecord();

        
        
        $body = $response->getBody();
        // Rewind the stream to read the body contents
        $body->rewind();
        $actualBodyContents = $body->getContents();
 
        $this->assertInstanceOf('Cake\Http\Response', $response);
        $this->assertEquals('application/json', $response->getType());

         // Assert the response body is the expected JSON
         $expectedBody = json_encode(['message' => 'Welcome to the Mobile API!']);
         $this->assertEquals($expectedBody, $actualBodyContents);
       
    }

    public function testIndexGet()
    {
        $this->get('/mobileapi/MobileApiBills/computeRecord');
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');
        $this->assertResponseContains('"message":"Welcome to the Mobile API!"');
    }
    public function testAddSuccess()
    {

        $this->enableCsrfToken();

        $recordUniqueIds = ['1234567','12345678'];
         // Prepare the data to be sent in the request
         $data = [
            [
                'unique_id' => $recordUniqueIds[0],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
            ],
            [
                'unique_id' => $recordUniqueIds[1],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
            ],
            
        ];

        
        $this->post('/mobileapi/MobileApiBills/add', $data);
       
        // Assert the response
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        
        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert the response data
        $this->assertTrue($responseData['is_success']);
        $this->assertEquals('Bills save successfully.', $responseData['message']);

        // Verify that the data was saved in the database
        $billsTable = TableRegistry::getTableLocator()->get('Bills');
        $query = $billsTable->find()->where(['unique_id IN' => [$recordUniqueIds[0], $recordUniqueIds[1]]]);
        $records = $query->toArray();
        $this->assertCount(2, $records, 'The query should return two records.');
        // Assert properties or values for each record
        $this->assertEquals($recordUniqueIds[0], $records[0]->unique_id);
        $this->assertEquals($recordUniqueIds[1], $records[1]->unique_id);
       
    }


    /**
     * Test case for testing the error scenario when adding bills.
     *
     * This test case verifies that when attempting to add bills with duplicate unique IDs,
     * the operation fails, and no records are saved in the database.
     */

    public function testAddError()
    {

        $this->enableCsrfToken();

        $recordUniqueIds = ['123456789','123456789'];
         // Prepare the data to be sent in the request
         $data = [
            [
                'unique_id' => $recordUniqueIds[0],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
            ],
            [
                'unique_id' => $recordUniqueIds[1],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
                ],
            
        ];

        
        $this->post('/mobileapi/MobileApiBills/add', $data);
       
        // Assert the response
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert the response data
        $this->assertFalse($responseData['is_success']);
        $this->assertEquals('Bills are failed to save.', $responseData['message']);

        // Verify that the data was saved in the database
        $billsTable = TableRegistry::getTableLocator()->get('Bills');
        $query = $billsTable->find()->where(['unique_id IN' => [$recordUniqueIds[0], $recordUniqueIds[1]]]);
        $records = $query->toArray();  
        $this->assertCount(0, $records, 'The query should return two records.');
      
    }

    /**
     * Test case for testing the error scenario when adding three bills with two bills having the same unique ID.
     *
     * This test case verifies that when attempting to add three bills, where two of them have the same unique ID,
     * the operation fails, and no records are saved in the database.
     */
    public function testAddThreeRecordWithTwoRecordHaveSameUniqueIdError()
    {

        $this->enableCsrfToken();

        $recordUniqueIds = ['123456799','123456788','123456788'];
         // Prepare the data to be sent in the request
         $data = [
            [
                'unique_id' => $recordUniqueIds[0],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
            ],
            [
                'unique_id' => $recordUniqueIds[1],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
            ],
            [
                'unique_id' => $recordUniqueIds[2],
                'group_unique_id' => 'group123',
                'amount' => 150.75,
                'name' => 'Electric Bill',
                'category' => 'Utilities',
                'due_date' => '2024-08-01 12:00:00',
                'is_recurring' => 1,
                'repeat_every' => 1,
                'repeat_by' => 'month',
                'repeat_until' => '2025-08-01 12:00:00',
                'repeat_count' => 12,
                'image_name' => 'electric_bill.jpg',
                'status' => 0,
                'uploaded' => 0,
                'created' => '2024-07-25 12:00:00',
                'modified' => '2024-07-25 12:00:00',
            ],    
            
        ];

        
        $this->post('/mobileapi/MobileApiBills/add', $data);
       
        // Assert the response
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert the response data
        $this->assertFalse($responseData['is_success']);
        $this->assertEquals('Bills are failed to save.', $responseData['message']);

        // Verify that the data was saved in the database
        $billsTable = TableRegistry::getTableLocator()->get('Bills');
        $query = $billsTable->find()->where(['unique_id IN' => [$recordUniqueIds[0], $recordUniqueIds[1], $recordUniqueIds[2]]]);
        $records = $query->toArray();  
        $this->assertCount(0, $records, 'The query should return 0 records.');
      
    }

    /**
     * Test case for testing the error scenario when adding an empty data set.
     *
     * This test case verifies that when attempting to add an empty data set,
     * the operation fails with an appropriate error message.
     */
    public function testAddEmpytDataError()
    {

        $this->enableCsrfToken();
         // Prepare the data to be sent in the request
         $data = [];
        
        $this->post('/mobileapi/MobileApiBills/add', $data);
       
        // Assert the response
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert the response data
        $this->assertFalse($responseData['is_success']);
        $this->assertEquals('Nothing to save.', $responseData['message']);

      
    }
}
