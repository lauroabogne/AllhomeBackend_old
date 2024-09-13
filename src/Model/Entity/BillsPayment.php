<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BillsPayment Entity
 *
 * @property int $id
 * @property string $unique_id
 * @property string $bill_unique_id
 * @property string $bill_group_unique_id
 * @property string $payment_amount
 * @property \Cake\I18n\FrozenTime $payment_date
 * @property string|null $payment_note
 * @property string|null $image_name
 * @property int|null $status
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class BillsPayment extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'unique_id' => true,
        'bill_unique_id' => true,
        'bill_group_unique_id' => true,
        'payment_amount' => true,
        'payment_date' => true,
        'payment_note' => true,
        'image_name' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
    ];
}
