<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\I18n\FrozenTime;
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
            ->add('amount', 'validNumber', [
                'rule' => function ($value, $context) {
                    return is_numeric($value);
                },
                'message' => 'Amount must be a valid number.'
            ])
            ->requirePresence('amount', 'create') // Ensures presence on creation
            ->notEmptyString('amount', 'Amount is required');
        

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->add('name', 'must_not_empty', [
                'rule' => function ($value, $context) {
                    $trimmed = trim($value);
                    return !empty($trimmed);
                },
                'message' => 'Name is required and cannot be empty after trimming.'
            ]);

        $validator
            ->scalar('category')
            ->maxLength('category', 255)
            ->allowEmptyString('category');

        $validator
            ->date('due_date', ['ymd'], 'Invalid date format.')
            ->allowEmptyDate('due_date');

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
            ->allowEmptyDate('repeat_until')
            ->add('repeat_until', 'validFormat', [
                'rule' => [$this, 'validateDateTime'],
                'message' => 'Invalid date and time format. Use YYYY-MM-DD HH:MM:SS.'
            ])
            ->requirePresence('repeat_until', 'create', 'Repeat until date is required.');

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

   /**
     * Validate date and time format.
     *
     * @param string $value The value to validate.
     * @param array $context Validation context.
     * @return bool
     */
    public function validateDateTime($value, $context): bool
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        return $dateTime && $dateTime->format('Y-m-d H:i:s') === $value;
    }
    public function getRecordByUniqueIdFrozenTimeConvertToStringDate($uniqueId)
    {
        $record = $this->find()
            ->where(['unique_id' => $uniqueId])
            ->first();

       return $record;
    }

    /**
     * Recursively converts FrozenTime values in the array to strings.
     *
     * This method traverses the provided array and converts any instances of FrozenTime
     * to a formatted string representation. It handles nested arrays as well.
     *
     * @param array $data The data array potentially containing FrozenTime instances.
     * @return array The array with FrozenTime values converted to strings.
     */
    private function convertFrozenTimeToString(array $data): array
    {
        foreach ($data as &$value) {
            // Check if the current value is an instance of FrozenTime
            if ($value instanceof FrozenTime) {
                // Format the FrozenTime instance as a string in 'Y-m-d H:i:s' format
                $value = $value->format('Y-m-d H:i:s');
            } elseif (is_array($value)) {
                // If the value is an array, recursively process it
                $value = $this->convertFrozenTimeToString($value);
            }
        }

        // Return the modified array with FrozenTime instances converted to strings
        return $data;
    }
    
}
