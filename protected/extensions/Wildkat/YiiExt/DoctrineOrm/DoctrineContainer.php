<?php

namespace Wildkat\YiiExt\DoctrineOrm;

use Doctrine\DBAL,
    Doctrine\ORM,
    Doctrine\Common\Cache,
    Doctrine\Common\ClassLoader,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ORM\Mapping\Driver\AnnotationDriver;

//die();
\Yii::import("vend\\Doctrine\\Common\\ClassLoader", true);
//require_once '../../../../vendor/Doctrine/Common/ClassLoader.php';

/**
 * DoctrineContainer.
 *
 * This Yii application component acts as a container for the Doctrine 2 ORM
 * library. Firstly, you will need to download the latest version of the Doctrine 2 ORM
 * library and place it inside ext.Wildkat.YiiExt.DoctrineOrm.vendors. The directory
 * layout should look like the following;
 *
 * <pre>
 * DoctrineOrm/
 *   - vendor/
 *       - Doctrine/
 *           - Common/
 *           - DBAL/
 *           - ORM/
 *       - Symfony/
 * </pre>
 *
 *
 * Now, inside main.php config file, set up the component with 3 keys
 * (dbal, entityManager & cache) where each key represents a configuration
 * set for each D2 component. For example;
 *
 * <pre>
 * 'components' => array(
 *     'doctrine' => array(
 * 	       'dbal' => array(
 * 		       [multiple dbal configurations here]
 * 		   ),
 * 		   'entityManager' => array(
 * 		       [multiple entity manager configurations here]
 * 		   ),
 * 		   'cache' => array(
 * 		       [multiple cache configurations here]
 * 		   ),
 *     ),
 * )
 * </pre>
 *
 * You must also set and alias for the Wildkat namespace. At the top of the main.php
 * configuration file, put
 * Yii::setPathOfAlias('Wildkat', realpath(dirname(__FILE__) . '/../extensions/Wildkat'));
 *
 * For each D2 component, you can specify any number of configurations and index
 * them numerically or with a string. You can then access each configuration
 * through the component method e.g. Yii::app()->doctrine->getConnection('foo')
 * returns a dbal connection with a configuration index of 'foo'.
 *
 * @category YiiExtensions
 * @package  Wildkat\YiiExt\DoctrineOrm
 * @author   Kevin Bradwick <kevin@wildk.at>
 * @license  New BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: 1.0.1
 * @link     http://www.wildk.at
 */
class DoctrineContainer extends \CApplicationComponent
{
    /**
     * List of dbal configurations. Each configuration has the following options
     * @link http://www.doctrine-project.org/docs/dbal/2.0/en/reference/configuration.html
     * See the Doctrine Documentation for an explanation of connection parameters
     * @var array
     */
    public $dbal;

    /**
     * List of cache configurations. Each with the following options
     * <ul>
     *   <li>driver - (string) the driver e.g. ApcCache, MemcacheCache</li>
     *   <li>namespace - (string) the cache namespace</li>
     *   <li>servers - (array) used for memcache</li>
     * </ul>
     * @var array
     */
    public $cache;

    /**
     * List of entity manager configurations. Each with the following options
     * <ul>
     *   <li>connection - (string) the dbal config name</li>
     *   <li>mappingDriver - (string) [AnnotationDriver, YamlDriver, XmlDriver]</li>
     *   <li>mappingPaths - (array) An array of paths to find mapping information</li>
     *   <li>mappingDriverOptions - (array) Additional mapping driver options
     *   defined in and array and make reference to each of the drivers set
     *   methods</li>
     *   <li>metadataCache - (string) the cache configuration for metadata</li>
     *   <li>queryCache - (string) the cache configuration for query conversions</li>
     *   <li>resultCache - (string) the cache configuration for results</li>
     *   <li>proxyDir - (string)the directory location for proxy classes</li>
     *   <li>proxyNamespace - (string) the proxy namespace</li>
     *   <li>entityNamespaces - (array) entity namespaces</li>
     *   <li>autoGenerateProxyClasses - (bool) true false</li>
     * </ul>
     * @var array
     */
    public $entityManager;

    /**
     * Cached component instances
     * @var array
     */
    private $_cache;

    /**
     * Component initialization.
     *
     * This method registers doctrine's autoloaders by pushing them on the
     * current autoloader stack.
     *
     * @return null
     * @see    CApplicationComponent::init()
     */
    public function init()
    {
        $classLoader = new ClassLoader('Doctrine', \Yii::app()->basePath . '/vendor');
        \Yii::registerAutoloader(array($classLoader, 'loadClass'));

        $classLoader = new ClassLoader('Symfony', \Yii::app()->basePath . '/vendor');
        \Yii::registerAutoloader(array($classLoader, 'loadClass'));

    }//end init()

    /**
     * Get an instance of a DBAL Connection
     *
     * @param sting $name the connection name
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection($name='default')
    {
        if (isset($this->_cache['dbal'][$name]) === true
            && $this->_cache['dbal'][$name] instanceof DBAL\Connection
        ) {
            return $this->_cache['dbal'][$name];
        }

        $config = $this->dbal[$name];
        $conn   = DBAL\DriverManager::getConnection(
            $config,
            $this->_getDBALConfiguration($config),
            $this->_getEventManager($config)
        );

        $this->_cache['dbal'][$name] = $conn;

        return $conn;

    }//end getConnection()

    /**
     * Returns a cache instance. Drivers can be specified by their
     * fully qualified name e.g. Doctrine\Common\Cache\ArrayCache or by their
     * short name e.g. ArrayCache.
     *
     * If driver class is a custom implementation, it must extend from
     * Doctrine\Common\Cache\AbstractCache.
     *
     * @param sting $name the cache name
     *
     * @return Doctrine\Common\Cache\AbstractCache
     * @throws CException
     */
    public function getCache($name='default')
    {
        if (isset($this->_cache['cache'][$name]) === true
            && $this->_cache['cache'][$name] instanceof Cache\AbstractCache
        ) {
            return $this->_cache['cache'][$name];
        }

        if (isset($this->cache[$name]) === false) {
            throw new \CException(
                \Yii::t(
                    'wk',
                    'Unknown cache configuration "{name}"',
                    array('{name}' => $name)
                )
            );
        }

        $doctrineDrivers = array(
            'ApcCache'      => 'Doctrine\Common\Cache\ApcCache',
            'ArrayCache'    => 'Doctrine\Common\Cache\ArrayCache',
            'MemcacheCache' => 'Doctrine\Common\Cache\MemcacheCache',
            'XcacheCache'   => 'Doctrine\Common\Cache\XcacheCache',
        );

        $config = $this->cache[$name];

        if (array_key_exists($config['driver'], $doctrineDrivers) === true) {
            $driver = new $doctrineDrivers[$config['driver']];
        } else if (in_array($config['driver'], $doctrineDrivers) === true) {
            $driver = new $config['driver'];
        } else if (isset($config['driver']) === true) {
            $driver = new $config['driver'];
            if ($driver instanceof Cache\AbstractCache === false) {
                throw new \CException(
                    \Yii::t(
                        'wk',
                        'Cache driver must inherit from AbstractCache'
                    )
                );
            }
        }

        if (isset($driver) === false) {
            throw new \CException(
                \Yii::t(
                    'wk',
                    'Unknown cache configuration "{name}"',
                    array('{name}' => $name)
                )
            );
        }

        if (isset($config['namespace']) === true) {
            $driver->setNamespace($config['namespace']);
        }

        if (method_exists($driver, 'initialize') === true) {
            $driver->initialize($config);
        }

        if ($driver instanceof Cache\MemcacheCache) {
            $defaultServer = array(
                'host'          => 'localhost',
                'port'          => 11211,
                'persistent'    => true,
                'weight'        => 1,
                'timeout'       => 1,
                'retryInterval' => 15,
                'status'        => true
            );

            $memcache = new \Memcache();

            if (isset($config['servers']) === true) {
                foreach ($config['servers'] as $server) {
                    $server = array_replace_recursive(
                        $defaultServer,
                        $server
                    );

                    $memcache->addServer(
                        $server['host'],
                        $server['port'],
                        $server['persistent'],
                        $server['weight'],
                        $server['timeout'],
                        $server['retryInterval'],
                        $server['status']
                    );
                }
            } else {
                 $memcache->addServer(
                     $defaultServer['host'],
                     $defaultServer['port'],
                     $defaultServer['persistent'],
                     $defaultServer['weight'],
                     $defaultServer['timeout'],
                     $defaultServer['retryInterval'],
                     $defaultServer['status']
                 );
            }//end if

            $driver->setMemcache($memcache);
        }//end if

        $this->_cache['cache'][$name] = $driver;

        return $driver;

    }//end getCache()

    /**
     * Returns an entity manager
     *
     * @param string $name the entity manager configuration name
     *
     * @return Doctrine\ORM\EntityManager
     * @throws CException
     */
    public function getEntityManager($name='default')
    {
        if (isset($this->_cache['em'][$name]) === true) {
            return $this->_cache['em'][$name];
        }

        if (isset($this->entityManager[$name]) === false) {
            throw new \CException(
                \Yii::t(
                    'wk',
                    'Unknown entity manager configuration "{name}"',
                    array('{name}' => $name)
                )
            );
        }

        $options = $this->entityManager[$name];
        $conn    = $this->getConnection($options['connection']);
        $config  = new ORM\Configuration();

        unset($options['connection']);

        $driver = $this->_getMappingDriver($options);
        $config->setMetadataDriverImpl($driver);

        // set metadata cache
        if (isset($options['metadataCache']) === true) {
            $config->setMetadataCacheImpl(
                $this->getCache($options['metadataCache'])
            );
            unset($options['metadataCache']);
        }

        // set query cache
        if (isset($options['queryCache']) === true) {
            $config->setQueryCacheImpl(
                $this->getCache($options['queryCache'])
            );
            unset($options['queryCache']);
        }

        // set result cache
        if (isset($options['resultCache']) === true) {
            $config->setResultCacheImpl(
                $this->getCache($options['resultCache'])
            );
            unset($options['resultCache']);
        }

        $options['proxyDir'] = \Yii::getPathOfAlias($options['proxyDir']);

        // loop through setters of remaining options
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($config, $method) === true) {
                $config->{$method}($value);
            }
        }

        $em = ORM\EntityManager::create($conn, $config);

        $this->_cache['em'][$name] = $em;

        return $em;

    }//end getEntityManager()

    /**
     * Take the configuration and return a mapping driver
     *
     * @param array &$config the driver options
     *
     * @return Doctrine\ORM\Mapping\Driver\Driver
     */
    private function _getMappingDriver(array & $config)
    {
        $drivers = array(
            'XmlDriver'  => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
            'YamlDriver' => 'Doctrine\ORM\Mapping\Driver\YamlDriver',
        );

        if (is_array($config['mappingPaths']) === true) {
            foreach ($config['mappingPaths'] as $index => $path) {
                $config['mappingPaths'][$index] = \Yii::getPathOfAlias($path);
            }
        } else {
            $config['mappingPaths'] = \Yii::getPathOfAlias($config['mappingPaths']);
        }

        // set default annotation driver
        if (array_key_exists($config['mappingDriver'], $drivers) === false
            || $config['mappingDriver'] === 'AnnotationDriver'
        ) {
            $reader = new AnnotationReader();
            $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
            $driver = new AnnotationDriver($reader, $config['mappingPaths']);
            unset($config['mappingDriver']);
            unset($config['mappingPaths']);
            return $driver;
        }

        $mappingClass = $drivers[$config['mappingDriver']];
        $driver       = new $mappingClass($config['mappingPaths']);

        if (isset($config['mappingDriverOptions']) === true) {
            foreach ($config['mappingDriverOptions'] as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($driver, $method) === true) {
                    $driver->{$method}($value);
                }
            }

            unset($config['mappingDriverOptions']);
        }

        unset($config['mappingDriver']);
        unset($config['mappingDriverPaths']);

        return $driver;

    }//end _getMappingDriver()

    /**
     * Get an event manager configuration
     *
     * @param array $config the configuration
     *
     * @return Doctrine\Common\EventManager|null
     */
    private function _getEventManager(array $config=array())
    {
        if (isset($config['eventManagerClass']) === false) {
            return null;
        }

        if (isset($this->_cache['eventManager']) === false) {
            $eventManagerClass = $config['eventManagerClass'];
            $this->_cache['eventManager'] = new $eventManagerClass();
        }

        if (isset($config['eventSubscribers']) === true) {
            foreach ($config['eventSubscribers'] as $subscriber) {
                $sub = new $subscriber();
                $this->_cache['eventManager']->addEventSubscriber($sub);
            }
        }

        return $this->_cache['eventManager'];

    }//end _getEventManager()

    /**
     * Get a DBAL configuration
     *
     * @param array $config the configuration
     *
     * @return Doctrine\DBAL\Configuration|null
     */
    private function _getDBALConfiguration(array $config=array())
    {
        if (array_key_exists('configurationClass', $config) === false) {
            return;
        }

        $configClass   = $config['configurationClass'];
        $configuration = new $configClass();

        if (empty($config['sqlLoggerClass']) === false) {
            $sqlLoggerClass = $config['sqlLoggerClass'];
            $loggerClass    = new $sqlLoggerClass();
            $configuration->setSQLLogger($loggerClass);
        }

        return $configuration;

    }//end _getDBALConfiguration()

}//end class