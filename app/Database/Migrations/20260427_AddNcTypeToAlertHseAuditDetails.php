<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNcTypeToAlertHseAuditDetails extends Migration
{
    public function up()
    {
        $check = $this->db->query("SHOW COLUMNS FROM alert_hse_audit_details LIKE 'nc_type'");
        $exists = $check ? count($check->getResultArray()) > 0 : false;

        if (! $exists) {
            $columns = [
                'nc_type' => [
                    'type' => 'ENUM',
                    'constraint' => ['NC','RD'],
                    'default' => 'NC',
                    'null' => false,
                ],
            ];
            $this->forge->addColumn('alert_hse_audit_details', $columns);
        }
    }

    public function down()
    {
        $check = $this->db->query("SHOW COLUMNS FROM alert_hse_audit_details LIKE 'nc_type'");
        $exists = $check ? count($check->getResultArray()) > 0 : false;

        if ($exists) {
            $this->forge->dropColumn('alert_hse_audit_details', 'nc_type');
        }
    }
}
