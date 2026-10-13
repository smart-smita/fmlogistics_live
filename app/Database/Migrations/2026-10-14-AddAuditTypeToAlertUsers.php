<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuditTypeToAlertUsers extends Migration
{
    public function up()
    {
        $fields = [
            'audit_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'Both',
                'comment'    => 'OE, HSE, or Both'
            ],
        ];
        $this->forge->addColumn('alert_users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('alert_users', 'audit_type');
    }
}
?>
