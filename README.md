Here’s a README in English for your Yii2 behavior code:

---

# Model Attribute Event Behavior for Yii2

A behavior for Yii2 ActiveRecord that triggers custom methods when specific model attributes are changed. Useful for handling attribute-specific logic after insert/update operations.

## Installation

Place the `ModelAttributeEventBehaviour` class in your project (e.g., under `components/behaviors`). Ensure the namespace matches your project structure.

## Usage

1. **Extend the Behavior**: Create a new class inheriting from `ModelAttributeEventBehaviour`.
2. **Attach to Model**: Attach the behavior to your ActiveRecord model.
3. **Define Handlers**: Implement methods in the format `onChange{AttributeName}` or `onInsert{AttributeName}` to handle attribute changes.

### Example

#### Step 1: Create a Custom Behavior Class
```php
namespace app\components\behaviors;

class MyEventBehavior extends \carono\yii2\behaviors\ModelAttributeEventBehaviour
{
    public function onChangeStatusId($event, $insert, $model, $value, $oldValue, $changedAttributes)
    {
        // Triggered when `status_id` changes
        if (!$insert && $oldValue != $value) {
            // Custom logic here (e.g., send notification, log change)
        }
    }

    public function onInsertEmail($event, $insert, $model, $value, $oldValue, $changedAttributes)
    {
        // Triggered when `email` is set during model creation
        if ($insert) {
            // Custom logic for new email
        }
    }
}
```

#### Step 2: Attach to Your Model
```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\behaviors\MyEventBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            MyEventBehavior::class,
        ];
    }
}
```

## Method Parameters

Handler methods receive the following arguments:
- `$event`: The AfterSaveEvent object.
- `$insert`: Boolean indicating if the model was just inserted.
- `$model`: The ActiveRecord model instance.
- `$value`: New value of the attribute.
- `$oldValue`: Old value of the attribute.
- `$changedAttributes`: Array of all changed attributes and their old values (as in `$event->changedAttributes`).

## Events

- **After Insert**: Triggers methods prefixed with `onInsert` (e.g., `onInsertEmail` for the `email` attribute).
- **After Update**: Triggers methods prefixed with `onChange` (e.g., `onChangeStatusId` for the `status_id` attribute).

## Important Notes

- The `$changedAttributes` array contains the **old values** of changed attributes.
- Handlers are only triggered if the attribute value actually changes (as detected by Yii2’s dirty attributes).
- The behavior automatically binds to the `EVENT_AFTER_INSERT` and `EVENT_AFTER_UPDATE` events.

## License

MIT