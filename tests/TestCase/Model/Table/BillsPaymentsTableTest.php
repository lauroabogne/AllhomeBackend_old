<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BillsPaymentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BillsPaymentsTable Test Case
 */
class BillsPaymentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BillsPaymentsTable
     */
    protected $BillsPayments;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.BillsPayments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('BillsPayments') ? [] : ['className' => BillsPaymentsTable::class];
        $this->BillsPayments = $this->getTableLocator()->get('BillsPayments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->BillsPayments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\BillsPaymentsTable::validationDefault()
     */
    // public function testValidationDefault(): void
    // {
    //     $this->markTestIncomplete('Not implemented yet.');
    // }

    public function testOne(){
        $this->assertTrue(true);
    }
}
