<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BillsPaymentsFixture
 */
class BillsPaymentsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'unique_id' => 'Lorem ipsum dolor sit amet',
                'bill_unique_id' => 'Lorem ipsum dolor sit amet',
                'bill_group_unique_id' => 'Lorem ipsum dolor sit amet',
                'payment_amount' => 1.5,
                'payment_date' => '2024-07-25 16:03:43',
                'payment_note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'image_name' => 'Lorem ipsum dolor sit amet',
                'status' => 1,
                'created' => 1721923423,
                'modified' => 1721923423,
            ],
        ];
        parent::init();
    }
}
