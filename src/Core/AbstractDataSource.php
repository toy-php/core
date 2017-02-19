<?php

namespace Core;

use Core\Interfaces\DataSource as DataSourceInterface;

abstract class AbstractDataSource extends EventObject implements DataSourceInterface
{
    /**
     * Маршруты событий
     * @return array
     */
    public function getEventRouts()
    {
        $eventRouts = parent::getEventRouts();
        $eventRouts[ModelEvents::EVENT_FETCH] = [$this, 'fetch'];
        $eventRouts[ModelEvents::EVENT_SAVE] = [$this, 'save'];
        $eventRouts[ModelEvents::EVENT_DELETE] = [$this, 'delete'];
        return $eventRouts;
    }

}