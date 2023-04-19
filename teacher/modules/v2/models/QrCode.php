<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\base\Model;
use common\models\Curl;
use components\Spark;

class QrCode extends Model
{
	static public function getChapters($course_id)
    {
		$chapters = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['course-chapters'].'?course_id='.$course_id
        );
        foreach ($chapters as $v) {
            $chapter[] = [
                'id' => $v->id,
                'name' => $v->title,
                'studio_id' => $v->studio_id->nickname,
                'instructor' => $v->instructor_id->nickname,
                'preview' => $v->preview_image,
                'cc_id' => "<!DOCTYPE html><html><head></head><body style='margin:0;padding:0; background:black;'>".Spark::getPlayCode($v->chapter, $v->charging_option * 10)."</body></html>",
                'watch_count' => $v->play_count + $v->play_count_increment,
                'description' => $v->chapter_detail
            ];
        }
        return $chapter;
	}
}
