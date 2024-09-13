<?php
namespace App\Controller\MobileApi;

use Cake\I18n\Time;
use App\Controller\AppController;
use Cake\Controller\Controller;
use DateTime;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableLocator;
use Cake\Http\Response;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;
use App\Utility\ArrayUtilities;

class MobileApiBillsController extends MobileApiController
{
  


  public function initialize(): void
    {
        parent::initialize();
        
    }

    
    public function index()
    {


        $this->autoRender = false;
      
        $billTable = $this->fetchTable('Bills');
        $bills =  $billTable->find('all');
        $this->set(compact('bills'));
    }

    public function add()
    {
        try{

          $requestData = $this->request->getData();

          if(empty($requestData) || count($requestData) == 0 ){
             

            return $this->createErrorResponse('Nothing to save.');
          }

          $billTable = $this->fetchTable('Bills');
          $bills =  $billTable->newEntities($this->request->getData());
          
          // Save all entities
          if ($billTable->saveMany($bills)) {
          
            return $this->createSuccessResponse('Bills save successfully.');

          } else {
         
            return $this->createErrorResponse('Bills could not be saved. Please, try again.');
            
          }
          
        }catch(\PDOException $e){
          
          Log::error('An error occurred: ' . $e->getMessage());

          return $this->createErrorResponse('Bills are failed to save.');

        }
    }

     /**
     * Insert or update bills based on their unique ID.
     *
     * This method handles the uploading of bills data. If a bill with a given unique ID already exists, it updates the
     * corresponding record. Otherwise, it inserts a new record. It validates the incoming data, and if there are any
     * validation errors, it returns an appropriate error response. If the data is successfully saved, it returns a success
     * response with an array of unique IDs for the saved records.
     *
     * @return \Cake\Http\Response|null Returns a JSON response indicating success or failure.
     * @throws \PDOException If there is an error during the database transaction.
     */
    public function uploadBills()
    {
        if ($this->request->is('post')) {
            
            // Get the BillsPayments table
            $billsTable = $this->fetchTable('Bills');
                
            try {
                // Get the request data
                $requestData = $this->request->getData();

                // Validate request data
                if ($this->isRequestDataEmpty($requestData)) {
                    return $this->createErrorResponse('Nothing to save.');
                }
                
                $entities = [];

                foreach ($requestData as $data) {
                    // Convert camelCase keys to snake_case
                    $transformedData = ArrayUtilities::convertCamelToSnake($data);

                 
                    

                    // Create a new entity with the transformed data and validate it
                    $billEntity = $billsTable->newEntity($transformedData, ['validate' => 'default']);

                    // Check for validation errors in the new entity
                    if ($billEntity->getErrors()) {
                        $errors = $billEntity->getErrors();
                        return $this->createErrorResponseForArray('Failed to save bills.', $errors);
                    }

                    $uniqueId = $transformedData['unique_id'];
                    // Check if a bill with the same unique ID already exists
                    $bill = $billsTable->getRecordByUniqueIdFrozenTimeConvertToStringDate($uniqueId);

                    if ($bill) {
                        // If the bill exists, patch the entity with the new data for updating
                        $patchedBillEntity = $billsTable->patchEntity($bill, $transformedData);
                        $entities[] = $patchedBillEntity;
                    } else {
                        // If the bill doesn't exist, add it as a new entity for insertion
                        $entities[] = $billEntity;
                    }
                }
                
                // Save all entities (both new and patched) in a single transaction
                $savedEntities = $billsTable->saveMany($entities);

                // Check if saveMany was successful
                if ($savedEntities === false) {
                    return $this->createErrorResponse('Bills payment failed to save.');
                }
                
                // Extract unique IDs from the saved entities
                $savedUniqueIds = $this->extractUniqueIds($savedEntities);
                return $this->createSuccessResponseWithArray("Bill saved successfully.", $savedUniqueIds);

            } catch (\PDOException $e) {
                // Log the error and return an error response
                Log::error('An error occurred: ' . $e->getMessage());
                return $this->createErrorResponse('Bills payment failed to save.');
            }
        }
    }


    /**
     * Extract unique IDs from an array of saved entities.
     *
     * @param array $entities Array of saved entities.
     * @return array Array of unique IDs.
     */
    public function extractUniqueIds($entities): array
    {
        $uniqueIds = [];

        foreach ($entities as $entity) {
            if ($entity->get('unique_id')) {
                $uniqueIds[] = $entity->get('unique_id');
            }
        }

        return $uniqueIds;
    }
    public function edit($id = null)
    {
        $bill = $this->Bills->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bill = $this->Bills->patchEntity($bill, $this->request->getData());
            if ($this->Bills->save($bill)) {
                $this->Flash->success(__('The bill has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bill could not be saved. Please, try again.'));
        }
        $this->set(compact('bill'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bill = $this->Bills->get($id);
        if ($this->Bills->delete($bill)) {
            $this->Flash->success(__('The bill has been deleted.'));
        } else {
            $this->Flash->error(__('The bill could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
  
    public function computeRecord(){
      $response = $this->response->withType('application/json')
        ->withStringBody(json_encode(['message' => 'Welcome to the Mobile API!']));
      return $response;
    }

    public function csrfToken()
    {
      $this->request->allowMethod(['get']); // Allow only GET requests for this action
        
        // Get the CSRF token
        $csrfToken = $this->request->getAttribute('csrfToken');
        
        // Create a response object
        $response = new Response();
        
        // Set the response to JSON format
        $response = $response->withType('application/json')
                             ->withStringBody(json_encode(['csrfToken' => $csrfToken]));
                             
        return $response;

        
    }
}