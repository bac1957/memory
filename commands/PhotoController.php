<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\Fighter;
use app\models\FighterPhoto;

class PhotoController extends Controller
{
    /**
     * Добавляет фотографию для бойца
     * 
     * Использование:
     * yii photo/add <fighter_id> <file_path> [--description=""] [--year=] [--main=0]
     * 
     * @param int $fighterId ID бойца
     * @param string $filePath Путь к файлу фотографии
     * @param string $description Описание фотографии
     * @param int $year Год фотографии
     * @param int $main Признак главной фотографии (0 или 1)
     * @return int
     */
    public function actionAdd($fighterId, $filePath, $description = '', $year = null, $main = 0)
    {
        $this->stdout("Добавление фотографии для бойца ID: {$fighterId}\n", Console::FG_BLUE);
        
        // Проверяем существование бойца
        $fighter = Fighter::findOne($fighterId);
        if (!$fighter) {
            $this->stderr("Ошибка: Боец с ID {$fighterId} не найден.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        $this->stdout("Боец: {$fighter->fullName}\n", Console::FG_GREEN);
        
        // Проверяем существование файла (экранируем путь)
        $filePath = trim($filePath);
        if (!file_exists($filePath)) {
            $this->stderr("Ошибка: Файл '{$filePath}' не найден.\n", Console::FG_RED);
            $this->stdout("Текущая директория: " . getcwd() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // Проверяем тип файла
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($filePath);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->stderr("Ошибка: Неподдерживаемый тип файла: {$mimeType}\n", Console::FG_RED);
            $this->stdout("Разрешенные типы: " . implode(', ', $allowedTypes) . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        $this->stdout("Файл: {$filePath} ({$mimeType})\n");
        
        // Читаем файл
        $photoData = file_get_contents($filePath);
        if ($photoData === false) {
            $this->stderr("Ошибка: Не удалось прочитать файл.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        $fileSize = filesize($filePath);
        $this->stdout("Размер файла: " . $this->formatBytes($fileSize) . "\n");
        
        // Создаем эскиз
        $thumbnailData = $this->createThumbnail($filePath, $mimeType);
        if ($thumbnailData) {
            $this->stdout("Эскиз создан: " . strlen($thumbnailData) . " байт\n");
        } else {
            $this->stdout("Эскиз не создан\n", Console::FG_YELLOW);
        }
        
        // Если устанавливаем как главную, снимаем флаг с других фото
        if ($main) {
            $this->removeMainPhotoFlag($fighterId);
            $this->stdout("Установлена как главная фотография\n", Console::FG_GREEN);
        }
        
        // Создаем запись в базе данных
        $photo = new FighterPhoto();
        $photo->fighter_id = $fighterId;
        $photo->photo_data = $photoData;
        $photo->thumbnail_data = $thumbnailData;
        $photo->mime_type = $mimeType;
        $photo->file_size = $fileSize;
        $photo->description = $description;
        $photo->photo_year = $year;
        $photo->is_main = $main ? 1 : 0;
        $photo->status = 'approved'; // Автоматически одобряем
        $photo->moderated_at = date('Y-m-d H:i:s');
        $photo->moderator_id = 2; // ID модератора
        
        if ($photo->save()) {
            $this->stdout("✅ Фотография успешно добавлена! ID записи: {$photo->id}\n", Console::FG_GREEN);
            
            // Выводим информацию о созданной записи
            $this->stdout("\nИнформация о записи:\n", Console::FG_BLUE);
            $this->stdout("  ID: {$photo->id}\n");
            $this->stdout("  Боец: {$fighter->fullName} (ID: {$fighter->id})\n");
            $this->stdout("  Тип: {$photo->mime_type}\n");
            $this->stdout("  Размер: " . $this->formatBytes($photo->file_size) . "\n");
            $this->stdout("  Статус: {$photo->status}\n");
            $this->stdout("  Главная: " . ($photo->is_main ? 'ДА' : 'нет') . "\n");
            $this->stdout("  Дата модерации: {$photo->moderated_at}\n");
            $this->stdout("  Модератор: {$photo->moderator_id}\n");
            
            if ($description) {
                $this->stdout("  Описание: {$description}\n");
            }
            if ($year) {
                $this->stdout("  Год: {$year}\n");
            }
            
            return ExitCode::OK;
        } else {
            $this->stderr("❌ Ошибка при сохранении фотографии:\n", Console::FG_RED);
            foreach ($photo->errors as $attribute => $errors) {
                foreach ($errors as $error) {
                    $this->stderr("  - {$attribute}: {$error}\n", Console::FG_RED);
                }
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Удаляет флаг главной фотографии у всех фото бойца
     */
    private function removeMainPhotoFlag($fighterId)
    {
        FighterPhoto::updateAll(
            ['is_main' => 0],
            ['fighter_id' => $fighterId, 'is_main' => 1]
        );
    }
    
    /**
     * Создает эскиз изображения с пропорциями: макс. ширина 300px, макс. высота 200px
     */
    private function createThumbnail($filePath, $mimeType)
    {
        try {
            // Максимальные размеры эскиза
            $maxWidth = 300;
            $maxHeight = 200;
            
            // Создаем изображение в зависимости от типа
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($filePath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($filePath);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($filePath);
                    break;
                default:
                    return null;
            }
            
            if (!$source) {
                return null;
            }
            
            // Получаем размеры исходного изображения
            $srcWidth = imagesx($source);
            $srcHeight = imagesy($source);
            
            $this->stdout("Исходные размеры: {$srcWidth}x{$srcHeight}\n");
            
            // Вычисляем коэффициенты масштабирования
            $widthRatio = $maxWidth / $srcWidth;
            $heightRatio = $maxHeight / $srcHeight;
            
            // Используем меньший коэффициент, чтобы изображение вписалось в оба ограничения
            $ratio = min($widthRatio, $heightRatio);
            
            // Вычисляем новые размеры
            $newWidth = intval($srcWidth * $ratio);
            $newHeight = intval($srcHeight * $ratio);
            
            // Гарантируем, что размеры не превышают максимальные
            $newWidth = min($newWidth, $maxWidth);
            $newHeight = min($newHeight, $maxHeight);
            
            $this->stdout("Размеры эскиза: {$newWidth}x{$newHeight}\n");
            
            // Создаем новое изображение для эскиза
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Сохраняем прозрачность для PNG и GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
            } else {
                // Для JPEG и WebP устанавливаем белый фон
                $white = imagecolorallocate($thumbnail, 255, 255, 255);
                imagefill($thumbnail, 0, 0, $white);
            }
            
            // Копируем и изменяем размер
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
            
            // Сохраняем в буфер
            ob_start();
            
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($thumbnail, null, 85);
                    break;
                case 'image/png':
                    imagepng($thumbnail, null, 8);
                    break;
                case 'image/gif':
                    imagegif($thumbnail);
                    break;
                case 'image/webp':
                    imagewebp($thumbnail, null, 85);
                    break;
            }
            
            $thumbnailData = ob_get_clean();
            
            // Освобождаем память
            imagedestroy($source);
            imagedestroy($thumbnail);
            
            if ($thumbnailData) {
                $this->stdout("Эскиз создан успешно: " . strlen($thumbnailData) . " байт\n", Console::FG_GREEN);
                return $thumbnailData;
            } else {
                $this->stdout("Не удалось создать эскиз: пустые данные\n", Console::FG_YELLOW);
                return null;
            }
            
        } catch (\Exception $e) {
            $this->stdout("Предупреждение: Не удалось создать эскиз: " . $e->getMessage() . "\n", Console::FG_YELLOW);
            return null;
        }
    }   
    /**
     * Форматирует размер в байтах в читаемый вид
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Помощь по команде
     */
    public function actionIndex()
    {
        $this->stdout("\nКоманды для работы с фотографиями бойцов:\n\n", Console::FG_BLUE);
        
        $this->stdout("Добавить фотографию:\n", Console::FG_GREEN);
        $this->stdout("  yii photo/add <fighter_id> <file_path> [options]\n\n");
        
        $this->stdout("Параметры:\n");
        $this->stdout("  fighter_id    - ID бойца в базе данных (обязательный)\n");
        $this->stdout("  file_path     - Путь к файлу фотографии (обязательный)\n");
        $this->stdout("  --description - Описание фотографии (необязательный)\n");
        $this->stdout("  --year        - Год фотографии (необязательный)\n");
        $this->stdout("  --main        - Признак главной фотографии: 0 или 1 (по умолчанию 0)\n\n");
        
        $this->stdout("Примеры:\n", Console::FG_YELLOW);
        $this->stdout("  yii photo/add 15 /path/to/photo.jpg\n");
        $this->stdout("  yii photo/add 15 /path/to/photo.jpg --main=1\n");
        $this->stdout("  yii photo/add 15 /path/to/photo.jpg --description=\"Фото в военной форме\" --year=1943\n");
        $this->stdout("  yii photo/add 15 /path/to/photo.jpg \"Фото с семьей\" 1941 1\n");
        $this->stdout("  yii photo/add 23 /path/to/photo.png --main=1\n");
        
        return ExitCode::OK;
    }
}