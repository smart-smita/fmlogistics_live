<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSmtpStatusToEmailSettings extends Migration
{
    public function up()
    {
        $fields = [
            'smtp_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => 'Unknown',
            ],
            'last_test_email_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('email_settings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('email_settings', 'smtp_status');
        $this->forge->dropColumn('email_settings', 'last_test_email_date');
    }
}
