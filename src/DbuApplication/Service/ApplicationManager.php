<?php
/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace DbuApplication\Service;

use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationManager implements ServiceLocatorAwareInterface
{
    const LOGGER_SERVICE = 'logger';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $isInitEnvironment = false;

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @return ApplicationManager
     */
    public function initEnvironment()
    {
        if ($this->isInitEnvironment) {
            return $this;
        }
        $options = isset($this->config['application_environment']) ? $this->config['application_environment'] : array();
        $this->initEnvironmentOptions($options);
        $this->isInitEnvironment = true;
        return $this;
    }

    /**
     * Register logger service with default 'null' writer
     * Register shutdown function
     *
     * @return ApplicationManager
     */
    public function initLoggerService()
    {
        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = $this->getServiceLocator()->get('ServiceManager');
        $serviceManager->setFactory(self::LOGGER_SERVICE, function ($sm) {
            return new Logger(array('writers' => array(array('name' => 'null'))));
        });
        $this->registerShutdownFunction($serviceManager, self::LOGGER_SERVICE);
        return $this;
    }

    /**
     * Register shutdown function
     *
     * @param ServiceLocatorInterface $serviceManager
     * @param string $loggerServiceName
     * @return ApplicationManager
     */
    protected function registerShutdownFunction(ServiceLocatorInterface $serviceManager, $loggerServiceName)
    {
        register_shutdown_function(function($serviceManager) use ($loggerServiceName) {
            /**
             * @var \Zend\ServiceManager\ServiceManager $serviceManager
             * @var Logger $logger
             */
            if ($e = error_get_last()) {
                $logger = $serviceManager->get($loggerServiceName);
                $logger->crit($e['message'], $e);
            }
        }, $serviceManager);
        return $this;
    }

    /**
     * Init application environment
     *
     * @param array $options
     * @return ApplicationManager
     */
    protected function initEnvironmentOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'error_reporting':
                    error_reporting($value);
                    break;
                case 'ini_set':
                    foreach ($value as $opt => $val) {
                        ini_set($opt, $val);
                    }
                    break;
                case 'date_default_timezone':
                    date_default_timezone_set($value);
                    break;
                case 'umask':
                    umask($value);
                    break;
                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ApplicationManager
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}