<?php
/**
 * 迁移 .env 文件
 *
 * @author mybsdc <mybsdc@gmail.com>
 * @date 2021/11/3
 * @time 15:57
 */

namespace Luolongfei\App\Console;

use Luolongfei\Libs\Env;

class MigrationEnvFile extends Base
{
    /**
     * @var array 当前已有的环境变量数据
     */
    protected $allOldEnvValues;

    /**
     * @var int 迁移环境变量数量
     */
    public $migrateNum = 0;

    /**
     * @var FreeNom
     */
    private static $instance;

    /**
     * @return FreeNom
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->allOldEnvValues = $this->getAllOldEnvValues();
    }

    private function __clone()
    {
    }

    /**
     * 获取当前有效的旧的环境变量值
     *
     * 会做一些基本的处理，让旧版数据兼容新版 .env 文件
     *
     * @return array
     */
    protected function getAllOldEnvValues()
    {
        $allOldEnvValues = env();

        unset($allOldEnvValues['ENV_FILE_VERSION']);

        $allOldEnvValues = array_filter($allOldEnvValues, function ($val) {
            return $val !== '';
        });

        $allOldEnvValues = array_map(function ($val) {
            $tmpVal = strtolower($val);

            if ($tmpVal === 'true' || $tmpVal === true) {
                return 1;
            } else if ($tmpVal === 'false' || $tmpVal === false) {
                return 0;
            } else {
                return $val;
            }
        }, $allOldEnvValues);

        return $allOldEnvValues;
    }

    /**
     * 是否需要迁移
     *
     * @return bool
     * @throws \Exception
     */
    public function isNeedMigration()
    {
        $envVer = $this->getEnvFileVer();

        if (is_null($envVer)) {
            return true;
        }

        $envExampleVer = $this->getEnvFileVer('.env.example');

        return version_compare($envExampleVer, $envVer, '>');
    }

    public function getEnvFilePath($filename = '.env')
    {
        return ROOT_PATH . DS . $filename;
    }

    /**
     * 获取 env 文件版本
     *
     * @param string $filename
     *
     * @return string|null
     * @throws \Exception
     */
    public function getEnvFileVer($filename = '.env')
    {
        $file = $this->getEnvFilePath($filename);

        if (!file_exists($file)) {
            throw new \Exception('文件不存在：' . $file);
        }

        if (($fileContent = file_get_contents($file)) === false) {
            throw new \Exception('读取文件内容失败：' . $file);
        }

        if (!preg_match('/^ENV_FILE_VERSION=(?P<env_file_version>.*?)$/im', $fileContent, $m)) {
            return null;
        }

        return $this->getVerNum($m['env_file_version']);
    }

    /**
     * 获取版本号数字部分
     *
     * @param $rawVer
     *
     * @return string|null
     */
    public function getVerNum($rawVer)
    {
        if (preg_match('/(?P<ver_num>\d+(?:\.\d+)*)/i', $rawVer, $m)) {
            return $m['ver_num'];
        }

        return null;
    }

    /**
     * 备份旧文件
     *
     * 如果目标文件已存在，将会被覆盖
     *
     * @return bool
     * @throws \Exception
     */
    public function backup()
    {
        if (copy($this->getEnvFilePath(), $this->getEnvFilePath('.env.old')) === false) {
            throw new \Exception('备份 .env 文件到 .env.old 文件时出错');
        }

        return true;
    }

    /**
     * 生成新的 .env 文件
     *
     * @return bool
     * @throws \Exception
     */
    public function genNewEnvFile()
    {
        if (copy($this->getEnvFilePath('.env.example'), $this->getEnvFilePath('.env')) === false) {
            throw new \Exception('从 .env.example 文件生成 .env 文件时出错');
        }

        return true;
    }

    /**
     * 迁移环境变量数据
     *
     * @param array $allEnvVars
     * @throws \Exception
     */
    public function migrateData(array $allEnvVars)
    {
        foreach ($allEnvVars as $envKey => $envVal) {
            if ($this->setEnv($envKey, $envVal)) {
                $this->migrateNum++;
            }
        }

        // 重载环境变量
        Env::getInstance()->init('.env', true);
    }

    /**
     * 写入单个环境变量值
     *
     * @param string $key
     * @param $value
     *
     * @return bool
     */
    public function setEnv(string $key, $value)
    {
        $envFilePath = $this->getEnvFilePath();
        $contents = file_get_contents($envFilePath);

        $contents = preg_replace("/^{$key}=[^\r\n]*/miu", $this->formatEnvVal($key, $value), $contents, -1, $count);

        return $this->writeFile($envFilePath, $contents) && $count;
    }

    /**
     * 格式化环境变量
     *
     * @param string $key
     * @param string|integer $value
     *
     * @return string
     */
    public function formatEnvVal($key, $value)
    {
        return sprintf(is_numeric($value) ? '%s=%s' : "%s='%s'", $key, $value);
    }

    /**
     * 覆盖文件内容
     *
     * @param string $path
     * @param string $contents
     *
     * @return bool
     */
    protected function writeFile(string $path, string $contents): bool
    {
        $file = fopen($path, 'w');
        fwrite($file, $contents);

        return fclose($file);
    }

    /**
     * @return bool
     */
    public function handle()
    {
        try {
            if (!$this->isNeedMigration()) {
                return true;
            }

            system_log('检测到你的 .env 文件内容过旧，程式将根据 .env.example 文件自动更新相关配置项，不要慌张，此操作对已有数据不会有任何影响');

            $this->backup();
            system_log(sprintf('<green>已完成 .env 文件备份</green>，旧文件位置为 %s/.env.old', ROOT_PATH));

            $this->genNewEnvFile();
            system_log('已生成新 .env 文件');

            $this->migrateData($this->allOldEnvValues);
            system_log(sprintf('<green>数据迁移完成</green>，共迁移 %d 条环境变量数据', $this->migrateNum));

            system_log('<green>恭喜，已成功完成 .env 文件升级</green>');

            return true;
        } catch (\Exception $e) {
            system_log('升级 .env 文件出错：' . $e->getMessage());

            return false;
        }
    }
}
