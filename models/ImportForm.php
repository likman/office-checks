<?php
namespace app\models;

use app\components\Helper;
use app\components\Logger;
use app\components\PermissionManager;
use app\models\checks\EventHuman;
use Exception;
use yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImportForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $import_file;
    public $import_type;

    public function rules()
    {
        return [
            [['import_file'], 'file', 'skipOnEmpty' => false, 'checkExtensionByMimeType' => false, 'extensions' => 'csv',],
            [['import_type'], 'integer'],
            [['import_file','import_type'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'import_file' => 'Файл для импорта',
            'import_type' => 'Тип данных',
        ];
    }

    public static function getTypeList()
    {
        return [
            1=>'Сотрудники',
            2=>'Запись на мероприятие',
        ];
    }

    public function save()
    {
        switch ($this->import_type) {
            case 1:
                return $this->importHumans();
            case 2:
                return $this->importEventHumanBindings();
        }
        $this->addError('import_type', 'Задание не найдено.');
        return false;
    }

    private function importHumans()
    {
        if (!PermissionManager::can("Human update")) {
            $this->addError('import_type', 'У вас нет прав.');
            return false;
        }
        $rows = Helper::getDataFromCsvFile($this->import_file->tempName);
        $i = 1;
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($rows as $row) {
            $i++;
            if (!Helper::isOk($row['Код роли']))
                continue;
            try {
                $human = new HumanForm();
                $human->setScenario($human::SCENARIO_CREATE);
                $human->id_role = $row['Код роли'];
                $human->name = $row['ФИО'];
                $human->email = $row['Почта'];
                $human->telephone = $row['Телефон'];
                $human->password = $row['Пароль'];
                if (!$human->validate()) {
                    $transaction->rollBack();
                    $this->addError('import_file', "Ошибка в строке $i - " . Helper::getModelError($human));
                    return false;
                }
                $human->save();
            } catch (Exception $exception) {
                Logger::error("Ошибка в строке $i - " . $exception->getMessage(), "web");
                $transaction->rollBack();
                $this->addError('import_file', "Ошибка в строке $i - " . Helper::getModelError($human));
                return false;
            }
        }
        $transaction->commit();
        return true;
    }


    private function importEventHumanBindings()
    {
        if (!PermissionManager::can("EventHuman update")) {
            $this->addError('import_type', 'У вас нет прав.');
            return false;
        }
        $rows = Helper::getDataFromCsvFile($this->import_file->tempName);
        $i = 1;
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($rows as $row) {
            $i++;
            if (!Helper::isOk($row['Код мероприятия']))
                continue;
            try {
                $event_human = new EventHuman();
                $event_human->id_human = $row['Код сотрудника'];
                $event_human->id_event = $row['Код мероприятия'];
                if (!$event_human->validate()) {
                    $transaction->rollBack();
                    $this->addError('import_file', "Ошибка в строке $i - " . Helper::getModelError($event_human));
                    return false;
                }
                $event_human->save();
            } catch (Exception $exception) {
                Logger::error("Ошибка в строке $i - " . $exception->getMessage(), "web");
                $transaction->rollBack();
                $this->addError('import_file', "Ошибка в строке $i - " . Helper::getModelError($event_human));
                return false;
            }
        }
        $transaction->commit();
        return true;
    }

}