<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;

use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * Migration state: one row per run, one job per migrated object, and the audit log.
 * Tables are created on first use. The log is kept for LOG_RETENTION_DAYS independently of the backups.
 */
class Store
{
    public static function runsTable()
    {
        return Edition::table('Migrations');
    }
    public static function jobsTable()
    {
        return Edition::table('MigrationJobs');
    }
    public static function logTable()
    {
        return Edition::table('MigrationLog');
    }

    const LOG_RETENTION_DAYS    = 30;
    const BACKUP_RETENTION_DAYS = 14;

    // run statuses
    const RUN_DRY_RUN     = 'dry_run';
    const RUN_PREPARING   = 'preparing';   // backups being written
    const RUN_RUNNING     = 'running';     // services in batches
    const RUN_SWITCHING   = 'switching';   // servertype / module flip + rescan
    const RUN_COMPLETED   = 'completed';
    const RUN_FAILED      = 'failed';
    const RUN_ROLLED_BACK = 'rolled_back';

    // job statuses
    const JOB_PENDING = 'pending';
    const JOB_DONE    = 'done';
    const JOB_FAILED  = 'failed';
    const JOB_SKIPPED = 'skipped';

    // job kinds
    const KIND_SERVICE  = 'service';
    const KIND_PRODUCT  = 'product';
    const KIND_SETTINGS = 'settings';

    protected static $ready = false;

    public static function createTables(): void
    {
        if(self::$ready)
        {
            return;
        }

        $schema = Capsule::schema();

        if(!$schema->hasTable(self::runsTable()))
        {
            $schema->create(self::runsTable(), function($t) {
                $t->engine = 'InnoDB';
                $t->increments('id');
                $t->string('source', 8);                       // ggs | tss
                $t->string('source_version', 32)->nullable();
                $t->string('status', 16)->default(self::RUN_DRY_RUN);
                $t->string('started_by', 128)->nullable();
                $t->string('lock_token', 64)->nullable();
                $t->dateTime('heartbeat_at')->nullable();
                $t->dateTime('snapshot_at')->nullable();      // rows created after this are picked up by the rescan
                $t->string('backup_prefix', 64)->nullable();
                $t->dateTime('backup_expires_at')->nullable();
                $t->text('options')->nullable();              // json
                $t->text('summary')->nullable();              // json: counts, backups, exceptions, cron lines
                $t->text('last_error')->nullable();
                $t->dateTime('started_at')->nullable();
                $t->dateTime('finished_at')->nullable();
                $t->dateTime('created_at');
                $t->dateTime('updated_at');
            });
        }

        if(!$schema->hasTable(self::jobsTable()))
        {
            $schema->create(self::jobsTable(), function($t) {
                $t->engine = 'InnoDB';
                $t->increments('id');
                $t->integer('migration_id')->index();
                $t->string('kind', 16);                       // service | product | settings
                $t->integer('object_id')->default(0);         // tblhosting.id or tblproducts.id
                $t->integer('ssl_order_id')->default(0);      // tblsslorders.id
                $t->string('status', 16)->default(self::JOB_PENDING)->index();
                $t->string('reason', 512)->nullable();
                $t->integer('attempts')->default(0);
                $t->text('data')->nullable();                 // json: ids of rows written (for rollback) + flags
                $t->dateTime('created_at');
                $t->dateTime('updated_at');
            });
        }

        if(!$schema->hasTable(self::logTable()))
        {
            $schema->create(self::logTable(), function($t) {
                $t->engine = 'InnoDB';
                $t->increments('id');
                $t->integer('migration_id')->index();
                $t->integer('service_id')->default(0);
                $t->string('level', 8);                       // info | warning | error
                $t->string('phase', 24);
                $t->text('message');
                $t->text('context')->nullable();              // json
                $t->dateTime('created_at')->index();
            });
        }

        self::$ready = true;
        Schema::refresh();
    }

    // ---- Runs ---- //

    public static function createRun(string $source, ?string $sourceVersion, string $startedBy, array $options, string $status = self::RUN_DRY_RUN): int
    {
        self::createTables();

        return (int)Capsule::table(self::runsTable())->insertGetId([
            'source'         => $source,
            'source_version' => $sourceVersion,
            'status'         => $status,
            'started_by'     => substr($startedBy, 0, 128),
            'options'        => json_encode($options),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getRun(int $id): ?array
    {
        self::createTables();
        $row = Capsule::table(self::runsTable())->where('id', $id)->first();

        return $row ? self::decodeRun((array)$row) : null;
    }

    /**
     * The most recent run that is not a dry run (what the dashboard shows)
     */
    public static function latestRun(): ?array
    {
        self::createTables();
        $row = Capsule::table(self::runsTable())->where('status', '<>', self::RUN_DRY_RUN)->orderBy('id', 'desc')->first();

        return $row ? self::decodeRun((array)$row) : null;
    }

    /**
     * A run that is still in progress (preparing / running / switching), if any
     */
    public static function activeRun(): ?array
    {
        self::createTables();
        $row = Capsule::table(self::runsTable())
            ->whereIn('status', [self::RUN_PREPARING, self::RUN_RUNNING, self::RUN_SWITCHING])
            ->orderBy('id', 'desc')->first();

        return $row ? self::decodeRun((array)$row) : null;
    }

    public static function updateRun(int $id, array $fields): void
    {
        foreach(['options', 'summary'] as $json)
        {
            if(isset($fields[$json]) && is_array($fields[$json]))
            {
                $fields[$json] = json_encode($fields[$json]);
            }
        }
        $fields['updated_at'] = date('Y-m-d H:i:s');
        Capsule::table(self::runsTable())->where('id', $id)->update($fields);
    }

    public static function mergeSummary(int $id, array $patch): array
    {
        $run     = self::getRun($id);
        $summary = array_replace($run['summary'] ?? [], $patch);
        self::updateRun($id, ['summary' => $summary]);

        return $summary;
    }

    protected static function decodeRun(array $row): array
    {
        $row['options'] = $row['options'] ? (json_decode($row['options'], true) ?: []) : [];
        $row['summary'] = $row['summary'] ? (json_decode($row['summary'], true) ?: []) : [];

        return $row;
    }

    // ---- Jobs ---- //

    public static function addJob(int $migrationId, string $kind, int $objectId, int $sslOrderId = 0, string $status = self::JOB_PENDING, ?string $reason = null, array $data = []): int
    {
        return (int)Capsule::table(self::jobsTable())->insertGetId([
            'migration_id' => $migrationId,
            'kind'         => $kind,
            'object_id'    => $objectId,
            'ssl_order_id' => $sslOrderId,
            'status'       => $status,
            'reason'       => $reason !== null ? substr($reason, 0, 512) : null,
            'data'         => json_encode($data),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public static function pendingJobs(int $migrationId, string $kind, int $limit): array
    {
        return array_map(function($row) { return self::decodeJob((array)$row); },
            Capsule::table(self::jobsTable())
                ->where('migration_id', $migrationId)->where('kind', $kind)->where('status', self::JOB_PENDING)
                ->orderBy('id')->limit($limit)->get()->all());
    }

    public static function jobsByStatus(int $migrationId, string $kind, string $status): array
    {
        return array_map(function($row) { return self::decodeJob((array)$row); },
            Capsule::table(self::jobsTable())
                ->where('migration_id', $migrationId)->where('kind', $kind)->where('status', $status)
                ->orderBy('id')->get()->all());
    }

    public static function jobCounts(int $migrationId, string $kind = self::KIND_SERVICE): array
    {
        $counts = [self::JOB_PENDING => 0, self::JOB_DONE => 0, self::JOB_FAILED => 0, self::JOB_SKIPPED => 0];
        foreach(Capsule::table(self::jobsTable())->selectRaw('status, COUNT(*) AS c')->where('migration_id', $migrationId)->where('kind', $kind)->groupBy('status')->get() as $row)
        {
            $counts[$row->status] = (int)$row->c;
        }
        $counts['total'] = array_sum($counts);

        return $counts;
    }

    public static function hasJob(int $migrationId, string $kind, int $objectId): bool
    {
        return (bool)Capsule::table(self::jobsTable())->where('migration_id', $migrationId)->where('kind', $kind)->where('object_id', $objectId)->first();
    }

    public static function finishJob(int $jobId, string $status, ?string $reason = null, array $data = []): void
    {
        $update = [];
        if(!empty($data['ssl_order_id']))
        {
            $update['ssl_order_id'] = (int)$data['ssl_order_id'];
        }
        Capsule::table(self::jobsTable())->where('id', $jobId)->update($update + [
            'status'     => $status,
            'reason'     => $reason !== null ? substr($reason, 0, 512) : null,
            'data'       => json_encode($data),
            'attempts'   => Capsule::raw('attempts + 1'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected static function decodeJob(array $row): array
    {
        $row['data'] = $row['data'] ? (json_decode($row['data'], true) ?: []) : [];

        return $row;
    }

    // ---- Log ---- //

    public static function log(int $migrationId, string $level, string $phase, string $message, array $context = [], int $serviceId = 0): void
    {
        self::createTables();
        Capsule::table(self::logTable())->insert([
            'migration_id' => $migrationId,
            'service_id'   => $serviceId,
            'level'        => $level,
            'phase'        => substr($phase, 0, 24),
            'message'      => $message,
            'context'      => $context ? json_encode($context) : null,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public static function logRows(int $migrationId, int $limit = 5000, ?string $level = null): array
    {
        $query = Capsule::table(self::logTable())->where('migration_id', $migrationId)->orderBy('id');
        if($level)
        {
            $query->where('level', $level);
        }

        return array_map(function($row) { return (array)$row; }, $query->limit($limit)->get()->all());
    }

    /**
     * Removes log rows older than LOG_RETENTION_DAYS (runs from the cron)
     */
    public static function purgeOldLogs(): int
    {
        self::createTables();

        return (int)Capsule::table(self::logTable())
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-' . self::LOG_RETENTION_DAYS . ' days')))
            ->delete();
    }
}
