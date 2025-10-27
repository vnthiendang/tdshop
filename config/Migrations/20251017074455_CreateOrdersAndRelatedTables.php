<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateOrdersAndRelatedTables extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        // --- Orders ---
        $this->table('orders')
            ->addColumn('order_code', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('customer_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('customer_phone', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('customer_email', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('shipping_address', 'text', ['null' => false])
            ->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('payment_method', 'enum', [
                'values' => ['cod', 'bank_transfer', 'vnpay', 'momo'],
                'null' => false,
            ])
            ->addColumn('payment_status', 'enum', [
                'values' => ['pending', 'paid', 'failed', 'refunded'],
                'default' => 'pending',
            ])
            ->addColumn('payment_date', 'datetime', ['null' => true])
            ->addColumn('payment_proof', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('order_status', 'enum', [
                'values' => ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'],
                'default' => 'pending',
            ])
            ->addColumn('transaction_id', 'string', ['limit' => 100, 'null' => true])
            // ->addColumn('bank_code', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('admin_notes', 'text', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['order_code'])
            ->addIndex(['payment_status'])
            ->addIndex(['order_status'])
            ->create();

        // --- Order Items ---
        $this->table('order_items')
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('product_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('quantity', 'integer', ['null' => false])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('total', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'CASCADE'])
            ->create();

        // --- Payment Logs ---
        $this->table('payment_logs')
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('payment_method', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('status', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('transaction_data', 'text', ['null' => true])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
