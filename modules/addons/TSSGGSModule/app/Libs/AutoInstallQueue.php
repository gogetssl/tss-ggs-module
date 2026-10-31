<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * AutoInstall cron queue (validate / install / reissue tasks) and service logs.
 * Ported from SSLCENTERWHMCS (models\cronQueue + models\logs). Tables are created on first use.
 */
class AutoInstallQueue
{
    const QUEUE_TABLE = 'TSSGGSModule_AutoInstallQueue';
    const LOGS_TABLE  = 'TSSGGSModule_AutoInstallLogs';

    const MAX_ATTEMPTS = 5;

    protected static $tablesChecked = false;

    public static function createTables()
    {
        if(self::$tablesChecked)
        {
            return;
        }

        $schema = Capsule::schema();

        if(!$schema->hasTable(self::QUEUE_TABLE))
        {
            $schema->create(self::QUEUE_TABLE, function($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id')->index();
                $table->string('type', 32);
                $table->string('status', 32)->default('pending');
                $table->string('domain')->nullable();
                $table->integer('attempts')->default(0);
                $table->text('data')->nullable();
                $table->string('error_message', 512)->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
            });
        }

        if(!$schema->hasTable(self::LOGS_TABLE))
        {
            $schema->create(self::LOGS_TABLE, function($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id')->index();
                $table->string('type', 16);
                $table->text('message');
                $table->dateTime('created_at');
            });
        }

        self::$tablesChecked = true;
    }

    // ---- Queue ---- //

    public static function addTask($clientId, $serviceId, $type, array $data = [])
    {
        self::createTables();

        return Capsule::table(self::QUEUE_TABLE)->insertGetId([
            'client_id'  => (int)$clientId,
            'service_id' => (int)$serviceId,
            'type'       => $type,
            'status'     => 'pending',
            'attempts'   => 0,
            'domain'     => isset($data['domain']) ? $data['domain'] : '',
            'data'       => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getPendingTasks($type, $limit = 20)
    {
        self::createTables();

        return Capsule::table(self::QUEUE_TABLE)
            ->where('status', 'pending')
            ->where('type', $type)
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Atomically takes the task - returns false when another cron run took it already.
     */
    public static function claim($id)
    {
        return Capsule::table(self::QUEUE_TABLE)
                ->where('id', $id)
                ->where('status', 'pending')
                ->update(['status' => 'processing', 'updated_at' => date('Y-m-d H:i:s')]) > 0;
    }

    public static function markDone($id, $message = '')
    {
        Capsule::table(self::QUEUE_TABLE)->where('id', $id)->update([
            'status'        => 'done',
            'error_message' => $message ? substr($message, 0, 512) : null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public static function markPending($id, $message = '')
    {
        Capsule::table(self::QUEUE_TABLE)->where('id', $id)->update([
            'status'        => 'pending',
            'error_message' => $message ? substr($message, 0, 512) : null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Retries the task until MAX_ATTEMPTS is reached. Returns true when the task failed for good.
     */
    public static function markError($id, $message = '')
    {
        $task      = Capsule::table(self::QUEUE_TABLE)->where('id', $id)->first();
        $attempts  = $task ? (int)$task->attempts + 1 : 1;
        $newStatus = $attempts >= self::MAX_ATTEMPTS ? 'error' : 'pending';

        Capsule::table(self::QUEUE_TABLE)->where('id', $id)->update([
            'status'        => $newStatus,
            'attempts'      => $attempts,
            'error_message' => substr((string)$message, 0, 512),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return $newStatus === 'error';
    }

    public static function markFailed($id, $message = '')
    {
        Capsule::table(self::QUEUE_TABLE)->where('id', $id)->update([
            'status'        => 'error',
            'error_message' => substr((string)$message, 0, 512),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public static function updateTaskData($id, array $data)
    {
        Capsule::table(self::QUEUE_TABLE)->where('id', $id)->update([
            'data'       => json_encode($data),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Admin "Reset" (Automation Queue) - the task is processed again by the next cron run as a fresh one.
     * api_order_id is synchronized with tblsslorders.remoteid, otherwise the cron closes the task as "Order replaced".
     */
    public static function resetTask($id)
    {
        self::createTables();

        $task = Capsule::table(self::QUEUE_TABLE)->where('id', (int)$id)->first();
        if(!$task)
        {
            throw new \Exception('Task #' . (int)$id . ' does not exist');
        }

        $data = json_decode($task->data, true) ?: [];
        unset($data['validate_attempts'], $data['same_cert_checks']);
        if(isset($data['dns_check_start']))
        {
            $data['dns_check_start'] = time();
        }

        $remoteId = (int)Capsule::table('tblsslorders')->where('serviceid', $task->service_id)->value('remoteid');
        if($remoteId > 0)
        {
            $data['api_order_id'] = $remoteId;
        }

        Capsule::table(self::QUEUE_TABLE)->where('id', $task->id)->update([
            'status'        => 'pending',
            'attempts'      => 0,
            'data'          => json_encode($data),
            'error_message' => null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public static function resetStuckProcessing($minutesOld = 15)
    {
        self::createTables();

        Capsule::table(self::QUEUE_TABLE)
            ->where('status', 'processing')
            ->where('updated_at', '<', date('Y-m-d H:i:s', strtotime('-' . (int)$minutesOld . ' minutes')))
            ->update([
                'status'        => 'pending',
                'error_message' => 'Auto-recovered from stuck processing state',
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
    }

    public static function hasOpenTask($serviceId, $type = null)
    {
        self::createTables();

        $query = Capsule::table(self::QUEUE_TABLE)
            ->where('service_id', (int)$serviceId)
            ->whereIn('status', ['pending', 'processing']);

        if($type !== null)
        {
            $query->where('type', $type);
        }

        return (bool)$query->first();
    }

    public static function cancelOpenTasks($serviceId, $reason = '')
    {
        self::createTables();

        Capsule::table(self::QUEUE_TABLE)
            ->where('service_id', (int)$serviceId)
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status'        => 'cancelled',
                'error_message' => $reason ? substr($reason, 0, 512) : null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
    }

    // ---- Logs ---- //

    public static function addLog($clientId, $serviceId, $type, $message)
    {
        self::createTables();

        Capsule::table(self::LOGS_TABLE)->insert([
            'client_id'  => (int)$clientId,
            'service_id' => (int)$serviceId,
            'type'       => $type,
            'message'    => (string)$message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getLogs($serviceId, $limit = 15)
    {
        self::createTables();

        return Capsule::table(self::LOGS_TABLE)
            ->where('service_id', (int)$serviceId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }
}
