<?php
/*
**********************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
* ==================================================================
*                Модель фото бойцов
* 
* @file models/FighterPhoto.php
* @version 0.0.2
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

    /**
     * @var UploadedFile Атрибут для загрузки файла через форму
     */
    public $photo_file;

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
            [['fighter_id', 'file_size', 'photo_year', 'moderator_id', 'uploaded_by'], 'integer'],
            [['photo_year'], 'integer', 'min' => 1900, 'max' => 2025],
            
            // Правила для загрузки файла через форму
            [['photo_file'], 'file', 
                'skipOnEmpty' => false, 
                'extensions' => 'jpg, jpeg, png, gif, webp', 
                'maxSize' => self::MAX_FILE_SIZE,
                'mimeTypes' => 'image/jpeg, image/png, image/gif, image/webp',
                'on' => 'upload' // Сценарий для загрузки
            ],
            
            [['description', 'ai_description'], 'string'],
            [['description'], 'string', 'max' => 500],
            [['photo_data', 'thumbnail_data'], 'safe'],
            [['mime_type'], 'string', 'max' => 50],
            [['file_name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
            [['is_main'], 'boolean'],
            
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['is_main'], 'default', 'value' => 0],
            [['file_size'], 'default', 'value' => 0],
            
            [['fighter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fighter::class, 'targetAttribute' => ['fighter_id' => 'id']],
            [['moderator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['moderator_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['uploaded_by' => 'id']],
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
            'file_name' => 'Имя файла',
            'file_size' => 'Размер файла',
            'photo_file' => 'Файл фотографии',
            'description' => 'Описание',
            'ai_description' => 'Описание от ИИ',
            'photo_year' => 'Год фотографии',
            'is_main' => 'Главная фотография',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'moderated_at' => 'Дата модерации',
            'moderator_id' => 'Модератор',
            'uploaded_by' => 'Загружено пользователем',
        ];
    }

    /**
     * Сценарии валидации
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['upload'] = ['fighter_id', 'photo_file', 'description', 'photo_year', 'file_name', 'file_size', 'mime_type', 'uploaded_by'];
        $scenarios['update'] = ['description', 'photo_year', 'is_main', 'file_name'];
        $scenarios['moderate'] = ['status', 'moderator_id', 'moderated_at'];
        
        return $scenarios;
    }

    /**
     * Обработка загрузки изображения и сохранение в BLOB
     */
    public function uploadToDb()
    {
        if (!$this->photo_file || !$this->validate(['photo_file'])) {
            Yii::error('Ошибка валидации файла: ' . implode(', ', $this->getErrors('photo_file')), 'fighter');
            return false;
        }

        try {
            // Читаем файл в бинарном формате
            $filePath = $this->photo_file->tempName;
            $photoData = file_get_contents($filePath);
            
            // Сохраняем информацию о файле
            $this->file_size = $this->photo_file->size;
            $this->mime_type = $this->photo_file->type;
            $this->file_name = $this->photo_file->name;
            
            // Сохраняем оригинал
            $this->photo_data = $photoData;
            
            // Создаем и сохраняем миниатюру
            $this->createThumbnail($filePath);
            
            // Генерируем AI описание
            $this->generateAIDescription();
            
            // Устанавливаем пользователя, загрузившего фото
            if (!Yii::$app->user->isGuest) {
                $this->uploaded_by = Yii::$app->user->id;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Yii::error('Ошибка загрузки фото в БД: ' . $e->getMessage(), 'fighter');
            $this->addError('photo_file', 'Ошибка загрузки изображения: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Создание миниатюры с использованием GD
     */
    private function createThumbnail($filePath)
    {
        try {
            // Определяем тип изображения
            $imageInfo = @getimagesize($filePath);
            if (!$imageInfo) {
                throw new \Exception('Не удалось определить тип изображения');
            }
            
            $mimeType = $imageInfo['mime'];
            list($srcWidth, $srcHeight) = $imageInfo;
            
            // Создаем изображение из файла
            switch ($mimeType) {
                case 'image/jpeg':
                    $sourceImage = imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($filePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($filePath);
                    break;
                case 'image/webp':
                    if (!function_exists('imagecreatefromwebp')) {
                        throw new \Exception('WebP не поддерживается на этом сервере');
                    }
                    $sourceImage = imagecreatefromwebp($filePath);
                    break;
                default:
                    throw new \Exception('Неподдерживаемый формат изображения: ' . $mimeType);
            }
            
            if (!$sourceImage) {
                throw new \Exception('Не удалось создать изображение из файла');
            }
            
            // Вычисляем пропорции для миниатюры
            $ratio = $srcWidth / $srcHeight;
            $thumbWidth = self::THUMBNAIL_WIDTH;
            $thumbHeight = self::THUMBNAIL_HEIGHT;
            
            if ($thumbWidth / $thumbHeight > $ratio) {
                $thumbWidth = (int)($thumbHeight * $ratio);
            } else {
                $thumbHeight = (int)($thumbWidth / $ratio);
            }
            
            // Создаем миниатюру
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // Сохраняем прозрачность для PNG и GIF
            if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);
            } else {
                // Для JPEG устанавливаем белый фон
                $white = imagecolorallocate($thumbnail, 255, 255, 255);
                imagefill($thumbnail, 0, 0, $white);
            }
            
            // Копируем и изменяем размер с ресемплированием
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
            
            // Сохраняем миниатюру в буфер как JPEG (универсальный формат для миниатюр)
            ob_start();
            imagejpeg($thumbnail, null, 85);
            $thumbnailData = ob_get_clean();
            
            $this->thumbnail_data = $thumbnailData;
            
            // Освобождаем память
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            
        } catch (\Exception $e) {
            Yii::error('Ошибка создания миниатюры: ' . $e->getMessage(), 'fighter');
            // В крайнем случае создаем заглушку
            $this->thumbnail_data = $this->createPlaceholderThumbnail();
        }
    }

    /**
     * Создание заглушки для миниатюры
     */
    private function createPlaceholderThumbnail()
    {
        $width = self::THUMBNAIL_WIDTH;
        $height = self::THUMBNAIL_HEIGHT;
        
        $placeholder = imagecreate($width, $height);
        $backgroundColor = imagecolorallocate($placeholder, 240, 240, 240);
        $textColor = imagecolorallocate($placeholder, 150, 150, 150);
        
        imagefilledrectangle($placeholder, 0, 0, $width, $height, $backgroundColor);
        
        $text = 'Thumbnail';
        $font = 5; // Встроенный шрифт
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = (int)(($width - $textWidth) / 2);
        $y = (int)(($height - $textHeight) / 2);
        
        imagestring($placeholder, $font, $x, $y, $text, $textColor);
        
        ob_start();
        imagejpeg($placeholder);
        $imageData = ob_get_clean();
        imagedestroy($placeholder);
        
        return $imageData;
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
                
                if ($this->fighter->militaryRank) {
                    $description .= ", {$this->fighter->militaryRank->name}";
                }
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
     * Получить текст статуса
     */
    public function getStatusText()
    {
        $statuses = [
            self::STATUS_PENDING => 'На модерации',
            self::STATUS_APPROVED => 'Одобрено',
            self::STATUS_REJECTED => 'Отклонено',
        ];
        
        return $statuses[$this->status] ?? 'Неизвестно';
    }

    /**
     * Получить CSS класс для статуса
     */
    public function getStatusClass()
    {
        $classes = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
        ];
        
        return $classes[$this->status] ?? 'default';
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
        return $this->save(false, ['is_main', 'updated_at']);
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
        return $this->save(false, ['status', 'moderator_id', 'moderated_at', 'updated_at']);
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
        
        return $this->save(false, ['status', 'moderator_id', 'moderated_at', 'description', 'updated_at']);
    }

    /**
     * Проверить, можно ли установить как основную
     */
    public function canSetAsMain()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Проверить, можно ли удалить
     */
    public function canDelete()
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }
        
        // Модераторы и админы могут удалять любые фото
        $userModel = $user->identity;
        if (in_array($userModel->role, ['Moderator', 'Admin', 'Develop'])) {
            return true;
        }
        
        // Пользователи могут удалять только свои фото
        return ($this->fighter && $this->fighter->user_id == $user->id);
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            // Устанавливаем пользователя, загрузившего фото
            if (Yii::$app->user->isGuest) {
                $this->uploaded_by = null;
            } else {
                $this->uploaded_by = Yii::$app->user->id;
            }
        }

        // Если это главное фото, сбрасываем другие главные фото
        if ($this->is_main && $this->status === self::STATUS_APPROVED) {
            self::updateAll(
                ['is_main' => 0],
                ['fighter_id' => $this->fighter_id, 'is_main' => 1]
            );
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
     * Gets query for [[Uploader]].
     */
    public function getUploader()
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
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

    /**
     * Получить фото на модерации
     */
    public static function getPendingPhotos()
    {
        return self::find()
            ->where(['status' => self::STATUS_PENDING])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    /**
     * Получить количество фото на модерации
     */
    public static function getPendingCount()
    {
        return self::find()
            ->where(['status' => self::STATUS_PENDING])
            ->count();
    }
}