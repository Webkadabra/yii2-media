<?php

namespace DevGroup\Media\models;

use DevGroup\Media\MediaModule;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\helpers\ArrayHelper;
use yii2tech\ar\role\RoleBehavior;

/**
 * Class File
 *
 * @package DevGroup\Media\models
 * @mixin RoleBehavior
 */
class File extends MediaFs
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'roleBehavior' => [
                'class' => RoleBehavior::className(), // Attach role behavior
                'roleRelation' => 'fileData', // specify name of the relation to the slave table
                'roleAttributes' => [
                    'is_file' => '1',
                ],
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'attributeTypes' => [
                    'size' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'file_type_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => true,
            ],
        ]);
    }

    /**
     * @return MediaFsQuery
     */
    public static function find()
    {
        return parent::find()->where(['is_file' => 1])->with('fileData');
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            $this->getRoleRelationModel()->attributeLabels()
        );
    }

    public function attributeHints()
    {
        return array_merge(
            parent::attributeHints(),
            $this->getRoleRelationModel()->attributeHints()
        );
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->getRoleRelationModel()->rules()
        );
    }

    public function getFileData()
    {
        return $this->hasOne(MediaFile::class, ['file_id' => 'id']);
    }

    public function getImageData()
    {
        return $this->hasOne(MediaImage::class, ['file_id' => 'file_id']);
    }

    /**
     * @param \DevGroup\Media\models\Folder $folder
     * @param string                        $name
     *
     * @return File
     */
    public static function ensureFile(Folder $folder, $name)
    {
        $model = File::find()
            ->inFolder($folder, 1)
            ->andWhere([
                'name' => $name
            ])
            ->one();
        if ($model === null) {
            $model = new File(['name' => $name, 'fs_path' => $name]);
            $model->appendTo($folder);
        }
        return $model;
    }
}
