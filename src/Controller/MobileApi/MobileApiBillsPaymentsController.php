<?php
declare(strict_types=1);

namespace App\Controller\MobileApi;

use App\Controller\AppController;
use Cake\Log\Log;
use Cake\Core\Configure;
use App\Utility\FileUtilities;

/**
 * MobileApiBillsPayments Controller
 *
 * @property \App\Model\Table\BillsPaymentsTable $BillsPayments
 * @method \App\Model\Entity\BillsPayment[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MobileApiBillsPaymentsController extends MobileApiController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {


        $billTable = $this->fetchTable('BillsPayments');
        $billsPayments = $this->paginate( $billTable);
        $bills =  $billTable->find('all');
        // $this->set(compact('bills'));

        // //$this->set(compact('billsPayments'));
        $this->autoRender = false;
        // debug($bills->toArray());
    }

    /**
     * View method
     *
     * @param string|null $id Bills Payment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $billsPayment = $this->BillsPayments->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('billsPayment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function addBillPayments()
    {
        
        $billsPaymentTable = $this->fetchTable('BillsPayments');

        if($this->request->is('post')){

            try{
            $requestData = $this->request->getData();
            
            if($this->isRequestDataEmpty($requestData)){

              return $this->createErrorResponse('Nothing to save.');
            }

            $billsPaymentEntities = $billsPaymentTable->newEntities($this->request->getData());

            if ($billsPaymentTable->saveMany($billsPaymentEntities)) {
          
                return $this->createSuccessResponse('Bills payment successfully.');
    
              } else {
             
                return $this->createErrorResponse('Bills payment could not be saved. Please, try again.');
                
              }
            }catch(\PDOException $e){
          
                Log::error('An error occurred: ' . $e->getMessage());
      
                return $this->createErrorResponse('Bills payment are failed to save.');
      
              }
        }
    }
    /**
     * Handles the addition of bill payments along with an uploaded image.
     *
     * This method processes the request to add a new bill payment. It validates the request data, 
     * checks the uploaded image for validity, and saves the image to the specified directory.
     * Finally, it saves the bill payment data to the database.
     *
     * @return \Cake\Http\Response|null A response object containing the result of the operation.
     */
    public function addBillPaymentWithOrWithoutImage()
    {
        if ($this->request->is('post')) {
            try {
                $requestData = $this->request->getData();
                
                // Check if image is uploaded
                $image = $requestData['image'] ?? null;
                unset($requestData['image']); // Remove image from request data before validating and saving other data

                // Validate request data
                if ($this->isRequestDataEmpty($requestData)) {
                    return $this->createErrorResponse('Nothing to save.');
                }

                // Fetch the BillsPayments table
                $billsPaymentTable = $this->fetchTable('BillsPayments');
                $billsPaymentEntity = $billsPaymentTable->newEntity($requestData);

                // Check for validation errors in the entity
                if ($billsPaymentEntity->getErrors()) {
                    $errors = $billsPaymentEntity->getErrors();
                    return $this->createErrorResponseForArray('Failed to save bill payment.', $errors);
                }

                if ($image !== null) {

                    // Validate the uploaded image
                    if (!FileUtilities::isValidImage($image)) {
                        return $this->createErrorResponse('Failed to save bill payment. Invalid image.');
                    }

                    // Check the size of the uploaded image
                    $uploadFileSizeLimit = Configure::read('upload_size_limit');
                    if (FileUtilities::getImageSizeInMb($image) > $uploadFileSizeLimit) {
                        return $this->createErrorResponse("Failed to save bill payment. Limit of file size is $uploadFileSizeLimit MB.");
                    }

                    // Save the uploaded image
                    $doSavingImageSuccess = FileUtilities::saveImage($image, Configure::read('bill_payment_images_path'), $requestData['unique_id']);
                    if (!$doSavingImageSuccess) {
                        return $this->createErrorResponse('Failed to save image.');
                    }
                }
                
                // Save the bill payment entity to the database
                if ($billsPaymentTable->save($billsPaymentEntity)) {
                    return $this->createSuccessResponse('Bills payment successfully saved.');
                } else {
                    return $this->createErrorResponse('Bills payment could not be saved. Please, try again.');
                }
            } catch (\PDOException $e) {
                Log::error('An error occurred: ' . $e->getMessage());
                return $this->createErrorResponse('Bills payment failed to save.');
            }
        }
    }

  
    public function updateBillPayments(){
        
        if ($this->request->is('post')) {
            // Get the BillsPayments table
            $billsPaymentTable = $this->fetchTable('BillsPayments');
            
            // Start a new transaction
            $connection = $billsPaymentTable->getConnection();
            $connection->begin();
            
            try {
                // Get the request data
                $requestData = $this->request->getData();

                // Check if an image is uploaded and remove it from request data for validation
                $image = $requestData['image'] ?? null;
                unset($requestData['image']);

                // Validate request data
                if ($this->isRequestDataEmpty($requestData)) {
                    $connection->rollback();
                    return $this->createErrorResponse('Nothing to save.');
                }

                // Create a new entity with the provided data and validation rules
                $billsPaymentEntity = $billsPaymentTable->newEntity($requestData, ['validate' => 'ForUpdate']);

                // Check for validation errors in the new entity
                if ($billsPaymentEntity->getErrors()) {
                    $errors = $billsPaymentEntity->getErrors();
                    $connection->rollback();
                    return $this->createErrorResponseForArray('Failed to save bill payment.', $errors);
                }

                // Check if a record with the given unique ID already exists in the database
                $entityFromDatabase = $billsPaymentTable->getByUniqueId($billsPaymentEntity->unique_id ?? '');

                if( ! $entityFromDatabase ){
                    $connection->rollback();
                    return $this->createErrorResponse("Bill payment with id {$requestData['unique_id']} not found.");
                }
               

                if ($entityFromDatabase) {
                    // Update the existing record
                    $billsPaymentPatchEntity = $billsPaymentTable->patchEntity($entityFromDatabase, $requestData, [
                        'validate' => 'ForUpdate'
                    ]);

                    // Check for validation errors in the patched entity
                    if ($billsPaymentPatchEntity->getErrors()) {
                        $errors = $billsPaymentPatchEntity->getErrors();
                        $connection->rollback();
                        return $this->createErrorResponseForArray('Failed to save bill payment.', $errors);
                    }

                    // Save the updated bill payment entity
                    if (!$billsPaymentTable->save($billsPaymentPatchEntity)) {
                        $connection->rollback();
                        return $this->createErrorResponse('Bills payment could not be saved. Please, try again.');
                    }

                } else {
                    // Save the new bill payment entity
                    if (!$billsPaymentTable->save($billsPaymentEntity)) {
                        $connection->rollback();
                        return $this->createErrorResponse('Bills payment could not be saved. Please, try again.');
                    }
                }

                // Handle image upload if an image is provided
                if ($image !== null) {
                    // Validate the uploaded image
                    if (!FileUtilities::isValidImage($image)) {
                        $connection->rollback();
                        return $this->createErrorResponse('Failed to save bill payment. Invalid image.');
                    }

                    // Check the size of the uploaded image
                    $uploadFileSizeLimit = Configure::read('upload_size_limit');
                    if (FileUtilities::getImageSizeInMb($image) > $uploadFileSizeLimit) {
                        $connection->rollback();
                        return $this->createErrorResponse("Failed to save bill payment. Limit of file size is $uploadFileSizeLimit MB.");
                    }

                    // Save the uploaded image
                    $doSavingImageSuccess = FileUtilities::saveImage($image, Configure::read('bill_payment_images_path'), $requestData['unique_id']);
                    if (!$doSavingImageSuccess) {
                        $connection->rollback();
                        return $this->createErrorResponse('Failed to save image.');
                    }
                }

                // Commit the transaction
                $connection->commit();

                return $this->createSuccessResponse('Bills payment successfully saved.');

            } catch (\PDOException $e) {
                // Rollback the transaction in case of error
                $connection->rollback();
                Log::error('An error occurred: ' . $e->getMessage());
                return $this->createErrorResponse('Bills payment failed to save.');
            }
        }
    }

    /**
     * Add or update a bill payment record with or without an image.
     *
     * This method handles the creation of a new bill payment record or updates an existing one
     * based on the provided data. It validates the input, manages transactions, and handles
     * image uploads if an image is included in the request.
     *
     * @return \Cake\Http\Response|null Returns a JSON response indicating success or failure.
     */
    public function addOrUpdateIfExists()
    {
        if ($this->request->is('post')) {
            // Get the BillsPayments table
            $billsPaymentTable = $this->fetchTable('BillsPayments');
            
            // Start a new transaction
            $connection = $billsPaymentTable->getConnection();
            $connection->begin();
            
            try {
                // Get the request data
                $requestData = $this->request->getData();

                // Check if an image is uploaded and remove it from request data for validation
                $image = $requestData['image'] ?? null;
                unset($requestData['image']);

                // Validate request data
                if ($this->isRequestDataEmpty($requestData)) {
                    $connection->rollback();
                    return $this->createErrorResponse('Nothing to save.');
                }

                // Create a new entity with the provided data and validation rules
                $billsPaymentEntity = $billsPaymentTable->newEntity($requestData, ['validate' => 'ForUpdate']);

                // Check for validation errors in the new entity
                if ($billsPaymentEntity->getErrors()) {
                    $errors = $billsPaymentEntity->getErrors();
                    $connection->rollback();
                    return $this->createErrorResponseForArray('Failed to save bill payment.', $errors);
                }

                // Check if a record with the given unique ID already exists in the database
                $entityFromDatabase = $billsPaymentTable->getByUniqueId($billsPaymentEntity->unique_id ?? '');

                if ($entityFromDatabase) {
                    // Update the existing record
                    $billsPaymentPatchEntity = $billsPaymentTable->patchEntity($entityFromDatabase, $requestData, [
                        'validate' => 'ForUpdate'
                    ]);

                    // Check for validation errors in the patched entity
                    if ($billsPaymentPatchEntity->getErrors()) {
                        $errors = $billsPaymentPatchEntity->getErrors();
                        $connection->rollback();
                        return $this->createErrorResponseForArray('Failed to save bill payment.', $errors);
                    }

                    // Save the updated bill payment entity
                    if (!$billsPaymentTable->save($billsPaymentPatchEntity)) {
                        $connection->rollback();
                        return $this->createErrorResponse('Bills payment could not be saved. Please, try again.');
                    }

                } else {
                    // Save the new bill payment entity
                    if (!$billsPaymentTable->save($billsPaymentEntity)) {
                        $connection->rollback();
                        return $this->createErrorResponse('Bills payment could not be saved. Please, try again.');
                    }
                }

                // Handle image upload if an image is provided
                if ($image !== null) {
                    // Validate the uploaded image
                    if (!FileUtilities::isValidImage($image)) {
                        $connection->rollback();
                        return $this->createErrorResponse('Failed to save bill payment. Invalid image.');
                    }

                    // Check the size of the uploaded image
                    $uploadFileSizeLimit = Configure::read('upload_size_limit');
                    if (FileUtilities::getImageSizeInMb($image) > $uploadFileSizeLimit) {
                        $connection->rollback();
                        return $this->createErrorResponse("Failed to save bill payment. Limit of file size is $uploadFileSizeLimit MB.");
                    }

                    // Save the uploaded image
                    $doSavingImageSuccess = FileUtilities::saveImage($image, Configure::read('bill_payment_images_path'), $requestData['unique_id']);
                    if (!$doSavingImageSuccess) {
                        $connection->rollback();
                        return $this->createErrorResponse('Failed to save image.');
                    }
                }

                // Commit the transaction
                $connection->commit();

                return $this->createSuccessResponse('Bills payment successfully saved.');

            } catch (\PDOException $e) {
                // Rollback the transaction in case of error
                $connection->rollback();
                Log::error('An error occurred: ' . $e->getMessage());
                return $this->createErrorResponse('Bills payment failed to save.');
            }
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Bills Payment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $billsPayment = $this->BillsPayments->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $billsPayment = $this->BillsPayments->patchEntity($billsPayment, $this->request->getData());
            if ($this->BillsPayments->save($billsPayment)) {
                $this->Flash->success(__('The bills payment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bills payment could not be saved. Please, try again.'));
        }
        $this->set(compact('billsPayment'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bills Payment id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $billsPayment = $this->BillsPayments->get($id);
        if ($this->BillsPayments->delete($billsPayment)) {
            $this->Flash->success(__('The bills payment has been deleted.'));
        } else {
            $this->Flash->error(__('The bills payment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}
