<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

class PXB_Controller
{
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        if (defined('WP_PXB_PLUGIN_VERSION')) {
            $this->version = WP_PXB_PLUGIN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = WP_PXB_PLUGIN_NAME;

    }

    private function loadDependenciesPXB()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'helpers/helper.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-pxb-http-client.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-pxb-activator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-pxb-loader.php';

        $this->loader = new PXB_Loader();
    }

    public function runPX()
    {
        $this->loadClassesPXB();
        $this->createInstancesPXB();
        $this->loadDependenciesPXB();
        try {
            $this->dependency_checker->checkPX();
        } catch (PXB_Missing_Dependencies_Exception $e) {
            $this->reportMissingDependenciesPXB($e->getMissingPluginNamesPXB());
            return;
        }
        $this->loader->run();
    }

    private function loadClassesPXB()
    {
        // Exceptions
        require_once dirname(__DIR__) . '/classes/exceptions/class-pxb-exception.php';
        require_once dirname(__DIR__) . '/classes/exceptions/class-pxb-missing-dependencies-exception.php';

        // Dependency checker
        require_once dirname(__DIR__) . '/classes/class-pxb-dependency-checker.php';
        require_once dirname(__DIR__) . '/classes/class-pxb-missing-dependency-reporter.php';
    }

    private function createInstancesPXB()
    {
        $this->dependency_checker = new PXB_Dependency_Checker();
    }

    /**
     * @param string[] $missing_plugin_names
     */
    private function reportMissingDependenciesPXB($missing_plugin_names)
    {
        $missing_dependency_reporter = new PXB_Missing_Dependency_Reporter($missing_plugin_names);
        $missing_dependency_reporter->bindToAdminHooksPXB();
    }

}
