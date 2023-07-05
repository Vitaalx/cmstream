<?php

namespace Services\Back;

use Entity\Video;
use Entity\Url;

class VideoManagerService
{
    /**
     * this function create a video
     *
     * @param array $url
     * @param string $title
     * @param string $description
     * @param integer $category
     */
    public static function createVideo(): Video
    {
        return Video::insertOne([]);
    }

    /**
     * this function update a video
     *
     * @param integer $id
     * @param string $title
     * @param string $description
     * @param integer $category
     * @return void
     */
    public static function updateVideo(int $id, string $title, string $description): void
    {
        try {
            $video = Video::findFirst([
                "id" => $id
            ]);
            $video->setTitle($title);
            $video->setDescription($description);
            $video->setUpdatedAt(date("Y-m-d H:i:s"));
            $video->save();
        } catch (\Exception $e) {
            throw new \Exception("Error update video: " . $e->getMessage());
        }
    }

    /**
     * this function get url where video id
     *
     * @param integer $video
     * @return array
     */
    public static function getUrlWhereVideo(int $video): array
    {
        $urls = Url::findMany([
            "video_id" => $video
        ]);
        $result = [];
        foreach ($urls as $url) {
            $result[] = $url->getValue();
        }
        return $result;
    }

    public static function createUrlWhereVideo(int $video_id, string $content): Url
    {
        return Url::insertOne([
            "video_id" => $video_id,
            "value" => $content
        ]);
    }
}
