<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email_enabled'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'smtp_host'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'smtp_port'       => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'smtp_username'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'smtp_password'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'smtp_encryption' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'from_email'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'from_name'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reply_to_email'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'updated_by'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('email_settings');
        
        // Insert default row
        $db = \Config\Database::connect();
        $db->table('email_settings')->insert([
            'email_enabled'   => 1,
            'smtp_host'       => '',
            'smtp_port'       => '',
            'smtp_username'   => '',
            'smtp_password'   => '',
            'smtp_encryption' => 'tls',
            'from_email'      => 'noreply@example.com',
            'from_name'       => 'System Administrator',
            'reply_to_email'  => 'support@example.com',
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('email_settings');
    }
}
