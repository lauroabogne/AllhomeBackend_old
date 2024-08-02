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