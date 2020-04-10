<?php
namespace SynergyDataGridTest;

use Doctrine\ORM\Tools\SchemaTool;
use Laminas\Mvc\Application;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('UTC');

chdir(dirname(realpath(__DIR__ . '/../../')));
$basePath = realpath('./') . '/';

set_include_path(
    implode(
        PATH_SEPARATOR,
        array($basePath,
              $basePath . '/vendor',
              $basePath . '/tests',
              get_include_path(),
        )
    )
);

$classList = include __DIR__ . "/../../autoload_classmap.php";

spl_autoload_register(
    function ($class) use ($classList, $basePath) {
        if (isset($classList[$class])) {
            $filename = $classList[$class];
            include "{$filename}";
        } else {
            $filename = str_replace('\\\\', '/', $class) . '.php';
            if (file_exists($filename)) {
                require "{$filename}";
            }
        }
    }
);

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('src')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        $zf2ModulePaths[] = './';

        $config              = include __DIR__ . '/../../../config/application.config.php';
        $config['modules'][] = 'SynergyDataGridTest';

        include __DIR__ . '/../../../init_autoloader.php';

        /** @var \Laminas\Mvc\Application $app */
        $app = Application::init($config);

        $serviceManager         = $app->getServiceManager();
        static::$serviceManager = $serviceManager;

        self::setUpDatabase();
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    /**
     *
     * @param string $path
     *
     * @return boolean|string false if the path cannot be found
     */
    protected static function findParentPath($path)
    {
        $dir    = __DIR__;
        $srcDir = realpath($dir . '/../../../');

        return $srcDir . '/' . $path;
    }

    /**
     * @method getServiceManager()
     */
    public static function setUpDatabase()
    {
        unlink(sys_get_temp_dir() . '/sqlite.db');
        $entityManager = self::getServiceManager()->get('doctrine.entitymanager.orm_default');
        $tool          = new SchemaTool($entityManager);
        $classes       = $entityManager->getMetadataFactory()->getAllMetadata();
        $tool->getDropDatabaseSQL();
        $tool->createSchema($classes);
    }
}

Bootstrap::init();
