<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\MobileApi;

use App\Controller\MobileApi\BillsPaymentsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Laminas\Diactoros\UploadedFile;
use Cake\Http\ServerRequest;
use App\Controller\MobileApi\MobileApiBillsPaymentsController;
use Cake\Http\Response;
use Cake\Utility\Text;
use Cake\ORM\Table;
use Cake\Validation\Validator;
/**
 * App\Controller\MobileApi\BillsPaymentsController Test Case
 *
 * @uses \App\Controller\MobileApi\BillsPaymentsController
 */
class MobileApiBillsPaymentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    // protected $fixtures = [
    //     'app.BillsPayments',
    // ];

    //protected $billPaymentTable;
    
    public function setUp(): void
    {
        parent::setUp();
       
    }

    public function tearDown(): void
    {
        // Clear table data after each test method
        $this->clearBillsPaymentsTable();
        
        // Delete all images in the specified directory
        $this->deleteAllImages();
    
        parent::tearDown();
    }
    
    /**
     * Clear all records from the BillsPayments table.
     *
     * @return void
     */
    protected function clearBillsPaymentsTable(): void
    {
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        $billsTable->deleteAll([]);
    }
    
    /**
     * Delete all images in the bill payment images directory.
     *
     * @return void
     */
    protected function deleteAllImages(): void
    {
        $actualImagePath = Configure::read('bill_payment_images_path');
    
        // Check if the directory exists
        if (is_dir($actualImagePath)) {
            // Scan the directory for all files
            $files = scandir($actualImagePath);
    
            // Iterate over the files and delete them
            foreach ($files as $file) {
                // Skip '.' and '..' entries
                if ($file === '.' || $file === '..') {
                    continue;
                }
    
                $filePath = $actualImagePath . DIRECTORY_SEPARATOR . $file;
    
                // Check if it's a file and delete it
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

   /**
     * Test the addition of a single bill payment.
     *
     * This test case verifies that a single bill payment can be successfully added 
     * to the database via the `addBillPayments` endpoint and ensures that all fields 
     * are correctly inserted.
     *
     */
    public function testAddBillPaymentsAddSingleBillPayments()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique IDs for the test records
        $recordUniqueIds = ['UID123456'];

        // Define the data to be posted to the addBillPayments endpoint
        $datas = [
            [
                'unique_id' => $recordUniqueIds[0],
                'bill_unique_id' => 'BUID123456',
                'bill_group_unique_id' => 'BGUID123456',
                'payment_amount' => 250.00,
                'payment_date' => '2024-07-25 10:00:00',
                'payment_note' => 'July payment for electricity',
                'image_name' => 'electricity_bill.jpg',
                'status' => 0,
                'created' => '2024-07-25 10:00:00',
                'modified' => '2024-07-25 10:00:00'
            ]
        ];

        // Send a POST request to the addBillPayments endpoint with the test data
        $this->post('/mobileapi/MobileApiBillsPayments/addBillPayments', $datas);

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id' => $recordUniqueIds[0]]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $records, 'The query should return one record.');

        // Loop through the records and validate each field
        foreach ($records as $record) {
            $uniqueId = $record['unique_id'];
            
            // Search for the index of the record in the original data array
            $indexByUniqueId = array_search($uniqueId, array_column($datas, 'unique_id'));
            
            // Loop through each field in the record
            foreach ($record as $key => $value) {
                // Skip the 'id' field
                if ($key == 'id') {
                    continue;
                }
                
                // If the value is a FrozenTime object, format it for comparison
                if ($value instanceof FrozenTime) {
                    $datetimeFromRecord = $value->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($datetimeFromRecord, $datas[$indexByUniqueId][$key], "Field '$key' does not match.");
                } else {
                    // Otherwise, directly compare the value
                    $this->assertEquals($value, $datas[$indexByUniqueId][$key], "Field '$key' does not match.");
                }
            }
        }
    }

    /**
     * Test the addition of multiple bill payments.
     *
     * This test case verifies that multiple bill payments can be successfully added 
     * to the database via the `addBillPayments` endpoint and ensures that all fields 
     * are correctly inserted. It also checks the response from the endpoint and 
     * validates that the data in the database matches the data sent in the request.
     *
     * @return void
     */
    public function testAddBillPaymentsAddMultipleBillPayments()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique IDs for the test records
        $recordUniqueIds = ['UID123456', 'UID654321'];

        // Define the data to be posted to the addBillPayments endpoint
        $datas = [
            [
                'unique_id' => $recordUniqueIds[0],
                'bill_unique_id' => 'BUID123456',
                'bill_group_unique_id' => 'BGUID123456',
                'payment_amount' => 250.00,
                'payment_date' => '2024-07-25 10:00:00',
                'payment_note' => 'July payment for electricity',
                'image_name' => 'electricity_bill.jpg',
                'status' => 0,
                'created' => '2024-07-25 10:00:00',
                'modified' => '2024-07-25 10:00:00'
            ],
            [
                'unique_id' => $recordUniqueIds[1],
                'bill_unique_id' => 'BUID654321',
                'bill_group_unique_id' => 'BGUID654321',
                'payment_amount' => 150.75,
                'payment_date' => '2024-07-26 15:30:00',
                'payment_note' => 'August payment for water',
                'image_name' => 'water_bill.jpg',
                'status' => 1,
                'created' => '2024-07-26 15:30:00',
                'modified' => '2024-07-26 15:30:00'
            ]
        ];

        // Send a POST request to the addBillPayments endpoint with the test data
        $this->post('/mobileapi/MobileApiBillsPayments/addBillPayments', $datas);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully.', $responseData['message'], 'The success message should match.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueIds]);
        $query->enableHydration(false);
        $records = $query->toArray();
      

        // Assert that exactly two records are returned from the query
        //$this->assertCount(2, $records, 'The query should return two records.');

        // Assert properties or values for each record
        $this->assertEquals($recordUniqueIds[0], $records[0]['unique_id']);
        $this->assertEquals($recordUniqueIds[1], $records[1]['unique_id']);

        // Loop through the records and validate each field
        foreach ($records as $record) {
            $uniqueId = $record['unique_id'];

            // Search for the index of the record in the original data array
            $indexByUniqueId = array_search($uniqueId, array_column($datas, 'unique_id'));

            // Loop through each field in the record
            foreach ($record as $key => $value) {
                // Skip the 'id' field
                if ($key == 'id') {
                    continue;
                }

                // If the value is a FrozenTime object, format it for comparison
                if ($value instanceof FrozenTime) {
                    $datetimeFromRecord = $value->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($datetimeFromRecord, $datas[$indexByUniqueId][$key], "Field '$key' does not match.");
                } else {
                    // Otherwise, directly compare the value
                    $this->assertEquals($value, $datas[$indexByUniqueId][$key], "Field '$key' does not match.");
                }
            }
        }
    }

    /**
     * Test the handling of empty data in the addBillPayments endpoint.
     *
     * This test case verifies that the `addBillPayments` endpoint correctly handles 
     * and returns an appropriate error message when an empty data array is sent 
     * in the request. It ensures that the system responds with an error indicating 
     * that there is nothing to save.
     *
     * @return void
     */
    public function testAddBillPaymentsAddEmptyBillPaymentsError()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define an empty data array for the test
        $data = [];

        // Send a POST request to the addBillPayments endpoint with the empty data
        $this->post('/mobileapi/MobileApiBillsPayments/addBillPayments', $data);     

        // Decode the JSON response from the request
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates failure (i.e., no success)
        $this->assertFalse($responseData['is_success'], 'The response should indicate failure when no data is provided.');

        // Assert that the error message in the response matches the expected message
        $this->assertEquals('Nothing to save.', $responseData['message'], 'The error message should match the expected error message when no data is provided.');
    }

    /**
     * Test addBillPaymentWithOrWithoutImage method with valid image and valid data.
     *
     * This test case verifies that the `addBillPaymentWithOrWithoutImage` endpoint correctly handles
     * the submission of valid image data along with other bill payment details. It ensures
     * that the system processes the request, saves the image, stores the bill payment details
     * in the database, and returns a success response.
     *
     * @return void
     */
    public function testAddBillPaymentsAddMultipleBillPaymentsWithSameUniqueIdError():void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique IDs for the test records
        $recordUniqueIds = ['UID123456', 'UID123456','UID17777'];

        // Define the data to be posted to the addBillPayments endpoint
        $datas = [
            [
                'unique_id' => $recordUniqueIds[0],
                'bill_unique_id' => 'BUID123456',
                'bill_group_unique_id' => 'BGUID123456',
                'payment_amount' => 250.00,
                'payment_date' => '2024-07-25 10:00:00',
                'payment_note' => 'July payment for electricity12',
                'image_name' => 'electricity_bill.jpg',
                'status' => 0,
                'created' => '2024-07-25 10:00:00',
                'modified' => '2024-07-25 10:00:00'
            ],
            [
                'unique_id' => $recordUniqueIds[1],
                'bill_unique_id' => 'BUID654321',
                'bill_group_unique_id' => 'BGUID654321',
                'payment_amount' => 150.75,
                'payment_date' => '2024-07-26 15:30:00',
                'payment_note' => 'August payment for water12',
                'image_name' => 'water_bill.jpg',
                'status' => 1,
                'created' => '2024-07-26 15:30:00',
                'modified' => '2024-07-26 15:30:00'
            ],
            [
                'unique_id' => $recordUniqueIds[2],
                'bill_unique_id' => 'BUID789012',
                'bill_group_unique_id' => 'BGUID789012',
                'payment_amount' => 300.00,
                'payment_date' => '2024-07-27 09:00:00',
                'payment_note' => 'September payment for gas',
                'image_name' => 'gas_bill.jpg',
                'status' => 0,
                'created' => '2024-07-27 09:00:00',
                'modified' => '2024-07-27 09:00:00'
            ]
        ];

        // Send a POST request to the addBillPayments endpoint with the test data
        $this->post('/mobileapi/MobileApiBillsPayments/addBillPayments', $datas);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);


        // Assert that the response indicates success
        $this->assertFalse($responseData['is_success'], 'The response should indicate failure.');
        $this->assertEquals( $responseData['message'], 'Bills payment are failed to save.', 'The failed message should match.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique IDs
        $query = $billsTable->find();//->where(['unique_id IN' => $recordUniqueIds]);
        $query->enableHydration(false);
        $records = $query->toArray();

       // debug($records);

        // Assert that exactly two records are returned from the query
        //$this->assertCount(0, $records, 'The query should return 0 record.');


    }

    /**
     * Test addBillPaymentWithOrWithoutImage method with valid image and valid data.
     *
     * Purpose:
     * This test case verifies that the `addBillPaymentWithOrWithoutImage` endpoint correctly handles
     * the submission of valid image data along with other bill payment details. It ensures
     * that the system processes the request, saves the image, stores the bill payment details
     * in the database, and returns a success response.
     *
     * Expected Result:
     * - The system should successfully save the image to the specified path.
     * - The bill payment details should be stored in the database.
     * - The response should indicate success and contain the expected success message.
     * - The uploaded image file should exist at the target path.
     * - The retrieved bill payment record from the database should match the submitted data.
     *
     * @return void
     */
    public function testAddBillPaymentWithImageSuccess(): void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique IDs for the test records
        $recordUniqueId = ['UID123456'];

        $testImagePath = Configure::read('test_bill_payment_images_path') . DS . "$recordUniqueId[0].png";
        $actualImagePath = Configure::read('bill_payment_images_path') . DS . "$recordUniqueId[0].png";

        // Check if the file exists
        if (!file_exists($testImagePath)) {
            $this->fail('The image file does not exist.');
        }

        // Create an UploadedFile object
        $uploadedFile = new UploadedFile(
            $testImagePath,
            filesize($testImagePath),
            UPLOAD_ERR_OK,
            'UID123456.png',
            'image/png'
        );

        $data = [
            'image' => $uploadedFile,
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        $this->post('/mobileapi/MobileApiBillsPayments/addBillPaymentWithOrWithoutImage', $data);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);
        
        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully saved.', $responseData['message'], 'The success message should match.');

        // Assert that the uploaded image file exists
        $this->assertFileExists($actualImagePath, 'The uploaded image file does not exist.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $records, 'The query should return one record.');

        // Assert properties or values for each record
        $this->assertEquals($recordUniqueId[0], $records[0]['unique_id']);

        // Loop through the records and validate each field
        foreach ($records as $record) {
            // Loop through each field in the record
            foreach ($record as $key => $value) {
                // Skip the 'id' field
                if ($key == 'id') {
                    continue;
                }

                // If the value is a FrozenTime object, format it for comparison
                if ($value instanceof FrozenTime) {
                    $datetimeFromRecord = $value->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($datetimeFromRecord, $data[$key], "Field '$key' does not match.");
                } else {
                    // Otherwise, directly compare the value
                    $this->assertEquals($value, $data[$key], "Field '$key' does not match.");
                }
            }
        }
    }

    /**
     * Test adding a bill payment without an image.
     *
     * This test case verifies that the `addBillPaymentWithOrWithoutImage` endpoint correctly handles 
     * and successfully saves a bill payment when no image is provided in the request.
     * It ensures that the system responds with a success message and that the data is correctly saved.
     *
     * @return void
     */
    public function testAddBillPaymentWithoutImageSuccess(): void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique IDs for the test records
        $recordUniqueId = ['UID123456'];

        $data = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        $this->post('/mobileapi/MobileApiBillsPayments/addBillPaymentWithOrWithoutImage', $data);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);
        
        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully saved.', $responseData['message'], 'The success message should match.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $records, 'The query should return one record.');

        // Assert properties or values for each record
        $this->assertEquals($recordUniqueId[0], $records[0]['unique_id']);

        // Loop through the records and validate each field
        foreach ($records as $record) {
            // Loop through each field in the record
            foreach ($record as $key => $value) {
                // Skip the 'id' field
                if ($key == 'id') {
                    continue;
                }

                // If the value is a FrozenTime object, format it for comparison
                if ($value instanceof FrozenTime) {
                    $datetimeFromRecord = $value->i18nFormat('yyyy-MM-dd HH:mm:ss');
                    $this->assertEquals($datetimeFromRecord, $data[$key], "Field '$key' does not match.");
                } else {
                    // Otherwise, directly compare the value
                    $this->assertEquals($value, $data[$key], "Field '$key' does not match.");
                }
            }
        }
    }

    /**
     * Test adding a bill payment with an invalid image.
     *
     * This test case verifies that the `addBillPaymentWithOrWithoutImage` endpoint correctly handles 
     * and returns an appropriate error message when an invalid image is provided in the request.
     * It ensures that the system responds with an error indicating that the image is invalid 
     * and does not save the data or image.
     *
     * @return void
     */
    public function testAddBillPaymentWithInvalidImageError(): void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique IDs for the test records
        $recordUniqueId = ['UID123456'];

        $testImagePath = Configure::read('test_bill_payment_images_path') . DS . "$recordUniqueId[0].png";
        $actualImagePath = Configure::read('bill_payment_images_path') . DS . "$recordUniqueId[0].png";

        // Check if the file exists
        if (!file_exists($testImagePath)) {
            $this->fail('The image file does not exist.');
        }

        $data = [
            'image' => '',
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        $this->post('/mobileapi/MobileApiBillsPayments/addBillPaymentWithOrWithoutImage', $data);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);
        
        // Assert that the response indicates success
        $this->assertFalse($responseData['is_success'], 'The response should indicate error.');
        $this->assertEquals('Failed to save bill payment. Invalid image.', $responseData['message'], 'The error message should match.');

        // Assert that the uploaded image file exists
        $this->assertFileDoesNotExist($actualImagePath, 'The uploaded image file does not exist test here.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that exactly one record is returned from the query
        $this->assertCount(0, $records, 'The query should return 0 record.');

    }

    /**
     * Test adding bill payment with missing required data.
     *
     * This test case verifies that the `addBillPaymentWithOrWithoutImage` endpoint correctly handles
     * and returns an appropriate error message when required data fields are missing.
     * It ensures that the system responds with an error indicating the missing fields.
     *
     * @return void
     */
    public function testAddBillPaymentWithImageMissingData(): void
    {
        $recordUniqueId = ['UID123456'];

        $data = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', // missing
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        $keyRequired = ['unique_id', 'bill_unique_id', 'bill_group_unique_id', 'payment_amount', 'payment_date', 'payment_note','status'];

        foreach ($keyRequired as $key) {
            // Create a copy of the data array
            $dataCopy = $data;
    
            // Unset the key in the copy
            unset($dataCopy[$key]);
            // Call the actual test function with the modified copy
            $this->addBillPaymentWithImageMissingKeyActualTest($dataCopy, $recordUniqueId[0], $key);
        }
    }

    /**
     * Test adding bill payment with a specific missing required key.
     *
     * This test case verifies that the `addBillPaymentWithImage` endpoint correctly handles
     * and returns an appropriate error message when a specific required key is missing from the data.
     * It ensures that the system responds with an error indicating the missing key.
     *
     * @param array $data The data to be sent in the request
     * @param string $keyToCheck The key that will be removed from the data
     * @return void
     */
    public function  addBillPaymentWithImageMissingKeyActualTest(array $data, string $uniqueid, $key): void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Send a POST request to the endpoint with the data
        $this->post('/mobileapi/MobileApiBillsPayments/addBillPaymentWithOrWithoutImage', $data);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);
     
        // Assert that the response indicates an error
        $this->assertFalse($responseData['is_success'], 'The response should indicate an error due to missing data.');
        $this->assertEquals('Failed to save bill payment.', $responseData['message'], 'The error message should match.');
        
        // Assert that the 'errors' key exists in the response
        $this->assertArrayHasKey('errors', $responseData, 'The response should contain an "errors" key.');

        // Assert that the 'errors' array contains the 'status' key
        $this->assertArrayHasKey($key, $responseData['errors'], 'The "errors" array should contain a "status" key.');

        //debug($responseData['errors'][$key]);
        // Assert that the 'status' array contains the '_required' key with the expected message
        $this->assertArrayHasKey('_required', $responseData['errors'][$key], 'The "status" error should contain a "_required" key.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique IDs
        $query = $billsTable->find()->where(['unique_id' => $uniqueid]);
        $query->enableHydration(false);
        $records = $query->toArray();

        // Assert that no records are returned from the query since the save should fail
        $this->assertCount(0, $records, 'The query should return no records due to the save failure.');
    }

   /**
     * Test the addition of a bill payment with an image and checks for unintended output.
     *
     * This test ensures that when adding a bill payment with an image via the `addBillPaymentWithOrWithoutImage` endpoint:
     * 1. No unintended output (such as debugging information) is generated.
     * 2. Only the expected JSON response is returned without any extra content.
     * 
     * @return void
     */
    public function testAddBillPaymentWithImageCheckedUnintendedOutput()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique ID for the test record
        $recordUniqueId = ['UID123456'];

        // Define the data to be posted to the addBillPaymentWithOrWithoutImage endpoint
        $data = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Start output buffering to capture any unintended output
        ob_start();
        
        // Send a POST request to the addBillPaymentWithOrWithoutImage endpoint with the test data
        $this->post('/mobileapi/MobileApiBillsPayments/addBillPaymentWithOrWithoutImage', $data);
        
        // Capture the echoed output
        $unintendedOutput = ob_get_clean(); // Retrieves and cleans the output buffer

        // Assert that no unintended output is present in the response
        $this->assertEmpty($unintendedOutput, 'Must not contain invalid output. Expected JSON data.');
    }
    /**
     * Test the addOrUpdateIfExists method in the MobileApiBillsPaymentsController.
     *
     * This test ensures that a new record can be created or an existing record can be updated
     * with or without an image, and verifies that the database and file system changes are as expected.
     */
    public function testAddOrUpdateIfExists_1()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the path to the test image and the actual image location
        $testImagePath = Configure::read('test_bill_payment_images_path') . DS . "$recordUniqueId[0].png";
        $actualImagePath = Configure::read('bill_payment_images_path') . DS . "$recordUniqueId[0].png";

        // Check if the test image file exists
        if (!file_exists($testImagePath)) {
            $this->fail('The image file does not exist.');
        }

        // Create an UploadedFile object for the test image
        $uploadedFile = new UploadedFile(
            $testImagePath,
            filesize($testImagePath),
            UPLOAD_ERR_OK,
            'UID123456.png',
            'image/png'
        );

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record
        $newData = [
            'image' => $uploadedFile,
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Send a POST request to the addOrUpdateIfExistsWithOrWithoutImage endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/addOrUpdateIfExists', $newData);
        
 
        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);
   
        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully saved.', $responseData['message'], 'The success message should match.');

        // Assert that the uploaded image file exists at the expected location
        $this->assertFileExists($actualImagePath, 'The uploaded image file does not exist.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();
        
        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Remove the image field from newData for comparison purposes
        unset($newData['image']);

        // Loop through each key-value pair in newData
        foreach ($newData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $newData[$key], "The value for '$key' does not match.");
            } else {
                // Assert that each value in newData matches the corresponding value in the record
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }

    /**
     * Test function to verify the addOrUpdateIfExists method without an image
     * This test ensures that the function successfully updates or adds a record without an image.
     */
    public function testAddOrUpdateIfExists_2()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record
        $newData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Send a POST request to the addOrUpdateIfExistsWithOrWithoutImage endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/addOrUpdateIfExists', $newData);

        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);
        
        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully saved.', $responseData['message'], 'The success message should match.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();
        
        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Remove the image field from newData for comparison purposes
        unset($newData['image']);

        // Loop through each key-value pair in newData
        foreach ($newData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $newData[$key], "The value for '$key' does not match.");
            } else {
                // Assert that each value in newData matches the corresponding value in the record
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }
    /**
     * Test function to verify the addOrUpdateIfExists method when some keys are missing.
     * Expected result: The function should return false indicating failure to save the record.
     */
    public function testAddOrUpdateIfExists_3()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record
        $newData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Define required keys for the test
        $keyRequired = ['unique_id', 'bill_unique_id', 'bill_group_unique_id', 'payment_amount', 'payment_date', 'payment_note', 'status'];

        // Iterate over each required key, unset it in the data, and call the test function
        foreach ($keyRequired as $key) {
            // Create a copy of the data array
            $dataCopy = $newData;
        
            // Unset the key in the copy
            unset($dataCopy[$key]);

            // Call the actual test function with the modified copy
            $this->addOrUpdateIfExists_3($billsTable, $dataCopy, $initialData, $recordUniqueId[0], $key);
        }
    }

    /**
     * Function to handle the actual test logic for missing keys in function testAddOrUpdateIfExists_3()
     * 
     * @param Table $billsTable The BillsPayments table instance.
     * @param array $newData The data array with a key missing.
     * @param array $initialData The initial data saved in the database.
     * @param string $recordUniqueId The unique ID of the record.
     * @param string $key The key that is missing in the newData array.
     */
    public function addOrUpdateIfExists_3(Table $billsTable, array $newData, array $initialData, string $recordUniqueId)
    {
        // Send a POST request to the addOrUpdateIfExistsWithOrWithoutImage endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/addOrUpdateIfExists', $newData);

        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates failure
        $this->assertFalse($responseData['is_success'], 'The response should indicate failure.');
        $this->assertEquals('Failed to save bill payment.', $responseData['message'], 'The failure message should match.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();

        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Compare the initial data with the record in the database
        foreach ($initialData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $initialData[$key], "The value for '$key' does not match.");
            } else {
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }
    /**
     * Test function to verify response error when image data is invalid.
     * Expected result: failure due to invalid image.
     */
    public function testAddOrUpdateIfExists_4()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record with an invalid image
        $newData = [
            'image' => '',
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
         // Save the initial data to the database and ensure it is saved correctly
        $initialSaveResult = $billsTable->save($billsTable->newEntity($initialData));
        $this->assertNotFalse($initialSaveResult, 'Failed to save initial data.');

        // Send a POST request to the addOrUpdateIfExistsWithOrWithoutImage endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/addOrUpdateIfExists', $newData);

        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates failure due to invalid image
        $this->assertFalse($responseData['is_success'], 'The response should indicate failure.');
        $this->assertEquals('Failed to save bill payment. Invalid image.', $responseData['message'], 'The failure message should match.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();

        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Loop through each key-value pair in initial data to assert the values remain unchanged
        foreach ($initialData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $initialData[$key], "The value for '$key' does not match.");
            } else {
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }

   /**
     * Test case to ensure that errors are returned when required fields are empty.
     *
     * This function tests the behavior of the `addOrUpdateIfExists` endpoint by
     * submitting data with required fields left empty to verify that appropriate error messages
     * are returned and the data remains unchanged in the database.
     *
     * @return void
     */
    function testAddOrUpdateIfExists_5()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record with, including an invalid image
        $newData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Save the initial data to the database and ensure it is saved correctly
        $initialSaveResult = $billsTable->save($billsTable->newEntity($initialData));
        $this->assertNotFalse($initialSaveResult, 'Failed to save initial data.');

        // Get the validator for the 'update' operation
        $validator = $billsTable->getValidator('forUpdate');
    
        // Initialize an array to hold fields that are not allowed to be empty
        $notEmptyFields = [];

        // Loop through validator fields to find those that are required
        foreach ($validator->__debugInfo()['_fields'] as $field => $properties) {
            if ($properties['isEmptyAllowed'] == false) {
                $notEmptyFields[] = $field;
            }
        }

        // For each required field, create a copy of the data with that field set to empty
        foreach ($notEmptyFields as $key) {
            $dataCopy = $newData;
            $dataCopy[$key] = '';
            
            // Call the actual test function with the modified data
            $this->addOrUpdateIfExists_5($initialData, $dataCopy, $recordUniqueId[0], $key);
        }
    }

    /**
     * Test function to check the response of the `addOrUpdateIfExists` endpoint
     * when required fields are missing.
     *
     * This function sends a POST request with missing required fields and verifies that the response
     * indicates an error and that the database record remains unchanged.
     *
     * @param array $initialData The initial data used to set up the test.
     * @param array $newData The data to be sent in the request, with one or more required fields left empty.
     * @param string $uniqueid The unique ID of the record being tested.
     * @param string $key The specific field that was left empty.
     * @return void
     */
    public function addOrUpdateIfExists_5(array $initialData, array $newData, string $uniqueid, $key): void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Send a POST request to the endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/addOrUpdateIfExists', $newData);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates an error due to missing data
        $this->assertFalse($responseData['is_success'], 'The response should indicate an error due to missing data.');
        $this->assertEquals('Failed to save bill payment.', $responseData['message'], 'The error message should match.');
        
        // Assert that the 'errors' key exists in the response
        $this->assertArrayHasKey('errors', $responseData, 'The response should contain an "errors" key.');

        // Assert that the 'errors' array contains the specific field that was left empty
        $this->assertArrayHasKey($key, $responseData['errors'], 'The "errors" array should contain the specified key.');

        // Assert that the specific field's error contains the '_empty' key with the expected message
        $this->assertArrayHasKey('_empty', $responseData['errors'][$key], 'The "errors" array should contain the "_empty" key.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique ID
        $query = $billsTable->find()->where(['unique_id' => $uniqueid]);
        $query->enableHydration(false);
        $record = $query->toArray();

        // Assert that one record is returned from the query (the save should fail)
        $this->assertCount(1, $record, 'The query should return one record.');

        // Loop through each key-value pair in initial data to assert the values remain unchanged
        foreach ($initialData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $initialData[$key], "The value for '$key' does not match.");
            } else {
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }

    /**
     * Test the addition or update of a record with an invalid image.
     *
     * This test case verifies that:
     * 1. An existing record can be updated correctly when an invalid image is provided.
     * 2. No unintended output (such as debugging information) is generated during the process.
     * 
     * The test focuses on ensuring that the endpoint handles the update operation correctly 
     * and returns only the expected JSON response without any extra content.
     * 
     * @return void
     */
    public function testAddOrUpdateIfExists_6()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record with an invalid image
        $newData = [
            'image' => '', // Simulate an invalid image input
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        $initialSaveResult = $billsTable->save($billsTable->newEntity($initialData));
        $this->assertNotFalse($initialSaveResult, 'Failed to save initial data.');

        // Start output buffering to capture any unintended output
        ob_start();
        
        // Send a POST request to the addOrUpdateIfExistsWithOrWithoutImage endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/addOrUpdateIfExists', $newData);
        
        // Capture the echoed output
        $unintendedOutput = ob_get_clean(); // Retrieves and cleans the output buffer

        // Assert that no unintended output is present in the response
        $this->assertEmpty($unintendedOutput, 'Must not contain invalid output. Expected JSON data.');
    }
    /**
     * Test function to ensure all data are saved correctly when updating a bill payment.
     *
     * This function verifies that a bill payment record can be updated with new data, 
     * including an uploaded image, and that all fields are correctly saved in the database.
     *
     * @return void
     */
    public function testUpdateBillPayments_1()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the path to the test image and the actual image location
        $testImagePath = Configure::read('test_bill_payment_images_path') . DS . "$recordUniqueId[0].png";
        $actualImagePath = Configure::read('bill_payment_images_path') . DS . "$recordUniqueId[0].png";

        // Check if the test image file exists
        if (!file_exists($testImagePath)) {
            $this->fail('The image file does not exist.');
        }

        // Create an UploadedFile object for the test image
        $uploadedFile = new UploadedFile(
            $testImagePath,
            filesize($testImagePath),
            UPLOAD_ERR_OK,
            'UID123456.png',
            'image/png'
        );

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012', 
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record
        $newData = [
            'image' => $uploadedFile,
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Send a POST request to the updateBillPayments endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/updateBillPayments', $newData);

        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully saved.', $responseData['message'], 'The success message should match.');

        // Assert that the uploaded image file exists at the expected location
        $this->assertFileExists($actualImagePath, 'The uploaded image file does not exist.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();
        
        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Remove the image field from newData for comparison purposes
        unset($newData['image']);

        // Loop through each key-value pair in newData
        foreach ($newData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $newData[$key], "The value for '$key' does not match.");
            } else {
                // Assert that each value in newData matches the corresponding value in the record
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }
    /**
     * Test function to ensure that bill payment data is updated successfully without an image.
     *
     * This function verifies that a bill payment record can be updated with new data,
     * and that all fields are correctly saved in the database without including an image file.
     *
     * @return void
     */
    public function testUpdateBillPayments_2()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record
        $newData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Send a POST request to the updateBillPayments endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/updateBillPayments', $newData);

        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates success
        $this->assertTrue($responseData['is_success'], 'The response should indicate success.');
        $this->assertEquals('Bills payment successfully saved.', $responseData['message'], 'The success message should match.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();
        
        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Remove the image field from newData for comparison purposes
        unset($newData['image']);

        // Loop through each key-value pair in newData
        foreach ($newData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $newData[$key], "The value for '$key' does not match.");
            } else {
                // Assert that each value in newData matches the corresponding value in the record
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }

    /**
     * Test function to ensure failure message if invalid image is provided.
     *
     * This function verifies that when an empty image is sent in the request,
     * the response indicates a failure due to an invalid image, and the database
     * record remains unchanged.
     *
     * @return void
     */
    public function testUpdateBillPayments_3()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record, including an empty image
        $newData = [
            'image' => '',
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Send a POST request to the updateBillPayments endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/updateBillPayments', $newData);

        // Decode the JSON response from the POST request
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates failure due to an invalid image
        $this->assertFalse($responseData['is_success'], 'The response should indicate failure.');
        $this->assertEquals('Failed to save bill payment. Invalid image.', $responseData['message'], 'The failed message should match.');

        // Query the database to retrieve the record with the specified unique ID
        $query = $billsTable->find()->where(['unique_id IN' => $recordUniqueId]);
        $query->enableHydration(false);
        $record = $query->toArray();
        
        // Assert that exactly one record is returned from the query
        $this->assertCount(1, $record, 'The query should return one record.');

        // Compare each field in initialData with the corresponding field in the database record
        foreach ($initialData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $initialData[$key], "The value for '$key' does not match.");
            } else {
                // Assert that each value in initialData matches the corresponding value in the record
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }

    /**
     * This test function ensures that the update fails due to missing keys or data.
     * The data in the database should remain unchanged.
     *
     * @return void
     */
    public function testUpdateBillPayment_4()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define a unique ID for the record to be tested
        $recordUniqueId = ['UID123456'];

        // Define the initial data to be saved in the database
        $initialData = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Define the new data to update the record, including an empty image
        $newData = [
            'image' => '',
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 500.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas updated',
            'image_name' => 'gas_bill_update.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-28 09:00:00'
        ];

        // Get the BillsPayments table instance
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');
        
        // Save the initial data to the database and ensure it is saved correctly
        if (!$billsTable->save($billsTable->newEntity($initialData))) {
            $this->fail('Failed to save initial data.');
        }

        // Get the validator for the 'update' operation
        $validator = $billsTable->getValidator('forUpdate');

        // Initialize an array to hold fields that are not allowed to be empty
        $notEmptyFields = [];

        // Loop through validator fields to find those that are required
        foreach ($validator->__debugInfo()['_fields'] as $field => $properties) {
            if ($properties['isEmptyAllowed'] == false) {
                $notEmptyFields[] = $field;
            }
        }

        // For each required field, create a copy of the data with that field set to empty
        foreach ($notEmptyFields as $key) {
            $dataCopy = $newData;
            $dataCopy[$key] = '';
            
            // Call the actual test function with the modified data
            $this->updateBillPayment_4($initialData, $dataCopy, $recordUniqueId[0], $key);
        }
    }

    /**
     * Helper function to test the update operation with a specific field left empty.
     *
     * This function sends a POST request with the modified data and verifies that
     * the response indicates an error due to missing data, and the database record
     * remains unchanged.
     *
     * @param array $initialData The initial data used to set up the test.
     * @param array $newData The data to be sent in the request, with one required field left empty.
     * @param string $uniqueid The unique ID of the record being tested.
     * @param string $key The specific field that was left empty.
     * @return void
     */
    public function updateBillPayment_4(array $initialData, array $newData, string $uniqueid, $key): void
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Send a POST request to the endpoint with the new data
        $this->post('/mobileapi/MobileApiBillsPayments/updateBillPayments', $newData);

        // Decode the JSON response
        $responseData = json_decode((string)$this->_response->getBody(), true);

        // Assert that the response indicates an error due to missing data
        $this->assertFalse($responseData['is_success'], 'The response should indicate an error due to missing data.');
        $this->assertEquals('Failed to save bill payment.', $responseData['message'], 'The error message should match.');
        
        // Assert that the 'errors' key exists in the response
        $this->assertArrayHasKey('errors', $responseData, 'The response should contain an "errors" key.');

        // Assert that the 'errors' array contains the specific field that was left empty
        $this->assertArrayHasKey($key, $responseData['errors'], 'The "errors" array should contain the specified key.');

        // Assert that the specific field's error contains the '_empty' key with the expected message
        $this->assertArrayHasKey('_empty', $responseData['errors'][$key], 'The "errors" array should contain the "_empty" key.');

        // Get the BillsPayments table
        $billsTable = TableRegistry::getTableLocator()->get('BillsPayments');

        // Query the database to retrieve the records with the specified unique ID
        $query = $billsTable->find()->where(['unique_id' => $uniqueid]);
        $query->enableHydration(false);
        $record = $query->toArray();

        // Assert that one record is returned from the query (the save should fail)
        $this->assertCount(1, $record, 'The query should return one record.');

        // Loop through each key-value pair in initial data to assert the values remain unchanged
        foreach ($initialData as $key => $value) {
            if (in_array($key, ['payment_date', 'created', 'modified'])) {
                // Format the date/time fields for comparison
                $formattedDatetime = (new \Cake\I18n\FrozenTime($record[0][$key]))->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $this->assertEquals($formattedDatetime, $initialData[$key], "The value for '$key' does not match.");
            } else {
                $this->assertEquals($value, $record[0][$key], "The value for '$key' does not match.");
            }
        }
    }

    public function testUpdateBillPayment_5()
    {
        // Enable CSRF token protection for the request
        $this->enableCsrfToken();

        // Define the unique ID for the test record
        $recordUniqueId = ['UID123456'];

        // Define the data to be posted to the addBillPaymentWithOrWithoutImage endpoint
        $data = [
            'unique_id' => $recordUniqueId[0],
            'bill_unique_id' => 'BUID789012',
            'bill_group_unique_id' => 'BGUID789012',
            'payment_amount' => 300.00,
            'payment_date' => '2024-07-27 09:00:00',
            'payment_note' => 'September payment for gas',
            'image_name' => 'gas_bill.jpg',
            'status' => 0,
            'created' => '2024-07-27 09:00:00',
            'modified' => '2024-07-27 09:00:00'
        ];

        // Start output buffering to capture any unintended output
        ob_start();
        
        // Send a POST request to the addBillPaymentWithOrWithoutImage endpoint with the test data
        $this->post('/mobileapi/MobileApiBillsPayments/updateBillPayments', $data);
        
        // Capture the echoed output
        $unintendedOutput = ob_get_clean(); // Retrieves and cleans the output buffer

        // Assert that no unintended output is present in the response
        $this->assertEmpty($unintendedOutput, 'Must not contain invalid output. Expected JSON data.');
    }


}
