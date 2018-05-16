<?php
/**
 * DatabaseListener.php
 *
 */

namespace Uniondrug\Framework\Events\Listeners;

use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Uniondrug\Framework\Injectable;

/**
 * Class DatabaseListener
 **/
class DatabaseListener extends Injectable
{
    /**
     * @var \Phalcon\Db\Profiler
     */
    protected $profiler;

    /**
     * Creates the profiler and starts the logging
     */
    public function __construct()
    {
        $this->profiler = new Profiler();
    }

    /**
     * This is executed if the event triggered is 'beforeQuery'
     *
     * @param \Phalcon\Events\Event $event
     * @param \Phalcon\Db\Adapter   $connection
     */
    public function beforeQuery(Event $event, $connection)
    {
        $this->profiler->startProfile(
            $connection->getSQLStatement(), $connection->getSQLVariables()
        );
    }

    /**
     * This is executed if the event triggered is 'afterQuery'
     *
     * @param \Phalcon\Events\Event $event
     * @param \Phalcon\Db\Adapter   $connection
     */
    public function afterQuery(Event $event, $connection)
    {
        $processId = getmypid();
        $this->profiler->stopProfile();

        /** @var \Phalcon\Db\Profiler\Item $profile */
        $profile = $this->profiler->getLastProfile();
        $sql = $profile->getSQLStatement();
        $vars = $profile->getSQLVariables();
        if (count($vars)) {
            if ('select' == strtolower(substr($sql, 0, 7))) {
                // 针对select的替换
                $sql = str_replace(array_map(function ($v) {
                    return ':' . $v;
                }, array_keys($vars)), array_values($vars), $sql);
            } else {
                // 针对update/insert的替换
                $replaced = 0;
                $cursor = 0;
                while ($s = substr($sql, $cursor, 1)) {
                    if ($s == '?') {
                        if (is_string($vars[$replaced])) {
                            $replacement = "\"" . $vars[$replaced] . "\"";
                        } else {
                            $replacement = $vars[$replaced];
                        }
                        $sql = substr_replace($sql, $replacement , $cursor, 1);

                        $cursor += strlen($replacement);
                        $replaced ++;
                    } else {
                        $cursor ++;
                    }
                }
            }
        }

        $start = $profile->getInitialTime();
        $final = $profile->getFinalTime();
        $total = $profile->getTotalElapsedSeconds();
        $this->getDI()->getLogger('database')->debug("[Database][$processId]: Start=$start, Final=$final, Total=$total, SQL=$sql");
    }

    /**
     * @return \Phalcon\Db\Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
}
