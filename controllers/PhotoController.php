<?php
namespace app\controllers;

use Yii;
use app\models\FighterPhoto;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

class PhotoController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view', 'thumbnail'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionView($id)
    {
        $photo = $this->findModel($id);
        
        // Проверяем права доступа
        if ($photo->status !== FighterPhoto::STATUS_APPROVED && !Yii::$app->user->can('moderator')) {
            throw new \yii\web\ForbiddenHttpException('Фотография находится на модерации');
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', $photo->mime_type);
        Yii::$app->response->headers->set('Content-Length', strlen($photo->photo_data));
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=86400');
        Yii::$app->response->headers->set('Pragma', 'cache');
        
        return $photo->photo_data;
    }

    public function actionThumbnail($id)
    {
        $photo = $this->findModel($id);
        
        if ($photo->status !== FighterPhoto::STATUS_APPROVED && !Yii::$app->user->can('moderator')) {
            throw new \yii\web\ForbiddenHttpException('Фотография находится на модерации');
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        
        // Используем миниатюру если есть, иначе создаем на лету
        $imageData = $photo->thumbnail_data;
        $mimeType = 'image/jpeg'; // Миниатюры всегда JPEG
        
        if (!$imageData) {
            $imageData = $this->createThumbnailOnFly($photo->photo_data, $photo->mime_type);
        }
        
        Yii::$app->response->headers->set('Content-Type', $mimeType);
        Yii::$app->response->headers->set('Content-Length', strlen($imageData));
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=86400');
        
        return $imageData;
    }

    /**
     * Создание миниатюры на лету
     */
    private function createThumbnailOnFly($imageData, $originalMimeType)
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'thumb_');
            file_put_contents($tempFile, $imageData);
            
            $image = \yii\imagine\Image::getImagine()->open($tempFile);
            $thumbnail = $image->thumbnail(
                new \Imagine\Image\Box(400, 400),
                \Imagine\Image\ImageInterface::THUMBNAIL_INSET
            );
            
            $thumbnailData = $thumbnail->get('jpg', ['quality' => 85]);
            unlink($tempFile);
            
            return $thumbnailData;
            
        } catch (\Exception $e) {
            Yii::error('Ошибка создания миниатюры на лету: ' . $e->getMessage());
            return $imageData; // Возвращаем оригинал если ошибка
        }
    }

    protected function findModel($id)
    {
        if (($model = FighterPhoto::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Фотография не найдена.');
    }

    public function actionTest($id)
    {
        $photo = FighterPhoto::findOne($id);
        if (!$photo) {
            return "Фото не найдено";
        }
    
        return $this->render('test', [
            'photo' => $photo
        ]);
    }
}
