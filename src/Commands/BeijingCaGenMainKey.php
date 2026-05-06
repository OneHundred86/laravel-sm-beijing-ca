<?php

namespace Oh86\LaravelSmBeijingCa\Commands;

use Illuminate\Console\Command;

class BeijingCaGenMainKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beijingCa:genMainKey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '北京CA生成主密钥';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $config = config('sm_cryptor.beijingCa');
        $api = new \Oh86\LaravelSmBeijingCa\Apis\BeijingCaApi($config);
        $result = $api->createKey();
        $this->info('生成主密钥成功:');
        foreach ($result as $k => $v) {
            if (is_array($v)) {
                $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
            $this->line("\t$k: $v");
        }

        return 0;
    }
}
