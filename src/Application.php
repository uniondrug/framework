<?php
/**
 * 应用入口
 */

namespace Uniondrug\Framework;

/**
 * Class Application
 *
 * @property \Uniondrug\Framework\Container $di
 */
class Application extends \Phalcon\Mvc\Application
{

    public function boot()
    {
        /**
         * Providers
         */
        $providers = $this->di->getConfig()->path('app.providers', []);
        $providers === null || $this->di->registerServices($providers);
        // disable views
        $this->useImplicitView(false);

        return $this;
    }
}
