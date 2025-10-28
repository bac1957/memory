<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class FighterSearch extends Model
{
    public $q; // поисковый запрос

    public function rules()
    {
        return [
            [['q'], 'string', 'max' => 255],
            ['q', 'filter', 'filter' => 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'q' => 'Фамилия Имя Отчество или год рождения',
        ];
    }

    public function search($params)
    {
        $query = Fighter::find()
            ->joinWith(['status', 'photos'])
            ->andWhere(['fighter_photo.status' => FighterPhoto::STATUS_APPROVED])
            ->groupBy('fighter.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
            'sort' => [
                'defaultOrder' => [
                    'last_name' => SORT_ASC,
                    'first_name' => SORT_ASC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Обработка поискового запроса
        if (!empty($this->q)) {
            $this->applySearchConditions($query, $this->q);
        }

        return $dataProvider;
    }

    /**
     * Применяет условия поиска к запросу
     */
    private function applySearchConditions($query, $searchQuery)
    {
        // Разбиваем запрос на компоненты
        $components = $this->parseSearchQuery($searchQuery);
        
        // Применяем поиск по ФИО если есть
        if (!empty($components['fio'])) {
            $this->applyNameSearch($query, $components['fio']);
        }
        
        // Применяем поиск по году если есть
        if (!empty($components['year'])) {
            $this->applyYearSearch($query, $components['year']);
        }
    }

    /**
     * Парсит поисковый запрос на ФИО и год
     */
    private function parseSearchQuery($query)
    {
        $result = [
            'fio' => [],
            'year' => null
        ];
        
        $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($terms as $term) {
            // Проверяем, является ли термин годом или диапазоном годов
            if ($this->isYearTerm($term)) {
                $result['year'] = $term;
            } else {
                $result['fio'][] = $term;
            }
        }
        
        return $result;
    }

    /**
     * Проверяет, является ли строка годом или диапазоном годов
     */
    private function isYearTerm($term)
    {
        // Проверяем на диапазон годов (1918-1925)
        if (preg_match('/^\d{4}-\d{4}$/', $term)) {
            list($start, $end) = explode('-', $term);
            return $start >= 1900 && $start <= 1945 && $end >= 1900 && $end <= 1945 && $start <= $end;
        }
        
        // Проверяем на одиночный год (1918)
        if (preg_match('/^\d{4}$/', $term)) {
            $year = (int)$term;
            return $year >= 1900 && $year <= 1945;
        }
        
        return false;
    }

    /**
     * Применяет поиск по году
     */
    private function applyYearSearch($query, $yearTerm)
    {
        if (strpos($yearTerm, '-') !== false) {
            // Диапазон годов
            list($startYear, $endYear) = explode('-', $yearTerm);
            $query->andWhere(['between', 'fighter.birth_year', (int)$startYear, (int)$endYear]);
        } else {
            // Одиночный год
            $query->andWhere(['fighter.birth_year' => (int)$yearTerm]);
        }
    }

    /**
     * Применяет поиск по ФИО
     */
    private function applyNameSearch($query, $nameTerms)
    {
        $nameCount = count($nameTerms);
        
        if ($nameCount === 0) {
            return;
        }
        
        // Если один термин - ищем по фамилии
        if ($nameCount === 1) {
            $query->andWhere(['or',
                ['like', 'fighter.last_name', $nameTerms[0]],
                ['like', 'fighter.first_name', $nameTerms[0]],
                ['like', 'fighter.middle_name', $nameTerms[0]]
            ]);
            return;
        }
        
        // Если два термина - фамилия и имя
        if ($nameCount === 2) {
            $query->andWhere(['and',
                ['like', 'fighter.last_name', $nameTerms[0]],
                ['like', 'fighter.first_name', $nameTerms[1]]
            ]);
            return;
        }
        
        // Если три термина - фамилия, имя, отчество
        if ($nameCount === 3) {
            $query->andWhere(['and',
                ['like', 'fighter.last_name', $nameTerms[0]],
                ['like', 'fighter.first_name', $nameTerms[1]],
                ['like', 'fighter.middle_name', $nameTerms[2]]
            ]);
            return;
        }
        
        // Если больше трех терминов - ищем по всем полям
        if ($nameCount > 3) {
            $orConditions = ['or'];
            foreach ($nameTerms as $term) {
                $orConditions[] = ['like', 'fighter.last_name', $term];
                $orConditions[] = ['like', 'fighter.first_name', $term];
                $orConditions[] = ['like', 'fighter.middle_name', $term];
            }
            $query->andWhere($orConditions);
        }
    }
}
