<?php
/*
**********************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
* ==================================================================
*                Модель фото бойцов
* 
* @file models/FighterPhoto.php
* @version 0.0.1
*
* @author Александр Васильков
* @author Home Lab, Пенза (с), 2025
* @author E-Mail bac@sura.ru
* @var yii\web\View $this
* 
* @date 06.09.2025
*
**********************************************************************
*/

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\imagine\Image;
use Imagine\Image\Box;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class FighterPhoto extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    
    // Максимальные размеры файлов
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    const THUMBNAIL_WIDTH = 400;
    const THUMBNAIL_HEIGHT = 400;

    public $imageFile;

    public static function tableName()
    {
        return 'fighter_photo';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['fighter_id'], 'required'],
            [['fighter_id', 'file_size', 'photo_year', 'moderator_id'], 'integer'],
            [['photo_year'], 'integer', 'min' => 1900, 'max' => 2025],
            [['description', 'ai_description'], 'string'],
            [['photo_data', 'thumbnail_data'], 'safe'],
            [['mime_type'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 10],
            [['is_main'], 'boolean'],
            
            [['imageFile'], 'file', 
                'skipOnEmpty' => true, // Изменено на true для обновления
                'extensions' => 'png, jpg, jpeg',
                'maxSize' => self::MAX_FILE_SIZE,
                'mimeTypes' => 'image/jpeg, image/png, image/jpg'
            ],
            
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['is_main'], 'default', 'value' => 0],
            
            [['fighter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fighter::class, 'targetAttribute' => ['fighter_id' => 'id']],
            [['moderator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['moderator_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fighter_id' => 'Боец',
            'photo_data' => 'Фото',
            'thumbnail_data' => 'Эскиз',
            'mime_type' => 'Тип файла',
            'file_size' => 'Размер файла',
            'description' => 'Описание',
            'ai_description' => 'Описание от ИИ',
            'photo_year' => 'Год фотографии',
            'is_main' => 'Главная фотография',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'moderated_at' => 'Дата модерации',
            'moderator_id' => 'Модератор',
            'imageFile' => 'Фотография',
        ];
    }

    /**
     * Обработка загрузки изображения и сохранение в BLOB
     */
    public function uploadToDb()
    {
        if (!$this->imageFile || !$this->validate()) {
            return false;
        }

        try {
            // Читаем файл в бинарном формате
            $filePath = $this->imageFile->tempName;
            $photoData = file_get_contents($filePath);
            $this->file_size = strlen($photoData);
            $this->mime_type = $this->imageFile->type;
            
            // Сохраняем оригинал
            $this->photo_data = $photoData;
            
            // Создаем и сохраняем миниатюру
            $this->createThumbnail($filePath);
            
            return true;
            
        } catch (\Exception $e) {
            Yii::error('Ошибка загрузки фото в БД: ' . $e->getMessage(), 'fighter');
            $this->addError('imageFile', 'Ошибка загрузки изображения: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Создание миниатюры
     */
    private function createThumbnail($filePath)
    {
        try {
            $image = Image::getImagine()->open($filePath);
            
            // Получаем размеры оригинала
            $size = $image->getSize();
            $width = $size->getWidth();
            $height = $size->getHeight();
            
            // Вычисляем пропорции для миниатюры
            $ratio = $width / $height;
            $thumbWidth = self::THUMBNAIL_WIDTH;
            $thumbHeight = self::THUMBNAIL_HEIGHT;
            
            if ($thumbWidth / $thumbHeight > $ratio) {
                $thumbWidth = $thumbHeight * $ratio;
            } else {
                $thumbHeight = $thumbWidth / $ratio;
            }
            
            $thumbnail = $image->thumbnail(
                new Box($thumbWidth, $thumbHeight),
                \Imagine\Image\ImageInterface::THUMBNAIL_INSET
            );
        
            // Конвертируем миниатюру в бинарные данные
            $thumbnailData = $thumbnail->get('jpg', ['quality' => 85]);
            $this->thumbnail_data = $thumbnailData;
        
        } catch (\Exception $e) {
            Yii::error('Ошибка создания миниатюры: ' . $e->getMessage(), 'fighter');
            // Не прерываем сохранение, если миниатюра не создалась
        }
    }
    
    /**
     * Упрощенная генерация описания (без AI API)
     */
    public function generateAIDescription()
    {
        if (!$this->photo_data) {
            return null;
        }

        try {
            // Базовое описание на основе данных фото
            $description = "Фотография бойца";
            
            if ($this->photo_year) {
                $description .= " {$this->photo_year} года";
            }
            
            if ($this->fighter) {
                $description .= ". Боец: {$this->fighter->fullName}";
            }
            
            $description .= ". Историческая фотография времен Великой Отечественной войны.";
            
            $this->ai_description = $description;
            return $this->ai_description;
            
        } catch (\Exception $e) {
            Yii::error('Ошибка генерации описания: ' . $e->getMessage(), 'fighter');
        }
        
        return null;
    }

    /**
     * Получить фото как base64
     */
    public function getPhotoBase64()
    {
        if (!empty($this->photo_data)) {
            // Обработка BLOB данных из MySQL
            if (is_resource($this->photo_data)) {
                $content = stream_get_contents($this->photo_data);
                rewind($this->photo_data);
            } else {
                $content = $this->photo_data;
            }
            
            if (!empty($content)) {
                return 'data:' . $this->mime_type . ';base64,' . base64_encode($content);
            }
        }
        return null;
    }

    /**
     * Получить миниатюру как base64
     */
    public function getThumbnailBase64()
    {
        if (!empty($this->thumbnail_data)) {
            // Обработка BLOB данных из MySQL
            if (is_resource($this->thumbnail_data)) {
                $content = stream_get_contents($this->thumbnail_data);
                rewind($this->thumbnail_data);
            } else {
                $content = $this->thumbnail_data;
            }
            
            if (!empty($content)) {
                return 'data:image/jpeg;base64,' . base64_encode($content);
            }
        }
        
        // Если миниатюры нет, возвращаем оригинал
        return $this->getPhotoBase64();
    }

    /**
     * Получить размер файла в читаемом формате
     */
    public function getFormattedFileSize()
    {
        return Yii::$app->formatter->asShortSize($this->file_size);
    }

    /**
     * Установить как главную фотографию
     */
    public function setAsMain()
    {
        // Сбрасываем все главные фото у этого бойца
        self::updateAll(
            ['is_main' => 0],
            ['fighter_id' => $this->fighter_id]
        );
        
        // Устанавливаем текущую как главную
        $this->is_main = 1;
        return $this->save();
    }

    /**
     * Проверить, является ли фото главным
     */
    public function getIsMainPhoto()
    {
        return (bool)$this->is_main;
    }

    /**
     * Одобрить фото
     */
    public function approve($moderatorId = null)
    {
        $this->status = self::STATUS_APPROVED;
        $this->moderated_at = new Expression('NOW()');
        $this->moderator_id = $moderatorId ?: Yii::$app->user->id;
        return $this->save();
    }

    /**
     * Отклонить фото
     */
    public function reject($moderatorId = null, $reason = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->moderated_at = new Expression('NOW()');
        $this->moderator_id = $moderatorId ?: Yii::$app->user->id;
        
        if ($reason) {
            $this->description = ($this->description ? $this->description . "\n\nПричина отклонения: " : "Причина отклонения: ") . $reason;
        }
        
        return $this->save();
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Если это главное фото, сбрасываем другие главные фото
        if ($this->is_main) {
            self::updateAll(
                ['is_main' => 0],
                ['fighter_id' => $this->fighter_id]
            );
        }

        // Автоматическая генерация описания если его нет
        if ($insert && empty($this->description) && empty($this->ai_description)) {
            $this->generateAIDescription();
        }

        return true;
    }

    /**
     * После удаления
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Если удалили главное фото, назначаем новое главное
        if ($this->is_main) {
            $newMain = self::find()
                ->where(['fighter_id' => $this->fighter_id])
                ->andWhere(['<>', 'id', $this->id])
                ->andWhere(['status' => self::STATUS_APPROVED])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();
                
            if ($newMain) {
                $newMain->setAsMain();
            }
        }
    }

    /**
     * Gets query for [[Fighter]].
     */
    public function getFighter()
    {
        return $this->hasOne(Fighter::class, ['id' => 'fighter_id']);
    }

    /**
     * Gets query for [[Moderator]].
     */
    public function getModerator()
    {
        return $this->hasOne(User::class, ['id' => 'moderator_id']);
    }

    /**
     * Получить все одобренные фото бойца
     */
    public static function getApprovedPhotos($fighterId)
    {
        return self::find()
            ->where(['fighter_id' => $fighterId, 'status' => self::STATUS_APPROVED])
            ->orderBy(['is_main' => SORT_DESC, 'photo_year' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();
    }

    /**
     * Получить главное фото бойца
     */
    public static function getMainPhoto($fighterId)
    {
        return self::find()
            ->where(['fighter_id' => $fighterId, 'is_main' => 1, 'status' => self::STATUS_APPROVED])
            ->one();
    }
}