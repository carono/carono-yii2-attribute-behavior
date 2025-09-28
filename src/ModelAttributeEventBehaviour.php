<?php

namespace carono\yii2\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class ModelAttributeEventBehaviour
 *
 * Behavior for creating events when model attributes change
 * You need to create a new class inheriting from this behavior and attach it to the model
 * Then, in the new behavior, you can define methods like onChange{attribute_name}, and when exactly
 * this attribute changes, this method will be triggered.
 * Method parameters:
 *
 * AfterSaveEvent $event - event object
 * boolean $insert - indicates whether the model was created or updated (similar to afterSave)
 * ActiveRecord $model - the model itself
 * mixed $value - new attribute value
 * mixed $oldValue - old attribute value
 * array $changedAttributes - array of all changed attributes in the model
 *
 * !IMPORTANT: $changedAttributes contains old attribute values, not new ones
 *
 * Example
 *
 * public function onChangeStatus_id($event, $insert, $model, $value, $oldValue, $changedAttributes)
 * {
 *   // Event when status_id attribute changes in the model
 * }
 *
 */
class ModelAttributeEventBehaviour extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'onAfterUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'onAfterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'onBeforeUpdate',
        ];
    }

    public function onAfterUpdate($event)
    {
        $this->event($event, false, 'onChange', $event->changedAttributes);
    }

    public function onAfterInsert($event)
    {
        $this->event($event, true, 'onInsert', $event->changedAttributes);
    }

    public function onBeforeUpdate($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $dirty = $owner->getDirtyAttributes();
        $changedAttributes = [];

        foreach ($dirty as $attribute => $newValue) {
            $oldValue = $owner->getOldAttribute($attribute);
            $changedAttributes[$attribute] = $oldValue;
        }

        $this->event($event, false, 'onBeforeChange', $changedAttributes);
    }

    /**
     * @param \yii\base\Event|\yii\db\AfterSaveEvent $event
     * @param bool $insert
     * @param string $methodPrefix
     */
    protected function event($event, $insert, $methodPrefix, $changedAttributes)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        foreach ($changedAttributes as $attribute => $oldValue) {
            $method = $methodPrefix . ucfirst($attribute);
            if (method_exists($this, $method)) {
                $newValue = $owner->getAttribute($attribute);
                call_user_func(
                    [$this, $method],
                    $event,
                    $insert,
                    $owner,
                    $newValue,
                    $oldValue,
                    $changedAttributes
                );
            }
        }
    }
}