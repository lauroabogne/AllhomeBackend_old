<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class BillsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('bills');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'modified' => 'always'
                ]
            ]
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('unique_id')
            ->maxLength('unique_id', 255)
            ->requirePresence('unique_id', 'create')
            ->notEmptyString('unique_id');

        $validator
            ->scalar('group_unique_id')
            ->maxLength('group_unique_id', 255)
            ->allowEmptyString('group_unique_id');

        $validator
            ->decimal('amount')
            ->allowEmptyString('amount');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('category')
            ->maxLength('category', 255)
            ->allowEmptyString('category');

        $validator
            ->dateTime('due_date')
            ->allowEmptyDateTime('due_date');

        $validator
            ->boolean('is_recurring')
            ->allowEmptyString('is_recurring');

        $validator
            ->integer('repeat_every')
            ->allowEmptyString('repeat_every');

        $validator
            ->scalar('repeat_by')
            ->maxLength('repeat_by', 255)
            ->allowEmptyString('repeat_by');

        $validator
            ->scalar('repeat_until')
            ->maxLength('repeat_until', 255)
            ->allowEmptyString('repeat_until');

        $validator
            ->integer('repeat_count')
            ->allowEmptyString('repeat_count');

        $validator
            ->scalar('image_name')
            ->maxLength('image_name', 255)
            ->allowEmptyString('image_name');

        $validator
            ->boolean('status')
            ->allowEmptyString('status');

        return $validator;
    }
}
