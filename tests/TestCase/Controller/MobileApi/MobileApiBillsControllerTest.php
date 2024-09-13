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
use App\Utility\ArrayUtilities;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

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

    public function tearDown(): void
    {
        // Clear table data after each test method
        $this->clearBillsPaymentsTable();
        
        parent::tearDown();
    }
    
    /**
     * Clear all records from the BillsPayments table.
     *
     * @return void
     */
    protected function clearBillsPaymentsTable(): void
    {
        $billsTable = TableRegistry::getTableLocator()->get('Bills');
        $billsTable->deleteAll([]);
    }
    public function dataProvider()
    {
        $uniqueIds = [
            '05a8e00d-43ac-49e6-a84b-22478171a187',
            '07b8f00d-53bc-49e6-a85b-23478172b197',
            '09c8f00d-63dc-49e6-a95b-24478183c298'
        ];
        // Define initial data to be saved as existing records in the database
        $initialDataWithThreeRecord = [
            [
                'amount' => 500.0,
                'category' => 'Category',
                'dueDate' => '2024-07-20 00:00:00',
                'groupUniqueId' => '68500b8f-0fdb-41a1-8cbe-3826cf783aa9',
                'imageName' => '',
                'isRecurring' => 0,
                'name' => 'Electric',
                'repeatBy' => '',
                'repeatCount' => 0,
                'repeatEvery' => 0,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 1,
                'uniqueId' => $uniqueIds[0],
            ],
            [
                'amount' => 750.0,
                'category' => 'Utilities',
                'dueDate' => '2024-08-15 00:00:00',
                'groupUniqueId' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
                'imageName' => 'water_bill.png',
                'isRecurring' => 1,
                'name' => 'Water Bill',
                'repeatBy' => 'day',
                'repeatCount' => 30,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[1],
            ],
            [
                'amount' => 1200.0,
                'category' => 'Rent',
                'dueDate' => '2024-09-01 00:00:00',
                'groupUniqueId' => '7g8h9i0j-1k2l-3m4n-5o6p-7q8r9s0t1u2v',
                'imageName' => 'rent_receipt.png',
                'isRecurring' => 1,
                'name' => 'Monthly Rent',
                'repeatBy' => 'month',
                'repeatCount' => 12,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-09-01 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[2],
            ]
        ];
        $initialDataWithTwoRecord = [
            [
                'amount' => 500.0,
                'category' => 'Category',
                'dueDate' => '2024-07-20 00:00:00',
                'groupUniqueId' => '68500b8f-0fdb-41a1-8cbe-3826cf783aa9',
                'imageName' => '',
                'isRecurring' => 0,
                'name' => 'Electric',
                'repeatBy' => '',
                'repeatCount' => 0,
                'repeatEvery' => 0,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 1,
                'uniqueId' => $uniqueIds[0],
            ],
            [
                'amount' => 750.0,
                'category' => 'Utilities',
                'dueDate' => '2024-08-15 00:00:00',
                'groupUniqueId' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
                'imageName' => 'water_bill.png',
                'isRecurring' => 1,
                'name' => 'Water Bill',
                'repeatBy' => 'day',
                'repeatCount' => 30,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[1],
            ],
            [
                'amount' => 1200.0,
                'category' => 'Rent',
                'dueDate' => '2024-09-01 00:00:00',
                'groupUniqueId' => '7g8h9i0j-1k2l-3m4n-5o6p-7q8r9s0t1u2v',
                'imageName' => 'rent_receipt.png',
                'isRecurring' => 1,
                'name' => 'Monthly Rent',
                'repeatBy' => 'month',
                'repeatCount' => 12,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-09-01 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[2],
            ]
        ];
         // Define edited test data for bill records
         $editedData = [
            [
                'amount' => 5000,
                'category' => 'Category',
                'dueDate' => '2024-07-20 00:00:00',
                'groupUniqueId' => '68500b8f-0fdb-41a1-8cbe-3826cf783aa9',
                'imageName' => '',
                'isRecurring' => 0,
                'name' => 'Electric edited',
                'repeatBy' => '',
                'repeatCount' => 0,
                'repeatEvery' => 0,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 1,
                'uniqueId' => $uniqueIds[0],
            ],
            [
                'amount' => 7500,
                'category' => 'Utilities',
                'dueDate' => '2024-08-15 00:00:00',
                'groupUniqueId' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
                'imageName' => 'water_bill.png',
                'isRecurring' => 1,
                'name' => 'Water Bill edited',
                'repeatBy' => 'day',
                'repeatCount' => 30,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[1],
            ],
            [
                'amount' => 12000,
                'category' => 'Rent',
                'dueDate' => '2024-09-01 00:00:00',
                'groupUniqueId' => '7g8h9i0j-1k2l-3m4n-5o6p-7q8r9s0t1u2v',
                'imageName' => 'rent_receipt.png',
                'isRecurring' => 1,
                'name' => 'Monthly Rent edited',
                'repeatBy' => 'month',
                'repeatCount' => 12,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-09-01 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[2],
            ]
        ];

        return [
            
                'initialDataWithThreeRecord'=>$initialDataWithThreeRecord, 
                'initialDataWithTwoRecord'=>$initialDataWithTwoRecord, 
                'editedData'=>$editedData, 
                'uniqueIds'=>$uniqueIds
        ];
    }
    /**
     * Test case for the `uploadBills` method.
     * 
     * This test ensures that the `uploadBills` method successfully inserts bill records into the database
     * and that the response returned is accurate. It validates the response from the `uploadBills` endpoint
     * and checks that the records are correctly inserted and match the provided data.
     * 
     * @return void
     */
    public function testUploadBills_1()
    {
        // Define unique IDs for test records to avoid duplication and ensure correct matching
        $uniqueIds = [
            '05a8e00d-43ac-49e6-a84b-22478171a187',
            '07b8f00d-53bc-49e6-a85b-23478172b197',
            '09c8f00d-63dc-49e6-a95b-24478183c298'
        ];

        // Define test data for bill records
        $data = [
            [
                'amount' => 500.0,
                'category' => 'Category',
                'dueDate' => '2024-07-20 00:00:00',
                'groupUniqueId' => '68500b8f-0fdb-41a1-8cbe-3826cf783aa9',
                'imageName' => '',
                'isRecurring' => 0,
                'name' => 'Electric',
                'repeatBy' => '',
                'repeatCount' => 0,
                'repeatEvery' => 0,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 1,
                'uniqueId' => $uniqueIds[0],
            ],
            [
                'amount' => 750.0,
                'category' => 'Utilities',
                'dueDate' => '2024-08-15 00:00:00',
                'groupUniqueId' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
                'imageName' => 'water_bill.png',
                'isRecurring' => 1,
                'name' => 'Water Bill',
                'repeatBy' => 'day',
                'repeatCount' => 30,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[1],
            ],
            [
                'amount' => 1200.0,
                'category' => 'Rent',
                'dueDate' => '2024-09-01 00:00:00',
                'groupUniqueId' => '7g8h9i0j-1k2l-3m4n-5o6p-7q8r9s0t1u2v',
                'imageName' => 'rent_receipt.png',
                'isRecurring' => 1,
                'name' => 'Monthly Rent',
                'repeatBy' => 'month',
                'repeatCount' => 12,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-09-01 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[2],
            ]
        ];

        // Send a POST request to the `uploadBills` endpoint with the test data
        $this->post('/mobileapi/MobileApiBills/uploadBills', $data);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bill saved successfully.', $responseData['message'], 'The success message should match.');
        $this->assertCount(count($uniqueIds), $responseData['data'], 'The number of saved records should match the number of unique IDs.');

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('Bills');

        // Query the database for records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $uniqueIds]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that the number of records retrieved matches the number of records inserted
        $this->assertCount(count($data), $records, 'The number of records in the database should match the number of test records.');

        // Compare each field in the initial data with the corresponding field in the database records
        foreach ($data as $index => $expectedRecord) {
            // Convert camelCase keys to snake_case for database comparison
            $expectedRecordSnakeCase = ArrayUtilities::convertCamelToSnake($expectedRecord);

            foreach ($expectedRecordSnakeCase as $key => $expectedValue) {
                // Compare each field in the database record with the expected value
                $actualValue = $records[$index][$key];

                if ($actualValue instanceof FrozenTime) {
                    // Format datetime fields for comparison
                    $formattedDatetime = (new \Cake\I18n\FrozenTime($actualValue))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($expectedValue, $formattedDatetime, "The value for '$key' does not match.");
                } else {
                    $this->assertEquals($expectedValue, $actualValue, "The value for '$key' does not match.");
                }
            }
        }
    }
    /**
     * Tests the functionality of updating existing bill records in the database.
     *
     * This function simulates a scenario where bill records already exist in the database.
     * It first saves initial data to represent these existing records, then sends a POST request to the `uploadBills` endpoint with modified data. 
     * The function then verifies:
     * 1. That the response from the POST request indicates a successful operation.
     * 2. That the database records have been correctly updated with the modified data.
     * 3. That each field in the updated records matches the expected values.
     */
    public function testUploadBills_2()
    {
        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('Bills');

        // Define unique IDs for test records to avoid duplication and ensure correct matching
        $uniqueIds = [
            '05a8e00d-43ac-49e6-a84b-22478171a187',
            '07b8f00d-53bc-49e6-a85b-23478172b197',
            '09c8f00d-63dc-49e6-a95b-24478183c298'
        ];

        // Define initial data to be saved as existing records in the database
        $initialData = [
            [
                'amount' => 500.0,
                'category' => 'Category',
                'dueDate' => '2024-07-20 00:00:00',
                'groupUniqueId' => '68500b8f-0fdb-41a1-8cbe-3826cf783aa9',
                'imageName' => '',
                'isRecurring' => 0,
                'name' => 'Electric',
                'repeatBy' => '',
                'repeatCount' => 0,
                'repeatEvery' => 0,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 1,
                'uniqueId' => $uniqueIds[0],
            ],
            [
                'amount' => 750.0,
                'category' => 'Utilities',
                'dueDate' => '2024-08-15 00:00:00',
                'groupUniqueId' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
                'imageName' => 'water_bill.png',
                'isRecurring' => 1,
                'name' => 'Water Bill',
                'repeatBy' => 'day',
                'repeatCount' => 30,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[1],
            ],
            [
                'amount' => 1200.0,
                'category' => 'Rent',
                'dueDate' => '2024-09-01 00:00:00',
                'groupUniqueId' => '7g8h9i0j-1k2l-3m4n-5o6p-7q8r9s0t1u2v',
                'imageName' => 'rent_receipt.png',
                'isRecurring' => 1,
                'name' => 'Monthly Rent',
                'repeatBy' => 'month',
                'repeatCount' => 12,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-09-01 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[2],
            ]
        ];

        // Convert camelCase keys to snake_case for initial data
        $convertedInitialData = array_map(function ($item) {
            return ArrayUtilities::convertCamelToSnake($item);
        }, $initialData);

        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->saveMany($billsTable->newEntities($convertedInitialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Define edited test data for bill records
        $editedData = [
            [
                'amount' => 5000,
                'category' => 'Category',
                'dueDate' => '2024-07-20 00:00:00',
                'groupUniqueId' => '68500b8f-0fdb-41a1-8cbe-3826cf783aa9',
                'imageName' => '',
                'isRecurring' => 0,
                'name' => 'Electric edited',
                'repeatBy' => '',
                'repeatCount' => 0,
                'repeatEvery' => 0,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 1,
                'uniqueId' => $uniqueIds[0],
            ],
            [
                'amount' => 7500,
                'category' => 'Utilities',
                'dueDate' => '2024-08-15 00:00:00',
                'groupUniqueId' => '1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p',
                'imageName' => 'water_bill.png',
                'isRecurring' => 1,
                'name' => 'Water Bill edited',
                'repeatBy' => 'day',
                'repeatCount' => 30,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-08-15 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[1],
            ],
            [
                'amount' => 12000,
                'category' => 'Rent',
                'dueDate' => '2024-09-01 00:00:00',
                'groupUniqueId' => '7g8h9i0j-1k2l-3m4n-5o6p-7q8r9s0t1u2v',
                'imageName' => 'rent_receipt.png',
                'isRecurring' => 1,
                'name' => 'Monthly Rent edited',
                'repeatBy' => 'month',
                'repeatCount' => 12,
                'repeatEvery' => 1,
                'repeatUntil' => '2025-09-01 00:00:00',
                'status' => 0,
                'uniqueId' => $uniqueIds[2],
            ]
        ];

        // Send a POST request to the `uploadBills` endpoint with the edited test data
        $this->post('/mobileapi/MobileApiBills/uploadBills', $editedData);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bill saved successfully.', $responseData['message'], 'The success message should match.');
        $this->assertCount(count($uniqueIds), $responseData['data'], 'The number of saved records should match the number of unique IDs.');

        // Query the database for records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $uniqueIds]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that the number of records retrieved matches the number of records inserted
        $this->assertCount(count($editedData), $records, 'The number of records in the database should match the number of test records.');

        // Compare each field in the edited test data with the corresponding field in the database records
        foreach ($editedData as $index => $expectedRecord) {
            // Convert camelCase keys to snake_case for database comparison
            $expectedRecordSnakeCase = ArrayUtilities::convertCamelToSnake($expectedRecord);

            foreach ($expectedRecordSnakeCase as $key => $expectedValue) {
                // Compare each field in the database record with the expected value
                $actualValue = $records[$index][$key];

                if ($actualValue instanceof FrozenTime) {
                    // Format datetime fields for comparison
                    $formattedDatetime = (new \Cake\I18n\FrozenTime($actualValue))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($expectedValue, $formattedDatetime, "The value for '$key' does not match.");
                } else {
                    $this->assertEquals($expectedValue, $actualValue, "The value for '$key' does not match.");
                }
            }
        }
    }
    /**
     * Tests the uploadBills endpoint functionality for updating existing records and inserting new ones.
     * 
     * This function simulates a scenario where some data already exists in the database.
     * It ensures that existing data will be updated and non-existing data will be inserted correctly.
     */
    public function testUploadBills_3()
    {
        // Get the Bills table instance from TableRegistry
        $billsTable = TableRegistry::getTableLocator()->get('Bills');

        // Define unique IDs for test records to avoid duplication and ensure correct matching
        $uniqueIds = $this->dataProvider()['uniqueIds'];

        // Define initial data to be saved as existing records in the database
        $initialData = $this->dataProvider()['initialDataWithThreeRecord'];

        
        // Convert camelCase keys to snake_case for initial data
        $convertedInitialData = array_map(function ($item) {
            return ArrayUtilities::convertCamelToSnake($item);
        }, $initialData);

        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->saveMany($billsTable->newEntities($convertedInitialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Define edited test data for bill records
        $editedData = $this->dataProvider()['editedData'];

        // Send a POST request to the `uploadBills` endpoint with the edited test data
        $this->post('/mobileapi/MobileApiBills/uploadBills', $editedData);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bill saved successfully.', $responseData['message'], 'The success message should match.');
        $this->assertCount(count($uniqueIds), $responseData['data'], 'The number of saved records should match the number of unique IDs.');

        // Query the database for records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $uniqueIds]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that the number of records retrieved matches the number of records inserted
        $this->assertCount(count($editedData), $records, 'The number of records in the database should match the number of test records.');

        // Compare each field in the edited test data with the corresponding field in the database records
        foreach ($editedData as $index => $expectedRecord) {
            // Convert camelCase keys to snake_case for database comparison
            $expectedRecordSnakeCase = ArrayUtilities::convertCamelToSnake($expectedRecord);

            foreach ($expectedRecordSnakeCase as $key => $expectedValue) {
                // Compare each field in the database record with the expected value
                $actualValue = $records[$index][$key];

                if ($actualValue instanceof FrozenTime) {
                    // Format datetime fields for comparison
                    $formattedDatetime = (new \Cake\I18n\FrozenTime($actualValue))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($expectedValue, $formattedDatetime, "The value for '$key' does not match.");
                } else {
                    $this->assertEquals($expectedValue, $actualValue, "The value for '$key' does not match.");
                }
            }
        }
    }

    /**
     * this function simulate uploading record with some missing data
     */
    public function testUploadBulls_4()
    {

    }
   
}
