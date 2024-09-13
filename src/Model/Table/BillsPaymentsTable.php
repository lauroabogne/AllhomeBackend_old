<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BillsPayments Model
 *
 * @method \App\Model\Entity\BillsPayment newEmptyEntity()
 * @method \App\Model\Entity\BillsPayment newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\BillsPayment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BillsPayment get($primaryKey, $options = [])
 * @method \App\Model\Entity\BillsPayment findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\BillsPayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\BillsPayment[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BillsPayment|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BillsPayment saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BillsPayment[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BillsPayment[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\BillsPayment[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BillsPayment[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BillsPaymentsTable extends Table
{
    public static $validationLengths = [
        'unique_id' => [
            'maxLength' => 255
        ],
        'bill_unique_id' => [
            'maxLength' => 255
        ],
        'bill_group_unique_id' => [
            'maxLength' => 255
        ],
        'image_name' => [
            'maxLength' => 255
        ],
    ];

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('bills_payments');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('unique_id')
            ->maxLength('unique_id', self::$validationLengths['unique_id']['maxLength'], 'Unique ID must be no longer than '.self::$validationLengths['unique_id']['maxLength'].' characters.')
            ->requirePresence('unique_id', 'create', 'Unique ID is required.')
            ->notEmptyString('unique_id', 'Unique ID cannot be empty.')
            ->add('unique_id', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'The unique ID must be unique.'
            ]);

        $validator
            ->scalar('bill_unique_id')
            ->maxLength('bill_unique_id', self::$validationLengths['bill_unique_id']['maxLength'], 'Bill Unique ID must be no longer than '.self::$validationLengths['bill_unique_id']['maxLength'].' characters.')
            ->requirePresence('bill_unique_id', 'create', 'Bill Unique ID is required.')
            ->notEmptyString('bill_unique_id', 'Bill Unique ID cannot be empty.');

        $validator
            ->scalar('bill_group_unique_id')
            ->maxLength('bill_group_unique_id', self::$validationLengths['bill_group_unique_id']['maxLength'], 'Bill Group Unique ID must be no longer than '.self::$validationLengths['bill_group_unique_id']['maxLength'].' characters.')
            ->requirePresence('bill_group_unique_id', 'create', 'Bill Group Unique ID is required.')
            ->notEmptyString('bill_group_unique_id', 'Bill Group Unique ID cannot be empty.');

        $validator
            ->decimal('payment_amount')
            ->requirePresence('payment_amount', 'create', 'Payment amount is required.')
            ->notEmptyString('payment_amount', 'Payment amount cannot be empty.')
            ->add('payment_amount', 'validFormat', [
                'rule' => 'decimal',
                'message' => 'Payment amount must be a decimal value.'
            ]);

        $validator
            ->add('payment_date', 'validFormat', [
                'rule' => [$this, 'validateDateTime'],
                'message' => 'Invalid date and time format. Use YYYY-MM-DD HH:MM:SS.'
            ])
            ->requirePresence('payment_date', 'create', 'Payment date is required.')
            ->notEmptyDateTime('payment_date', 'Payment date cannot be empty.');

        $validator
            ->scalar('payment_note')
            ->requirePresence('payment_note', 'create', 'Payment note not found.')
            ->allowEmptyString('payment_note');

        $validator
            ->scalar('image_name')
            ->maxLength('image_name', self::$validationLengths['image_name']['maxLength'], 'Image name must be no longer than '.self::$validationLengths['image_name']['maxLength'].' characters.')
            ->allowEmptyString('image_name');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create', 'Status is required.')
            ->notEmptyString('status', 'Status is required.')
            ->add('status', 'valid', [
                'rule' => ['inList', [0, 1]],
                'message' => 'Status must be either 0 or 1'
            ]);

        return $validator;
    }

    /**
     * Validation for updating records.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationForUpdate(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('unique_id')
            ->maxLength('unique_id', self::$validationLengths['unique_id']['maxLength'], 'Unique ID must be no longer than '.self::$validationLengths['unique_id']['maxLength'].' characters.')
            ->requirePresence('unique_id', 'create', 'Unique ID is required.')
            ->notEmptyString('unique_id', 'Unique ID cannot be empty.');

        $validator
            ->scalar('bill_unique_id')
            ->maxLength('bill_unique_id', self::$validationLengths['bill_unique_id']['maxLength'], 'Bill Unique ID must be no longer than '.self::$validationLengths['bill_unique_id']['maxLength'].' characters.')
            ->requirePresence('bill_unique_id', 'create', 'Bill Unique ID is required.')
            ->notEmptyString('bill_unique_id', 'Bill Unique ID cannot be empty.');

        $validator
            ->scalar('bill_group_unique_id')
            ->maxLength('bill_group_unique_id', self::$validationLengths['bill_group_unique_id']['maxLength'], 'Bill Group Unique ID must be no longer than '.self::$validationLengths['bill_group_unique_id']['maxLength'].' characters.')
            ->requirePresence('bill_group_unique_id', 'create', 'Bill Group Unique ID is required.')
            ->notEmptyString('bill_group_unique_id', 'Bill Group Unique ID cannot be empty.');

        $validator
            ->decimal('payment_amount')
            ->requirePresence('payment_amount', 'create', 'Payment amount is required.')
            ->notEmptyString('payment_amount', 'Payment amount cannot be empty.')
            ->add('payment_amount', 'validFormat', [
                'rule' => 'decimal',
                'message' => 'Payment amount must be a decimal value.'
            ]);

        $validator
            ->add('payment_date', 'validFormat', [
                'rule' => [$this, 'validateDateTime'],
                'message' => 'Invalid date and time format. Use YYYY-MM-DD HH:MM:SS.'
            ])
            ->requirePresence('payment_date', 'create', 'Payment date is required.')
            ->notEmptyDateTime('payment_date', 'Payment date cannot be empty.');

        $validator
            ->scalar('payment_note')
            ->requirePresence('payment_note', 'create', 'Payment note not found.')
            ->allowEmptyString('payment_note');

        $validator
            ->scalar('image_name')
            ->maxLength('image_name', self::$validationLengths['image_name']['maxLength'], 'Image name must be no longer than '.self::$validationLengths['image_name']['maxLength'].' characters.')
            ->allowEmptyString('image_name');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create', 'Status is required.')
            ->notEmptyString('status', 'Status is required.')
            ->add('status', 'valid', [
                'rule' => ['inList', [0, 1]],
                'message' => 'Status must be either 0 or 1'
            ]);

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

    /**
     * Get a record by unique_id.
     *
     * @param string $uniqueId
     * @return \App\Model\Entity\BillsPayment|null
     */
    public function getByUniqueId(string $uniqueId)
    {
        return $this->find()
            ->where(['unique_id' => $uniqueId])
            ->first();
    }

    /**
     * Save a record (either insert or update).
     *
     * @param \App\Model\Entity\BillsPayment $entity
     * @return \App\Model\Entity\BillsPayment|false
     */
    public function saveRecord(\App\Model\Entity\BillsPayment $entity)
    {
        return $this->save($entity);
    }
}
