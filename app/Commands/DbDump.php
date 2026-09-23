<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DbDump extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:dump';
    protected $description = 'Dump DB schema.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('alert_hse_audit_details');
        foreach ($fields as $field) {
            CLI::write($field);
        }
    }
}
