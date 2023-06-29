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
     * @return void
     */
    public static function createVideo(string $title, string $description): Video
    {
        try {
            $video = Video::insertOne([
                "title" => $title,
                "description" => $description,
            ]);
            return $video;
        } catch (\Exception $e) {
            throw new \Exception("Error creating video - " . $e->getMessage());
        }
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
        try {
            $urls = Url::findMany([
                "video_url_id" => $video
            ]);
            $result = [];
            foreach ($urls as $url) {
                $result[] = $url->getUrl();
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error get url where video: " . $e->getMessage());
        }
    }

    public static function createUrlWhereVideo(int $video_id, string $content): Url
    {
        try {
            $url = Url::insertOne([
                "video_url_id" => $video_id,
                "url" => $content
            ]);
            return $url;
        } catch (\Exception $e) {
            throw new \Exception("Error create url where video: " . $e->getMessage());
        }
    }
}
